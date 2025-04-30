<?php
require_once "config/database.php";
require_once "includes/functions.php";

// Start session securely
session_start_safe();

// Check if the user is already logged in
if (is_logged_in()) {
    header("location: /index.php");
    exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        // Keep using sanitize_input as defined in functions.php
        $username = sanitize_input($_POST["username"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate reCAPTCHA
    if (empty($_POST['g-recaptcha-response'])) {
        $login_err = "Please complete the reCAPTCHA verification.";
    } else {
        $captcha_token = $_POST['g-recaptcha-response'];
        if (!verify_captcha($captcha_token)) {
            $login_err = "reCAPTCHA verification failed. Please try again.";
        }
    }
    
    // Validate credentials
    if (empty($username_err) && empty($password_err) && empty($login_err)) {
        // Prepare a select statement - this is already using prepared statements correctly
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                // Check if username exists, if yes then verify password
                if ($stmt->num_rows == 1) {                    
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct
                            
                            // Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Redirect user to welcome page
                            header("location: /task-management-system");
                            exit;
                        } else {
                            // Password is not valid
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist
                    $login_err = "Invalid username or password.";
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}

include "includes/header.php";
?>

<div class="auth-container">
    <h2 class="text-center mb-4">Login</h2>
    
    <?php 
    if (!empty($login_err)) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($login_err) . '</div>';
    }        
    ?>

    <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
            <span id="username-error" class="invalid-feedback"><?php echo htmlspecialchars($username_err); ?></span>
        </div>    
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
            <span id="password-error" class="invalid-feedback"><?php echo htmlspecialchars($password_err); ?></span>
        </div>
        <div class="mb-3">
            <div class="g-recaptcha" data-sitekey="6LfTMyArAAAAAPgGaCHZRyEaYQ4muiEd-eo04Q_f"></div>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </div>
        <p class="text-center">Don't have an account? <a href="register.php">Sign up now</a>.</p>
    </form>
</div>

<?php include "includes/footer.php"; ?>