<?php
// Include the database configuration file (assuming it's in the admin directory)
require_once '../config.php';

// Establish a database connection (assuming your config.php sets up the $pdo variable)
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: Database connection failed. " . $e->getMessage());
}


// Initialize variables
$successMessage = '';
$errorMessage = '';
$editPersonMode = false;
$editReasonMode = false;
$editPersonId = 0;
$editReasonId = 0;
$editPersonRow = [];
$editReasonRow = [];

// Handle Delete Reason
if (isset($_POST['delete_reason'])) {
    $idToDelete = $_POST['reason_id'];


// Check if there are any visitors associated with this reason
$stmtCheckVisitors = $pdo->prepare("SELECT COUNT(*) as count FROM visit_reasons WHERE reason_id = ?");
$stmtCheckVisitors->execute([$idToDelete]);
    $result = $stmtCheckVisitors->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];

    if ($count === 0) {
        // No visitors associated, proceed with deletion
        $stmtDelete = $pdo->prepare("DELETE FROM visit_reasons WHERE reason_id = ?");
        $stmtDelete->execute([$idToDelete]);
        $successMessage .= " Reason deleted successfully.";
    } else {
        $errorMessage .= " There are visitors associated with this reason. ";
        $errorMessage .= "<form method='POST'>";
        $errorMessage .= "<input type='hidden' name='reason_id' value='$idToDelete'>";
        $errorMessage .= "<button type='submit' name='delete_reason_anyway'>Delete Anyway</button>";
        $errorMessage .= "</form>";
    }
}

// Handle Delete Reason Anyway
if (isset($_POST['delete_reason_anyway'])) {
    $idToDelete = $_POST['reason_id'];

    // Proceed with deletion even if visitors are associated
    $stmtDelete = $pdo->prepare("DELETE FROM visit_reasons WHERE reason_id = ?");
    $stmtDelete->execute([$idToDelete]);
    $successMessage .= " Reason deleted successfully (even with associated visitors).";
}

