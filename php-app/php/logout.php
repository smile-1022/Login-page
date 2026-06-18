<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
    $token = substr($authHeader, 7);
    try {
        $redis = getRedisConnection();
        $redis->del(['session:' . $token]);
    } catch (Exception $e) {
    }
}

jsonResponse(['success' => true, 'message' => 'Logged out successfully.']);
