<?php
require_once __DIR__ . '/../Actions/session.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

function redirect_back($msgKey, $msgVal){
  $loc = '../Profile/edit_profile.php?'.$msgKey.'='.urlencode($msgVal);
  header('Location: '.$loc); exit;
}

$user_id       = (int)($_POST['user_id'] ?? 0);
$current_email = trim($_POST['current_email'] ?? '');
$current_photo = trim($_POST['current_photo'] ?? '');

$first_name  = trim($_POST['first_name'] ?? '');
$last_name   = trim($_POST['last_name'] ?? '');
$national_id = trim($_POST['national_id'] ?? '');
$birth_date  = trim($_POST['birth_date'] ?? '');
$email       = trim($_POST['email'] ?? '');
$phone       = trim($_POST['phone'] ?? '');

$pass        = $_POST['password']  ?? '';
$pass2       = $_POST['password2'] ?? '';

if ($user_id <= 0 || $first_name==='' || $last_name==='' || $national_id==='' || $birth_date==='' || $email==='' || $phone==='') {
  redirect_back('error','Missing required fields');
}


if (strcasecmp($email, $current_email) !== 0) {
  $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
  $check->bind_param('si', $email, $user_id);
  $check->execute();
  $exists = $check->get_result()->num_rows > 0;
  $check->close();
  if ($exists) redirect_back('error','Email already in use');
}


$photo_path = $current_photo;
if (!empty($_FILES['photo']['name'])) {
  $file = $_FILES['photo'];
  if ($file['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
      redirect_back('error','Invalid image type. Use JPG/PNG/WEBP');
    }
    if ($file['size'] > 2*1024*1024) { // 2MB
      redirect_back('error','Image too large (max 2MB)');
    }
    // carpeta destino
    $destDir = __DIR__ . '/../../Img/users';
    if (!is_dir($destDir)) { @mkdir($destDir, 0777, true); }
    $ext = $allowed[$mime];
    $safeName = 'u'.$user_id.'_'.time().'.'.$ext;
    $destPath = $destDir . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
      redirect_back('error','Cannot save image');
    }

    $photo_path = '/Img/users/'.$safeName;
  } else if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
    redirect_back('error','Upload error: '.$file['error']);
  }
}


$setPasswordSql = '';
$params = [];
$types  = '';

if ($pass !== '' || $pass2 !== '') {
  if ($pass !== $pass2) redirect_back('error','Passwords do not match');
  if (strlen($pass) < 6) redirect_back('error','Password must be at least 6 characters');
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $setPasswordSql = ', password_hash = ?';
  $params[] = $hash; $types .= 's';
}

//Actualizar datos
$sql = "UPDATE users
        SET first_name=?, last_name=?, national_id=?, birth_date=?, email=?, phone=?, photo_path=? $setPasswordSql
        WHERE id=?";
$stmt = $conn->prepare($sql);

$params = array_merge(
  [$first_name, $last_name, $national_id, $birth_date, $email, $phone, $photo_path],
  $params,
  [$user_id]
);
$types = 'ssssss' . 's' . $types . 'i'; 

$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
  $err = $stmt->error;
  $stmt->close();
  redirect_back('error', "DB error: $err");
}
$stmt->close();


$_SESSION['user_name']  = $first_name.' '.$last_name;
$_SESSION['user_email'] = $email;

$conn->close();
header('Location: /Driver/my_rides.php?msg=Profile updated successfully');
