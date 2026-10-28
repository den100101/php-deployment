<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "auth.php";
require_once "db.php";


if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection error. Please check db.php.");
}

// Get the signed-in user's profile details.
// Supports sessions that store either user_id or username.
$currentUser = [
    "FULLNAME" => $_SESSION["fullname"] ?? "User",
    "USERNAME" => $_SESSION["username"] ?? "",
    "IMAGE" => $_SESSION["image"] ?? ""
];

try {
    if (!empty($_SESSION["user_id"])) {
        $profileStmt = $conn->prepare(
            "SELECT FULLNAME, USERNAME, IMAGE
             FROM tbl_user
             WHERE ID = ?
             LIMIT 1"
        );
        $profileStmt->bind_param("i", $_SESSION["user_id"]);
    } elseif (!empty($_SESSION["username"])) {
        $profileStmt = $conn->prepare(
            "SELECT FULLNAME, USERNAME, IMAGE
             FROM tbl_user
             WHERE USERNAME = ?
             LIMIT 1"
        );
        $profileStmt->bind_param("s", $_SESSION["username"]);
    } else {
        $profileStmt = null;
    }

    if ($profileStmt !== null) {
        $profileStmt->execute();
        $profileResult = $profileStmt->get_result();

        if ($profileRow = $profileResult->fetch_assoc()) {
            $currentUser = $profileRow;
            $_SESSION["fullname"] = $profileRow["FULLNAME"];
            $_SESSION["username"] = $profileRow["USERNAME"];
            $_SESSION["image"] = $profileRow["IMAGE"] ?? "";
        }

        $profileStmt->close();
    }
} catch (mysqli_sql_exception $e) {
    error_log("Profile loading error: " . $e->getMessage());
}

// Only display profile images from the designated users folder.
$profileImage = "";
if (!empty($currentUser["IMAGE"])) {
    $savedImage = str_replace("\\\\", "/", $currentUser["IMAGE"]);

    if (
        preg_match('#^images/users/[a-zA-Z0-9_-]+\\.JPG$#i', $savedImage) &&
        is_file(__DIR__ . "/" . $savedImage)
    ) {
        $profileImage = $savedImage;
    }
}



$courseCount = 0;
$yearlevelCount = 0;
$sectionCount = 0;
$termCount = 0;
$schoolyearCount = 0;
$subjectCount = 0;





function getTableCount($conn, $table)
{
    $allowedTables = [
        "tbl_course",
        "tbl_yearlevel",
        "tbl_section",
        "tbl_term",
        "tbl_schoolyear",
        "tbl_subjects"
    ];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    $sql = "SELECT COUNT(*) AS total FROM `$table`";

    $result = $conn->query($sql);

    if ($result === false) {
        return 0;
    }

    $row = $result->fetch_assoc();

    return isset($row["total"])
        ? (int)$row["total"]
        : 0;
}



$courseCount =
    getTableCount($conn, "tbl_course");

$yearlevelCount =
    getTableCount($conn, "tbl_yearlevel");

$sectionCount =
    getTableCount($conn, "tbl_section");

$termCount =
    getTableCount($conn, "tbl_term");

$schoolyearCount =
    getTableCount($conn, "tbl_schoolyear");

$subjectCount =
    getTableCount($conn, "tbl_subjects");



$search = trim(
    $_GET["search"] ?? ""
);

$searchResults = [];

