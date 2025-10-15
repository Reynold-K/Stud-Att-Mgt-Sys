<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$student_id = $_SESSION['student_id'];

// Generate a challenge (a random string)
$challenge = bin2hex(random_bytes(32));
$_SESSION['webauthn_challenge'] = $challenge;

// Prepare the PublicKeyCredentialCreationOptions
$options = [
    'challenge' => $challenge,
    'rp' => [
        'name' => 'MMU Biometric Attendance System',
        'id' => 'localhost' // Update this to your domain in production
    ],
    'user' => [
        'id' => base64_encode($student_id),
        'name' => $_SESSION['student_name'],
        'displayName' => $_SESSION['student_name']
    ],
    'pubKeyCredParams' => [
        ['type' => 'public-key', 'alg' => -7], // ES256
        ['type' => 'public-key', 'alg' => -257] // RS256
    ],
    'authenticatorSelection' => [
        'authenticatorAttachment' => 'platform', // Use Windows Hello (platform authenticator)
        'requireResidentKey' => false,
        'userVerification' => 'required'
    ],
    'timeout' => 60000,
    'attestation' => 'none'
];

echo json_encode($options);
?>