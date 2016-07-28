<div class="postauthor-container">			  
  <div class="postauthor-title">				  
  <div class="about col-sm-4"><?php _e('About The Author', 'amigo'); ?></div>
  <div class="vcard col-sm-8">
      <span class="fn">
        <?php the_author_posts_link(); ?>
      </span>
  </div> 				
  </div>        	
  <div class="postauthor-content">	             
    <?php if(function_exists('get_avatar')) { echo get_avatar( get_the_author_meta('email'), '80' );  } ?> 						           
    <p>
      <?php the_author_meta('description') ?>
    </p>					
  </div>	 		
</div>