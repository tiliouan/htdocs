<?php
/**
 * Footer template for POS Theme
 */
?>

<?php if (!is_pos_page()): ?>
<footer class="pos-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?> - POS System</p>
    </div>
</footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
