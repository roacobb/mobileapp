<?php if (get_theme_mod( 'rigth-sidebar-check', 1 ) != 0 ) : ?>
    <aside id="sidebar" class="rsrc-right col-md-<?php echo esc_attr(get_theme_mod( 'right-sidebar-size', 3 )); ?>" role="complementary">
        <?php //get the right sidebar
        dynamic_sidebar( 'amigo-right-sidebar' ); ?>
    </aside>
<?php endif; ?>