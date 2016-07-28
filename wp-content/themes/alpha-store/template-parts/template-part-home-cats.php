</div>
</div>
<?php $image	 = get_post_meta( get_the_ID(), 'alpha_store_image', true ); ?> 
<div class="top-area container-fluid" style="background-image: url('<?php echo esc_url( $image ); ?>')">
	<div id="carousel-home" class="flexslider woocommerce loading-hide">
		<ul class="slides products">
			<?php
			$include = get_post_meta( get_the_ID(), 'alpha_store_carousel_select', true );
			$loop	 = new WP_Query( array(
				'post_type'	 => 'product',
				'post__in'	 => array_merge( array( 0 ), $include )
			) );
			while ( $loop->have_posts() ):$loop->the_post();
				global $product;
				?> 
				<li class="carousel-item"> 
					<div class="flex-img">                    	           
						<?php woocommerce_show_product_sale_flash( $post, $product ); ?>
						<div class="top-carousel-img">  
							<?php if ( has_post_thumbnail( $loop->post->ID ) ) echo get_the_post_thumbnail( $loop->post->ID, 'alpha-store-carousel' );
							else echo '<img src="' . get_template_directory_uri() . '/img/carousel-img.png" alt="Placeholder" width="270px" height="423px" />'; ?>
						</div>

						<div class="top-carousel-heading">
							<div class="top-carousel-title"><?php the_title(); ?></div>
							<div class="price"><?php echo $product->get_price_html(); ?></div>
							<?php woocommerce_template_loop_add_to_cart(); //ouptput the woocommerce loop add to cart button  ?>
						</div>
						<div class="carousel-heading-hover">
							<div class="top-carousel-title-hover"><a href="<?php echo get_permalink( $loop->post->ID ) ?>" title="<?php echo esc_attr( $loop->post->post_title ? $loop->post->post_title : $loop->post->ID ); ?>"><?php the_title(); ?></a></div>
							<div class="top-carousel-excerpt"><?php the_excerpt(); ?></div>
							<div class="price-hover"><?php echo $product->get_price_html(); ?></div>
							<?php woocommerce_template_loop_add_to_cart(); //ouptput the woocommerce loop add to cart button  ?>
						</div>                                                                                  
					</div> 
				</li>      
			<?php endwhile; wp_reset_postdata(); ?>  
		</ul> 
	</div>   
</div>
<div class="container rsrc-container" role="main"> 
