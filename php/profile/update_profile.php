<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug Log File - absolute path to be safe/simple
$logFile = __DIR__ . '/debug_log.txt';
function logToDebug($msg) {
    global $logFile;
    $date = date('[Y-m-d H:i:s] ');
    file_put_contents($logFile, $date . $msg . "\n", FILE_APPEND);
}

$debugLog = [];

try {
    require_once "../config/redis.php";
    require_once "../config/mysql.php"; 
    require_once "../config/mongo.php"; // Establishes $collection

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method not allowed");
    }

    $email = $_POST['email'] ?? '';
    $sessionId = $_POST['sessionId'] ?? '';
    $username = $_POST['username'] ?? '';
    $age = $_POST['age'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $newEmail = $_POST['new_email'] ?? '';

    $debugLog[] = "Request received for email: $email";
    logToDebug("Request: Email=$email, Session=$sessionId");

    if (empty($email) || empty($sessionId)) {
        throw new Exception("Email and session required");
    }

    // Validate session in Redis
    $sessionData = $redis->get("session:" . $sessionId);
    if (!$sessionData) {
        throw new Exception("Invalid session or session expired");
    }

    // Get user ID and details from MySQL
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        throw new Exception("User not found in MySQL");
    }
    
    $userIdInt = (int)$user['id'];
    logToDebug("User found. ID: " . $userIdInt);

    // ---------------------------------------------------------
    // SELF-HEALING: Check for String-based ID and fix if needed
    // ---------------------------------------------------------
    $existingProfileString = $collection->findOne(["userId" => (string)$userIdInt]);
    if ($existingProfileString) {
        // If we found a string-ID profile, rename it to int-ID
        // First check if an int-ID profile also exists
        $existingProfileInt = $collection->findOne(["userId" => $userIdInt]);
        
        if ($existingProfileInt) {
            // Both exist. We should probably merge or just delete the string one. 
            // We'll delete data and assume Int one is the master or new master.
            // A merge is complex, let's keep it simple: Int prevails.
            $collection->deleteOne(["_id" => $existingProfileString['_id']]);
            $debugLog[] = "Self-Healing: Removed duplicate String-ID profile.";
            logToDebug("Self-Healing: Removed duplicate String-ID profile.");
        } else {
            // Only string exists. Convert it.
            $collection->updateOne(
                ["_id" => $existingProfileString['_id']],
                ['$set' => ["userId" => $userIdInt]]
            );
            $debugLog[] = "Self-Healing: Converted String userId to Integer userId.";
            logToDebug("Self-Healing: Converted String userId to Integer userId.");
        }
    }
    // ---------------------------------------------------------


    // Handle Email Update
    $emailChanged = false;
    $finalEmail = $user['email'];
    
    if (!empty($newEmail) && $newEmail !== $user['email']) {
        // Check if new email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $newEmail, $userIdInt);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
             throw new Exception("Email '$newEmail' is already taken by another user.");
        }
        
        // Update Email in MySQL
        $updateEmailStmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $updateEmailStmt->bind_param("si", $newEmail, $userIdInt);
        if ($updateEmailStmt->execute()) {
             $emailChanged = true;
             $finalEmail = $newEmail;
             $debugLog[] = "MySQL Email Updated from {$user['email']} to $newEmail";
        } else {
             throw new Exception("MySQL Email Update Failed: " . $conn->error);
        }
    }

    // Update username in MySQL if provided and different
    if (!empty($username) && $username !== $user['name']) {
        $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
        $stmt->bind_param("si", $username, $userIdInt);
        if ($stmt->execute()) {
            $debugLog[] = "MySQL Name Updated Successfully";
        } else {
            $debugLog[] = "MySQL Name Update Failed: " . $conn->error;
        }
    }

    // Update profile in MongoDB
    $finalUsername = !empty($username) ? $username : $user['name'];
    
    $updateData = [
        '$set' => [
            "username" => $finalUsername,
            "email" => $finalEmail,
            "age" => $age,
            "dob" => $dob,
            "contact" => $contact,
            "updated_at" => date('Y-m-d H:i:s')
        ]
    ];

    logToDebug("Updating MongoDB for userId $userIdInt with data: " . json_encode($updateData));

    $result = $collection->updateOne(
        ["userId" => $userIdInt],
        $updateData,
        ["upsert" => true] // Create if not exists
    );

    $debugLog[] = "Mongo Result: Matched=" . $result->getMatchedCount() . ", Modified=" . $result->getModifiedCount() . ", Upserted=" . $result->getUpsertedCount();
    logToDebug("Mongo Update Result: M=" . $result->getMatchedCount() . " Mod=" . $result->getModifiedCount() . " U=" . $result->getUpsertedCount());

    $response = [
        "success" => true, 
        "message" => "Profile updated successfully!",
        "debug" => $debugLog
    ];
    
    if ($emailChanged) {
        $response['new_email'] = $finalEmail;
        $response['message'] .= " Email updated - please use new email for next login.";
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    logToDebug("ERROR: " . $e->getMessage());
    echo json_encode([
        "error" => $e->getMessage(),
        "debug" => $debugLog
    ]);
}
?>
