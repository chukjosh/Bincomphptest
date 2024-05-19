<?php
include 'db_connection.php';

// Fetch polling units
$pu_query = "SELECT uniqueid, polling_unit_name FROM polling_unit WHERE state_id = 25";
$pu_result = $conn->query($pu_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Polling Unit Results</title>
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
        /* CSS to style the form and table */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
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
            overflow-x: visible;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
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
        <h1>Results for Individual Polling Unit</h1>
        <?php
            $state_id = 25; // Delta State

            // Query to get detailed results for the selected polling unit
            $results_query = "SELECT pu.polling_unit_name, w.ward_name, l.lga_name, apr.party_abbreviation, apr.party_score 
                              FROM announced_pu_results apr
                              JOIN polling_unit pu ON apr.polling_unit_uniqueid = pu.uniqueid
                              JOIN ward w ON pu.ward_id = w.ward_id
                              JOIN lga l ON pu.lga_id = l.lga_id
                              WHERE l.state_id = $state_id";
            $results_result = $conn->query($results_query);

            if ($results_result->num_rows > 0) {
                echo "<table>
                        <tr>
                            <th>Serial Number</th>
                            <th>Polling Unit Name</th>
                            <th>Ward Name</th>
                            <th>Local Government Name</th>
                            <th>Party</th>
                            <th>Score</th>
                        </tr>";
                $serial_number = 1;
                while($results_row = $results_result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$serial_number}</td>
                            <td>{$results_row['polling_unit_name']}</td>
                            <td>{$results_row['ward_name']}</td>
                            <td>{$results_row['lga_name']}</td>
                            <td>{$results_row['party_abbreviation']}</td>
                            <td>{$results_row['party_score']}</td>
                          </tr>";
                    $serial_number++;
                }
                echo "</table>";
            } else {
                echo "No results found.";
            }
        $conn->close();
        ?>
    </div>
</body>
</html>
