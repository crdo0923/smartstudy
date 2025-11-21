<?php
// ajax_fetch_messages.php
include_once 'config.php'; // Gumamit ng include_once para iwas error

$sender_id = $_SESSION['user_id']; 
$receiver_id = $_POST['receiver_id'] ?? 0; 
$output = "";

if ($receiver_id != 0) {
    $update_sql = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $receiver_id, $sender_id);
    $update_stmt->execute();
}

// BAGO: Kunin lahat ng reactions para sa chat na 'to
$reactions = [];
$sql_reactions = "SELECT msg_id, reaction_emoji, COUNT(reaction_id) as count, GROUP_CONCAT(user_id) as users 
                  FROM message_reactions 
                  WHERE msg_id IN (
                      SELECT msg_id FROM messages WHERE 
                      (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
                  )
                  GROUP BY msg_id, reaction_emoji";
$stmt_reactions = $conn->prepare($sql_reactions);
$stmt_reactions->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt_reactions->execute();
$result_reactions = $stmt_reactions->get_result();
while ($row_react = $result_reactions->fetch_assoc()) {
    $reactions[$row_react['msg_id']][] = $row_react;
}
$stmt_reactions->close();


// Kunin ang messages
$sql = "SELECT 
            m1.msg_id, m1.sender_id, m1.message, m1.timestamp,
            m2.message AS replied_message, 
            u.firstname AS replied_sender_name
        FROM messages AS m1
        LEFT JOIN messages AS m2 ON m1.reply_to_msg_id = m2.msg_id
        LEFT JOIN users AS u ON m2.sender_id = u.id
        WHERE 
            ( (m1.sender_id = ? AND m1.receiver_id = ?) OR (m1.sender_id = ? AND m1.receiver_id = ?) )
        AND
            ( (m1.sender_id = ? AND m1.deleted_by_sender = 0) OR (m1.receiver_id = ? AND m1.deleted_by_receiver = 0) )
        ORDER BY m1.timestamp ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $sender_id, $receiver_id, $receiver_id, $sender_id, $sender_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $type = ($row['sender_id'] == $sender_id) ? 'outgoing' : 'incoming';
        $time = date('h:i A', strtotime($row['timestamp']));
        $msg_id = $row['msg_id'];
        $messageContent = htmlspecialchars($row['message']);
        
        // Build reaction HTML
        $reactionHTML = "";
        if (isset($reactions[$msg_id])) {
            $reactionHTML = "<div class='message-reaction'>";
            foreach ($reactions[$msg_id] as $reaction) {
                // Check kung kasama ang current user sa nag-react
                $user_ids = explode(',', $reaction['users']);
                $my_reaction_class = (in_array($sender_id, $user_ids)) ? 'my-reaction' : '';
                
                $reactionHTML .= "<span class='message-reaction-item {$my_reaction_class}' data-emoji='{$reaction['reaction_emoji']}'>
                                    {$reaction['reaction_emoji']} {$reaction['count']}
                                  </span>";
            }
            $reactionHTML .= "</div>";
        }

        // Build reply HTML
        $replyHTML = "";
        if ($row['replied_message']) {
            $replied_sender = ($row['replied_sender_name'] == $_SESSION['firstname']) ? "You" : htmlspecialchars($row['replied_sender_name']);
            $replied_text = htmlspecialchars($row['replied_message']);
            if (strpos($replied_text, 'uploads/') === 0) {
                 $replied_text = "[File Attachment]";
            }
            $replyHTML = "<div class='inline-reply-preview'>
                            <strong>Replying to {$replied_sender}</strong>
                            <p>{$replied_text}</p>
                          </div>";
        }

        // Build message content HTML
        if (strpos($messageContent, 'uploads/') === 0) {
            $fileExt = strtolower(pathinfo($messageContent, PATHINFO_EXTENSION));
            $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']; 
            $fileName = basename($messageContent);
            if (in_array($fileExt, $allowedImageTypes)) {
                $messageHTML = "<img src='{$messageContent}' alt='Sent Photo' class='chat-image'>";
            } else {
                $messageHTML = "<a href='{$messageContent}' class='chat-file-link' target='_blank'>
                                  <span class='file-icon'>📁</span>
                                  <span class='file-name'>{$fileName}</span>
                                </a>";
            }
        } else {
            $messageHTML = "<p>{$messageContent}</p>";
        }

        // Assemble all parts
        $output .= "
        <div class='message-wrapper $type'> 
            <div class='message-bubble $type' data-msg-id='{$msg_id}'>
                <div class='bubble-content-wrapper'>
                    {$replyHTML}
                    {$messageHTML}
                    <span class='message-time'>$time</span>
                </div>
                {$reactionHTML}
            </div>
            <div class='message-actions'>
                 <button class='btn-react-emoji' data-msg-id='{$msg_id}' title='React'>😊</button>
                 <button class='btn-reply' data-msg-id='{$msg_id}' title='Reply'>↩️</button>
                 <button class='btn-bubble-menu' data-msg-id='{$msg_id}' title='More'>...</button>
            </div>
        </div>
        ";
    }
} else {
    $output = "<div class='chat-empty-state'>Start the conversation!</div>";
}

echo $output;
?>