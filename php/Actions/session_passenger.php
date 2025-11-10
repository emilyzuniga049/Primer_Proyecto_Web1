<?php
session_start();
if (empty($_SESSION['logged_in']) || ($_SESSION['user_role'] ?? '') !== 'passenger') {
  header('Location: /Index.html?error=Please login as passenger.');
  exit;
}
