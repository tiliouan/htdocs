<?php
/**
 * POS Theme Functions
 * 
 * A minimal theme designed specifically for YITH Point of Sale
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function pos_theme_setup() {
    // Add theme support for title tag
    add_theme_support('title-tag');
    
    // Add theme support for HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Add theme support for custom logo
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ));
}
add_action('after_setup_theme', 'pos_theme_setup');

/**
 * Enqueue scripts and styles
 */
function pos_theme_scripts() {
    // Main theme stylesheet
    wp_enqueue_style('pos-theme-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // POS specific styles
    if (is_pos_page()) {
        wp_enqueue_style('pos-theme-pos', get_template_directory_uri() . '/assets/pos.css', array(), '1.0.0');
    }
    
    // Enqueue enhancement styles
    wp_enqueue_style('pos-theme-enhancements', get_template_directory_uri() . '/assets/enhancements.css', array('pos-theme-style'), '1.0.0');
}
add_action('wp_enqueue_scripts', 'pos_theme_scripts');

/**
 * Check if current page is POS related
 */
function is_pos_page() {
    global $post;
    
    // Check if we're on the POS page
    if ($post && has_blocks($post->post_content)) {
        return false;
    }
    
    // Check if this is the POS page by template
    if (is_page_template('yith-pos-page.php')) {
        return true;
    }
    
    // Check if we have POS body class
    if (function_exists('yith_pos_get_body_classes')) {
        $body_classes = yith_pos_get_body_classes();
        return in_array('yith-pos-page', $body_classes);
    }
    
    // Check for POS page option
    if (function_exists('yith_pos_get_pos_page_id')) {
        $pos_page_id = yith_pos_get_pos_page_id();
        return is_page($pos_page_id);
    }
    
    return false;
}

/**
 * Remove admin bar for POS users
 */
function pos_theme_remove_admin_bar() {
    if (is_pos_page() || (isset($_GET['page']) && $_GET['page'] === 'pos')) {
        show_admin_bar(false);
    }
}
add_action('init', 'pos_theme_remove_admin_bar');

/**
 * Add body classes for POS pages
 */
function pos_theme_body_classes($classes) {
    if (is_pos_page()) {
        $classes[] = 'pos-only-mode';
        $classes[] = 'pos-theme-active';
    }
    return $classes;
}
add_filter('body_class', 'pos_theme_body_classes');

/**
 * Remove unnecessary WordPress elements from POS pages
 */
function pos_theme_clean_pos_page() {
    if (is_pos_page()) {
        // Remove WordPress generator
        remove_action('wp_head', 'wp_generator');
        
        // Remove emoji scripts
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        
        // Remove unnecessary scripts
        wp_dequeue_script('wp-embed');
        wp_deregister_script('wp-embed');
    }
}
add_action('init', 'pos_theme_clean_pos_page');

/**
 * Custom template hierarchy for POS
 */
function pos_theme_template_hierarchy($template) {
    if (is_pos_page()) {
        // Check if YITH POS plugin has its own template
        if (function_exists('yith_pos_get_pos_page_template')) {
            $pos_template = yith_pos_get_pos_page_template();
            if ($pos_template && file_exists($pos_template)) {
                return $pos_template;
            }
        }
        
        // Use theme's POS template if available
        $theme_pos_template = get_template_directory() . '/pos-page.php';
        if (file_exists($theme_pos_template)) {
            return $theme_pos_template;
        }
    }
    
    return $template;
}
add_filter('template_include', 'pos_theme_template_hierarchy', 99);

/**
 * Hide admin notices on POS pages
 */
function pos_theme_hide_admin_notices() {
    if (is_pos_page()) {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}
add_action('init', 'pos_theme_hide_admin_notices');

/**
 * Disable WordPress updates and maintenance mode for POS users
 */
function pos_theme_disable_updates_for_pos() {
    if (is_pos_page() || (is_user_logged_in() && current_user_can('yith_pos_cashier'))) {
        // Disable automatic updates
        add_filter('automatic_updater_disabled', '__return_true');
        
        // Disable maintenance mode
        add_filter('enable_maintenance_mode', '__return_false');
    }
}
add_action('init', 'pos_theme_disable_updates_for_pos');

/**
 * Add POS-specific meta tags
 */
function pos_theme_pos_meta_tags() {
    if (is_pos_page()) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
        echo '<meta name="theme-color" content="#667eea">' . "\n";
    }
}
add_action('wp_head', 'pos_theme_pos_meta_tags', 1);

/**
 * Redirect non-POS pages to login or error page
 */
function pos_theme_redirect_non_pos() {
    // Only redirect if we're not on admin pages or POS pages
    if (!is_admin() && !is_pos_page() && !is_login_page()) {
        // Check if user has POS permissions
        if (is_user_logged_in() && (current_user_can('yith_pos_cashier') || current_user_can('yith_pos_manager'))) {
            // Redirect to POS page
            if (function_exists('yith_pos_get_pos_page_url')) {
                wp_redirect(yith_pos_get_pos_page_url());
                exit;
            }
        } elseif (!is_user_logged_in()) {
            // Redirect to login
            wp_redirect(wp_login_url());
            exit;
        } else {
            // Show access denied
            wp_die(__('Access denied. You need POS permissions to access this system.', 'pos-theme'));
        }
    }
}
// Uncomment the next line if you want to enforce POS-only access
// add_action('template_redirect', 'pos_theme_redirect_non_pos');

/**
 * Check if current page is login page
 */
function is_login_page() {
    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
}

/**
 * Add theme options for POS customization
 */
function pos_theme_customizer($wp_customize) {
    // Add POS section
    $wp_customize->add_section('pos_theme_options', array(
        'title'    => __('POS Theme Options', 'pos-theme'),
        'priority' => 30,
    ));
    
    // Logo setting
    $wp_customize->add_setting('pos_theme_logo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'pos_theme_logo', array(
        'label'    => __('POS Logo', 'pos-theme'),
        'section'  => 'pos_theme_options',
        'settings' => 'pos_theme_logo',
    )));
    
    // Primary Color
    $wp_customize->add_setting('pos_theme_primary_color', array(
        'default'           => '#667eea',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'pos_theme_primary_color', array(
        'label'    => __('Primary Color', 'pos-theme'),
        'section'  => 'pos_theme_options',
        'settings' => 'pos_theme_primary_color',
    )));
}
add_action('customize_register', 'pos_theme_customizer');

