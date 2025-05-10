<?php
/**
 * Task Detail View Page
 * 
 * This file displays detailed information about a specific task.
 * It includes task properties, status indicators, and action buttons.
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
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: read.php");
    exit();
}

$task_id = $_GET["id"];
$task = null;
$error_message = "";

// If notification ID is provided, mark it as read
if (isset($_GET["notification"]) && !empty($_GET["notification"])) {
    $notification_id = $_GET["notification"];
    mark_notification_as_read($conn, $notification_id, $_SESSION["id"]);
    
    // Clear notification cache after marking as read
    clear_query_cache();
}

// Get task details using optimized function
$task = get_task_by_id($conn, $task_id, $_SESSION["id"]);

// Handle database errors or task not found
if ($task === null) {
    $error_message = "Task not found or an error occurred.";
}

include "includes/header.php";
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Task Details</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="read.php" class="btn btn-secondary">Back to Tasks</a>
    </div>
</div>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php elseif ($task): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><?php echo htmlspecialchars($task['title']); ?></h3>
            <div>
                <a href="update.php?id=<?php echo $task['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                <a href="delete.php?id=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm ms-2" onclick="return confirm('Are you sure you want to delete this task?')">Delete</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($task['category_name'])): ?>
                <div class="mb-3">
                    <strong>Category:</strong>
                    <span class="category-badge" style="background-color: <?php echo $task['category_color']; ?>">
                        <?php echo htmlspecialchars($task['category_name']); ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <strong>Status:</strong>
                <span class="badge bg-<?php echo ($task['status'] == 'pending') ? 'warning' : (($task['status'] == 'in_progress') ? 'primary' : 'success'); ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                </span>
            </div>
            
            <?php if (!empty($task['due_date'])): ?>
                <div class="mb-3">
                    <strong>Due Date:</strong>
                    <?php 
                        // Calculate days difference for due date styling and messaging
                        $due_date = strtotime($task['due_date']);
                        $today = strtotime(date('Y-m-d'));
                        $days_diff = round(($due_date - $today) / (60 * 60 * 24));
                        
                        $date_class = '';
                        if ($days_diff < 0 && $task['status'] != 'completed') {
                            $date_class = 'due-date-warning';
                        } elseif ($days_diff == 0 && $task['status'] != 'completed') {
                            $date_class = 'due-date-today';
                        } elseif ($days_diff <= 2 && $task['status'] != 'completed') {
                            $date_class = 'due-date-upcoming';
                        }
                    ?>
                    <span class="<?php echo $date_class; ?>">
                        <?php echo date('F d, Y', $due_date); ?>
                        <?php if ($days_diff < 0 && $task['status'] != 'completed'): ?>
                            (Overdue by <?php echo abs($days_diff); ?> day<?php echo abs($days_diff) > 1 ? 's' : ''; ?>)
                        <?php elseif ($days_diff == 0 && $task['status'] != 'completed'): ?>
                            (Due today!)
                        <?php elseif ($days_diff == 1 && $task['status'] != 'completed'): ?>
                            (Due tomorrow)
                        <?php elseif ($days_diff > 0 && $days_diff <= 2 && $task['status'] != 'completed'): ?>
                            (Due in <?php echo $days_diff; ?> days)
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <strong>Created:</strong>
                <?php echo date('F d, Y g:i A', strtotime($task['created_at'])); ?>
            </div>
            
            <div class="mb-3">
                <strong>Description:</strong>
                <div class="mt-2 p-3 bg-light rounded">
                    <?php echo empty($task['description']) ? '<em>No description provided</em>' : nl2br(htmlspecialchars($task['description'])); ?>
                </div>
            </div>
            
            <?php if ($task['status'] != 'completed' && !empty($task['due_date']) && strtotime($task['due_date']) < strtotime(date('Y-m-d'))): ?>
                <div class="alert alert-danger mt-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    This task is overdue! Consider updating its status or due date.
                </div>
            <?php elseif ($task['status'] != 'completed' && !empty($task['due_date']) && strtotime($task['due_date']) == strtotime(date('Y-m-d'))): ?>
                <div class="alert alert-warning mt-4">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    This task is due today!
                </div>
            <?php endif; ?>
            
            <?php if ($task['status'] == 'completed'): ?>
                <div class="alert alert-success mt-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    This task has been completed!
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-muted">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="update.php?id=<?php echo $task['id']; ?>" class="btn btn-primary">Edit Task</a>
                    <a href="read.php" class="btn btn-secondary ms-2">Back to Tasks</a>
                </div>
                <?php if ($task['status'] != 'completed'): ?>
                    <form action="mark_completed.php" method="post" class="d-inline">
                        <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                        <button type="submit" class="btn btn-success">Mark as Completed</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
