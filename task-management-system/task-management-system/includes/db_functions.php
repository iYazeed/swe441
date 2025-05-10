<?php
/**
 * Database Functions Library
 * 
 * This file contains optimized database functions for the Task Management System.
 * It includes query caching, optimized queries, and performance-focused database operations.
 * 
 * @author Task Management System Team
 * @version 1.0
 */

// Simple query cache implementation
$query_cache = [];

/**
 * Execute a cached query if available, otherwise execute and cache the result
 * 
 * @param mysqli $conn Database connection
 * @param string $query SQL query to execute
 * @param string $types Parameter types for prepared statement
 * @param array $params Parameters for prepared statement
 * @param int $cache_ttl Cache time-to-live in seconds (0 = no cache)
 * @return array|null Query result as associative array or null on error
 */
function execute_cached_query($conn, $query, $types = "", $params = [], $cache_ttl = 60) {
    global $query_cache;
    
    // Create a cache key based on the query and parameters
    $cache_key = md5($query . serialize($params));
    
    // Check if we have a cached result and it's still valid
    if ($cache_ttl > 0 && isset($query_cache[$cache_key]) && 
        (time() - $query_cache[$cache_key]['time'] < $cache_ttl)) {
        return $query_cache[$cache_key]['data'];
    }
    
    // No cache or expired cache, execute the query
    $result = [];
    
    if ($stmt = $conn->prepare($query)) {
        // Bind parameters if any
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        // Execute the query
        if ($stmt->execute()) {
            $query_result = $stmt->get_result();
            
            // Fetch all results
            while ($row = $query_result->fetch_assoc()) {
                $result[] = $row;
            }
            
            // Cache the result if caching is enabled
            if ($cache_ttl > 0) {
                $query_cache[$cache_key] = [
                    'time' => time(),
                    'data' => $result
                ];
            }
        } else {
            log_error("Query execution failed: " . $conn->error . " for query: " . $query);
            return null;
        }
        
        $stmt->close();
    } else {
        log_error("Query preparation failed: " . $conn->error . " for query: " . $query);
        return null;
    }
    
    return $result;
}

/**
 * Clear the query cache
 * 
 * @param string $cache_key Specific cache key to clear (optional, clears all if not specified)
 * @return void
 */
function clear_query_cache($cache_key = null) {
    global $query_cache;
    
    if ($cache_key !== null && isset($query_cache[$cache_key])) {
        unset($query_cache[$cache_key]);
    } else {
        $query_cache = [];
    }
}

/**
 * Get user tasks with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param string $category_id Category ID filter (optional)
 * @param string $status Status filter (optional)
 * @param int $limit Maximum number of tasks to return (optional)
 * @param int $offset Offset for pagination (optional)
 * @return array Tasks array or empty array on error
 */
function get_user_tasks($conn, $user_id, $category_id = null, $status = null, $limit = null, $offset = 0) {
    // Build the query with optimized JOIN and WHERE clauses
    $sql = "SELECT t.id, t.title, t.description, t.status, t.due_date, t.created_at, t.category_id, 
                  c.name as category_name, c.color as category_color 
           FROM tasks t 
           LEFT JOIN categories c ON t.category_id = c.id 
           WHERE t.user_id = ?";
    
    $params = [$user_id];
    $types = "i";
    
    // Add category filter if specified
    if ($category_id !== null) {
        $sql .= " AND t.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }
    
    // Add status filter if specified
    if ($status !== null) {
        $sql .= " AND t.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    // Add optimized ORDER BY clause
    $sql .= " ORDER BY CASE WHEN t.due_date IS NULL THEN 1 ELSE 0 END, t.due_date ASC, t.created_at DESC";
    
    // Add LIMIT and OFFSET if specified
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
            $types .= "i";
        }
    }
    
    // Use a short cache time for tasks since they change frequently
    return execute_cached_query($conn, $sql, $types, $params, 30);
}

/**
 * Get user categories with optimized query and caching
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array Categories array or empty array on error
 */
function get_user_categories_optimized($conn, $user_id) {
    $sql = "SELECT id, name, color FROM categories WHERE user_id = ? ORDER BY name ASC";
    
    // Cache categories for longer since they change less frequently
    return execute_cached_query($conn, $sql, "i", [$user_id], 300);
}

/**
 * Get task count by status for a user
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array Associative array with counts by status
 */
function get_task_counts_by_status($conn, $user_id) {
    $sql = "SELECT status, COUNT(*) as count FROM tasks WHERE user_id = ? GROUP BY status";
    
    $result = execute_cached_query($conn, $sql, "i", [$user_id], 60);
    
    // Format the result into a more usable structure
    $counts = [
        'pending' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'total' => 0
    ];
    
    if ($result) {
        foreach ($result as $row) {
            $counts[$row['status']] = (int)$row['count'];
            $counts['total'] += (int)$row['count'];
        }
    }
    
    return $counts;
}

/**
 * Get unread notifications count for a user with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return int Number of unread notifications
 */
function get_unread_notifications_count_optimized($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    
    $result = execute_cached_query($conn, $sql, "i", [$user_id], 30);
    
    return $result ? (int)$result[0]['count'] : 0;
}

/**
 * Get notifications for a user with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $limit Maximum number of notifications to return (optional)
 * @return array Notifications array
 */
