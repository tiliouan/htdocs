<?php
/**
 * Custom POS Page Template
 * 
 * This template is specifically designed for the YITH POS plugin
 * Template Name: POS Page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in and has POS permissions
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

// Check POS permissions
if (!current_user_can('yith_pos_cashier') && !current_user_can('yith_pos_manager') && !current_user_can('manage_options')) {
    wp_die(__('You do not have permission to access the POS system.', 'pos-theme'));
}

// Remove admin bar for POS
show_admin_bar(false);

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#667eea">
    <title><?php _e('Point of Sale', 'pos-theme'); ?> - <?php bloginfo('name'); ?></title>
    
    <?php
    // Load POS specific styles and scripts
    if (function_exists('yith_pos_head')) {
        yith_pos_head();
    } else {
        wp_head();
    }
    ?>
    
    <style>
        /* Ensure full-screen POS interface */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background: #f8f9fa;
        }
        
        #yith-pos-root {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
            background: #fff;
        }
        
        /* Hide any WordPress elements that might interfere */
        .admin-bar body {
            margin-top: 0 !important;
        }
        
        #wpadminbar {
            display: none !important;
        }
        
        .update-nag,
        .notice,
        .error {
            display: none !important;
        }
    </style>
</head>

<body <?php echo function_exists('yith_pos_body_class') ? 'yith_pos_body_class()' : 'body_class()'; ?>>

<?php
// Check if POS plugin is active and functioning
if (function_exists('yith_pos') && function_exists('yith_pos_register_logged_in')) {
    
    $logged_in = is_user_logged_in();
    $register_id = 0;
    $user_editing = false;
    
    if ($logged_in) {
        $register_id = yith_pos_register_logged_in();
        
        if (!function_exists('yith_pos_can_view_register') || !yith_pos_can_view_register()) {
            $register_id = isset($_REQUEST['register']) ? absint($_REQUEST['register']) : $register_id;
            $user_editing = isset($_REQUEST['user-editing']) ? absint($_REQUEST['user-editing']) : 
                           (function_exists('yith_pos_check_register_lock') ? yith_pos_check_register_lock($register_id) : false);
            
            if ($register_id && $user_editing && function_exists('yith_pos_register_logout')) {
                yith_pos_register_logout();
            }
        }
    }
    
    // Display appropriate POS content
    if (!$logged_in) {
        // Show login form
        if (file_exists(YITH_POS_TEMPLATE_PATH . 'yith-pos-login.php')) {
            wc_get_template('yith-pos-login.php', array(), '', YITH_POS_TEMPLATE_PATH);
        } else {
            echo '<div style="display: flex; align-items: center; justify-content: center; height: 100vh; font-family: Arial, sans-serif;">';
            echo '<div style="text-align: center; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">';
            echo '<h2>POS Login Required</h2>';
            echo '<p>Please login to access the Point of Sale system.</p>';
            echo '<a href="' . esc_url(wp_login_url()) . '" style="display: inline-block; padding: 10px 20px; background: #667eea; color: #fff; text-decoration: none; border-radius: 4px;">Login</a>';
            echo '</div></div>';
        }
    } else {
        // Check if user can view register
        if (function_exists('yith_pos_can_view_register') && yith_pos_can_view_register()) {
            // Show main POS interface
            echo '<div id="yith-pos-root" data-no-support="' . esc_attr__('You are using an outdated browser; please update your browser or use a new generation web browser!', 'yith-point-of-sale-for-woocommerce') . '"></div>';
        } else {
            // Show store/register selection
            if (file_exists(YITH_POS_TEMPLATE_PATH . 'yith-pos-store-register.php')) {
                wc_get_template('yith-pos-store-register.php', compact('register_id', 'user_editing'), '', YITH_POS_TEMPLATE_PATH);
            } else {
                echo '<div style="display: flex; align-items: center; justify-content: center; height: 100vh; font-family: Arial, sans-serif;">';
                echo '<div style="text-align: center; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">';
                echo '<h2>Select Store and Register</h2>';
                echo '<p>Please select a store and register to continue.</p>';
                echo '</div></div>';
            }
        }
    }
    
} else {
    // POS plugin not active or not functioning
    echo '<div style="display: flex; align-items: center; justify-content: center; height: 100vh; font-family: Arial, sans-serif;">';
    echo '<div style="text-align: center; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">';
    echo '<h2 style="color: #dc3545;">POS System Error</h2>';
    echo '<p>The Point of Sale plugin is not active or not properly configured.</p>';
    echo '<p>Please contact your administrator.</p>';
    echo '</div></div>';
}

// Footer actions
if (function_exists('yith_pos_footer')) {
    yith_pos_footer();
} else {
    wp_footer();
}
?>

</body>
</html>
