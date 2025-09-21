<?php
if (!defined('ABSPATH')) {
    exit;
}

class DRW_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'dynamic_review_widget';
    }
    
    public function get_title() {
        return __('Dynamic Review Widget', 'dynamic-review-widget');
    }
    
    public function get_icon() {
        return 'eicon-star';
    }
    
    public function get_categories() {
        return ['general'];
    }
    
    public function get_script_depends() {
        return ['drw-elementor'];
    }
    
    public function get_style_depends() {
        return ['drw-elementor-styles'];
    }
    
    protected function _register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'dynamic-review-widget'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'widget_title',
            [
                'label' => __('Widget Title', 'dynamic-review-widget'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Customer Reviews', 'dynamic-review-widget'),
                'placeholder' => __('Enter widget title', 'dynamic-review-widget'),
            ]
        );
        
        $this->add_control(
            'post_id',
            [
                'label' => __('Post ID', 'dynamic-review-widget'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => get_the_ID(),
                'description' => __('Leave empty to use current post ID', 'dynamic-review-widget'),
            ]
        );
        
        $this->add_control(
            'show_form',
            [
                'label' => __('Show Review Form', 'dynamic-review-widget'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'dynamic-review-widget'),
                'label_off' => __('Hide', 'dynamic-review-widget'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_breakdown',
            [
                'label' => __('Show Rating Breakdown', 'dynamic-review-widget'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'dynamic-review-widget'),
                'label_off' => __('Hide', 'dynamic-review-widget'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'require_login',
            [
                'label' => __('Require Login to Review', 'dynamic-review-widget'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'dynamic-review-widget'),
                'label_off' => __('No', 'dynamic-review-widget'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => __('If enabled, only logged-in users can submit reviews', 'dynamic-review-widget'),
            ]
        );
        
        $this->add_control(
            'max_reviews',
            [
                'label' => __('Maximum Reviews to Display', 'dynamic-review-widget'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 10,
                'min' => 1,
                'max' => 50,
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'dynamic-review-widget'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'primary_color',
            [
                'label' => __('Primary Color', 'dynamic-review-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#EA5A3C',
                'selectors' => [
                    '{{WRAPPER}} .drw-star.active' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .drw-star-icon' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .drw-rating-bar' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .drw-submit-btn' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .drw-login-btn' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'dynamic-review-widget'),
                'selector' => '{{WRAPPER}} .drw-widget-title',
            ]
        );
        
        $this->add_control(
            'card_background',
            [
                'label' => __('Card Background', 'dynamic-review-widget'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .drw-review-card' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $atts = array(
            'post_id' => $settings['post_id'] ?: get_the_ID(),
            'show_form' => $settings['show_form'],
            'show_breakdown' => $settings['show_breakdown'],
            'max_reviews' => $settings['max_reviews'],
            'title' => $settings['widget_title'],
            'require_login' => $settings['require_login']
        );
        
        echo DynamicReviewWidget::render_widget($atts);
    }
}