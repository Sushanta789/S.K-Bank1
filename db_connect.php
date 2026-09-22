<?php
// Local XAMPP database host. Use the server IP or DNS name only when MySQL is remote.
$host = '127.0.0.1';
$dbUser = 'root';
$dbPass = 'Sushanta@1430';
$dbName = 'sk_bank';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $dbUser, $dbPass);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS `" . $conn->real_escape_string($dbName) . "`");
if (!$conn->select_db($dbName)) {
    die('Database selection failed: ' . $conn->error);
}

$conn->set_charset('utf8');
?>
