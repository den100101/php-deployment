<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "4abalabagan";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

$conn->set_charset("utf8mb4");
?>
