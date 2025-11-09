<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$driver_id     = (int)($_SESSION['user_id'] ?? 0);
$vehicle_id    = (int)($_POST['vehicle_id'] ?? 0);

$plate         = trim($_POST['plate'] ?? '');
$color         = trim($_POST['color'] ?? '');
$make          = trim($_POST['make'] ?? '');
$model         = trim($_POST['model'] ?? '');
$year          = (int)($_POST['year'] ?? 0);
$seat_capacity = (int)($_POST['seat_capacity'] ?? 0);

if ($driver_id<=0 || $vehicle_id<=0 || $plate==='' || $make==='' || $model==='' || !$year || !$seat_capacity) {
  header('Location: ../Driver/vehicles.php?error=Invalid+data'); exit;
}

// validar propiedad
$sql = "SELECT id, photo_path FROM vehicles WHERE id=? AND user_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $vehicle_id, $driver_id);
$stmt->execute();
$veh = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$veh) { header('Location: ../Driver/vehicles.php?error=Vehicle+not+found'); exit; }

// validar placa
$chk = $conn->prepare("SELECT id FROM vehicles WHERE plate=? AND id<>? LIMIT 1");
$chk->bind_param('si', $plate, $vehicle_id);
$chk->execute();
if ($chk->get_result()->num_rows>0) { $chk->close(); header('Location: ../Driver/vehicle_edit.php?id='.$vehicle_id.'&error=Plate+already+exists'); exit; }
$chk->close();


$photo_rel = $veh['photo_path'];
if (!empty($_FILES['photo']['name'])) {
  $f = $_FILES['photo'];
  if ($f['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $mime = mime_content_type($f['tmp_name']);
    if (!isset($allowed[$mime])) { header('Location: ../Driver/vehicle_edit.php?id='.$vehicle_id.'&error=Invalid+image'); exit; }
    if ($f['size'] > 3*1024*1024) { header('Location: ../Driver/vehicle_edit.php?id='.$vehicle_id.'&error=Image+too+large'); exit; }
    $dir = __DIR__ . '/../../Img/vehicles';
    if (!is_dir($dir)) @mkdir($dir,0777,true);
    $name = 'veh_'.$driver_id.'_'.time().'.'.$allowed[$mime];
    if (!move_uploaded_file($f['tmp_name'], $dir.'/'.$name)) {
      header('Location: ../Driver/vehicle_edit.php?id='.$vehicle_id.'&error=Cannot+save+image'); exit;
    }
    $photo_rel = 'Img/vehicles/'.$name;
  } elseif ($f['error'] !== UPLOAD_ERR_NO_FILE) {
    header('Location: ../Driver/vehicle_edit.php?id='.$vehicle_id.'&error=Upload+error'); exit;
  }
}

$upd = $conn->prepare("UPDATE vehicles
      SET plate=?, color=?, make=?, model=?, year=?, seat_capacity=?, photo_path=?
      WHERE id=? AND user_id=?");
$upd->bind_param('sssssissi', $plate, $color, $make, $model, $year, $seat_capacity, $photo_rel, $vehicle_id, $driver_id);

if (!$upd->execute()) {
  $err = urlencode($upd->error);
  $upd->close(); $conn->close();
  header('Location: ../Driver/vehicle_edit.php?id='.$vehicle_id.'&error=DB+'.$err); exit;
}
$upd->close(); $conn->close();

header('Location: ../Driver/vehicles.php?msg=Vehicle+updated');
exit;
