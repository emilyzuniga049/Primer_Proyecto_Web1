<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Conexion/db_conexion.php'; // define $conn (mysqli)

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No mysqli connection available ($conn).']);
    exit;
}

// Parámetros de filtro
$from  = isset($_GET['from'])  ? trim($_GET['from']) : '';
$to    = isset($_GET['to'])    ? trim($_GET['to'])   : '';
$daysQ = isset($_GET['days'])  ? trim($_GET['days']) : ''; // ej: "mon,wed,fri"

// Normalizar días permitidos
$allowedDays = ['mon','tue','wed','thu','fri','sat','sun'];
$days = array_values(array_filter(array_map(function($d){
    return strtolower(trim($d));
}, explode(',', $daysQ)), function($d) use ($allowedDays){
    return in_array($d, $allowedDays, true);
}));

$where  = ["r.status = 'active'"];
$params = [];
$types  = '';

if ($from !== '') {
    $where[] = 'r.origin = ?';
    $params[] = $from;
    $types   .= 's';
}
if ($to !== '') {
    $where[] = 'r.destination = ?';
    $params[] = $to;
    $types   .= 's';
}
if (!empty($days)) {
    $or = [];
    foreach ($days as $d) {
        $or[]     = "FIND_IN_SET(?, r.days_set)";
        $params[] = $d;
        $types   .= 's';
    }
    $where[] = '(' . implode(' OR ', $or) . ')';
}
$where[] = 'r.available_seats > 0';

$sql = "
SELECT
  r.id,
  r.origin,
  r.destination,
  r.departure_time,
  r.seat_price,
  r.available_seats,
  r.days_set,
  u.email AS driver_email,
  v.make  AS vehicle_make,
  v.model AS vehicle_model,
  v.year  AS vehicle_year
FROM rides r
JOIN users u ON u.id = r.driver_id
LEFT JOIN vehicles v ON v.id = r.vehicle_id
" . (count($where) ? 'WHERE ' . implode(' AND ', $where) : '') . "
ORDER BY r.created_at DESC
";


$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

if (!empty($params)) {
    $bindParams = [];
    $bindParams[] = & $types;
    foreach ($params as $k => $v) {
        $bindParams[] = & $params[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    exit;
}

$res = $stmt->get_result();
$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

$data = array_map(function($r) {
    return [
        'id'        => (int)$r['id'],
        'from'      => $r['origin'],
        'to'        => $r['destination'],
        'time'      => isset($r['departure_time']) ? substr($r['departure_time'], 0, 5) : null,
        'seats'     => (int)$r['available_seats'],
        'fee'       => isset($r['seat_price']) ? (float)$r['seat_price'] : null,
        'userEmail' => $r['driver_email'],
        'vehicle'   => [
            'make'  => $r['vehicle_make'],
            'model' => $r['vehicle_model'],
            'year'  => isset($r['vehicle_year']) ? (int)$r['vehicle_year'] : null,
        ],
        'days'      => array_values(array_filter(array_map('trim', explode(',', (string)$r['days_set']))))
    ];
}, $rows);


echo json_encode(['ok' => true, 'rides' => $data]);
