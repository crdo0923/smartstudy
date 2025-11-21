<?php
// ajax_upload_file.php
include 'config.php';

// ⭐ IBINALIK SA 'user_id'
$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? 0;
$output = 'error';

if (isset($_FILES['file']) && $receiver_id != 0) {
    $file = $_FILES['file'];

    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if ($fileError === 0) {
        if ($fileSize < 10000000) { // 10MB limit
            
            $fileNameNew = uniqid('', true) . "." . $fileExt;
            $uploadPath = '../uploads/' . $fileNameNew; 
            $dbPath = 'uploads/' . $fileNameNew;

            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $sender_id, $receiver_id, $dbPath); 
                
                if ($stmt->execute()) {
                    $output = "success";
                } else {
                    $output = "Database insert error.";
                }
            } else {
                $output = "Error moving file.";
            }
        } else {
            $output = "File is too large (Max 10MB).";
        }
    } else {
        $output = "Error uploading file code: " . $fileError;
    }
    
} else {
    $output = "Invalid request.";
}

echo $output;
?>