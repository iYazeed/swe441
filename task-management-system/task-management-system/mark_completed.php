<?php
/**
 * Mark Task as Completed
 * 
 * This file handles the action of marking a task as completed.
 * It updates the task status in the database and redirects back to the task view.
 * 
 * @author Task Management System Team
 * @version 1.0
 */

require_once "config/database.php";
require_once "includes/functions.php";
require_once "includes/db_functions.php"; // Include optimized database functions

// Check if user is logged in
redirect_if_not_logged_in();

// Check if task ID is provided
if (!isset($_POST["id"]) || empty($_POST["id"])) {
    header("location: read.php");
    exit();
}

$task_id = $_POST["id"];
$success = false;
$error_message = "";

// Update task status using optimized function
$success = update_task_status($conn, $task_id, 'completed', $_SESSION["id"]);

if (!$success) {
    $error_message = "Oops! Something went wrong. Please try again later.";
}

// Redirect back to task view with success or error message
if ($success) {
    $_SESSION["task_success"] = "Task marked as completed successfully.";
} else {
    $_SESSION["task_error"] = $error_message;
}

header("location: view_task.php?id=" . $task_id);
exit();
?>
