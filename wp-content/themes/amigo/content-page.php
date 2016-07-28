<?php if (function_exists('amigo_breadcrumb') && get_theme_mod( 'breadcrumbs-check', 1 ) != 0) {amigo_breadcrumb(); } ?> 
<!-- start content container -->
<div class="row rsrc-content"> 
  <?php //left sidebar ?>    
  <?php get_sidebar( 'left' ); ?>    
   <article class="col-md-<?php amigo_main_content_width(); ?> rsrc-main">        
    <?php // theloop
            if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>                          
    <div <?php post_class('rsrc-post-content'); ?>>                            
      <div class="single-thumbnail featured-thumbnail"><?php echo the_post_thumbnail('amigo-slider'); ?><?php get_template_part('template-part', 'review'); ?></div>
      <header>                              
        <h1 class="entry-title page-header">
          <?php the_title() ;?>
        </h1>                                                        
      </header>                            
      <div class="entry-content">                              
        <?php the_content(); ?>                            
      </div>                               
      <?php wp_link_pages(); ?>
      <?php if( get_theme_mod( 'share-check', 1 ) == 1 ) : ?>
        <?php get_template_part('template-part', 'postshare'); ?>
      <?php endif; ?>                                                                                     
      <?php comments_template(); ?>                         
    </div>        
    <?php endwhile; ?>        
    <?php else: ?>            
    <?php get_404_template(); ?>        
    <?php endif; ?>    
  </article>    
  <?php //get the right sidebar ?>    
  <?php get_sidebar( 'right' ); ?>
</div>
<!-- end content container -->