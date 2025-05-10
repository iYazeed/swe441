<?php
/**
 * Performance Optimization Functions
 * 
 * This file contains functions to improve page load time and overall performance
 * of the Task Management System.
 * 
 * @author Task Management System Team
 * @version 1.0
 */

/**
 * Start output buffering to improve page rendering performance
 * 
 * @return void
 */
function start_page_buffer() {
    ob_start();
}

/**
 * End output buffering and send content to browser
 * 
 * @return void
 */
function end_page_buffer() {
    ob_end_flush();
}

/**
 * Set browser caching headers for improved performance
 * 
 * @param int $seconds Cache duration in seconds
 * @return void
 */
function set_cache_headers($seconds = 86400) { // Default: 1 day
    $ts = gmdate("D, d M Y H:i:s", time() + $seconds) . " GMT";
    header("Expires: $ts");
    header("Pragma: cache");
    header("Cache-Control: max-age=$seconds");
}

/**
 * Set no-cache headers for dynamic content
 * 
 * @return void
 */
function set_no_cache_headers() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

/**
 * Minify HTML output
 * 
 * @param string $html HTML content to minify
 * @return string Minified HTML
 */
function minify_html($html) {
    // Simple HTML minification - remove extra whitespace, comments, etc.
    $search = [
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/' // Remove HTML comments
    ];
    
    $replace = [
        '>',
        '<',
        '\\1',
        ''
    ];
    
    return preg_replace($search, $replace, $html);
}

/**
 * Generate HTML for lazy loading images
 * 
 * @param string $src Image source URL
 * @param string $alt Alt text
 * @param string $class CSS classes
 * @param int $width Width
 * @param int $height Height
 * @return string HTML for lazy loaded image
 */
function lazy_load_image($src, $alt = '', $class = '', $width = null, $height = null) {
    $dimensions = '';
    if ($width) {
        $dimensions .= " width=\"$width\"";
    }
    if ($height) {
        $dimensions .= " height=\"$height\"";
    }
    
    return "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\" data-src=\"$src\" alt=\"$alt\" class=\"lazy $class\"$dimensions>";
}

/**
 * Simple page caching function
 * 
 * @param string $page_id Unique identifier for the page
 * @param int $cache_time Cache duration in seconds
 * @return bool True if a valid cache exists, false otherwise
 */
function page_cache_start($page_id, $cache_time = 3600) {
    // Don't cache for logged-in users or POST requests
    if (isset($_SESSION['loggedin']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
        return false;
    }
    
    $cache_file = __DIR__ . "/../cache/" . md5($page_id) . ".html";
    
    // Check if cache directory exists, create if not
    if (!is_dir(__DIR__ . "/../cache/")) {
        mkdir(__DIR__ . "/../cache/", 0755, true);
    }
    
    // Check if cache file exists and is still valid
    if (file_exists($cache_file) && (time() - $cache_time) < filemtime($cache_file)) {
        // Output the cached file and exit
        readfile($cache_file);
        exit;
    }
    
    // Start output buffering to capture content
    ob_start();
    return true;
}

/**
 * End page caching and save content
 * 
 * @param string $page_id Unique identifier for the page
 * @return void
 */
function page_cache_end($page_id) {
    // Don't cache for logged-in users or POST requests
    if (isset($_SESSION['loggedin']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
        return;
    }
    
    $cache_file = __DIR__ . "/../cache/" . md5($page_id) . ".html";
    
    // Get the page content from the buffer
    $content = ob_get_contents();
    
    // Save the content to the cache file
    file_put_contents($cache_file, $content);
}

/**
 * Clear the page cache
 * 
 * @param string $page_id Specific page to clear (optional, clears all if not specified)
 * @return void
 */
function clear_page_cache($page_id = null) {
    if ($page_id) {
        $cache_file = __DIR__ . "/../cache/" . md5($page_id) . ".html";
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
    } else {
        // Clear all cache files
        $files = glob(__DIR__ . "/../cache/*.html");
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
?>
