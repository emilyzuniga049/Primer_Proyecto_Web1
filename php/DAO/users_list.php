<?php
// Validar y preparar filtros
$role   = $_GET['role']   ?? '';
$status = $_GET['status'] ?? '';

$allowRole   = ['admin','driver','passenger'];
$allowStatus = ['active','inactive','pending'];

$where  = [];
$params = [];
$types  = '';

if (in_array($role, $allowRole, true)) {
  $where[] = 'role = ?';
  $params[] = $role;
  $types .= 's';
}
if (in_array($status, $allowStatus, true)) {
  $where[] = 'status = ?';
  $params[] = $status;
  $types .= 's';
}

// Armar SQL
$sql = "SELECT id, role, status, first_name, last_name, email, phone, birth_date, created_at
        FROM users";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY role, status, created_at DESC';

// Ejecutar
$stmt = $conn->prepare($sql);
if ($where) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$users = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
