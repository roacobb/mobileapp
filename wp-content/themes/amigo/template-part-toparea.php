<?php if( is_active_sidebar( 'amigo-before-content' ) ) { ?>
  <div id="content-top-section" class="clearfix">
    <?php
    	if ( !dynamic_sidebar( 'amigo-before-content' ) ):
      endif;
    ?>
  </div>
<?php } ?>