if ($search !== "") {

    $sql = "
        SELECT
            ID,
            COURSE,
            DESCRIPTION
        FROM tbl_course
        WHERE COURSE LIKE ?
           OR DESCRIPTION LIKE ?
        ORDER BY COURSE ASC
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $keyword = "%" . $search . "%";

        $stmt->bind_param(
            "ss",
            $keyword,
            $keyword
        );

        if ($stmt->execute()) {

            /*
            ==========================================
            GET SEARCH RESULTS
            ==========================================
            */

            $stmt->store_result();

            $stmt->bind_result(
                $id,
                $course,
                $description
            );

            while ($stmt->fetch()) {

                $searchResults[] = [
                    "ID" => $id,
                    "COURSE" => $course,
                    "DESCRIPTION" => $description
                ];
            }
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        4ABALABAGAN - Academic Management System
    </title>


  
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =========================================
         BOOTSTRAP ICONS
    ========================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f1f5f9;

            color: #1f2937;
        }


        
        .top-header {

            min-height: 58px;

            background: #1479e9;

            color: white;

            display: flex;

            align-items: center;

            padding: 0 20px;

            box-shadow:
                0 2px 6px
                rgba(0, 0, 0, 0.12);
        }


        .header-inner {

            width: 820px;

            max-width: 100%;

            margin: auto;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;
        }


        .brand {

            font-size: 15px;

            font-weight: bold;

            display: flex;

            align-items: center;

            gap: 7px;

            white-space: nowrap;
        }


        .brand i {

            font-size: 15px;
        }


       

        .user-profile {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-left: auto;
            margin-right: 5px;
            min-width: 0;
        }

        .profile-image,
        .profile-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.85);
            flex-shrink: 0;
        }

        .profile-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 16px;
        }

        .profile-details {
            min-width: 0;
            line-height: 1.3;
        }

        .profile-name {
            font-size: 12px;
            font-weight: bold;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .profile-role {
            font-size: 10px;
            opacity: 0.85;
        }

        .logout-button {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            color: white;

            text-decoration: none;

            background:
                rgba(0, 0, 0, 0.12);

            border:
                1px solid
                rgba(255, 255, 255, 0.35);

            padding: 7px 13px;

            border-radius: 5px;

            font-size: 12px;

            font-weight: 600;

            transition: 0.2s;

            white-space: nowrap;
        }


        .logout-button:hover {

            color: white;

            background:
                rgba(0, 0, 0, 0.22);

            border-color:
                rgba(255, 255, 255, 0.55);
        }


     

        .main-container {

            width: 820px;

            max-width:
                calc(100% - 30px);

            margin:
                28px auto 50px;
        }


    
        .page-title {

            margin-bottom: 20px;
        }


        .page-title h1 {

            font-size: 28px;

            font-weight: 700;

            margin-bottom: 4px;

            color: #202020;
        }


        .page-title p {

            margin: 0;

            font-size: 13px;

            color: #9a9a9a;
        }


      
        .search-box {

            background: white;

            padding: 14px;

            margin-bottom: 18px;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.05);
        }


        .search-top {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            margin-bottom: 10px;
        }


        .search-label {

            color: #777;

            font-size: 12px;
        }


        .search-label i {

            color: #1479e9;

            font-size: 17px;

            margin-right: 4px;
        }


        .course-count {

            background: #1479e9;

            color: white;

            font-size: 10px;

            padding: 6px 9px;

            border-radius: 4px;
        }


        .search-form {

            display: flex;

            gap: 5px;
        }


        .search-form input {

            flex: 1;

            height: 32px;

            border:
                1px solid #e0e0e0;

            padding: 0 10px;

            font-size: 12px;

            outline: none;
        }


        .search-form input:focus {

            border-color:
                #1479e9;
        }


        .search-button {

            width: 125px;

            border: none;

            background: #1479e9;

            color: white;

            font-size: 12px;

            border-radius: 4px;

            cursor: pointer;
        }


        .search-button:hover {

            background: #086bd8;
        }


        .reset-button {

            width: 55px;

            border: none;

            background: #777;

            color: white;

            border-radius: 4px;

            cursor: pointer;
        }


        .reset-button:hover {

            background: #555;
        }


        
        .search-results {

            background: white;

            margin-bottom: 18px;

            padding: 15px;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.05);
        }


        .search-results h5 {

            font-size: 14px;

            margin-bottom: 12px;
        }


        .result-item {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            padding: 10px;

            border-bottom:
                1px solid #eee;

            gap: 10px;
        }


        .result-item:last-child {

            border-bottom: none;
        }


        .result-course {

            font-weight: bold;

            color: #1479e9;
        }


        .result-description {

            color: #777;

            font-size: 12px;
        }


        /*
        ==========================================
        DASHBOARD GRID
        ==========================================
        */

        .dashboard-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 14px;
        }


        /*
        ==========================================
        DASHBOARD CARD
        ==========================================
        */

        .dashboard-card {

            background: white;

            min-height: 180px;

            padding:
                20px 12px 14px;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.05);

            position: relative;

            transition: 0.2s;
        }


        .dashboard-card:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.10);
        }


        .card-icon {

            font-size: 28px;

            margin-bottom: 20px;
        }


        .dashboard-card h3 {

            font-size: 13px;

            font-weight: bold;

            margin-bottom: 5px;
        }


        .card-number {

            font-size: 20px;

            margin-bottom: 14px;
        }


        .manage-button {

            display: inline-block;

            padding:
                7px 10px;

            border-radius: 4px;

            color: white;

            text-decoration: none;

            font-size: 10px;
        }


        .manage-button:hover {

            color: white;

            opacity: 0.9;
        }


        /*
        ==========================================
        CARD COLORS
        ==========================================
        */

        .blue .card-icon {
            color: #31a8d5;
        }

        .blue .manage-button {
            background: #1479e9;
        }


        .green .card-icon {
            color: #42b89c;
        }

        .green .manage-button {
            background: #27a88b;
        }


        .yellow .card-icon {
            color: #e8b500;
        }

        .yellow .manage-button {

            background: #ffbd00;

            color: #222;
        }

        .yellow .manage-button:hover {
            color: #222;
        }


        .cyan .card-icon {
            color: #26a8d1;
        }

        .cyan .manage-button {
            background: #20a9cc;
        }


        .red .card-icon {
            color: #e65b60;
        }

        .red .manage-button {
            background: #e65358;
        }


        .dark .card-icon {
            color: #222;
        }

        .dark .manage-button {
            background: #222;
        }


        /*
        ==========================================
        RESPONSIVE
        ==========================================
        */

        @media (max-width: 850px) {

            .header-inner {
                width: 100%;
            }

            .main-container {
                width: 100%;
            }
        }


        @media (max-width: 700px) {

            .dashboard-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .search-form {

                flex-wrap: wrap;
            }


            .search-button {

                width: 100px;
            }
        }


        @media (max-width: 500px) {

            .top-header {

                padding: 0 10px;
            }


            .brand {

                font-size: 13px;
            }

            .user-profile {
                gap: 5px;
                margin-right: 0;
            }

            .profile-image,
            .profile-placeholder {
                width: 30px;
                height: 30px;
            }

            .profile-name {
                max-width: 85px;
                font-size: 10px;
            }

            .profile-role {
                font-size: 9px;
            }

            .logout-button {

                padding: 6px 10px;

                font-size: 11px;
            }


            .dashboard-grid {

                grid-template-columns: 1fr;
            }


            .main-container {

                max-width:
                    calc(100% - 20px);

                margin-top: 20px;
            }


            .page-title h1 {

                font-size: 23px;
            }


            .search-top {

                align-items:
                    flex-start;

                gap: 10px;

                flex-direction:
                    column;
            }


            .search-form {

                flex-direction:
                    column;
            }


            .search-form input,
            .search-button,
            .reset-button {

                width: 100%;
            }


            .result-item {

                align-items:
                    flex-start;

                flex-direction:
                    column;
            }
        }

    </style>

