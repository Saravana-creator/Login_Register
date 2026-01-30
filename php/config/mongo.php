<?php
// MongoDB configuration - prioritize MongoDB over MySQL
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    // Check if MongoDB extension is loaded
    if (!extension_loaded('mongodb')) {
        throw new Exception('MongoDB extension not loaded');
    }
    
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->selectDatabase('guvi_intern'); 
    $collection = $db->selectCollection('profiles');
    
    // Test connection by pinging
    $client->selectDatabase('admin')->command(['ping' => 1]);
    
    // Success - using MongoDB
    $usingMongoDB = true;
    
} catch (Exception $e) {
    // Strict Requirement: MongoDB must be available
    http_response_code(500);
    die(json_encode(["error" => "MongoDB Connection Failed: " . $e->getMessage()]));
}
?>