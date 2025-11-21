<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

// ===================================================
// CONFIGURATION AND DATABASE CONNECTION
// ===================================================
$servername = 'localhost';
$db_username = 'root';
$db_password = ''; 
$database = 'smart_study';
$user_id = (int)($_SESSION['user_id'] ?? 0); // Explicitly cast to integer for security

// Initialize connection
$conn = mysqli_connect($servername, $db_username, $db_password, $database); 

// Handle connection failure gracefully
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error()); 
    $db_fetch_success = false;
} else {
    $db_fetch_success = true;
}

// Default values from session or placeholders
$firstname = htmlspecialchars($_SESSION['firstname'] ?? 'Student');
$lastname = htmlspecialchars($_SESSION['lastname'] ?? 'User');
$program = htmlspecialchars($_SESSION['program'] ?? 'BSIT');
$student_id = htmlspecialchars($_SESSION['student_id'] ?? '2021-12345');
$bio = htmlspecialchars($_SESSION['bio'] ?? 'Computer Science enthusiast passionate about learning!');
$current_user_points = 0; // Default to 0 points

// ===================================================
// ✨ START OF FLASH MESSAGE LOGIC (UPDATED FOR TOAST)
// ===================================================
$welcome_message = null; // Default value is null
if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'] === true) {
    // I-set ang message content na gagamitin sa JavaScript
    $welcome_message = "Welcome back, " . $firstname . "! Let's get productive. 💪";
    
    // TANGGALIN ang flag agad para hindi na magpakita sa next refresh/navigation.
    unset($_SESSION['just_logged_in']);
}
// ===================================================
// ✨ END OF FLASH MESSAGE LOGIC

// Fetch updated user data including points from the database
if ($db_fetch_success && $user_id > 0) {
    // Using prepared statement for better security
    $query = "SELECT firstname, lastname, program, student_id, bio, points FROM users WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Update local variables with fresh, HTML-safe data
            $firstname = htmlspecialchars($user['firstname'] ?? 'Student');
            $lastname = htmlspecialchars($user['lastname'] ?? 'User');
            $program = htmlspecialchars($user['program'] ?? 'BSIT');
            $student_id = htmlspecialchars($user['student_id'] ?? 'N/A');
            $bio = htmlspecialchars($user['bio'] ?? 'Computer Science enthusiast passionate about learning!');
            $current_user_points = $user['points'] ?? 0;
            
            // Re-save critical data to session (for use on other pages)
            $_SESSION['firstname'] = $user['firstname']; 
            $_SESSION['lastname'] = $user['lastname'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['bio'] = $user['bio'];
        }
        mysqli_stmt_close($stmt);
    }
    // Only close the connection if it was successfully established
    if (isset($conn)) {
        mysqli_close($conn); 
    }
}

// ===================================================
// Points and Rank System (Dynamic Calculation)
// ===================================================
$medal_icon = '⭐';
$medal_title = 'Starter Rank';
$base_points = 0;
$next_rank_points = 1000;
$next_rank_medal_icon = '🥉';
$next_rank_title = 'Bronze Rank';

if ($current_user_points >= 2000) {
    $medal_icon = '🥇';
    $medal_title = 'Gold Rank';
    $base_points = 2000;
    $next_rank_points = 2500; 
    $next_rank_medal_icon = '🏆';
    $next_rank_title = 'Max Rank';
} elseif ($current_user_points >= 1500) {
    $medal_icon = '🥈';
    $medal_title = 'Silver Rank';
    $base_points = 1500;
    $next_rank_points = 2000;
    $next_rank_medal_icon = '🥇';
    $next_rank_title = 'Gold Rank';
} elseif ($current_user_points >= 1000) {
    $medal_icon = '🥉';
    $medal_title = 'Bronze Rank';
    $base_points = 1000;
    $next_rank_points = 1500;
    $next_rank_medal_icon = '🥈';
    $next_rank_title = 'Silver Rank';
} 

// Calculate Progress (Simplified/Consolidated Logic)
if ($current_user_points >= $next_rank_points) {
    $progress_percent = 100;
    $points_to_go = 0;
} else {
    $points_in_tier = $current_user_points - $base_points;
    $points_for_tier = $next_rank_points - $base_points;
    if ($points_for_tier > 0) {
        $progress_percent = ($points_in_tier / $points_for_tier) * 100;
    } else {
        $progress_percent = 0;
    }
    $points_to_go = $next_rank_points - $current_user_points;
}

