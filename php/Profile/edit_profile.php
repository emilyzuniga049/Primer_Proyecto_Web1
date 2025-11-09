<?php
require_once __DIR__ . '/../Actions/session.php'; 
require_once __DIR__ . '/../Conexion/db_conexion.php';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
  header('Location: /Index.html?error=Please login');
  exit;
}

$sql = "SELECT id, role, status, first_name, last_name, national_id, birth_date, email, phone, photo_path
        FROM users WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$DEFAULT_AVATAR = '../../Img/user_icon.png';
$PHOTO_REL = trim($user['photo_path'] ?? '');
if (!empty($PHOTO_REL)) {
  $avatarUrl = '../../' . ltrim($PHOTO_REL, '/');
} else {
  $avatarUrl = $DEFAULT_AVATAR;
}
$stmt->close();
$conn->close();

if (!$user) {
  header('Location: /Index.html?error=User not found');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Aventones · Edit Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- estilos -->
  <link rel="stylesheet" href="../../css/estilos.css">
  <link rel="stylesheet" href="../../css/estilo_nav_logo.css">
  <link rel="stylesheet" href="../../css/estilos_NewEditRide.css?v=4">
  <script src="../../js/edit_profile_user.js"></script>
</head>
<body>
  <header class="main-header">
    <div class="logo-container">
      <img src="../../Img/icono_carros.png" class="logo" alt="Logo">
      <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
      <?php if(($_SESSION['user_role'] ?? '') === 'driver'): ?>
        <a href="../Driver/my_rides.php">My Rides</a>
        <a href="../Driver/new_ride.php">New Ride</a>
        <a href="../Driver/vehicles.php">Vehicles</a>
        <a href="../Driver/bookings.php">Bookings</a>
      <?php else: ?>
        <a href="../Search_Rides/index.php">Search Rides</a>
        <a href="../Bookings/index.php">My Bookings</a>
      <?php endif; ?>
      <div class="user-menu" style="margin-left:auto">
        <img src="../../Img/user_icon.png" class="user-icon" alt="User Icon">
        <div class="user-dropdown">
          <a href="../Actions/logout.php">Logout</a>
          <a class="active" href="./edit_profile.php">Profile</a>
        </div>
      </div>
    </nav>
  </header>

  <main class="form-container">
    <div class="profile-hero">
    <div class="profile-title">
        <h2>Edit Profile</h2>
        <small class="muted">Update your personal information</small>
    </div>
    <div class="avatar-wrap">
        <img class="avatar" src="<?= e($avatarUrl) ?>" alt="User photo">
        <label for="photo" class="btn sm ghost">Change</label>
    </div>
    </div>
    <?php if(isset($_GET['error'])): ?>
      <div class="msg err"><?= e($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['msg'])): ?>
      <div class="msg ok"><?= e($_GET['msg']) ?></div>
    <?php endif; ?>

    <form action="../DAO/update_profile.php" method="post" enctype="multipart/form-data" class="grid2" autocomplete="off">
      <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
      <input type="hidden" name="current_email" value="<?= e($user['email']) ?>">
      <input type="hidden" name="current_photo" value="<?= e($user['photo_path'] ?? '') ?>">

       <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">

      <div>
        <label>First Name</label>
        <input type="text" name="first_name" required value="<?= e($user['first_name']) ?>">
      </div>

      <div>
        <label>Last Name</label>
        <input type="text" name="last_name" required value="<?= e($user['last_name']) ?>">
      </div>

      <div>
        <label>ID Number</label>
        <input type="text" name="national_id" required value="<?= e($user['national_id']) ?>">
      </div>

      <div>
        <label>Birth Date</label>
        <input type="date" name="birth_date" required value="<?= e($user['birth_date']) ?>">
      </div>

      <div>
        <label>Email</label>
        <input type="email" name="email" required value="<?= e($user['email']) ?>">
      </div>

      <div>
        <label>Phone</label>
        <input type="tel" name="phone" required value="<?= e($user['phone']) ?>">
      </div>

      <div>
        <label>New Password (optional)</label>
        <input type="password" name="password" placeholder="Leave blank to keep current">
      </div>

      <div>
        <label>Repeat New Password</label>
        <input type="password" name="password2" placeholder="Repeat new password">
      </div>

      <div style="grid-column:1 / -1; text-align:right">
        <a class="btn" href="<?php
          echo (($_SESSION['user_role'] ?? '') === 'driver')
               ? '../Driver/my_rides.php'
               : '../Search_Rides/index.php';
        ?>">Cancel</a>
        <button class="btn primary" type="submit">Save Changes</button>
      </div>
    </form>
  </main>
</body>
</html>
