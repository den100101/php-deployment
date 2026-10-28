    <?php

    require_once "../db.php";

    $search = trim($_GET["search"] ?? "");

    /* ==============================
    GET COURSES
    ============================== */

    if ($search !== "") {

        $stmt = $conn->prepare("
            SELECT ID, COURSE, DESCRIPTION
            FROM tbl_course
            WHERE COURSE LIKE ?
            OR DESCRIPTION LIKE ?
            ORDER BY COURSE ASC
        ");

        $keyword = "%" . $search . "%";

        $stmt->bind_param(
            "ss",
            $keyword,
            $keyword
        );

        $stmt->execute();

        $courses = $stmt->get_result();

    } else {

        $courses = $conn->query("
            SELECT ID, COURSE, DESCRIPTION
            FROM tbl_course
            ORDER BY COURSE ASC
        ");

        if (!$courses) {
            die("Course table error: " . $conn->error);
        }
    }

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Courses - 4ABALABAGAN</title>

        <style>

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: Arial, Helvetica, sans-serif;
                background: #f1f5f9;
                color: #202020;
            }


            /* =========================
            HEADER
            ========================= */

            .top-header {
                height: 44px;
                background: #1479e9;
                color: white;

                display: flex;
                align-items: center;

                box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            }

            .header-content {
                width: 820px;
                max-width: calc(100% - 30px);
                margin: auto;

                font-size: 14px;
                font-weight: bold;
            }

            .header-content a {
                color: white;
                text-decoration: none;
            }


            /* =========================
            MAIN
            ========================= */

            .main-container {
                width: 820px;
                max-width: calc(100% - 30px);

                margin: 27px auto 50px;
            }


            /* =========================
            PAGE TITLE
            ========================= */

            .page-title {
                margin-bottom: 20px;
            }

            .page-title h1 {
                font-size: 27px;
                margin: 0 0 4px;
                font-weight: 700;
            }

            .page-title p {
                margin: 0;
                color: #999;
                font-size: 12px;
            }


            /* =========================
            BACK BUTTON
            ========================= */

            .back-button {
                display: inline-block;

                background: #6c757d;
                color: white;

                text-decoration: none;

                padding: 8px 11px;

                border-radius: 4px;

                font-size: 11px;

                margin-bottom: 14px;
            }

            .back-button:hover {
                background: #5a6268;
                color: white;
            }


            /* =========================
            COURSE HEADER CARD
            ========================= */

            .list-header {
                background: white;

                min-height: 61px;

                padding: 12px 17px;

                display: flex;
                justify-content: space-between;
                align-items: center;

                box-shadow: 0 2px 7px rgba(0,0,0,0.05);

                margin-bottom: 13px;
            }

            .list-title {
                font-size: 12px;
                font-weight: bold;
                margin-bottom: 3px;
            }

            .list-count {
                color: #999;
                font-size: 10px;
            }


            /* =========================
            ADD COURSE BUTTON
            ========================= */

            .add-button {
                background: #1479e9;
                color: white;

                text-decoration: none;

                padding: 8px 13px;

                border-radius: 4px;

                font-size: 11px;
            }

            .add-button:hover {
                background: #086bd8;
                color: white;
            }


            /* =========================
            TABLE CARD
            ========================= */

            .table-card {
                background: white;

                box-shadow: 0 2px 7px rgba(0,0,0,0.05);

                overflow-x: auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            thead {
                background: white;
            }

            th {
                text-align: left;

                padding: 13px 17px;

                font-size: 11px;

                font-weight: bold;

                border-bottom: 1px solid #e8e8e8;
            }

            td {
                padding: 10px 17px;

                font-size: 11px;

                border-bottom: 1px solid #eeeeee;

                vertical-align: middle;
            }

            tbody tr:hover {
                background: #fafcff;
            }


            /* =========================
            COURSE BADGE
            ========================= */

            .course-badge {
                display: inline-block;

                background: #edf5ff;

                color: #1479e9;

                padding: 5px 8px;

                border-radius: 15px;

                font-size: 10px;

                font-weight: bold;
            }


            /* =========================
            ACTION BUTTONS
            ========================= */

            .action-buttons {
                display: flex;
                gap: 5px;
            }

            .update-button,
            .delete-button {
                border: none;

                color: white;

                text-decoration: none;

                padding: 6px 9px;

                border-radius: 3px;

                font-size: 10px;

                cursor: pointer;
            }

            .update-button {
                background: #ffc107;
                color: #222;
            }

            .update-button:hover {
                background: #e0a800;
                color: #222;
            }

            .delete-button {
                background: #dc3545;
            }

            .delete-button:hover {
                background: #c82333;
                color: white;
            }


            /* =========================
            NO RECORDS
            ========================= */

            .no-records {
                text-align: center;

                padding: 35px;

                color: #999;

                font-size: 12px;
            }


            /* =========================
            SEARCH
            ========================= */

            .search-area {
                background: white;

                padding: 12px 17px;

                margin-bottom: 13px;

                box-shadow: 0 2px 7px rgba(0,0,0,0.05);
            }

            .search-form {
                display: flex;
                gap: 5px;
            }

            .search-form input {
                flex: 1;

                height: 32px;

                border: 1px solid #ddd;

                padding: 0 10px;

                font-size: 11px;

                outline: none;
            }

            .search-form input:focus {
                border-color: #1479e9;
            }

            .search-btn {
                background: #1479e9;

                color: white;

                border: none;

                padding: 0 15px;

                border-radius: 3px;

                font-size: 11px;

                cursor: pointer;
            }

            .reset-btn {
                background: #6c757d;

                color: white;

                border: none;

                padding: 0 12px;

                border-radius: 3px;

                font-size: 11px;

                cursor: pointer;
            }


            /* =========================
            RESPONSIVE
            ========================= */

            @media (max-width: 700px) {

                .main-container {
                    width: 100%;
                }

                .list-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                }

                .action-buttons {
                    flex-direction: column;
                }

                th,
                td {
                    padding: 9px;
                }

            }

        </style>

    </head>

    <body>


    <!-- =========================
        HEADER
    ========================= -->

    <header class="top-header">

        <div class="header-content">

            <a href="../index.php">
                4ABALABAGAN
            </a>

        </div>

    </header>


    <!-- =========================
        MAIN
    ========================= -->

    <main class="main-container">


        <!-- PAGE TITLE -->

        <div class="page-title">

            <h1>
                Courses
            </h1>

            <p>
                Manage all courses in the Academic Management System.
            </p>

        </div>


        <!-- BACK TO HOME -->

        <a
            href="../index.php"
            class="back-button"
        >
            ← Back to Home
        </a>


        <!-- =========================
            SEARCH
        ========================= -->

        <div class="search-area">

            <form
                method="GET"
                action="index.php"
                class="search-form"
            >

                <input
                    type="text"
                    name="search"
                    placeholder="Search course or description..."
                    value="<?= htmlspecialchars($search); ?>"
                >

                <button
                    type="submit"
                    class="search-btn"
                >
                    Search
                </button>

                <button
                    type="button"
                    class="reset-btn"
                    onclick="window.location='index.php'"
                >
                    Reset
                </button>

            </form>

        </div>


        <!-- =========================
            COURSE LIST HEADER
        ========================= -->

        <div class="list-header">

            <div>

                <div class="list-title">
                    Course List
                </div>

                <div class="list-count">

                    <?php
                    $totalCourses = $courses->num_rows;
                    ?>

                    <?= $totalCourses; ?> course(s) registered

                </div>

            </div>


            <a
                href="edit.php"
                class="add-button"
            >
                + Add Course
            </a>

        </div>


        <!-- =========================
            COURSE TABLE
        ========================= -->

        <div class="table-card">

            <table>

                <thead>

                    <tr>

                        <th style="width: 25%;">
                            Course
                        </th>

                        <th style="width: 55%;">
                            Description
                        </th>

                        <th style="width: 20%;">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if ($courses->num_rows > 0): ?>

                        <?php while ($row = $courses->fetch_assoc()): ?>

                            <tr>

                                <td>

                                    <span class="course-badge">

                                        <?= htmlspecialchars($row["COURSE"]); ?>

                                    </span>

                                </td>


                                <td>

                                    <?= htmlspecialchars($row["DESCRIPTION"]); ?>

                                </td>


                                <td>

                                    <div class="action-buttons">

                                        <!-- UPDATE -->

                                        <a
                                            href="edit.php?id=<?= $row["ID"]; ?>"
                                            class="update-button"
                                        >
                                            Update
                                        </a>


                                        <!-- DELETE -->

                                        <a
                                            href="delete.php?id=<?= $row["ID"]; ?>"
                                            class="delete-button"
                                            onclick="return confirm('Are you sure you want to delete this course?');"
                                        >
                                            Delete
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="3"
                                class="no-records"
                            >

                                No courses found.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>


    </main>

    </body>
    </html>