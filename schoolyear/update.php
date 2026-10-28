    <?php

    require_once "../db.php";

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: index.php");
        exit;
    }

    $id = intval($_POST["id"] ?? 0);

    $schoolyear = trim($_POST["schoolyear"] ?? "");
    $description = trim($_POST["description"] ?? "");

    /* VALIDATION */
    if ($id <= 0) {
        die("Invalid School Year ID.");
    }

    if ($schoolyear === "" || $description === "") {
        die("School Year and Description are required.");
    }

    /* UPDATE SCHOOL YEAR */
    $stmt = $conn->prepare("
        UPDATE tbl_schoolyear
        SET SCHOOLYEAR = ?, DESCRIPTION = ?
        WHERE ID = ?
    ");

    if (!$stmt) {
        die("Update preparation failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssi",
        $schoolyear,
        $description,
        $id
    );

    if ($stmt->execute()) {

        $stmt->close();

        header("Location: index.php");
        exit;

    } else {

        if ($stmt->errno == 1062) {
            die("This school year already exists.");
        }

        die("Update failed: " . $stmt->error);
    }

    ?>