<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Actions/session_passenger.php';
require_once __DIR__ . '/../Conexion/db_conexion.php'; // debe crear $conn (mysqli)

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No mysqli connection available ($conn).']);
    exit;
}

// Parámetros
$input = json_decode(file_get_contents('php://input'), true);
$rideId = isset($input['ride_id']) ? (int)$input['ride_id'] : 0;
$seats  = isset($input['seats']) ? (int)$input['seats'] : 1;

// Ajusta si tu sesión usa otra clave
$passengerId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($rideId <= 0 || $passengerId <= 0 || $seats <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos.']);
    exit;
}

try {
    // 1) Verificar que el ride exista y esté activo (no se descuenta nada aquí)
    $sqlRide = "
        SELECT r.id, r.driver_id, r.status
        FROM rides r
        WHERE r.id = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sqlRide);
    if (!$stmt) throw new Exception('Prepare ride failed: ' . $conn->error);
    $stmt->bind_param('i', $rideId);
    if (!$stmt->execute()) throw new Exception('Execute ride failed: ' . $stmt->error);
    $res = $stmt->get_result();
    $ride = $res->fetch_assoc();
    $stmt->close();

    if (!$ride) {
        throw new Exception('Ride no existe.');
    }
    if ($ride['status'] !== 'active') {
        throw new Exception('El ride no está activo.');
    }
    if ((int)$ride['driver_id'] === $passengerId) {
        throw new Exception('No puedes solicitar tu propio ride.');
    }

    // 2) (Opcional recomendado) Evitar duplicados pendientes/aceptados
    $sqlDup = "
        SELECT id FROM reservations
        WHERE ride_id = ? AND passenger_id = ? AND status IN ('pending','accepted')
        LIMIT 1
    ";
    $stmt = $conn->prepare($sqlDup);
    if (!$stmt) throw new Exception('Prepare dup failed: ' . $conn->error);
    $stmt->bind_param('ii', $rideId, $passengerId);
    if (!$stmt->execute()) throw new Exception('Execute dup failed: ' . $stmt->error);
    $dup = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($dup) {
        throw new Exception('Ya tienes una solicitud en curso para este ride.');
    }

    // 3) Insertar la solicitud (status = pending). NO se tocan asientos aquí.
    $sqlIns = "
        INSERT INTO reservations (ride_id, passenger_id, seats, status, created_at)
        VALUES (?,?,?,?, NOW())
    ";
    $status = 'pending';
    $stmt = $conn->prepare($sqlIns);
    if (!$stmt) throw new Exception('Prepare insert failed: ' . $conn->error);
    $stmt->bind_param('iiis', $rideId, $passengerId, $seats, $status);
    if (!$stmt->execute()) throw new Exception('Execute insert failed: ' . $stmt->error);
    $reservationId = $stmt->insert_id;
    $stmt->close();

    echo json_encode(['ok' => true, 'reservation_id' => $reservationId]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
