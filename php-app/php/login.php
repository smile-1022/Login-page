<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) {
    jsonError('Email and password are required.');
}

try {
    $pdo = getMysqlConnection();

    $stmt = $pdo->prepare('SELECT id, first_name, last_name, email, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonError('Invalid email or password.', 401);
    }

    $token = bin2hex(random_bytes(32));

    $redis = getRedisConnection();
    $sessionData = json_encode([
        'user_id'    => $user['id'],
        'first_name' => $user['first_name'],
        'last_name'  => $user['last_name'],
        'email'      => $user['email'],
    ]);
    $redis->setex('session:' . $token, SESSION_TTL, $sessionData);

    jsonResponse([
        'success'     => true,
        'message'     => 'Login successful.',
        'token'       => $token,
        'user'        => [
            'id'         => $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
        ]
    ]);

} catch (PDOException $e) {
    jsonError('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
