<?php

require_once "../db.php";

$id = intval($_GET["id"] ?? 0);

$subject = [
    "ID" => 0,
    "COURSE" => "",
    "YEARLEVEL" => "",
    "TERM" => "",
    "CODE" => "",
    "TITLE" => "",
    "LECTURE" => "",
    "LABORATORY" => "",
    "CREDIT" => "",
    "GRADE" => "",
    "PRE_REQUISITE" => ""
];

if ($id > 0) {

    $stmt = $conn->prepare("
        SELECT ID, COURSE, YEARLEVEL, TERM, CODE, TITLE,
               LECTURE, LABORATORY, CREDIT, GRADE, PRE_REQUISITE
        FROM tbl_subjects
        WHERE ID = ?
    ");

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Subject not found.");
    }

    $subject = $result->fetch_assoc();

    $stmt->close();
}

/* LOAD COURSES */
$courses = $conn->query("
    SELECT ID, COURSE, DESCRIPTION
    FROM tbl_course
    ORDER BY COURSE ASC
");

if (!$courses) {
    die("Course table error: " . $conn->error);
}

/* LOAD YEAR LEVELS */
$yearlevels = $conn->query("
    SELECT ID, YEARLEVEL, DESCRIPTION
    FROM tbl_yearlevel
    ORDER BY ID ASC
");

if (!$yearlevels) {
    die("Year level table error: " . $conn->error);
}

/* LOAD TERMS */
$terms = $conn->query("
    SELECT ID, TERM, DESCRIPTION
    FROM tbl_term
    ORDER BY ID ASC
");

if (!$terms) {
    die("Term table error: " . $conn->error);
}

$isEdit = ($id > 0);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $isEdit ? "Edit Subject" : "Add Subject"; ?>
    </title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #eef3f8;
            color: #172033;
        }

        .header {
            background: #1261a0;
            color: white;
            padding: 20px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        }

        .header h1 {
            font-size: 24px;
        }

        .home-btn {
            background: white;
            color: #1261a0;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 7px;
            font-weight: bold;
        }

        .home-btn:hover {
            background: #dceeff;
        }

        .container {
            max-width: 900px;
            margin: 35px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.10);
        }

        .container h2 {
            margin-bottom: 25px;
            color: #1261a0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .full {
            grid-column: 1 / 3;
        }

        label {
            font-weight: bold;
            margin-bottom: 7px;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccd5df;
            border-radius: 7px;
            font-size: 15px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #1261a0;
            box-shadow: 0 0 0 2px rgba(18,97,160,0.12);
        }

        .buttons {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }

        .btn {
            border: none;
            padding: 11px 20px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 15px;
            cursor: pointer;
            font-weight: bold;
        }

        .save-btn {
            background: #1261a0;
            color: white;
        }

        .save-btn:hover {
            background: #0d4e83;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
        }

        .cancel-btn:hover {
            background: #565e64;
        }

        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }

            .header {
                padding: 15px 20px;
            }

            .container {
                margin: 20px;
                padding: 20px;
            }

        }

    </style>

</head>

<body>

<div class="header">

    <h1>
        <?= $isEdit ? "Edit Subject" : "Add Subject"; ?>
    </h1>

    <a href="../index.php" class="home-btn">
        4abalabagan
    </a>

</div>

