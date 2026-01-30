# User Management System

A complete user registration, login, and profile management system using HTML, CSS, JavaScript, PHP, MySQL, MongoDB, and Redis.

## Tech Stack
- **Frontend**: HTML, CSS (Bootstrap), JavaScript (jQuery)
- **Backend**: PHP
- **Databases**: MySQL (user authentication), MongoDB (profile data)
- **Session Management**: Redis
- **AJAX**: jQuery for all backend interactions

## Setup Instructions

### 1. Prerequisites
- XAMPP (Apache, MySQL, PHP)
- MongoDB
- Redis
- Composer (for MongoDB PHP driver)

### 2. Database Setup

**MySQL:**
```sql
-- Run this in phpMyAdmin or MySQL command line
CREATE DATABASE IF NOT EXISTS guvi_internship;
USE guvi_internship;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**MongoDB:**
- Start MongoDB service
- Database: `internship`
- Collection: `profiles` (auto-created)

**Redis:**
- Start Redis server on default port 6379

### 3. PHP Dependencies
```bash
cd php/config
composer require mongodb/mongodb
```

### 4. Configuration
Update database credentials in:
- `php/config/mysql.php`
- `php/config/mongo.php` 
- `php/config/redis.php`

### 5. File Structure
```
internship/
├── assets/css/styles.css
├── js/
│   ├── register.js
│   ├── login.js
│   └── profile.js
├── php/
│   ├── auth/
│   │   ├── register.php
│   │   └── login.php
│   ├── config/
│   │   ├── mysql.php
│   │   ├── mongo.php
│   │   └── redis.php
│   └── profile/
│       ├── get_profile.php
│       └── update_profile.php
├── index.html
├── register.html
├── login.html
└── profile.html
```

## Features

### Registration (register.html)
- User registration with username, email, password
- Data stored in MySQL with prepared statements
- Password hashing
- Email uniqueness validation

### Login (login.html)
- Email/password authentication
- Session creation in Redis
- LocalStorage for client-side session management
- Redirect to profile on success

### Profile (profile.html)
- Session validation via Redis
- Display user info from MySQL
- Additional profile data (age, DOB, contact) stored in MongoDB
- Profile update functionality
- Auto-redirect to login if session invalid

## Security Features
- Password hashing (PHP password_hash)
- Prepared statements (SQL injection prevention)
- Session management with Redis
- Input validation
- CSRF protection via method validation

## Usage Flow
1. **Register**: Create account → Stored in MySQL
2. **Login**: Authenticate → Create Redis session → Redirect to profile
3. **Profile**: View/update profile → Additional data in MongoDB

## API Endpoints
- `POST /php/auth/register.php` - User registration
- `POST /php/auth/login.php` - User login
- `POST /php/profile/get_profile.php` - Get profile data
- `POST /php/profile/update_profile.php` - Update profile data

All endpoints use prepared statements and session validation.