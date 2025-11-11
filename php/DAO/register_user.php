<?php
require_once __DIR__ . '/../Conexion/db_conexion.php'; 
require_once __DIR__ . '/../SendMails/send_email.php';

function redirect_with($url, $params = []) {
    if (!empty($params)) {
        $q = http_build_query($params);
        if (strpos($url, '?') === false) $url .= '?' . $q; else $url .= '&' . $q;
    }
    header("Location: $url");
    exit;
}

function generarToken(): array {
    // Genera token seguro (64 chars hex) y su hash SHA-256
    $rawToken  = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);
    return [$rawToken, $tokenHash];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with('/Index.html', ['error' => 'Invalid request method']);
}

$role = strtolower(trim($_POST['role'] ?? ''));
if (!in_array($role, ['passenger', 'driver'], true)) {
    redirect_with('/Index.html', ['error' => 'Invalid role']);
}
$form_back = ($role === 'passenger') ? '/Register_User/Index.html' : '/Register_Driver/Index.html';

// === Datos del formulario ===
$first_name  = trim($_POST['first_name']  ?? '');
$last_name   = trim($_POST['last_name']   ?? '');
$national_id = trim($_POST['national_id'] ?? '');
$birth_date  = trim($_POST['birth_date']  ?? '');
$email       = trim($_POST['email']       ?? '');
$phone       = trim($_POST['phone']       ?? '');
$password    = $_POST['password']  ?? '';
$password2   = $_POST['password2'] ?? '';

// === Validaciones ===
if ($first_name === '' || $last_name === '' || $national_id === '' || $birth_date === '' ||
    $email === '' || $phone === '' || $password === '' || $password2 === '') {
    redirect_with($form_back, ['error' => 'Please fill in all required fields.']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with($form_back, ['error' => 'Invalid email address.']);
}
if ($password !== $password2) {
    redirect_with($form_back, ['error' => 'Passwords do not match.']);
}
if (strlen($password) < 8) {
    redirect_with($form_back, ['error' => 'Password must be at least 8 characters long.']);
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date)) {
    redirect_with($form_back, ['error' => 'Invalid birth date format (use YYYY-MM-DD).']);
}

// === Verificar email único ===
$sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    redirect_with($form_back, ['error' => 'This email is already registered.']);
}
$stmt->close();

// === Foto de usuario (opcional) ===
$photo_path = null;
if (!empty($_FILES['photo']['name'])) {
    $upload_dir_abs = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR . 'Img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;
    if ($upload_dir_abs === false) $upload_dir_abs = __DIR__ . '/../../Img/users/';
    if (!is_dir($upload_dir_abs)) {
        if (!mkdir($upload_dir_abs, 0777, true)) {
            redirect_with($form_back, ['error' => 'Failed to prepare upload directory.']);
        }
    }

    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg','jpeg','png','webp'];
    if (!in_array($ext, $allowed_ext, true)) {
        redirect_with($form_back, ['error' => 'Unsupported image format. Allowed: JPG, PNG, WebP.']);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES['photo']['tmp_name']);
    $allowed_mime = ['image/jpeg','image/png','image/webp'];
    if (!in_array($mime, $allowed_mime, true)) {
        redirect_with($form_back, ['error' => 'Invalid image content type.']);
    }

    $new_name = uniqid('user_', true) . '.' . $ext;
    $target_abs = $upload_dir_abs . $new_name;
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_abs)) {
        redirect_with($form_back, ['error' => 'Failed to upload the image.']);
    }

    $photo_path = 'Img/users/' . $new_name;
}

// === Insertar usuario en estado 'pending' ===
$password_hash = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO users 
(role, status, first_name, last_name, national_id, birth_date, email, phone, photo_path, password_hash)
VALUES (?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

$stmt->bind_param(
    'sssssssss',
    $role,
    $first_name,
    $last_name,
    $national_id,
    $birth_date,
    $email,
    $phone,
    $photo_path,
    $password_hash
);

if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    $conn->close();
    redirect_with($form_back, ['error' => "Database error: $err"]);
}

$user_id = $stmt->insert_id;
$stmt->close();

// === Generar token y guardar ===
[$rawToken, $tokenHash] = generarToken();
$expires_at = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');

$sql = "INSERT INTO email_verification_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $user_id, $tokenHash, $expires_at);
if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    redirect_with($form_back, ['error' => 'Could not generate verification token.']);
}
$stmt->close();

// === Construir enlace de activación ===
$base_url = 'http://www.proyecto1web2aventones.isw:8080';
$activate_path = '/php/Actions/activate.php';
$link = $base_url . $activate_path . '?email=' . urlencode($email) . '&token=' . $rawToken;

// === Enviar correo de activación ===
$subject = 'Activa tu cuenta en Aventones';
$html = "
  <h2>¡Hola, {$first_name}!</h2>
  <p>Gracias por registrarte en <b>Aventones</b>. Para activar tu cuenta, haz clic en el siguiente enlace:</p>
  <p><a href='{$link}'>Activar cuenta</a></p>
  <p>Si no puedes hacer clic, copia y pega esta URL en tu navegador:<br>{$link}</p>
  <p><small>El enlace expira en 24 horas.</small></p>
";
$text = "Hola, {$first_name}. Activa tu cuenta con este enlace (expira en 24h): {$link}";

if (!enviarCorreo($email, "{$first_name} {$last_name}", $subject, $html, $text)) {
    $conn->close();
    redirect_with('/Index.html', [
        'msg'   => 'Registration created, but the verification email could not be sent. Try again later.',
        'email' => $email
    ]);
}

$conn->close();

echo "<script>
  alert('Registration successful! Please check your email to activate your account.');
  window.location.href = '/Index.html';
</script>";
exit;
?>
