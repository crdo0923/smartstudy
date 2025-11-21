<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStudy - AI-Powered Study Planner</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/loading.css">
</head>
<body>

<div class="page-loader">
    <div class="loader-container">
        <div class="loader-icon">🧠</div>
        <div class="loader-text">SmartStudy</div>
        <div class="loader-spinner">
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
        </div>
        <div class="loader-progress">
            <div class="progress-bar"></div>
        </div>
        <div class="loader-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>
</div>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <span class="logo">🧠</span>
                <h2>SmartStudy</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="active">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="auth.php" class="btn-login">Login</a></li>
                <li><a href="auth.php" class="btn-register">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Master Your Study Time with <span class="gradient-text">AI Power</span></h1>
                    <p>Intelligent study planner na tutulong sayo mag-organize, mag-focus, at mag-succeed sa academics mo!</p>
                    <div class="hero-buttons">
                        <a href="auth.php" class="btn btn-primary">Get Started Free</a>
                    </div>
                    
                    </div>
                <div class="hero-image">
                    <div class="floating-card card-1">
                        <span class="icon">📚</span>
                        <p>Smart Scheduling</p>
                    </div>
                    <div class="floating-card card-2">
                        <span class="icon">🎯</span>
                        <p>Focus Mode</p>
                    </div>
                    <div class="floating-card card-3">
                        <span class="icon">📊</span>
                    <p>Progress Tracking</p>
                    </div>
                    <div class="hero-illustration">
                        <div class="study-circle"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Powerful Features Para Sa'yo</h2>
                <p>Everything you need para maging productive at successful student</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🤖</div>
                    <h3>AI-Powered Scheduler</h3>
                    <p>Automatic na mag-create ng optimal study schedule based sa deadlines at preferences mo</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Distraction Blocker</h3>
                    <p>Block social media at distracting websites habang nag-aaral ka</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⏱️</div>
                    <h3>Pomodoro Timer</h3>
                    <p>Built-in focus timer with break reminders para sa maximum productivity</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Progress Analytics</h3>
                    <p>Track your study patterns, productivity, at improvement over time</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Smart Prioritization</h3>
                    <p>AI na mag-prioritize ng tasks based sa urgency at importance</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏆</div>
                    <h3>Rewards System</h3>
                    <p>Earn points, badges, at achievements habang nag-study ka</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💡</div>
                    <h3>Study Insights</h3>
                    <p>AI-generated recommendations para sa better study habits</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌙</div>
                    <h3>Wellness Breaks</h3>
                    <p>Smart break suggestions para hindi ka ma-burnout</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Cross-Device Sync</h3>
                    <p>Access your study plans anywhere, anytime</p>
                </div>
            </div>
        </div>
    </section>

    <section class="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>How it works?</h2>
                <p>it's simple! 3 steps to better studying</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Create Account</h3>
                    <p>Sign up in less than a minute</p>
                </div>
                <div class="step-arrow">→</div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Add Your Tasks</h3>
                    <p>Input your subjects at deadlines</p>
                </div>
                <div class="step-arrow">→</div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Let AI Help</h3>
                    <p>Get personalized study plans!</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Transform Your Study Habits?</h2>
                <p>Join hundreds of PLMun students achieving their academic goals</p>
                <a href="auth.php" class="btn btn-large">Start Free Today</a>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>🧠 SmartStudy</h3>
                    <p>AI-Powered Study Planner for PLMun Students</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="auth.php">Login</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Developers</h4>
                    <p>FLANDEZ, RICARDO Jr. G.</p>
                    <p>COLLADO, PATRICK S.</p>
                    <p>PISTON, CHRIS ZAN D.</p>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>PLMun CITCS</p>
                    <p>Muntinlupa City</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 SmartStudy. Capstone Project - PLMun CITCS</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>