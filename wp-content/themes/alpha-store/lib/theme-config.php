<?php

/**
 * Kirki Advanced Customizer
 * This is a sample configuration file to demonstrate all fields & capabilities.
 * @package alpha-store
 */
// Early exit if Kirki is not installed
if ( !class_exists( 'Kirki' ) ) {
	return;
}
/* Register Kirki config */
Kirki::add_config( 'alpha_store_settings', array(
	'capability'	 => 'edit_theme_options',
	'option_type'	 => 'theme_mod',
) );

/**
 * Add sections
 */
Kirki::add_section( 'alpha_store_sidebar_section', array(
	'title'			 => __( 'Sidebars', 'alpha-store' ),
	'priority'		 => 10,
	'description'	 => __( 'Sidebar layouts.', 'alpha-store' ),
) );

Kirki::add_section( 'alpha_store_top_bar_section', array(
	'title'		 => __( 'Top Bar', 'alpha-store' ),
	'priority'	 => 10,
) );

Kirki::add_section( 'alpha_store_search_bar_section', array(
	'title'			 => __( 'Search Bar', 'alpha-store' ),
	'priority'		 => 10,
	'description'	 => __( 'Search Bar', 'alpha-store' ),
) );

if ( class_exists( 'WooCommerce' ) ) {
	Kirki::add_section( 'alpha_store_woo_section', array(
		'title'		 => __( 'WooCommerce', 'alpha-store' ),
		'priority'	 => 10,
	) );
}

Kirki::add_section( 'alpha_store_links_section', array(
	'title'		 => __( 'Theme Important Links', 'alpha-store' ),
	'priority'	 => 190,
) );


Kirki::add_field( 'alpha_store_settings', array(
	'type'			 => 'switch',
	'settings'		 => 'rigth-sidebar-check',
	'label'			 => __( 'Right Sidebar', 'alpha-store' ),
	'description'	 => __( 'Enable the Right Sidebar', 'alpha-store' ),
	'section'		 => 'alpha_store_sidebar_section',
	'default'		 => 1,
	'priority'		 => 10,
) );

Kirki::add_field( 'alpha_store_settings', array(
	'type'		 => 'radio-buttonset',
	'settings'	 => 'right-sidebar-size',
	'label'		 => __( 'Right Sidebar Size', 'alpha-store' ),
	'section'	 => 'alpha_store_sidebar_section',
	'default'	 => '3',
	'priority'	 => 10,
	'choices'	 => array(
		'1'	 => '1',
		'2'	 => '2',
		'3'	 => '3',
		'4'	 => '4',
	),
) );

Kirki::add_field( 'alpha_store_settings', array(
	'type'			 => 'switch',
	'settings'		 => 'left-sidebar-check',
	'label'			 => __( 'Left Sidebar', 'alpha-store' ),
	'description'	 => __( 'Enable the Left Sidebar', 'alpha-store' ),
	'section'		 => 'alpha_store_sidebar_section',
	'default'		 => 0,
	'priority'		 => 10,
) );

Kirki::add_field( 'alpha_store_settings', array(
	'type'		 => 'radio-buttonset',
	'settings'	 => 'left-sidebar-size',
	'label'		 => __( 'Left Sidebar Size', 'alpha-store' ),
	'section'	 => 'alpha_store_sidebar_section',
	'default'	 => '3',
	'priority'	 => 10,
	'choices'	 => array(
		'1'	 => '1',
		'2'	 => '2',
		'3'	 => '3',
		'4'	 => '4',
	),
) );
Kirki::add_field( 'alpha_store_settings', array(
	'type'			 => 'switch',
	'settings'		 => 'alpha_store_account',
	'label'			 => __( 'My Account/Login', 'alpha-store' ),
	'description'	 => __( 'Enable or Disable My Account/Login link', 'alpha-store' ),
	'section'		 => 'alpha_store_top_bar_section',
	'default'		 => 1,
	'priority'		 => 10,
) );
Kirki::add_field( 'alpha_store_settings', array(
	'type'			 => 'switch',
	'settings'		 => 'alpha_store_socials',
	'label'			 => __( 'Social Icons', 'alpha-store' ),
	'description'	 => __( 'Enable or Disable the social icons', 'alpha-store' ),
	'section'		 => 'alpha_store_top_bar_section',
	'default'		 => 0,
	'priority'		 => 10,
) );
$s_social_links = array(
	'twp_social_facebook'	 => __( 'Facebook', 'alpha-store' ),
	'twp_social_twitter'	 => __( 'Twitter', 'alpha-store' ),
	'twp_social_google'		 => __( 'Google-Plus', 'alpha-store' ),
	'twp_social_instagram'	 => __( 'Instagram', 'alpha-store' ),
	'twp_social_pin'		 => __( 'Pinterest', 'alpha-store' ),
	'twp_social_youtube'	 => __( 'YouTube', 'alpha-store' ),
	'twp_social_reddit'		 => __( 'Reddit', 'alpha-store' ),
);

foreach ( $s_social_links as $keys => $values ) {
	Kirki::add_field( 'alpha_store_settings', array(
		'type'			 => 'text',
		'settings'		 => $keys,
		'label'			 => $values,
		'description'	 => sprintf( __( 'Insert your custom link to show the %s icon.', 'alpha-store' ), $values ),
		'help'			 => __( 'Leave blank to hide icon.', 'alpha-store' ),
		'section'		 => 'alpha_store_top_bar_section',
		'default'		 => '',
		'priority'		 => 10,
	) );
}
Kirki::add_field( 'alpha_store_settings', array(
	'type'				 => 'textarea',
	'settings'			 => 'infobox-text',
	'label'				 => __( 'Search bar info text', 'alpha-store' ),
	'help'				 => __( 'You can add custom text. Only text allowed!', 'alpha-store' ),
	'section'			 => 'alpha_store_search_bar_section',
	'sanitize_callback'	 => 'wp_kses_post',
	'default'			 => '',
	'priority'			 => 10,
) );

