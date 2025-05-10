<?php
require_once "config/database.php";
require_once "includes/functions.php";

// Check if user is logged in
redirect_if_not_logged_in();

// Mark all notifications as read
if (mark_all_notifications_as_read($conn, $_SESSION["id"])) {
    // Set success message in session
    $_SESSION["notification_success"] = "All notifications marked as read.";
} else {
    // Set error message in session
    $_SESSION["notification_error"] = "Failed to mark notifications as read.";
}

// Redirect back to the referring page or notifications page
$redirect_url = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "notifications.php";
header("Location: " . $redirect_url);
exit();
?>
