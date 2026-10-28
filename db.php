<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv("DB_HOST");
$username = getenv("DB_USER");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_NAME");
$port = (int) (getenv("DB_PORT") ?: 3306);

$caCert = __DIR__ . "/ca.pem";

try {
    $conn = mysqli_init();

    if (!$conn) {
        throw new RuntimeException("Could not initialize MySQLi.");
    }

    $conn->ssl_set(
        null,
        null,
        $caCert,
        null,
        null
    );

    $conn->real_connect(
        $host,
        $username,
        $password,
        $database,
        $port,
        null,
        MYSQLI_CLIENT_SSL
    );

    $conn->set_charset("utf8mb4");
} catch (Throwable $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Please try again later.");
}
