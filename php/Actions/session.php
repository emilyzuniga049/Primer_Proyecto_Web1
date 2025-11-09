<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
  // No autenticado
  header('Location: /Index.html?error=Please+login');
  exit;
}
