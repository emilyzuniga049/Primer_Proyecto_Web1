<?php
require_once __DIR__ . '/../Actions/ssession_admin.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

function back($msg, $isErr=false){
  $q = $isErr ? ['error'=>$msg] : ['msg'=>$msg];
  $qs = http_build_query($q);
  header("Location: /Admin/users.php?$qs");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') back('Invalid request method', true);

$role = 'admin'; 
$first = trim($_POST['first_name'] ?? '');
$last  = trim($_POST['last_name'] ?? '');
$nid   = trim($_POST['national_id'] ?? '');
$birth = trim($_POST['birth_date'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$pass1 = $_POST['password'] ?? '';
$pass2 = $_POST['password2'] ?? '';

if ($first===''||$last===''||$nid===''||$birth===''||$email===''||$phone===''||$pass1===''||$pass2==='') back('Please fill in all required fields.', true);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) back('Invalid email address.', true);
if ($pass1 !== $pass2) back('Passwords do not match.', true);
if (strlen($pass1) < 8) back('Password must be at least 8 characters long.', true);

$stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0){ $stmt->close(); back('This email is already registered.', true); }
$stmt->close();

// Foto 
$photo_path = null;
if (!empty($_FILES['photo']['name'])) {
  $upload_dir = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR . 'Img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;
  if ($upload_dir === false) $upload_dir = __DIR__ . '/../../Img/users/';
  if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

  $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) back('Unsupported image format. Allowed: JPG, PNG, WebP.', true);

  $new_name = uniqid('user_', true) . '.' . $ext;
  $target = $upload_dir . $new_name;
  if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) back('Failed to upload the image.', true);

  $photo_path = 'Img/users/' . $new_name;
}

$hash = password_hash($pass1, PASSWORD_BCRYPT);

$sql = "INSERT INTO users (role, status, first_name, last_name, national_id, birth_date, email, phone, photo_path, password_hash)
        VALUES ('admin','active',?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
if ($photo_path === null) {
  $null = null;
  $stmt->bind_param('ssssssss', $first,$last,$nid,$birth,$email,$phone,$null,$hash);
} else {
  $stmt->bind_param('ssssssss', $first,$last,$nid,$birth,$email,$phone,$photo_path,$hash);
}
if (!$stmt->execute()) {
  $err = $stmt->error;
  $stmt->close();
  back("Database error: $err", true);
}
$stmt->close();
back('Administrator created successfully.');
