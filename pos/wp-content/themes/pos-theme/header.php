<?php
/**
 * Header template for POS Theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if (!is_pos_page()): ?>
<header class="pos-header show">
    <div class="container">
        <div class="pos-logo">
            <?php
            $logo = get_theme_mod('pos_theme_logo');
            if ($logo) {
                echo '<img src="' . esc_url($logo) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
            } else {
                echo esc_html(get_bloginfo('name'));
            }
            ?>
        </div>
        
        <nav class="pos-nav">
            <?php if (is_user_logged_in()): ?>
                <span><?php printf(__('Welcome, %s', 'pos-theme'), wp_get_current_user()->display_name); ?></span>
                <a href="<?php echo esc_url(wp_logout_url()); ?>" class="pos-button">
                    <?php _e('Logout', 'pos-theme'); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo esc_url(wp_login_url()); ?>" class="pos-button">
                    <?php _e('Login', 'pos-theme'); ?>
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php endif; ?>
