<?php
// Include the database configuration file
require_once '../config.php';

// Function to upload CSV data to the database using PDO
function uploadCSVData($pdo) {
    if (isset($_POST["submit"])) {
        // Check if a file was selected
        if ($_FILES["file"]["error"] == 0) {
            // Get the uploaded file
            $file = $_FILES["file"]["tmp_name"];

            // Open the file for reading
            if (($handle = fopen($file, "r")) !== false) {
                // Begin a transaction
                $pdo->beginTransaction();

                try {
                    // Loop through each row in the CSV file
                    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                        // Check if the required columns exist
                        if (isset($data[0]) && isset($data[1]) && isset($data[2])) {
                            // Extract data from CSV columns
                            $first_name = $data[0];
                            $last_name = $data[1];
                            $email = $data[2];
                    
                            // Prepare the SQL query to insert data into the 'users' table
                            $sql = "INSERT INTO users (first_name, last_name, email) VALUES (?, ?, ?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$first_name, $last_name, $email]);
                        }
                    }

                    // Commit the transaction
                    $pdo->commit();

                    // Close the CSV file
                    fclose($handle);

                    echo "CSV data has been successfully uploaded to the database.";
                } catch (Exception $e) {
                    // Rollback the transaction in case of error
                    $pdo->rollBack();
                    echo "Error uploading CSV data: " . $e->getMessage();
                }
            } else {
                echo "Error opening the CSV file.";
            }
        } else {
            echo "Error uploading the file.";
        }
    }
}

// Call the function to upload CSV data
uploadCSVData($pdo);
?>

<!DOCTYPE html>
<html>
<head>
    <title>CSV Upload</title>
    <img src="../img/dnd-project-sm-logo.png">
    <link rel="stylesheet" type="text/css" href="theme.php">
</head>
<body>
<center> <a href="user_upload_template.csv" download>Download User Upload Template</a></center>
    <h2><a href="./" class="button">Return to Admin Dashboard</a></h2>
  
    <h2>Upload User Data CSV File</h2>
    <form method="post" enctype="multipart/form-data">
        <b>Select CSV file to upload:</b>
        <input type="file" class="button" name="file" id="file">
        <input type="submit" class="delete-button" name="submit" value="Upload File">
    </form>
</body>
</html>
