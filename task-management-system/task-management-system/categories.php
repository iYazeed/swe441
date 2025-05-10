<?php
require_once "config/database.php";
require_once "includes/functions.php";

// Check if user is logged in
redirect_if_not_logged_in();

// Define variables and initialize with empty values
$name = $color = "";
$name_err = $color_err = $general_err = "";
$success_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if we're adding, editing, or deleting a category
    if (isset($_POST["action"])) {
        
        // Add new category
        if ($_POST["action"] == "add") {
            // Validate name
            if (empty(trim($_POST["name"]))) {
                $name_err = "Please enter a category name.";
            } elseif (strlen(trim($_POST["name"])) > 50) {
                $name_err = "Category name must be less than 50 characters.";
            } else {
                $name = sanitize_input($_POST["name"]);
                
                // Check if category name already exists for this user
                $sql = "SELECT id FROM categories WHERE name = ? AND user_id = ?";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("si", $param_name, $param_user_id);
                    
                    $param_name = $name;
                    $param_user_id = $_SESSION["id"];
                    
                    if ($stmt->execute()) {
                        $stmt->store_result();
                        
                        if ($stmt->num_rows > 0) {
                            $name_err = "This category name already exists.";
                        }
                    } else {
                        $general_err = "Oops! Something went wrong. Please try again later.";
                    }
                    
                    $stmt->close();
                }
            }
            
            // Validate color
            if (empty(trim($_POST["color"]))) {
                $color = "#6c757d"; // Default color
            } else {
                $color = sanitize_input($_POST["color"]);
                
                // Check if color is a valid hex color
                if (!preg_match('/^#[a-f0-9]{6}$/i', $color)) {
                    $color_err = "Please enter a valid hex color code.";
                }
            }
            
            // Check input errors before inserting in database
            if (empty($name_err) && empty($color_err) && empty($general_err)) {
                // Prepare an insert statement
                $sql = "INSERT INTO categories (name, color, user_id) VALUES (?, ?, ?)";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssi", $param_name, $param_color, $param_user_id);
                    
                    $param_name = $name;
                    $param_color = $color;
                    $param_user_id = $_SESSION["id"];
                    
                    if ($stmt->execute()) {
                        $success_message = "Category added successfully.";
                        $name = $color = ""; // Clear form fields
                    } else {
                        $general_err = "Oops! Something went wrong. Please try again later.";
                    }
                    
                    $stmt->close();
                }
            }
        }
        
        // Edit existing category
        elseif ($_POST["action"] == "edit" && isset($_POST["id"])) {
            $id = $_POST["id"];
            
            // Validate name
            if (empty(trim($_POST["edit_name"]))) {
                $general_err = "Category name cannot be empty.";
            } elseif (strlen(trim($_POST["edit_name"])) > 50) {
                $general_err = "Category name must be less than 50 characters.";
            } else {
                $name = sanitize_input($_POST["edit_name"]);
                
                // Check if category name already exists for this user (excluding current category)
                $sql = "SELECT id FROM categories WHERE name = ? AND user_id = ? AND id != ?";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("sii", $param_name, $param_user_id, $param_id);
                    
                    $param_name = $name;
                    $param_user_id = $_SESSION["id"];
                    $param_id = $id;
                    
                    if ($stmt->execute()) {
                        $stmt->store_result();
                        
                        if ($stmt->num_rows > 0) {
                            $general_err = "This category name already exists.";
                        }
                    } else {
                        $general_err = "Oops! Something went wrong. Please try again later.";
                    }
                    
                    $stmt->close();
                }
            }
            
            // Validate color
            if (empty(trim($_POST["edit_color"]))) {
                $color = "#6c757d"; // Default color
            } else {
                $color = sanitize_input($_POST["edit_color"]);
                
                // Check if color is a valid hex color
                if (!preg_match('/^#[a-f0-9]{6}$/i', $color)) {
                    $general_err = "Please enter a valid hex color code.";
                }
            }
            
            // Check input errors before updating in database
            if (empty($general_err)) {
                // Prepare an update statement
                $sql = "UPDATE categories SET name = ?, color = ? WHERE id = ? AND user_id = ?";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssii", $param_name, $param_color, $param_id, $param_user_id);
                    
                    $param_name = $name;
                    $param_color = $color;
                    $param_id = $id;
                    $param_user_id = $_SESSION["id"];
                    
                    if ($stmt->execute()) {
                        $success_message = "Category updated successfully.";
                    } else {
                        $general_err = "Oops! Something went wrong. Please try again later.";
                    }
                    
                    $stmt->close();
                }
            }
        }
        
        // Delete category
        elseif ($_POST["action"] == "delete" && isset($_POST["id"])) {
            $id = $_POST["id"];
            
            // Prepare a delete statement
            $sql = "DELETE FROM categories WHERE id = ? AND user_id = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ii", $param_id, $param_user_id);
                
                $param_id = $id;
                $param_user_id = $_SESSION["id"];
                
                if ($stmt->execute()) {
                    $success_message = "Category deleted successfully.";
                } else {
                    $general_err = "Oops! Something went wrong. Please try again later.";
                }
                
                $stmt->close();
            }
        }
    }
}

