<?php if ( has_nav_menu( 'main_menu' ) ) : ?>
<?php if( is_front_page() || is_home() || is_404() ) { 
  $heading = 'h1';
  $desc = 'h2';
   } else { 
  $heading = 'h2';
  $desc = 'h3';
} ?> 
    <div class="row rsrc-top-menu" >
        <nav id="site-navigation" class="navbar navbar-inverse" role="navigation">     
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-1-collapse">
                        <span class="sr-only"><?php _e('Toggle navigation','amigo'); ?></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                <?php if ( get_theme_mod( 'header-logo', '' ) != '' ) : ?>
                    <div class="rsrc-header-img navbar-brand">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url(get_theme_mod('header-logo')); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" /></a>
                    </div>
                    <?php else : ?>
                    <div class="rsrc-header-text navbar-brand">
                        <<?php echo $heading ?> class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?></a></<?php echo $heading ?>>
                    </div>
                <?php endif; ?>  
                </div>
                <?php if ( get_theme_mod( 'amigo_socials', 0 ) == 1 ) : ?>
                  <div class="top-section nav navbar-nav navbar-right">
                      <?php if( get_theme_mod( 'amigo_socials', 0 ) == 1 ) { amigo_social_links(); } ?>                
                  </div>
                <?php endif; ?>  
                <?php
                wp_nav_menu( array(
                        'theme_location'    => 'main_menu',
                        'depth'             => 3,
                        'container'         => 'div', 
                        'container_class'   => 'collapse navbar-collapse navbar-1-collapse',
                        'menu_class'        => 'nav navbar-nav',
                        'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                        'walker'            => new wp_bootstrap_navwalker())
                );
                ?>
        </nav> 
    </div>
<?php endif; ?>
<?php //carousel section ?>
    <?php if ( get_theme_mod( 'carousel-categories', '' ) != '' && get_theme_mod( 'get-carousel', '0' ) != '0' && is_front_page() || get_theme_mod( 'carousel-categories', '' ) != '' && get_theme_mod( 'get-carousel', '0' ) != '0' && get_theme_mod( 'carousel-everywhere', 'home' ) == 'everywhere')   : ?>
      <?php get_template_part('template-part', 'carousel'); ?>
  <?php endif; ?>
</div>
<div class="container rsrc-container" role="main">

