<?php
/**
 * Header Template
 * 
 * This file contains the header section that appears on all pages.
 * It includes the HTML head, navigation bar, and notification system.
 * 
 * @author Task Management System Team
 * @version 1.0
 */

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/../includes/db_functions.php"; // Include optimized database functions
session_start_safe();

// Check for due date notifications if user is logged in
if (is_logged_in()) {
    // Get tasks requiring notifications
    $tasks_requiring_notifications = check_tasks_requiring_notifications($conn, $_SESSION["id"]);
    
    // Create notifications in bulk if needed
    if (!empty($tasks_requiring_notifications)) {
        $notifications_to_create = [];
        $today = date('Y-m-d');
        
        foreach ($tasks_requiring_notifications as $task) {
            if ($task['notification_type'] == 'upcoming') {
                $days_until = (strtotime($task['due_date']) - strtotime($today)) / (60 * 60 * 24);
                $days_text = $days_until <= 1 ? "tomorrow" : "in 2 days";
                
                $message = "Task \"" . $task['title'] . "\" is due " . $days_text . " (" . date('M d, Y', strtotime($task['due_date'])) . ").";
            } else { // overdue
                $days_overdue = (strtotime($today) - strtotime($task['due_date'])) / (60 * 60 * 24);
                $days_text = $days_overdue <= 1 ? "today" : $days_overdue . " days ago";
                
                $message = "Task \"" . $task['title'] . "\" was due " . $days_text . " (" . date('M d, Y', strtotime($task['due_date'])) . ").";
            }
            
            $notifications_to_create[] = [
                'user_id' => $_SESSION["id"],
                'task_id' => $task['id'],
                'message' => $message,
                'type' => $task['notification_type']
            ];
        }
        
        if (!empty($notifications_to_create)) {
            create_notifications_bulk($conn, $notifications_to_create);
        }
    }
    
    // Get unread notifications count using optimized function
    $unread_notifications_count = get_unread_notifications_count_optimized($conn, $_SESSION["id"]);
    
    // Get recent notifications using optimized function
    $notifications = get_user_notifications_optimized($conn, $_SESSION["id"], 5);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Task Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="read.php">My Tasks</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create.php">Create Task</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">Categories</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                <?php if (isset($unread_notifications_count) && $unread_notifications_count > 0): ?>
                                    <span class="notification-badge"><?php echo $unread_notifications_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
                                <div class="notification-header">
                                    <h6 class="mb-0">Notifications</h6>
                                    <?php if (isset($unread_notifications_count) && $unread_notifications_count > 0): ?>
                                        <a href="mark_all_read.php" class="text-decoration-none">Mark all read</a>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($notifications) && !empty($notifications)): ?>
                                    <?php foreach ($notifications as $notification): ?>
                                        <a href="view_task.php?id=<?php echo $notification['task_id']; ?>&notification=<?php echo $notification['id']; ?>" class="notification-link">
                                            <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?> notification-type-<?php echo $notification['type']; ?>">
                                                <div class="notification-content">
                                                    <?php echo htmlspecialchars($notification['message']); ?>
                                                </div>
                                                <div class="notification-time">
                                                    <?php echo date('M d, Y g:i A', strtotime($notification['created_at'])); ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                    
                                    <div class="notification-footer">
                                        <a href="notifications.php" class="text-decoration-none">View all notifications</a>
                                    </div>
                                <?php else: ?>
                                    <div class="notification-empty">
                                        No notifications
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
