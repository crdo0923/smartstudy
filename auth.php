<?php
session_start();

// --- 1. Database Connection and Metric Fetching ---

// Connection Parameters
$servername = 'localhost';
$username = 'root'; 
$password = ''; 
$dbname = 'smart_study'; 

// Initialize metrics with fallback/default values
$active_students = "500+"; 
$total_sessions = "10k+";
$success_rate = "95%";
$connection_error = false; // Flag to check connection status


// Establish connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // Connection failed. Log the error and keep the fallback values.
    error_log("Database Connection Failed in auth.php: " . $conn->connect_error);
    $connection_error = true;
    // Note: $conn is not closed here as it never fully opened.
} else {
    // Connection successful. Fetch the data.
    
    // 1. Fetch Total Students
    $sql_students = "SELECT COUNT(*) AS total_students FROM users";
    $result_students = $conn->query($sql_students);

    if ($result_students && $result_students->num_rows > 0) {
        $row = $result_students->fetch_assoc();
        $active_students = number_format($row["total_students"]) . ""; 
    }

    // 2. Fetch Total Sessions
    $sql_sessions = "SELECT COUNT(*) AS total_sessions FROM study_sessions";
    $result_sessions = $conn->query($sql_sessions);
    
    if ($result_sessions && $result_sessions->num_rows > 0) {
        $row = $result_sessions->fetch_assoc();
        $total_sessions = number_format($row["total_sessions"]) . "";
    }

    // 3. Fetch Success Rate
    $sql_rate = "SELECT success_rate FROM analytics ORDER BY recorded_at DESC LIMIT 1";
    $result_rate = $conn->query($sql_rate);
    
    if ($result_rate && $result_rate->num_rows > 0) {
        $row = $result_rate->fetch_assoc();
        $success_rate = round($row["success_rate"]) . "%"; 
    }
    
    // Isara ang koneksyon matapos makuha ang lahat ng metrics
    $conn->close();
}


// --- PHP for handling old input and errors ---

// Get previous input data to repopulate the Registration form
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear after retrieving

// Get specific validation errors for Registration
$validation_errors = $_SESSION['validation_errors'] ?? [];
unset($_SESSION['validation_errors']); // Clear after retrieving

// Get previous input data to repopulate the Login form (Email only)
$login_email = $_SESSION['login_email'] ?? '';
unset($_SESSION['login_email']); // Clear after retrieving

// Check for a Login Error (Gagamitin sa HTML para sa error highlighting)
$has_login_error = isset($_SESSION['login_error']); 

// Helper function to echo old input value
function old_value($field) {
    global $form_data;
    echo htmlspecialchars($form_data[$field] ?? '');
}

// Helper function to echo error message for a field
function field_error($field) {
    global $validation_errors;
    if (isset($validation_errors[$field])) {
        // Gumagamit ng bagong CSS class 'error-message'
        return '<span class="error-message">' . htmlspecialchars($validation_errors[$field]) . '</span>';
    }
    return '';
}

// Helper function to check if a select option should be marked as selected
function is_selected($field, $value) {
    global $form_data;
    if (isset($form_data[$field]) && $form_data[$field] === $value) {
        return 'selected';
    }
    return '';
}


