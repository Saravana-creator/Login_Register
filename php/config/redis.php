<?php
// Redis configuration - Strict requirement
// Do NOT use PHP native sessions

try {
    if (!class_exists('Redis')) {
        throw new Exception("Redis extension not loaded");
    }

    $redis = new Redis();
    // Connect to Redis (default port 6379)
    if (!$redis->connect("127.0.0.1", 6379)) {
        throw new Exception("Could not connect to Redis server");
    }
    
    // Optional: Authenticate if needed
    // $redis->auth('password');
    
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => "Redis Error: " . $e->getMessage()]));
}
?>
