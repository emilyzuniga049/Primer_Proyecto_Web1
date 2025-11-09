<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$driver_id     = (int)($_SESSION['user_id'] ?? 0);
$reservation_id= (int)($_POST['reservation_id'] ?? 0);
$action        = trim($_POST['action'] ?? '');

if ($driver_id<=0 || $reservation_id<=0 || !in_array($action, ['accept','reject'], true)) {
  header('Location: ../Driver/bookings.php?error=Invalid+request'); exit;
}

$sql = "
  SELECT rsv.id, rsv.status, rsv.seats, rsv.ride_id,
         rd.driver_id, rd.available_seats
  FROM reservations rsv
  JOIN rides rd ON rd.id = rsv.ride_id
  WHERE rsv.id = ? AND rd.driver_id = ?
  LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $reservation_id, $driver_id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$stmt->close();

if (!$data) {
  header('Location: ../Driver/bookings.php?error=Reservation+not+found'); exit;
}
if ($data['status'] !== 'pending') {
  header('Location: ../Driver/bookings.php?error=Only+pending+reservations+can+be+changed'); exit;
}

$ride_id = (int)$data['ride_id'];
$seats   = (int)$data['seats'];

if ($action === 'accept') {
  // Verificar asientos disponibles y descontar
  if ($data['available_seats'] < $seats) {
    header('Location: ../Driver/bookings.php?error=Not+enough+available+seats'); exit;
  }

  $conn->begin_transaction();
  try {
    // actualizar reserva
    $up1 = $conn->prepare("UPDATE reservations SET status='accepted', updated_at=NOW() WHERE id=?");
    $up1->bind_param('i', $reservation_id);
    if(!$up1->execute()) throw new Exception($up1->error);
    $up1->close();

    // descontar asientos del ride
    $up2 = $conn->prepare("UPDATE rides SET available_seats = available_seats - ? WHERE id=? AND available_seats >= ?");
    $up2->bind_param('iii', $seats, $ride_id, $seats);
    if(!$up2->execute() || $up2->affected_rows===0) throw new Exception('Seat update failed');
    $up2->close();

    $conn->commit();
    header('Location: ../Driver/bookings.php?msg=Reservation+accepted');
    exit;
  } catch (Exception $ex) {
    $conn->rollback();
    header('Location: ../Driver/bookings.php?error='.urlencode('DB error: '.$ex->getMessage()));
    exit;
  }

} else { // negar
  $upd = $conn->prepare("UPDATE reservations SET status='rejected', updated_at=NOW() WHERE id=?");
  $upd->bind_param('i', $reservation_id);
  if (!$upd->execute()) {
    $err = urlencode($upd->error);
    $upd->close();
    header('Location: ../Driver/bookings.php?error=DB+'.$err); exit;
  }
  $upd->close();
  header('Location: ../Driver/bookings.php?msg=Reservation+rejected');
  exit;
}
