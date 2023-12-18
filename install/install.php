<!DOCTYPE html>
<html>
<head>
    <title>Database Installation</title>
    <link rel="stylesheet" type="text/css" href="../theme.php">
    <link rel="stylesheet" type="text/css" href="../../css/style.css">
    <style>
        table {
            width: 60%;
            margin: 0 auto; /* Center the table */
        }
    </style>
</head>
<body>
    <h2>Database Installation</h2>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $servername = $_POST["servername"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $dbname = $_POST["dbname"];

        // Create connection
        $conn = new mysqli($servername, $username, $password);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        echo "Connected to the database server<br>";

        // Create database
        $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
        if ($conn->query($sql) === TRUE) {
            echo "Database created successfully<br>";
        } else {
            echo "Error creating database: " . $conn->error . "<br>";
        }

        // Select the database
        $conn->select_db($dbname);

        // SQL to create tables
        $tables = [
            "CREATE TABLE check_ins (
                checkin_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                visiting_person_id INT NOT NULL,
                visit_reason_id INT NOT NULL,
                checkin_time DATETIME NOT NULL
            )",
            "CREATE TABLE checkin_checkout (
                record_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                visiting_person_id INT NOT NULL,
                visit_reason_id INT NOT NULL,
                checkin_time DATETIME NOT NULL,
                checkout_time DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            "CREATE TABLE user_visits (
                visit_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                visiting_person_id INT,
                visit_reason_id INT,
                checkin_time DATETIME
            )",
            "CREATE TABLE users (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(255),
                last_name VARCHAR(255),
                phone VARCHAR(255),
                email VARCHAR(255),
                visiting_person_id INT,
                visit_reason_id INT,
                checkin_time DATETIME
            )",
            "CREATE TABLE visit_reasons (
                reason_id INT AUTO_INCREMENT PRIMARY KEY,
                reason_description VARCHAR(255) NOT NULL
            )",
            "CREATE TABLE visiting_persons (
                person_id INT AUTO_INCREMENT PRIMARY KEY,
                person_name VARCHAR(255)
            )"
        ];

        foreach ($tables as $sql) {
            if ($conn->query($sql) === TRUE) {
                echo "Table created successfully<br>";
            } else {
                echo "Error creating table: " . $conn->error . "<br>";
            }
        }

        $conn->close();
        echo "Disconnected from the database server<br>";

        // Create config.php file with PDO setup
        $configFileContent = "<?php\n";
        $configFileContent .= "try {\n";
        $configFileContent .= "    \$pdo = new PDO(\"mysql:host=$servername;dbname=$dbname\", \"$username\", \"$password\");\n";
        $configFileContent .= "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
        $configFileContent .= "} catch (PDOException \$e) {\n";
        $configFileContent .= "    echo 'Connection failed: ' . \$e->getMessage();\n";
        $configFileContent .= "}\n";
        $configFileContent .= "?>";

        file_put_contents('../../config.php', $configFileContent);
        echo "config.php file created successfully.<br>";

        // Display the link to the admin dashboard after all operations are complete
        echo '<h2><a href="../admin" class="button">Return to Admin Dashboard</a></h2>';
        echo '<h2>Be sure to delete the install/install.php script for added security!</h2>';

    } else {
        ?>
        <form method="POST">
            <table>
                <tr><td>Server Name:</td><td><input type="text" name="servername" required value="localhost"></td></tr>
                <tr><td>Username:</td><td><input type="text" name="username" required></td></tr>
                <tr><td>Password:</td><td><input type="password" name="password"></td></tr>
                <tr><td>Database Name:</td><td><input type="text" name="dbname" required></td></tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <button type="submit">Install Database</button>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }
    ?>
</body>
</html>
