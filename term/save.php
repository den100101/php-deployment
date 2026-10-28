<?php

require_once "../db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = intval($_POST["id"] ?? 0);

$term = trim($_POST["term"] ?? "");
$description = trim($_POST["description"] ?? "");

/* VALIDATION */
if ($term === "" || $description === "") {
    die("Term and Description are required.");
}

/* =========================================
   UPDATE TERM
========================================= */

if ($id > 0) {

    $stmt = $conn->prepare("
        UPDATE tbl_term
        SET TERM = ?, DESCRIPTION = ?
        WHERE ID = ?
    ");

    if (!$stmt) {
        die("Update preparation failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssi",
        $term,
        $description,
        $id
    );

    if ($stmt->execute()) {

        $stmt->close();

        header("Location: index.php");
        exit;

    } else {

        if ($stmt->errno == 1062) {
            die("This term already exists.");
        }

        die("Update failed: " . $stmt->error);
    }
}

/* =========================================
   INSERT TERM
========================================= */

$stmt = $conn->prepare("
    INSERT INTO tbl_term (TERM, DESCRIPTION)
    VALUES (?, ?)
");

if (!$stmt) {
    die("Insert preparation failed: " . $conn->error);
}

$stmt->bind_param(
    "ss",
    $term,
    $description
);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: index.php");
    exit;

} else {

    if ($stmt->errno == 1062) {
        die("This term already exists.");
    }

    die("Insert failed: " . $stmt->error);
}

?>