<?php if (get_theme_mod( 'left-sidebar-check', 0 ) != 0 ) : ?>
    <aside id="sidebar-secondary" class="rsrc-left col-md-<?php echo esc_attr(get_theme_mod( 'left-sidebar-size', 3 )); ?>" role="complementary">
        <?php dynamic_sidebar( 'amigo-left-sidebar' ); ?>
    </aside>
<?php endif; ?>