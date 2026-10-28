<?php

require_once "../db.php";

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    die("Invalid Course ID.");
}


/* =========================
   DELETE COURSE
========================= */

$stmt = $conn->prepare("
    DELETE FROM tbl_course
    WHERE ID = ?
");

if (!$stmt) {
    die("Delete preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: index.php");
    exit;

} else {

    /*
       Error 1451 means the course
       is currently being used by
       another table such as tbl_subjects.
    */

    if ($stmt->errno == 1451) {
        die(
            "Cannot delete this course because it is being used by another record."
        );
    }

    die("Delete failed: " . $stmt->error);
}

?>