/**
 * Output custom CSS for customizer options
 */
function pos_theme_custom_css() {
    $primary_color = get_theme_mod('pos_theme_primary_color', '#667eea');
    
    if ($primary_color !== '#667eea') {
        echo '<style type="text/css">';
        echo ':root { --pos-primary-color: ' . esc_attr($primary_color) . '; }';
        echo '.pos-button, .pos-logo { background-color: ' . esc_attr($primary_color) . '; }';
        echo '</style>';
    }
}
add_action('wp_head', 'pos_theme_custom_css');

/**
 * Additional security measures for POS environment
 */
function pos_theme_security_headers() {
    if (is_pos_page()) {
        // Prevent caching of POS pages
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Security headers
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
add_action('send_headers', 'pos_theme_security_headers');

/**
 * Disable file editing in WordPress admin for POS users
 */
function pos_theme_disable_file_editing() {
    if (is_user_logged_in() && (current_user_can('yith_pos_cashier') || current_user_can('yith_pos_manager'))) {
        if (!current_user_can('manage_options')) {
            if (!defined('DISALLOW_FILE_EDIT')) {
                define('DISALLOW_FILE_EDIT', true);
            }
        }
    }
}
add_action('init', 'pos_theme_disable_file_editing');

/**
 * Remove unnecessary WordPress features for POS users
 */
function pos_theme_remove_wp_features() {
    if (is_pos_page() || (is_user_logged_in() && current_user_can('yith_pos_cashier'))) {
        // Remove WordPress version from head
        remove_action('wp_head', 'wp_generator');
        
        // Remove RSD link
        remove_action('wp_head', 'rsd_link');
        
        // Remove Windows live writer
        remove_action('wp_head', 'wlwmanifest_link');
        
        // Remove shortlink
        remove_action('wp_head', 'wp_shortlink_wp_head');
        
        // Remove feed links
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
        
        // Remove REST API links
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
    }
}
add_action('init', 'pos_theme_remove_wp_features');

/**
 * Custom login redirect for POS users
 */
function pos_theme_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        // Check if user has POS capabilities
        if (in_array('yith_pos_cashier', $user->roles) || in_array('yith_pos_manager', $user->roles)) {
            if (function_exists('yith_pos_get_pos_page_url')) {
                return yith_pos_get_pos_page_url();
            }
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'pos_theme_login_redirect', 10, 3);

/**
 * Custom logout redirect for POS users
 */
function pos_theme_logout_redirect() {
    if (function_exists('yith_pos_get_pos_page_url')) {
        wp_redirect(yith_pos_get_pos_page_url());
        exit;
    }
}
add_action('wp_logout', 'pos_theme_logout_redirect');

/**
 * Add POS-specific body classes
 */
function pos_theme_additional_body_classes($classes) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (in_array('yith_pos_cashier', $user->roles)) {
            $classes[] = 'pos-cashier';
        }
        if (in_array('yith_pos_manager', $user->roles)) {
            $classes[] = 'pos-manager';
        }
    }
    
    // Add mobile detection
    if (wp_is_mobile()) {
        $classes[] = 'pos-mobile';
    }
    
    return $classes;
}
add_filter('body_class', 'pos_theme_additional_body_classes');

