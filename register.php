<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

require 'db_connect.php';

$role = strtolower(trim($_POST['role'] ?? ''));
$name = trim($_POST['name'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$dob = trim($_POST['dob'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$photo = (string)($_POST['photo'] ?? '');
$accountNumber = strtoupper(trim($_POST['account_number'] ?? ''));
$customerId = strtoupper(trim($_POST['customer_id'] ?? ''));
$pin = trim($_POST['pin'] ?? '');

$tableByRole = [
    'customer' => 'customers',
    'employee' => 'employees',
    'manager' => 'managers'
];

if (!isset($tableByRole[$role]) || $name === '' || $mobile === '' || $email === '' || $password === '' || $accountNumber === '') {
    echo json_encode(['success' => false, 'message' => 'Required registration data is missing.']);
    exit;
}

$table = $tableByRole[$role];
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$status = 'pending';

if ($role === 'customer') {
    if ($customerId === '') {
        $customerId = 'SKC' . random_int(10000000, 99999999);
    }

    $stmt = $conn->prepare(
        "INSERT INTO customers (name, mobile, email, account_no, password, customer_id, gender, dob, photo, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?, ?)"
    );
    $stmt->bind_param('ssssssssss', $name, $mobile, $email, $accountNumber, $hashedPassword, $customerId, $gender, $dob, $photo, $status);
} elseif ($role === 'employee') {
    $stmt = $conn->prepare(
        "INSERT INTO employees (name, mobile, email, account_no, password, pin, dob, photo, status)
         VALUES (?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, ?)"
    );
    $stmt->bind_param('sssssssss', $name, $mobile, $email, $accountNumber, $hashedPassword, $pin, $dob, $photo, $status);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO managers (name, mobile, email, account_no, password, dob, photo, status)
         VALUES (?, ?, ?, ?, ?, NULLIF(?, ''), ?, ?)"
    );
    $stmt->bind_param('ssssssss', $name, $mobile, $email, $accountNumber, $hashedPassword, $dob, $photo, $status);
}

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Registration schema is not ready. Run database.sql in phpMyAdmin first.']);
    $conn->close();
    exit;
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => ucfirst($role) . ' registration saved to MySQL.',
        'account_number' => $accountNumber,
        'customer_id' => $customerId,
        'pin' => $pin
    ]);
} else {
    $message = $conn->errno === 1062
        ? 'Mobile number, email, account number, or generated ID already exists.'
        : 'Registration failed: ' . $stmt->error;
    echo json_encode(['success' => false, 'message' => $message]);
}

$stmt->close();
$conn->close();