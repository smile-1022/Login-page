<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$firstName = trim($input['first_name'] ?? '');
$lastName  = trim($input['last_name'] ?? '');
$email     = trim($input['email'] ?? '');
$password  = $input['password'] ?? '';

if (!$firstName || !$lastName || !$email || !$password) {
    jsonError('All fields are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError('Invalid email address.');
}

if (strlen($password) < 6) {
    jsonError('Password must be at least 6 characters.');
}

try {
    $pdo = getMysqlConnection();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonError('An account with this email already exists.');
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
    $userId = $pdo->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => 'Account created successfully! Please log in.',
        'user_id' => $userId
    ], 201);

} catch (PDOException $e) {
    jsonError('Database error: ' . $e->getMessage(), 500);
}
