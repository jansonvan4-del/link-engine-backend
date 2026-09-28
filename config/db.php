<?php
$host = "sql104.infinityfree.com"; // Sesuaikan dengan "MySQL Hostname" dari cPanel Anda
$user = "if0_42933604";            // Sesuaikan dengan "MySQL Username" dari cPanel Anda
$pass = "qAW5Iz2T82G";    // Masukkan password hosting/cPanel InfinityFree Anda
$db   = "if0_42933604_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
