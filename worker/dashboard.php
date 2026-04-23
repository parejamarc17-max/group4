<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../p_login/login.php");
    exit();
}

if ($_SESSION['role'] === 'worker') {
    header("Location: worker.php");
    exit();
}

header("Location: ../admin/dashboard.php");
exit();