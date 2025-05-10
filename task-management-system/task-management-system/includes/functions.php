<?php
// Start session if not already started
function session_start_safe() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function is_logged_in() {
    session_start_safe();
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// Redirect to login page if not logged in
function redirect_if_not_logged_in() {
    if (!is_logged_in()) {
        header("location: /auth/login.php");
        exit;
    }
}

// Sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Display error message
function display_error($message) {
    echo '<div class="alert alert-danger">' . $message . '</div>';
}

// Display success message
function display_success($message) {
    echo '<div class="alert alert-success">' . $message . '</div>';
}

// Verify Google reCAPTCHA token
function verify_captcha($token) {
    $secret = "6LfTMyArAAAAADCE0uDPm3kDcze8jI4adO8Tec9O"; 
    
    $data = array(
        'secret' => $secret,
        'response' => $token
    );
    
    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($verify);
    
    if ($response === false) {
        return false;
    }
    
    $responseData = json_decode($response);
    return $responseData->success;
}

// Add a new function for logging errors
/**
 * Log error to file
 * @param string $message Error message
 * @param string $file File where error occurred
 * @param int $line Line number where error occurred
 * @return void
 */
function log_error($message, $file = '', $line = 0) {
    $log_file = __DIR__ . '/../logs/error.log';
    $log_dir = dirname($log_file);
    
    // Create logs directory if it doesn't exist
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Format the error message
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message";
    
    if (!empty($file)) {
        $log_message .= " in $file";
        
        if ($line > 0) {
            $log_message .= " on line $line";
        }
    }
    
    $log_message .= PHP_EOL;
    
    // Write to log file
    error_log($log_message, 3, $log_file);
}

// Add a function to handle database errors
/**
 * Handle database error
 * @param mysqli $conn Database connection
 * @param string $query SQL query that failed (optional)
 * @return string Error message
 */
function handle_db_error($conn, $query = '') {
    $error = $conn->error;
    $errno = $conn->errno;
    
    // Log the error
    $message = "Database error #$errno: $error";
    if (!empty($query)) {
        $message .= " | Query: $query";
    }
    
    log_error($message, debug_backtrace()[0]['file'], debug_backtrace()[0]['line']);
    
    // Return user-friendly message
    return "A database error occurred. Please try again later.";
}

/**
 * Get user categories
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array Categories array or empty array on error
 */
function get_user_categories($conn, $user_id) {
    $categories = [];
    
    $sql = "SELECT id, name, color FROM categories WHERE user_id = ? ORDER BY name ASC";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        } else {
            log_error("Failed to get categories: " . $conn->error);
        }
        
        $stmt->close();
    } else {
        log_error("Failed to prepare category query: " . $conn->error);
    }
    
    return $categories;
}

/**
 * Get category name by ID
 * @param mysqli $conn Database connection
 * @param int $category_id Category ID
 * @return array|null Category data or null if not found
 */
function get_category_by_id($conn, $category_id) {
    if (empty($category_id)) {
        return null;
    }
    
    $sql = "SELECT id, name, color FROM categories WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $category_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $category = $result->fetch_assoc();
                $stmt->close();
                return $category;
            }
        }
        
        $stmt->close();
    }
    
    return null;
}

/**
 * Check for tasks with upcoming due dates and create notifications
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return void
 */
function check_due_date_notifications($conn, $user_id) {
    // Check for tasks due in the next 2 days that don't already have notifications
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $day_after = date('Y-m-d', strtotime('+2 days'));
    
    // First, check for upcoming tasks (due in 1-2 days)
    $sql = "SELECT t.id, t.title, t.due_date 
            FROM tasks t 
            LEFT JOIN notifications n ON t.id = n.task_id AND n.type = 'upcoming'
            WHERE t.user_id = ? 
            AND t.due_date BETWEEN ? AND ? 
            AND t.status != 'completed'
            AND n.id IS NULL";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iss", $user_id, $tomorrow, $day_after);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                // Create notification for upcoming task
                $days_until = (strtotime($row['due_date']) - strtotime($today)) / (60 * 60 * 24);
                $days_text = $days_until <= 1 ? "tomorrow" : "in 2 days";
                
                $message = "Task \"" . $row['title'] . "\" is due " . $days_text . " (" . date('M d, Y', strtotime($row['due_date'])) . ").";
                create_notification($conn, $user_id, $row['id'], $message, 'upcoming');
            }
        }
        
        $stmt->close();
    }
    
    // Next, check for overdue tasks
    $sql = "SELECT t.id, t.title, t.due_date 
            FROM tasks t 
            LEFT JOIN notifications n ON t.id = n.task_id AND n.type = 'overdue'
            WHERE t.user_id = ? 
            AND t.due_date < ? 
            AND t.status != 'completed'
            AND n.id IS NULL";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("is", $user_id, $today);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                // Create notification for overdue task
                $days_overdue = (strtotime($today) - strtotime($row['due_date'])) / (60 * 60 * 24);
                $days_text = $days_overdue <= 1 ? "today" : $days_overdue . " days ago";
                
                $message = "Task \"" . $row['title'] . "\" was due " . $days_text . " (" . date('M d, Y', strtotime($row['due_date'])) . ").";
                create_notification($conn, $user_id, $row['id'], $message, 'overdue');
            }
        }
        
        $stmt->close();
    }
}

/**
 * Create a notification
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $task_id Task ID
 * @param string $message Notification message
 * @param string $type Notification type (upcoming or overdue)
 * @return bool Success or failure
 */
function create_notification($conn, $user_id, $task_id, $message, $type) {
    $sql = "INSERT INTO notifications (user_id, task_id, message, type) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiss", $user_id, $task_id, $message, $type);
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    return false;
}

/**
 * Get unread notifications count for a user
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return int Number of unread notifications
 */
function get_unread_notifications_count($conn, $user_id) {
    $count = 0;
    
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $count = $row['count'];
        }
        
        $stmt->close();
    }
    
    return $count;
}

/**
 * Get notifications for a user
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $limit Maximum number of notifications to return (optional)
 * @return array Notifications array
 */
function get_user_notifications($conn, $user_id, $limit = 10) {
    $notifications = [];
    
    $sql = "SELECT n.id, n.message, n.type, n.is_read, n.created_at, n.task_id, t.title as task_title 
            FROM notifications n
            JOIN tasks t ON n.task_id = t.id
            WHERE n.user_id = ? 
            ORDER BY n.created_at DESC
            LIMIT ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $user_id, $limit);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
        }
        
        $stmt->close();
    }
    
    return $notifications;
}

/**
 * Mark notification as read
 * @param mysqli $conn Database connection
 * @param int $notification_id Notification ID
 * @param int $user_id User ID (for security)
 * @return bool Success or failure
 */
function mark_notification_as_read($conn, $notification_id, $user_id) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $notification_id, $user_id);
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    return false;
}

/**
 * Mark all notifications as read for a user
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return bool Success or failure
 */
function mark_all_notifications_as_read($conn, $user_id) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    return false;
}
?>
