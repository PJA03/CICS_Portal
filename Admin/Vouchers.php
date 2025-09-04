<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vouchers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <style>
        .message-container {
            width: 90%;
            height: 20px; /* Fixed height for the message area */
            margin: 10px auto;
            text-align: center;
        }
        
        .table-wrapper {
            height: 500px; /* Set a fixed height for the table container */
            overflow-y: auto; /* Enable vertical scrolling */
            width: 90%;
            margin-top: 20px;
        }

        table {
            width: 100%;
            height: 80%;
            border-collapse: collapse;
            margin-bottom: 20px;
            justify-content: center;
            align-items: center;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .message {
            color: green;
        }

        .error {
            color: red;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            width: 90%;
            gap: 20px;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            margin-bottom: 10px;
        }
    
        .action-btn {
            max-width: 200px;
            max-height: 100px;
            padding: 10px;
            border-radius: 0.5rem;
            background: black;
            color: #fbbe15;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }


        .buttons button:hover {
            background: #fbbe15;
            color: black;
        }

        body {
            margin-top: 20px;
            margin-bottom: 20px;
            align-items: center;
            justify-content: center;
        }

        h1 {
            margin: 20px 0 5px;
            font-size: 30px;
            font-weight: bolder;
        }

        .card-header h1 {
            margin: 20px 0 5px;
            font-size: 22px;
            font-weight: bolder;
            align-items: center;
            text-align: center;
            justify-content: center;
        }
        

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("../Resources/frassati-bg.jpg") no-repeat center center/cover;
            filter: blur(6px);
            z-index: -1;
            font-family: 'Poppins', sans-serif;
        }

        .card {
            background: white;
            border-radius: 10px;
            text-align: center;
            max-width: 80%;
            width: 60%;
            max-height: 80%;
            min-width: 260px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border: black 3px solid;
            display: flex;
            margin: 0 auto;
            align-items: center;
            justify-content: center;
            padding-bottom: 20px;
        }

        /* Responsive adjustments for smartphones */
        @media screen and (max-width: 500px) {
            .card {
                max-width: 95vw;
                min-width: 0;
                font-size: 14px;
            }
            .background {
                background: #cccccc;
                filter: none;
            }
        }

        .card-header {
              display: flex;
                align-items: center;
                justify-content: space-between;
                background: #fbbe15;
                border-radius: 15px 15px;
                padding: 15px;
                width: 100%;
        }
        .logo {
            height: 80px;
            width: auto;
        }

        /* Responsive adjustments for smartphones */
        @media screen and (max-width: 500px) {
            .logo {
                height: 60px;
                width: auto;
            }
        }
    </style>
</head>

<body>
    <div class="background"></div>
    <div class="card">
      <div class="card-header">
        <img src="../Resources/ust.png" alt="UST Logo" class="logo">
        <h1>College of Information and Computing Sciences</h1>
        <img src="../Resources/cics.png" alt="CICS Logo" class="logo">
      </div>      

    <h1>GUEST VOUCHERS</h1>
    
    <div class = "message-container">
    <?php
    include '../conn.php';

    // Function to generate random code
    function generateRandomCode($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        $max = strlen($characters) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $max)];
        }
        return $code;
    }

    // Function to check if code exists in the database
    function codeExists($conn, $code) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM vouchers WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'] > 0;
    }

    // Handle form submission for generating vouchers
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_vouchers'])) {
        $codes = [];
        $attempts = 0;
        $max_attempts = 100; // Prevent infinite loop
        $target_codes = 10;

        // Generate unique codes
        while (count($codes) < $target_codes && $attempts < $max_attempts) {
            $new_code = generateRandomCode();
            if (!codeExists($conn, $new_code)) {
                $codes[] = $new_code;
            }
            $attempts++;
        }

        // Insert unique codes into the database
        $success_count = 0;
        $skipped_count = 0;
        foreach ($codes as $code) {
            $stmt = $conn->prepare("INSERT INTO vouchers (code, status, date_created) VALUES (?, 'valid', NOW())");
            $stmt->bind_param("s", $code);
            if ($stmt->execute()) {
                $success_count++;
            } else {
                echo "<p class='error'>Error inserting code $code: " . $stmt->error . "</p>";
                $skipped_count++;
            }
            $stmt->close();
        }

        //make it into a pop up alert
        if ($success_count > 0) {
            echo "<p class='message'>Successfully inserted $success_count voucher(s)</p>";
        }
        if ($skipped_count > 0 || count($codes) < $target_codes) {
            $generated_count = count($codes);
            echo "<p class='error'>Generated $generated_count unique codes (some duplicates were skipped or not enough unique codes found)</p>";
        }
    }

    // Display delete feedback
    if (isset($_GET['message'])) {
        echo "<p class='message'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
    if (isset($_GET['error'])) {
        echo "<p class='error'>" . htmlspecialchars($_GET['error']) . "</p>";
    }
    ?>
    </div> 

    <?php
    // Handle sorting
    $sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'ID';
    $sort_order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
    $valid_columns = ['ID', 'status', 'date_created'];
    
    // Validate sort column
    if (!in_array($sort_column, $valid_columns)) {
        $sort_column = 'ID';
    }

    // Toggle sort order for next click
    $next_order = $sort_order === 'ASC' ? 'desc' : 'asc';

    // Fetch vouchers with sorting
    $query = "SELECT * FROM vouchers ORDER BY " . $sort_column . " " . $sort_order;
    $result = $conn->query($query);
    if (!$result) {
        echo "<p class='error'>Error fetching vouchers: " . $conn->error . "</p>";
    }
    ?>

    <div class = "table-wrapper">
    <table>
        <tr>
            <th>Voucher ID</th>
            <th>Code</th>
            <th>Status</th>
            <th>Created At</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . (isset($row['ID']) ? htmlspecialchars($row['ID']) : 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['code']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['date_created']) . "</td>";
                echo "<td>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No vouchers found</td></tr>";
        }
        $conn->close();
        ?>
    </table>
    </div>

    <div class="buttons">
    <form method="post">
        <button type="submit" name="generate_vouchers" class = "action-btn">Create Voucher</button>
    </form>
        <button onclick="window.location.href='Vouchers.php?sort=status&order=<?php echo $sort_column === 'status' ? $next_order : 'asc'; ?>'" class = "action-btn">
            Sort by Status (<?php echo $sort_column === 'status' ? ($sort_order === 'ASC' ? '↑' : '↓') : '-'; ?>)
        </button>
        <button onclick="window.location.href='Vouchers.php?sort=date_created&order=<?php echo $sort_column === 'date_created' ? $next_order : 'asc'; ?>'" class = "action-btn">
            Sort by Created At (<?php echo $sort_column === 'date_created' ? ($sort_order === 'ASC' ? '↑' : '↓') : '-'; ?>)
        </button>
    </div>

</body>
</html>