</head>


<body>


<!-- =========================================
     HEADER
========================================== -->

<header class="top-header">

    <div class="header-inner">

        <div class="brand">

            <i class="bi bi-mortarboard-fill"></i>

            <span>
                4ABALABAGAN
            </span>

        </div>


        <div class="user-profile">

            <?php if ($profileImage !== ""): ?>
                <img
                    src="<?= htmlspecialchars($profileImage, ENT_QUOTES, "UTF-8") ?>"
                    alt="Profile picture"
                    class="profile-image"
                >
            <?php else: ?>
                <div class="profile-placeholder">
                    <i class="bi bi-person-fill"></i>
                </div>
            <?php endif; ?>

            <div class="profile-details">
                <div class="profile-name">
                    <?= htmlspecialchars($currentUser["FULLNAME"] ?? "User", ENT_QUOTES, "UTF-8") ?>
                </div>
                <div class="profile-role">
                    <?= htmlspecialchars($currentUser["USERNAME"] ?? "", ENT_QUOTES, "UTF-8") ?>
                </div>
            </div>

        </div>

        <a
            href="logout.php"
            class="logout-button"
            onclick="return confirm('Are you sure you want to logout?');"
        >

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</header>


<!-- =========================================
     MAIN
========================================== -->

<main class="main-container">


    <!-- =========================================
         TITLE
    ========================================== -->

    <div class="page-title">

        <h1>
            Academic Management System
        </h1>

        <p>
            Manage courses, curriculum, sections and school years.
        </p>

    </div>


    <!-- =========================================
         SEARCH
    ========================================== -->

    <div class="search-box">

        <div class="search-top">

            <div class="search-label">

                <i class="bi bi-search"></i>

                <span>
                    Search for BSIS, BSCPE, BSA, BSIT, etc.
                </span>

            </div>


            <div class="course-count">

                <?= $courseCount; ?> Courses

            </div>

        </div>


        <form
            method="GET"
            action="index.php"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                placeholder="Enter course code, e.g. BSIS"
                value="<?= htmlspecialchars(
                    $search,
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>"
            >


            <button
                type="submit"
                class="search-button"
            >

                <i class="bi bi-search"></i>

                Search

            </button>


            <button
                type="button"
                class="reset-button"
                onclick="window.location='index.php'"
            >

                <i class="bi bi-arrow-clockwise"></i>

            </button>

        </form>

    </div>


    <!-- =========================================
         SEARCH RESULTS
    ========================================== -->

    <?php if ($search !== ""): ?>

        <div class="search-results">

            <h5>
                Search Results
            </h5>


            <?php if (count($searchResults) > 0): ?>

                <?php foreach (
                    $searchResults as $row
                ): ?>

                    <div class="result-item">

                        <div>

                            <div class="result-course">

                                <?= htmlspecialchars(
                                    $row["COURSE"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>

                            </div>


                            <div class="result-description">

                                <?= htmlspecialchars(
                                    $row["DESCRIPTION"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>

                            </div>

                        </div>


                        <a
                            href="course/edit.php?id=<?= (int)$row["ID"]; ?>"
                            class="manage-button"
                            style="background:#1479e9;"
                        >

                            Edit

                        </a>

                    </div>

                <?php endforeach; ?>


            <?php else: ?>

                <div class="text-muted">

                    No course found.

                </div>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <!-- =========================================
         DASHBOARD CARDS
    ========================================== -->

    <div class="dashboard-grid">


        <!-- COURSES -->

        <div class="dashboard-card blue">

            <div class="card-icon">

                <i class="bi bi-book"></i>

            </div>


            <h3>
                Courses
            </h3>


            <div class="card-number">

                <?= $courseCount; ?>

            </div>


            <a
                href="course/index.php"
                class="manage-button"
            >

                Manage Courses

            </a>

        </div>


        <!-- YEAR LEVELS -->

        <div class="dashboard-card green">

            <div class="card-icon">

                <i class="bi bi-layers"></i>

            </div>


            <h3>
                Year Levels
            </h3>


            <div class="card-number">

                <?= $yearlevelCount; ?>

            </div>


            <a
                href="yearlevel/index.php"
                class="manage-button"
            >

                Manage Year Levels

            </a>

        </div>


        <!-- SECTIONS -->

        <div class="dashboard-card yellow">

            <div class="card-icon">

                <i class="bi bi-people"></i>

            </div>


            <h3>
                Sections
            </h3>


            <div class="card-number">

                <?= $sectionCount; ?>

            </div>


            <a
                href="section/index.php"
                class="manage-button"
            >

                Manage Sections

            </a>

        </div>


        <!-- TERMS -->

        <div class="dashboard-card cyan">

            <div class="card-icon">

                <i class="bi bi-clock"></i>

            </div>


            <h3>
                Terms
            </h3>


            <div class="card-number">

                <?= $termCount; ?>

            </div>


            <a
                href="term/index.php"
                class="manage-button"
            >

                Manage Terms

            </a>

        </div>


        <!-- SCHOOL YEARS -->

        <div class="dashboard-card red">

            <div class="card-icon">

                <i class="bi bi-calendar3"></i>

            </div>


            <h3>
                School Years
            </h3>


            <div class="card-number">

                <?= $schoolyearCount; ?>

            </div>


            <a
                href="schoolyear/index.php"
                class="manage-button"
            >

                Manage School Years

            </a>

        </div>


        <!-- SUBJECTS -->

        <div class="dashboard-card dark">

            <div class="card-icon">

                <i class="bi bi-journal-bookmark"></i>

            </div>


            <h3>
                Subjects
            </h3>


            <div class="card-number">

                <?= $subjectCount; ?>

            </div>


            <a
                href="subjects/index.php"
                class="manage-button"
            >

                Manage Subjects

            </a>

        </div>


    </div>

</main>


</body>

</html>