<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Actions/session.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Invalid session']);
  exit;
}

$sql = "SELECT first_name, bio FROM users WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>$conn->error]); exit; }
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
  http_response_code(404);
  echo json_encode(['ok' => false, 'error' => 'User not found']);
  exit;
}

echo json_encode([
  'ok' => true,
  'first_name' => $row['first_name'] ?? '',
  'bio' => $row['bio'] ?? ''
]);
