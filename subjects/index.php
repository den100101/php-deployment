<?php
require_once "../db.php";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $stmt = $conn->prepare("
        SELECT
            s.ID,
            s.CODE,
            s.TITLE,
            s.LECTURE,
            s.LABORATORTY,
            s.CREDIT,
            s.GRADE,
            s.PRE_REQUISITE,
            c.COURSE,
            y.YEARLEVEL,
            t.TERM
        FROM tbl_subjects s
        INNER JOIN tbl_course c
            ON s.COURSE = c.ID
        INNER JOIN tbl_yearlevel y
            ON s.YEARLEVEL = y.ID
        INNER JOIN tbl_term t
            ON s.TERM = t.ID
        WHERE s.CODE LIKE ?
           OR s.TITLE LIKE ?
           OR c.COURSE LIKE ?
           OR y.YEARLEVEL LIKE ?
           OR t.TERM LIKE ?
           OR s.PRE_REQUISITE LIKE ?
        ORDER BY s.ID DESC
    ");

    $keyword = "%" . $search . "%";

    $stmt->bind_param(
        "ssssss",
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword
    );

    $stmt->execute();

    $subjects = $stmt->get_result();
} else {

    $subjects = $conn->query("
        SELECT
            s.ID,
            s.CODE,
            s.TITLE,
            s.LECTURE,
            s.LABORATORTY,
            s.CREDIT,
            s.GRADE,
            s.PRE_REQUISITE,
            c.COURSE,
            y.YEARLEVEL,
            t.TERM
        FROM tbl_subjects s
        INNER JOIN tbl_course c
            ON s.COURSE = c.ID
        INNER JOIN tbl_yearlevel y
            ON s.YEARLEVEL = y.ID
        INNER JOIN tbl_term t
            ON s.TERM = t.ID
        ORDER BY s.ID DESC
    ");

    if (!$subjects) {
        die("Subject table error: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Subjects</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #eef3f8;
            color: #222;
        }


        /* =========================
           HEADER
        ========================= */

        .top-header {
            height: 63px;

            background: #1479e9;

            color: white;

            display: flex;

            align-items: center;

            padding-left: calc((100% - 1100px) / 2);

            font-size: 14px;

            font-weight: bold;

            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        }


        /* =========================
           MAIN CONTAINER
        ========================= */

        .main-container {
            width: 1000px;

            margin: 28px auto;
        }


        /* =========================
           TOP AREA
        ========================= */

        .top-area {
            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            margin-bottom: 14px;
        }


        /* =========================
           TITLE
        ========================= */

        .page-title {
            font-size: 28px;

            font-weight: bold;

            margin-bottom: 5px;
        }

        .page-subtitle {
            font-size: 11px;

            color: #999;
        }


        /* =========================
           ADD SUBJECT
        ========================= */

        .add-button {
            display: inline-block;

            background: #1479e9;

            color: white;

            text-decoration: none;

            padding: 8px 12px;

            border-radius: 4px;

            font-size: 11px;

            margin-top: 13px;
        }

        .add-button:hover {
            background: #086bd8;
        }


        /* =========================
           BACK BUTTON
        ========================= */

        .back-button {
            display: inline-block;

            background: #6c757d;

            color: white;

            text-decoration: none;

            padding: 7px 10px;

            border-radius: 4px;

            font-size: 11px;

            margin-bottom: 12px;
        }

        .back-button:hover {
            background: #5a6268;
        }


        /* =========================
           TABLE CARD
        ========================= */

        .table-card {
            background: white;

            padding: 14px 17px;

            box-shadow: 0 2px 9px rgba(0, 0, 0, 0.07);

            overflow-x: auto;
        }


        /* =========================
           TABLE
        ========================= */

        table {
            width: 100%;

            min-width: 940px;

            border-collapse: collapse;
        }


        /* TABLE HEADER */

        thead th {
            background: #202b3c;

            color: white;

            text-align: left;

            font-size: 11px;

            font-weight: bold;

            padding: 8px 6px;

            border-right: 1px solid #374151;
        }

        thead th:last-child {
            border-right: none;
        }


        /* TABLE BODY */

        tbody td {
            padding: 7px 6px;

            font-size: 10px;

            border-bottom: 1px solid #ddd;

            white-space: nowrap;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }


        /* =========================
           BADGES
        ========================= */

        .course-badge {
            display: inline-block;

            background: #edf5ff;

            color: #0069e8;

            padding: 4px 7px;

            border-radius: 3px;

            font-size: 10px;

            font-weight: bold;
        }

        .year-badge {
            color: #333;
        }

        .term-badge {
            color: #333;
        }

        .code-badge {
            display: inline-block;

            background: #f1f1f1;

            color: #333;

            padding: 4px 7px;

            border-radius: 3px;

            font-size: 10px;

            font-weight: bold;
        }


        /* =========================
           ACTION BUTTONS
        ========================= */

        .update-button {
            display: inline-block;

            background: #ffbd00;

            color: #222;

            text-decoration: none;

            padding: 6px 9px;

            border-radius: 3px;

            font-size: 9px;

            margin-right: 2px;
        }

        .update-button:hover {
            background: #e6a900;
        }


        .delete-button {
            display: inline-block;

            background: #dc3545;

            color: white;

            text-decoration: none;

            padding: 6px 9px;

            border-radius: 3px;

            font-size: 9px;
        }

        .delete-button:hover {
            background: #c82333;
        }


        /* =========================
           EMPTY
        ========================= */

        .no-records {
            text-align: center;

            color: #999;

            padding: 25px;

            font-size: 11px;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 1050px) {

            .main-container {
                width: 92%;
            }

            .top-header {
                padding-left: 4%;
            }

        }
    </style>

</head>


<body>


    <!-- =========================
         HEADER
    ========================= -->

    <div class="top-header">
        4ABALABAGAN
    </div>


    <!-- =========================
         MAIN
    ========================= -->

    <div class="main-container">


        <!-- TOP AREA -->

        <div class="top-area">

            <div>

                <h1 class="page-title">
                    Subjects
                </h1>

                <div class="page-subtitle">
                    Manage curriculum subjects
                </div>

            </div>


            <a
                href="edit.php"
                class="add-button">
                + Add Subject
            </a>

        </div>


        <!-- BACK -->

        <a
            href="../index.php"
            class="back-button">
            ← Back to Home
        </a>


        <!-- =========================
             SUBJECT TABLE
        ========================= -->

        <div class="table-card">

            <table>

                <thead>

                    <tr>

                        <th>Course</th>

                        <th>Year Level</th>

                        <th>Term</th>

                        <th>Code</th>

                        <th>Title</th>

                        <th>Lecture</th>

                        <th>Laboratory</th>

                        <th>Credit</th>

                        <th>Grade</th>

                        <th>Pre-Requisite</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if ($subjects->num_rows > 0): ?>

                        <?php while ($row = $subjects->fetch_assoc()): ?>

                            <tr>


                                <!-- COURSE -->

                                <td>

                                    <span class="course-badge">
                                        <?= htmlspecialchars($row["COURSE"]) ?>
                                    </span>

                                </td>


                                <!-- YEAR LEVEL -->

                                <td>

                                    <span class="year-badge">
                                        <?= htmlspecialchars($row["YEARLEVEL"]) ?>
                                    </span>

                                </td>


                                <!-- TERM -->

                                <td>

                                    <span class="term-badge">
                                        <?= htmlspecialchars($row["TERM"]) ?>
                                    </span>

                                </td>


                                <!-- CODE -->

                                <td>

                                    <span class="code-badge">
                                        <?= htmlspecialchars($row["CODE"]) ?>
                                    </span>

                                </td>


                                <!-- TITLE -->

                                <td>
                                    <?= htmlspecialchars($row["TITLE"]) ?>
                                </td>


                                <!-- LECTURE -->

                                <td>
                                    <?= number_format((float)$row["LECTURE"], 2) ?>
                                </td>


                                <!-- LABORATORY -->

                                <td>
                                    <?= number_format((float)$row["LABORATORY"], 2) ?>
                                </td>


                                <!-- CREDIT -->

                                <td>
                                    <?= number_format((float)$row["CREDIT"], 2) ?>
                                </td>


                                <!-- GRADE -->

                                <td>

                                    <?php if ($row["GRADE"] !== null): ?>

                                        <?= number_format((float)$row["GRADE"], 2) ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>


                                <!-- PRE-REQUISITE -->

                                <td>

                                    <?php if (!empty($row["PRE_REQUISITE"])): ?>

                                        <?= htmlspecialchars($row["PRE_REQUISITE"]) ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <a
                                        href="edit.php?id=<?= $row["ID"] ?>"
                                        class="update-button">
                                        Update
                                    </a>


                                    <a
                                        href="delete.php?id=<?= $row["ID"] ?>"
                                        class="delete-button"
                                        onclick="return confirm('Are you sure you want to delete this subject?');">
                                        Delete
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="11"
                                class="no-records">
                                No subject records found.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


</body>

</html>