// --- 2. HTML START ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - SmartStudy</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/loading.css">
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. Loader Hide Logic (FIXED to ensure visibility before fade-out) ---
        const pageLoader = document.querySelector('.page-loader');
        if (pageLoader) {
            // I-set ang opacity to 0 pagkatapos ng maikling delay
            setTimeout(() => {
                pageLoader.style.opacity = '0'; 
                setTimeout(() => {
                    pageLoader.style.display = 'none';
                }, 300); // Tiyakin na tapos ang CSS transition
            }, 100); // Maliit na initial delay
        }
        
        // --- 2. Reliable Tab Switching for Errors/Old Data ---
        const urlParams = new URLSearchParams(window.location.search);
        const formParam = urlParams.get('form');
        
        const registerBtn = document.querySelector('.toggle-btn[data-form="register"]');
        const loginBtn = document.querySelector('.toggle-btn[data-form="login"]');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        // Titingnan kung may Registration errors/old data O may form=register sa URL
        <?php if (!empty($form_data) || !empty($validation_errors) || $formParam === 'register'): ?>
            
            // **Switch to Register Tab**
            if (registerBtn && loginBtn && loginForm && registerForm) {
                loginBtn.classList.remove('active');
                registerBtn.classList.add('active');
                
                loginForm.classList.remove('active');
                registerForm.classList.add('active');
            }
        <?php endif; ?>
    });
    
    // --- Add togglePassword function (Para gumana ang mata icon) ---
    function togglePassword(id) {
        const input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }
    </script>
    <style>
        .error-message {
            display: block;
            color: #d9534f; /* Red color for warning */
            font-size: 0.9em;
            margin-top: 5px;
            font-weight: 500;
        }
        
        /* Style para sa login error fields (kung may error, mag-highlight) */
        .form-group.login-error .input-wrapper {
            border: 1px solid #d9534f !important; /* Visual cue for error */
            box-shadow: 0 0 0 3px rgba(217, 83, 79, 0.2);
        }
        
        /* Kung kailangan ng basic styles ng loader, dapat nasa css/loading.css na ito */
    </style>
</head>
<body>
<div class="page-loader" style="opacity: 1; display: flex;">
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
<div class="auth-background">
    <div class="bg-circle circle-1"></div>
    <div class="bg-circle circle-2"></div>
    <div class="bg-circle circle-3"></div>
</div>

<a href="index.php" class="back-home">
    <span>←</span> Back to Home
</a>

