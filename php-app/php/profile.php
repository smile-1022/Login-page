<?php
require_once 'config.php';
require_once 'session.php';

$sessionUser = validateSession();
$userId = (int) $sessionUser['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $js  = 'JSON.stringify(db.' . MONGO_COL . '.findOne({user_id: ' . $userId . '}))';
    $raw = mongoQuery($js);

    if ($raw === null) {
        jsonResponse([
            'success' => true,
            'profile' => null,
            'user'    => $sessionUser
        ]);
    }

    unset($raw['_id']);

    jsonResponse([
        'success' => true,
        'profile' => $raw,
        'user'    => $sessionUser
    ]);

} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    $allowedFields = ['age', 'dob', 'contact', 'address', 'city', 'bio'];
    $setFields = ['user_id' => $userId, 'updated_at' => date('c')];

    foreach ($allowedFields as $field) {
        if (isset($input[$field]) && $input[$field] !== '') {
            if ($field === 'age') {
                $setFields[$field] = (int) $input[$field];
            } else {
                $setFields[$field] = htmlspecialchars(strip_tags($input[$field]));
            }
        }
    }

    $setJson    = json_encode($setFields, JSON_UNESCAPED_UNICODE);
    $filterJson = json_encode(['user_id' => $userId]);

    $js = 'db.' . MONGO_COL . '.updateOne(' . $filterJson . ', {$set: ' . $setJson . '}, {upsert: true}); print("ok");';

    mongoRun($js);

    jsonResponse([
        'success' => true,
        'message' => 'Profile updated successfully.',
        'profile' => $setFields
    ]);

} else {
    jsonError('Method not allowed', 405);
}
