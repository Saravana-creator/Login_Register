-- Create database if not exists
CREATE DATABASE IF NOT EXISTS guvi_intern;
USE guvi_intern;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create profiles table for additional user data
CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    age VARCHAR(10),
    dob DATE,
    contact VARCHAR(20),
    FOREIGN KEY (userId) REFERENCES users(id)
);