<?php
// messaging/ajax_delete_message.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("error_not_logged_in");
}

$current_user_id = $_SESSION['user_id'];
$msg_id = $_POST['msg_id'] ?? 0;
$delete_type = $_POST['delete_type'] ?? ''; // 'me' or 'everyone'

if ($msg_id == 0 || empty($delete_type)) {
    die("error_invalid_request");
}

// Security Check: Kunin ang message para malaman kung sino ang may-ari
$stmt_check = $conn->prepare("SELECT sender_id, receiver_id FROM messages WHERE msg_id = ?");
$stmt_check->bind_param("i", $msg_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows == 0) {
    die("error_msg_not_found");
}
$row = $result->fetch_assoc();
$sender_id = $row['sender_id'];
$receiver_id = $row['receiver_id'];

// --- Logic para sa Pag-delete ---

if ($delete_type === 'me') {
    // "Delete for me"
    if ($current_user_id == $sender_id) {
        // Ako (sender) ang nag-delete
        $sql = "UPDATE messages SET deleted_by_sender = 1 WHERE msg_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $msg_id);
    } elseif ($current_user_id == $receiver_id) {
        // Ako (receiver) ang nag-delete
        $sql = "UPDATE messages SET deleted_by_receiver = 1 WHERE msg_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $msg_id);
    } else {
        die("error_not_participant");
    }

} elseif ($delete_type === 'everyone') {
    // "Delete for everyone"
    // Security: Dapat sender lang ang pwedeng gumawa nito
    if ($current_user_id == $sender_id) {
        $sql = "DELETE FROM messages WHERE msg_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $msg_id);
    } else {
        die("error_not_sender");
    }
} else {
    die("error_invalid_type");
}

// Execute the final query
if ($stmt->execute()) {
    echo "success";
} else {
    echo "error_db_fail";
}

$stmt->close();
$conn->close();
?>