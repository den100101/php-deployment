<?php
session_start();
require_once __DIR__ . "/db.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";
$error = "";
$fullname = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    try {
        if ($fullname === "" || $username === "" || $password === "" || $confirm_password === "") {
            throw new RuntimeException("Please fill in all fields.");
        }
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            throw new RuntimeException("Username may contain letters, numbers, underscores, and hyphens only.");
        }
        if ($password !== $confirm_password) {
            throw new RuntimeException("Passwords do not match.");
        }
        if (strlen($password) < 6) {
            throw new RuntimeException("Password must be at least 6 characters.");
        }

        if (!isset($_FILES["profile_image"]) || !is_array($_FILES["profile_image"])) {
            throw new RuntimeException("Please choose a profile image.");
        }
        $upload = $_FILES["profile_image"];
        if ($upload["error"] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => "The image exceeds the PHP upload size limit. Check upload_max_filesize in php.ini.",
                UPLOAD_ERR_FORM_SIZE => "The image is too large.",
                UPLOAD_ERR_PARTIAL => "The image upload was incomplete. Please try again.",
                UPLOAD_ERR_NO_FILE => "Please choose a profile image.",
                UPLOAD_ERR_NO_TMP_DIR => "PHP temporary upload folder is missing.",
                UPLOAD_ERR_CANT_WRITE => "PHP could not write the uploaded image.",
                UPLOAD_ERR_EXTENSION => "A PHP extension stopped the image upload."
            ];
            throw new RuntimeException($uploadErrors[$upload["error"]] ?? "Image upload failed.");
        }
        if ($upload["size"] <= 0 || $upload["size"] > 5 * 1024 * 1024) {
            throw new RuntimeException("Image must be larger than 0 bytes and no more than 5 MB.");
        }
        if (!is_uploaded_file($upload["tmp_name"])) {
            throw new RuntimeException("The uploaded file could not be verified. Please try again.");
        }

        // Check the actual image type rather than trusting the filename.
        $imageInfo = @getimagesize($upload["tmp_name"]);
        if ($imageInfo === false) {
            throw new RuntimeException("The selected file is not a valid image.");
        }

        $mime = $imageInfo["mime"] ?? "";
        $allowedMimes = ["image/jpeg", "image/png", "image/webp"];
        if (!in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException("Please upload a JPG, PNG, or WEBP image.");
        }

        // Use the project's existing connection variable ($conn).
        $check = $conn->prepare("SELECT ID FROM tbl_user WHERE USERNAME = ? LIMIT 1");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();
        $usernameExists = $check->num_rows > 0;
        $check->close();

        if ($usernameExists) {
            throw new RuntimeException("That username already exists. Please choose another username.");
        }

        $folder = __DIR__ . "/images/users/";
        if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
            throw new RuntimeException("Could not create the images/users folder.");
        }
        if (!is_writable($folder)) {
            throw new RuntimeException("The images/users folder is not writable. Check the folder permissions.");
        }

        $filename = $username . ".JPG";
        $absolutePath = $folder . $filename;
        $relativePath = "images/users/" . $filename;

        // If an old image file exists without a matching database account,
        // replace that orphaned file. The database username check above prevents
        // replacing the image of an existing account.
        $savedImage = false;

        if ($mime === "image/jpeg") {
            // JPEG is already the required format; no GD extension is needed.
            if (!move_uploaded_file($upload["tmp_name"], $absolutePath)) {
                throw new RuntimeException("Could not save the image. Check that images/users is writable.");
            }
            $savedImage = true;
        } else {
            // PNG and WEBP need GD to be converted into a real JPG.
            if (!extension_loaded("gd") || !function_exists("imagecreatetruecolor") || !function_exists("imagejpeg")) {
                throw new RuntimeException("PHP GD is disabled. You can upload a JPG now, or enable extension=gd in php.ini to convert PNG/WEBP to JPG.");
            }

            if ($mime === "image/png") {
                $sourceImage = @imagecreatefrompng($upload["tmp_name"]);
            } else {
                $sourceImage = function_exists("imagecreatefromwebp")
                    ? @imagecreatefromwebp($upload["tmp_name"])
                    : false;
            }

            if (!$sourceImage) {
                throw new RuntimeException("Could not read this image. Try another JPG, PNG, or WEBP file.");
            }

            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $jpgImage = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($jpgImage, 255, 255, 255);
            imagefill($jpgImage, 0, 0, $white);
            imagecopy($jpgImage, $sourceImage, 0, 0, 0, 0, $width, $height);
            imagedestroy($sourceImage);

            if (!imagejpeg($jpgImage, $absolutePath, 90)) {
                imagedestroy($jpgImage);
                throw new RuntimeException("Could not save the converted JPG image.");
            }
            imagedestroy($jpgImage);
            $savedImage = true;
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                throw new RuntimeException("Could not secure the password.");
            }

            // Public registration creates Student accounts only.
            $usertype = "Student";
            $sql = "INSERT INTO tbl_user (FULLNAME, USERNAME, PASSWORD, USERTYPE, IMAGE)
                    VALUES (?, ?, ?, ?, ?)";
            $insert = $conn->prepare($sql);
            $insert->bind_param("sssss", $fullname, $username, $hashedPassword, $usertype, $relativePath);
            $insert->execute();
            $insert->close();

            $message = "Registration successful! You can now log in.";
            $fullname = "";
            $username = "";
        } catch (Throwable $e) {
            if ($savedImage && is_file($absolutePath)) {
                @unlink($absolutePath);
            }
            throw $e;
        }
    } catch (Throwable $e) {
        error_log("Registration error: " . $e->getMessage());
        // Show a useful validation/database error during local development.
        $error = $e instanceof RuntimeException
            ? $e->getMessage()
            : "Registration failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - 4ABALABAGAN</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; justify-content: center;
            align-items: center; padding: 20px 0; font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #eee7ff, #dcd0ff);
        }
        .register-container {
            width: 320px; max-width: calc(100% - 30px); padding: 25px 21px 22px;
            background: #fff; border-radius: 9px;
            box-shadow: 0 8px 25px rgba(60, 35, 120, 0.12);
        }
        h2 { margin: 0 0 8px; text-align: center; color: #49239b; font-size: 22px; }
        .subtitle { margin: 0 0 20px; text-align: center; color: #999; font-size: 10px; }
        label { display: block; margin: 12px 0 5px; font-size: 10px; font-weight: bold; color: #111; }
        input {
            display: block; width: 100%; min-height: 29px; padding: 7px 9px;
            border: 1px solid #ddd; border-radius: 4px; font-size: 10px; outline: none;
        }
        input:focus { border-color: #6427d5; }
        input::placeholder { color: #aaa; }
        input[type="file"] { height: auto; padding: 7px; background: #fff; }
        .image-help { margin: 5px 0 0; font-size: 9px; line-height: 1.5; color: #888; }
        button {
            width: 100%; min-height: 29px; margin-top: 18px; padding: 8px;
            border: none; border-radius: 4px; background: #6025d5; color: white;
            font-size: 11px; font-weight: bold; cursor: pointer;
        }
        button:hover { background: #4c19b5; }
        .login-link { margin-top: 16px; text-align: center; font-size: 9px; color: #333; }
        .login-link a { color: #5120b5; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
        .message, .error {
            padding: 9px; margin-bottom: 12px; border-radius: 4px;
            font-size: 11px; overflow-wrap: anywhere;
        }
        .message { background: #e5f7e9; color: #176b2c; }
        .error { background: #fde8e8; color: #a51f1f; }
    </style>
</head>
<body>
<div class="register-container">
    <h2>Register</h2>
    <p class="subtitle">4ABALABAGAN System</p>

    <?php if ($message !== ""): ?>
        <div class="message"><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" placeholder="Enter your full name"
               value="<?= htmlspecialchars($fullname, ENT_QUOTES, "UTF-8") ?>" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter username"
               value="<?= htmlspecialchars($username, ENT_QUOTES, "UTF-8") ?>"
               pattern="[a-zA-Z0-9_-]+" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password"
               minlength="6" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password"
               placeholder="Confirm password" minlength="6" required>

        <label for="profile_image">Profile Image</label>
        <input type="file" id="profile_image" name="profile_image"
               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
        <p class="image-help">JPG, PNG, or WEBP. Maximum file size: 5 MB. The image will be saved as username.JPG.</p>

        <button type="submit">Register</button>
    </form>

    <div class="login-link">Already have an account? <a href="login.php">Login here</a></div>
</div>
</body>
</html>