<div class="auth-container">
    <div class="auth-info">
        <div class="info-content">
            <div class="logo-section">
                <span class="logo">🧠</span>
                <h1>SmartStudy</h1>
            </div>
            <h2>Welcome to Your Study Revolution</h2>
            <p>Transform your academic journey with AI-powered study planning, distraction management, and productivity tracking.</p>
            
            <div class="features-list">
                <div class="feature-item">
                    <span class="icon">🤖</span>
                    <div>
                        <h4>AI-Powered Scheduling</h4>
                        <p>Smart algorithms optimize your study time</p>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="icon">🎯</span>
                    <div>
                        <h4>Focus & Productivity</h4>
                        <p>Block distractions and stay on track</p>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="icon">📊</span>
                    <div>
                        <h4>Track Your Progress</h4>
                        <p>Detailed analytics and insights</p>
                    </div>
                </div>
            </div>

            <div class="stats-mini">
                <div class="stat-item">
                    <h3><?php echo htmlspecialchars($active_students); ?></h3>
                    <p>Students</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo htmlspecialchars($total_sessions); ?></h3>
                    <p>Study Sessions</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo htmlspecialchars($success_rate); ?></h3>
                    <p>Success Rate</p>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-forms">
        <div class="forms-wrapper">
            <div class="form-toggle">
                <button class="toggle-btn active" data-form="login">Login</button>
                <button class="toggle-btn" data-form="register">Sign Up</button>
            </div>

            <div class="form-container active" id="login-form">
                <div class="form-header">
                    <h2>Welcome Back!</h2>
                    <p>Login to continue your study journey</p>
                </div>

                <?php 
                    // Login Error message (galing sa php/login.php)
                    if ($has_login_error) { 
                        // Ginagamit ang custom CSS class 'alert-danger'
                        echo '<div class="alert-box alert-danger">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                        
                    }
                    // Registration Success message (galing sa php/register.php)
                    if (isset($_SESSION['register_success'])) {
                        // Ginagamit ang custom CSS class 'alert-success'
                        echo '<div class="alert-box alert-success">' . htmlspecialchars($_SESSION['register_success']) . '</div>';
                        unset($_SESSION['register_success']); 
                    }
                ?>
                <form action="php/login.php" method="POST" class="auth-form">
                    
                    <div class="form-group <?php echo $has_login_error ? 'login-error' : ''; ?>">
                        <label for="login-email">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📧</span>
                            <input type="email" id="login-email" name="email" placeholder="your.email@plmun.edu.ph" required value="<?php echo htmlspecialchars($login_email); ?>">
                        </div>
                    </div>

                    <div class="form-group <?php echo $has_login_error ? 'login-error' : ''; ?>">
                        <label for="login-password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('login-password')">👁️</button>
                        </div>
                        <?php if ($has_login_error): ?>
                             <span class="error-message">Check your email or password.</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-submit">
                        <span>Login</span>
                        <span class="btn-icon">→</span>
                    </button>
                </form>
                <?php 
                    // I-unset ang login error pagkatapos ma-display lahat
                    if ($has_login_error) { 
                        unset($_SESSION['login_error']); 
                    }
                ?>
            </div>

            <div class="form-container" id="register-form">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Join thousands of successful students</p>
                </div>
                
                <?php 
                    // General Registration Error message (galing sa php/register.php - for DB failure, etc.)
                    if (isset($_SESSION['register_error'])) {
                        // Tanging general errors lang ang lalabas dito, hindi field-specific.
                        echo '<div class="alert-box alert-danger">' . htmlspecialchars($_SESSION['register_error']) . '</div>';
                        unset($_SESSION['register_error']); // Clear
                    }
                ?>
                <form action="php/register.php" method="POST" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-firstname">First Name</label>
                            <div class="input-wrapper">
                                <span class="input-icon">👤</span>
                                <input type="text" id="reg-firstname" name="firstname" placeholder="Juan" required value="<?php old_value('firstname'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reg-lastname">Last Name</label>
                            <div class="input-wrapper">
                                <span class="input-icon">👤</span>
                                <input type="text" id="reg-lastname" name="lastname" placeholder="Dela Cruz" required value="<?php old_value('lastname'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reg-email">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📧</span>
                            <input type="email" id="reg-email" name="email" placeholder="your.email@plmun.edu.ph" required value="<?php old_value('email'); ?>">
                        </div>
                        <?php echo field_error('email'); ?>
                    </div>

                    <div class="form-group">
                        <label for="reg-student-id">Student ID</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🎓</span>
                            <input type="text" id="reg-student-id" name="student_id" placeholder="2021-12345" required value="<?php old_value('student_id'); ?>">
                        </div>
                        <?php echo field_error('student_id'); ?>
                    </div>

                    <div class="form-group">
                        <label for="reg-program">Program</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📚</span>
                            <select id="reg-program" name="program" required>
                                <option value="" <?php echo is_selected('program', ''); ?>>Select Program</option>
                                <option value="BSIT" <?php echo is_selected('program', 'BSIT'); ?>>BS Information Technology</option>
                                <option value="BSCS" <?php echo is_selected('program', 'BSCS'); ?>>BS Computer Science</option>
                                <option value="BSIS" <?php echo is_selected('program', 'BSIS'); ?>>BS Information Systems</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-password">Password</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🔒</span>
                                <input type="password" id="reg-password" name="password" placeholder="Min. 8 characters" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('reg-password')">👁️</button>
                            </div>
                            <?php echo field_error('password'); ?>
                        </div>

                        <div class="form-group">
                            <label for="reg-confirm">Confirm Password</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🔒</span>
                                <input type="password" id="reg-confirm" name="confirm_password" placeholder="Re-enter password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('reg-confirm')">👁️</button>
                            </div>
                            <?php echo field_error('confirm_password'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" required>
                            <span>I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></span>
                        </label>
                    </div>

                    <button type="submit" class="btn-submit">
                        <span>Create Account</span>
                        <span class="btn-icon">→</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="js/main.js"></script>
<script src="js/auth.js"></script>
</body>
</html>