function get_user_notifications_optimized($conn, $user_id, $limit = 10) {
    $sql = "SELECT n.id, n.message, n.type, n.is_read, n.created_at, n.task_id, t.title as task_title 
            FROM notifications n
            JOIN tasks t ON n.task_id = t.id
            WHERE n.user_id = ? 
            ORDER BY n.created_at DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        return execute_cached_query($conn, $sql, "ii", [$user_id, $limit], 30);
    } else {
        return execute_cached_query($conn, $sql, "i", [$user_id], 30);
    }
}

/**
 * Check for upcoming and overdue tasks with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array Array of tasks requiring notifications
 */
function check_tasks_requiring_notifications($conn, $user_id) {
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $day_after = date('Y-m-d', strtotime('+2 days'));
    
    // Get upcoming tasks (due in 1-2 days) that don't have notifications yet
    $upcoming_sql = "SELECT t.id, t.title, t.due_date, 'upcoming' as notification_type
                    FROM tasks t 
                    LEFT JOIN notifications n ON t.id = n.task_id AND n.type = 'upcoming'
                    WHERE t.user_id = ? 
                    AND t.due_date BETWEEN ? AND ? 
                    AND t.status != 'completed'
                    AND n.id IS NULL";
    
    $upcoming_tasks = execute_cached_query(
        $conn, 
        $upcoming_sql, 
        "iss", 
        [$user_id, $tomorrow, $day_after],
        0  // No caching for notification checks
    );
    
    // Get overdue tasks that don't have notifications yet
    $overdue_sql = "SELECT t.id, t.title, t.due_date, 'overdue' as notification_type
                   FROM tasks t 
                   LEFT JOIN notifications n ON t.id = n.task_id AND n.type = 'overdue'
                   WHERE t.user_id = ? 
                   AND t.due_date < ? 
                   AND t.status != 'completed'
                   AND n.id IS NULL";
    
    $overdue_tasks = execute_cached_query(
        $conn, 
        $overdue_sql, 
        "is", 
        [$user_id, $today],
        0  // No caching for notification checks
    );
    
    // Combine the results
    return array_merge($upcoming_tasks ?: [], $overdue_tasks ?: []);
}

/**
 * Create notifications in bulk for efficiency
 * 
 * @param mysqli $conn Database connection
 * @param array $notifications Array of notification data
 * @return bool Success or failure
 */
function create_notifications_bulk($conn, $notifications) {
    if (empty($notifications)) {
        return true;
    }
    
    // Start a transaction for bulk insert
    $conn->begin_transaction();
    
    try {
        $sql = "INSERT INTO notifications (user_id, task_id, message, type) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($notifications as $notification) {
            $stmt->bind_param(
                "iiss", 
                $notification['user_id'], 
                $notification['task_id'], 
                $notification['message'], 
                $notification['type']
            );
            $stmt->execute();
        }
        
        $stmt->close();
        $conn->commit();
        
        // Clear notification-related caches
        clear_query_cache();
        
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        log_error("Failed to create notifications in bulk: " . $e->getMessage());
        return false;
    }
}

/**
 * Get a single task with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param int $task_id Task ID
 * @param int $user_id User ID (for security)
 * @return array|null Task data or null if not found
 */
function get_task_by_id($conn, $task_id, $user_id) {
    $sql = "SELECT t.*, c.name as category_name, c.color as category_color 
            FROM tasks t 
            LEFT JOIN categories c ON t.category_id = c.id 
            WHERE t.id = ? AND t.user_id = ?";
    
    $result = execute_cached_query($conn, $sql, "ii", [$task_id, $user_id], 60);
    
    return $result ? $result[0] : null;
}

/**
 * Update task status with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param int $task_id Task ID
 * @param string $status New status
 * @param int $user_id User ID (for security)
 * @return bool Success or failure
 */
function update_task_status($conn, $task_id, $status, $user_id) {
    $sql = "UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sii", $status, $task_id, $user_id);
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            // Clear task-related caches
            clear_query_cache();
        }
        
        return $success;
    }
    
    return false;
}

/**
 * Mark all notifications as read with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return bool Success or failure
 */
function mark_all_notifications_as_read_optimized($conn, $user_id) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            // Clear notification-related caches
            clear_query_cache();
        }
        
        return $success;
    }
    
    return false;
}

/**
 * Check if user exists with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param string $username Username to check
 * @return bool True if user exists, false otherwise
 */
function user_exists($conn, $username) {
    $sql = "SELECT 1 FROM users WHERE username = ? LIMIT 1";
    
    $result = execute_cached_query($conn, $sql, "s", [$username], 300);
    
    return !empty($result);
}

/**
 * Check if email exists with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param string $email Email to check
 * @return bool True if email exists, false otherwise
 */
function email_exists($conn, $email) {
    $sql = "SELECT 1 FROM users WHERE email = ? LIMIT 1";
    
    $result = execute_cached_query($conn, $sql, "s", [$email], 300);
    
    return !empty($result);
}

/**
 * Get user data by username with optimized query
 * 
 * @param mysqli $conn Database connection
 * @param string $username Username to look up
 * @return array|null User data or null if not found
 */
function get_user_by_username($conn, $username) {
    $sql = "SELECT id, username, password, email FROM users WHERE username = ?";
    
    $result = execute_cached_query($conn, $sql, "s", [$username], 0); // No caching for security
    
    return $result ? $result[0] : null;
}
?>
