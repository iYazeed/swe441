<?php
require_once "config/database.php";
require_once "includes/functions.php";

// Check if user is logged in
redirect_if_not_logged_in();

// Check if notification ID is provided
if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $notification_id = $_GET["id"];
    
    // Mark notification as read
    if (mark_notification_as_read($conn, $notification_id, $_SESSION["id"])) {
        // Set success message in session
        $_SESSION["notification_success"] = "Notification marked as read.";
    } else {
        // Set error message in session
        $_SESSION["notification_error"] = "Failed to mark notification as read.";
    }
} else {
    // Set error message in session
    $_SESSION["notification_error"] = "Invalid notification ID.";
}

// Redirect back to the referring page or notifications page
$redirect_url = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "notifications.php";
header("Location: " . $redirect_url);
exit();
?>
