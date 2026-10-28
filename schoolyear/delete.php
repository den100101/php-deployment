<?php

require_once "../db.php";

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    die("Invalid School Year ID.");
}

$stmt = $conn->prepare("
    DELETE FROM tbl_schoolyear
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

    if ($stmt->errno == 1451) {
        die("Cannot delete this school year because it is being used by another record.");
    }

    die("Delete failed: " . $stmt->error);
}

?>