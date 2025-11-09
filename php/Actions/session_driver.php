<?php
session_start();
if (empty($_SESSION['logged_in']) || ($_SESSION['user_role'] ?? '') !== 'driver') {
  header('Location: /Index.html?error=Please login as driver');
  exit;
}
