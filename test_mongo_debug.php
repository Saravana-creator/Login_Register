<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Checking MongoDB...\n";

if (!extension_loaded('mongodb')) {
    die("ERROR: MongoDB extension is NOT loaded in this PHP installation.");
}
echo "Extension Loaded.\n";

require_once 'vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "SUCCESS: Connected to MongoDB server.";
} catch (Exception $e) {
    die("ERROR: Could not connect to MongoDB server. " . $e->getMessage());
}
?>
