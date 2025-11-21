// (Ito 'yung delete function mo, inayos ko lang para sa bagong HTML structure)
function deleteMessage(msgId, deleteType) {
    const formData = new FormData();
    formData.append('msg_id', msgId);
    formData.append('delete_type', deleteType);

    fetch('messaging/ajax_delete_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            // BINAGO: Target-in 'yung .message-wrapper para ma-delete lahat
            const bubbleWrapper = document.querySelector(`.message-bubble[data-msg-id='${msgId}']`).closest('.message-wrapper');
            if (bubbleWrapper) {
                bubbleWrapper.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                bubbleWrapper.style.opacity = '0';
                bubbleWrapper.style.transform = 'scale(0.8)';
                setTimeout(() => bubbleWrapper.remove(), 300);
            }
        } else {
            console.error('Delete Error:', data);
            alert('Error: Could not delete message. Check console.');
        }
    })
    .catch(error => console.error('Error deleting message:', error));
}


document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. GLOBAL VARIABLES ---
    let currentReceiverId = null;
    let messagePollingInterval = null; 
    let chatListPollingInterval = null; 
    const currentUserId = document.getElementById('currentUserId').value;
    
    // BAGO: Para sa Reply
    let currentReplyToId = null;

    // --- 2. ELEMENT SELECTORS ---
    const chatList = document.getElementById('chatList');
    const chatWindow = document.getElementById('chatWindow');
    const detailsPanel = document.getElementById('chatDetails');
    const searchInput = document.getElementById('searchInput');
    const container = document.querySelector('.messaging-container');
    
    // --- CONTEXT MENU LOGIC (Delete Menu) ---
    const menuHTML = `
    <div id="messageContextMenu" class="message-context-menu">
        <div class="context-menu-option" id="deleteForMe">Delete for me</div>
        <div class="context-menu-option danger" id="deleteForEveryone">Delete for everyone</div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', menuHTML);
    
    const contextMenu = document.getElementById('messageContextMenu');
    const deleteForMeBtn = document.getElementById('deleteForMe');
    const deleteForEveryoneBtn = document.getElementById('deleteForEveryone');

    deleteForMeBtn.addEventListener('click', function() {
        const msgId = contextMenu.dataset.msgId;
        if (msgId) {
            deleteMessage(msgId, 'me');
        }
        contextMenu.style.display = 'none';
    });

    deleteForEveryoneBtn.addEventListener('click', function() {
        const msgId = contextMenu.dataset.msgId;
        if (msgId) {
            if (confirm('Are you sure you want to delete this for everyone? This cannot be undone.')) {
                deleteMessage(msgId, 'everyone');
            }
        }
        contextMenu.style.display = 'none';
    });

    // BAGO: Kunin 'yung reaction picker
    const reactionPicker = document.getElementById('reactionPicker');

    document.addEventListener('click', function(e) {
        // Isara ang context menu (delete)
        if (contextMenu.style.display === 'block' && !e.target.closest('.btn-bubble-menu')) {
            contextMenu.style.display = 'none';
        }
        // Isara ang reaction picker
        if (reactionPicker.classList.contains('visible') && !e.target.closest('.btn-react-emoji') && !e.target.closest('.reaction-picker')) {
            reactionPicker.classList.remove('visible');
        }
        // Isara ang active actions
        if (!e.target.closest('.message-wrapper')) {
            document.querySelectorAll('.message-wrapper.actions-visible').forEach(wrapper => {
                wrapper.classList.remove('actions-visible');
            });
        }
    });
    // --- END: CONTEXT MENU ---

    // --- 3. CORE FUNCTIONS ---

    function loadChatList(query = '') {
        fetch('messaging/ajax_get_chat_list.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'query=' + encodeURIComponent(query)
        })
        .then(response => response.text())
        .then(data => {
            chatList.innerHTML = data;
            attachChatClickListeners();
            if (currentReceiverId) {
                const activeItem = document.querySelector(`.chat-item[data-user-id='${currentReceiverId}']`);
                if (activeItem) activeItem.classList.add('active');
            }
        })
        .catch(error => console.error('Error loading chat list:', error));
    }

    function loadMessages(receiverId, userName, userAvatar) {
        if (messagePollingInterval) {
            clearInterval(messagePollingInterval);
        }
        currentReceiverId = receiverId; 
        currentReplyToId = null; // BAGO: I-reset ang reply 'pag nagpalit ng chat
        
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.userId == receiverId) {
                item.classList.add('active');
                const unreadBadge = item.querySelector('.chat-unread-count');
                if (unreadBadge) unreadBadge.remove();
            }
        });
        
        // BAGO: Idinagdag ang reply-preview-bar
        const chatWindowHTML = `
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="chat-avatar">${userAvatar}</div>
                    <div>
                        <h3>${userName}</h3>
                        <p class="chat-status">Active now</p>
                    </div>
                </div>
                <div class="chat-header-actions">
                     <button class="btn-icon" id="toggleDetailsButtonDynamic">ℹ️</button>
                </div>
            </div>
            <div id="convoSearchContainer" class="convo-search-container" style="display: none;">
                <input type="text" id="convoSearchInput" placeholder="Search in conversation...">
                <button id="convoSearchClose" class="btn-icon">&times;</button>
            </div>
            <div class="message-area" id="messageArea">
                <div class="chat-empty-state">Loading messages...</div>
            </div>
            
            <div class="reply-preview-bar" id="replyPreviewBar">
                <div class="reply-preview-content">
                    <strong>Replying to...</strong>
                    <p id="replyPreviewText">Message content...</p>
                </div>
                <button class="btn-icon" id="cancelReplyButton">&times;</button>
            </div>
            
            <div class="message-input-area">
                <button class="btn-icon" id="uploadButtonDynamic">➕</button>
                <input type="text" placeholder="Type a message..." id="messageInputDynamic">
                <button class="btn-icon btn-send" id="sendMessageButtonDynamic">➡️</button>
            </div>
        `;
        chatWindow.innerHTML = chatWindowHTML;
        
        const messageArea = document.getElementById('messageArea'); 

        // --- BINAGO: Master Click Listener ---
        messageArea.addEventListener('click', function(e) {
            const clickedWrapper = e.target.closest('.message-wrapper');
            const clickedAction = e.target.closest('.message-actions');
            const clickedReaction = e.target.closest('.message-reaction-item');

            // --- 1. Alisin ang 'actions-visible' sa lahat maliban sa pinindot ---
            document.querySelectorAll('.message-wrapper.actions-visible').forEach(wrapper => {
                if (wrapper !== clickedWrapper) {
                    wrapper.classList.remove('actions-visible');
                }
            });

            // --- 2. Kung message wrapper ang pinindot (pero HINDI action button) ---
            if (clickedWrapper && !clickedAction && !clickedReaction) {
                clickedWrapper.classList.toggle('actions-visible');
            }
            
            // --- 3. Kung "..." (More) button ang pinindot ---
            if (e.target.closest('.btn-bubble-menu')) {
                const bubble = e.target.closest('.message-bubble');
                showBubbleContextMenu(e, bubble);
            }
            
            // --- 4. Kung "😊" (React) button ang pinindot ---
            else if (e.target.closest('.btn-react-emoji')) {
                e.stopPropagation(); // Pigilan 'yung document click listener
                const reactButton = e.target.closest('.btn-react-emoji');
                const msgId = reactButton.dataset.msgId;
                showReactionPicker(reactButton, msgId);
            }
            
            // --- 5. Kung "↩️" (Reply) button ang pinindot ---
            else if (e.target.closest('.btn-reply')) {
                const bubble = e.target.closest('.message-bubble');
                const msgId = bubble.dataset.msgId;
                const text = bubble.querySelector('.bubble-content-wrapper p, .chat-file-link .file-name')?.textContent || '[Attachment]';
                const senderName = bubble.classList.contains('outgoing') ? 'Yourself' : userName;
                showReplyPreviewBar(msgId, senderName, text);
                clickedWrapper.classList.remove('actions-visible'); 
            }
            
            // --- 6. Kung Image ang pinindot ---
            else if (e.target.classList.contains('chat-image')) {
                showImagePreview(e.target.src); 
            }

            // --- 7. BAGO: Kung existing reaction ang pinindot (para i-un-react) ---
            else if (clickedReaction) {
                if (clickedReaction.classList.contains('my-reaction')) {
                    const msgId = clickedReaction.closest('.message-bubble').dataset.msgId;
                    const emoji = clickedReaction.dataset.emoji;
                    sendReaction(msgId, emoji); // I-se-send ulit 'yung emoji, na-de-delete sa backend
                }
            }
        });
        
        // --- 8. Itago ang menus kung nag-scroll ---
        messageArea.addEventListener('scroll', function() {
             document.querySelectorAll('.message-wrapper.actions-visible').forEach(wrapper => {
                wrapper.classList.remove('actions-visible');
            });
            if (contextMenu.style.display === 'block') {
                contextMenu.style.display = 'none';
            }
            if (reactionPicker.classList.contains('visible')) {
                reactionPicker.classList.remove('visible');
            }
        });
        // --- END NG BINAGO ---

        const chatDetailsHTML = `
            <div class="details-header">
                <div class="details-avatar">${userAvatar}</div>
                <h3>${userName}</h3>
                <p class="chat-status">Active now</p>
            </div>
            <div class="details-actions">
                <a href="profile.php?user_id=${receiverId}&from=messages" class="action-button">
                    <span class="icon">👤</span><span>Profile</span>
                </a>
                <div class="action-button" id="muteButtonDynamic"><span class="icon">🔇</span><span>Mute</span></div>
                <div class="action-button" id="searchConvoButtonDynamic"><span class="icon">🔍</span><span>Search</span></div>
            </div>
            <div class="details-accordion">
                <div class="accordion-item">
                    <button class="accordion-header" id="mediaAccordionHeader">
                        <span>Media & files</span>
                        <span class="accordion-icon">▼</span>
                    </button>
                    <div class="accordion-content" id="mediaAccordionContent" style="display: none;">
                        <div class="media-tabs">
                            <button class="media-tab active" data-tab="media">Media</button>
                            <button class="media-tab" data-tab="files">Files</button>
                        </div>
                        <div class="media-tab-content active" id="media-tab-media">
                            <p class='empty-media'>Loading media...</p>
                        </div>
                        <div class="media-tab-content" id="media-tab-files">
                            <p class='empty-media'>Loading files...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        detailsPanel.innerHTML = chatDetailsHTML;
        
        attachDynamicButtonListeners(receiverId);
        fetchAndDisplayMessages(receiverId);

        messagePollingInterval = setInterval(() => {
            fetchAndDisplayMessages(receiverId, false); 
        }, 3000); 
    }
    
    function fetchAndDisplayMessages(receiverId, scrollToBottom = true) {
        const messageArea = document.getElementById('messageArea');
        if (!messageArea) return; 
        
        const shouldStayScrolled = !scrollToBottom && (messageArea.scrollHeight - messageArea.scrollTop - messageArea.clientHeight < 200);

        // --- ITO 'YUNG CACHE BUSTER ---
        fetch('messaging/ajax_fetch_messages.php?v=1.1', { // Idinagdag ang ?v=1.1
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'receiver_id=' + receiverId
        })
        .then(response => response.text())
        .then(data => {
            const activeWrapper = document.querySelector('.message-wrapper.actions-visible .message-bubble');
            const activeMsgId = activeWrapper ? activeWrapper.dataset.msgId : null;
            
            messageArea.innerHTML = data;
            
            if (activeMsgId) {
                const newActiveWrapper = document.querySelector(`.message-bubble[data-msg-id='${activeMsgId}']`);
                if (newActiveWrapper) {
                    newActiveWrapper.closest('.message-wrapper').classList.add('actions-visible');
                }
            }
            
            if (scrollToBottom || shouldStayScrolled) {
                messageArea.scrollTop = messageArea.scrollHeight;
            }
        })
        .catch(error => console.error('Error fetching messages:', error));
    }

    function sendMessage(receiverId) {
        const messageInput = document.getElementById('messageInputDynamic');
        if (!messageInput) return;
        const message = messageInput.value;
        if (message.trim() === '') return; 

        const formData = new URLSearchParams();
        formData.append('receiver_id', receiverId);
        formData.append('message', message);
        if (currentReplyToId) {
            formData.append('reply_to_id', currentReplyToId);
        }
        
        fetch('messaging/ajax_send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                messageInput.value = ''; 
                hideReplyPreviewBar(); 
                fetchAndDisplayMessages(receiverId, true);
                loadChatList(); 
            } else {
                alert('Error sending message: ' + data);
            }
        })
        .catch(error => console.error('Error sending message:', error));
    }

    function uploadFile(file) {
        // ... (walang pagbabago)
    }

    function searchInConversation() {
        // ... (walang pagbabago)
    }

    // --- MGA HELPER FUNCTIONS ---

    function showBubbleContextMenu(event, bubble) {
        event.preventDefault(); 
        event.stopPropagation();
        if (!bubble) return;
        const msgId = bubble.dataset.msgId;
        if (!msgId) return;
        
        reactionPicker.classList.remove('visible'); // Itago 'yung reaction picker
        
        if (contextMenu.style.display === 'block') {
            contextMenu.style.display = 'none';
        }
        const deleteEveryoneBtn = contextMenu.querySelector('#deleteForEveryone');
        if (bubble.classList.contains('outgoing')) {
            deleteEveryoneBtn.style.display = 'block';
        } else {
            deleteEveryoneBtn.style.display = 'none';
        }
        contextMenu.style.display = 'block';
        const rect = event.target.getBoundingClientRect();
        let top = rect.bottom + window.scrollY + 5;
        let left = rect.left + window.scrollX - (contextMenu.offsetWidth / 2) + (rect.width / 2);
        if (left < 10) left = 10;
        if (left + contextMenu.offsetWidth > window.innerWidth - 10) {
            left = window.innerWidth - contextMenu.offsetWidth - 10;
        }
        contextMenu.style.top = `${top}px`;
        contextMenu.style.left = `${left}px`;
        contextMenu.dataset.msgId = msgId;
    }

    function sendReaction(msgId, emoji) {
        fetch('messaging/ajax_add_reaction.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `msg_id=${msgId}&emoji=${encodeURIComponent(emoji)}` // Idinagdag ang emoji
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                fetchAndDisplayMessages(currentReceiverId, false);
            } else {
                console.error('React Error:', data);
            }
        })
        .catch(error => console.error('React Fetch Error:', error));
    }

    function showReactionPicker(button, msgId) {
        contextMenu.style.display = 'none'; // Itago 'yung delete menu
        
        const rect = button.getBoundingClientRect();
        reactionPicker.style.top = `${rect.top + window.scrollY - reactionPicker.offsetHeight - 10}px`; // 10px sa taas ng button
        reactionPicker.style.left = `${rect.left + window.scrollX - (reactionPicker.offsetWidth / 2) + (rect.width / 2)}px`;
        
        reactionPicker.classList.add('visible');
        reactionPicker.dataset.msgId = msgId; // Itago 'yung msgId dito
    }

    reactionPicker.addEventListener('click', function(e) {
        const emojiButton = e.target.closest('.reaction-emoji');
        if (emojiButton) {
            const emoji = emojiButton.dataset.emoji;
            const msgId = reactionPicker.dataset.msgId;
            if (msgId && emoji) {
                sendReaction(msgId, emoji);
            }
            reactionPicker.classList.remove('visible');
        }
    });

    function showImagePreview(src) {
        const modal = document.getElementById('imagePreviewModal');
        const modalImg = document.getElementById('fullScreenImage');
        modal.style.display = 'flex';
        modalImg.src = src;
    }
    const modal = document.getElementById('imagePreviewModal');
    const closeModal = document.getElementById('imageModalCloseButton');
    closeModal.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });


    function loadMediaAndFiles(receiverId) {
        fetch('messaging/ajax_fetch_media.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'receiver_id=' + receiverId
        })
        .then(response => response.json())
        .then(data => {
            const mediaTab = document.getElementById('media-tab-media');
            const filesTab = document.getElementById('media-tab-files');
            if (mediaTab) mediaTab.innerHTML = data.media;
            if (filesTab) filesTab.innerHTML = data.files;
        })
        .catch(error => {
            console.error('Error fetching media:', error);
            const mediaTab = document.getElementById('media-tab-media');
            const filesTab = document.getElementById('media-tab-files');
            if (mediaTab) mediaTab.innerHTML = "<p class='empty-media'>Error loading media.</p>";
            if (filesTab) filesTab.innerHTML = "<p class='empty-media'>Error loading files.</p>";
        });
    }
    
    function attachMediaTabListeners() {
        const accordionContent = document.getElementById('mediaAccordionContent');
        if (!accordionContent) return;
        accordionContent.addEventListener('click', function(e) {
            if (e.target.classList.contains('media-tab')) {
                accordionContent.querySelectorAll('.media-tab').forEach(tab => tab.classList.remove('active'));
                accordionContent.querySelectorAll('.media-tab-content').forEach(content => content.classList.remove('active'));
                const tabName = e.target.dataset.tab;
                e.target.classList.add('active');
                document.getElementById(`media-tab-${tabName}`).classList.add('active');
            }
        });
    }

    function showReplyPreviewBar(msgId, senderName, text) {
        currentReplyToId = msgId;
        const bar = document.getElementById('replyPreviewBar');
        const barText = document.getElementById('replyPreviewText');
        const barSender = bar.querySelector('strong');
        
        barSender.textContent = `Replying to ${senderName}`;
        barText.textContent = text;
        bar.classList.add('active');
        document.getElementById('messageInputDynamic').focus();
    }

    function hideReplyPreviewBar() {
        currentReplyToId = null;
        const bar = document.getElementById('replyPreviewBar');
        bar.classList.remove('active');
    }

    // --- 4. ATTACH EVENT LISTENERS ---
    
    function attachDynamicButtonListeners(receiverId) {
        document.getElementById('sendMessageButtonDynamic').addEventListener('click', () => {
            sendMessage(receiverId);
        });
        document.getElementById('messageInputDynamic').addEventListener('keyup', (event) => {
            if (event.key === 'Enter') sendMessage(receiverId);
        });
        document.getElementById('toggleDetailsButtonDynamic').addEventListener('click', () => {
            container.classList.toggle('details-hidden');
        });
        document.getElementById('uploadButtonDynamic').addEventListener('click', () => {
            document.getElementById('uploadModal').style.display = 'flex';
        });

        document.getElementById('cancelReplyButton').addEventListener('click', hideReplyPreviewBar);
        
        const searchConvoButton = document.getElementById('searchConvoButtonDynamic');
        const convoSearchContainer = document.getElementById('convoSearchContainer');
        const convoSearchInput = document.getElementById('convoSearchInput');
        const convoSearchClose = document.getElementById('convoSearchClose');
        
        if (searchConvoButton) { /* ... (search logic) ... */ }
        if (convoSearchClose) { /* ... (search logic) ... */ }
        if (convoSearchInput) { /* ... (search logic) ... */ }

        const mediaHeader = document.getElementById('mediaAccordionHeader');
        const mediaContent = document.getElementById('mediaAccordionContent');
        if (mediaHeader) {
            mediaHeader.addEventListener('click', function() {
                const isOpen = mediaContent.style.display === 'block';
                mediaContent.style.display = isOpen ? 'none' : 'block';
                this.querySelector('.accordion-icon').textContent = isOpen ? '▼' : '▲';
                if (!isOpen && !mediaContent.dataset.loaded) {
                    loadMediaAndFiles(receiverId);
                    mediaContent.dataset.loaded = 'true';
                }
            });
            attachMediaTabListeners();
        }
    }

    function attachChatClickListeners() {
        document.querySelectorAll('.chat-item').forEach(item => {
            item.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const userName = this.dataset.userName;
                const userAvatar = this.querySelector('.chat-avatar').innerHTML;
                
                loadMessages(userId, userName, userAvatar);
            });
        });
    }

    // --- 5. INITIAL PAGE LOAD ATTACHMENTS ---
    
    function startChatListPolling() {
        if (chatListPollingInterval) {
            clearInterval(chatListPollingInterval);
        }
        chatListPollingInterval = setInterval(() => {
            if (document.activeElement !== searchInput) {
                loadChatList(searchInput.value);
            }
        }, 5000);
    }
    
    loadChatList();
    startChatListPolling();
    
    searchInput.addEventListener('keyup', function() {
        let query = searchInput.value;
        clearInterval(chatListPollingInterval); 
        loadChatList(query);
        setTimeout(() => {
            if (searchInput.value === query) { 
                startChatListPolling();
            }
        }, 1000);
    });
    
    // Modal Listeners
    const uploadModal = document.getElementById('uploadModal');
    const modalCloseButton = document.getElementById('modalCloseButton');
    const photoUpload = document.getElementById('photoUpload');
    const fileUpload = document.getElementById('fileUpload');
    if (uploadModal && modalCloseButton) {
        modalCloseButton.addEventListener('click', () => uploadModal.style.display = 'none');
        uploadModal.addEventListener('click', (event) => {
            if (event.target === uploadModal) uploadModal.style.display = 'none';
        });
        photoUpload.addEventListener('change', function() {
            if (this.files.length > 0) {
                uploadFile(this.files[0]);
                uploadModal.style.display = 'none';
                this.value = null; 
            }
        });
        fileUpload.addEventListener('change', function() {
            if (this.files.length > 0) {
                uploadFile(this.files[0]);
                uploadModal.style.display = 'none';
                this.value = null; 
            }
        });
    }
    
    // Header Buttons
    document.getElementById('chatOptionsButton').addEventListener('click', () => alert('Chat Options clicked!'));
    document.getElementById('newMessageButton').addEventListener('click', () => alert('New Message clicked!'));

    
    // --- 6. AUTO-OPEN CHAT FROM NOTIFICATION ---
    const autoOpenUserId = document.body.dataset.openChat;
    
    if (autoOpenUserId && autoOpenUserId !== 'null') {
        setTimeout(() => {
            const chatToOpen = document.querySelector(`.chat-item[data-user-id='${autoOpenUserId}']`);
            if (chatToOpen) {
                console.log('Opening chat with user ' + autoOpenUserId);
                chatToOpen.click(); 
            } else {
                console.warn('Tried to open chat, but user not found in list: ' + autoOpenUserId);
            }
        }, 500); 
    }

});