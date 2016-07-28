<?php
/* * *
 * Theme Info
 *
 * Adds a simple Theme Info page to the Appearance section of the WordPress Dashboard. 
 *
 */


// Add Theme Info page to admin menu
add_action( 'admin_menu', 'alpha_store_add_theme_info_page' );

function alpha_store_add_theme_info_page() {

	// Get Theme Details from style.css
	$theme = wp_get_theme();

	add_theme_page(
	sprintf( esc_html__( 'Welcome to %1$s %2$s', 'alpha-store' ), $theme->get( 'Name' ), $theme->get( 'Version' ) ), esc_html__( 'Theme Info', 'alpha-store' ), 'edit_theme_options', 'alpha-store', 'alpha_store_display_theme_info_page'
	);
}

// Display Theme Info page
function alpha_store_display_theme_info_page() {

	// Get Theme Details from style.css
	$theme = wp_get_theme();
	?>

	<div class="wrap theme-info-wrap">

		<h1><?php printf( esc_html__( 'Welcome to %1$s %2$s', 'alpha-store' ), $theme->get( 'Name' ), $theme->get( 'Version' ) ); ?></h1>

		<div class="theme-description"><?php echo $theme->get( 'Description' ); ?></div>

		<hr>
		<div class="important-links clearfix">
			<p><strong><?php esc_html_e( 'Theme Links', 'alpha-store' ); ?>:</strong>
				<a href="<?php echo esc_url( 'http://themes4wp.com/theme/alpha-store' ); ?>" target="_blank"><?php esc_html_e( 'Theme Page', 'alpha-store' ); ?></a>
				<a href="<?php echo esc_url( 'http://demo.themes4wp.com/alpha-store/' ); ?>" target="_blank"><?php esc_html_e( 'Theme Demo', 'alpha-store' ); ?></a>
				<a href="<?php echo esc_url( 'http://demo.themes4wp.com/documentation/category/alpha-store/' ); ?>" target="_blank"><?php esc_html_e( 'Theme Documentation', 'alpha-store' ); ?></a>
				<a href="<?php echo esc_url( 'http://wordpress.org/support/view/theme-reviews/alpha-store?filter=5' ); ?>" target="_blank"><?php esc_html_e( 'Rate this theme', 'alpha-store' ); ?></a>
				<a href="<?php echo esc_url( 'https://wordpress.org/plugins/kirki/' ); ?>" target="_blank"><?php esc_html_e( 'Kirki (Theme options toolkit)', 'alpha-store' ); ?></a>
			</p>
		</div>
		<hr>

		<div id="getting-started">

			<h3><?php printf( esc_html__( 'Getting Started with %s', 'alpha-store' ), $theme->get( 'Name' ) ); ?></h3>

			<div class="columns-wrapper clearfix">

				<div class="column column-half clearfix">

					<div class="section">
						<h4><?php esc_html_e( 'Theme Documentation', 'alpha-store' ); ?></h4>

						<p class="about">
							<?php esc_html_e( 'You need help to setup and configure this theme? We got you covered with an extensive theme documentation on our website.', 'alpha-store' ); ?>
						</p>
						<p>
							<a href="<?php echo esc_url( 'http://demo.themes4wp.com/documentation/category/alpha-store/' ); ?>" target="_blank" class="button button-secondary">
								<?php printf( esc_html__( 'View %s Documentation', 'alpha-store' ), 'Alpha Store' ); ?>
							</a>
						</p>
					</div>

					<div class="section">
						<h4><?php esc_html_e( 'Theme Setup', 'alpha-store' ); ?></h4>

						<p class="youtube">
							<iframe width="500" height="300" src="<?php echo esc_url( 'https://www.youtube.com/embed/eb8PrCVajiM' ); ?>" frameborder="0" allowfullscreen></iframe>
						</p>
						<p>
							<a href="<?php echo esc_url( 'http://demo.themes4wp.com/documentation/homepage-setup-alpha-store/' ); ?>" target="_blank" class="button button-primary">
								<?php printf( esc_html__( '%s Homepage Setup', 'alpha-store' ), 'Alpha Store' ); ?>
							</a>
						</p>
					</div>
					
					<div class="section">
						<h4><?php esc_html_e( 'Theme Options', 'alpha-store' ); ?></h4>

						<p class="about">
							<?php printf( esc_html__( '%s makes use of the Customizer for all theme settings. First install Kirki Toolkit and than click on "Customize Theme" to open the Customizer.', 'alpha-store' ), $theme->get( 'Name' ) ); ?>
						</p>
						<p>
							<a href="<?php echo admin_url( 'customize.php' ); ?>" class="button button-primary">
								<?php esc_html_e( 'Customize Theme', 'alpha-store' ); ?>
							</a>
						</p>
					</div>

					<div class="section">
						<h4><?php esc_html_e( 'Pro Version', 'alpha-store' ); ?></h4>

						<p class="about">
							<?php printf( esc_html__( 'Purchase the Pro Version of %s to get additional features and advanced customization options.', 'alpha-store' ), 'alpha-store' ); ?>
						</p>
						<ul>
							<li><?php esc_html_e( 'Unlimited colors', 'alpha-store' ); ?></li>
							<li><?php esc_html_e( '600+ Google fonts', 'alpha-store' ); ?></li>
							<li><?php esc_html_e( 'Custom tabs', 'alpha-store' ); ?></li>
							<li><?php esc_html_e( 'Live search support', 'alpha-store' ); ?></li>
							<li><?php esc_html_e( 'Image flipper', 'alpha-store' ); ?></li>
							<li><?php esc_html_e( 'And much more...', 'alpha-store' ); ?></li>
						</ul>
						<p>
							<a href="<?php echo esc_url( 'http://themes4wp.com/product/alpha-store-pro/' ); ?>" target="_blank" class="button button-secondary">
								<?php printf( esc_html__( 'Learn more about %s Pro', 'alpha-store' ), 'Alpha Store' ); ?>
							</a>
						</p>
					</div>

				</div>

				<div class="column column-half clearfix">

					<img src="<?php echo get_template_directory_uri(); ?>/screenshot.png" />

				</div>

			</div>

		</div>

		<hr>

		<div id="theme-author">

			<p>
				<?php printf( esc_html__( '%1$s is proudly brought to you by %2$s. If you like this theme, %3$s :)', 'alpha-store' ), $theme->get( 'Name' ), '<a target="_blank" href="http://themes4wp.com/" title="Themes4WP">Themes4WP</a>', '<a target="_blank" href="http://wordpress.org/support/view/theme-reviews/alpha-store?filter=5" title="Alpha Store Review">' . esc_html__( 'rate it', 'alpha-store' ) . '</a>' ); ?>
			</p>

		</div>

	</div>

	<?php
}

// Add CSS for Theme Info Panel
add_action( 'admin_enqueue_scripts', 'alpha_store_theme_info_page_css' );

function alpha_store_theme_info_page_css( $hook ) {

	// Load styles and scripts only on theme info page
	if ( 'appearance_page_alpha-store' != $hook ) {
		return;
	}

	// Embed theme info css style
	wp_enqueue_style( 'alpha-store-theme-info-css', get_template_directory_uri() . '/css/theme-info.css' );
}
