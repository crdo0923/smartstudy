<?php
// messaging/ajax_get_chat_list.php
include 'config.php';
$current_user_id = $_SESSION['user_id'];
$query = $_POST['query'] ?? '';
$output = '';

$search_term = "%{$query}%";

// --- ITO 'YUNG BAGONG LOGIC ---
if (empty($query)) {
    // Kung WALANG search, kunin lang 'yung may existing conversation
    $sql = "
        SELECT 
            u.id, u.firstname, u.lastname,
            (SELECT message FROM messages 
             WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) 
             ORDER BY timestamp DESC LIMIT 1) as last_msg,
            (SELECT timestamp FROM messages 
             WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) 
             ORDER BY timestamp DESC LIMIT 1) as last_msg_time,
            (SELECT COUNT(*) FROM messages 
             WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
        FROM users u
        -- Ito 'yung nag-filter
        JOIN (
            SELECT DISTINCT
                CASE
                    WHEN sender_id = ? THEN receiver_id
                    WHEN receiver_id = ? THEN sender_id
                END AS user_id
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?
        ) AS conversations ON u.id = conversations.user_id
        WHERE u.id != ?
        ORDER BY last_msg_time DESC;
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiiiiiii", $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id);
} else {
    // Kung MAY search, kunin lahat ng users na tugma (gaya ng dati)
    $sql = "
        SELECT 
            u.id, u.firstname, u.lastname,
            (SELECT message FROM messages 
             WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) 
             ORDER BY timestamp DESC LIMIT 1) as last_msg,
            (SELECT timestamp FROM messages 
             WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) 
             ORDER BY timestamp DESC LIMIT 1) as last_msg_time,
            (SELECT COUNT(*) FROM messages 
             WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
        FROM users u
        WHERE 
            u.id != ? AND (u.firstname LIKE ? OR u.lastname LIKE ?)
        ORDER BY last_msg_time DESC;
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiisss", $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $search_term, $search_term);
}
// --- END NG BAGONG LOGIC ---

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $user_id = $row['id'];
        $fullname = $row['firstname'] . ' ' . $row['lastname'];
        $avatar_initial = strtoupper(substr($row['firstname'], 0, 1));
        
        $last_msg = "No messages yet.";
        if ($row['last_msg']) {
             $last_msg = htmlspecialchars($row['last_msg']);
             if (strpos($last_msg, 'uploads/') === 0) {
                $last_msg = "<i>[File Attachment]</i>";
             }
        }

        $time = $row['last_msg_time'] ? date('h:i A', strtotime($row['last_msg_time'])) : "";
        
        $unread_count = $row['unread_count'];
        $unread_badge = $unread_count > 0 ? "<span class='chat-unread-count'>$unread_count</span>" : "";

        $output .= "
        <div class='chat-item' data-user-id='$user_id' data-user-name='$fullname'>
            <div class='chat-avatar'>$avatar_initial</div>
            <div class='chat-info'>
                <h4 class='chat-name'>$fullname</h4>
                <p class='last-message'>$last_msg</p>
            </div>
            <div class='chat-meta'>
                <span class='chat-time'>$time</span>
                $unread_badge
            </div>
        </div>
        ";
    }
} else {
     $output = "<p style='text-align:center; color: var(--text-gray);'>No users found.</p>";
}
echo $output;
?>