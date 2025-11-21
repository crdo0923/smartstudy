document.addEventListener('DOMContentLoaded', function() {
    
    const notificationContainer = document.getElementById('notification-container');

    if (!notificationContainer) {
        console.error('Notification container not found!');
        return;
    }

    function checkNotifications() {
        fetch('messaging/ajax_get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.notifications.length > 0) {
                    data.notifications.forEach(notif => {
                        createNotificationPopup(notif.sender_name, notif.sender_id, notif.count);
                    });
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    function createNotificationPopup(senderName, senderId, count) {
        const popup = document.createElement('div');
        popup.className = 'notification-popup';
        
        const message = count > 1 
            ? `You have ${count} new messages from ${senderName}`
            : `New message from ${senderName}`;
        
        popup.innerHTML = `
            <div class="notif-icon">💬</div>
            <div class="notif-content">
                <strong>New Message</strong>
                <p>${message}</p>
            </div>
            <span class="notif-close">&times;</span>
        `;
        
        notificationContainer.appendChild(popup);
        setTimeout(() => popup.classList.add('show'), 100);

        popup.addEventListener('click', function(e) {
            if (!e.target.classList.contains('notif-close')) {
                window.location.href = `messaging.php?user_id=${senderId}`;
            }
        });

        popup.querySelector('.notif-close').addEventListener('click', function(e) {
            e.stopPropagation(); 
            popup.classList.remove('show');
            setTimeout(() => popup.remove(), 500);
        });
        
        setTimeout(() => {
            popup.classList.remove('show');
            setTimeout(() => popup.remove(), 500);
        }, 5000);
    }

    setInterval(checkNotifications, 10000);
    checkNotifications();
});