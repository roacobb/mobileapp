<?php if (function_exists('wp_review_includes_libraries')) { ?>
  <?php    
    $like_count = get_post_meta( get_the_ID(), '_post_like_count', true ); 
    $review_total = get_post_meta( get_the_ID(), 'wp_review_total', true );
    if (empty($like_count)) $like_count = '0';
    if (function_exists('mts_get_post_reviews')) {
      $postReviews = mts_get_post_reviews( get_the_ID() );
    	$userTotal = $postReviews['rating'];
    } else {
      $userTotal = '';
    }                
  ?>    
  <div class="archive-layer"></div>
    <div class="archive-read-more">
      <?php if (function_exists('wp_review_show_total') && $review_total !='') { ?>
      <div class="archive-total">
        <div class="editor-total"><?php _e( 'Editor', 'amigo' ) ?></div>
        <?php if (function_exists('wp_review_show_total')) wp_review_show_total( ); ?>
      </div>
      <div class="archive-total users">
        <div class="editor-total"><?php _e( 'User', 'amigo' ) ?></div>
        <div class="archive-users-total"><?php if ($userTotal) { ?><div class="yet-rated"><?php if (function_exists('amigo_review_show_total')) amigo_review_show_total(); ?></div><?php } else { ?><div class="not-rated"><?php echo __( '-', 'amigo' ) ?></div><?php } ?></div>
      </div>
      <?php } ?>
      <div class="archive-more-link">
        <?php _e( 'Read More', 'amigo' ) ?>
      </div>              
    </div>
<?php } ?>    