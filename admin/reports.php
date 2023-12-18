<?php
// Include the database configuration file (assuming it's in the admin directory)
require_once '../config.php';  // Adjust the path if needed

// Establish a database connection
try {
    // Include your database connection code here
    // Example:
    // $pdo = new PDO("mysql:host=localhost;dbname=your_database_name", "your_username", "your_password");
    // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo = new PDO("mysql:host=localhost;dbname=dblgiqjqmf8xh5", "u5qyyg6q4wy1z", "Jxg8dc$$");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: Database connection failed. " . $e->getMessage());
}

// Fetch the list of checked-in visitors along with the person visited and the reason
$checkedInVisitorsQuery = "SELECT users.first_name, users.last_name, checkin_checkout.checkin_time, checkin_checkout.checkout_time,
                           visiting_persons.person_name AS person_visited,
                           visit_reasons.reason_description AS reason
                           FROM checkin_checkout
                           INNER JOIN users ON checkin_checkout.user_id = users.user_id
                           LEFT JOIN visiting_persons ON checkin_checkout.visiting_person_id = visiting_persons.person_id
                           LEFT JOIN visit_reasons ON checkin_checkout.visit_reason_id = visit_reasons.reason_id
                           ORDER BY checkin_checkout.checkin_time DESC";

try {
    $stmt = $pdo->prepare($checkedInVisitorsQuery);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: Failed to fetch checked-in visitors. " . $e->getMessage());
}

// Function to fetch records with prepared statements
function fetchRecords($conn, $condition = '', $params = []) {
    // Define your SQL query for fetching records
    $sql = "SELECT users.first_name, users.last_name, checkin_checkout.checkin_time, checkin_checkout.checkout_time,
            visiting_persons.person_name AS person_visited,
            visit_reasons.reason_description AS reason
            FROM checkin_checkout
            INNER JOIN users ON checkin_checkout.user_id = users.user_id
            LEFT JOIN visiting_persons ON checkin_checkout.visiting_person_id = visiting_persons.person_id
            LEFT JOIN visit_reasons ON checkin_checkout.visit_reason_id = visit_reasons.reason_id";
    if ($condition) {
        $sql .= " " . $condition;
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error in prepare statement: " . $conn->errorInfo()[2]);
    }

    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }

    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $records;
}

// CSV generation function
function generateCSV($records) {
    $delimiter = ",";
    $newline = "\r\n";
    $csvContent = "First Name,Last Name,Reason,Check-In Time,Check-Out Time" . $newline;
    foreach ($records as $row) {
        $csvContent .= implode($delimiter, [
            $row['first_name'],
            $row['last_name'],
            $row['reason'],
            $row['checkin_time'],
            $row['checkout_time']
        ]) . $newline;
    }

    return $csvContent;
}

// ... Rest of the code for processing and handling CSV download requests ...

// Handle CSV download request
if (isset($_POST['download_report']) && !empty($records)) {
    $csvContent = generateCSV($records);
    header("Content-Type: application/csv");
    header("Content-Disposition: attachment; filename=\"report.csv\"");
    echo $csvContent;
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checked-In Visitors</title>
    <link rel="stylesheet" type="text/css" href="theme.php">
</head>
<body>
    <h1>Checked-In Visitors</h1>
    <img src="../img/dnd-project-sm-logo.png">
    <h2><a href="./" class="button">Return to Admin Dashboard</a></h2>
    <form method="POST">
        <input type="submit" name="report_today" value="Get Reports for Today" class="button">
        
        <!-- Add the date range selection here -->
        <label for="month">Month:</label>
        <select id="month" name="month" required class="select-style">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo $i == date('m') ? 'selected' : ''; ?>>
                    <?php echo date('F', mktime(0, 0, 0, $i, 10)); ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="year">Year:</label>
        <select id="year" name="year" required class="select-style">
            <?php
            $currentYear = date('Y');
            for ($i = $currentYear; $i <= $currentYear + 5; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo $i == $currentYear ? 'selected' : ''; ?>>
                    <?php echo $i; ?>
                </option>
            <?php endfor; ?>
        </select>

        <input type="submit" name="get_report" value="Get Report" class="button">

        <input type="text" name="search" placeholder="Search by Name">
        <input type="submit" name="search_submit" value="Search" class="button">

        <!-- Button for CSV download -->
        <input type="submit" name="download_report" value="Download CSV" class="delete-button">
    </form>

    <table border="1">
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Person Visited</th>
            <th>Reason</th>
            <th>Check-in Time</th>
            <th>Check-out Time</th>
        </tr>
        <?php foreach ($records as $visitor): ?>
            <tr>
                <td><?= htmlspecialchars($visitor['first_name']) ?></td>
                <td><?= htmlspecialchars($visitor['last_name']) ?></td>
                <td><?= isset($visitor['person_visited']) ? htmlspecialchars($visitor['person_visited']) : '' ?></td>
                <td><?= isset($visitor['reason']) ? htmlspecialchars($visitor['reason']) : '' ?></td>
                <td><?= htmlspecialchars($visitor['checkin_time']) ?></td>
                <td><?= $visitor['checkout_time'] ? htmlspecialchars($visitor['checkout_time']) : 'Not Checked Out' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
