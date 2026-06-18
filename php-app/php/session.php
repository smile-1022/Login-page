<?php
require_once 'config.php';

function validateSession() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        jsonError('Unauthorized: No session token provided.', 401);
    }

    $token = substr($authHeader, 7);

    try {
        $redis = getRedisConnection();
        $sessionData = $redis->get('session:' . $token);

        if (!$sessionData) {
            jsonError('Unauthorized: Session expired or invalid.', 401);
        }

        return json_decode($sessionData, true);

    } catch (Exception $e) {
        jsonError('Session validation error: ' . $e->getMessage(), 500);
    }
}
