// ========================================
// PROFILE PAGE FUNCTIONALITY
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // EDIT TOGGLE FUNCTIONALITY
    // ========================================
    const editButtons = document.querySelectorAll('.btn-edit-toggle');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetFormId = this.getAttribute('data-target');
            const form = document.getElementById(targetFormId);
            const inputs = form.querySelectorAll('input, select, textarea');
            
            if (this.classList.contains('editing')) {
                // Save mode - make readonly again
                inputs.forEach(input => {
                    if (input.type === 'checkbox') return;
                    input.readOnly = true;
                    input.disabled = true;
                });
                this.innerHTML = '<span>✏️</span> Edit';
                this.classList.remove('editing');
            } else {
                // Edit mode - make editable
                inputs.forEach(input => {
                    if (input.type === 'checkbox') return;
                    input.readOnly = false;
                    input.disabled = false;
                });
                this.innerHTML = '<span>💾</span> Done';
                this.classList.add('editing');
            }
        });
    });

    // ========================================
    // PHOTO UPLOAD
    // ========================================
    const photoUpload = document.getElementById('photoUpload');
    const defaultAvatar = document.querySelector('.default-avatar');
    
    if (photoUpload) {
        photoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Create new img element
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'profile-photo';
                    img.alt = 'Profile';
                    
                    // Replace default avatar
                    const photoWrapper = document.querySelector('.profile-photo-wrapper');
                    const existingPhoto = photoWrapper.querySelector('.profile-photo');
                    const existingAvatar = photoWrapper.querySelector('.default-avatar');
                    
                    if (existingPhoto) {
                        existingPhoto.remove();
                    }
                    if (existingAvatar) {
                        existingAvatar.remove();
                    }
                    
                    photoWrapper.insertBefore(img, photoWrapper.firstChild);
                    
                    // Show success message
                    showNotification('Profile photo updated! Click Save Changes to keep it.', 'success');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ========================================
    // BIO CHARACTER COUNT
    // ========================================
    const bioTextarea = document.querySelector('.form-textarea');
    const charCount = document.querySelector('.char-count');
    
    if (bioTextarea && charCount) {
        // Initialize count
        updateCharCount();
        
        bioTextarea.addEventListener('input', updateCharCount);
        
        function updateCharCount() {
            const length = bioTextarea.value.length;
            const maxLength = 200;
            charCount.textContent = `${length} / ${maxLength}`;
            
            if (length > maxLength) {
                charCount.style.color = 'var(--danger)';
                bioTextarea.value = bioTextarea.value.substring(0, maxLength);
            } else if (length > maxLength * 0.9) {
                charCount.style.color = 'var(--warning)';
            } else {
                charCount.style.color = 'var(--text-gray)';
            }
        }
    }

    // ========================================
    // SAVE PROFILE
    // ========================================
    const saveButton = document.getElementById('saveProfile');
    
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            const button = this;
            const originalHTML = button.innerHTML;
            
            // Show loading
            button.innerHTML = '<span>⏳</span> Saving...';
            button.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Collect form data
                const formData = {
                    // Personal Info
                    firstname: document.querySelector('#personal-form input[type="text"]').value,
                    // ... collect other fields
                    
                    // Bio
                    bio: document.querySelector('.form-textarea').value,
                    
                    // Preferences
                    aiOptimization: document.querySelectorAll('.preference-label input')[0].checked,
                    autoFocus: document.querySelectorAll('.preference-label input')[1].checked,
                    dailyReminders: document.querySelectorAll('.preference-label input')[2].checked,
                    trackStreak: document.querySelectorAll('.preference-label input')[3].checked,
                };
                
                console.log('Saving profile data:', formData);
                
                // Reset button
                button.innerHTML = '<span>✅</span> Saved!';
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }, 2000);
                
                showNotification('Profile updated successfully!', 'success');
            }, 1500);
        });
    }

    // ========================================
    // SECURITY BUTTONS
    // ========================================
    const securityButtons = document.querySelectorAll('.btn-security');
    
    securityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim();
            
            if (action.includes('Change Password')) {
                showPasswordChangeModal();
            } else if (action.includes('Update Email')) {
                showEmailUpdateModal();
            } else if (action.includes('2FA')) {
                show2FAModal();
            }
        });
    });

    // ========================================
    // DANGER ZONE
    // ========================================
    const dangerButtons = document.querySelectorAll('.btn-danger-action');
    
    dangerButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim();
            
            if (action.includes('Delete All Study Data')) {
                if (confirm('⚠️ Are you sure you want to delete all your study data? This cannot be undone!')) {
                    showNotification('Study data deletion initiated...', 'warning');
                    // Implement deletion logic
                }
            } else if (action.includes('Delete Account')) {
                const confirmation = prompt('⚠️ Type "DELETE" to permanently delete your account:');
                if (confirmation === 'DELETE') {
                    showNotification('Account deletion initiated... Goodbye 😢', 'danger');
                    setTimeout(() => {
                        // window.location.href = 'php/delete-account.php';
                    }, 2000);
                } else if (confirmation !== null) {
                    showNotification('Account deletion cancelled', 'info');
                }
            }
        });
    });

    // ========================================
    // HELPER FUNCTIONS
    // ========================================
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: var(--dark-card);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            color: var(--text-light);
            z-index: 10000;
            animation: slideIn 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        `;
        
        // Color based on type
        if (type === 'success') {
            notification.style.borderColor = 'var(--success)';
        } else if (type === 'warning') {
            notification.style.borderColor = 'var(--warning)';
        } else if (type === 'danger') {
            notification.style.borderColor = 'var(--danger)';
        }
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    function showPasswordChangeModal() {
        const modal = prompt('Enter new password:');
        if (modal) {
            showNotification('Password change request submitted', 'success');
        }
    }

    function showEmailUpdateModal() {
        const modal = prompt('Enter new email address:');
        if (modal) {
            showNotification('Email update request submitted', 'success');
        }
    }

    function show2FAModal() {
        if (confirm('Enable Two-Factor Authentication?\n\nYou will receive a verification code via email.')) {
            showNotification('2FA setup initiated. Check your email!', 'success');
        }
    }
});

// Add animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);