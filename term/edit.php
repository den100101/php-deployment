<?php

require_once "../db.php";

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    die("Invalid Term ID.");
}

/* GET TERM DATA */
$stmt = $conn->prepare("
    SELECT ID, TERM, DESCRIPTION
    FROM tbl_term
    WHERE ID = ?
");

if (!$stmt) {
    die("Select preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Term not found.");
}

$row = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Term</title>

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

        .container {
            width: 500px;
            max-width: 90%;
            margin: 60px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.10);
        }

        h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #1261a0;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #1261a0;
        }

        .buttons {
            display: flex;
            gap: 10px;
        }

        button,
        .cancel {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background: #1261a0;
            color: white;
        }

        button:hover {
            background: #0d4f84;
        }

        .cancel {
            background: #6c757d;
            color: white;
        }

        .cancel:hover {
            background: #545b62;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <h2>Edit Term</h2>

        <form action="save.php" method="POST">

            <!-- TERM ID -->
            <input
                type="hidden"
                name="id"
                value="<?php echo $row['ID']; ?>"
            >

            <label for="term">
                Term
            </label>

            <input
                type="text"
                id="term"
                name="term"
                value="<?php echo htmlspecialchars($row['TERM']); ?>"
                placeholder="Example: 1ST SEM"
                required
            >

            <label for="description">
                Description
            </label>

            <input
                type="text"
                id="description"
                name="description"
                value="<?php echo htmlspecialchars($row['DESCRIPTION']); ?>"
                placeholder="Example: FIRST SEMESTER"
                required
            >

            <div class="buttons">

                <button type="submit">
                    Update Term
                </button>

                <a href="cancel.php" class="cancel">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>