<?php

require_once "../db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = intval($_POST["id"] ?? 0);

$section = trim($_POST["section"] ?? "");
$description = trim($_POST["description"] ?? "");


/* =========================
   VALIDATION
========================= */

if ($id <= 0) {
    die("Invalid Section ID.");
}

if ($section === "" || $description === "") {
    die("Section and Description are required.");
}


/* =========================
   UPDATE SECTION
========================= */

$stmt = $conn->prepare("
    UPDATE tbl_section
    SET SECTION = ?, DESCRIPTION = ?
    WHERE ID = ?
");

if (!$stmt) {
    die("Update preparation failed: " . $conn->error);
}

$stmt->bind_param(
    "ssi",
    $section,
    $description,
    $id
);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: index.php");
    exit;

} else {

    if ($stmt->errno == 1062) {
        die("Section already exists.");
    }

    die("Update failed: " . $stmt->error);
}

?>