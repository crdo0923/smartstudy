<?php
// ajax_send_message.php
include 'config.php';

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? 0;
$message = $_POST['message'] ?? '';
// BAGO: Kunin 'yung reply_to_id
$reply_to_id = $_POST['reply_to_id'] ?? NULL;

// BAGO: I-check kung valid number o NULL
if ($reply_to_id !== NULL && !is_numeric($reply_to_id)) {
    $reply_to_id = NULL;
}

if (!empty($message) && $receiver_id != 0) {
    // BINAGO: Idinagdag ang reply_to_msg_id
    $sql = "INSERT INTO messages (sender_id, receiver_id, message, reply_to_msg_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    // BINAGO: Naging "iisi" (integer, integer, string, integer)
    $stmt->bind_param("iisi", $sender_id, $receiver_id, $message, $reply_to_id); 
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>