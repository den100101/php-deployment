<?php

require_once "../db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = intval($_POST["id"] ?? 0);

$course = trim($_POST["course"] ?? "");
$description = trim($_POST["description"] ?? "");


/* =========================
   VALIDATION
========================= */

if ($course === "" || $description === "") {
    die("Course and Description are required.");
}


/* =========================
   UPDATE EXISTING COURSE
========================= */

if ($id > 0) {

    $stmt = $conn->prepare("
        UPDATE tbl_course
        SET COURSE = ?, DESCRIPTION = ?
        WHERE ID = ?
    ");

    if (!$stmt) {
        die("Update preparation failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssi",
        $course,
        $description,
        $id
    );

    if ($stmt->execute()) {

        $stmt->close();

        header("Location: index.php");
        exit;

    } else {

        if ($stmt->errno == 1062) {
            die("Course already exists.");
        }

        die("Update failed: " . $stmt->error);
    }
}


/* =========================
   INSERT NEW COURSE
========================= */

$stmt = $conn->prepare("
    INSERT INTO tbl_course
    (COURSE, DESCRIPTION)
    VALUES (?, ?)
");

if (!$stmt) {
    die("Insert preparation failed: " . $conn->error);
}

$stmt->bind_param(
    "ss",
    $course,
    $description
);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: index.php");
    exit;

} else {

    if ($stmt->errno == 1062) {
        die("Course already exists.");
    }

    die("Insert failed: " . $stmt->error);
}

?>