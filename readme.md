=== Dynamic Review Widget ===
Contributors: mohimmolla
Tags: reviews, ratings, elementor, widget, testimonials
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin Overview:
Here is the preview Link: https://www.stompitparts.com/product/diy-guitar-pedal-wiring-kit-3
<img width="1230" height="669" alt="image" src="https://github.com/user-attachments/assets/c79f7d62-8c7e-4d21-89b7-76ca20c79713" />
<img width="1220" height="764" alt="image" src="https://github.com/user-attachments/assets/c2f8d320-1c80-4a79-9fc3-a1b6e893d21b" />
<img width="1727" height="443" alt="image" src="https://github.com/user-attachments/assets/7734964e-9664-4dc4-8635-817df74a18b1" />






A fully functional, dynamic review system with Elementor widget integration.

== Description ==

Dynamic Review Widget is a comprehensive review system for WordPress that allows your visitors to leave reviews and ratings on your posts and pages. The plugin features a beautiful, responsive design and seamless integration with Elementor.

**Key Features:**

* ⭐ Interactive star rating system
* 📊 Dynamic rating breakdown with percentages
* 💬 Real-time review submission and display
* 🎨 Beautiful, responsive design
* 🔧 Elementor widget integration
* 📱 Mobile-friendly interface
* 🛡️ Built-in spam protection
* 📈 Admin dashboard with statistics
* 🎯 Shortcode support
* 🔄 AJAX-powered for smooth user experience

**Perfect for:**
* Product reviews
* Service testimonials
* Blog post feedback
* Course reviews
* Restaurant ratings
* Any content that needs user feedback

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/dynamic-review-widget` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The plugin will automatically create the necessary database table.
4. Use the shortcode `[dynamic_reviews]` or add the Elementor widget to display reviews.

== Usage ==

**Shortcode:**
`[dynamic_reviews post_id="123" show_form="yes" show_breakdown="yes" max_reviews="10" title="Customer Reviews"]`

**Elementor Widget:**
Search for "Dynamic Review Widget" in the Elementor widget panel.

**PHP Function:**
`<?php echo DynamicReviewWidget::render_widget(array('post_id' => get_the_ID())); ?>`

== Frequently Asked Questions ==

= How do I display reviews on my page? =

You can use the shortcode `[dynamic_reviews]`, add the Elementor widget, or use the PHP function in your theme files.

= Can I moderate reviews before they appear? =

Yes, you can manage all reviews from the admin dashboard and approve/reject them as needed.

= Is the plugin mobile-friendly? =

Absolutely! The plugin is fully responsive and works perfectly on all devices.

= Can I customize the appearance? =

Yes, the plugin includes CSS classes that you can style, and the Elementor widget has built-in styling options.

== Screenshots ==

1. Review widget frontend display
2. Elementor widget settings
3. Admin dashboard with review management
4. Mobile responsive design

== Changelog ==

= 1.0.0 =
* Initial release
* Interactive star rating system
* Dynamic rating breakdown
* Elementor widget integration
* Admin dashboard
* Shortcode support
* Mobile responsive design

== Upgrade Notice ==

= 1.0.0 =
Initial release of Dynamic Review Widget.
