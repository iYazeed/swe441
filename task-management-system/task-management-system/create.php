<?php
/**
 * Task Creation Page
 * 
 * This file handles the creation of new tasks. It includes form validation,
 * database operations, and error handling for the task creation process.
 * 
 * @author Task Management System Team
 * @version 1.0
 */

require_once "config/database.php";
require_once "includes/functions.php";

// Check if user is logged in
redirect_if_not_logged_in();

// Check database connection
if ($conn->connect_error) {
    log_error("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Define variables and initialize with empty values
$title = $description = $status = $due_date = $category_id = "";
$title_err = $description_err = $status_err = $due_date_err = $category_err = $general_err = "";

// Get user categories
$categories = get_user_categories($conn, $_SESSION["id"]);

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate title
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = sanitize_input($_POST["title"]);
    }
    
    // Validate description (optional)
    $description = sanitize_input($_POST["description"]);
    
    // Validate status
    if (empty($_POST["status"])) {
        $status_err = "Please select a status.";
    } else {
        $status = sanitize_input($_POST["status"]);
    }
    
    // Validate due date (optional)
    if (!empty($_POST["due_date"])) {
        $due_date = sanitize_input($_POST["due_date"]);
        
        // Check if due date is valid
        $today = date("Y-m-d");
        if ($due_date < $today) {
            $due_date_err = "Due date cannot be in the past.";
        }
    }
    
    // Validate category (optional)
    if (!empty($_POST["category_id"])) {
        $category_id = sanitize_input($_POST["category_id"]);
        
        // Check if category exists and belongs to user
        $category_exists = false;
        foreach ($categories as $category) {
            if ($category["id"] == $category_id) {
                $category_exists = true;
                break;
            }
        }
        
        if (!$category_exists) {
            $category_err = "Invalid category selected.";
        }
    }
    
    // Check input errors before inserting in database
    if (empty($title_err) && empty($status_err) && empty($due_date_err) && empty($category_err)) {
        try {
            // Prepare an insert statement
            $sql = "INSERT INTO tasks (title, description, status, due_date, category_id, user_id) VALUES (?, ?, ?, ?, ?, ?)";
             
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("ssssii", $param_title, $param_description, $param_status, $param_due_date, $param_category_id, $param_user_id);
                
                // Set parameters
                $param_title = $title;
                $param_description = $description;
                $param_status = $status;
                $param_due_date = $due_date;
                $param_category_id = !empty($category_id) ? $category_id : NULL;
                $param_user_id = $_SESSION["id"];
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Redirect to tasks page
                    header("location: read.php");
                    exit();
                } else {
                    $general_err = "Database error: " . $conn->error;
                }

                // Close statement
                $stmt->close();
            } else {
                $general_err = "Database preparation error: " . $conn->error;
            }
        } catch (Exception $e) {
            $general_err = "An unexpected error occurred: " . $e->getMessage();
        }
    }
    
    // Close connection
    $conn->close();
}

include "includes/header.php";
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Create New Task</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($general_err)): ?>
                    <div class="alert alert-danger mb-3"><?php echo $general_err; ?></div>
                <?php endif; ?>
                <form id="task-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                        <span id="title-error" class="invalid-feedback"><?php echo $title_err; ?></span>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4"><?php echo $description; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select <?php echo (!empty($category_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">-- Select Category (Optional) --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="invalid-feedback"><?php echo $category_err; ?></span>
                        <div class="mt-1">
                            <a href="categories.php" class="text-decoration-none">
                                <small>Manage Categories</small>
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select <?php echo (!empty($status_err)) ? 'is-invalid' : ''; ?>">
                            <option value="pending" <?php echo ($status == "pending") ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo ($status == "in_progress") ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo ($status == "completed") ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <span class="invalid-feedback"><?php echo $status_err; ?></span>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control <?php echo (!empty($due_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $due_date; ?>">
                        <span id="due-date-error" class="invalid-feedback"><?php echo $due_date_err; ?></span>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Create Task</button>
                        <a href="read.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
