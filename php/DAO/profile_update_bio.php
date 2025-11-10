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

$input = json_decode(file_get_contents('php://input'), true);
$bio = trim((string)($input['bio'] ?? ''));

$sql = "UPDATE users SET bio = ?, updated_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>$conn->error]); exit; }
$stmt->bind_param('si', $bio, $user_id);
$ok = $stmt->execute();
$err = $stmt->error;
$stmt->close();

if (!$ok) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $err ?: 'Update failed']);
  exit;
}

echo json_encode(['ok' => true]);
