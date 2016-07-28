<div class="post-meta row">
  <div class="author-meta col-xs-12">
      <div class="author-avatar">
        <?php echo get_avatar( get_the_author_meta( 'ID' ), 60 ); ?> 
      </div>
      <div class="author-link">
        <?php the_author_posts_link(); ?>
      </div>
  </div>
</div>