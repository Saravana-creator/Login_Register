# User Management System

#
The User Management System is a web-based application designed to facilitate user registration, authentication, and profile administration. This system utilizes a three-tier architecture comprising a client-side interface, a server-side processing layer, and a database management layer.

## System Features
- User Registration: Allows new users to create accounts with verified credentials.
- Authentication: Secure login mechanism with session persistence.
- Profile Dashboard: Interface for users to view and modify personal information.
- Session Handling: Server-side session management using Redis.
- Data Persistence: Structured storage of user data using MySQL.

## Technology Stack
The application is built using the following technologies:
- Frontend: HTML, CSS, JavaScript, jQuery, Bootstrap
- Backend: PHP
- Database: MySQL
- Session Store: Redis

## Prerequisites
Before deploying the application, ensure the following environments are configured:
- Web Server (Apache via XAMPP or equivalent)
- PHP Runtime (Version 7.4 or later)
- MySQL Database Server
- Redis Server

## Installation Instructions

1. Deployment
Place the project directory into the web server document root (typically htdocs).

2. Database Setup
Access the MySQL database management interface.
Create a new database named guvi_intern.
Execute the setup.sql script provided in the root directory to initialize the database schema.

3. Configuration
Locate the configuration files in the php/config/ directory.
Edit mysql.php to verify the database connection parameters (host, username, password, database name).
Edit redis.php to verify the Redis server connection parameters.

4. Execution
Start the Apache, MySQL, and Redis services.
Open a web browser and navigate to the application URL (e.g., localhost/internship/login.html).

## Directory Structure
- assets/ - Static assets including stylesheets and images
- js/ - Client-side scripts for application logic and AJAX requests
- php/ - Server-side scripts for data processing and authentication
- setup.sql - Database schema definition script