// Handle Edit Reason
if (isset($_POST['edit_reason'])) {
    $editReasonMode = true;
    $editReasonId = $_POST['reason_id'];
    $stmt = $pdo->prepare("SELECT reason_description FROM visit_reasons WHERE reason_id = ?");
    $stmt->execute([$editReasonId]);
    $editReasonRow = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle Update Reason
if (isset($_POST['update_reason'])) {
    $reasonIdToUpdate = $_POST['reason_id'];
    $updatedDescription = $_POST['updated_reason_description'];
    $stmt = $pdo->prepare("UPDATE visit_reasons SET reason_description = ? WHERE reason_id = ?");
    $stmt->execute([$updatedDescription, $reasonIdToUpdate]);
    $successMessage .= " Reason updated successfully.";
    $editReasonMode = false;
}

// Handle Delete Person
if (isset($_POST['delete_person'])) {
    $idToDelete = $_POST['person_id'];
    $stmt = $pdo->prepare("DELETE FROM visiting_persons WHERE person_id = ?");
    $stmt->execute([$idToDelete]);
    $successMessage .= " Person deleted successfully.";
}

// Handle Edit Person
if (isset($_POST['edit_person'])) {
    $editPersonMode = true;
    $editPersonId = $_POST['person_id'];
    $stmt = $pdo->prepare("SELECT person_name FROM visiting_persons WHERE person_id = ?");
    $stmt->execute([$editPersonId]);
    $editPersonRow = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle Update Person
if (isset($_POST['update_person'])) {
    $personIdToUpdate = $_POST['person_id'];
    $updatedName = $_POST['updated_person_name'];
    $stmt = $pdo->prepare("UPDATE visiting_persons SET person_name = ? WHERE person_id = ?");
    $stmt->execute([$updatedName, $personIdToUpdate]);
    $successMessage .= " Person updated successfully.";
    $editPersonMode = false;
}

// Handle adding new visiting person and new visit reason
if (isset($_POST['add_new'])) {
    if (!empty($_POST['new_person_name'])) {
        $newPersonName = $_POST['new_person_name'];
        $stmt = $pdo->prepare("INSERT INTO visiting_persons (person_name) VALUES (?)");
        $stmt->execute([$newPersonName]);
        $successMessage .= " New person added successfully.";
    }

    if (!empty($_POST['new_reason_description'])) {
        $newReasonDescription = $_POST['new_reason_description'];
        $stmt = $pdo->prepare("INSERT INTO visit_reasons (reason_description) VALUES (?)");
        $stmt->execute([$newReasonDescription]);
        $successMessage .= " New reason added successfully.";
    }
}

// Fetching existing visiting persons and reasons for visit
$visitingPersonsQuery = "SELECT * FROM visiting_persons";
$visitingPersonsResult = $pdo->query($visitingPersonsQuery);

$visitReasonsQuery = "SELECT * FROM visit_reasons";
$visitReasonsResult = $pdo->query($visitReasonsQuery);

// Close the database connection
$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Management System</title>
    <link rel="stylesheet" type="text/css" href="theme.php">
    <style>
        /* Center-align the edit tables */
        table.edit-table {
            width: 40%;
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
    <h1>Visitor Management System</h1>
    <img src="../img/dnd-project-sm-logo.png">
    <h2><a href="./" class="button">Return to Admin Dashboard</a></h2>

    <!-- Edit existing reason -->
    <?php if ($editReasonMode) : ?>
        <h2><b>Edit Reason</b></h2>
        <table class="centered-table edit-table">
            <tr>
                <th>Edit Reason</th>
            </tr>
            <tr>
                <td>
                    <form method="POST">
                        <input type="hidden" name="reason_id" value="<?php echo $editReasonId; ?>">
                        <label for="updated_reason_description">Updated Description:</label>
                        <input type="text" name="updated_reason_description" value="<?php echo $editReasonRow['reason_description']; ?>" required>
                        <button type="submit" name="update_reason">Update</button>
                    </form>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <!-- Edit existing person -->
    <?php if ($editPersonMode) : ?>
        <h2><b>Edit Person</b></h2>
        <table class="centered-table edit-table">
            <tr>
                <th>Edit Person</th>
            </tr>
            <tr>
                <td>
                    <form method="POST">
                        <input type="hidden" name="person_id" value="<?php echo $editPersonId; ?>">
                        <label for="updated_person_name">Updated Name:</label>
                        <input type="text" name="updated_person_name" value="<?php echo $editPersonRow['person_name']; ?>" required>
                        <button type="submit" name="update_person">Update</button>
                    </form>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <!-- Display success and error messages -->
    <?php if (!empty($successMessage)) : ?>
        <p style="color: green;"><?php echo $successMessage; ?></p>
    <?php endif; ?>
    <?php if (!empty($errorMessage)) : ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <!-- Add new visitor and reason form -->
    <h2><b>Add New Visiting Person or Reason for Visit</b></h2>
    <form method="POST">
        <input type="text" name="new_person_name" placeholder="Enter new person name">
        <input type="text" name="new_reason_description" placeholder="Enter new reason for visit">
        <input type="submit" name="add_new" value="Add New">
    </form>

    <!-- List of existing visiting persons -->
    <h2>Existing Visiting Persons</h2>
    <table class="existing-table">
        <tr>
            <th>Name</th>
            <th class="actions">Actions</th>
        </tr>
        <?php while ($row = $visitingPersonsResult->fetch(PDO::FETCH_ASSOC)) : ?>
            <tr>
                <td><?php echo htmlspecialchars($row['person_name']); ?></td>
                <td class="actions">
                    <form method="POST">
                        <input type="hidden" name="person_id" value="<?php echo $row['person_id']; ?>">
                        <button type="submit" name="edit_person">Edit</button>
                        <button type="submit" name="delete_person" class="delete-button">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- List of existing visit reasons -->
    <h2>Existing Visit Reasons</h2>
    <table class="existing-table">
        <tr>
            <th>Description</th>
            <th class="actions">Actions</th>
        </tr>
        <?php while ($row = $visitReasonsResult->fetch(PDO::FETCH_ASSOC)) : ?>
            <tr>
                <td><?php echo htmlspecialchars($row['reason_description']); ?></td>
                <td class="actions">
                    <form method="POST">
                        <input type="hidden" name="reason_id" value="<?php echo $row['reason_id']; ?>">
                        <button type="submit" name="edit_reason">Edit</button>
                        <button type="submit" name="delete_reason" class="delete-button">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
