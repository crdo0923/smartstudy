<?php
session_start();

// Check if user is logged in. Redirect to auth.php if not.
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

// ===================================================
// CONFIGURATION AND DATABASE CONNECTION (Placeholder)
// ===================================================
$servername = 'localhost';
$db_username = 'root';
$db_password = ''; 
$database = 'smart_study';
$user_id = (int)($_SESSION['user_id'] ?? 0); 

$firstname = htmlspecialchars($_SESSION['firstname'] ?? 'Student');
$program = htmlspecialchars($_SESSION['program'] ?? 'BSIT');
$message = '';
$error = '';

// ===================================================
// HANDLE SETTINGS UPDATE (Simplified example - Keep this as placeholder)
// ===================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_preferences') {
    // NOTE: Perform validation and database UPDATE here for preferences.
    $message = 'Application preferences updated successfully!';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title> 
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/settings.css"> 
    <link rel="stylesheet" href="css/loading.css"> 
</head>
<body class="profile-body"> 
    
    <div class="page-loader">
        <div class="loader-container">
            <div class="loader-icon">🧠</div>
            <div class="loader-text">Loading Settings...</div>
            <div class="loader-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <div class="loader-progress">
                <div class="progress-bar"></div>
            </div>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-actions">
            <a href="dashboard.php" class="btn-back">
                <span>←</span> Back to Dashboard
            </a>
            <div class="action-buttons">
                <button class="btn-save" id="saveSettings" type="button"> 
                    <span>💾</span> Save Changes
                </button>
                </div>
        </div>

        <div class="profile-content">
            
            <div class="section-header">
                <h1>⚙️ Settings</h1>
                <p>Manage your study preferences, security features, and data options.</p>
            </div>

            <?php if (!empty($message)): ?>
                <div style="background-color: #10b981; color: white; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <?= $message; ?>
                </div>
            <?php endif; ?>

            <div class="settings-container">
                
                <div class="settings-card">
                    <h3>📚 Study Preferences</h3>
                    <form action="settings.php" method="POST" id="preferences-form">
                        <input type="hidden" name="action" value="update_preferences">
                        
                        <div class="setting-group">
                            <label for="defaultSubject">Default Subject for Quick Add</label>
                            <select id="defaultSubject" name="defaultSubject" class="form-input">
                                <option value="Database">Database Management Systems</option>
                                <option value="Web Dev" selected>Web Development</option>
                                <option value="Data Structures">Data Structures & Algo</option>
                                <option value="Networks">Computer Networks</option>
                            </select>
                        </div>
                        
                        <div class="setting-toggle-wrapper">
                            <div class="setting-toggle">
                                <span>Enable Pomodoro Sound Notifications</span>
                                <label class="switch">
                                    <input type="checkbox" checked name="pomodoro_sound">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-toggle-wrapper">
                            <div class="setting-toggle">
                                <span>AI Scheduler Auto-Prioritization</span>
                                <label class="switch">
                                    <input type="checkbox" checked name="ai_priority">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-toggle-wrapper">
                            <div class="setting-toggle">
                                <span>Dark Mode Default</span>
                                <label class="switch">
                                    <input type="checkbox" checked name="dark_mode">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="settings-card">
                    <h3>🔒 Security & Password</h3>
                    <form action="php/update_security.php" method="POST">
                        <div class="setting-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" name="currentPassword" placeholder="Required to change password" class="form-input">
                        </div>
                        <div class="setting-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" class="form-input">
                        </div>
                        
                        <div class="setting-toggle-wrapper">
                            <div class="setting-toggle">
                                <span>Two-Factor Authentication (2FA)</span>
                                <label class="switch">
                                    <input type="checkbox" name="2fa_enabled">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn-save-settings">Update Password</button>
                    </form>
                </div>
                
                <div class="settings-card danger-card">
                    <h3>⚠️ Danger Zone</h3>
                    <p>These actions are permanent and cannot be undone. Proceed with caution.</p>
                    
                    <div class="danger-actions-wrapper"> 
                        <button onclick="if(confirm('Are you sure you want to download all your data?')) { window.location.href='php/export_data.php'; }" 
                                class="btn-save-settings btn-download">
                            <span>💾</span> Download My Study Data
                        </button>
                        
                        <button onclick="if(confirm('WARNING: Are you sure you want to permanently delete ALL your study data? This is irreversible.')) { window.location.href='php/delete_data.php'; }" 
                                class="btn-save-settings btn-delete-data">
                            <span>🗑️</span> Delete All Study Data
                        </button>
                        
                        <button onclick="if(confirm('FINAL WARNING: Are you absolutely sure you want to permanently DELETE YOUR ENTIRE ACCOUNT? This is irreversible.')) { window.location.href='php/delete_account.php'; }" 
                                class="btn-save-settings btn-delete-account">
                            <span>❌</span> Delete Account Permanently
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Functionality for the toggle switches (for UX)
            document.querySelectorAll('.switch input').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const settingName = this.closest('.setting-toggle').querySelector('span').textContent;
                    console.log(`Setting "${settingName}" changed to: ${this.checked ? 'Enabled' : 'Disabled'}`);
                    
                    // Note: Since the Save button submits the form, direct AJAX is not strictly necessary here, 
                    // but we keep the console log for testing.
                });
            });
            
            // IBINALIK ANG FLOATING SAVE BUTTON FUNCTIONALITY
            document.getElementById('saveSettings').addEventListener('click', function() {
                const form = document.getElementById('preferences-form');
                if (form) {
                    form.submit();
                } else {
                    alert('Error: Preferences form not found.');
                }
            });
        });
    </script>
</body>
</html>