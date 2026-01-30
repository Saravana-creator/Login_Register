<?php
require_once "../config/mysql.php";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('<div class="alert alert-danger">Method not allowed</div>');
}

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    exit('<div class="alert alert-danger">All fields are required</div>');
}

// Check if user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    exit('<div class="alert alert-danger">Email already registered</div>');
}

// Hash password and insert user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) {
    // Get the new user ID
    $userId = $conn->insert_id;
    
    // Create initial profile in MongoDB
    require_once "../config/mongo.php";
    if (isset($usingMongoDB) && $usingMongoDB) {
        try {
            $insertResult = $collection->insertOne([
                "userId" => (int)$userId,
                "username" => $username,
                "email" => $email,
                "age" => "",
                "dob" => "",
                "contact" => "",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ]);
            
            if ($insertResult->getInsertedCount() === 0) {
                 throw new Exception("MongoDB Insert Failed");
            }
            
            // Log to debug file
            $logFile = __DIR__ . '/../../debug_mongo_update.log';
            $msg = "New User Registered: ID=$userId, Email=$email";
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);

            error_log("Result: Initial MongoDB profile created for user $userId");
        } catch (Exception $e) {
            error_log("Error creating MongoDB profile: " . $e->getMessage());
            // Rollback MySQL user creation if Mongo fails (Optional but helps consistency)
            $conn->query("DELETE FROM users WHERE id = $userId");
            exit('<div class="alert alert-danger">Registration Failed: Could not write to MongoDB (' . $e->getMessage() . ')</div>');
        }
    } else {
        // Warning: MongoDB extension not active
        $conn->query("DELETE FROM users WHERE id = $userId");
        exit('<div class="alert alert-danger">Registration Failed: MongoDB extension not available or connection failed.</div>');
    }

    echo '<div class="alert alert-success">Registration successful! <a href="login.html">Login here</a></div>';
} else {
    echo '<div class="alert alert-danger">Registration failed. Please try again.</div>';
}
?>
