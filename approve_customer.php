<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

require 'db_connect.php';

$accountNumber = strtoupper(trim($_POST['account_number'] ?? ''));
$approval = strtolower(trim($_POST['approval'] ?? ''));
$column = $approval === 'manager' ? 'manager_approval' : ($approval === 'employee' ? 'employee_approval' : '');

if ($accountNumber === '' || $column === '') {
    echo json_encode(['success' => false, 'message' => 'Valid account number and approval type are required.']);
    exit;
}

$query = "UPDATE customers SET {$column} = 1, status = CASE WHEN manager_approval = 1 AND employee_approval = 1 THEN 'approved' ELSE status END WHERE account_no = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $accountNumber);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Customer approval saved to MySQL.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Customer account was not found or approval was already saved.']);
}

$stmt->close();
$conn->close();