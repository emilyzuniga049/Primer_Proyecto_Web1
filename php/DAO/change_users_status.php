<?php
require_once __DIR__ . '/../Actions/ssession_admin.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

function back($msg, $isErr=false){
  $qs = http_build_query($isErr?['error'=>$msg]:['msg'=>$msg]);
  header("Location: /Admin/users.php?$qs");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') back('Invalid request method', true);

$userId = (int)($_POST['user_id'] ?? 0);
$newStatus = $_POST['new_status'] ?? '';

if ($userId <= 0 || !in_array($newStatus, ['active','inactive'], true)) back('Invalid data.', true);

if ($userId === (int)($_SESSION['user_id'] ?? 0) && $newStatus === 'inactive') {
  back('You cannot deactivate your own account.', true);
}

$stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
$stmt->bind_param('si', $newStatus, $userId);
if (!$stmt->execute()) {
  $err = $stmt->error; $stmt->close();
  back("Database error: $err", true);
}
$stmt->close();
back("User status updated to $newStatus.");
