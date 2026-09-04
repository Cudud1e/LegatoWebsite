<?php

$host = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "legato_db";

$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>