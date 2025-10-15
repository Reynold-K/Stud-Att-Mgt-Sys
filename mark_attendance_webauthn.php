<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$student_id = $_SESSION['student_id'];
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input: No data received']);
    exit();
}

if (!isset($input['response'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid WebAuthn response: response object missing']);
    exit();
}

if (!isset($input['response']['clientDataJSON'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid WebAuthn response: clientDataJSON missing']);
    exit();
}

// Validate subject_id
if (!isset($input['subject_id']) || empty($input['subject_id'])) {
    echo json_encode(['success' => false, 'message' => 'No subject selected']);
    exit();
}

// Use the provided subject_id and faculty_id
$subject_id = $input['subject_id'];
$faculty_id = isset($input['faculty_id']) && !empty($input['faculty_id']) ? $input['faculty_id'] : null;

// Decode and validate clientDataJSON
$clientDataJSON = base64_decode($input['response']['clientDataJSON']);
if ($clientDataJSON === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid WebAuthn response: Failed to decode clientDataJSON']);
    exit();
}

$clientData = json_decode($clientDataJSON, true);
if (!$clientData) {
    echo json_encode(['success' => false, 'message' => 'Invalid WebAuthn response: Failed to parse clientDataJSON']);
    exit();
}

if (!isset($clientData['challenge'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid WebAuthn response: challenge missing in clientDataJSON']);
    exit();
}

// Validate the challenge
$challenge = $_SESSION['webauthn_challenge'] ?? '';
if ($clientData['challenge'] !== $challenge) {
    echo json_encode(['success' => false, 'message' => 'Invalid challenge']);
    exit();
}

// Mark attendance
$date = date('Y-m-d');
$time = date('H:i:s');
$status = 'present';
$auth_method = 'Windows Hello';

// Use prepared statements to prevent SQL injection
$query = "INSERT INTO attendance (student_id, subject_id, faculty_id, date, attendance_time, status, auth_method, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($query);

// Bind parameters, handling NULL for faculty_id
if ($faculty_id === null) {
    $stmt->bind_param("issssss", $student_id, $subject_id, $null = null, $date, $time, $status, $auth_method);
} else {
    $stmt->bind_param("issssss", $student_id, $subject_id, $faculty_id, $date, $time, $status, $auth_method);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Attendance marked successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to mark attendance: ' . $stmt->error]);
}

unset($_SESSION['webauthn_challenge']);
?>