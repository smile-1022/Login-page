<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError('Please provide a valid email address.');
}

try {
    $pdo = getMysqlConnection();

    $stmt = $pdo->prepare('SELECT id, first_name FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonError('No account found with that email address.', 404);
    }

    $token = bin2hex(random_bytes(24));

    $redis = getRedisConnection();
    $redis->setex('reset:' . $token, 900, json_encode([
        'user_id' => $user['id'],
        'email'   => $email,
    ]));

    jsonResponse([
        'success' => true,
        'message' => 'Reset link generated.',
        'token'   => $token,
    ]);

} catch (PDOException $e) {
    jsonError('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