$progress_percent = min(100, max(0, $progress_percent));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SmartStudy</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/loading.css">
</head>
<body>
    <div class="page-loader">
        <div class="loader-container">
            <div class="loader-icon">🧠</div>
            <div class="loader-text">Loading Dashboard...</div>
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

    <div id="toastNotification" class="toast-notification">
        <span class="toast-icon">👋</span>
        <span class="toast-text"></span>
    </div>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <span class="logo">🧠</span>
                <h2 class="logo-text">SmartStudy</h2>
            </div>
            
            <div class="profile-card" id="profileCard">
                <div class="user-avatar"><?= strtoupper(substr($firstname, 0, 1)); ?></div> 
                <div class="user-info">
                    <div class="user-name-row">
                        <h4><?= $firstname; ?></h4> 
                        <?php if (!empty($medal_icon)): ?>
                            <span class="user-medal" title="<?= $medal_title; ?>"><?= $medal_icon; ?></span> 
                        <?php endif; ?>
                    </div>
                    <p class="user-program"><?= $program; ?></p> 
                </div>
                
                <div class="gametag-popup">
                    <div class="gametag-header">
                        <div class="gametag-avatar"><?= strtoupper(substr($firstname, 0, 1)); ?></div>
                        <div>
                            <h3><?= $firstname . ' ' . $lastname; ?></h3>
                            <p class="gametag-id">ID: <?= $student_id; ?></p>
                        </div>
                    </div>
                    <div class="gametag-stats">
                        <div class="gametag-stat">
                            <span class="stat-label">Rank</span>
                            <span class="stat-value">#5 <?= $medal_icon; ?></span>
                        </div>
                        <div class="gametag-stat">
                            <span class="stat-label">Points</span>
                            <span class="stat-value"><?= number_format($current_user_points); ?></span>
                        </div>
                        <div class="gametag-stat">
                            <span class="stat-label">Course</span>
                            <span class="stat-value"><?= $program; ?></span>
                        </div>
                    </div>
                    <div class="gametag-bio">
                        <p><?= $bio; ?></p>
                    </div>
                    <a href="profile.php" class="btn-view-profile">View Full Profile →</a>
                </div>
            </div>

           <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item active" data-section="dashboard">
        <span class="nav-icon">📊</span>
        <span>Dashboard</span>
    </a>
    <a href="learning_resources.php" class="nav-item">
        <span class="nav-icon">📚</span>
        <span>Learning Resources</span>
    </a>
    <a href="messaging.php" class="nav-item">
        <span class="nav-icon">💬</span>
        <span>Messaging</span>
    </a>
    <a href="settings.php" class="nav-item">
        <span class="nav-icon">⚙️</span>
        <span>Settings</span>
    </a>
</nav>
        </div>

        <div class="sidebar-footer">
    <a href="#" class="nav-item logout" id="openLogoutModal">
    <i class="fas fa-sign-out-alt">🚪</i> 
    <span>Logout</span>
