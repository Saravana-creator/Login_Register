<?php
require "../config/redis.php";
require "../config/mysql.php";
require "../config/mongo.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(["error" => "Method not allowed"]));
}

$email = $_POST['email'] ?? '';
$sessionId = $_POST['sessionId'] ?? '';

if (empty($email) || empty($sessionId)) {
    http_response_code(400);
    exit(json_encode(["error" => "Email and session required"]));
}

// Validate session in Redis
$sessionData = $redis->get("session:" . $sessionId);
if (!$sessionData) {
    http_response_code(401);
    exit(json_encode(["error" => "Invalid session"]));
}

// Get user data from MySQL
$stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(404);
    exit(json_encode(["error" => "User not found"]));
}

// Get profile data from MongoDB
// Get profile data from MongoDB with loose ID matching
try {
    // Search for both Integer and String ID
    $criteria = [
        '$or' => [
            ["userId" => (int)$user['id']],
            ["userId" => (string)$user['id']]
        ]
    ];
    $profile = $collection->findOne($criteria);
    
    // Log for debugging
    $logMsg = "GetProfile: UserID {$user['id']} -> " . ($profile ? "Found" : "Not Found");
    file_put_contents(__DIR__ . '/debug_log.txt', date('[Y-m-d H:i:s] ') . $logMsg . "\n", FILE_APPEND);
    
    if (isset($usingMongoDB) && $usingMongoDB) {
        error_log("Profile retrieved from MongoDB for userId: " . $user['id']);
    } else {
        error_log("Profile retrieved from MySQL for userId: " . $user['id']);
    }
    
} catch (Exception $e) {
    error_log("Profile retrieval error: " . $e->getMessage());
    $profile = [];
}

$response = [
    "username" => $profile['username'] ?? $user['name'],
    "email" => $user['email'], // Always use MySQL email as source of truth
    "age" => $profile['age'] ?? '',
    "dob" => $profile['dob'] ?? '',
    "contact" => $profile['contact'] ?? ''
];

echo json_encode($response);
