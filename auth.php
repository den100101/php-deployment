<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



if (empty($_SESSION["user_id"])) {
    header("Location: /4abalabagan/login.php");
    exit;
}


$username = $_SESSION["username"] ?? "";
$fullname = $_SESSION["fullname"] ?? "";
$usertype = strtolower(trim($_SESSION["usertype"] ?? ""));

/* =========================================
   ROLE PERMISSION
========================================= */

function requireRole(array $allowedRoles): void
{
    $currentRole = strtolower(
        trim($_SESSION["usertype"] ?? "")
    );

    /* Normalize role names */
    $roleAliases = [
        "administrator" => "admin",
        "admin"         => "admin",
        "dean"          => "dean",
        "deans"         => "dean",
        "teacher"       => "instructor",
        "professor"     => "instructor",
        "instructor"    => "instructor",
        "student"       => "student",
        "registrar"     => "registrar"
    ];

    $currentRole = $roleAliases[$currentRole] ?? $currentRole;

    $allowedRoles = array_map(
        function ($role) use ($roleAliases) {
            $role = strtolower(trim($role));
            return $roleAliases[$role] ?? $role;
        },
        $allowedRoles
    );

    /* Allow only the specified roles */
    if (!in_array($currentRole, $allowedRoles, true)) {
        http_response_code(403);

        exit(
            '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport"
                      content="width=device-width, initial-scale=1">
                <title>403 - Access Denied</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background: #eef3f8;
                        text-align: center;
                        padding: 80px 20px;
                    }
                    .box {
                        max-width: 450px;
                        margin: auto;
                        padding: 30px;
                        background: white;
                        border-radius: 12px;
                        box-shadow: 0 4px 15px #0001;
                    }
                    a {
                        display: inline-block;
                        margin-top: 15px;
                        padding: 10px 18px;
                        background: #1479e9;
                        color: white;
                        text-decoration: none;
                        border-radius: 6px;
                    }
                </style>
            </head>
            <body>
                <div class="box">
                    <h2>403 - Access Denied</h2>
                    <p>Wala kang permission na buksan ang page na ito.</p>
                    <a href="/4abalabagan/index.php">Back to Home</a>
                </div>
            </body>
            </html>'
        );
    }
}
?>
```