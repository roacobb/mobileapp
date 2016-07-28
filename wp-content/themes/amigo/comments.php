<?php if ( is_single() || is_page() ) : ?>
    <div class="clear"></div>
    <div class="rsrc-comments">
    <a name="comments"></a>
    <?php if ( have_comments() && comments_open() ) : ?>
        <h4 id="comments"><?php comments_number(__('Leave a Comment','amigo'), __('One Comment','amigo'), '%' . __(' Comments','amigo') );?></h4>
            <ul class="commentlist list-unstyled">
                <?php wp_list_comments(); ?>
                <?php paginate_comments_links(); ?>
                <?php if ( is_singular() ) wp_enqueue_script( "comment-reply" ); ?>
            </ul>
            <div class="well"><?php comment_form(); ?></div>
    <?php else :
            if ( comments_open() ) : ?>
            <div class="no-comments text-center">
              <p class="fa fa-comments-o comment-icon"></p>
              <h4><?php _e('There are no comments yet','amigo');?></h4>
              <h4><?php _e('Why not be the first','amigo');?></h4>
            </div>
                <div class="well"><?php comment_form(); ?></div>
           <?php
            endif;
    endif; ?>
    </div>
<?php endif; ?>