<?php

require_once "../db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = intval($_POST["id"] ?? 0);

$yearlevel = trim($_POST["yearlevel"] ?? "");
$description = trim($_POST["description"] ?? "");


/* =========================
   VALIDATION
========================= */

if ($yearlevel === "" || $description === "") {
    die("Year Level and Description are required.");
}


/* =========================
   UPDATE EXISTING YEAR LEVEL
========================= */

if ($id > 0) {

    $stmt = $conn->prepare("
        UPDATE tbl_yearlevel
        SET YEARLEVEL = ?, DESCRIPTION = ?
        WHERE ID = ?
    ");

    if (!$stmt) {
        die("Update preparation failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssi",
        $yearlevel,
        $description,
        $id
    );

    if ($stmt->execute()) {

        $stmt->close();

        header("Location: index.php");
        exit;

    } else {

        if ($stmt->errno == 1062) {
            die("Year Level already exists.");
        }

        die("Update failed: " . $stmt->error);
    }
}


/* =========================
   INSERT NEW YEAR LEVEL
========================= */

$stmt = $conn->prepare("
    INSERT INTO tbl_yearlevel
    (YEARLEVEL, DESCRIPTION)
    VALUES (?, ?)
");

if (!$stmt) {
    die("Insert preparation failed: " . $conn->error);
}

$stmt->bind_param(
    "ss",
    $yearlevel,
    $description
);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: index.php");
    exit;

} else {

    if ($stmt->errno == 1062) {
        die("Year Level already exists.");
    }

    die("Insert failed: " . $stmt->error);
}

?>