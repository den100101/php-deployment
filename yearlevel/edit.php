<?php

require_once "../db.php";

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    die("Invalid Year Level ID.");
}

/* GET YEAR LEVEL DATA */
$stmt = $conn->prepare("
    SELECT ID, YEARLEVEL, DESCRIPTION
    FROM tbl_yearlevel
    WHERE ID = ?
");

if (!$stmt) {
    die("Select preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Year Level not found.");
}

$row = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Year Level</title>

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

        <h2>Edit Year Level</h2>

        <form action="save.php" method="POST">

            <!-- YEAR LEVEL ID -->
            <input
                type="hidden"
                name="id"
                value="<?php echo $row['ID']; ?>"
            >

            <label for="yearlevel">
                Year Level
            </label>

            <input
                type="text"
                id="yearlevel"
                name="yearlevel"
                value="<?php echo htmlspecialchars($row['YEARLEVEL']); ?>"
                placeholder="Example: 1ST"
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
                placeholder="Example: FIRST YEAR"
                required
            >

            <div class="buttons">

                <button type="submit">
                    Update Year Level
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