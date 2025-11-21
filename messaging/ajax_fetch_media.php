<?php
// messaging/ajax_fetch_media.php
include_once 'config.php'; // Gumamit ng include_once para iwas error

$current_user_id = $_SESSION['user_id'] ?? 0;
$receiver_id = $_POST['receiver_id'] ?? 0;

if ($current_user_id == 0 || $receiver_id == 0) {
    die(json_encode(['media' => '<p class="empty-media">Invalid request.</p>', 'files' => '<p class="empty-media">Invalid request.</p>']));
}

$sql = "SELECT message, timestamp, msg_id FROM messages 
        WHERE 
            ( (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) )
        AND 
            message LIKE 'uploads/%' 
        ORDER BY timestamp DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$mediaHTML = "";
$filesHTML = "";
$allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $path = htmlspecialchars($row['message']);
        $fileName = basename($path);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedImageTypes)) {
            // Ito ay MEDIA (Image)
            $mediaHTML .= "<div class='media-grid-item'>
                             <img src='{$path}' alt='{$fileName}' class='chat-image' loading='lazy'>
                           </div>";
        } else {
            // Ito ay FILES (Document, etc.)
            $filesHTML .= "<a href='{$path}' target='_blank' class='file-list-item'>
                             <span class='file-icon'>📁</span>
                             <span class='file-name'>{$fileName}</span>
                           </a>";
        }
    }
}

if (empty($mediaHTML)) $mediaHTML = "<p class='empty-media'>No media shared yet.</p>";
if (empty($filesHTML)) $filesHTML = "<p class='empty-media'>No files shared yet.</p>";

echo json_encode(['media' => $mediaHTML, 'files' => $filesHTML]);
?>