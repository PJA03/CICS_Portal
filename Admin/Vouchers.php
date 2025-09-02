<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vouchers</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
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
            margin: 10px 0;
            padding: 10px;
            color: green;
        }
        .error {
            color: red;
        }
        .sort-buttons {
            margin: 10px 0;
        }
        .sort-buttons button {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Guest Vouchers</h1>
    
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

    <form method="post">
        <button type="submit" name="generate_vouchers">Create Voucher</button>
    </form>

    <div class="sort-buttons">
        <button onclick="window.location.href='Vouchers.php?sort=status&order=<?php echo $sort_column === 'status' ? $next_order : 'asc'; ?>'">
            Sort by Status (<?php echo $sort_column === 'status' ? ($sort_order === 'ASC' ? '↑' : '↓') : '-'; ?>)
        </button>
        <button onclick="window.location.href='Vouchers.php?sort=date_created&order=<?php echo $sort_column === 'date_created' ? $next_order : 'asc'; ?>'">
            Sort by Created At (<?php echo $sort_column === 'date_created' ? ($sort_order === 'ASC' ? '↑' : '↓') : '-'; ?>)
        </button>
    </div>

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
</body>
</html>