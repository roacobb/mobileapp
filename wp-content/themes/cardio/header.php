 <?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Cardio
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
		<div id="header">
            <div class="container">	
                   	<div class="logo">
							<?php cardio_the_custom_logo(); ?>
						<h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>

					<?php $description = get_bloginfo( 'description', 'display' );
					if ( $description || is_customize_preview() ) : ?>
						<p><?php echo $description; ?></p>
					<?php endif; ?>
						</div>		
						<div class="toggle">
							<a class="toggleMenu" href="#"><?php _e('Menu','cardio'); ?></a>
						</div> 						
						<div class="main-nav">
							<?php wp_nav_menu( array('theme_location'  => 'primary') ); ?>							
						</div>						
				<div class="clear"></div>
            </div><!--container-->               
		</div><!-- header -->	
			
    <?php if ( is_front_page() && !is_home() ) { ?>
	<?php $hideslide = get_theme_mod('hide_slider', '1'); ?>
		<?php if($hideslide == ''){ ?>
                <!-- Slider Section -->
                <?php for($sld=7; $sld<10; $sld++) { ?>
                	<?php if( get_theme_mod('page-setting'.$sld)) { ?>
                	<?php $slidequery = new WP_query('page_id='.get_theme_mod('page-setting'.$sld,true)); ?>
                	<?php while( $slidequery->have_posts() ) : $slidequery->the_post();
                			$image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
                			$img_arr[] = $image;
               				$id_arr[] = $post->ID;
                		endwhile;
                	}
                }
                ?>
<?php if(!empty($id_arr)){ ?>
        <div id="slider" class="nivoSlider">
            <?php 
            $i=1;
            foreach($img_arr as $url){ ?>
            <?php if(!empty($url)){ ?>
            <img src="<?php echo $url; ?>" title="#slidecaption<?php echo $i; ?>" />
            <?php }else{ ?>
            <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/default-slide.jpg" title="#slidecaption<?php echo $i; ?>" />
            <?php } ?>
            <?php $i++; }  ?>
        </div>   
<?php 
	$i=1;
		foreach($id_arr as $id){ 
		$title = get_the_title( $id ); 
		$post = get_post($id); 
		$content = apply_filters('the_content', substr(strip_tags($post->post_content), 0, 150)); 
?>                 
<div id="slidecaption<?php echo $i; ?>" class="nivo-html-caption">
    <div class="top-bar">
    	<h2><?php echo $title; ?></h2>
    	<?php echo $content; ?>
    	<a class="read-more" href="<?php the_permalink(); ?>"><?php echo get_theme_mod('slidelink_text',__('Read More','cardio')); ?></a>
    </div>
</div>      
    <?php $i++; } ?>       
     </div>
<div class="clear"></div>        
</section>
<?php } else { ?>
<div class="slider-wrapper theme-default">
    <div id="slider" class="nivoSlider">
    <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/slider1.jpg" alt="" title="#slidecaption1" />
    <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/slider2.jpg" alt="" title="#slidecaption2" />
    <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/slider3.jpg" alt="" title="#slidecaption3" />
    </div>                    
      <div id="slidecaption1" class="nivo-html-caption">
        <div class="top-bar">
                <h2><?php esc_html_e('Welcome to Cardio.','cardio'); ?></h2>
                <p><?php esc_html_e('For setup slider go to Appearace >> Customize >> Slider Settings >> Here you can select the pages for slider.','cardio'); ?></p>
        </div>
        </div>
        
        <div id="slidecaption2" class="nivo-html-caption">
            <div class="top-bar">
                   <h2><?php esc_html_e('Hustle to Gain More Muscle.','cardio'); ?></h2>
                <p><?php esc_html_e('For setup slider go to Appearace >> Customize >> Slider Settings >> Here you can select the pages for slider.','cardio'); ?></p>
            </div>
        </div>
        
        <div id="slidecaption3" class="nivo-html-caption">
            <div class="top-bar">
                    <h2><?php esc_html_e('We provide 24x7 Support.','cardio'); ?></h2>
                <p><?php esc_html_e('For setup slider go to Appearace >> Customize >> Slider Settings >> Here you can select the pages for slider.','cardio'); ?></p>
            </div>
        </div>
<div class="clear"></div>
<!-- Slider Section -->
<?php } } } ?>

      <div class="main-container"> 
      		<?php if(is_front_page() && !is_home()) { ?>
            		<div class="container">
                    		<div class="welcome">
                            <?php $hidetitle = get_theme_mod('hide_title', '1'); ?>
                            	<?php if($hidetitle != '') { ?>
                                  <h2 class="section-title"><?php echo get_theme_mod('welcome_title',__('Welcome to Fitnesstime','cardio')); ?></h2>
                                        <p><em><?php echo get_theme_mod('welcome_subtitle',__('Take care of your body. Its the only place you have to live.','cardio')); ?></em></p>
                                        <?php } ?>
                                        
             <?php $hidebox = get_theme_mod('box_hide', '1'); ?>  
             <?php if($hidebox == '') { ?>                        
        	<?php for($f=1; $f<5; $f++) { ?>
         <?php if(get_theme_mod('page-setting'.$f) != '') { ?>
         	<?php $page_query = new WP_Query('page_id='.get_theme_mod('page-setting'.$f)); ?>
         		<?php while( $page_query->have_posts() ) : $page_query->the_post(); ?>
                      <div class="welcome-box <?php if( $f%4==0) { ?>wellast<?php } ?>">
                      		<?php 	$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
									$url = $thumb['0'];
							?>
                            	<div class="welcome-icon">
                                <img src="<?php if(has_post_thumbnail()) { echo $url; } else { echo esc_url(get_template_directory_uri().'/images/no-image.jpg'); } ?>">
                                </div>
                                <h3><a href="#"><?php the_title(); ?></a></h3>				
                                <p><?php the_excerpt(); ?></p>				
                      </div><?php if( $f%4==0) { ?><div class="clear"></div><?php } ?> 
                 <?php endwhile; ?>
         <?php } else { ?>
         		<div class="welcome-box <?php if( $f%4==0) { ?>wellast<?php } ?> ">
                		<div class="welcome-icon">
							<img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpg">
                        </div>
						<h3><?php esc_html_e('Page Title','cardio'); ?> <?php echo $f; ?></h3>				
						<p><?php echo esc_html_e('For setup page boxes go to Appearance >> Customize >> Welcome Section >> here you can select the pages form the dropdown.','cardio') ;?></p>				
				</div><?php if( $f%4==0) { ?><div class="clear"></div><?php } ?> 
                <?php  } } } ?>
                     </div><!-- welcome -->
                   <div class="clear"></div>
              </div>
          <?php } ?>
          
         <?php if( function_exists('is_woocommerce') && is_woocommerce() ) { ?>
		 	<div class="content-area">
                <div class="middle-align content_sidebar">
                	<div id="sitemain" class="site-main">
         <?php } ?>