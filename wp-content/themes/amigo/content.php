<article> 
<div <?php post_class(); ?>> 
  <div class="home-thumbnail">
    <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'amigo' ), the_title_attribute( 'echo=0' ) ) ); ?>">                         
      <?php if ( has_post_thumbnail() ) : ?>                               
        <div class="featured-thumbnail"><?php the_post_thumbnail('amigo-slider'); ?><?php get_template_part('template-part', 'review'); ?></div>                                                           
        <?php endif; ?>
    </a>
    <div class="time-info">
        <div class="thetime"><?php the_time( __( 'j', 'amigo' ) ); ?></div>
        <div class="thedate"><?php the_time( __( 'M', 'amigo' ) ); ?></div>
        <div class="comments-meta"><i class="fa fa-comment"></i><?php comments_popup_link( __('0', 'amigo' ), __('1', 'amigo' ), __('%', 'amigo' ), 'comments-link', __('Off', 'amigo' ) );?></div> 
    </div>
  </div>
  <div class="home-header"> 
  <header>
  <h2 class="page-header">                                
    <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'amigo' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark">
      <?php the_title(); ?>
    </a>                            
  </h2> 
  <?php get_template_part('template-part', 'postmeta'); ?>
  </header>                                                      
  <div class="entry-summary">
		<?php $content = get_the_content();  echo wp_trim_words( $content , '65', $more = '...' ); ?> 
	</div><!-- .entry-summary --> 
  <div class="cat-list">
      <?php echo get_the_category_list();?>
  </div>                                                                                                                       
  <div class="clear"></div>
  <div class="info-reviews archive-reviews">
    <?php echo amigo_rating(get_the_ID(), 'wp_review_user_reviews', 'wp_review_user_review_type'); ?>
    <?php echo amigo_rating(get_the_ID()); ?>
  </div>
  <?php if ( has_post_format( 'video' )) {
      echo '<a href="' . get_post_format_link('video'). '"><i class="post-icon fa fa-play-circle-o"></i></a>';
    } elseif ( has_post_format( 'gallery' )) {
      echo '<a href="' . get_post_format_link('gallery'). '"><i class="post-icon fa fa-file-image-o"></i></a>';
    } 
  ?>                                                              
  </div>                      
</div>
<div class="clear"></div>
</article>
<?php if( $wp_query->current_post == 0 && is_active_sidebar( 'amigo-post-area' )) { ?>
  	<div class="first-textarea">
       <?php dynamic_sidebar( 'amigo-post-area' ); ?>
    </div> 
<?php } ?>