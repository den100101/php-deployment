<?php

require_once "../db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

/* =========================================
   GET FORM DATA
========================================= */

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


/* =========================================
   VALIDATION
========================================= */

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


/* =========================================
   UPDATE SUBJECT
========================================= */

if ($id > 0) {

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

    /*
       i = course
       i = yearlevel
       i = term
       s = code
       s = title
       d = lecture
       d = laboratory
       d = credit
       d = grade
       s = prerequisite
       i = id
    */

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
}


/* =========================================
   INSERT SUBJECT
========================================= */

$stmt = $conn->prepare("
    INSERT INTO tbl_subjects
    (
        COURSE,
        YEARLEVEL,
        TERM,
        CODE,
        TITLE,
        LECTURE,
        LABORATORY,
        CREDIT,
        GRADE,
        PRE_REQUISITE
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Insert preparation failed: " . $conn->error);
}

/*
   i = course
   i = yearlevel
   i = term
   s = code
   s = title
   d = lecture
   d = laboratory
   d = credit
   d = grade
   s = prerequisite
*/

$stmt->bind_param(
    "iiissdddds",
    $course,
    $yearlevel,
    $term,
    $code,
    $title,
    $lecture,
    $laboratory,
    $credit,
    $grade,
    $pre_requisite
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

    die("Insert failed: " . $stmt->error);
}

?>