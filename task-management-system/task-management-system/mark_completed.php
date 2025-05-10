<?php
require_once "config/database.php";
require_once "includes/functions.php";

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

// Update task status to completed
$sql = "UPDATE tasks SET status = 'completed' WHERE id = ? AND user_id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $task_id, $_SESSION["id"]);
    
    if ($stmt->execute()) {
        $success = true;
    } else {
        $error_message = "Oops! Something went wrong. Please try again later.";
    }
    
    $stmt->close();
} else {
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
