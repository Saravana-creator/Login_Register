<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log output to a file we can inspect if needed, or just stderr
$debugLog = [];

try {
    require "../config/redis.php";
    require "../config/mysql.php"; // This now uses port 3307
    require "../config/mongo.php";

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

    $debugLog[] = "Received - Email: $email, NewEmail: $newEmail, Username: $username";

    if (empty($email) || empty($sessionId)) {
        throw new Exception("Email and session required");
    }

    // ... Session validation ...
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
    $debugLog[] = "User Found - ID: $userIdInt, Old Name: {$user['name']}, Old Email: {$user['email']}";

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
             $debugLog[] = "MySQL Email Update Failed: " . $conn->error;
        }
    }

    // Update username in MySQL if provided and different
    // ...
    // Update username in MySQL if provided and different
    if (!empty($username) && $username !== $user['name']) {
        $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
        $stmt->bind_param("si", $username, $userIdInt);
        if ($stmt->execute()) {
            $debugLog[] = "MySQL Name Updated Successfully " . $stmt->affected_rows . " rows";
        } else {
            $debugLog[] = "MySQL Name Update Failed: " . $conn->error;
        }
    }

    // Update profile in MongoDB
    // We use the NEW username if provided, otherwise the OLD one from MySQL
    $finalUsername = !empty($username) ? $username : $user['name'];
    
    // ...
    // ...

    $updateData = [
        '$set' => [
            "username" => $finalUsername,
            "email" => $finalEmail, // Use the NEW email
            "age" => $age,
            "dob" => $dob,
            "contact" => $contact,
            "updated_at" => date('Y-m-d H:i:s')
        ]
    ];

    $result = $collection->updateOne(
        ["userId" => $userIdInt],
        $updateData,
        ["upsert" => true] // Create if not exists
    );

    $debugLog[] = "Mongo Result: Matched=" . $result->getMatchedCount() . ", Modified=" . $result->getModifiedCount() . ", Upserted=" . $result->getUpsertedCount();

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
    echo json_encode([
        "error" => $e->getMessage(),
        "debug" => $debugLog
    ]);
}
?>
