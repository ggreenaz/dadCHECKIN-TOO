<?php
session_start(); // Start the session

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    // Get the selected theme from the form
    $selectedTheme = $_POST['theme'];

    // Validate the selected theme (ensure it exists to prevent vulnerabilities)
    $allowedThemes = ['style1', 'darkmode', 'lightmode', 'ltgreen', 'academi', 'gator', 'packers', 'trc'];

    if (in_array($selectedTheme, $allowedThemes)) {
        // Set a session variable with the same key as in JavaScript
        $_SESSION['selected_theme'] = $selectedTheme;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const themeSelect = document.getElementById("theme-select");
        const storedTheme = localStorage.getItem("selected_theme");

        // Set the dropdown to the stored theme
        if (storedTheme) {
            themeSelect.value = storedTheme;
        }

        themeSelect.addEventListener("change", function () {
            const selectedTheme = this.value;
            localStorage.setItem("selected_theme", selectedTheme);
            document.getElementById("theme-link").href = `../css/${selectedTheme}.css`;
        });
    });
</script>

</head>
<body>
    <center>
        <img src="../img/dnd-project-sm-logo.png">
    </center>

    <div class="container">
        <h1>Admin Dashboard</h1>
        <a href="reports.php">Print Records</a>
        <a href="edit.php">Edit Records</a>
        <a href="admin.php">Add or Edit Person/Reason</a>
        <a href="history.php">Visitor History Data</a>
        <a href="upload.php">Upload Data</a>
        <a href="settings.php">Connect to LDAP</a>
    </div>

    <form method="post" action="">
        <label for="theme-select">Select a theme:</label>
        <select id="theme-select" name="theme">
            <option value="style1">Default</option>
            <option value="darkmode">Dark Mode</option>
            <option value="lightmode">Light Mode</option>
            <option value="ltgreen">Light Green Mode</option>
            <option value="academi">Academi Mode</option>
            <option value="gator">Gator Mode</option>
            <option value="packers">Green Bay Mode</option>
            <option value="trc">TRC Mode</option>
            <!-- Add more options for additional themes -->
        </select>
        <input type="submit" value="Apply Theme">
    </form>
</body>
</html>
