=== Dynamic Review Widget ===
Contributors: mohimmolla
Tags: reviews, ratings, elementor, widget, testimonials
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin Overview: ![Error](https://drive.google.com/file/d/1B27ftbYDsdP45W00gUCkh7SEPrwgND9q/view?usp=sharing)

<img width="1471" height="526" alt="image" src="https://github.com/user-attachments/assets/58dd21ad-87ab-430a-baf3-7112bb0fe416" />



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
