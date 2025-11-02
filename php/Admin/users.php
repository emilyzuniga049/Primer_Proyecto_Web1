<?php
require_once __DIR__ . '/../Actions/session_admin.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';
require_once __DIR__ . '/../DAO/users_list.php';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - User Management</title>
  <link rel="stylesheet" href="../../css/estilos.css?v=4">
  <link rel="stylesheet" href="../../css/estilo_admin_users.css?v=4">
  <link rel="stylesheet" href="../../css/estilo_Login_Register.css?v=4">
</head>
<body>
  <header class="admin-header">
    <div class="brand">
      <img src="../../Img/icono_carros.png" alt="Aventones" />
      <span>Aventones · Admin</span>
    </div>
    <nav class="nav-actions">
      <span class="who"><?= e($_SESSION['user_name'] ?? 'Admin') ?></span>
      <a class="btn ghost sm" href="../../php/Actions/logout.php">Logout</a>
    </nav>
  </header>
  <div class="wrap">
    <h1>Admin · User Management</h1>

    <?php if(isset($_GET['msg'])): ?>
      <div class="msg ok"><?= e($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
      <div class="msg err"><?= e($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="row">
      <!-- crear nuevo admin -->
      <div class="card">
        <h2>Create Administrator</h2>
        <form method="post" action="../DAO/create_admin.php" enctype="multipart/form-data" class="grid2">
          <input type="hidden" name="role" value="admin">
          <div>
            <label>First Name</label>
            <input type="text" name="first_name" required>
          </div>
          <div>
            <label>Last Name</label>
            <input type="text" name="last_name" required>
          </div>
          <div>
            <label>ID Number</label>
            <input type="text" name="national_id" required>
          </div>
          <div>
            <label>Birth Date</label>
            <input type="date" name="birth_date" required>
          </div>
          <div>
            <label>Email</label>
            <input type="email" name="email" required>
          </div>
          <div>
            <label>Phone</label>
            <input type="tel" name="phone" required>
          </div>
          <div>
            <label>Password</label>
            <input type="password" name="password" required>
          </div>
          <div>
            <label>Repeat Password</label>
            <input type="password" name="password2" required>
          </div>
          <div style="grid-column:1 / -1" class="custom-file-upload">
            <label for="photo" class="file-label">Select Personal Photo</label>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
            <span id="file-name">No file selected</span>
          </div>
          <div style="grid-column:1 / -1;text-align:right" >
            <button class="btn primary" type="submit">Create Admin</button>
          </div>
        </form>
      </div>

      <!-- Lista de usuarios -->
      <div class="card">
        <div class="section-head">
        <h2>Users</h2>
        </div>
        <form class="toolbar" method="get">
          <label>Role
            <select name="role">
              <option value="">All</option>
              <option value="admin"     <?= (($_GET['role'] ?? '')==='admin')?'selected':''; ?>>admin</option>
              <option value="driver"    <?= (($_GET['role'] ?? '')==='driver')?'selected':''; ?>>driver</option>
              <option value="passenger" <?= (($_GET['role'] ?? '')==='passenger')?'selected':''; ?>>passenger</option>
            </select>
          </label>
          <label>Status
            <select name="status">
              <option value="">All</option>
              <option value="active"   <?= (($_GET['status'] ?? '')==='active')?'selected':''; ?>>active</option>
              <option value="inactive" <?= (($_GET['status'] ?? '')==='inactive')?'selected':''; ?>>inactive</option>
              <option value="pending"  <?= (($_GET['status'] ?? '')==='pending')?'selected':''; ?>>pending</option>
            </select>
          </label>
          <button class="btn" type="submit">Filter</button>
          <a class="btn" href="users.php">Clear</a>
        </form>

        <table class="table">
          <thead>
            <tr>
              <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Phone</th><th>Birth</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($users as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= e($u['first_name'].' '.$u['last_name']) ?></td>
              <td><?= e($u['email']) ?></td>
              <td><?= e($u['role']) ?></td>
              <td><span class="badge <?= e($u['status']) ?>"><?= e($u['status']) ?></span></td>
              <td><?= e($u['phone']) ?></td>
              <td><?= e($u['birth_date']) ?></td>
              <td class="actions">
                <?php if ($u['status'] !== 'active'): ?>
                  <form method="post" action="../DAO/change_users_status.php">
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <input type="hidden" name="new_status" value="active">
                    <button class="btn primary" type="submit">Activate</button>
                  </form>
                <?php endif; ?>
                <?php if ($u['status'] !== 'inactive'): ?>
                  <form method="post" action="../DAO/change_users_status.php">
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <input type="hidden" name="new_status" value="inactive">
                    <button class="btn danger" type="submit">Deactivate</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

      </div>
    </div>
  </div>
</body>
</html>