<div class="container">

    <h2>
        <?= $isEdit ? "Update Subject Information" : "Add New Subject"; ?>
    </h2>

    <form action="save.php" method="POST">

        <?php if ($isEdit): ?>

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($subject["ID"]); ?>"
            >

        <?php endif; ?>

        <div class="form-grid">

            <!-- COURSE -->
            <div class="form-group">

                <label for="course">
                    Course
                </label>

                <select name="course" id="course" required>

                    <option value="">
                        Select Course
                    </option>

                    <?php while ($course = $courses->fetch_assoc()): ?>

                        <option
                            value="<?= $course["ID"]; ?>"
                            <?= ($subject["COURSE"] == $course["ID"]) ? "selected" : ""; ?>
                        >

                            <?= htmlspecialchars($course["COURSE"]); ?>
                            -
                            <?= htmlspecialchars($course["DESCRIPTION"]); ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- YEAR LEVEL -->
            <div class="form-group">

                <label for="yearlevel">
                    Year Level
                </label>

                <select name="yearlevel" id="yearlevel" required>

                    <option value="">
                        Select Year Level
                    </option>

                    <?php while ($year = $yearlevels->fetch_assoc()): ?>

                        <option
                            value="<?= $year["ID"]; ?>"
                            <?= ($subject["YEARLEVEL"] == $year["ID"]) ? "selected" : ""; ?>
                        >

                            <?= htmlspecialchars($year["YEARLEVEL"]); ?>
                            -
                            <?= htmlspecialchars($year["DESCRIPTION"]); ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- TERM -->
            <div class="form-group">

                <label for="term">
                    Term
                </label>

                <select name="term" id="term" required>

                    <option value="">
                        Select Term
                    </option>

                    <?php while ($term = $terms->fetch_assoc()): ?>

                        <option
                            value="<?= $term["ID"]; ?>"
                            <?= ($subject["TERM"] == $term["ID"]) ? "selected" : ""; ?>
                        >

                            <?= htmlspecialchars($term["TERM"]); ?>
                            -
                            <?= htmlspecialchars($term["DESCRIPTION"]); ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- SUBJECT CODE -->
            <div class="form-group">

                <label for="code">
                    Subject Code
                </label>

                <input
                    type="text"
                    name="code"
                    id="code"
                    placeholder="IS101"
                    value="<?= htmlspecialchars($subject["CODE"]); ?>"
                    required
                >

            </div>


            <!-- SUBJECT TITLE -->
            <div class="form-group full">

                <label for="title">
                    Subject Title
                </label>

                <input
                    type="text"
                    name="title"
                    id="title"
                    placeholder="Introduction to Information Systems"
                    value="<?= htmlspecialchars($subject["TITLE"]); ?>"
                    required
                >

            </div>


            <!-- LECTURE -->
            <div class="form-group">

                <label for="lecture">
                    Lecture Units
                </label>

                <input
                    type="number"
                    name="lecture"
                    id="lecture"
                    step="0.01"
                    min="0"
                    value="<?= htmlspecialchars($subject["LECTURE"]); ?>"
                >

            </div>


            <!-- LABORATORY -->
            <div class="form-group">

                <label for="laboratory">
                    Laboratory Units
                </label>

                <input
                    type="number"
                    name="laboratory"
                    id="laboratory"
                    step="0.01"
                    min="0"
                    value="<?= htmlspecialchars($subject["LABORATORY"]); ?>"
                >

            </div>


            <!-- CREDIT -->
            <div class="form-group">

                <label for="credit">
                    Credit
                </label>

                <input
                    type="number"
                    name="credit"
                    id="credit"
                    step="0.01"
                    min="0"
                    value="<?= htmlspecialchars($subject["CREDIT"]); ?>"
                >

            </div>


            <!-- GRADE -->
            <div class="form-group">

                <label for="grade">
                    Grade
                </label>

                <input
                    type="number"
                    name="grade"
                    id="grade"
                    step="0.01"
                    min="0"
                    value="<?= htmlspecialchars($subject["GRADE"] ?? ""); ?>"
                >

            </div>


            <!-- PRE-REQUISITE -->
            <div class="form-group full">

                <label for="pre_requisite">
                    Pre-Requisite
                </label>

                <input
                    type="text"
                    name="pre_requisite"
                    id="pre_requisite"
                    placeholder="Example: IS100"
                    value="<?= htmlspecialchars($subject["PRE_REQUISITE"] ?? ""); ?>"
                >

            </div>

        </div>


        <div class="buttons">

            <button type="submit" class="btn save-btn">

                <?= $isEdit ? "Update Subject" : "Save Subject"; ?>

            </button>

            <a href="cancel.php" class="btn cancel-btn">
                Cancel
            </a>

        </div>

    </form>

</div>

</body>
</html>