<?php

require_once "../db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = intval($_POST["id"] ?? 0);

$course = intval($_POST["course"] ?? 0);
$yearlevel = intval($_POST["yearlevel"] ?? 0);
$term = intval($_POST["term"] ?? 0);

$code = trim($_POST["code"] ?? "");
$title = trim($_POST["title"] ?? "");

$lecture = floatval($_POST["lecture"] ?? 0);
$laboratory = floatval($_POST["laboratory"] ?? 0);
$credit = floatval($_POST["credit"] ?? 0);

$gradeInput = trim($_POST["grade"] ?? "");
$grade = ($gradeInput === "") ? null : floatval($gradeInput);

$pre_requisite = trim($_POST["pre_requisite"] ?? "");

if ($pre_requisite === "") {
    $pre_requisite = null;
}

/* VALIDATION */

if ($id <= 0) {
    die("Invalid Subject ID.");
}

if ($course <= 0) {
    die("Please select a course.");
}

if ($yearlevel <= 0) {
    die("Please select a year level.");
}

if ($term <= 0) {
    die("Please select a term.");
}

if ($code === "") {
    die("Subject code is required.");
}

if ($title === "") {
    die("Subject title is required.");
}

/* UPDATE SUBJECT */

$stmt = $conn->prepare("
    UPDATE tbl_subjects
    SET
        COURSE = ?,
        YEARLEVEL = ?,
        TERM = ?,
        CODE = ?,
        TITLE = ?,
        LECTURE = ?,
        LABORATORY = ?,
        CREDIT = ?,
        GRADE = ?,
        PRE_REQUISITE = ?
    WHERE ID = ?
");

if (!$stmt) {
    die("Update preparation failed: " . $conn->error);
}

$stmt->bind_param(
    "iiissddddsi",
    $course,
    $yearlevel,
    $term,
    $code,
    $title,
    $lecture,
    $laboratory,
    $credit,
    $grade,
    $pre_requisite,
    $id
);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: index.php");
    exit;

} else {

    if ($stmt->errno == 1062) {
        die("This subject code already exists.");
    }

    if ($stmt->errno == 1452) {
        die("Invalid Course, Year Level, or Term.");
    }

    die("Update failed: " . $stmt->error);
}

?>