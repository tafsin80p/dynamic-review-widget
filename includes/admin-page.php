<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'dynamic_reviews';

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] === 'bulk_action' && isset($_POST['review_ids'])) {
    $action = sanitize_text_field($_POST['bulk_action']);
    $review_ids = array_map('intval', $_POST['review_ids']);
    
    if ($action === 'approve') {
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET status = 'approved' WHERE id IN (" . implode(',', array_fill(0, count($review_ids), '%d')) . ")",
            ...$review_ids
        ));
        echo '<div class="notice notice-success"><p>Reviews approved successfully.</p></div>';
    } elseif ($action === 'reject') {
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET status = 'rejected' WHERE id IN (" . implode(',', array_fill(0, count($review_ids), '%d')) . ")",
            ...$review_ids
        ));
        echo '<div class="notice notice-success"><p>Reviews rejected successfully.</p></div>';
    } elseif ($action === 'delete') {
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE id IN (" . implode(',', array_fill(0, count($review_ids), '%d')) . ")",
            ...$review_ids
        ));
        echo '<div class="notice notice-success"><p>Reviews deleted successfully.</p></div>';
    }
}

// Get reviews with pagination
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$where_clause = $status_filter !== 'all' ? $wpdb->prepare("WHERE status = %s", $status_filter) : '';

$total_reviews = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where_clause");
$reviews = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name $where_clause ORDER BY review_date DESC LIMIT %d OFFSET %d",
    $per_page, $offset
));

$total_pages = ceil($total_reviews / $per_page);
?>

<div class="wrap">
    <h1>Dynamic Reviews</h1>
    
    <!-- Statistics -->
    <div class="drw-admin-stats" style="display: flex; gap: 20px; margin: 20px 0;">
        <?php
        $stats = $wpdb->get_row("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            AVG(rating) as average_rating
            FROM $table_name");
        ?>
        <div class="drw-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); min-width: 150px;">
            <h3 style="margin: 0 0 10px 0; color: #1d4ed8;">Total Reviews</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0;"><?php echo $stats->total; ?></p>
        </div>
        <div class="drw-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); min-width: 150px;">
            <h3 style="margin: 0 0 10px 0; color: #059669;">Approved</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0;"><?php echo $stats->approved; ?></p>
        </div>
        <div class="drw-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); min-width: 150px;">
            <h3 style="margin: 0 0 10px 0; color: #d97706;">Pending</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0;"><?php echo $stats->pending; ?></p>
        </div>
        <div class="drw-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); min-width: 150px;">
            <h3 style="margin: 0 0 10px 0; color: #EA5A3C;">Average Rating</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0;"><?php echo $stats->average_rating ? round($stats->average_rating, 1) : '0'; ?> ★</p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <select name="status" id="status-filter">
                <option value="all" <?php selected($status_filter, 'all'); ?>>All Statuses</option>
                <option value="approved" <?php selected($status_filter, 'approved'); ?>>Approved</option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
                <option value="rejected" <?php selected($status_filter, 'rejected'); ?>>Rejected</option>
            </select>
            <input type="submit" class="button" value="Filter" onclick="window.location.href='?page=dynamic-reviews&status=' + document.getElementById('status-filter').value;">
        </div>
    </div>
    
    <!-- Reviews Table -->
    <form method="post">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="bulk_action">
                    <option value="">Bulk Actions</option>
                    <option value="approve">Approve</option>
                    <option value="reject">Reject</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all">
                    </td>
                    <th class="manage-column">Reviewer</th>
                    <th class="manage-column">Rating</th>
                    <th class="manage-column">Review</th>
                    <th class="manage-column">Post</th>
                    <th class="manage-column">Date</th>
                    <th class="manage-column">Status</th>
                    <th class="manage-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">No reviews found.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <tr>
                    <th class="check-column">
                        <input type="checkbox" name="review_ids[]" value="<?php echo $review->id; ?>">
                    </th>
                    <td>
                        <strong><?php echo esc_html($review->reviewer_name); ?></strong>
                        <?php if ($review->reviewer_email): ?>
                        <br><small><?php echo esc_html($review->reviewer_email); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span style="color: <?php echo $i <= $review->rating ? '#EA5A3C' : '#ddd'; ?>;">★</span>
                        <?php endfor; ?>
                        (<?php echo $review->rating; ?>/5)
                    </td>
                    <td>
                        <div style="max-width: 300px; overflow: hidden;">
                            <?php echo esc_html(wp_trim_words($review->review_text, 20)); ?>
                        </div>
                    </td>
                    <td>
                        <?php 
                        $post = get_post($review->post_id);
                        if ($post) {
                            echo '<a href="' . get_permalink($post->ID) . '" target="_blank">' . esc_html($post->post_title) . '</a>';
                        } else {
                            echo 'Post not found';
                        }
                        ?>
                    </td>
                    <td><?php echo date('M j, Y g:i A', strtotime($review->review_date)); ?></td>
                    <td>
                        <span class="drw-status drw-status-<?php echo $review->status; ?>" style="
                            padding: 4px 8px; 
                            border-radius: 4px; 
                            font-size: 12px; 
                            font-weight: 500;
                            background: <?php echo $review->status === 'approved' ? '#d1fae5' : ($review->status === 'pending' ? '#fef3c7' : '#fee2e2'); ?>;
                            color: <?php echo $review->status === 'approved' ? '#065f46' : ($review->status === 'pending' ? '#92400e' : '#991b1b'); ?>;
                        ">
                            <?php echo ucfirst($review->status); ?>
                        </span>
                    </td>
                    <td>
                        <a href="?page=dynamic-reviews&action=edit&id=<?php echo $review->id; ?>" class="button button-small">Edit</a>
                        <a href="?page=dynamic-reviews&action=delete&id=<?php echo $review->id; ?>" class="button button-small" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <input type="hidden" name="action" value="bulk_action">
    </form>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            $page_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total' => $total_pages,
                'current' => $current_page
            ));
            echo $page_links;
            ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Usage Instructions -->
    <div class="drw-usage-instructions" style="margin-top: 40px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2>How to Use</h2>
        <h3>Shortcode</h3>
        <p>Use this shortcode to display the review widget anywhere:</p>
        <code>[dynamic_reviews post_id="123" show_form="yes" show_breakdown="yes" max_reviews="10" title="Customer Reviews"]</code>
        
        <h3>Elementor Widget</h3>
        <p>Search for "Dynamic Review Widget" in the Elementor widget panel and drag it to your page.</p>
        
        <h3>PHP Function</h3>
        <p>Use this PHP function in your theme files:</p>
        <code><?php echo DynamicReviewWidget::render_widget(array('post_id' => get_the_ID())); ?></code>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Select all checkbox functionality
    $('#cb-select-all').on('change', function() {
        $('input[name="review_ids[]"]').prop('checked', this.checked);
    });
});
</script>