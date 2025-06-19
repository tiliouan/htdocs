<?php
/**
 * The main template file for POS Theme
 * 
 * This template is used when no specific template matches the query.
 * For POS pages, this will typically redirect to the POS interface.
 */

get_header(); ?>

<div class="pos-container">
    <div class="pos-content">
        
        <?php if (is_pos_page() || function_exists('yith_pos')): ?>
            <!-- POS Interface will be loaded by the plugin -->
            <?php
            // Check if we're on the actual POS page
            if (function_exists('yith_pos_get_pos_page_id') && is_page(yith_pos_get_pos_page_id())) {
                // Let the POS plugin handle the content
                the_content();
            } else {
                // Redirect to POS page if user has permissions
                if (is_user_logged_in() && (current_user_can('yith_pos_cashier') || current_user_can('yith_pos_manager'))) {
                    if (function_exists('yith_pos_get_pos_page_url')) {
                        wp_redirect(yith_pos_get_pos_page_url());
                        exit;
                    }
                }
            }
            ?>
            
        <?php else: ?>
            <!-- Fallback content for non-POS pages -->
            <div class="pos-error">
                <div class="pos-error-content">
                    <h1><?php _e('POS System', 'pos-theme'); ?></h1>
                    <p><?php _e('This is a Point of Sale system. Please contact your administrator for access.', 'pos-theme'); ?></p>
                    
                    <?php if (!is_user_logged_in()): ?>
                        <a href="<?php echo esc_url(wp_login_url()); ?>" class="pos-button">
                            <?php _e('Login', 'pos-theme'); ?>
                        </a>
                    <?php else: ?>
                        <p><?php _e('You do not have permission to access the POS system.', 'pos-theme'); ?></p>
                        <a href="<?php echo esc_url(wp_logout_url()); ?>" class="pos-button">
                            <?php _e('Logout', 'pos-theme'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php get_footer(); ?>
