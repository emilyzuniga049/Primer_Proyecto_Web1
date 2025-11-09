<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$driver_id     = (int)($_SESSION['user_id'] ?? 0);
$user_id       = (int)($_POST['user_id'] ?? 0);
$plate         = trim($_POST['plate'] ?? '');
$color         = trim($_POST['color'] ?? '');
$make          = trim($_POST['make'] ?? '');
$model         = trim($_POST['model'] ?? '');
$year          = (int)($_POST['year'] ?? 0);
$seat_capacity = (int)($_POST['seat_capacity'] ?? 0);

if ($driver_id <= 0 || $user_id !== $driver_id || $plate==='' || $make==='' || $model==='' || !$year || !$seat_capacity) {
  header('Location: ../Driver/vehicles.php?error=Missing+or+invalid+fields'); exit;
}

$chk = $conn->prepare("SELECT id FROM vehicles WHERE plate = ? LIMIT 1");
$chk->bind_param('s', $plate);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
  $chk->close();
  header('Location: ../Driver/vehicles.php?error=Plate+already+exists'); exit;
}
$chk->close();

// foto 
$photo_rel = null;
if (!empty($_FILES['photo']['name'])) {
  $f = $_FILES['photo'];
  if ($f['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $mime = mime_content_type($f['tmp_name']);
    if (!isset($allowed[$mime])) {
      header('Location: ../Driver/vehicles.php?error=Invalid+image+type'); exit;
    }
    if ($f['size'] > 3*1024*1024) { 
      header('Location: ../Driver/vehicles.php?error=Image+too+large'); exit;
    }
    $dir = __DIR__ . '/../../Img/vehicles';
    if (!is_dir($dir)) @mkdir($dir,0777,true);
    $name = 'veh_'.$driver_id.'_'.time().'.'.$allowed[$mime];
    if (!move_uploaded_file($f['tmp_name'], $dir.'/'.$name)) {
      header('Location: ../Driver/vehicles.php?error=Cannot+save+image'); exit;
    }
    $photo_rel = 'Img/vehicles/'.$name;
  } elseif ($f['error'] !== UPLOAD_ERR_NO_FILE) {
    header('Location: ../Driver/vehicles.php?error=Upload+error'); exit;
  }
}

$sql = "INSERT INTO vehicles(user_id, plate, color, make, model, year, seat_capacity, photo_path)
        VALUES (?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issssiss', $driver_id, $plate, $color, $make, $model, $year, $seat_capacity, $photo_rel);

if (!$stmt->execute()) {
  $err = urlencode($stmt->error);
  $stmt->close();
  header("Location: ../Driver/vehicles.php?error=DB+$err"); exit;
}
$stmt->close();
$conn->close();

header('Location: ../Driver/vehicles.php?msg=Vehicle+added');
exit;
