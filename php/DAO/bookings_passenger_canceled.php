<?php
require_once __DIR__ . '/../Actions/session_passenger.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$passenger_id = (int)($_SESSION['user_id'] ?? 0);
if ($passenger_id <= 0) {
  header('Location: ../Passenger/bookings_passenger.php?error=' . urlencode('Invalid session.'));
  exit;
}

$reservation_id = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;
if ($reservation_id <= 0) {
  header('Location: ../Passenger/booking_passenger.php?error=' . urlencode('Invalid reservation.'));
  exit;
}

try {
  // Traer la reserva y el ride
  $sql = "
    SELECT rsv.id, rsv.ride_id, rsv.passenger_id, rsv.seats, rsv.status AS res_status,
           rd.available_seats
    FROM reservations rsv
    JOIN rides rd ON rd.id = rsv.ride_id
    WHERE rsv.id = ?
    LIMIT 1
  ";
  $stmt = $conn->prepare($sql);
  if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
  $stmt->bind_param('i', $reservation_id);
  if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();

  if (!$row) {
    throw new Exception('Reservation not found.');
  }
  if ((int)$row['passenger_id'] !== $passenger_id) {
    throw new Exception('You cannot cancel a reservation that is not yours.');
  }

  $currentStatus = $row['res_status'];
  if (in_array($currentStatus, ['rejected','cancelled','completed'], true)) {
    throw new Exception('Reservation cannot be cancelled.');
  }

  // Transacción: si aceptada, sumar asientos; si pendiente, solo cambiar estado
  $conn->begin_transaction();

  // 1) Cambiar a cancelled
  $sqlUpd = "UPDATE reservations SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
  $stmt = $conn->prepare($sqlUpd);
  if (!$stmt) throw new Exception('Prepare update failed: ' . $conn->error);
  $stmt->bind_param('i', $reservation_id);
  if (!$stmt->execute()) throw new Exception('Execute update failed: ' . $stmt->error);
  $stmt->close();

  // 2) Si estaba accepted, devolver asientos al ride
  if ($currentStatus === 'accepted') {
    $sqlSeats = "UPDATE rides SET available_seats = available_seats + ? WHERE id = ?";
    $stmt = $conn->prepare($sqlSeats);
    if (!$stmt) throw new Exception('Prepare seats failed: ' . $conn->error);
    $stmt->bind_param('ii', $row['seats'], $row['ride_id']);
    if (!$stmt->execute()) throw new Exception('Execute seats failed: ' . $stmt->error);
    $stmt->close();
  }

  $conn->commit();

  header('Location: ../Passenger/bookings_passenger.php?msg=' . urlencode('Reservation cancelled.'));
  exit;
} catch (Exception $e) {
  if ($conn->errno === 0) {
    $conn->rollback();
  }
  header('Location: ../Passenger/bookings_passenger.php?error=' . urlencode($e->getMessage()));
  exit;
}
