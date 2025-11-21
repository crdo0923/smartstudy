<?php
// Ginamit natin 'yung config galing sa messaging folder
include 'messaging/config.php'; 

// --- BAGONG LOGIC PARA SA PROFILE VIEW ---

// Check kung sino ang naka-login (gamit ang 'user_id' base sa luma mong profile.php)
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}
$current_user_id = $_SESSION['user_id'];

$is_own_profile = true;
$user_id_to_view = 0;

// 1. TINGNAN KUNG MAY 'user_id' SA URL
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    // Tinitingnan ang profile ng IBA
    $user_id_to_view = (int)$_GET['user_id'];
    
    if ($user_id_to_view != $current_user_id) {
        $is_own_profile = false;
    } else {
        $is_own_profile = true;
    }
} else {
    // Tinitingnan ang SARILING profile
    $user_id_to_view = $current_user_id;
    $is_own_profile = true;
}

// 2. KUNIN ANG DATA MULA SA DATABASE
$sql = "SELECT * FROM users WHERE id = ?"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id_to_view);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("User not found.");
}
$user = $result->fetch_assoc();

// 3. I-ASSIGN ANG VARIABLES
$firstname = $user['firstname'];
$lastname = $user['lastname'];
$email = $user['email'];
$student_id = $user['student_id'];
$program = $user['program'];
$bio = $user['bio'] ?? 'No bio set.'; 
$profile_photo = $user['profile_photo'] ?? ''; 

// --- ⭐ BAGONG LOGIC PARA SA BACK BUTTON ⭐ ---
$back_link = "dashboard.php";
$back_text = "Back to Dashboard";

if (isset($_GET['from']) && $_GET['from'] === 'messages') {
    $back_link = "messaging.php";
    $back_text = "Back to Messages";
}
// --- END NG BAGONG LOGIC ---

// Stats (Hardcoded placeholder pa rin)
$current_points = 1250;
$rank = '#5';
$streak = 7;
$total_study_hours = 124;

