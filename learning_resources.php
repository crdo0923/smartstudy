<?php
session_start();

// Check if user is logged in. Redirect to auth.php if not.
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$firstname = htmlspecialchars($_SESSION['firstname'] ?? 'Student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Resources - SmartStudy</title>
    <link rel="stylesheet" href="css/learning_resources.css"> 
    <link rel="stylesheet" href="css/loading.css"> 
</head>
<body class="resources-body"> 
    
    <div class="page-loader">
        <div class="loader-container">
            <div class="loader-icon">🧠</div>
            <div class="loader-text">Finding Resources...</div>
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

    <div class="resources-container">
        
        <div class="resources-actions">
            <a href="dashboard.php" class="btn-back">
                ← Back to Dashboard
            </a>
            </div>

        <div class="resources-content">
            
            <div class="section-header">
                <h1>📚 Learning Resources</h1>
                <p>Curated links, tools, and guides to help you master your subjects.</p>
            </div>

            <div class="resources-grid">
                
                <div class="resource-card">
                    <span class="icon">💻</span>
                    <h3>Web Development Mastery</h3>
                    <p>Links to the best free courses and documentation for HTML, CSS, and JS.</p>
                    <a href="#" class="btn-resource-link">Go to Tutorials →</a>
                </div>

                <div class="resource-card">
                    <span class="icon">🗄️</span>
                    <h3>Database Systems Guides</h3>
                    <p>SQL cheatsheets, normalization guides, and PostgreSQL/MySQL tutorials.</p>
                    <a href="#" class="btn-resource-link">View SQL Guides →</a>
                </div>

                <div class="resource-card">
                    <span class="icon">🧩</span>
                    <h3>DSA Practice Problems</h3>
                    <p>Access competitive programming platforms and algorithm visualizers.</p>
                    <a href="#" class="btn-resource-link">Start Practice →</a>
                </div>

                <div class="resource-card">
                    <span class="icon">🛡️</span>
                    <h3>Networking & Ethical Hacking</h3>
                    <p>Videos and articles covering TCP/IP, subnetting, and basic security principles.</p>
                    <a href="#" class="btn-resource-link">Explore Security →</a>
                </div>
                
                <div class="resource-card full-width">
                    <span class="icon">🍅</span>
                    <h3>Focus Tools & Pomodoro Apps</h3>
                    <p>Find external tools to enhance your focus and time management while studying.</p>
                    <a href="#" class="btn-resource-link">Check Focus Apps →</a>
                </div>

            </div>
            </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Optional JS here for resources page
    </script>
</body>
</html>