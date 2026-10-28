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

if ($section === "" || $description === "") {
    die("Section and Description are required.");
}


/* =========================
   UPDATE EXISTING SECTION
========================= */

if ($id > 0) {

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
}


/* =========================
   INSERT NEW SECTION
========================= */

$stmt = $conn->prepare("
    INSERT INTO tbl_section
    (SECTION, DESCRIPTION)
    VALUES (?, ?)
");

if (!$stmt) {
    die("Insert preparation failed: " . $conn->error);
}

$stmt->bind_param(
    "ss",
    $section,
    $description
);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: index.php");
    exit;

} else {

    if ($stmt->errno == 1062) {
        die("Section already exists.");
    }

    die("Insert failed: " . $stmt->error);
}

?>