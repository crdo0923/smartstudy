<?php
session_start();

$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'smart_study'; 

// Define Redirect Path (Assuming login.php is in /php/)
$auth_redirect = '../auth.php'; 
$dashboard_redirect = '../dashboard.php'; 

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    // Kung mag-fail ang connection, mag-log at mag-redirect pabalik
    error_log("Database connection failed in login.php: " . $conn->connect_error);
    // Ginamit ko ang 'login_error' para tugma sa hinahanap ng auth.php
    $_SESSION['login_error'] = "System Error: Server maintenance mode. Please try again later."; 
    header("Location: {$auth_redirect}");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $input_password = $_POST['password']; // Huwag i-trim ang password, handle na ng password_verify()
    
    // I-store ang email sa session para maibalik sa form
    $_SESSION['login_email'] = $email;

    // Gumamit ng Prepared Statements (Best Practice!)
    $stmt = $conn->prepare("SELECT id, firstname, program, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // User found
        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];

        if (password_verify($input_password, $hashed_password)) {
            // ✅ Login Successful
            
            // I-set ang Session Variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['program'] = $user['program'];

            // ✨ BAGONG LINYA PARA SA WELCOME MESSAGE FLASH:
            $_SESSION['just_logged_in'] = true; 
            
            // Clear old login data at errors
            unset($_SESSION['login_error']);
            unset($_SESSION['login_email']); 
            
            $stmt->close();
            $conn->close();
            
            header("Location: {$dashboard_redirect}");
            exit();
            
        } else {
            // ❌ Password incorrect
            // Ginamit ko ang 'login_error' para tugma sa hinahanap ng auth.php
            $_SESSION['login_error'] = "Invalid email or password."; 
            $stmt->close();
            $conn->close();
            header("Location: {$auth_redirect}"); // Balik sa auth.php, Login tab (default)
            exit();
        }
    } else {
        // ❌ Email not found
        // Ginamit ko ang 'login_error' para tugma sa hinahanap ng auth.php
        $_SESSION['login_error'] = "Invalid email or password.";
        $stmt->close();
        $conn->close();
        header("Location: {$auth_redirect}"); // Balik sa auth.php, Login tab (default)
        exit();
    }
}

$conn->close();

// Kung may nag-access ng file na walang POST request
header("Location: {$auth_redirect}");
exit();
?>