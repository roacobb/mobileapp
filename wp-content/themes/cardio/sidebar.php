<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Cardio
 */
?>
<?php if( function_exists('is_woocommerce') && is_woocommerce() ) { ?>
		</div>
<?php } ?>
<div id="sidebar">
    
    <?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>
        <?php
			the_widget( 'WP_Widget_Meta');
		 ?>
    <?php endif; // end sidebar widget area ?>
	
</div><!-- sidebar -->

<?php if( function_exists('is_woocommerce') && is_woocommerce() ) { ?>
		</div>
	</div>
    <div class="clear"></div>
<?php } ?>
