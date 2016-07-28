<?php
if ( function_exists( 'amigo_breadcrumb' ) && get_theme_mod( 'breadcrumbs-check', 1 ) != 0 ) {
	amigo_breadcrumb();
}
?> 
<!-- start content container -->
<div class="row rsrc-content">

	<?php //left sidebar   ?>    
	<?php get_sidebar( 'left' ); ?>    
		<article class="col-md-<?php amigo_main_content_width(); ?> rsrc-main">        
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>                         
				<div <?php post_class( 'rsrc-post-content' ); ?>>                            
					<div class="home-thumbnail">
						<div class="single-thumbnail featured-thumbnail"><?php echo the_post_thumbnail( 'amigo-slider' ); ?><?php get_template_part( 'template-part', 'review' ); ?></div>
						<div class="time-info">
							<div class="thetime"><?php the_time( __( 'j', 'amigo' ) ); ?></div>
							<div class="thedate"><?php the_time( __( 'M', 'amigo' ) ); ?></div>
							<div class="comments-meta"><i class="fa fa-comment"></i><?php comments_popup_link( __( '0', 'amigo' ), __( '1', 'amigo' ), __( '%', 'amigo' ), 'comments-link', __( 'Off', 'amigo' ) ); ?></div> 
						</div> 
					</div>   
					<header>                              
						<h1 class="entry-title page-header">
						<?php the_title(); ?>
						</h1>                              
						<?php get_template_part( 'template-part', 'postmeta' ); ?>                            
					</header>                                                                                     
					<div class="entry-content">
					<?php the_content(); ?>
					</div>    
					<div id="custom-box"></div>                         
						<?php wp_link_pages(); ?>
					<div class="cat-list">
					<?php echo get_the_category_list(); ?>
					</div>  
					<?php get_template_part( 'template-part', 'posttags' ); ?>
					<?php if ( get_theme_mod( 'post-nav-check', 1 ) == 1 ) : ?>                            
						<div class="post-navigation row">
							<div class="post-previous col-xs-6"><?php previous_post_link( '%link', '<span class="meta-nav">' . __( 'Previous:', 'amigo' ) . '</span> %title' ); ?></div>
							<div class="post-next col-xs-6"><?php next_post_link( '%link', '<span class="meta-nav">' . __( 'Next:', 'amigo' ) . '</span> %title' ); ?></div>
						</div>                             
					<?php endif; ?>                            
					<?php if ( get_theme_mod( 'related-posts-check', 1 ) == 1 ) : ?>
						<?php get_template_part( 'template-part', 'related' ); ?>
					<?php endif; ?>
					<?php if ( get_theme_mod( 'author-check', 1 ) == 1 ) : ?>                               
						<?php get_template_part( 'template-part', 'postauthor' ); ?> 
					<?php endif; ?>                              
					<?php comments_template(); ?>                          
				</div>        
			<?php endwhile; ?>        
		<?php else: ?>            
		<?php get_404_template(); ?>        
	<?php endif; ?>    
	</article>    
<?php //get the right sidebar   ?>    
<?php get_sidebar( 'right' ); ?>
</div>
<!-- end content container -->