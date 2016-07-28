<?php

/**
 * Kirki Advanced Customizer
 * This is a sample configuration file to demonstrate all fields & capabilities.
 * @package amigo
 */
// Early exit if Kirki is not installed
if ( class_exists( 'Kirki' ) ) {

	/* Register Kirki config */
	Kirki::add_config( 'amigo_settings', array(
		'capability'	 => 'edit_theme_options',
		'option_type'	 => 'theme_mod',
	) );

	/**
	 * Add sections
	 */
	Kirki::add_section( 'sidebar_section', array(
		'title'			 => __( 'Sidebars', 'amigo' ),
		'priority'		 => 1,
		'description'	 => __( 'Sidebar layouts.', 'amigo' ),
	) );

	Kirki::add_section( 'layout_section', array(
		'title'			 => __( 'Main styling', 'amigo' ),
		'priority'		 => 2,
		'description'	 => __( 'Define theme layout', 'amigo' ),
	) );

	Kirki::add_section( 'top_bar_section', array(
		'title'		 => __( 'Social icons', 'amigo' ),
		'priority'	 => 3,
	) );

	Kirki::add_section( 'post_section', array(
		'title'			 => __( 'Post settings', 'amigo' ),
		'priority'		 => 5,
		'description'	 => __( 'Single post settings', 'amigo' ),
	) );

	Kirki::add_section( 'site_bg_section', array(
		'title'		 => __( 'Site Background', 'amigo' ),
		'priority'	 => 6,
	) );
	Kirki::add_section( 'colors_section', array(
		'title'		 => __( 'Colors and Typography', 'amigo' ),
		'priority'	 => 7,
	) );

	/**
	 * Add the configuration.
	 */
	Kirki::add_config( 'amigo_settings', array(
		'capability'	 => 'edit_theme_options',
		'option_type'	 => 'theme_mod',
	) );


	/**
	 * Add fields
	 */
	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'switch',
		'settings'		 => 'rigth-sidebar-check',
		'label'			 => __( 'Right Sidebar', 'amigo' ),
		'description'	 => __( 'Enable the Right Sidebar', 'amigo' ),
		'section'		 => 'sidebar_section',
		'default'		 => 1,
		'priority'		 => 10,
	) );

	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'radio-buttonset',
		'settings'	 => 'right-sidebar-size',
		'label'		 => __( 'Right Sidebar Size', 'amigo' ),
		'section'	 => 'sidebar_section',
		'default'	 => '4',
		'priority'	 => 10,
		'choices'	 => array(
			'1'	 => '1',
			'2'	 => '2',
			'3'	 => '3',
			'4'	 => '4',
			'5'	 => '5'
		),
		'required'	 => array(
			array(
				'setting'	 => 'rigth-sidebar-check',
				'operator'	 => '==',
				'value'		 => 1,
			),
		)
	) );

	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'switch',
		'settings'		 => 'left-sidebar-check',
		'label'			 => __( 'Left Sidebar', 'amigo' ),
		'description'	 => __( 'Enable the Left Sidebar', 'amigo' ),
		'section'		 => 'sidebar_section',
		'default'		 => 0,
		'priority'		 => 10,
	) );

	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'radio-buttonset',
		'settings'	 => 'left-sidebar-size',
		'label'		 => __( 'Left Sidebar Size', 'amigo' ),
		'section'	 => 'sidebar_section',
		'default'	 => '3',
		'priority'	 => 10,
		'choices'	 => array(
			'1'	 => '1',
			'2'	 => '2',
			'3'	 => '3',
			'4'	 => '4',
			'5'	 => '5'
		),
		'required'	 => array(
			array(
				'setting'	 => 'left-sidebar-check',
				'operator'	 => '==',
				'value'		 => 1,
			),
		)
	) );


	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'image',
		'settings'		 => 'header-logo',
		'label'			 => __( 'Logo', 'amigo' ),
		'description'	 => __( 'Upload your logo', 'amigo' ),
		'section'		 => 'layout_section',
		'default'		 => '',
		'priority'		 => 10,
	) );

	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'switch',
		'settings'		 => 'get-carousel',
		'label'			 => __( 'Carousel', 'amigo' ),
		'description'	 => __( 'Enable or disable carousel', 'amigo' ),
		'section'		 => 'layout_section',
		'default'		 => 0,
		'priority'		 => 10,
	) );

	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'select',
		'settings'		 => 'carousel-categories',
		'label'			 => __( 'Carousel category', 'amigo' ),
		'description'	 => __( 'Select category for carousel', 'amigo' ),
		'section'		 => 'layout_section',
		'default'		 => '',
		'priority'		 => 10,
		'choices'		 => amigo_get_cats(),
		'required'		 => array(
			array(
				'setting'	 => 'get-carousel',
				'operator'	 => '==',
				'value'		 => 1,
			),
		)
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'radio-buttonset',
		'settings'		 => 'carousel-auto-slide',
		'label'			 => __( 'Carousel autoplay', 'amigo' ),
		'description'	 => __( 'Carousel automatic sliding', 'amigo' ),
		'section'		 => 'layout_section',
		'default'		 => 'true',
		'priority'		 => 10,
		'choices'		 => array(
			'true'	 => __( 'Yes', 'amigo' ),
			'false'	 => __( 'No', 'amigo' ),
		),
		'required'		 => array(
			array(
				'setting'	 => 'get-carousel',
				'operator'	 => '==',
				'value'		 => 1,
			),
		)
	) );

	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'radio-buttonset',
		'settings'		 => 'carousel-everywhere',
		'label'			 => __( 'Carousel placing', 'amigo' ),
		'description'	 => __( 'Enable carousel everywhere or only on homepage', 'amigo' ),
		'section'		 => 'layout_section',
		'default'		 => 'home',
		'priority'		 => 10,
		'choices'		 => array(
			'home'		 => __( 'Homepage', 'amigo' ),
			'everywhere' => __( 'Everywhere', 'amigo' ),
		),
		'required'		 => array(
			array(
				'setting'	 => 'get-carousel',
				'operator'	 => '==',
				'value'		 => 1,
			),
		)
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'switch',
		'settings'		 => 'related-posts-check',
		'label'			 => __( 'Related posts', 'amigo' ),
		'description'	 => __( 'Enable or disable related posts', 'amigo' ),
		'section'		 => 'post_section',
		'default'		 => 1,
		'priority'		 => 10,
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'switch',
		'settings'		 => 'author-check',
		'label'			 => __( 'Author box', 'amigo' ),
		'description'	 => __( 'Enable or disable author box', 'amigo' ),
		'section'		 => 'post_section',
		'default'		 => 1,
		'priority'		 => 10,
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'switch',
		'settings'		 => 'post-nav-check',
		'label'			 => __( 'Post navigation', 'amigo' ),
		'description'	 => __( 'Enable or disable navigation below post content', 'amigo' ),
		'section'		 => 'post_section',
		'default'		 => 1,
		'priority'		 => 10,
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'switch',
		'settings'		 => 'breadcrumbs-check',
		'label'			 => __( 'Breadcrumbs', 'amigo' ),
		'description'	 => __( 'Enable or disable Breadcrumbs', 'amigo' ),
		'section'		 => 'post_section',
		'default'		 => 1,
		'priority'		 => 10,
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'			 => 'toggle',
		'settings'		 => 'amigo_socials',
		'label'			 => __( 'Social Icons', 'amigo' ),
		'description'	 => __( 'Enable or Disable the social icons', 'amigo' ),
		'section'		 => 'top_bar_section',
		'default'		 => 0,
		'priority'		 => 10,
	) );
	$s_social_links = array(
		'twp_social_facebook'	 => __( 'Facebook', 'amigo' ),
		'twp_social_twitter'	 => __( 'Twitter', 'amigo' ),
		'twp_social_google'		 => __( 'Google-Plus', 'amigo' ),
		'twp_social_instagram'	 => __( 'Instagram', 'amigo' ),
		'twp_social_pin'		 => __( 'Pinterest', 'amigo' ),
		'twp_social_youtube'	 => __( 'YouTube', 'amigo' ),
		'twp_social_reddit'		 => __( 'Reddit', 'amigo' ),
	);

	foreach ( $s_social_links as $keys => $values ) {
		Kirki::add_field( 'amigo_settings', array(
			'type'			 => 'text',
			'settings'		 => $keys,
			'label'			 => $values,
			'description'	 => sprintf( __( 'Insert your custom link to show the %s icon.', 'amigo' ), $values ),
			'help'			 => __( 'Leave blank to hide icon.', 'amigo' ),
			'section'		 => 'top_bar_section',
			'default'		 => '',
			'priority'		 => 10,
		) );
	}


	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'custom',
		'settings'	 => 'main_color_title',
		'label'		 => __( 'Main colors', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '<div style="border-bottom: 1px solid #fff;"></div>',
		'priority'	 => 10,
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'color',
		'settings'	 => 'main_site_color',
		'label'		 => __( 'Main color', 'amigo' ),
		'help'		 => __( 'Main site color.', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '#ff7f00',
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element'	 => '.pagination > li > a, i.post-icon.fa, .navbar-inverse .navbar-nav > .active > a, .navbar-inverse .navbar-nav > .active > a:hover, .navbar-inverse .navbar-nav > .active > a:focus, .navbar-inverse .navbar-nav > li > a:hover',
				'property'	 => 'color',
			),
			array(
				'element'	 => '.first-textarea .widget, .featured-thumbnail:hover .archive-layer, .navigation.pagination, #wp-calendar #prev a, #wp-calendar #next a, .comment-reply-link, .flex-caption .page-header:before, li.carousel-item:hover .carousel-layer, .btn-info, .comment-respond #submit, #searchform #searchsubmit, .time-info, #back-top span, .post-categories li',
				'property'	 => 'background-color',
			),
			array(
				'element'	 => '#slidebox, .btn-info, .comment-respond #submit, #searchform #searchsubmit, blockquote, .comment-reply-link, #wp-calendar #prev a, #wp-calendar #next a, .rsrc-author-credits, .pagination > .active > span',
				'property'	 => 'border-color',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'color',
		'settings'	 => 'body_color',
		'label'		 => __( 'Body color', 'amigo' ),
		'help'		 => __( 'Background color for pages and posts.', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '#fff',
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element'	 => '.search-head, .rsrc-main article, #slidebox, .rsrc-main .rsrc-post-content, i.post-icon.fa, .widget, #breadcrumbs, #content-footer-section, #content-footer-section .widget, .rsrc-author-credits',
				'property'	 => 'background-color',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'color',
		'settings'	 => 'links_color',
		'label'		 => __( 'Links color', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '#1E1E1E',
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element'	 => 'a',
				'property'	 => 'color',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'color',
		'settings'	 => 'links_hover_color',
		'label'		 => __( 'Links hover color', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '#fc002a',
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element'	 => 'a:hover',
				'property'	 => 'color',
			),
			array(
				'element'	 => '.btn-info:hover, .btn-info:focus, .btn-info.focus, .btn-info:active, .btn-info.active, .open > .dropdown-toggle.btn-info, .comment-respond #submit:hover, .comment-respond #submit:focus, .comment-respond #submit.focus, .comment-respond #submit:active, .comment-respond #submit.active, .open > .dropdown-toggle.comment-respond #submit, #searchform #searchsubmit:hover, #searchform #searchsubmit:focus, #searchform #searchsubmit.focus, #searchform #searchsubmit:active, #searchform #searchsubmit.active, .open > .dropdown-toggle#searchform #searchsubmit',
				'property'	 => 'background-color',
			),
			array(
				'element'	 => '.btn-info:hover, .btn-info:focus, .btn-info.focus, .btn-info:active, .btn-info.active, .open > .dropdown-toggle.btn-info, .comment-respond #submit:hover, .comment-respond #submit:focus, .comment-respond #submit.focus, .comment-respond #submit:active, .comment-respond #submit.active, .open > .dropdown-toggle.comment-respond #submit, #searchform #searchsubmit:hover, #searchform #searchsubmit:focus, #searchform #searchsubmit.focus, #searchform #searchsubmit:active, #searchform #searchsubmit.active, .open > .dropdown-toggle#searchform #searchsubmit',
				'property'	 => 'border-color',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'custom',
		'settings'	 => 'typo_title',
		'label'		 => __( 'Typography and font colors', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '<div style="border-bottom: 1px solid #fff;"></div>',
		'priority'	 => 10,
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'color',
		'settings'	 => 'color_site_title',
		'label'		 => __( 'Site title color', 'amigo' ),
		'help'		 => __( 'Site title text color, if not defined logo.', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '#fff',
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element'	 => 'h2.site-title a, h1.site-title a',
				'property'	 => 'color',
				'units'		 => ' !important',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'typography',
		'settings'	 => 'site_title_typography_font',
		'label'		 => __( 'Site title font', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => array(
			'font-style'	 => array( 'bold', 'italic' ),
			'font-family'	 => 'Erica One',
			'font-size'		 => '36px',
			'variant'		 => '400',
			'line-height'	 => '1.1',
			'letter-spacing' => '0',
		),
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element' => 'h2.site-title, h1.site-title',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'color',
		'settings'	 => 'color_content_text',
		'label'		 => __( 'Text color', 'amigo' ),
		'help'		 => __( 'Select the general text color used in the theme.', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '#4D4D4D',
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element'	 => 'body, .entry-summary, .info-reviews',
				'property'	 => 'color',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'typography',
		'settings'	 => 'content_typography_font',
		'label'		 => __( 'Site content font', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => array(
			'font-style'	 => array( 'bold', 'italic' ),
			'font-family'	 => 'Roboto',
			'font-size'		 => '14px',
			'variant'		 => '300',
			'line-height'	 => '1.8',
			'letter-spacing' => '0',
		),
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element' => 'body, .entry-summary, .btn-primary.outline',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'color',
		'settings'	 => 'color_headings',
		'label'		 => __( 'Headings color', 'amigo' ),
		'help'		 => __( 'Select the general text color used in the theme.', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '#3d3d3d',
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element'	 => '.home-header .page-header a, .page-header, .page-header a',
				'property'	 => 'color',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'typography',
		'settings'	 => 'headings_typography_font',
		'label'		 => __( 'Headings font', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => array(
			'font-style'	 => array( 'bold', 'italic' ),
			'font-family'	 => 'Oswald',
			'font-size'		 => '30px',
			'variant'		 => '700',
			'line-height'	 => '1.1',
			'letter-spacing' => '0',
		),
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element' => '.home-header .page-header a, .page-header, .page-header a',
			),
		),
	) );
	Kirki::add_field( 'amigo_settings', array(
		'type'		 => 'color',
		'settings'	 => 'color_widget_titles',
		'label'		 => __( 'Widget titles color', 'amigo' ),
		'section'	 => 'colors_section',
		'default'	 => '#3d3d3d',
		'priority'	 => 10,
		'output'	 => array(
			array(
				'element'	 => 'h3.widget-title',
				'property'	 => 'color',
			),
		),
	) );
}

/**
 * Configuration sample for the amigo Customizer.
 */
function amigo_configuration_sample( $config ) {

	$config[ 'color_back' ]		 = '#192429';
	$config[ 'color_accent' ]	 = '#FF7F00';
	$config[ 'width' ]			 = '25%';

	return $config;
}

add_filter( 'kirki/config', 'amigo_configuration_sample' );

function amigo_get_cats() {
	/* GET LIST OF CATEGORIES */
	$layercats		 = get_categories();
	$newList		 = array();
	$newList[ '0' ]	 = __( 'All categories', 'amigo' );
	foreach ( $layercats as $category ) {
		$newList[ $category->term_id ] = $category->cat_name;
	}
	return $newList;
}
