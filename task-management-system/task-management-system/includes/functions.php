<?php
// Start session if not already started
function session_start_safe() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function is_logged_in() {
    session_start_safe();
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// Redirect to login page if not logged in
function redirect_if_not_logged_in() {
    if (!is_logged_in()) {
        header("location: /auth/login.php");
        exit;
    }
}

// Sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Display error message
function display_error($message) {
    echo '<div class="alert alert-danger">' . $message . '</div>';
}

// Display success message
function display_success($message) {
    echo '<div class="alert alert-success">' . $message . '</div>';
}

// Verify Google reCAPTCHA token
function verify_captcha($token) {
    $secret = "6LfTMyArAAAAADCE0uDPm3kDcze8jI4adO8Tec9O"; 
    
    $data = array(
        'secret' => $secret,
        'response' => $token
    );
    
    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($verify);
    
    if ($response === false) {
        return false;
    }
    
    $responseData = json_decode($response);
    return $responseData->success;
}

// Add a new function for logging errors
/**
 * Log error to file
 * @param string $message Error message
 * @param string $file File where error occurred
 * @param int $line Line number where error occurred
 * @return void
 */
function log_error($message, $file = '', $line = 0) {
    $log_file = __DIR__ . '/../logs/error.log';
    $log_dir = dirname($log_file);
    
    // Create logs directory if it doesn't exist
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Format the error message
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message";
    
    if (!empty($file)) {
        $log_message .= " in $file";
        
        if ($line > 0) {
            $log_message .= " on line $line";
        }
    }
    
    $log_message .= PHP_EOL;
    
    // Write to log file
    error_log($log_message, 3, $log_file);
}

// Add a function to handle database errors
/**
 * Handle database error
 * @param mysqli $conn Database connection
 * @param string $query SQL query that failed (optional)
 * @return string Error message
 */
function handle_db_error($conn, $query = '') {
    $error = $conn->error;
    $errno = $conn->errno;
    
    // Log the error
    $message = "Database error #$errno: $error";
    if (!empty($query)) {
        $message .= " | Query: $query";
    }
    
    log_error($message, debug_backtrace()[0]['file'], debug_backtrace()[0]['line']);
    
    // Return user-friendly message
    return "A database error occurred. Please try again later.";
}
?>
