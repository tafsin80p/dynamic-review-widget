<?php
if (!defined('ABSPATH')) {
    exit;
}

$post_id = $atts['post_id'];
$show_form = $atts['show_form'] === 'yes';
$show_breakdown = $atts['show_breakdown'] === 'yes';
$max_reviews = intval($atts['max_reviews']);
$title = $atts['title'];
$require_login = $atts['require_login'] === 'yes';

$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
?>

<div class="drw-review-widget" data-post-id="<?php echo esc_attr($post_id); ?>" data-require-login="<?php echo esc_attr($require_login); ?>">
    <div class="drw-container">
        
        <?php if (!empty($title)): ?>
        <h3 class="drw-widget-title"><?php echo esc_html($title); ?></h3>
        <?php endif; ?>
        
        <div class="drw-grid">
            <!-- Left Column: Rating Overview and Form -->
            <div class="drw-left-column">
                
                <!-- Overall Rating Display -->
                <div class="drw-overall-rating">
                    <div class="drw-rating-display">
                        <span class="drw-star-icon">★</span>
                        <span class="drw-average-rating">0</span>
                        <span class="drw-total-reviews">(0)</span>
                    </div>
                </div>
                
                <?php if ($show_breakdown): ?>
                <!-- Rating Breakdown -->
                <div class="drw-rating-breakdown">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div class="drw-rating-row">
                        <div class="drw-rating-label">
                            <span><?php echo $i; ?></span>
                            <span class="drw-star-small">★</span>
                        </div>
                        <div class="drw-rating-bar-container">
                            <div class="drw-rating-bar" data-rating="<?php echo $i; ?>" style="width: 0%"></div>
                        </div>
                        <span class="drw-rating-percentage">0%</span>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($show_form): ?>
                <!-- Review Form -->
                <div class="drw-review-form">
                    <h4>Write a Review</h4>
                    
                    <!-- User Status Display -->
                    <?php if ($is_logged_in): ?>
                    <div class="drw-user-info">
                        <div class="drw-user-avatar">
                            <?php echo get_avatar($current_user->ID, 32); ?>
                        </div>
                        <div class="drw-user-details">
                            <span class="drw-user-name"><?php echo esc_html($current_user->display_name); ?></span>
                            <span class="drw-verified-badge">✓ Verified User</span>
                        </div>
                    </div>
                    <?php elseif ($require_login): ?>
                    <div class="drw-login-required">
                        <p>Please log in to write a review.</p>
                        <div class="drw-auth-buttons">
                            <a href="<?php echo wp_login_url(get_permalink()); ?>" class="drw-login-btn">Login</a>
                            <?php if (get_option('users_can_register')): ?>
                            <a href="<?php echo wp_registration_url(); ?>" class="drw-register-btn">Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Review Form -->
                    <?php if (!$require_login || $is_logged_in): ?>
                    <form id="drw-submit-form" style="display: none;">
                        <div class="drw-form-group">
                            <label>Rating</label>
                            <div class="drw-star-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="drw-star" data-rating="<?php echo $i; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="drw-rating-input" required>
                        </div>
                        
                        <?php if (!$is_logged_in): ?>
                        <div class="drw-form-group">
                            <label for="drw-reviewer-name">Your Name</label>
                            <input type="text" id="drw-reviewer-name" name="reviewer_name" required>
                        </div>
                        
                        <div class="drw-form-group">
                            <label for="drw-reviewer-email">Email (Optional)</label>
                            <input type="email" id="drw-reviewer-email" name="reviewer_email">
                        </div>
                        <?php endif; ?>
                        
                        <div class="drw-form-group">
                            <label for="drw-review-text">Your Review</label>
                            <textarea id="drw-review-text" name="review_text" rows="4" required placeholder="Share your experience..."></textarea>
                        </div>
                        
                        <button type="submit" class="drw-submit-btn">Submit Review</button>
                    </form>
                    
                    <!-- Already Reviewed Message -->
                    <div class="drw-already-reviewed" style="display: none;">
                        <div class="drw-existing-review">
                            <h5>Your Review</h5>
                            <div class="drw-existing-rating"></div>
                            <div class="drw-existing-text"></div>
                            <small class="drw-existing-date"></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="drw-form-message" style="display: none;"></div>
                </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Right Column: Individual Reviews -->
            <div class="drw-right-column">
                <h4>Customer Reviews</h4>
                <div class="drw-reviews-container">
                    <div class="drw-loading">Loading reviews...</div>
                </div>
                
                <button class="drw-load-more" style="display: none;">Load More Reviews</button>
            </div>
            
        </div>
    </div>
</div>