<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}