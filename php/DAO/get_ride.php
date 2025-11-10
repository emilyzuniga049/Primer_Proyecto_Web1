<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Conexion/db_conexion.php'; // crea $conn (mysqli)

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing or invalid id']);
    exit;
}

$rideId = (int)$_GET['id'];

$sql = "
SELECT
  r.id,
  r.origin,
  r.destination,
  r.departure_time,
  r.days_set,
  r.seat_price,
  r.available_seats,
  u.email AS driver_email,
  v.make  AS vehicle_make,
  v.model AS vehicle_model,
  v.year  AS vehicle_year
FROM rides r
JOIN users u ON u.id = r.driver_id
LEFT JOIN vehicles v ON v.id = r.vehicle_id
WHERE r.id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $rideId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    exit;
}

$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['ok' => true, 'ride' => null]);
    exit;
}

// Normalizar time a HH:MM porque tu JS lo espera así
$time = '';
if (!empty($row['departure_time'])) {
    // departure_time viene como HH:MM:SS
    $time = substr($row['departure_time'], 0, 5); // HH:MM
}

$ride = [
    'id'        => (int)$row['id'],
    'from'      => $row['origin'],
    'to'        => $row['destination'],
    'time'      => $time,
    'days'      => array_values(array_filter(array_map('trim', explode(',', (string)$row['days_set'])))),
    'seats'     => (int)$row['available_seats'],
    'fee'       => isset($row['seat_price']) ? (float)$row['seat_price'] : null,
    'userEmail' => $row['driver_email'],
    'vehicle'   => [
        'make'  => $row['vehicle_make'],
        'model' => $row['vehicle_model'],
        'year'  => isset($row['vehicle_year']) ? (int)$row['vehicle_year'] : null,
    ],
];

echo json_encode(['ok' => true, 'ride' => $ride]);
