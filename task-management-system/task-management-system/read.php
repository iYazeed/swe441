<?php
/**
 * Task List Page
 * 
 * This file displays all tasks for the logged-in user with filtering options.
 * It includes category and status filters, and displays tasks in a card layout.
 * 
 * @author Task Management System Team
 * @version 1.0
 */

require_once "config/database.php";
require_once "includes/functions.php";

// Check if user is logged in
redirect_if_not_logged_in();

// Define variables
$tasks = [];
$error_message = "";
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Get user categories
$categories = get_user_categories($conn, $_SESSION["id"]);

// Build the SQL query with filters
$sql = "SELECT t.id, t.title, t.description, t.status, t.due_date, t.created_at, t.category_id, 
               c.name as category_name, c.color as category_color 
        FROM tasks t 
        LEFT JOIN categories c ON t.category_id = c.id 
        WHERE t.user_id = ?";

$params = [$_SESSION["id"]];
$types = "i";

// Add category filter if specified
if (!empty($filter_category)) {
    $sql .= " AND t.category_id = ?";
    $params[] = $filter_category;
    $types .= "i";
}

// Add status filter if specified
if (!empty($filter_status)) {
    $sql .= " AND t.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

// Add order by clause
$sql .= " ORDER BY t.due_date ASC, t.created_at DESC";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param($types, ...$params);
    
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
        <a href="categories.php" class="btn btn-outline-secondary ms-2">Manage Categories</a>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Filters</h5>
                
                <!-- Category Filter -->
                <div class="mb-3">
                    <h6>Category:</h6>
                    <div class="category-filter">
                        <a href="?<?php echo !empty($filter_status) ? 'status=' . $filter_status : ''; ?>" 
                           class="btn <?php echo empty($filter_category) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All
                        </a>
                        <?php foreach ($categories as $category): ?>
                            <a href="?category=<?php echo $category['id']; ?><?php echo !empty($filter_status) ? '&status=' . $filter_status : ''; ?>" 
                               class="btn <?php echo $filter_category == $category['id'] ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <span class="category-color-preview" style="background-color: <?php echo $category['color']; ?>"></span>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <h6>Status:</h6>
                    <div class="category-filter">
                        <a href="?<?php echo !empty($filter_category) ? 'category=' . $filter_category : ''; ?>" 
                           class="btn <?php echo empty($filter_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All
                        </a>
                        <a href="?status=pending<?php echo !empty($filter_category) ? '&category=' . $filter_category : ''; ?>" 
                           class="btn <?php echo $filter_status == 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                            Pending
                        </a>
                        <a href="?status=in_progress<?php echo !empty($filter_category) ? '&category=' . $filter_category : ''; ?>" 
                           class="btn <?php echo $filter_status == 'in_progress' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            In Progress
                        </a>
                        <a href="?status=completed<?php echo !empty($filter_category) ? '&category=' . $filter_category : ''; ?>" 
                           class="btn <?php echo $filter_status == 'completed' ? 'btn-success' : 'btn-outline-success'; ?>">
                            Completed
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<?php if (empty($tasks)): ?>
    <div class="alert alert-info">
        <?php if (!empty($filter_category) || !empty($filter_status)): ?>
            No tasks match your filter criteria. <a href="read.php">Clear filters</a> to see all tasks.
        <?php else: ?>
            You don't have any tasks yet. <a href="create.php">Create one</a> to get started!
        <?php endif; ?>
    </div>
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
                                <li><a class="dropdown-item" href="view_task.php?id=<?php echo $task['id']; ?>">View Details</a></li>
                                <li><a class="dropdown-item" href="update.php?id=<?php echo $task['id']; ?>">Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="delete.php?id=<?php echo $task['id']; ?>" onclick="return confirm('Are you sure you want to delete this task?')">Delete</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($task['category_name'])): ?>
                            <div class="mb-2">
                                <span class="category-badge" style="background-color: <?php echo $task['category_color']; ?>">
                                    <?php echo htmlspecialchars($task['category_name']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-<?php echo ($task['status'] == 'pending') ? 'warning' : (($task['status'] == 'in_progress') ? 'primary' : 'success'); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                            </span>
                            <?php if (!empty($task['due_date'])): ?>
                                <?php 
                                    // Calculate days difference for due date styling
                                    $due_date = strtotime($task['due_date']);
                                    $today = strtotime(date('Y-m-d'));
                                    $tomorrow = strtotime('+1 day', $today);
                                    $days_diff = round(($due_date - $today) / (60 * 60 * 24));
                                    
                                    $date_class = 'text-muted';
                                    if ($days_diff < 0 && $task['status'] != 'completed') {
                                        $date_class = 'due-date-warning';
                                    } elseif ($days_diff == 0 && $task['status'] != 'completed') {
                                        $date_class = 'due-date-today';
                                    } elseif ($days_diff <= 2 && $task['status'] != 'completed') {
                                        $date_class = 'due-date-upcoming';
                                    }
                                ?>
                                <small class="<?php echo $date_class; ?>">
                                    Due: <?php echo date('M d, Y', $due_date); ?>
                                    <?php if ($days_diff < 0 && $task['status'] != 'completed'): ?>
                                        <i class="bi bi-exclamation-triangle-fill ms-1" title="Overdue"></i>
                                    <?php elseif ($days_diff == 0 && $task['status'] != 'completed'): ?>
                                        <i class="bi bi-exclamation-circle-fill ms-1" title="Due today"></i>
                                    <?php elseif ($days_diff <= 2 && $task['status'] != 'completed'): ?>
                                        <i class="bi bi-clock-fill ms-1" title="Due soon"></i>
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <div class="d-flex justify-content-between align-items-center">
                            <small>Created: <?php echo date('M d, Y', strtotime($task['created_at'])); ?></small>
                            <?php if ($task['status'] != 'completed'): ?>
                                <form action="mark_completed.php" method="post" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success">Complete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
