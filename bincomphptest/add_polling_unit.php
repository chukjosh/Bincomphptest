<?php
include 'db_connection.php';

// Fetch wards, LGAs, and parties for the select boxes
$ward_query = "SELECT ward_id, ward_name FROM ward";
$ward_result = $conn->query($ward_query);

$lga_query = "SELECT lga_id, lga_name FROM lga WHERE state_id = 25";
$lga_result = $conn->query($lga_query);

$party_query = "SELECT partyname FROM party";
$party_result = $conn->query($party_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $polling_unit_name = $_POST['polling_unit_name'];
    $ward_id = $_POST['ward_id'];
    $lga_id = $_POST['lga_id'];
    $state_id = 25; // Assuming only Delta State

    // Prepare an array to collect party scores
    $parties = [];

    foreach ($_POST['party_scores'] as $partyname => $score) {
        $parties[] = ['name' => $partyname, 'score' => $score];
    }

    if ($polling_unit_name && $ward_id && $lga_id && !empty($parties)) {
        // Insert polling unit into the database
        $insert_query = "INSERT INTO polling_unit (polling_unit_name, ward_id, lga_id) 
        VALUES ('$polling_unit_name', '$ward_id', '$lga_id')";
        if ($conn->query($insert_query) === TRUE) {
            $polling_unit_id = $conn->insert_id; // Get the last inserted polling unit ID

            // Insert each party result into the database
            foreach ($parties as $party) {
                $party_name = $party['name'];
                $party_score = $party['score'];
                $insert_party_query = "INSERT INTO announced_pu_results (polling_unit_uniqueid, party_abbreviation, party_score) 
                                       VALUES ('$polling_unit_id', '$party_name', '$party_score')";
                $conn->query($insert_party_query);
            }

            echo "New polling unit and results added successfully.";
        } else {
            echo "Error: " . $insert_query . "<br>" . $conn->error;
        }
    } else {
        echo "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Polling Unit</title>
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
        /* CSS to style the form */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            /* height: 100vh; */
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 600px;
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
        select, input[type="text"], input[type="submit"], .add-button {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
        input[type="submit"], .add-button {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover, .add-button:hover {
            background-color: #0056b3;
        }
        .party-score {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .party-score label {
            flex: 1;
        }
        .party-score input[type="number"] {
            flex: 2;
            margin-left: 10px;
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
        <h1>Add Polling Unit</h1>
        <form method="post" action="">
            <label for="polling_unit_name">Polling Unit Name:</label>
            <input type="text" name="polling_unit_name" id="polling_unit_name" required>

            <label for="ward_id">Select Ward:</label>
            <select name="ward_id" id="ward_id" class="select2" required>
                <option value="">Select Ward</option>
                <?php
                if ($ward_result->num_rows > 0) {
                    while($ward_row = $ward_result->fetch_assoc()) {
                        echo "<option value='{$ward_row['ward_id']}'>{$ward_row['ward_name']}</option>";
                    }
                }
                ?>
            </select>

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

            <div id="party-fields" class="party-fields">
                <?php
                if ($party_result->num_rows > 0) {
                    while($party_row = $party_result->fetch_assoc()) {
                        $partyname = $party_row['partyname'];
                        echo "<div class='party-score'>
                                <label for='party_score[$partyname]'>$partyname:</label>
                                <input type='number' name='party_scores[$partyname]' required>
                              </div>";
                    }
                }
                ?>
            </div>

            <input type="submit" value="Add Polling Unit">
        </form>
    </div>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
</body>
</html>
