<?php
// 1. TURN ON ALL ERRORS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<div style='font-family:sans-serif; padding:20px; border:2px solid #000;'>";

// 2. SETUP DIRECTORY
// __DIR__ gets exact path of current folder
 $target_dir = __DIR__ . "/uploads/";

// Create folder if it doesn't exist
if (!file_exists($target_dir)) {
    if (!mkdir($target_dir, 0777, true)) {
        die("<h2 style='color:red;'>CRITICAL ERROR</h2> 
            <p>I cannot create the 'uploads' folder. Please create a folder named 'uploads' manually in the same directory as this PHP file and give it permission to write.</p>");
    }
}

echo "<p>Directory Ready: $target_dir</p>";

// 3. CHECK FOR POST REQUEST (More reliable than checking button name)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check if file exists in array
    if (isset($_FILES['fileToUpload'])) {
        $file = $_FILES['fileToUpload'];
        $fileName = basename($file['name']);
        $target_file = $target_dir . $fileName;
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // DEBUGGING: Print what we received
        echo "<h3>File Info Received:</h3>";
        echo "Name: $fileName<br>";
        echo "Size: " . $file['size'] . " bytes<br>";
        echo "Type: $fileType<br>";
        echo "Tmp: " . $file['tmp_name'] . "<br>";
        echo "Error Code: " . $file['error'] . "<br><hr>";

        // 4. VALIDATIONS
        if ($file['size'] > 5000000) {
            die("<h3 style='color:red;'>Error</h3><p>File is too large (Max 5MB).</p>");
        }

        if ($fileType != "pdf" && $fileType != "doc" && $fileType != "docx") {
            die("<h3 style='color:red;'>Error</h3><p>Invalid file type. Only PDF, DOC, DOCX allowed.</p>");
        }

        // 5. MOVE FILE
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            
            echo "<h2 style='color:green;'>SUCCESS!</h2>";
            echo "<p>File '$fileName' has been uploaded successfully.</p>";

            // Save Metadata
            $title = isset($_POST['title']) ? $_POST['title'] : 'No Title';
            $author = isset($_POST['author']) ? $_POST['author'] : 'No Author';
            
            $log = "Title: $title | Author: $author | File: $fileName | " . date("Y-m-d H:i:s") . "\n";
            file_put_contents($target_dir . "submissions_log.txt", $log, FILE_APPEND);
            
            echo "<p>Data logged to file.</p>";
            echo "<a href='submit.html'>Go Back</a>";

        } else {
            // SPECIFIC ERROR REPORTING
            echo "<h2 style='color:red;'>UPLOAD FAILED</h2>";
            echo "<p>The server refused to move the file.</p>";
            echo "<p>PHP Error Code: " . $file['error'] . "</p>";
            
            if ($file['error'] == 1 || $file['error'] == 2) {
                echo "<p>Reason: File exceeds php.ini upload_max_filesize.</p>";
            } elseif ($file['error'] == 3) {
                echo "<p>Reason: File was only partially uploaded.</p>";
            } elseif ($file['error'] == 4) {
                echo "<p>Reason: No file was selected.</p>";
            } elseif ($file['error'] == 6) {
                echo "<p>Reason: Missing a temporary folder (XAMPP Config issue).</p>";
            } elseif ($file['error'] == 7) {
                echo "<p>Reason: Failed to write file to disk. <b>Check Permissions!</b></p>";
            }
        }

    } else {
        echo "<h2 style='color:red;'>Error</h2><p>No file detected in POST data.</p>";
    }

} else {
    echo "<h2>Access Denied</h2><p>You must submit the form to access this script.</p>";
}
echo "</div>";
?>