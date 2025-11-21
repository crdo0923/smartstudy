<?php
// messaging/ajax_add_reaction.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("error_not_logged_in");
}

$current_user_id = $_SESSION['user_id'];
$msg_id = $_POST['msg_id'] ?? 0;
$emoji = $_POST['emoji'] ?? ''; // BAGO: Kunin 'yung specific emoji

if ($msg_id == 0 || empty($emoji)) {
    die("error_invalid_request");
}

// 1. Tignan kung may reaction na 'yung user sa message na 'to
$stmt_check = $conn->prepare("SELECT reaction_emoji FROM message_reactions WHERE msg_id = ? AND user_id = ?");
$stmt_check->bind_param("ii", $msg_id, $current_user_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    // May reaction na
    $row = $result->fetch_assoc();
    $current_reaction = $row['reaction_emoji'];

    if ($current_reaction === $emoji) {
        // Nag-click ulit sa parehong emoji: Tanggalin (un-react)
        $stmt_action = $conn->prepare("DELETE FROM message_reactions WHERE msg_id = ? AND user_id = ?");
        $stmt_action->bind_param("ii", $msg_id, $current_user_id);
    } else {
        // Nag-click sa ibang emoji: Palitan (update)
        $stmt_action = $conn->prepare("UPDATE message_reactions SET reaction_emoji = ? WHERE msg_id = ? AND user_id = ?");
        $stmt_action->bind_param("sii", $emoji, $msg_id, $current_user_id);
    }

} else {
    // Wala pang reaction: Idagdag (insert)
    $stmt_action = $conn->prepare("INSERT INTO message_reactions (msg_id, user_id, reaction_emoji) VALUES (?, ?, ?)");
    $stmt_action->bind_param("iis", $msg_id, $current_user_id, $emoji);
}

// 4. Execute
if ($stmt_action->execute()) {
    echo "success";
} else {
    echo "error_db_fail";
}

$stmt_check->close();
$stmt_action->close();
$conn->close();
?><?php
// messaging/ajax_add_reaction.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("error_not_logged_in");
}

$current_user_id = $_SESSION['user_id'];
$msg_id = $_POST['msg_id'] ?? 0;
$emoji = $_POST['emoji'] ?? ''; // BAGO: Kunin 'yung specific emoji

if ($msg_id == 0 || empty($emoji)) {
    die("error_invalid_request");
}

// 1. Tignan kung may reaction na 'yung user sa message na 'to
$stmt_check = $conn->prepare("SELECT reaction_emoji FROM message_reactions WHERE msg_id = ? AND user_id = ?");
$stmt_check->bind_param("ii", $msg_id, $current_user_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    // May reaction na
    $row = $result->fetch_assoc();
    $current_reaction = $row['reaction_emoji'];

    if ($current_reaction === $emoji) {
        // Nag-click ulit sa parehong emoji: Tanggalin (un-react)
        $stmt_action = $conn->prepare("DELETE FROM message_reactions WHERE msg_id = ? AND user_id = ?");
        $stmt_action->bind_param("ii", $msg_id, $current_user_id);
    } else {
        // Nag-click sa ibang emoji: Palitan (update)
        $stmt_action = $conn->prepare("UPDATE message_reactions SET reaction_emoji = ? WHERE msg_id = ? AND user_id = ?");
        $stmt_action->bind_param("sii", $emoji, $msg_id, $current_user_id);
    }

} else {
    // Wala pang reaction: Idagdag (insert)
    $stmt_action = $conn->prepare("INSERT INTO message_reactions (msg_id, user_id, reaction_emoji) VALUES (?, ?, ?)");
    $stmt_action->bind_param("iis", $msg_id, $current_user_id, $emoji);
}

// 4. Execute
if ($stmt_action->execute()) {
    echo "success";
} else {
    echo "error_db_fail";
}

$stmt_check->close();
$stmt_action->close();
$conn->close();
?>