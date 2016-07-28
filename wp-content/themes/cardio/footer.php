<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Cardio
 */
?>
   <div class="copyright-wrapper">
        	<div class="inner">
            	<div class="footer-menu">
                		<?php wp_nav_menu( array('theme_location'  => 'footer') ); ?>	
                </div><!-- footer-menu -->
                <div class="copyright">
                    	<p><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a> <?php echo date('Y'); ?> | <?php _e('All Rights Reserved.','bball_beta') ;?></p>               
                </div><!-- copyright --><div class="clear"></div>           
            </div><!-- inner -->
        </div>
    </div>
<?php wp_footer(); ?>
</body>
</html>