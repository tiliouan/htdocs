<?php
/**
 * 404 Error Page Template for POS Theme
 */

get_header(); ?>

<div class="pos-container">
    <div class="pos-error">
        <div class="pos-error-content">
            <h1><?php _e('Page Not Found', 'pos-theme'); ?></h1>
            <p><?php _e('The page you are looking for could not be found.', 'pos-theme'); ?></p>
            
            <?php if (is_user_logged_in() && (current_user_can('yith_pos_cashier') || current_user_can('yith_pos_manager'))): ?>
                <a href="<?php echo esc_url(function_exists('yith_pos_get_pos_page_url') ? yith_pos_get_pos_page_url() : home_url()); ?>" class="pos-button">
                    <?php _e('Go to POS', 'pos-theme'); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo esc_url(home_url()); ?>" class="pos-button">
                    <?php _e('Go Home', 'pos-theme'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