if ( function_exists( 'YITH_WCWL' ) ) {
	Kirki::add_field( 'alpha_store_settings', array(
		'type'			 => 'switch',
		'settings'		 => 'wishlist-top-icon',
		'label'			 => __( 'Header Wishlist icon', 'alpha-store' ),
		'description'	 => __( 'Enable or disable heart icon with counter in header', 'alpha-store' ),
		'section'		 => 'alpha_store_woo_section',
		'default'		 => 0,
		'priority'		 => 10,
	) );
}
Kirki::add_field( 'alpha_store_settings', array(
	'type'			 => 'slider',
	'settings'		 => 'archive_number_products',
	'label'			 => __( 'Number of products', 'alpha-store' ),
	'description'	 => __( 'Change number of products displayed per page in archive(shop) page.', 'alpha-store' ),
	'section'		 => 'alpha_store_woo_section',
	'default'		 => 24,
	'priority'		 => 10,
	'choices'		 => array(
		'min'	 => 2,
		'max'	 => 60,
		'step'	 => 1
	),
) );
Kirki::add_field( 'alpha_store_settings', array(
	'type'			 => 'slider',
	'settings'		 => 'archive_number_columns',
	'label'			 => __( 'Number of products per row', 'alpha-store' ),
	'description'	 => __( 'Change the number of product columns per row in archive(shop) page.', 'alpha-store' ),
	'section'		 => 'alpha_store_woo_section',
	'default'		 => 4,
	'priority'		 => 10,
	'choices'		 => array(
		'min'	 => 2,
		'max'	 => 5,
		'step'	 => 1
	),
) );
Kirki::add_field( 'alpha_store_settings', array(
	'type'		 => 'color',
	'settings'	 => 'color_site_title',
	'label'		 => __( 'Site title color', 'alpha-store' ),
	'help'		 => __( 'Site title text color, if not defined logo.', 'alpha-store' ),
	'section'	 => 'colors',
	'default'	 => '#fff',
	'priority'	 => 10,
	'output'	 => array(
		array(
			'element'	 => 'h2.site-title a, h1.site-title a',
			'property'	 => 'color',
		),
	),
	'transport'	 => 'auto',
) );
Kirki::add_field( 'alpha_store_settings', array(
	'type'		 => 'color',
	'settings'	 => 'color_site_desc',
	'label'		 => __( 'Site description color', 'alpha-store' ),
	'help'		 => __( 'Site description text color, if not defined logo.', 'alpha-store' ),
	'section'	 => 'colors',
	'default'	 => '#fff',
	'priority'	 => 10,
	'output'	 => array(
		array(
			'element'	 => 'h2.site-desc, h3.site-desc',
			'property'	 => 'color',
		),
	),
	'transport'	 => 'auto',
) );

$theme_links = array(
	'documentation'	 => array(
		'link'		 => esc_url_raw( 'http://demo.themes4wp.com/documentation/category/alpha-store/' ),
		'text'		 => __( 'Documentation', 'alpha-store' ),
		'settings'	 => 'theme-docs',
	),
	'support'		 => array(
		'link'		 => esc_url_raw( 'http://support.themes4wp.com/' ),
		'text'		 => __( 'Support', 'alpha-store' ),
		'settings'	 => 'theme-support',
	),
	'demo'			 => array(
		'link'		 => esc_url_raw( 'http://demo.themes4wp.com/alpha-store/' ),
		'text'		 => __( 'View Demo', 'alpha-store' ),
		'settings'	 => 'theme-demo',
	),
	'rating'		 => array(
		'link'		 => esc_url_raw( 'https://wordpress.org/support/view/theme-reviews/alpha-store?filter=5' ),
		'text'		 => __( 'Rate This Theme', 'alpha-store' ),
		'settings'	 => 'theme-rate',
	)
);

foreach ( $theme_links as $theme_link ) {
	Kirki::add_field( 'alpha_store_settings', array(
		'type'		 => 'custom',
		'settings'	 => $theme_link[ 'settings' ],
		'section'	 => 'alpha_store_links_section',
		'default'	 => '<div style="padding: 10px; text-align: center; font-size: 22px; font-weight: bold;"><a target="_blank" href="' . $theme_link[ 'link' ] . '" >' . esc_attr( $theme_link[ 'text' ] ) . ' </a></div>',
		'priority'	 => 10,
	) );
}

function alpha_store_configuration() {

	$config[ 'color_back' ]		= '#192429';
	$config[ 'color_accent' ]	= '#00a0d2';
	$config[ 'width' ]			= '25%';

	return $config;
}

add_filter( 'kirki/config', 'alpha_store_configuration' );

/**
 * Add custom CSS styles
 */
function alpha_store_enqueue_header_css() {

	$columns = get_theme_mod( 'archive_number_columns', 4 );

	if ( $columns == '2' ) {
		$css = '@media only screen and (min-width: 769px) {.archive .rsrc-content .woocommerce ul.products li.product{width: 48.05%}}';
	} elseif ( $columns == '3' ) {
		$css = '@media only screen and (min-width: 769px) {.archive .rsrc-content .woocommerce ul.products li.product{width: 30.75%;}}';
	} elseif ( $columns == '5' ) {
		$css = '@media only screen and (min-width: 769px) {.archive .rsrc-content .woocommerce ul.products li.product{width: 16.95%;}}';
	} else {
		$css = '';
	}
	wp_add_inline_style( 'kirki-styles-alpha_store_settings', $css );
}

add_action( 'wp_enqueue_scripts', 'alpha_store_enqueue_header_css', 9999 );

