<?php
// messaging/ajax_get_notifications.php
include 'config.php';

// ⭐ IBINALIK SA 'user_id'
$current_user_id = $_SESSION['user_id'] ?? 0;
$notifications = [];

if ($current_user_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit;
}

$sql = "SELECT m.sender_id, u.firstname, u.lastname, COUNT(m.msg_id) as unread_count
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.receiver_id = ? 
          AND m.is_read = 0 
          AND m.is_notified = 0
        GROUP BY m.sender_id, u.firstname, u.lastname";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'sender_id' => $row['sender_id'],
            'sender_name' => $row['firstname'] . ' ' . $row['lastname'],
            'count' => $row['unread_count']
        ];
    }
    
    $update_sql = "UPDATE messages SET is_notified = 1 
                   WHERE receiver_id = ? AND is_read = 0 AND is_notified = 0";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $current_user_id);
    $update_stmt->execute();
}

echo json_encode(['status' => 'success', 'notifications' => $notifications]);
?>