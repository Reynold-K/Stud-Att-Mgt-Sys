<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$student_id = $_SESSION['student_id'];

// Handle POST request to store the WebAuthn credential (after registration)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid credential data']);
        exit();
    }

    // Store the credential in the database
    $credential_json = json_encode($input);
    $query = "UPDATE student SET webauthn_credential = ? WHERE s_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $credential_json, $student_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Credential stored successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to store credential']);
    }
    exit();
}

// Handle GET request to provide authentication options
$query = "SELECT webauthn_credential FROM student WHERE s_id = '$student_id'";
$result = mysqli_query($conn, $query);
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

$user = mysqli_fetch_assoc($result);
if (!$user || !$user['webauthn_credential']) {
    echo json_encode(['success' => false, 'message' => 'User not registered with Windows Hello']);
    exit();
}

$credential = json_decode($user['webauthn_credential'], true);
if (!$credential || !isset($credential['rawId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid credential data stored']);
    exit();
}

$challenge = bin2hex(random_bytes(32));
$_SESSION['webauthn_challenge'] = $challenge;

$options = [
    'challenge' => $challenge,
    'rpId' => 'localhost',
    'allowCredentials' => [
        [
            'type' => 'public-key',
            'id' => $credential['rawId']
        ]
    ],
    'userVerification' => 'required',
    'timeout' => 60000
];

echo json_encode($options);
?>