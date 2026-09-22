<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

require '../../../db_connect.php';

$accountNumber = trim($_POST['account_number'] ?? '');

if ($accountNumber === '') {
    echo json_encode(['success' => false, 'message' => 'Account number is required.']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM employees WHERE account_no = ?');
$stmt->bind_param('s', $accountNumber);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Employee account deleted successfully from MySQL.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No employee account found with this account number.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
