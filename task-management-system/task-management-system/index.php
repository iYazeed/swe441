<?php
/**
 * Dashboard/Home Page
 * 
 * This file displays the dashboard with task statistics and quick links.
 * It serves as the main landing page for both logged-in and guest users.
 * 
 * @author Task Management System Team
 * @version 1.0
 */

require_once "config/database.php";
require_once "includes/functions.php";
require_once "includes/db_functions.php"; // Include optimized database functions

session_start_safe();

// Get task statistics if user is logged in
if (is_logged_in()) {
    // Get task counts by status using optimized function
    $task_counts = get_task_counts_by_status($conn, $_SESSION["id"]);
    
    $pending = $task_counts['pending'];
    $in_progress = $task_counts['in_progress'];
    $completed = $task_counts['completed'];
    $total = $task_counts['total'];
}

include "includes/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 text-center">
            <h1 class="display-4 mb-4">Welcome to Task Management System</h1>
            
            <?php if (is_logged_in()): ?>
                <p class="lead mb-4">Hello, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>! Manage your tasks efficiently and stay organized.</p>
                
                <div class="row mt-5">
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h3 class="card-title">My Tasks</h3>
                                <p class="card-text">View and manage all your tasks in one place.</p>
                                <a href="read.php" class="btn btn-primary">View Tasks</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h3 class="card-title">Create Task</h3>
                                <p class="card-text">Add a new task to your list.</p>
                                <a href="create.php" class="btn btn-success">Create Task</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h3 class="card-title">Account</h3>
                                <p class="card-text">Manage your account settings.</p>
                                <a href="logout.php" class="btn btn-danger">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($total > 0): ?>
                    <div class="row mt-5">
                        <div class="col-md-8 mx-auto">
                            <div class="card">
                                <div class="card-header">
                                    <h3>Task Statistics</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="p-3 bg-warning bg-opacity-25 rounded">
                                                <h4><?php echo $pending; ?></h4>
                                                <p>Pending</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-primary bg-opacity-25 rounded">
                                                <h4><?php echo $in_progress; ?></h4>
                                                <p>In Progress</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-success bg-opacity-25 rounded">
                                                <h4><?php echo $completed; ?></h4>
                                                <p>Completed</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <p class="lead mb-4">A simple and efficient way to manage your tasks and stay organized.</p>
                <div class="row mt-5">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h3 class="card-title">Login</h3>
                                <p class="card-text">Already have an account? Login to manage your tasks.</p>
                                <a href="login.php" class="btn btn-primary">Login</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h3 class="card-title">Register</h3>
                                <p class="card-text">New user? Create an account to get started.</p>
                                <a href="register.php" class="btn btn-success">Register</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-md-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h3>Features</h3>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Create, view, update, and delete tasks</li>
                                    <li class="list-group-item">Set due dates for your tasks</li>
                                    <li class="list-group-item">Track task status (Pending, In Progress, Completed)</li>
                                    <li class="list-group-item">Secure authentication system</li>
                                    <li class="list-group-item">User-friendly interface</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
