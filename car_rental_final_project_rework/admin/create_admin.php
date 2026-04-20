<?php
require "config/database.php";

$pass = password_hash("admin123", PASSWORD_DEFAULT);

$pdo->prepare("INSERT INTO users(username,password,role) VALUES('admin',?, 'admin')")
->execute([$pass]);

echo "Admin created";
?>
