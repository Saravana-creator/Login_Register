<?php
// Initialize both MySQL and MongoDB databases
require_once "vendor/autoload.php"; // Root vendor folder
require_once "php/config/mysql.php"; // This now points to guvi_intern

echo "<h1>Initializing Databases...</h1>";

// 1. Setup MySQL Database and Tables
try {
    // We already connected to 'guvi_intern' in mysql.php, 
    // IF it doesn't exist, the connection might fail or select default.
    // Let's connect without DB first to ensure creation.
    $connRaw = new mysqli("127.0.0.1", "root", "");
    
    $sql = "CREATE DATABASE IF NOT EXISTS guvi_intern";
    if ($connRaw->query($sql) === TRUE) {
        echo "<p style='color:green'>✔ MySQL Database 'guvi_intern' created or checks out.</p>";
    } else {
        echo "<p style='color:red'>✘ Error creating MySQL DB: " . $connRaw->error . "</p>";
    }
    $connRaw->close();

    // Now use the connection from mysql.php (re-establish or use existing)
    // We need to verify tables exist
    $conn->select_db("guvi_intern");
    
    $usersTable = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($usersTable) === TRUE) {
        echo "<p style='color:green'>✔ MySQL Table 'users' created.</p>";
    } else {
        echo "<p style='color:red'>✘ Error creating users table: " . $conn->error . "</p>";
    }

    $profilesTable = "CREATE TABLE IF NOT EXISTS profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        userId INT NOT NULL,
        age VARCHAR(10),
        dob DATE,
        contact VARCHAR(20),
        FOREIGN KEY (userId) REFERENCES users(id)
    )";

    if ($conn->query($profilesTable) === TRUE) {
        echo "<p style='color:green'>✔ MySQL Table 'profiles' created.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>MySQL Error: " . $e->getMessage() . "</p>";
}

// 2. Setup MongoDB Database (Force Creation)
try {
    // This will force the connection to open
    require_once "php/config/mongo.php";
    
    if (isset($usingMongoDB) && $usingMongoDB) {
        // MongoDB creates databases lazily. We must insert data to "create" it.
        // We will check if it's empty. if so, we insert a placeholder or config doc.
        
        $count = $collection->countDocuments([]);
        if ($count == 0) {
            $collection->insertOne([
                "system_info" => "init", 
                "created_at" => date('Y-m-d H:i:s'),
                "message" => "Database initialized. This document makes the DB visible."
            ]);
            echo "<p style='color:green'>✔ MongoDB Database 'guvi_intern' initialized (dummy document created).</p>";
        } else {
            echo "<p style='color:green'>✔ MongoDB Database 'guvi_intern' already exists and has data.</p>";
        }
    } else {
        echo "<p style='color:orange'>⚠ MongoDB extension not loaded or connection failed. Using MySQL fallback.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>MongoDB Error: " . $e->getMessage() . "</p>";
}

echo "<hr><p><strong>Setup Complete.</strong> You can now <a href='login.html'>Login</a> or <a href='register.html'>Register</a>.</p>";
?>
