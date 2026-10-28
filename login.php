<?php
session_start();

require_once __DIR__ . "/db.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter username and password.";
    } else {
        try {
            // Correct table: tbl_user
            $sql = "SELECT ID, USERNAME, FULLNAME, PASSWORD, USERTYPE
                    FROM tbl_user
                    WHERE USERNAME = ?
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row && password_verify($password, $row["PASSWORD"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $row["ID"];
                $_SESSION["username"] = $row["USERNAME"];
                $_SESSION["fullname"] = $row["FULLNAME"];
                $_SESSION["usertype"] = $row["USERTYPE"];

                $stmt->close();

                // Redirect to your main page
                header("Location: /index.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }

            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Database error. Please check the database configuration.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - 4ABALABAGAN</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg,
                    #eeeaff 0%,
                    #e4dcff 50%,
                    #ded4ff 100%);
        }

        .login-container {
            width: 100%;
            max-width: 278px;
        }

        .login-card {
            background: #fff;
            border-radius: 8px;
            padding: 25px 21px 22px;
            box-shadow: 0 8px 25px rgba(74, 48, 140, 0.16);
        }

        .login-title {
            text-align: center;
            margin: 0 0 8px;
            color: #4d2b91;
            font-size: 24px;
            font-weight: 700;
        }

        .login-subtitle {
            text-align: center;
            margin: 0 0 17px;
            color: #999;
            font-size: 11px;
        }

        .error-message {
            background: #ffe5e8;
            border: 1px solid #f5b8bf;
            color: #b42335;
            padding: 8px 10px;
            border-radius: 5px;
            font-size: 11px;
            margin-bottom: 12px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: #111;
            font-size: 11px;
            font-weight: 700;
        }

        .form-input {
            width: 100%;
            height: 32px;
            padding: 0 9px;
            border: 1px solid #dcdcdc;
            border-radius: 4px;
            background: #fff;
            color: #333;
            font-size: 10px;
            outline: none;
        }

        .form-input::placeholder {
            color: #b5b5b5;
        }

        .form-input:focus {
            border-color: #6338b5;
            box-shadow: 0 0 0 2px rgba(99, 56, 181, 0.10);
        }

        .login-button {
            width: 100%;
            height: 33px;
            border: none;
            border-radius: 4px;
            background: linear-gradient(90deg, #5b27c8, #652bc9);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 2px;
        }

        .login-button:hover {
            background: linear-gradient(90deg, #4c1eaf, #5720b3);
        }

        .register-text {
            text-align: center;
            margin-top: 14px;
            margin-bottom: 0;
            color: #555;
            font-size: 10px;
        }

        .register-link {
            color: #51309b;
            font-weight: 700;
            text-decoration: none;
        }

        .register-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">

            <h1 class="login-title">Login</h1>
            <p class="login-subtitle">4ABALABAGAN System</p>

            <?php if ($error !== ""): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">

                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="Enter username"
                        autocomplete="username"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Enter password"
                        autocomplete="current-password"
                        required>
                </div>

                <button type="submit" class="login-button">Login</button>
            </form>

            <p class="register-text">
                Don't have an account?
                <a href="register.php" class="register-link">Register here</a>
            </p>

        </div>
    </div>
</body>

</html>