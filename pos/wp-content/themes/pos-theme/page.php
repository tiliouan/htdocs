<?php
/**
 * Template for displaying single pages
 */

get_header(); ?>

<div class="pos-container">
    <div class="pos-content">
        
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                
                <?php if (is_pos_page()): ?>
                    <!-- POS Page Content -->
                    <div class="pos-page-content">
                        <?php the_content(); ?>
                    </div>
                <?php else: ?>
                    <!-- Regular Page Content -->
                    <div class="pos-regular-page">
                        <div class="pos-page-header">
                            <h1><?php the_title(); ?></h1>
                        </div>
                        <div class="pos-page-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php endwhile; ?>
        <?php else : ?>
            
            <div class="pos-error">
                <div class="pos-error-content">
                    <h1><?php _e('Page Not Found', 'pos-theme'); ?></h1>
                    <p><?php _e('Sorry, but the page you were trying to view does not exist.', 'pos-theme'); ?></p>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</div>

<?php get_footer(); ?>
