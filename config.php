<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'relapse_db');
define('BASE_URL', 'http://192.168.100.140:8080/relapse');
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('SITE_NAME', 'RELAPSE');


function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) die('DB Error: ' . $conn->connect_error);
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn() { return isset($_SESSION['user_id']); }
function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
function requireLogin() { if (!isLoggedIn()) { header('Location: ' . BASE_URL . '/index.php'); exit; } }
function requireAdmin() { if (!isAdmin()) { header('Location: ' . BASE_URL . '/index.php'); exit; } }
function sanitize($d) { return htmlspecialchars(strip_tags(trim($d))); }

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data);
    exit;
}

function sendNotification($user_id, $title, $message, $type = 'system') {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)");
    $stmt->bind_param('isss', $user_id, $title, $message, $type);
    $stmt->execute();
}