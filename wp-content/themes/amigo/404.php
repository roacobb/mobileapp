<?php get_header(); ?>

<?php get_template_part('template-part', 'head'); ?>

<?php get_template_part('template-part', 'topnav'); ?>

    <!-- start content container -->
    <div class="row rsrc-content">

        <?php //left sidebar ?>
        <?php get_sidebar( 'left' ); ?>

        <div class="col-md-<?php amigo_main_content_width(); ?> rsrc-main">
          <div class="rsrc-post-content">
            <div class="error-template text-center">
                <h1><?php _e('Oops!', 'amigo'); ?></h1>
                <h2><?php _e('404 Not Found', 'amigo'); ?></h2>
                <p class="error-details">
                    <?php _e('Sorry, an error has occured, Requested page not found!', 'amigo'); ?>
                </p>
                <div class="error-actions">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-info"><span class="fa fa-home"></span><?php _e(' Take Me Home', 'amigo'); ?></a>    
                </div>
            </div>
          </div>
        </div>

        <?php //get the right sidebar ?>
        <?php get_sidebar( 'right' ); ?>

    </div>
    <!-- end content container -->

<?php get_footer(); ?>