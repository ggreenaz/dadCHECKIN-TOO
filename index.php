<?php
// Error reporting and PHP settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database configuration file
require_once './config.php';

$successMessage = '';
$errorMessage = '';
$studentData = null;

// Fetch visiting persons
$visitingPersonsQuery = "SELECT person_id, person_name FROM visiting_persons";
$visitingPersonsResult = $pdo->query($visitingPersonsQuery);

$visitingPersons = [];
if ($visitingPersonsResult->rowCount() > 0) {
    while ($row = $visitingPersonsResult->fetch()) {
        $visitingPersons[] = $row;
    }
}

// Fetch reasons for visit
$reasonsQuery = "SELECT reason_id, reason_description FROM visit_reasons";
$reasonsResult = $pdo->query($reasonsQuery);

$visitReasons = [];
if ($reasonsResult->rowCount() > 0) {
    while ($row = $reasonsResult->fetch()) {
        $visitReasons[] = $row;
    }
}

// Handle Check-In
if (isset($_POST['checkin'])) {
    $email_prefix = isset($_POST['email_prefix']) ? $_POST['email_prefix'] : '';
    $visiting_person_id = isset($_POST['name']) ? intval($_POST['name']) : 0;
    $visit_reason_id = isset($_POST['reason']) ? intval($_POST['reason']) : 0;
    $checkin_time = date('Y-m-d H:i:s');

    // Retrieve user data based on email_prefix
    $userQuery = "SELECT * FROM users WHERE email LIKE ?";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute([$email_prefix . '%']);

    if ($userStmt->rowCount() > 0) {
        $studentData = $userStmt->fetch();
        $user_id = $studentData['user_id'];

        // Insert the new visitor into the checkin_checkout table
        $insertStmt = $pdo->prepare("INSERT INTO checkin_checkout (user_id, visiting_person_id, visit_reason_id, checkin_time) VALUES (?, ?, ?, ?)");
        $insertStmt->execute([$user_id, $visiting_person_id, $visit_reason_id, $checkin_time]);

        if ($insertStmt->rowCount() > 0) {
            $successMessage = "Visitor checked in successfully!";
        } else {
            $errorMessage = "Error: " . implode(", ", $insertStmt->errorInfo());
        }
    } else {
        $errorMessage = "Error: User not found.";
    }
}

// Handle Check-Out
if (isset($_POST['checkout'])) {
    $email_prefix_out = isset($_POST['email_prefix_out']) ? $_POST['email_prefix_out'] : '';
    $checkout_time = date('Y-m-d H:i:s');

    // Retrieve user data based on email_prefix_out
    $userQuery = "SELECT * FROM users WHERE email LIKE ?";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute([$email_prefix_out . '%']);

    if ($userStmt->rowCount() > 0) {
        $studentData = $userStmt->fetch();
        $user_id = $studentData['user_id'];

        // Find the latest check-in record for this user that hasn't been checked out
        $checkinQuery = "SELECT * FROM checkin_checkout WHERE user_id = ? AND checkout_time IS NULL ORDER BY checkin_time DESC LIMIT 1";
        $checkinStmt = $pdo->prepare($checkinQuery);
        $checkinStmt->execute([$user_id]);

        if ($checkinStmt->rowCount() > 0) {
            $checkinData = $checkinStmt->fetch();

            // Update the record to set the checkout_time
            $updateQuery = "UPDATE checkin_checkout SET checkout_time = ? WHERE record_id = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([$checkout_time, $checkinData['record_id']]);

            if ($updateStmt->rowCount() > 0) {
                $successMessage = "Checkout successful!";
            } else {
                $errorMessage = "Error during checkout.";
            }
        } else {
            $errorMessage = "No active check-in found for this user.";
        }
    } else {
        $errorMessage = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Visitor Check-In and Check-Out</title>
    <link rel="stylesheet" type="text/css" href="admin/theme.php">
    <style>
        /* Center-align the edit tables */
        table.edit-table {
            width: 55%;
            margin: 0 auto;
        }

        /* Adjust table styles for existing visiting persons and visit reasons */
        table.existing-table {
            width: 50%; /* Set both tables to be 50% of the page */
            border-collapse: collapse;
            margin: 0 auto; /* Center-align the tables */
        }

        table.existing-table th,
        table.existing-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        /* Ensure uniform width for action buttons */
        table.existing-table .actions {
            width: 150px;
        }
    </style>
</head>
<body>
    <h1>Visitor Check-In and Check-Out</h1>

    <!-- Display Success and Error Messages -->
    <?php if (!empty($successMessage)): ?>
        <p><?= $successMessage ?></p>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <p><?= $errorMessage ?></p>
    <?php endif; ?>

    <!-- Form for Visitor Check-In -->
    <form method="POST" action="">
        <table class="centered-table edit-table" border="1">
            <tr>
                <td><label for="email_prefix">Email Prefix:</label></td>
                <td><input type="text" name="email_prefix" id="email_prefix" placeholder="Email Prefix" required>@yourcompany.com</td>
            </tr>
            <tr>
                <td><label for="name">Visiting (Name):</label></td>
                <td>
                    <select name="name" id="name" required style="width: auto;">
                        <option value="" disabled selected>Select Person</option>
                        <?php foreach ($visitingPersons as $person): ?>
                            <option value="<?= htmlspecialchars($person['person_id']); ?>">
                                <?= htmlspecialchars($person['person_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="reason">Reason for Visit:</label></td>
                <td>
                    <select name="reason" id="reason" required style="width: auto;">
                        <option value="" disabled selected>Select Reason</option>
                        <?php foreach ($visitReasons as $reason): ?>
                            <option value="<?= htmlspecialchars($reason['reason_id']); ?>">
                                <?= htmlspecialchars($reason['reason_description']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><center>
                    <button type="submit" name="checkin">Check-In</button>
                </center></td>
            </tr>
        </table>
    </form>

    <!-- Form for Visitor Check-Out -->
    <form method="POST" action="">
        <table class="centered-table edit-table" border="1">
            <tr>
                <td><label for="email_prefix_out">Email Prefix (for Check-Out):</label></td>
                <td><input type="text" name="email_prefix_out" id="email_prefix_out" placeholder="Email Prefix" required>@yourcompany.com</td>
            <p><tr><h2>Check-out Below</h2></tr><p>
            </tr>
            <tr>
                <td colspan="2"><center>
                    <button type="submit" name="checkout">Check-Out</button>
                </center></td>
            </tr>
        </table>
    </form>

    <!-- Display Student Data after Check-In -->
    <?php if ($studentData): ?>
        <h2>Student Information</h2>
        <a href="./" class="delete-button">Click here to finish registration</a>
        <table border="1" style="width: 60%; margin: 0 auto;">
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <!-- Add other necessary columns -->
            </tr>
            <tr>
                <td><?= htmlspecialchars($studentData['first_name']) ?></td>
                <td><?= htmlspecialchars($studentData['last_name']) ?></td>
                <td><?= htmlspecialchars($studentData['email']) ?></td>
                <!-- Display other student data -->
            </tr>
        </table>
    <?php endif; ?>
</body>
</html>
