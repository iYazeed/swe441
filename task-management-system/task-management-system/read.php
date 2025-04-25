<?php
require_once "config/database.php";
require_once "includes/functions.php";

// Check if user is logged in
redirect_if_not_logged_in();

// Define variables
$tasks = [];
$error_message = "";

// Get tasks for the current user
$sql = "SELECT id, title, description, status, due_date, created_at FROM tasks WHERE user_id = ? ORDER BY due_date ASC, created_at DESC";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $param_user_id);
    
    // Set parameters
    $param_user_id = $_SESSION["id"];
    
    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        // Check if any tasks exist
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }
        }
    } else {
        $error_message = "Oops! Something went wrong. Please try again later.";
    }
    
    // Close statement
    $stmt->close();
} else {
    $error_message = "Oops! Something went wrong. Please try again later.";
}

include "includes/header.php";
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>My Tasks</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="create.php" class="btn btn-primary">Create New Task</a>
    </div>
</div>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<?php if (empty($tasks)): ?>
    <div class="alert alert-info">You don't have any tasks yet. Create one to get started!</div>
<?php else: ?>
    <div class="row">
        <?php foreach ($tasks as $task): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card task-card status-<?php echo $task['status']; ?>">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($task['title']); ?></h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $task['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $task['id']; ?>">
                                <li><a class="dropdown-item" href="update.php?id=<?php echo $task['id']; ?>">Edit</a></li>
                                <li><a class="dropdown-item" href="delete.php?id=<?php echo $task['id']; ?>" onclick="return confirm('Are you sure you want to delete this task?')">Delete</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-<?php echo ($task['status'] == 'pending') ? 'warning' : (($task['status'] == 'in_progress') ? 'primary' : 'success'); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                            </span>
                            <?php if (!empty($task['due_date'])): ?>
                                <small class="text-muted">Due: <?php echo date('M d, Y', strtotime($task['due_date'])); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Created: <?php echo date('M d, Y', strtotime($task['created_at'])); ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include "includes/footer.php"; ?>