/**
 * Disable comments for POS theme
 */
function pos_theme_disable_comments() {
    // Close comments on the frontend
    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);
    
    // Hide existing comments
    add_filter('comments_array', '__return_empty_array', 10, 2);
    
    // Remove comments page in menu
    add_action('admin_menu', function() {
        remove_menu_page('edit-comments.php');
    });
    
    // Remove comments links from admin bar
    add_action('init', function() {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    });
}
add_action('init', 'pos_theme_disable_comments');

/**
 * Add theme support for POS features
 */
function pos_theme_pos_support() {
    // Add support for custom background
    add_theme_support('custom-background', array(
        'default-color' => 'f8f9fa',
    ));
    
    // Add support for custom header
    add_theme_support('custom-header', array(
        'height' => 60,
        'width' => 200,
        'flex-height' => true,
        'flex-width' => true,
    ));
    
    // Add support for post thumbnails
    add_theme_support('post-thumbnails');
    
    // Add support for automatic feed links
    add_theme_support('automatic-feed-links');
}
add_action('after_setup_theme', 'pos_theme_pos_support');

/**
 * Enqueue additional scripts for POS pages
 */
function pos_theme_pos_scripts() {
    if (is_pos_page()) {
        // Add custom POS JavaScript if needed
        wp_enqueue_script('pos-theme-pos-js', get_template_directory_uri() . '/assets/pos.js', array('jquery'), '1.0.0', true);
        
        // Localize script with POS data
        wp_localize_script('pos-theme-pos-js', 'posTheme', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pos_theme_nonce'),
            'isTouch' => wp_is_mobile() ? 'true' : 'false',
            'strings' => array(
                'loading' => __('Loading...', 'pos-theme'),
                'error' => __('An error occurred', 'pos-theme'),
            )
        ));
    }
}
add_action('wp_enqueue_scripts', 'pos_theme_pos_scripts', 20);

/**
 * Add custom CSS variables for theming
 */
function pos_theme_css_variables() {
    $primary_color = get_theme_mod('pos_theme_primary_color', '#667eea');
    $logo = get_theme_mod('pos_theme_logo');
    
    echo '<style type="text/css">:root {';
    echo '--pos-primary-color: ' . esc_attr($primary_color) . ';';
    echo '--pos-primary-rgb: ' . implode(',', sscanf($primary_color, "#%02x%02x%02x")) . ';';
    
    if ($logo) {
        echo '--pos-logo-url: url(' . esc_url($logo) . ');';
    }
    
    echo '}</style>';
}
add_action('wp_head', 'pos_theme_css_variables', 5);

/**
 * Handle POS theme AJAX requests
 */
function pos_theme_ajax_handler() {
    check_ajax_referer('pos_theme_nonce', 'nonce');
    
    $action = sanitize_text_field($_POST['pos_action']);
    
    switch ($action) {
        case 'check_status':
            wp_send_json_success(array('status' => 'ok'));
            break;
            
        default:
            wp_send_json_error('Invalid action');
    }
}
add_action('wp_ajax_pos_theme_action', 'pos_theme_ajax_handler');
add_action('wp_ajax_nopriv_pos_theme_action', 'pos_theme_ajax_handler');

/**
 * Add keyboard shortcuts support
 */
function pos_theme_keyboard_shortcuts() {
    if (is_pos_page()) {
        ?>
        <script>
        document.addEventListener('keydown', function(e) {
            // ESC key - close dialogs or logout
            if (e.key === 'Escape') {
                // Let POS plugin handle this
                return;
            }
            
            // F11 - toggle fullscreen
            if (e.key === 'F11') {
                e.preventDefault();
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    document.documentElement.requestFullscreen();
                }
            }
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'pos_theme_keyboard_shortcuts');
