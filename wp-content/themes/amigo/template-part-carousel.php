<div id="carousel-home" class="flexslider row carousel-loading" data-slide="<?php get_theme_mod( 'carousel-auto-slide', true) ?>">										   
  <ul class="slides">										        									     
    <?php $get_featured_posts = new WP_Query( array(
            'posts_per_page'        => 8,
            'post_type'             => 'post',
            'category__in'          => get_theme_mod( 'carousel-categories')
         ) );
         while( $get_featured_posts->have_posts() ):$get_featured_posts->the_post();
         $review_total = get_post_meta( get_the_ID(), 'wp_review_total', true );             
    ?>                    	     
    <li class="carousel-item">                    	        
    <div class="flex-img">                    	           
      <a href="<?php the_permalink(); ?>">                                         
        <?php if ( has_post_thumbnail() ) { ?>        												           
          <div class="featured-thumbnail">
            <?php the_post_thumbnail('amigo-home'); ?>
            <div class="featured-thumbnail-inside">
              <?php the_post_thumbnail(array(300, 300)); ?>
              <?php if (function_exists('wp_review_show_total') && $review_total !='') { ?>
              <div class="thumnail-read-more review-carousel">
                 <div class="read-more-link"><?php if (function_exists('wp_review_show_total')) wp_review_show_total(); ?></div>
              </div>
              <?php } else { ?>                                       
              <div class="thumnail-read-more">
                 <div class="read-more-link"><?php _e( 'Read More', 'amigo' ) ?></div>
              </div>   									               
              <?php } ?>       
            </div>
            <div class="carousel-layer"></div>
          </div>      											         
        <?php } else { ?>                                       
          <img src="<?php echo get_template_directory_uri(); ?>/img/noprew-carousel.jpg" alt="<?php the_title(); ?>">       									               
        <?php } ?>
        <div class="info-reviews">
          <?php echo amigo_rating(get_the_ID(), 'wp_review_user_reviews', 'wp_review_user_review_type'); ?>
          <div class="clear"></div> 
          <?php echo amigo_rating(get_the_ID()); ?>
        </div>                                 
      </a>                                                                
    </div>                               
    <div class="flex-caption">		                     						         
      <div class="home-header">                      						                                                                                                     
              <header>
              <h2 class="page-header">                                
                <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'amigo' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark">
                  <?php the_title(); ?></a>                            
              </h2> 
              </header>
              <div class="entry-summary hidden-xs">
          		  <?php $content = get_the_content();  echo wp_trim_words( $content , '9', $more = '...' ); ?> 
          	  </div><!-- .entry-summary -->                                                                                                                                                                                                                                                                                                                                                                                      
              <div class="carousel-meta">
                <span class="fa fa-user"></span><span class="author-link car"><?php the_author_posts_link(); ?></span>
                <span class="fa fa-comment"></span><span class="comments-meta car"><?php comments_popup_link( __('0', 'amigo' ), __('1', 'amigo' ), __('%', 'amigo' ), 'comments-link', __('Off', 'amigo' ) );?></span>
              </div>
      </div>                                  
    </div>                            
    </li>  											     
    <?php endwhile; wp_reset_postdata(); ?>  										   
  </ul>									 
</div>
