<?php
/*
 |--------------------------------------------------------------------
 | Database Connection
 |--------------------------------------------------------------------
 | Update these 4 values to match your local MySQL / XAMPP / WAMP
 | setup. Default XAMPP values already filled in below.
 */
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "insurance_db";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error .
        "\n\nMake sure you have imported database/insurance_db.sql and updated config/db.php");
}

$conn->set_charset("utf8mb4");
