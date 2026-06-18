<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_PORT', '3306');
define('MYSQL_DB',   'guvi_app');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');

define('MONGO_HOST',    '127.0.0.1');
define('MONGO_PORT',    27017);
define('MONGO_DB',      'guvi_app');
define('MONGO_BINARY',  '/nix/store/c3l5axlpfsvn0cj281si9dqvncai0r3n-mongodb-7.0.16/bin/mongo');
define('MONGO_COL',     'profiles');

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('SESSION_TTL', 86400);

function getMysqlConnection() {
    $dsn = 'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DB . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, MYSQL_USER, MYSQL_PASS, $options);
}

function mongoExec(string $js): string {
    $host = MONGO_HOST . ':' . MONGO_PORT;
    $db   = MONGO_DB;
    $bin  = MONGO_BINARY;

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $proc = proc_open(
        "$bin --quiet {$host}/{$db}",
        $descriptors,
        $pipes
    );
    if (!is_resource($proc)) {
        return '';
    }
    fwrite($pipes[0], $js);
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);
    return trim($out ?? '');
}

function mongoQuery(string $jsExpression): ?array {
    $out = mongoExec($jsExpression);
    if ($out === '' || strtolower($out) === 'null') {
        return null;
    }
    return json_decode($out, true);
}

function mongoRun(string $jsExpression): bool {
    mongoExec($jsExpression);
    return true;
}

function getRedisConnection() {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host'   => REDIS_HOST,
        'port'   => REDIS_PORT,
    ]);
    return $redis;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}
