<?php
$host = 'www.skbank.com';
$dbUser = 'root';
$dbPass = 'Sushanta@1430';
$dbName = 'sk_bank';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8');
?>