// Message handler
$message = '';
if ($is_own_profile && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_profile') {
    $message = 'Your profile information has been successfully updated!';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $firstname; ?>'s Profile - SmartStudy</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/loading.css">
</head>
<body class="profile-body">
    
    <div class="page-loader">
        </div>

    <div class="profile-container">
        <div class="profile-actions">
            <a href="<?php echo $back_link; ?>" class="btn-back">
                <span>←</span> <?php echo $back_text; ?>
            </a>
            
            <?php if ($is_own_profile): ?>
                <div class="action-buttons">
                    <button class="btn-save" id="saveProfile" type="submit" form="main-profile-form">
                        <span>💾</span> Save Changes
                    </button>
                    <a href="php/logout.php" class="btn-logout">
                        <span>🚪</span> Logout
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($message)): ?>
            <div style="background-color: #10b981; ...">
                <?= $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-content">
            
            <div class="profile-header-card">
                <div class="profile-banner"></div>
                <div class="profile-header-content">
                    <div class="profile-photo-section">
                        <div class="profile-photo-wrapper">
                            <?php if (empty($profile_photo)): ?>
                                <div class="default-avatar">
                                    <?php echo strtoupper(substr($firstname, 0, 1)); ?>
                                </div>
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" class="profile-photo">
                            <?php endif; ?>
                            
                            <?php if ($is_own_profile): ?>
                                <label for="photoUpload" class="photo-upload-btn">
                                    <span>📷</span>
                                    <input type="file" id="photoUpload" accept="image/*" style="display: none;">
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="profile-header-info">
                        <h1><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></h1>
                        <p class="profile-id">Student ID: <?php echo htmlspecialchars($student_id); ?></p>
                        <div class="profile-badges">
                             <span class="badge rank-badge">🏅 Rank <?php echo $rank; ?></span>
                             <span class="badge points-badge">🏆 <?php echo number_format($current_points); ?> Points</span>
                             <span class="badge streak-badge">🔥 <?php echo $streak; ?> Day Streak</span>
                        </div>
                    </div>

                    <div class="profile-stats-mini">
                        <div class="stat-mini">
                            <span class="stat-value"><?php echo $total_study_hours; ?>h</span>
                            <span class="stat-label">Total Study</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-value">23</span>
                            <span class="stat-label">Achievements</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-value">8</span>
                            <span class="stat-label">Subjects</span>
                        </div>
                    </div>
                </div>
            </div>

            <form id="main-profile-form" action="profile.php" method="POST" style="display: contents;">
                <input type="hidden" name="action" value="save_profile">
                
                <div class="profile-edit-grid">
                    
                    <div class="edit-card">
                        <div class="card-header">
                            <h3>👤 Personal Information</h3>
                        </div>
                        <div class="edit-form" id="personal-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" class="form-input" <?php if (!$is_own_profile) echo 'readonly'; ?>>
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" class="form-input" <?php if (!$is_own_profile) echo 'readonly'; ?>>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="form-input" <?php if (!$is_own_profile) echo 'readonly'; ?>>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Student ID</label>
                                    <input type="text" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" class="form-input" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Program</label>
                                    <select class="form-input" name="program" <?php if (!$is_own_profile) echo 'disabled'; ?>>
                                        <option value="BSIT" <?php if ($program == 'BSIT') echo 'selected'; ?>>BSIT</option>
                                        <option value="BSCS" <?php if ($program == 'BSCS') echo 'selected'; ?>>BSCS</option>
                                        <option value="BSIS" <?php if ($program == 'BSIS') echo 'selected'; ?>>BSIS</option>
                                        <option value="BSECE" <?php if ($program == 'BSECE') echo 'selected'; ?>>BSECE</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="edit-card">
                        <div class="card-header">
                            <h3>📝 Bio</h3>
                        </div>
                        <div class="edit-form" id="bio-form">
                            <div class="form-group">
                                <textarea name="bio" class="form-textarea" rows="4" placeholder="Tell us about yourself..." maxlength="200" <?php if (!$is_own_profile) echo 'readonly'; ?>><?php echo htmlspecialchars($bio); ?></textarea>
                                <span class="char-count">Character limit: 200</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($is_own_profile): ?>
                        <div class="edit-card">
                            <div class="card-header">
                                <h3>🎓 Academic Information</h3>
                            </div>
                            <div class="edit-form" id="academic-form">
                                 <div class="form-group">
                                    <label>Year Level</label>
                                    <select class="form-input" name="year_level">
                                        <option>1st Year</option>
                                        <option>2nd Year</option>
                                        <option selected>3rd Year</option>
                                        <option>4th Year</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Section</label>
                                    <input type="text" name="section" value="BSIT-3A" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label>Preferred Study Time</label>
                                    <select class="form-input" name="study_time">
                                        <option selected>Morning (6AM - 12PM)</option>
                                        <option>Afternoon (12PM - 6PM)</option>
                                        <option>Evening (6PM - 12AM)</option>
                                        <option>Night (12AM - 6AM)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="edit-card achievements-card">
                            <div class="card-header">
                                <h3>🏆 Recent Achievements</h3>
                                <a href="#" class="view-all">View All →</a>
                            </div>
                            <div class="achievements-grid">
                               <div class="achievement-item unlocked">
                                    <span class="achievement-icon">🥇</span>
                                    <div class="achievement-info">
                                        <h4>First Blood</h4>
                                        <p>Complete your first study session</p>
                                    </div>
                                </div>
                                <div class="achievement-item unlocked">
                                    <span class="achievement-icon">🔥</span>
                                    <div class="achievement-info">
                                        <h4>Week Warrior</h4>
                                        <p>Study for 7 days straight</p>
                                    </div>
                                </div>
                                <div class="achievement-item locked">
                                    <span class="achievement-icon">🏅</span>
                                    <div class="achievement-info">
                                        <h4>Century Club</h4>
                                        <p>Reach 100 total study hours</p>
                                    </div>
                                </div>
                                <div class="achievement-item locked">
                                    <span class="achievement-icon">👑</span>
                                    <div class="achievement-info">
                                        <h4>Top of the Class</h4>
                                        <p>Reach #1 in leaderboard</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script src="js/profile.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bioTextarea = document.querySelector('textarea[name="bio"]');
            const charCountSpan = document.querySelector('.char-count');

            if (bioTextarea && charCountSpan) {
                const maxLength = bioTextarea.maxLength || 200; 
                const updateCharCount = () => {
                    const currentLength = bioTextarea.value.length;
                    charCountSpan.textContent = `Characters: ${currentLength} / ${maxLength}`;
                };
                updateCharCount();
                bioTextarea.addEventListener('input', updateCharCount);
            }
        });
    </script>
</body>
</html>