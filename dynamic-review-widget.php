<?php
/**
 * Plugin Name: Dynamic Review Widget
 * Plugin URI: https://tafsinahmed.info/dynamic-review-widget
 * Description: A fully functional, dynamic review system with WordPress user integration and Elementor widget support
 * Version: 1.1.0
 * Author: Tafsin Ahmed Mohim
 * Author URI: https://tafsinahmed.info
 * License: GPL v2 or later
 * Text Domain: dynamic-review-widget
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DRW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DRW_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DRW_VERSION', '1.1.0');

class DynamicReviewWidget {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_submit_review', array($this, 'handle_review_submission'));
        add_action('wp_ajax_nopriv_submit_review', array($this, 'handle_review_submission'));
        add_action('wp_ajax_get_reviews', array($this, 'get_reviews'));
        add_action('wp_ajax_nopriv_get_reviews', array($this, 'get_reviews'));
        add_action('wp_ajax_check_user_review', array($this, 'check_user_review'));
        add_action('wp_ajax_nopriv_check_user_review', array($this, 'check_user_review'));
        
        // Elementor integration
        add_action('elementor/widgets/widgets_registered', array($this, 'register_elementor_widget'));
        add_action('elementor/frontend/after_register_scripts', array($this, 'register_elementor_scripts'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Create database table on activation
        register_activation_hook(__FILE__, array($this, 'create_database_table'));
        
        // Check and update database table on init
        add_action('init', array($this, 'check_and_update_database'));
    }
    
    public function init() {
        // Initialize plugin
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('drw-main', DRW_PLUGIN_URL . 'assets/js/review-widget.js', array('jquery'), DRW_VERSION, true);
        wp_enqueue_style('drw-styles', DRW_PLUGIN_URL . 'assets/css/review-widget.css', array(), DRW_VERSION);
        
        // Get current user data
        $current_user = wp_get_current_user();
        $user_data = array(
            'is_logged_in' => is_user_logged_in(),
            'user_id' => $current_user->ID,
            'display_name' => $current_user->display_name,
            'user_email' => $current_user->user_email,
            'avatar_url' => get_avatar_url($current_user->ID, array('size' => 96))
        );
        
        // Localize script for AJAX
        wp_localize_script('drw-main', 'drw_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('drw_nonce'),
            'user' => $user_data,
            'login_url' => wp_login_url(get_permalink()),
            'register_url' => wp_registration_url(),
            'debug' => WP_DEBUG
        ));
    }
    
    public function register_elementor_scripts() {
        wp_register_script('drw-elementor', DRW_PLUGIN_URL . 'assets/js/review-widget.js', array('jquery'), DRW_VERSION, true);
        wp_register_style('drw-elementor-styles', DRW_PLUGIN_URL . 'assets/css/review-widget.css', array(), DRW_VERSION);
    }
    
    public function create_database_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'dynamic_reviews';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL DEFAULT 0,
            user_id bigint(20) DEFAULT 0,
            reviewer_name varchar(255) NOT NULL,
            reviewer_email varchar(255) DEFAULT '',
            rating tinyint(1) NOT NULL,
            review_text text NOT NULL,
            review_date datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'approved',
            reviewer_ip varchar(45) DEFAULT '',
            is_verified tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Update the database version
        update_option('drw_db_version', '1.1.0');
    }
    
    public function check_and_update_database() {
        $current_version = get_option('drw_db_version', '1.0.0');
        
        if (version_compare($current_version, '1.1.0', '<')) {
            $this->update_database_table();
        }
    }
    
    public function update_database_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_reviews';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            // Create the table if it doesn't exist
            $this->create_database_table();
            return;
        }
        
        // Check if user_id column exists
        $user_id_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'user_id'");
        
        if (empty($user_id_exists)) {
            error_log('Adding user_id column to dynamic_reviews table');
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN user_id bigint(20) DEFAULT 0 AFTER post_id");
            $wpdb->query("ALTER TABLE $table_name ADD INDEX user_id (user_id)");
        }
        
        // Check if is_verified column exists
        $is_verified_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'is_verified'");
        
        if (empty($is_verified_exists)) {
            error_log('Adding is_verified column to dynamic_reviews table');
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN is_verified tinyint(1) DEFAULT 0 AFTER reviewer_ip");
        }
        
        // Update the database version
        update_option('drw_db_version', '1.1.0');
        error_log('Database updated to version 1.1.0');
    }
    
    public function handle_review_submission() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error('Invalid request method');
            return;
        }
        
        // Check if required POST data exists
        if (!isset($_POST['action']) || $_POST['action'] !== 'submit_review') {
            wp_send_json_error('Invalid action');
            return;
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'drw_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_reviews';
        
        // Sanitize and validate input data
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $review_text = isset($_POST['review_text']) ? sanitize_textarea_field($_POST['review_text']) : '';
        $reviewer_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        
        // Validate basic data
        if (empty($review_text) || $rating < 1 || $rating > 5 || $post_id <= 0) {
            wp_send_json_error('Please provide a valid rating and review text');
            return;
        }
        
        // Get user information
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $is_verified = is_user_logged_in() ? 1 : 0;
        
        if (is_user_logged_in()) {
            // Use logged-in user data
            $reviewer_name = !empty($current_user->display_name) ? $current_user->display_name : $current_user->user_login;
            $reviewer_email = $current_user->user_email;
            
            // Check if user already reviewed this post
            $existing_review = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE user_id = %d AND post_id = %d",
                $user_id, $post_id
            ));
            
            if ($existing_review) {
                wp_send_json_error('You have already reviewed this item. You can only submit one review per item.');
                return;
            }
        } else {
            // Use form data for non-logged-in users
            $reviewer_name = isset($_POST['reviewer_name']) ? sanitize_text_field($_POST['reviewer_name']) : '';
            $reviewer_email = isset($_POST['reviewer_email']) ? sanitize_email($_POST['reviewer_email']) : '';
            $user_id = 0;
            
            // Validate required fields for non-logged-in users
            if (empty($reviewer_name)) {
                wp_send_json_error('Please provide your name');
                return;
            }
            
            // Check for recent duplicate submissions from same IP
            $recent_review = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name 
                 WHERE reviewer_ip = %s 
                 AND reviewer_name = %s 
                 AND review_text = %s 
                 AND review_date > DATE_SUB(NOW(), INTERVAL 5 MINUTE)",
                $reviewer_ip, $reviewer_name, $review_text
            ));
            
            if ($recent_review) {
                wp_send_json_error('Duplicate review detected. Please wait before submitting again.');
                return;
            }
        }
        
        // Check if the required columns exist before inserting
        $columns = $wpdb->get_col("DESCRIBE $table_name");
        $has_user_id = in_array('user_id', $columns);
        $has_is_verified = in_array('is_verified', $columns);
        
        // Prepare data for insertion based on available columns
        if ($has_user_id && $has_is_verified) {
            // New table structure with user integration
            $insert_data = array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'reviewer_name' => $reviewer_name,
                'reviewer_email' => $reviewer_email,
                'rating' => $rating,
                'review_text' => $review_text,
                'reviewer_ip' => $reviewer_ip,
                'is_verified' => $is_verified,
                'review_date' => current_time('mysql')
            );
            $format = array('%d', '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%s');
        } else {
            // Old table structure without user integration
            $insert_data = array(
                'post_id' => $post_id,
                'reviewer_name' => $reviewer_name,
                'reviewer_email' => $reviewer_email,
                'rating' => $rating,
                'review_text' => $review_text,
                'reviewer_ip' => $reviewer_ip,
                'review_date' => current_time('mysql')
            );
            $format = array('%d', '%s', '%s', '%d', '%s', '%s', '%s');
        }
        
        // Insert review
        $result = $wpdb->insert($table_name, $insert_data, $format);
        
        if ($result !== false) {
            // Insert WooCommerce review
            $commentdata = array(
                'comment_post_ID' => $post_id,
                'comment_author' => $reviewer_name,
                'comment_author_email' => $reviewer_email,
                'comment_content' => $review_text,
                'comment_type' => 'review',
                'user_id' => $user_id,
                'comment_approved' => 1,
                'comment_date' => current_time('mysql'),
                'comment_author_IP' => $reviewer_ip,
            );
            $comment_id = wp_insert_comment($commentdata);

            // Add rating meta
            if ($comment_id && $rating) {
                add_comment_meta($comment_id, 'rating', $rating, true);
                add_comment_meta($comment_id, 'verified', $is_verified, true);
            }

            wp_send_json_success('Review submitted successfully');
        } else {
            wp_send_json_error('Failed to submit review. Please try again.');
        }
    }
    
    public function check_user_review() {
        if (!is_user_logged_in()) {
            wp_send_json_success(array('has_reviewed' => false));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_reviews';
        
        $post_id = intval($_GET['post_id']);
        $user_id = get_current_user_id();
        
        if ($post_id <= 0) {
            wp_send_json_error('Invalid post ID');
            return;
        }
        
        // Check if user_id column exists
        $columns = $wpdb->get_col("DESCRIBE $table_name");
        $has_user_id = in_array('user_id', $columns);
        
        if ($has_user_id) {
            $existing_review = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND post_id = %d",
                $user_id, $post_id
            ));
        } else {
            // Fallback for old table structure
            $existing_review = null;
        }
        
        wp_send_json_success(array(
            'has_reviewed' => !empty($existing_review),
            'review' => $existing_review
        ));
    }
    
    public function get_reviews() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_reviews';
        
        $post_id = intval($_GET['post_id']);
        $limit = intval($_GET['limit']) ?: 10;
        $offset = intval($_GET['offset']) ?: 0;
        
        if ($post_id <= 0) {
            wp_send_json_error('Invalid post ID');
            return;
        }
        
        // Get reviews
        $reviews = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d AND status = 'approved' ORDER BY review_date DESC LIMIT %d OFFSET %d",
            $post_id, $limit, $offset
        ));
        
        // Check if user_id column exists for avatar handling
        $columns = $wpdb->get_col("DESCRIBE $table_name");
        $has_user_id = in_array('user_id', $columns);
        
        // Add avatar URLs
        foreach ($reviews as &$review) {
            if ($has_user_id && isset($review->user_id) && $review->user_id > 0) {
                $review->avatar_url = get_avatar_url($review->user_id, array('size' => 96));
                $review->is_verified = isset($review->is_verified) ? $review->is_verified : 1;
            } else {
                $review->avatar_url = get_avatar_url($review->reviewer_email, array('size' => 96));
                $review->is_verified = 0;
            }
        }
        
        // Get statistics
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
            FROM $table_name WHERE post_id = %d AND status = 'approved'",
            $post_id
        ));
        
        wp_send_json_success(array(
            'reviews' => $reviews,
            'stats' => $stats
        ));
    }
    
    public function register_elementor_widget() {
        if (class_exists('\Elementor\Plugin')) {
            require_once(DRW_PLUGIN_PATH . 'includes/elementor-widget.php');
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \DRW_Elementor_Widget());
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Dynamic Reviews',
            'Dynamic Reviews',
            'manage_options',
            'dynamic-reviews',
            array($this, 'admin_page'),
            'dashicons-star-filled',
            30
        );
    }
    
    public function admin_page() {
        include DRW_PLUGIN_PATH . 'includes/admin-page.php';
    }
    
    public static function render_widget($atts = array()) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'show_form' => 'yes',
            'show_breakdown' => 'yes',
            'max_reviews' => 10,
            'title' => 'Customer Reviews',
            'require_login' => 'no'
        ), $atts);
        
        ob_start();
        include DRW_PLUGIN_PATH . 'templates/review-widget.php';
        return ob_get_clean();
    }
}

// Initialize the plugin
new DynamicReviewWidget();

// Shortcode support
add_shortcode('dynamic_reviews', array('DynamicReviewWidget', 'render_widget'));
?>