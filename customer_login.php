<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

require 'db_connect.php';

$customerId = strtoupper(trim($_POST['customer_id'] ?? ''));
$accountNumber = strtoupper(trim($_POST['account_number'] ?? ''));

if ($customerId === '' || $accountNumber === '') {
    echo json_encode(['success' => false, 'message' => 'Customer ID and account number are required.']);
    exit;
}

$stmt = $conn->prepare(
    'SELECT name, customer_id, account_no, gender, email, mobile, photo, balance, manager_approval, employee_approval
     FROM customers WHERE customer_id = ? AND account_no = ? LIMIT 1'
);
$stmt->bind_param('ss', $customerId, $accountNumber);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Login query failed.']);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    echo json_encode(['success' => false, 'message' => 'Invalid Customer ID or Account Number.']);
} elseif (!(int)$customer['manager_approval']) {
    echo json_encode(['success' => false, 'message' => 'Account is waiting for Manager approval.']);
} elseif (!(int)$customer['employee_approval']) {
    echo json_encode(['success' => false, 'message' => 'Account is waiting for Employee final approval.']);
} else {
    echo json_encode(['success' => true, 'customer' => [
        'fullName' => $customer['name'],
        'customerId' => $customer['customer_id'],
        'accountNumber' => $customer['account_no'],
        'gender' => $customer['gender'],
        'email' => $customer['email'],
        'mobile' => $customer['mobile'],
        'photo' => $customer['photo'],
        'balance' => $customer['balance'],
        'managerApproval' => true,
        'employeeApproval' => true
    ]]);
}

$stmt->close();
$conn->close();