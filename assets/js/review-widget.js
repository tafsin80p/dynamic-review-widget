jQuery(document).ready(function($) {
    'use strict';
    
    class DynamicReviewWidget {
        constructor(element) {
            this.element = $(element);
            this.postId = this.element.data('post-id');
            this.requireLogin = this.element.data('require-login') === 'yes';
            this.currentOffset = 0;
            this.maxReviews = 10;
            this.isLoading = false;
            this.isSubmitting = false;
            
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.loadReviews();
            this.checkUserReview();
        }
        
        bindEvents() {
            // Star rating interaction
            this.element.on('click', '.drw-star', (e) => {
                if ($(e.target).closest('.drw-star-rating').length) {
                    this.handleStarClick(e);
                }
            });
            
            this.element.on('mouseenter', '.drw-star', (e) => {
                if ($(e.target).closest('.drw-star-rating').length) {
                    this.handleStarHover(e);
                }
            });
            
            this.element.on('mouseleave', '.drw-star-rating', () => {
                this.resetStarHover();
            });
            
            // Form submission - Remove any existing handlers first
            this.element.off('submit', '#drw-submit-form');
            this.element.on('submit', '#drw-submit-form', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleFormSubmit(e);
                return false;
            });
            
            // Load more reviews
            this.element.on('click', '.drw-load-more', () => {
                this.loadMoreReviews();
            });
        }
        
        checkUserReview() {
            if (!drw_ajax.user.is_logged_in) {
                this.showReviewForm();
                return;
            }
            
            $.ajax({
                url: drw_ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'check_user_review',
                    post_id: this.postId
                },
                success: (response) => {
                    if (response.success) {
                        if (response.data.has_reviewed) {
                            this.showExistingReview(response.data.review);
                        } else {
                            this.showReviewForm();
                        }
                    } else {
                        this.showReviewForm();
                    }
                },
                error: () => {
                    this.showReviewForm();
                }
            });
        }
        
        showReviewForm() {
            this.element.find('#drw-submit-form').show();
            this.element.find('.drw-already-reviewed').hide();
            
            // Pre-fill user data if logged in
            if (drw_ajax.user.is_logged_in) {
                this.element.find('#drw-reviewer-name').val(drw_ajax.user.display_name);
                this.element.find('#drw-reviewer-email').val(drw_ajax.user.user_email);
            }
        }
        
        showExistingReview(review) {
            this.element.find('#drw-submit-form').hide();
            
            const existingReviewEl = this.element.find('.drw-already-reviewed');
            const date = new Date(review.review_date);
            const formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // Build stars HTML
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                starsHtml += `<span class="drw-star ${i <= review.rating ? 'active' : ''}">★</span>`;
            }
            
            existingReviewEl.find('.drw-existing-rating').html(starsHtml);
            existingReviewEl.find('.drw-existing-text').text(review.review_text);
            existingReviewEl.find('.drw-existing-date').text(`Reviewed on ${formattedDate}`);
            existingReviewEl.show();
        }
        
        handleStarClick(e) {
            const rating = $(e.target).data('rating');
            const stars = this.element.find('.drw-star-rating .drw-star');
            
            // Update visual state
            stars.removeClass('active');
            stars.each(function(index) {
                if ($(this).data('rating') <= rating) {
                    $(this).addClass('active');
                }
            });
            
            // Update hidden input
            this.element.find('#drw-rating-input').val(rating);
        }
        
        handleStarHover(e) {
            const rating = $(e.target).data('rating');
            const stars = this.element.find('.drw-star-rating .drw-star');
            
            stars.each(function(index) {
                if ($(this).data('rating') <= rating) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        }
        
        resetStarHover() {
            const currentRating = this.element.find('#drw-rating-input').val();
            const stars = this.element.find('.drw-star-rating .drw-star');
            
            stars.removeClass('active');
            if (currentRating) {
                stars.each(function(index) {
                    if ($(this).data('rating') <= currentRating) {
                        $(this).addClass('active');
                    }
                });
            }
        }
        
        handleFormSubmit(e) {
            console.log('Form submit triggered');
            
            if (this.isSubmitting) {
                console.log('Already submitting, preventing duplicate');
                return false;
            }
            
            const form = $(e.target);
            
            // Check if AJAX variables are available
            if (typeof drw_ajax === 'undefined') {
                console.error('drw_ajax is not defined');
                this.showMessage('Configuration error. Please refresh the page.', 'error');
                return false;
            }
            
            // Collect form data
            const formData = {
                action: 'submit_review',
                nonce: drw_ajax.nonce,
                post_id: this.postId,
                rating: form.find('[name="rating"]').val(),
                review_text: form.find('[name="review_text"]').val()
            };
            
            // Add name and email for non-logged-in users
            if (!drw_ajax.user.is_logged_in) {
                formData.reviewer_name = form.find('[name="reviewer_name"]').val();
                formData.reviewer_email = form.find('[name="reviewer_email"]').val();
                
                if (!formData.reviewer_name || !formData.reviewer_name.trim()) {
                    this.showMessage('Please enter your name.', 'error');
                    return false;
                }
            }
            
            // Validate form
            if (!formData.review_text || !formData.review_text.trim()) {
                this.showMessage('Please provide your review text.', 'error');
                return false;
            }
            
            if (!formData.rating || formData.rating < 1 || formData.rating > 5) {
                this.showMessage('Please provide a rating.', 'error');
                return false;
            }
            
            console.log('Submitting form data:', formData);
            console.log('AJAX URL:', drw_ajax.ajax_url);
            
            this.isSubmitting = true;
            const submitBtn = form.find('.drw-submit-btn');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Submitting...');
            
            $.ajax({
                url: drw_ajax.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 30000, // 30 second timeout
                success: (response) => {
                    console.log('AJAX response:', response);
                    if (response && response.success) {
                        this.showMessage('Review submitted successfully!', 'success');
                        form[0].reset();
                        this.element.find('.drw-star-rating .drw-star').removeClass('active');
                        this.element.find('#drw-rating-input').val('');
                        this.currentOffset = 0;
                        this.loadReviews();
                        
                        // Re-check user review status
                        setTimeout(() => {
                            this.checkUserReview();
                        }, 1000);
                    } else {
                        const errorMessage = response && response.data ? response.data : 'Failed to submit review.';
                        console.error('Server error:', errorMessage);
                        this.showMessage(errorMessage, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error details:', {
                        xhr: xhr,
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    
                    let errorMessage = 'An error occurred. Please try again.';
                    
                    if (xhr.status === 0) {
                        errorMessage = 'Network error. Please check your connection.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Server endpoint not found.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please try again later.';
                    } else if (status === 'timeout') {
                        errorMessage = 'Request timed out. Please try again.';
                    }
                    
                    this.showMessage(errorMessage, 'error');
                },
                complete: () => {
                    this.isSubmitting = false;
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
            
            return false;
        }
        
        loadReviews() {
            const container = this.element.find('.drw-reviews-container');
            
            if (this.currentOffset === 0) {
                container.html('<div class="drw-loading">Loading reviews...</div>');
            }
            
            $.ajax({
                url: drw_ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'get_reviews',
                    post_id: this.postId,
                    limit: this.maxReviews,
                    offset: this.currentOffset
                },
                success: (response) => {
                    if (response.success) {
                        this.renderReviews(response.data.reviews, response.data.stats);
                        this.updateStatistics(response.data.stats);
                    } else {
                        container.html('<div class="drw-loading">No reviews found.</div>');
                    }
                },
                error: () => {
                    container.html('<div class="drw-loading">Failed to load reviews.</div>');
                }
            });
        }
        
        loadMoreReviews() {
            this.currentOffset += this.maxReviews;
            
            $.ajax({
                url: drw_ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'get_reviews',
                    post_id: this.postId,
                    limit: this.maxReviews,
                    offset: this.currentOffset
                },
                success: (response) => {
                    if (response.success && response.data.reviews.length > 0) {
                        this.appendReviews(response.data.reviews);
                        
                        if (response.data.reviews.length < this.maxReviews) {
                            this.element.find('.drw-load-more').hide();
                        }
                    } else {
                        this.element.find('.drw-load-more').hide();
                    }
                }
            });
        }
        
        renderReviews(reviews, stats) {
            const container = this.element.find('.drw-reviews-container');
            
            if (reviews.length === 0) {
                container.html('<div class="drw-loading">No reviews yet. Be the first to review!</div>');
                this.element.find('.drw-load-more').hide();
                return;
            }
            
            let html = '';
            reviews.forEach(review => {
                html += this.createReviewCard(review);
            });
            
            container.html(html);
            
            if (reviews.length === this.maxReviews) {
                this.element.find('.drw-load-more').show();
            } else {
                this.element.find('.drw-load-more').hide();
            }
        }
        
        appendReviews(reviews) {
            const container = this.element.find('.drw-reviews-container');
            
            let html = '';
            reviews.forEach(review => {
                html += this.createReviewCard(review);
            });
            
            container.append(html);
        }
        
        createReviewCard(review) {
            const date = new Date(review.review_date);
            const formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                starsHtml += `<span class="drw-star ${i <= review.rating ? 'active' : ''}">★</span>`;
            }
            
            const isVerified = parseInt(review.is_verified) === 1;
            const verifiedClass = isVerified ? 'verified' : '';
            const verifiedIcon = isVerified ? '<span class="drw-verified-icon" title="Verified User">✓</span>' : '';
            
            // Use avatar URL if available, otherwise create initials
            let avatarHtml = '';
            if (review.avatar_url) {
                avatarHtml = `<img src="${review.avatar_url}" alt="${this.escapeHtml(review.reviewer_name)}">`;
            } else {
                const initials = review.reviewer_name.split(' ').map(n => n[0]).join('').toUpperCase();
                avatarHtml = initials;
            }
            
            return `
                <div class="drw-review-card ${verifiedClass}">
                    <div class="drw-review-header">
                        <div class="drw-reviewer-info">
                            <div class="drw-reviewer-avatar">${avatarHtml}</div>
                            <div class="drw-reviewer-name">
                                ${this.escapeHtml(review.reviewer_name)}
                                ${verifiedIcon}
                            </div>
                        </div>
                        <div class="drw-review-date">${formattedDate}</div>
                    </div>
                    <div class="drw-review-rating">
                        ${starsHtml}
                    </div>
                    <div class="drw-review-text">${this.escapeHtml(review.review_text)}</div>
                </div>
            `;
        }
        
        updateStatistics(stats) {
            if (!stats || stats.total_reviews == 0) {
                this.element.find('.drw-average-rating').text('0');
                this.element.find('.drw-total-reviews').text('(0)');
                return;
            }
            
            const averageRating = Math.round(parseFloat(stats.average_rating) * 10) / 10;
            this.element.find('.drw-average-rating').text(averageRating);
            this.element.find('.drw-total-reviews').text(`(${stats.total_reviews})`);
            
            // Update rating breakdown
            for (let i = 1; i <= 5; i++) {
                const count = parseInt(stats[`rating_${i}`]) || 0;
                const percentage = stats.total_reviews > 0 ? Math.round((count / stats.total_reviews) * 100) : 0;
                
                this.element.find(`.drw-rating-bar[data-rating="${i}"]`).css('width', `${percentage}%`);
                this.element.find(`.drw-rating-row:nth-child(${6-i}) .drw-rating-percentage`).text(`${percentage}%`);
            }
        }
        
        showMessage(message, type) {
            const messageEl = this.element.find('.drw-form-message');
            messageEl.removeClass('success error').addClass(type);
            messageEl.text(message).show();
            
            setTimeout(() => {
                messageEl.fadeOut();
            }, 5000);
        }
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
    
    // Initialize all review widgets on the page
    $('.drw-review-widget').each(function() {
        if (!$(this).data('drw-initialized')) {
            new DynamicReviewWidget(this);
            $(this).data('drw-initialized', true);
        }
    });
});