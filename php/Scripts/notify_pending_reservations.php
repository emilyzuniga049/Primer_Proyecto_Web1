<?php
/**
 * Script de consola para notificar choferes con reservas pendientes
 * Uso:
 *   php notify_pending_reservations.php x
 *     --- notifica choferes con solicitudes de hace más de x minutos sin atender
 */

require_once __DIR__ . '/../Conexion/db_conexion.php';
require_once __DIR__ . '/../SendMails/send_email.php';

// --- Verificar argumento ---
if ($argc < 2) {
    echo "Uso: php {$argv[0]} <minutos>\n";
    exit(1);
}

$minutos = (int)$argv[1];
if ($minutos <= 0) {
    echo "El parámetro de minutos debe ser un número mayor que 0.\n";
    exit(1);
}

echo "Buscando reservas pendientes de hace más de {$minutos} minutos...\n";

// --- Consulta de reservas pendientes ---
$sql = "
SELECT
    r.id            AS reservation_id,
    r.ride_id,
    r.created_at,
    rd.driver_id,
    u.email         AS driver_email,
    u.first_name    AS driver_name,
    rd.origin,
    rd.destination
FROM reservations r
JOIN rides rd ON rd.id = r.ride_id
JOIN users u  ON u.id = rd.driver_id
WHERE r.status = 'pending'
  AND r.created_at <= (NOW() - INTERVAL ? MINUTE)
ORDER BY rd.driver_id, r.created_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $minutos);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "No se encontraron reservas pendientes más antiguas de {$minutos} minutos.\n";
    exit(0);
}

// --- Agrupar por chofer ---
$choferes = [];
while ($row = $res->fetch_assoc()) {
    $driverId = $row['driver_id'];
    if (!isset($choferes[$driverId])) {
        $choferes[$driverId] = [
            'email' => $row['driver_email'],
            'name'  => $row['driver_name'],
            'reservas' => []
        ];
    }
    $choferes[$driverId]['reservas'][] = [
        'id'         => $row['reservation_id'],
        'ride_id'    => $row['ride_id'],
        'origin'     => $row['origin'],
        'destination'=> $row['destination'],
        'created_at' => $row['created_at']
    ];
}
$stmt->close();

// --- Envío de correos ---
$totalCorreos = 0;
foreach ($choferes as $driver) {
    $to      = $driver['email'];
    $nombre  = $driver['name'];
    $asunto  = "Tienes solicitudes pendientes en Aventones";

    $listado = '';
    foreach ($driver['reservas'] as $r) {
        $fecha = date('d/m/Y H:i', strtotime($r['created_at']));
        $listado .= "<li><b>Reserva #{$r['id']}</b>: {$r['origin']} → {$r['destination']} (creada el {$fecha})</li>";
    }

    $mensajeHTML = "
        <p>Hola <b>{$nombre}</b>,</p>
        <p>Tienes solicitudes de viaje pendientes por revisar en el sistema Aventones.</p>
        <p>Detalles de las solicitudes:</p>
        <ul>{$listado}</ul>
        <p>Por favor, inicia sesión y gestiona tus reservas pendientes.</p>
        <br>
        <p>Saludos,<br><b>Equipo Aventones</b></p>
    ";

    $mensajePlano = "Hola {$nombre},\nTienes solicitudes de viaje pendientes por revisar en Aventones.\n\nPor favor inicia sesión para gestionarlas.";

    if (enviarCorreo($to, $nombre, $asunto, $mensajeHTML, $mensajePlano)) {
        echo "Correo enviado a {$nombre} <{$to}> (" . count($driver['reservas']) . " reservas)\n";
        $totalCorreos++;
    } else {
        echo " Error al enviar correo a {$to}\n";
    }
}

echo "\nProceso completado. Correos enviados: {$totalCorreos}\n";
exit(0);
