<?php

$servername="127.0.0.1";
$username="root";
$password="";
$dbname="guvi_intern";
$port=3307;

try {
    // 1. Connect without Database first to ensure we can create it
    // We suppress warnings just in case, though the exception is what we catch
    $conn = new mysqli($servername, $username, $password, null, $port);
} catch (mysqli_sql_exception $e) {
    die("<h1>Database Connection Failed</h1>
         <p>Could not connect to the MySQL server. Please ensure that:</p>
         <ul>
            <li>The MySQL server is running (check XAMPP Control Panel).</li>
            <li>The username and password are correct.</li>
            <li>The host is reachable (127.0.0.1) on port $port.</li>
         </ul>
         <p>Error details: " . $e->getMessage() . "</p>");
}

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// 2. Create Database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
     die("Error creating database: " . $conn->error);
}

// 3. Select the Database
$conn->select_db($dbname);

// 4. Auto-Existent Tables (Self-Healing)
// This ensures that even if you drop the DB, the next page load fixes it.
$usersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$conn->query($usersTable)) {
    die("Error creating users table: " . $conn->error);
}

$profilesTable = "CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    age VARCHAR(10),
    dob DATE,
    contact VARCHAR(20),
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
)";
if (!$conn->query($profilesTable)) {
    die("Error creating profiles table: " . $conn->error);
}

?>