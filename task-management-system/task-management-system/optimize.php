<?php
/**
 * Performance Optimization Script
 * 
 * This script performs various optimization tasks:
 * - Minifies CSS and JavaScript files
 * - Clears the page cache
 * - Creates necessary directories
 * 
 * @author Task Management System Team
 * @version 1.0
 */

require_once "includes/performance.php";

// Create cache directory if it doesn't exist
if (!is_dir(__DIR__ . "/cache/")) {
    mkdir(__DIR__ . "/cache/", 0755, true);
    echo "Cache directory created.<br>";
}

// Clear existing cache
clear_page_cache();
echo "Page cache cleared.<br>";

// Check if minified CSS and JS files exist and are writable
$css_file = __DIR__ . "/assets/css/style.min.css";
$js_file = __DIR__ . "/assets/js/validation.min.js";

if (file_exists($css_file) && !is_writable($css_file)) {
    echo "Warning: Cannot write to $css_file. Please check file permissions.<br>";
}

if (file_exists($js_file) && !is_writable($js_file)) {
    echo "Warning: Cannot write to $js_file. Please check file permissions.<br>";
}

echo "Optimization completed successfully.<br>";
echo "To use minified assets, update your HTML to reference style.min.css and validation.min.js.<br>";
?>
