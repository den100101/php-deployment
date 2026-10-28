<?php

require_once "../db.php";

$id = intval($_GET["id"] ?? 0);

$course = "";
$description = "";

/* =========================
   EDIT EXISTING COURSE
========================= */

if ($id > 0) {

    $stmt = $conn->prepare("
        SELECT COURSE, DESCRIPTION
        FROM tbl_course
        WHERE ID = ?
    ");

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Course not found.");
    }

    $row = $result->fetch_assoc();

    $course = $row["COURSE"];
    $description = $row["DESCRIPTION"];

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    <?php echo $id > 0 ? "Edit Course" : "Add Course"; ?>
</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #eef3f8;
    color: #172033;
}

.header {
    background: #1261a0;
    color: white;
    padding: 20px 35px;
}

.header h1 {
    margin: 0;
}

.container {
    width: 92%;
    max-width: 700px;
    margin: 40px auto;
}

.form-box {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 3px 15px rgba(0,0,0,.08);
}

.form-box h2 {
    margin-top: 0;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

.form-group input:focus {
    outline: none;
    border-color: #1261a0;
}

.buttons {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

button,
.btn {
    padding: 11px 18px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
    font-size: 15px;
}

button {
    background: #1261a0;
    color: white;
}

button:hover {
    background: #0d4f82;
}

.btn {
    background: #555;
    color: white;
}

.btn:hover {
    background: #333;
}

</style>

</head>

<body>

<div class="header">

    <h1>
        Course Management
    </h1>

</div>

<div class="container">

    <div class="form-box">

        <h2>
            <?php
            echo $id > 0
                ? "Edit Course"
                : "Add Course";
            ?>
        </h2>

        <form action="save.php" method="POST">

            <?php if ($id > 0): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $id; ?>"
                >

            <?php endif; ?>


            <div class="form-group">

                <label for="course">
                    Course
                </label>

                <input
                    type="text"
                    id="course"
                    name="course"
                    placeholder="Example: BSIS"
                    value="<?php echo htmlspecialchars($course); ?>"
                    maxlength="20"
                    required
                >

            </div>


            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <input
                    type="text"
                    id="description"
                    name="description"
                    placeholder="Example: Bachelor of Science in Information Systems"
                    value="<?php echo htmlspecialchars($description); ?>"
                    maxlength="255"
                    required
                >

            </div>


            <div class="buttons">

                <button type="submit">

                    <?php
                    echo $id > 0
                        ? "Update Course"
                        : "Save Course";
                    ?>

                </button>

                <a
                    href="cancel.php"
                    class="btn"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>