// Get all categories for the current user
$categories = get_user_categories($conn, $_SESSION["id"]);

include "includes/header.php";
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Manage Categories</h2>
        <p>Create and manage categories to organize your tasks.</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="read.php" class="btn btn-secondary">Back to Tasks</a>
    </div>
</div>

<?php if (!empty($general_err)): ?>
    <div class="alert alert-danger"><?php echo $general_err; ?></div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add New Category</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <div class="invalid-feedback"><?php echo $name_err; ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <div id="color-preview" class="category-color-preview" style="background-color: <?php echo !empty($color) ? $color : '#6c757d'; ?>"></div>
                            </span>
                            <input type="text" name="color" id="color" class="form-control <?php echo (!empty($color_err)) ? 'is-invalid' : ''; ?>" value="<?php echo !empty($color) ? $color : '#6c757d'; ?>" placeholder="#HEX">
                            <div class="invalid-feedback"><?php echo $color_err; ?></div>
                        </div>
                        <small class="text-muted">Enter a hex color code (e.g., #FF5733)</small>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Your Categories</h3>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <div class="alert alert-info">You don't have any categories yet.</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-item">
                                <div>
                                    <span class="category-color-preview" style="background-color: <?php echo $category['color']; ?>"></span>
                                    <span><?php echo htmlspecialchars($category['name']); ?></span>
                                </div>
                                <div class="category-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-category" 
                                            data-id="<?php echo $category['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($category['name']); ?>" 
                                            data-color="<?php echo $category['color']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-category" 
                                            data-id="<?php echo $category['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                            data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Category Name</label>
                        <input type="text" name="edit_name" id="edit_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_color" class="form-label">Color</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <div id="edit-color-preview" class="category-color-preview"></div>
                            </span>
                            <input type="text" name="edit_color" id="edit_color" class="form-control" placeholder="#HEX">
                        </div>
                        <small class="text-muted">Enter a hex color code (e.g., #FF5733)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCategoryModalLabel">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the category "<span id="delete_category_name"></span>"?</p>
                <p class="text-danger">This will remove the category from all associated tasks.</p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Color preview for add form
    const colorInput = document.getElementById('color');
    const colorPreview = document.getElementById('color-preview');
    
    if (colorInput && colorPreview) {
        colorInput.addEventListener('input', function() {
            colorPreview.style.backgroundColor = this.value;
        });
    }
    
    // Edit category modal
    const editButtons = document.querySelectorAll('.edit-category');
    const editIdInput = document.getElementById('edit_id');
    const editNameInput = document.getElementById('edit_name');
    const editColorInput = document.getElementById('edit_color');
    const editColorPreview = document.getElementById('edit-color-preview');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const color = this.getAttribute('data-color');
            
            editIdInput.value = id;
            editNameInput.value = name;
            editColorInput.value = color;
            editColorPreview.style.backgroundColor = color;
        });
    });
    
    // Update color preview in edit modal
    if (editColorInput && editColorPreview) {
        editColorInput.addEventListener('input', function() {
            editColorPreview.style.backgroundColor = this.value;
        });
    }
    
    // Delete category modal
    const deleteButtons = document.querySelectorAll('.delete-category');
    const deleteIdInput = document.getElementById('delete_id');
    const deleteCategoryName = document.getElementById('delete_category_name');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            deleteIdInput.value = id;
            deleteCategoryName.textContent = name;
        });
    });
});
</script>

<?php include "includes/footer.php"; ?>
