<?php
session_start(); // Start the session

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['customImage'])) {
    $targetDirectory = "../img";
    $targetFile = $targetDirectory . basename($_FILES["customImage"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["customImage"]["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($_FILES["customImage"]["tmp_name"], $targetFile)) {
            $_SESSION['uploaded_image'] = $targetFile;
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "File is not an image.";
    }
}


// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    // Get the selected theme from the form
    $selectedTheme = $_POST['theme'];

    // Define the allowed themes
    $allowedThemes = [
        'style' => 'style.css',
        'darkmode' => 'darkmode.style.css',
        'lightmode' => 'lightmode.style.css',
        'ltgreen' => 'ltgreen.style.css',
        'academi' => 'academi.style.css',
        'gator' => 'gator.style.css',
        'packers' => 'packers.style.css',
        'olive' => 'olive.style.css',
        'raspberry' => 'raspberry.style.css',
        'trc' => 'trc.style.css',
        'blueshades' => 'blueshades.style.css',
        'royalblue' => 'royalblue.style.css',
        'teal' => 'teal.style.css',
        'red' => 'red.style.css',
        'limegreen' => 'limegreen.style.css',
        'majorblue' => 'majorblue.style.css',
        'yellow-charcoal' => 'yellow-charcoal.style.css'
        // Add any additional themes here
    ];

    // Validate the selected theme
    if (array_key_exists($selectedTheme, $allowedThemes)) {
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

        <?php
        // Display the uploaded image if it exists
        if (isset($_SESSION['uploaded_image'])) {
            echo '<img src="' . $_SESSION['uploaded_image'] . '">';
        } else {
            echo '<img src="../img/dnd-project-sm-logo.png">';
        }
        ?>
    </center>

    <!-- Confirmation Table -->
    <?php if ($studentData): ?>
        <h2>User Information</h2>
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

    <!-- Rest of the content goes here -->
    <div class="container">
        <h1>Admin Dashboard</h1>
        <a href="reports.php">Print Records</a>
        <a href="edit.php">Edit Records</a>
        <a href="admin.php">Add or Edit Person/Reason</a>
        <a href="history.php">Visitor History Data</a>
        <a href="upload.php">Upload Data</a>
        <a href="settings.php">Connect to LDAP</a>
    </div>
<p><p>
    <!-- Image upload form -->
    <form action="" method="post" enctype="multipart/form-data">
        Upload Your Custom Logo:
        <input type="file" name="customImage" id="customImage">
        <input type="submit" value="Upload Image" name="submit">
    </form>
<P><P>
<form method="post" action="">
    <label for="theme-select">Select a theme:</label>
    <select id="theme-select" name="theme">
        <option value="style">Default</option>
        <option value="darkmode">Dark Mode</option>
        <option value="lightmode">Light Mode</option>
        <option value="ltgreen">Light Green Mode</option>
        <option value="academi">Academi Mode</option>
        <option value="gator">Gator Mode</option>
        <option value="packers">Green Bay Mode</option>
        <option value="olive">Olive Mode</option>
        <option value="raspberry">Raspberry Mode</option>
        <option value="trc">TRC Mode</option>
        <option value="blueshades">Blue Shades Mode</option>
        <option value="royalblue">Royal Blue Mode</option>
        <option value="teal">Teal Mode</option>
        <option value="red">Red Mode</option>
        <option value="limegreen">Lime Green Mode</option>
        <option value="majorblue">Major Blue Mode</option>
        <option value="yellow-charcoal">Yellow Charcoal Mode</option>
        <!-- Add more options for additional themes -->
    </select>
    <input type="submit" value="Apply Theme">
</form>
</body>
</html>
