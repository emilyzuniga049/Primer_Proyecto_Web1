<?php
session_start();
require_once __DIR__ . '/../Conexion/db_conexion.php';

function redirect_with($url, $params = []) {
    if (!empty($params)) {
        $query = http_build_query($params);
        $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
    }
    header("Location: $url");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with('/Index.html', ['error' => 'Invalid request method']);
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    redirect_with('/Index.html', ['error' => 'Please fill in all fields.']);
}

$sql = "SELECT id, role, status, first_name, last_name, email, password_hash 
        FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect_with('/Index.html', ['error' => 'User not found.']);
}
$user = $result->fetch_assoc();
$stmt->close();

if ($user['status'] === 'pending') {
    redirect_with('/Index.html', ['error' => 'Your account is pending activation.']);
}
if ($user['status'] === 'inactive') {
    redirect_with('/Index.html', ['error' => 'Your account is inactive.']);
}

if (!password_verify($password, $user['password_hash'])) {
    redirect_with('/Index.html', ['error' => 'Incorrect password.']);
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['logged_in'] = true;

// Redirigir según rol
switch ($user['role']) {
  case 'admin':
    header('Location: /php/Admin/users.php');
    break;
  case 'driver':
    break;
  case 'passenger':
  default:
    break;
}
exit;
?>