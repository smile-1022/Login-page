<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $token = trim($_GET['token'] ?? '');

    if (!$token) {
        jsonResponse(['valid' => false]);
    }

    try {
        $redis = getRedisConnection();
        $data  = $redis->get('reset:' . $token);
        jsonResponse(['valid' => $data !== null && $data !== false]);
    } catch (Exception $e) {
        jsonResponse(['valid' => false]);
    }
}

if ($method === 'POST') {
    $input       = json_decode(file_get_contents('php://input'), true);
    $token       = trim($input['token'] ?? '');
    $newPassword = $input['new_password'] ?? '';

    if (!$token) {
        jsonError('Reset token is missing.');
    }

    if (strlen($newPassword) < 6) {
        jsonError('Password must be at least 6 characters.');
    }

    try {
        $redis     = getRedisConnection();
        $rawData   = $redis->get('reset:' . $token);

        if (!$rawData) {
            jsonError('This reset link has expired or is invalid. Please request a new one.', 410);
        }

        $resetData = json_decode($rawData, true);
        $userId    = (int) $resetData['user_id'];

        $pdo  = getMysqlConnection();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $userId]);

        $redis->del(['reset:' . $token]);

        jsonResponse([
            'success' => true,
            'message' => 'Password reset successfully.',
        ]);

    } catch (PDOException $e) {
        jsonError('Database error: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        jsonError('Server error: ' . $e->getMessage(), 500);
    }
}

jsonError('Method not allowed', 405);