</a>
        </div>
    </aside>

    <main class="main-content">
        <section id="dashboard-section" class="content-section active">
            
            <div class="section-header">
                <h1>Study Dashboard</h1>
                <p>Your all-in-one study hub</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">⏰</div>
                    <div class="stat-info">
                        <h3 id="studyTimeDisplay">4h 30m</h3> <p>Study Time Today</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">✅</div>
                    <div class="stat-info">
                        <h3 id="tasksCompletedDisplay">8/12</h3>
                        <p>Tasks Completed</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">🔥</div>
                    <div class="stat-info">
                        <h3>7 Days</h3>
                        <p>Study Streak</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ec4899, #be185d);">🏆</div>
                    <div class="stat-info">
                        <h3><?= number_format($current_user_points); ?></h3>
                        <p>Study Points</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card scheduler-card">
                    <div class="card-header">
                        <h3>🤖 AI Smart Scheduler</h3>
                        <button class="btn-small">View All</button>
                    </div>
                    
                    <div class="ai-recommendation-concise">
                        <p><strong>💡 AI Insight:</strong> Your **peak performance** is from 9:00 AM - 11:00 AM. Database (Exam in 2 days) is automatically prioritized for this slot. Keep up the 7-day streak! ⚡</p>
                    </div>

                    <div class="schedule-section">
                        <div class="schedule-header">
                            <h4>📅 Today's Optimized Schedule</h4>
                            <span class="schedule-date">November 08, 2025</span>
                        </div>

                        <div class="schedule-list">
                            <div class="schedule-item priority-urgent">
                                <div class="schedule-indicator urgent"></div>
                                <div class="schedule-time-block">
                                    <span class="time-main">09:00 AM</span>
                                    <span class="time-duration">90 mins</span>
                                </div>
                                <div class="schedule-content">
                                    <div class="schedule-header-row">
                                        <h4>Database Management Systems</h4>
                                        <span class="priority-badge urgent">
                                            <span class="badge-icon">🔴</span>
                                            URGENT
                                        </span>
                                    </div>
                                    <p class="schedule-desc">Chapter 5: Normalization & SQL Queries Review</p>
                                    <div class="schedule-meta">
                                        <span class="meta-tag">📚 Major Exam</span>
                                        <span class="meta-tag">🤖 AI Priority</span>
                                    </div>
                                </div>
                                <button class="btn-start-task">Start</button>
                            </div>

                        </div>
                    </div>
                </div>

                <form class="dashboard-card quick-add-task-card" id="quickAddForm"> 
                    <h3>➕ Quick Add Task</h3>
                    <input type="text" placeholder="e.g., Study Networking for 2 hours" class="task-input" required id="taskName"> 
                    <div class="task-selects">
                        <select class="task-subject" id="taskSubject"> 
                            <option value="">Subject</option>
                            <option value="Database">Database</option>
                            <option value="Web Dev">Web Dev</option>
                            <option value="Data Structures">Data Structures</option>
                            <option value="Networks">Networks</option>
                            <option value="Other">Other</option>
                        </select>
                        <select class="priority-select" id="taskPriority"> 
                            <option value="Urgent">Urgent</option>
                            <option value="High">High</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-add">
                        <span>🤖</span> Add to AI Schedule
                    </button>
                </form>

                <div class="dashboard-card focus-card">
                    <div class="card-header">
                        <h3 id="focusModeTitle">🎯 Focus Mode (Pomodoro)</h3> 
                        <button class="btn-small" id="resetFocus">Reset</button> 
                    </div>
                    
                    <div class="timer-display">
                        <svg class="timer-ring" width="200" height="200">
                            <circle cx="100" cy="100" r="85" fill="none" stroke="#1e293b" stroke-width="10"/>
                            <circle cx="100" cy="100" r="85" fill="none" stroke="url(#timer-gradient)" stroke-width="10" 
                                transform="rotate(-90 100 100)"/>
                            <defs>
                                <linearGradient id="timer-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#6366f1"/>
                                    <stop offset="100%" stop-color="#ec4899"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="timer-text">
                            <span id="timerMinutes">25</span>:<span id="timerSeconds">00</span>
                        </div>
                    </div>
                    <p class="focus-mode-description" style="text-align: center; margin-bottom: 1rem; color: #94a3b8;">Time to concentrate on your tasks.</p> 
                    <div class="timer-settings">
                        <h4>Study Duration (Mins)</h4>
                        <div class="time-options" id="studyTimeOptions">
                            <label class="time-radio">
                                <input type="radio" name="study-time" value="25" checked data-time="1500"> <span>25m</span>
                            </label>
                            <label class="time-radio">
                                <input type="radio" name="study-time" value="45" data-time="2700"> <span>45m</span>
                            </label>
                            <label class="time-radio custom-select-toggle">
                                <input type="radio" name="study-time" value="custom" id="customStudyToggle">
                                <span>Custom</span>
                            </label>
                        </div>
                        <input type="number" id="customStudyTime" placeholder="Custom Mins (e.g., 60)" min="1" max="180" class="custom-time-input" style="display:none;">

                        <h4>Break Duration (Mins)</h4>
                        <div class="time-options" id="breakTimeOptions">
                            <label class="time-radio">
                                <input type="radio" name="break-time" value="5" checked data-time="300"> <span>5m</span>
                            </label>
                            <label class="time-radio">
                                <input type="radio" name="break-time" value="10" data-time="600"> <span>10m</span>
                            </label>
                            <label class="time-radio custom-select-toggle">
                                <input type="radio" name="break-time" value="custom" id="customBreakToggle">
                                <span>Custom</span>
                            </label>
                        </div>
                        <input type="number" id="customBreakTime" placeholder="Custom Mins (e.g., 15)" min="1" max="60" class="custom-time-input" style="display:none;">
                    </div>

                    <div class="focus-controls">
                        <button class="btn-focus" id="startFocus">
                            <span class="btn-icon">▶️</span> Start Focus
                        </button>
                        <div class="focus-options">
                            <label><input type="checkbox" checked> Block Distractions</label>
                            <label><input type="checkbox" checked id="autoBreakToggle"> Auto-Break</label>
                        </div>
                    </div>
                    <div class="focus-sessions" style="margin-top: 1.5rem; border-top: 1px solid #334155; padding-top: 1rem;">
                    </div>
                </div>
                
                <div class="dashboard-card analytics-card">
                    <div class="card-header">
                        <h3>📈 Analytics & Goals</h3>
                        <button class="btn-small">Details</button>
                    </div>

                    <div class="chart-container">
                        <canvas id="weeklyChart"></canvas>
                    </div>

                    <div class="goals-list">
                        <div class="goal-item">
                            <div class="goal-info">
                                <span class="goal-icon">🎯</span>
                                <div>
                                    <h4>Study 20 hours this week</h4>
                                    <p>18.5 / 20 hours</p>
                                </div>
                            </div>
                            <div class="goal-progress">
                                <div class="progress-bar-mini">
                                    <div class="progress-fill" style="width: 92.5%;"></div>
                                </div>
                                <span>92%</span>
                            </div>
                        </div>
                        <div class="goal-item">
                            <div class="goal-info">
                                <span class="goal-icon">🔥</span>
                                <div>
                                    <h4>Maintain 7-day streak</h4>
                                    <p>7 / 7 days</p>
                                </div>
                            </div>
                            <div class="goal-progress">
                                <div class="progress-bar-mini">
                                    <div class="progress-fill" style="width: 100%;"></div>
                                </div>
                                <span>100%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card rewards-card">
                    <h3>🏆 Rewards & Ranking</h3>
                    
                    <div class="next-reward">
                        <span class="reward-icon"><?= $next_rank_medal_icon; ?></span>
                        <div class="reward-info">
                            <h4>Next Tier: <?= $next_rank_title; ?></h4>
                            <p><?= number_format($points_to_go); ?> points to go</p>
                            <div class="reward-progress-bar">
                                <div class="reward-progress-fill" style="width: <?= $progress_percent; ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="leaderboard-mini">
                        <h4>Top Students</h4>
                        <div class="leaderboard-item">
                            <span class="rank">#1</span>
                            <div class="user-mini">
                                <div class="avatar-mini">P</div>
                                <span>Patrick C.</span>
                            </div>
                            <span class="points">2,100</span>
                        </div>
                        <div class="leaderboard-item">
                            <span class="rank">#2</span>
                            <div class="user-mini">
                                <div class="avatar-mini">C</div>
                                <span>Chris Z.</span>
                            </div>
                            <span class="points">1,950</span>
                        </div>
                        <div class="leaderboard-item highlight">
                            <span class="rank">#5</span>
                            <div class="user-mini">
                                <div class="avatar-mini"><?= strtoupper(substr($firstname, 0, 1)); ?></div>
                                <span>You</span>
                            </div>
                            <span class="points"><?= number_format($current_user_points); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <div id="logoutModal">
    <div class="logout-modal-content">
        <h3>👋 Confirm Logout</h3>
        <p style="color: var(--text-gray); margin: 0.5rem 0 1.5rem 0;">Are you sure you want to end your current session?</p>
        
        <div class="modal-actions">
            <button id="cancelLogout">Cancel</button>
            <button id="confirmLogout" class="btn-confirm-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>
</div>

    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    <div id="notification-container"></div>

    <script src="js/dashboard_notifications.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kunin ang message mula sa PHP variable. (Ginamit ang addslashes para maiwasan ang JS errors)
            const message = "<?php echo $welcome_message ? addslashes($welcome_message) : ''; ?>";
            const toast = document.getElementById('toastNotification');
            const toastText = toast.querySelector('.toast-text');

            if (message && toast) {
                toastText.textContent = message;
                toast.classList.add('show'); // Ipakita ang toast sa pamamagitan ng CSS class

                // Awtomatikong mag-fade out at mawawala pagkatapos ng 4 na segundo (4000ms)
                setTimeout(() => {
                    toast.classList.remove('show'); // Simulan ang fade-out animation
                    
                    // Mawawala completely sa display pagkatapos ng transition (0.5s)
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 500); 
                    
                }, 4000); // Haba ng pagpapakita bago mag-fade out
            }
        });
    </script>
    </body>
</html>