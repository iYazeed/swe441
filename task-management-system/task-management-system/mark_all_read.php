<?php
/**
 * Mark All Notifications as Read
 * 
 * This file handles the action of marking all notifications as read.
 * It updates the notification status in the database and redirects back to the referring page.
 * 
 * @author Task Management System Team
 * @version 1.0
 */

require_once "config/database.php";
require_once "includes/functions.php";
require_once "includes/db_functions.php"; // Include optimized database functions

// Check if user is logged in
redirect_if_not_logged_in();

// Mark all notifications as read using optimized function
if (mark_all_notifications_as_read_optimized($conn, $_SESSION["id"])) {
    // Set success message in session
    $_SESSION["notification_success"] = "All notifications marked as read.";
} else {
    // Set error message in session
    $_SESSION["notification_error"] = "Failed to mark notifications as read.";
}

// Clear notification cache
clear_query_cache();

// Redirect back to the referring page or notifications page
$redirect_url = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "notifications.php";
header("Location: " . $redirect_url);
exit();
?>
