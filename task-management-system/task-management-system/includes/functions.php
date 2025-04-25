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
?>
