<?php
include 'messaging/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_user_name = htmlspecialchars($_SESSION['firstname'] ?? 'User');
$open_chat_user_id = $_GET['user_id'] ?? 'null';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-M">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaging - SmartStudy</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/messaging.css"> 
    <link rel="stylesheet" href="css/loading.css">
</head>
<body class="no-sidebar-layout" data-open-chat="<?php echo $open_chat_user_id; ?>">
    
    <input type="hidden" id="currentUserId" value="<?php echo $current_user_id; ?>">

    <main class="main-content full-width-layout">
        <section id="messaging-section" class="content-section active">
            
            <div class="page-top-controls">
                <a href="dashboard.php" class="btn-back">
                    ← Back to Dashboard
                </a>
                <div class="section-header">
                    <h1>Messages</h1>
                </div>
                <a href="profile.php" class="btn-back current-user-display">
                    <span class="avatar-mini"><?php echo strtoupper(substr($current_user_name, 0, 1)); ?></span>
                    <span><?php echo $current_user_name; ?></span>
                </a>
            </div>
            
            <div class="messaging-container">
                
                <div class="chat-list-panel">
                    <div class="chat-list-header">
                        <h2>Chats</h2>
                        <div class="header-actions">
                            <button class="btn-icon" id="chatOptionsButton">...</button>
                            <button class="btn-icon" id="newMessageButton">✏️</button>
                        </div>
                    </div>
                    <div class="search-bar">
                        <input type="text" placeholder="Search Messenger..." id="searchInput">
                    </div>
                    <div class="chat-list" id="chatList">
                         <p style='text-align:center; color: var(--text-gray); padding: 20px;'>Loading chats...</p>
                    </div>
                </div>

                <div class="chat-window-panel" id="chatWindow">
                    <div class="chat-placeholder">
                        <span class="placeholder-icon">💬</span>
                        <h3>Select a chat</h3>
                        <p>Search for users to start a conversation.</p>
                    </div>
                </div>
                
                <div class="chat-details-panel" id="chatDetails">
                    <div class="details-placeholder">
                        <span class="placeholder-icon">ℹ️</span>
                        <h3>Chat Details</h3>
                        <p>Click the 'Info' icon on a chat to see details.</p>
                    </div>
                </div>
                
            </div>
        </section>
    </main>

    <div id="uploadModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Upload</h3>
            <p>Select what you want to send:</p>
            <div class="modal-options">
                <label for="photoUpload" class="modal-option">
                    <span>🖼️</span>
                    <span>Photo</span>
                    <input type="file" id="photoUpload" accept="image/*" hidden>
                </label>
                <label for="fileUpload" class="modal-option">
                    <span>📁</span>
                    <span>File</span>
                    <input type="file" id="fileUpload" hidden>
                </label>
            </div>
            <button class="modal-close-btn" id="modalCloseButton">Close</button>
        </div>
    </div>

    <div id="imagePreviewModal" class="modal-overlay image-preview-modal">
        <span class="modal-close-btn" id="imageModalCloseButton">&times;</span>
        <img src="" alt="Image Preview" id="fullScreenImage">
    </div>

    <div id="reactionPicker" class="reaction-picker">
        <button class="reaction-emoji" data-emoji="❤️">❤️</button>
        <button class="reaction-emoji" data-emoji="😂">😂</button>
        <button class="reaction-emoji" data-emoji="👍">👍</button>
        <button class="reaction-emoji" data-emoji="😮">😮</button>
        <button class="reaction-emoji" data-emoji="😢">😢</button>
        <button class="reaction-emoji" data-emoji="😡">😡</button>
    </div>


    <script src="js/main.js"></script> 
    <script src="js/messaging.js"></script>
    </body>
</html>