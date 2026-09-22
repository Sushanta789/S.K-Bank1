<?php
require 'db_connect.php';

$stmt = $conn->query('SHOW TABLES');

if ($stmt) {
    echo 'MySQL connection successful. Available tables:<br>';
    while ($row = $stmt->fetch_row()) {
        echo '- ' . $row[0] . '<br>';
    }
} else {
    echo 'Database query failed: ' . $conn->error;
}

$conn->close();
