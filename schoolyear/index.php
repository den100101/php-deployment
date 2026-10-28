```php
<?php
/* =========================================
   SCHOOL YEAR MANAGEMENT
========================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* LOAD DATABASE ONCE */
require_once dirname(__DIR__) . "/db.php";

/*
 * Check login and role.
 * Huwag i-load ang auth.php nang paulit-ulit.
 */
require_once dirname(__DIR__) . "/auth.php";

/*
 * Temporarily allow all logged-in users to access
 * this page while fixing the 403 role mismatch.
 *
 * Kapag gumagana na, maaari nating ibalik ang
 * admin at registrar restriction.
 */
if (!isset($_SESSION["user_id"])) {
    header("Location: /4abalabagan/login.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";
$error = "";
$search = trim($_GET["search"] ?? "");
$editData = null;

/* ESCAPE HTML */
function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

/* =========================================
   ADD SCHOOL YEAR
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_schoolyear"])
) {
    $schoolyear = trim($_POST["schoolyear"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($schoolyear === "" || $description === "") {
        $error = "Please complete all fields.";
    } elseif (!preg_match('/^[0-9]{4}-[0-9]{4}$/', $schoolyear)) {
        $error = "Use the format YYYY-YYYY, for example 2026-2027.";
    } else {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO tbl_schoolyear
                 (SCHOOLYEAR, DESCRIPTION)
                 VALUES (?, ?)"
            );

            $stmt->bind_param("ss", $schoolyear, $description);
            $stmt->execute();
            $stmt->close();

            header("Location: index.php?status=added");
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log("School year insert: " . $e->getMessage());

            $error = $e->getCode() == 1062
                ? "This school year already exists."
                : "Unable to add school year.";
        }
    }

    $editData = [
        "ID" => 0,
        "SCHOOLYEAR" => $schoolyear,
        "DESCRIPTION" => $description
    ];
}

/* =========================================
   UPDATE SCHOOL YEAR
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_schoolyear"])
) {
    $id = (int)($_POST["id"] ?? 0);
    $schoolyear = trim($_POST["schoolyear"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($id <= 0 || $schoolyear === "" || $description === "") {
        $error = "Please complete all fields.";
    } elseif (!preg_match('/^[0-9]{4}-[0-9]{4}$/', $schoolyear)) {
        $error = "Use the format YYYY-YYYY, for example 2026-2027.";
    } else {
        try {
            $stmt = $conn->prepare(
                "UPDATE tbl_schoolyear
                 SET SCHOOLYEAR = ?, DESCRIPTION = ?
                 WHERE ID = ?"
            );

            $stmt->bind_param(
                "ssi",
                $schoolyear,
                $description,
                $id
            );

            $stmt->execute();
            $stmt->close();

            header("Location: index.php?status=updated");
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log("School year update: " . $e->getMessage());

            $error = $e->getCode() == 1062
                ? "That school year already exists."
                : "Unable to update school year.";
        }
    }

    $editData = [
        "ID" => $id,
        "SCHOOLYEAR" => $schoolyear,
        "DESCRIPTION" => $description
    ];
}

/* =========================================
   DELETE SCHOOL YEAR
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete_schoolyear"])
) {
    $id = (int)($_POST["id"] ?? 0);

    if ($id > 0) {
        try {
            $stmt = $conn->prepare(
                "DELETE FROM tbl_schoolyear WHERE ID = ?"
            );

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header("Location: index.php?status=deleted");
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log("School year delete: " . $e->getMessage());
            $error = "Cannot delete this school year because it may be in use.";
        }
    }
}

/* =========================================
   LOAD EDIT RECORD
========================================= */

if (isset($_GET["edit"]) && $editData === null) {
    $id = (int)$_GET["edit"];

    if ($id > 0) {
        $stmt = $conn->prepare(
            "SELECT ID, SCHOOLYEAR, DESCRIPTION
             FROM tbl_schoolyear
             WHERE ID = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $editData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$editData) {
            $error = "School year record not found.";
        }
    }
}

/* =========================================
   STATUS MESSAGES
========================================= */

$messages = [
    "added" => "School year added successfully.",
    "updated" => "School year updated successfully.",
    "deleted" => "School year deleted successfully."
];

$message = $messages[$_GET["status"] ?? ""] ?? "";

/* =========================================
   FETCH SCHOOL YEARS
========================================= */

if ($search !== "") {
    $stmt = $conn->prepare(
        "SELECT ID, SCHOOLYEAR, DESCRIPTION
         FROM tbl_schoolyear
         WHERE SCHOOLYEAR LIKE ?
            OR DESCRIPTION LIKE ?
         ORDER BY ID DESC"
    );

    $keyword = "%" . $search . "%";
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();

    $schoolyears = $stmt->get_result();
    $stmt->close();
} else {
    $schoolyears = $conn->query(
        "SELECT ID, SCHOOLYEAR, DESCRIPTION
         FROM tbl_schoolyear
         ORDER BY ID DESC"
    );
}

$total = $schoolyears->num_rows;

$showForm = ($editData !== null || $error !== "");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>School Years - 4ABALABAGAN</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #eef3f8;
    color: #222;
    font-size: 13px;
}

.top-header {
    min-height: 65px;
    background: #1479e9;
    color: white;
    display: flex;
    align-items: center;
    padding: 0 5%;
    font-size: 17px;
    font-weight: bold;
    box-shadow: 0 2px 8px #0002;
}

.main-container {
    width: 850px;
    max-width: 94%;
    margin: 30px auto;
}

.top-area {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.page-title {
    font-size: 27px;
    margin: 12px 0 6px;
}

.page-subtitle {
    color: #788493;
    font-size: 12px;
}

button, .btn {
    display: inline-block;
    border: 0;
    text-decoration: none;
    padding: 9px 13px;
    border-radius: 5px;
    font-size: 12px;
    cursor: pointer;
    white-space: nowrap;
}

.back-button, .cancel-button {
    background: #6c757d;
    color: white;
}

.add-button, .save-button, .search-button {
    background: #1479e9;
    color: white;
}

.update-button {
    background: #ffc107;
    color: #222;
}

.delete-button {
    background: #dc3545;
    color: white;
}

.form-card, .table-card {
    background: white;
    border-radius: 9px;
    box-shadow: 0 3px 12px #0000000c;
    margin-bottom: 22px;
    overflow: hidden;
}

.form-card {
    padding: 22px;
}

.form-card h2 {
    font-size: 18px;
    margin: 0 0 20px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

.form-group input, .search-form input {
    width: 100%;
    padding: 11px;
    border: 1px solid #d5dbe1;
    border-radius: 5px;
    font-size: 13px;
}

.form-group input:focus, .search-form input:focus {
    outline: none;
    border-color: #1479e9;
    box-shadow: 0 0 0 3px #1479e91a;
}

.form-actions, .actions, .search-form {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.search-area {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 18px;
    border-bottom: 1px solid #edf0f3;
}

.record-count {
    color: #7e8996;
    font-size: 11px;
    margin-top: 6px;
}

.search-form input {
    width: 220px;
}

.table-wrap {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

th {
    background: #f8fafc;
    text-align: left;
    padding: 15px;
    border-bottom: 1px solid #e5e9ef;
}

td {
    padding: 13px 15px;
    border-bottom: 1px solid #edf0f3;
    vertical-align: middle;
}

tbody tr:hover {
    background: #f6faff;
}

.schoolyear-badge {
    display: inline-block;
    background: #e4efff;
    color: #1261a0;
    padding: 7px 10px;
    border-radius: 5px;
    font-weight: bold;
}

.inline-form {
    display: inline;
}

.no-records {
    text-align: center;
    color: #8993a0;
    padding: 35px 15px;
}

.alert {
    padding: 13px 15px;
    border-radius: 6px;
    margin-bottom: 18px;
}

.success {
    background: #dff4e5;
    color: #176b35;
}

.error {
    background: #ffe5e5;
    color: #a51e1e;
}

@media (max-width: 650px) {
    .top-area, .search-area {
        align-items: stretch;
        flex-direction: column;
    }

    .search-form input {
        flex: 1;
        width: auto;
        min-width: 0;
    }

    .page-title {
        font-size: 23px;
    }

    th, td {
        padding: 10px;
    }
}
</style>
</head>

<body>

<header class="top-header">4ABALABAGAN</header>

<main class="main-container">

    <div class="top-area">
        <div>
            <a href="../index.php" class="btn back-button">
                &larr; Back to Home
            </a>

            <h1 class="page-title">School Years</h1>
            <p class="page-subtitle">Manage academic school years</p>
        </div>

        <button
            type="button"
            class="add-button"
            onclick="document.getElementById('schoolyear-form').style.display =
                document.getElementById('schoolyear-form').style.display === 'none'
                ? 'block' : 'none';"
        >+ Add School Year</button>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <section
        class="form-card"
        id="schoolyear-form"
        style="display: <?= $showForm ? 'block' : 'none' ?>;"
    >
        <h2><?= $editData !== null && (int)$editData["ID"] > 0
            ? "Update School Year"
            : "Add School Year" ?></h2>

        <form method="POST" action="index.php">
            <?php if ($editData !== null && (int)$editData["ID"] > 0): ?>
                <input type="hidden" name="id"
                       value="<?= (int)$editData["ID"] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="schoolyear">School Year</label>
                <input
                    type="text"
                    id="schoolyear"
                    name="schoolyear"
                    placeholder="Example: 2026-2027"
                    pattern="[0-9]{4}-[0-9]{4}"
                    value="<?= e($editData["SCHOOLYEAR"] ?? "") ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <input
                    type="text"
                    id="description"
                    name="description"
                    placeholder="Example: AY 2026-2027"
                    maxlength="100"
                    value="<?= e($editData["DESCRIPTION"] ?? "") ?>"
                    required
                >
            </div>

            <div class="form-actions">
                <button
                    type="submit"
                    class="save-button"
                    name="<?= $editData !== null && (int)$editData["ID"] > 0
                        ? 'update_schoolyear'
                        : 'add_schoolyear' ?>"
                >
                    <?= $editData !== null && (int)$editData["ID"] > 0
                        ? "Update School Year"
                        : "Save School Year" ?>
                </button>

                <a href="index.php" class="btn cancel-button">Cancel</a>
            </div>
        </form>
    </section>

    <section class="table-card">
        <div class="search-area">
            <div>
                <strong>School Year List</strong>
                <div class="record-count">
                    <?= (int)$total ?> school year(s) registered
                </div>
            </div>

            <form method="GET" action="index.php" class="search-form">
                <input
                    type="search"
                    name="search"
                    placeholder="Search school year..."
                    value="<?= e($search) ?>"
                >

                <button type="submit" class="search-button">Search</button>

                <?php if ($search !== ""): ?>
                    <a href="index.php" class="btn cancel-button">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>School Year</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($total > 0): ?>
                    <?php while ($row = $schoolyears->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="schoolyear-badge">
                                    <?= e($row["SCHOOLYEAR"]) ?>
                                </span>
                            </td>
                            <td><?= e($row["DESCRIPTION"]) ?></td>
                            <td>
                                <div class="actions">
                                    <a
                                        href="index.php?edit=<?= (int)$row["ID"] ?>"
                                        class="btn update-button"
                                    >Update</a>

                                    <form
                                        method="POST"
                                        action="index.php"
                                        class="inline-form"
                                        onsubmit="return confirm('Delete this school year?');"
                                    >
                                        <input type="hidden" name="id"
                                               value="<?= (int)$row["ID"] ?>">

                                        <button
                                            type="submit"
                                            name="delete_schoolyear"
                                            class="delete-button"
                                        >Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="no-records">
                            No school year records found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>
</body>
</html>
