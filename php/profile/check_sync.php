<?php
// Debug script to check data consistency
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../config/mysql.php";
require "../config/mongo.php";

echo "=== Database Consistency Check ===\n\n";

// 1. Get all users from MySQL
$result = $conn->query("SELECT id, name, email FROM users");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "MySQL User: ID [{$row['id']}] | Name: [{$row['name']}] | Email: [{$row['email']}]\n";
        
        // 2. Check corresponding Mongo Entry
        try {
            $mongoUser = $collection->findOne(['userId' => (int)$row['id']]);
            if ($mongoUser) {
                echo "  -> MongoDB Profile Found:\n";
                // Handle BSONDocument or array
                $mUser = (array)$mongoUser;
                echo "     Name in Mongo: [" . ($mUser['username'] ?? 'N/A') . "]\n";
                echo "     ID in Mongo: [" . ($mUser['userId'] ?? 'N/A') . "] (Type: " . gettype($mUser['userId']) . ")\n";
                
                if (($mUser['username'] ?? '') !== $row['name']) {
                    echo "     [WARNING] Name mismatch! MySQL has '{$row['name']}', Mongo has '{$mUser['username']}'\n";
                } else {
                     echo "     [OK] Names match.\n";
                }
            } else {
                echo "  -> [WARNING] No MongoDB profile found for userId " . $row['id'] . "\n";
                
                // Check if string ID exists by mistake
                $mongoUserString = $collection->findOne(['userId' => (string)$row['id']]);
                if ($mongoUserString) {
                     echo "     [CRITICAL] Found profile with STRING ID '{$row['id']}'. This is a type mismatch bug.\n";
                }
            }
        } catch (Exception $e) {
            echo "  -> Error querying MongoDB: " . $e->getMessage() . "\n";
        }
        echo "---------------------------------------------------\n";
    }
} else {
    echo "No users found in MySQL.\n";
}
?>
