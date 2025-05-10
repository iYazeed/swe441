<?php
/**
 * Notifications Page
 * 
 * This file displays all notifications for the logged-in user.
 * It includes options to view task details and mark notifications as read.
 * 
 * @author Task Management System Teamm
 * @version 1.0
 */

require_once "config/database.php";
require_once "includes/functions.php";

// Check if user is logged in
redirect_if_not_logged_in();

// Get all notifications for the user
$notifications = get_user_notifications($conn, $_SESSION["id"], 50);

include "includes/header.php";
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Notifications</h2>
    </div>
    <div class="col-md-4 text-end">
        <?php if (!empty($notifications)): ?>
            <a href="mark_all_read.php" class="btn btn-outline-primary">Mark All as Read</a>
        <?php endif; ?>
        <a href="read.php" class="btn btn-secondary ms-2">Back to Tasks</a>
    </div>
</div>

<?php if (isset($_SESSION["notification_success"])): ?>
    <div class="alert alert-success"><?php echo $_SESSION["notification_success"]; ?></div>
    <?php unset($_SESSION["notification_success"]); ?>
<?php endif; ?>

<?php if (isset($_SESSION["notification_error"])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION["notification_error"]; ?></div>
    <?php unset($_SESSION["notification_error"]); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($notifications)): ?>
            <div class="text-center p-4">
                <i class="bi bi-bell-slash" style="font-size: 3rem; color: #6c757d;"></i>
                <p class="mt-3">You don't have any notifications.</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?> notification-type-<?php echo $notification['type']; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="notification-content">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </div>
                                <div class="notification-time">
                                    <?php echo date('M d, Y g:i A', strtotime($notification['created_at'])); ?>
                                </div>
                            </div>
                            <div class="notification-actions">
                                <a href="view_task.php?id=<?php echo $notification['task_id']; ?>&notification=<?php echo $notification['id']; ?>" class="btn btn-sm btn-outline-primary">View Task</a>
                                <?php if (!$notification['is_read']): ?>
                                    <a href="mark_notification_read.php?id=<?php echo $notification['id']; ?>" class="btn btn-sm btn-outline-secondary ms-2">Mark Read</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
