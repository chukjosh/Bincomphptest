<?php
include 'db_connection.php';

// Fetch LGAs for the select box
$lga_query = "SELECT lga_id, lga_name FROM lga";
$lga_result = $conn->query($lga_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LGA Total Results</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        .navbar {
            width: 100%;
            background-color: #007bff;
            padding: 10px;
            display: flex;
            justify-content: center;
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {background-color: #f1f1f1}
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .dropdown:hover .dropbtn {
            background-color: #0056b3;
        }
        .active {
            background-color: #0056b3 !important;
            color: #fff !important;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 600px;
            margin-bottom: 20px;
        }
        /* CSS to style the table and form */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 600px;
            margin-bottom: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            margin: 10px 0 5px;
        }
        select, input[type="submit"] {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="dropdown">
            <button class="dropbtn">Navigate</button>
            <div class="dropdown-content">
                <a href="individual_result.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'individual_results.php' ? 'active' : ''; ?>">Individual Result</a>
                <a href="lga_result.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'summed_results.php' ? 'active' : ''; ?>">LGA Total Results</a>
                <a href="add_polling_unit.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_polling_unit.php' ? 'active' : ''; ?>">Add Polling Unit</a>
            </div>
        </div>
    </div>
    <div class="container">
        <h1>Total Summed Results by LGA</h1>
        <form method="get" action="">
            <label for="lga_id">Select LGA:</label>
            <select name="lga_id" id="lga_id" class="select2" required>
                <option value="">Select LGA</option>
                <?php
                if ($lga_result->num_rows > 0) {
                    while($lga_row = $lga_result->fetch_assoc()) {
                        echo "<option value='{$lga_row['lga_id']}'>{$lga_row['lga_name']}</option>";
                    }
                }
                ?>
            </select>
            <input type="submit" value="Get Results">
        </form>
    </div>

    <?php
    if (isset($_GET['lga_id'])) {
        $lga_id = $_GET['lga_id'];

        $result_query = "
            SELECT l.lga_name, w.ward_name, pu.polling_unit_name, apr.party_abbreviation, SUM(apr.party_score) as total_score 
            FROM polling_unit pu
            JOIN ward w ON pu.ward_id = w.ward_id
            JOIN lga l ON pu.lga_id = l.lga_id
            JOIN announced_pu_results apr ON pu.polling_unit_id = apr.polling_unit_uniqueid
            WHERE pu.lga_id = '$lga_id'
            GROUP BY apr.party_abbreviation
        ";
        $result = $conn->query($result_query);

        if ($result->num_rows > 0) {
            echo "<div class='container'>";
            echo "<h2>Total Results for LGA</h2>";
            echo "<table>";
            echo "<tr><th>Party</th><th>Total Score</th></tr>";

            while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['party_abbreviation']}</td>
                    <td>{$row['total_score']}</td>
                </tr>";
            }
            echo "</table>";
            echo "</div>";
        } else {
            echo "<div class='container'><p>No results found for the selected LGA.</p></div>";
        }
    }
    ?>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
</body>
</html>
