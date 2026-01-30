<?php
require_once "../config/mysql.php";
require_once "../config/redis.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(["status" => "error", "message" => "Method not allowed"]));
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    exit(json_encode(["status" => "error", "message" => "All fields are required"]));
}

// Get user from database
$stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    exit(json_encode(["status" => "error", "message" => "Invalid credentials"]));
}

// Create session in Redis
$sessionId = uniqid();
$sessionData = json_encode([
    "userId" => $user['id'],
    "username" => $user['name'],
    "email" => $email
]);

$redis->setex("session:" . $sessionId, 3600, $sessionData); // 1 hour expiry

echo json_encode([
    "status" => "success", 
    "message" => "Login successful",
    "sessionId" => $sessionId
]);
?>