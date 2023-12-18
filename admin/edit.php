<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Disable browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

require_once '../config.php';
$conn = $pdo;

$search = $_GET['search'] ?? '';

$baseQuery = "SELECT
    checkin_checkout.record_id,
    users.first_name AS first_name,
    users.last_name AS last_name,
    visiting_persons.person_name AS person_visited,
    visit_reasons.reason_description AS reason_for_visit,
    checkin_checkout.checkin_time,
    checkin_checkout.checkout_time
FROM checkin_checkout
INNER JOIN users ON checkin_checkout.user_id = users.user_id
LEFT JOIN visiting_persons ON checkin_checkout.visiting_person_id = visiting_persons.person_id
LEFT JOIN visit_reasons ON checkin_checkout.visit_reason_id = visit_reasons.reason_id
WHERE 1";

if (!empty($search)) {
    $baseQuery .= " AND (users.first_name LIKE :search OR users.last_name LIKE :search)";
}

$stmt = $conn->prepare($baseQuery);

if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
}

$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$editRecord = null;

function fetchDropdownOptions($pdo, $tableName) {
    $stmt = $pdo->prepare("SELECT * FROM $tableName");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$visitingPersons = fetchDropdownOptions($pdo, 'visiting_persons');
$visitReasons = fetchDropdownOptions($pdo, 'visit_reasons');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (isset($_POST['edit']) && $id) {
        $stmt = $conn->prepare("SELECT
            checkin_checkout.*,
            users.first_name,
            users.last_name,
            visiting_persons.person_name,
            visit_reasons.reason_description
        FROM checkin_checkout
        INNER JOIN users ON checkin_checkout.user_id = users.user_id
        LEFT JOIN visiting_persons ON checkin_checkout.visiting_person_id = visiting_persons.person_id
        LEFT JOIN visit_reasons ON checkin_checkout.visit_reason_id = visit_reasons.reason_id
        WHERE checkin_checkout.record_id = :record_id");
        $stmt->bindValue(':record_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $editRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif (isset($_POST['delete']) && $id) {
        $stmt = $conn->prepare("DELETE FROM checkin_checkout WHERE record_id = :record_id");
        $stmt->bindValue(':record_id', $id, PDO::PARAM_INT);
        $stmt->execute();
    } elseif (isset($_POST['save_edit']) && $id) {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $visitingPersonId = $_POST['visiting_person_id'];
        $visitReasonId = $_POST['visit_reason_id'];
        $checkinTime = $_POST['checkin_time'];
        $checkoutTime = $_POST['checkout_time'];

        $stmt = $conn->prepare("UPDATE users SET
            first_name = :first_name,
            last_name = :last_name
            WHERE user_id = :user_id");
        
        $stmt->bindValue(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE checkin_checkout SET
            visiting_person_id = :visiting_person_id,
            visit_reason_id = :visit_reason_id,
            checkin_time = :checkin_time,
            checkout_time = :checkout_time
            WHERE record_id = :record_id");

        $stmt->bindValue(':visiting_person_id', $visitingPersonId, PDO::PARAM_INT);
        $stmt->bindValue(':visit_reason_id', $visitReasonId, PDO::PARAM_INT);
        $stmt->bindValue(':checkin_time', $checkinTime);
        $stmt->bindValue(':checkout_time', $checkoutTime === '' ? null : $checkoutTime, PDO::PARAM_STR);
        $stmt->bindValue(':record_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect the user back to the list of visitors
        header("Location: ./edit.php");
        exit(); // Make sure to exit after the header redirect
    } elseif (isset($_POST['checkin_now']) && $id) {
        $current_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE checkin_checkout SET checkin_time = :checkin_time WHERE record_id = :record_id");
        $stmt->bindValue(':checkin_time', $current_time);
        $stmt->bindValue(':record_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect the user back to the list of visitors
        header("Location: your_page.php");
        exit(); // Make sure to exit after the header redirect
    } elseif (isset($_POST['checkout_now']) && $id) {
        $current_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE checkin_checkout SET checkout_time = :checkout_time WHERE record_id = :record_id");
        $stmt->bindValue(':checkout_time', $current_time);
        $stmt->bindValue(':record_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect the user back to the list of visitors
        header("Location: your_page.php");
        exit(); // Make sure to exit after the header redirect
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Visitor Records</title>
    <link rel="stylesheet" type="text/css" href="theme.php">
    <img src="../img/dnd-project-sm-logo.png">
    <h2><a href="/admin/" class="button">Return to Admin Dashboard</a></h2>
</head>
<body>
    <form method="GET">
        <input type="text" name="search" placeholder="Search by Name" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
        <input type="submit" value="Search"> <p>
    </form>

    <?php if ($editRecord): ?>
    <div class="edit-form">
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($editRecord['record_id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
            <table>
                <tr>
                    <td>First Name:</td>
                    <td><input type="text" name="first_name" value="<?= isset($editRecord['first_name']) ? htmlspecialchars($editRecord['first_name']) : '' ?>"></td>
                </tr>
                <tr>
                    <td>Last Name:</td>
                    <td><input type="text" name="last_name" value="<?= htmlspecialchars($editRecord['last_name'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Person Visited:</td>
                    <td>
                        <select name="visiting_person_id">
                            <?php foreach ($visitingPersons as $person): ?>
                                <option value="<?= $person['person_id'] ?>" <?= ($editRecord['visiting_person_id'] == $person['person_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($person['person_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Reason for Visit:</td>
                    <td>
                        <select name="visit_reason_id">
                            <?php foreach ($visitReasons as $reason): ?>
                                <option value="<?= $reason['reason_id'] ?>" <?= ($editRecord['visit_reason_id'] == $reason['reason_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($reason['reason_description']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Check-In Time:</td>
                    <td><input type="text" name="checkin_time" value="<?= htmlspecialchars($editRecord['checkin_time'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Check-Out Time:</td>
                    <td><input type="text" name="checkout_time" value="<?= htmlspecialchars($editRecord['checkout_time'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td colspan="2"> <center>
                        <input type="submit" name="save_edit" value="Save Changes">
                        <input type="submit" name="checkin_now" value="Check-In Now">
                        <input type="submit" name="checkout_now" value="Check-Out Now">
                    </td></center>
                </tr>
            </table>
        </form>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Person Visited</th>
                <th>Reason for Visit</th>
                <th>Check-In Time</th>
                <th>Check-Out Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?= htmlspecialchars($record['first_name']) ?></td>
                    <td><?= htmlspecialchars($record['last_name']) ?></td>
                    <td><?= htmlspecialchars($record['person_visited'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($record['reason_for_visit']) ?></td>
                    <td><?= htmlspecialchars($record['checkin_time']) ?></td>
                    <td><?= !empty($record['checkout_time']) ? htmlspecialchars($record['checkout_time']) : 'Not Checked Out Yet' ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($record['record_id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                            <input type="submit" name="edit" value="Edit">
                            <input type="submit" name="delete" class="delete-button" value="Delete">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
