<?php
// config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'smart_study'; 

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 * BINALOT SA 'if (!function_exists())' PARA HINDI NA MAG-ERROR
 * KAHIT MATAWAG PA SIYA NG MARAMING BESES.
 */
if (!function_exists('getUsername')) {
    function getUsername($conn, $user_id) {
        $sql = "SELECT firstname, lastname FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['firstname'] . ' ' . $row['lastname'];
        }
        return "Unknown User";
    }
}
?>