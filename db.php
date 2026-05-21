<?php
/**
 * Database connection handler for Donut Shop Management System.
 * Uses mysqli with environment variables for containerized deployments.
 */

declare(strict_types=1);

$host = getenv('HOST') ?: 'mysql';
$user = getenv('USER') ?: 'donut_user';
$password = getenv('PASSWORD') ?: 'donut_pass';
$database = getenv('DATABASE') ?: 'donutdb';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
}

$conn->set_charset('utf8mb4');
