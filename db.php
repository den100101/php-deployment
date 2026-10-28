
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv("DB_HOST");
$username = getenv("DB_USER");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_NAME");
$port = (int) (getenv("DB_PORT") ?: 3306);

try {
    $conn = new mysqli(
        $host,
        $username,
        $password,
        $database,
        $port
    );

    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Please try again later.");
}
?>
