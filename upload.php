<?php
// 1. Turn on error reporting to see details
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Define upload directory
// We create this folder manually if it doesn't exist
 $uploadDir = __DIR__ . '/uploads/';

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die("Error: Failed to create uploads folder. Check permissions.");
    }
}

// 3. Check if form is submitted
if (isset($_POST['submit'])) {
    
    // Check if file was actually uploaded
    if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
        
        $fileTmpPath = $_FILES['fileToUpload']['tmp_name'];
        $fileName    = basename($_FILES['fileToUpload']['name']);
        $fileSize    = $_FILES['fileToUpload']['size'];
        $fileType    = pathinfo($fileName, PATHINFO_EXTENSION);
        $uploadDest   = $uploadDir . $fileName;

        // A. Validation: Check File Type
        $allowedTypes = ['pdf', 'doc', 'docx'];
        if (!in_array(strtolower($fileType), $allowedTypes)) {
            die("<h3 style='color:red;'>Error: Invalid File Type</h3><p>Only PDF, DOC, DOCX allowed.</p><a href='submit.html'>Go Back</a>");
        }

        // B. Validation: Check File Size (Limit: 5MB)
        if ($fileSize > 5000000) {
            die("<h3 style='color:red;'>Error: File Too Large</h3><p>Max file size is 5MB.</p><a href='submit.html'>Go Back</a>");
        }

        // C. Move File
        if (move_uploaded_file($fileTmpPath, $uploadDest)) {
            
            // D. Save Form Data to Text File
            // Get data safely
            $title    = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : 'Unknown Title';
            $author   = isset($_POST['author']) ? htmlspecialchars($_POST['author']) : 'Unknown Author';
            $email    = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : 'No Email';
            
            $logMessage = "Date: " . date("Y-m-d H:i:s") . "\n" . 
                         "Title: $title\n" . 
                         "Author: $author\n" . 
                         "Email: $email\n" . 
                         "File: $fileName\n" . 
                         "-----------------------------------\n";

            // Append to log file
            if (file_put_contents($uploadDir . 'submissions_log.txt', $logMessage, FILE_APPEND)) {
                // SUCCESS
                echo "<!DOCTYPE html>
                      <html><head><style>body{font-family:sans-serif;text-align:center;padding:50px;}</style></head>
                      <body>
                        <h2 style='color:green;'>Success!</h2>
                        <p>Your paper <b>$title</b> has been submitted successfully.</p>
                        <p><a href='submit.html'>Click here to submit another paper</a></p>
                      </body></html>";
            } else {
                die("File uploaded, but failed to save log.");
            }

        } else {
            die("<h3 style='color:red;'>Upload Failed</h3><p>The file could not be moved to the upload directory. Check folder permissions for 'uploads'.</p>");
        }

    } else {
        $uploadError = $_FILES['fileToUpload']['error'];
        die("<h3 style='color:red;'>Upload Error</h3><p>Error Code: $uploadError.</p><p>Check if file exceeds php.ini upload limits.</p>");
    }

} else {
    // If accessed directly without submitting
    echo "This script is meant to process a form submission. <a href='submit.html'>Go to form</a>.";
}
?>