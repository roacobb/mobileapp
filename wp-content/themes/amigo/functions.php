<?php
////////////////////////////////////////////////////////////////////
// Settig Theme-options
////////////////////////////////////////////////////////////////////
include_once( trailingslashit( get_template_directory() ) . '/lib/plugin-activation.php' );
include_once( trailingslashit( get_template_directory() ) . '/lib/theme-config.php' );

add_action( 'after_setup_theme', 'amigo_setup' );

if ( !function_exists( 'amigo_setup' ) ) :

	function amigo_setup() {

		// Theme lang
		load_theme_textdomain( 'amigo', get_template_directory() . '/languages' );

		// Add Title Tag Support
		add_theme_support( 'title-tag' );

		// Register Menus
		register_nav_menus(
			array(
				'main_menu' => __( 'Main Menu', 'amigo' ),
			)
		);

		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 300, 300, true );
		add_image_size( 'amigo-home', 476, 634, true );
		add_image_size( 'amigo-slider', 765, 430, true );
		add_image_size( 'amigo-single', 1170, 400, true );

		// Adds RSS feed links to for posts and comments.
		add_theme_support( 'automatic-feed-links' );
		
		// Add Custom Background Support
		$args = array(
			'default-color' => '424242',
		);
		add_theme_support( 'custom-background', $args );
		
		// Set Content Width
		if ( !isset( $content_width ) )
			$content_width = 800;

		// Enable support for Post Formats.
		add_theme_support( 'post-formats', array( 'video', 'gallery' ) );
		
		// Display a admin notices
		add_action( 'admin_notices', 'amigo_admin_notice' );
	}

endif;

////////////////////////////////////////////////////////////////////
// Admin notices
////////////////////////////////////////////////////////////////////
function amigo_admin_notice() {
	global $current_user;
	$user_id = $current_user->ID;
	/* Check that the user hasn't already clicked to ignore the message */
	if ( !get_user_meta( $user_id, 'amigo_ignore_notice' ) ) {
		echo '<div class="updated notice-info point-notice" style="position:relative;"><p>';
		printf( __( 'Like Amigo theme? You will <strong>LOVE Amigo PRO</strong>! ', 'amigo' ) . '<a href="' . esc_url( 'http://themes4wp.com/product/amigo-pro/' ) . '" target="_blank">' . __( 'Click here for all the exciting features.', 'amigo' ) . '</a><a href="%1$s" class="dashicons dashicons-dismiss dashicons-dismiss-icon" style="position: absolute; top: 8px; right: 8px; color: #222; opacity: 0.4; text-decoration: none !important;"></a>', '?amigo_notice_ignore=0' );
		echo "</p></div>";
	}
}

add_action( 'admin_init', 'amigo_notice_ignore' );

function amigo_notice_ignore() {
	global $current_user;
	$user_id = $current_user->ID;
	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset( $_GET[ 'amigo_notice_ignore' ] ) && '0' == $_GET[ 'amigo_notice_ignore' ] ) {
		add_user_meta( $user_id, 'amigo_ignore_notice', 'true', true );
	}
}

add_action( 'customize_controls_print_footer_scripts', 'amigo_pro_banner' );

function amigo_pro_banner() {
	echo '<a href="' . esc_url( 'http://themes4wp.com/product/amigo-pro/' ) . '" id="pro-banner" style="display: none; margin-top: 10px; background: #192429;" target="_blank"><img src="' . get_template_directory_uri() . '/img/amigo-pro.jpg" /></a>';
	echo '<script type="text/javascript">jQuery(document).ready(function($) { $("#pro-banner").appendTo("#customize-info").css("display", "block"); });</script>';
}


////////////////////////////////////////////////////////////////////
// Enqueue Styles (normal style.css and bootstrap.css)
////////////////////////////////////////////////////////////////////
function amigo_theme_stylesheets() {
	wp_enqueue_style( 'amigo-bootstrap-css', get_template_directory_uri() . '/css/bootstrap.css' );
	wp_enqueue_style( 'amigo-stylesheet', get_stylesheet_uri() );
	// load Font Awesome css
	wp_enqueue_style( 'amigo-font-awesome', get_template_directory_uri() . '/css/font-awesome.min.css', false );
	// load Flexslider css
	wp_enqueue_style( 'amigo-stylesheet-flexslider', get_template_directory_uri() . '/css/flexslider.css', 'style' );
}

add_action( 'wp_enqueue_scripts', 'amigo_theme_stylesheets' );

////////////////////////////////////////////////////////////////////
// Register Bootstrap JS with jquery
////////////////////////////////////////////////////////////////////
function amigo_theme_js() {
	wp_enqueue_script( 'amigo-bootstrap-js', get_template_directory_uri() . '/js/bootstrap.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'amigo-theme-js', get_template_directory_uri() . '/js/customscript.js', array( 'jquery' ) );
	wp_enqueue_script( 'amigo-flexslider', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array( 'jquery' ) );
}

add_action( 'wp_enqueue_scripts', 'amigo_theme_js' );

////////////////////////////////////////////////////////////////////
// Theme Info page
////////////////////////////////////////////////////////////////////

if ( is_admin() ) {
	require_once(trailingslashit( get_template_directory() ) . 'lib/theme-info.php');
}

////////////////////////////////////////////////////////////////////
// Register Custom Navigation Walker include custom menu widget to use walkerclass
////////////////////////////////////////////////////////////////////

require_once( trailingslashit( get_template_directory() ) . 'lib/wp_bootstrap_navwalker.php');

////////////////////////////////////////////////////////////////////
// Register the Sidebar(s)
////////////////////////////////////////////////////////////////////
add_action( 'widgets_init', 'amigo_widgets_init' );

function amigo_widgets_init() {
	register_sidebar(
	array(
		'name'			 => __( 'Right Sidebar', 'amigo' ),
		'id'			 => 'amigo-right-sidebar',
		'before_widget'	 => '<div id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</div>',
		'before_title'	 => '<h3 class="widget-title">',
		'after_title'	 => '</h3>',
	) );

	register_sidebar(
	array(
		'name'			 => __( 'Left Sidebar', 'amigo' ),
		'id'			 => 'amigo-left-sidebar',
		'before_widget'	 => '<div id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</div>',
		'before_title'	 => '<h3 class="widget-title">',
		'after_title'	 => '</h3>',
	) );

	register_sidebar(
	array(
		'name'			 => __( 'Area After First Post', 'amigo' ),
		'id'			 => 'amigo-post-area',
		'description'	 => __( 'Suitable for text widget.', 'amigo' ),
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h3>',
		'after_title'	 => '</h3>',
	) );
}

////////////////////////////////////////////////////////////////////
// Register hook and action to set Main content area col-md- width based on sidebar declarations
////////////////////////////////////////////////////////////////////

add_action( 'amigo_main_content_width_hook', 'amigo_main_content_width_columns' );

function amigo_main_content_width_columns() {

	$columns = '12';

	if ( get_theme_mod( 'rigth-sidebar-check', 1 ) != 0 ) {
		$columns = $columns - absint( get_theme_mod( 'right-sidebar-size', 3 ) );
	}

	if ( get_theme_mod( 'left-sidebar-check', 0 ) != 0 ) {
		$columns = $columns - absint( get_theme_mod( 'left-sidebar-size', 3 ) );
	}

	echo $columns;
}

function amigo_main_content_width() {
	do_action( 'amigo_main_content_width_hook' );
}

////////////////////////////////////////////////////////////////////
// Breadcrumbs
////////////////////////////////////////////////////////////////////
function amigo_breadcrumb() {
	global $post, $wp_query;

	$home		 = __( 'Home', 'amigo' );
	$delimiter	 = ' / ';
	$homeLink	 = home_url();
	if ( is_home() || is_front_page() ) {

		// no need for breadcrumbs in homepage
	} else {
		echo '<div id="breadcrumbs" >';
		echo '<div class="breadcrumbs-inner text-center row">';

		// main breadcrumbs lead to homepage

		echo '<span><a href="' . esc_url( $homeLink ) . '">' . '<i class="fa fa-home"></i><span>' . $home . '</span>' . '</a></span>' . $delimiter . ' ';

		// if blog page exists

		if ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_for_posts' ) ) {
			echo '<span><a href="' . esc_url( get_permalink( get_option( 'page_for_posts' ) ) ) . '">' . '<span>' . __( 'Blog', 'amigo' ) . '</span></a></span>' . $delimiter . ' ';
		}

		if ( is_category() ) {
			$thisCat = get_category( get_query_var( 'cat' ), false );
			if ( $thisCat->parent != 0 ) {
				$category_link = get_category_link( $thisCat->parent );
				echo '<span><a href="' . esc_url( $category_link ) . '">' . '<span>' . get_cat_name( $thisCat->parent ) . '</span>' . '</a></span>' . $delimiter . ' ';
			}

			$category_id	 = get_cat_ID( single_cat_title( '', false ) );
			$category_link	 = get_category_link( $category_id );
			echo '<span><a href="' . esc_url( $category_link ). '">' . '<span>' . single_cat_title( '', false ) . '</span>' . '</a></span>';
		} elseif ( is_single() && !is_attachment() ) {
			if ( get_post_type() != 'post' ) {
				$post_type	 = get_post_type_object( get_post_type() );
				$link = get_post_type_archive_link( get_post_type() );
				if ( $link ) {
					printf( '<span><a href="%s">%s</a></span>', esc_url( $link ), $post_type->labels->name );
					echo ' ' . $delimiter . ' ';
				}
				echo get_the_title();
			} else {
				$category = get_the_category();
				if ( $category ) {
					foreach ( $category as $cat ) {
						echo '<span><a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . '<span>' . $cat->name . '</span>' . '</a></span>' . $delimiter . ' ';
					}
				}

				echo get_the_title();
			}
		} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() && !is_search() ) {
			$post_type = get_post_type_object( get_post_type() );
			echo $post_type->labels->singular_name;
		} elseif ( is_attachment() ) {
			$parent = get_post( $post->post_parent );
			echo '<span><a href="' . esc_url( get_permalink( $parent ) ). '">' . '<span>' . $parent->post_title . '</span>' . '</a></span>';
			echo ' ' . $delimiter . ' ' . get_the_title();
		} elseif ( is_page() && !$post->post_parent ) {
			$get_post_slug	 = $post->post_name;
			$post_slug		 = str_replace( '-', ' ', $get_post_slug );
			echo '<span><a href="' . esc_url( get_permalink() ). '">' . '<span>' . ucfirst( $post_slug ) . '</span>' . '</a></span>';
		} elseif ( is_page() && $post->post_parent ) {
			$parent_id	 = $post->post_parent;
			$breadcrumbs = array();
			while ( $parent_id ) {
				$page			 = get_page( $parent_id );
				$breadcrumbs[]	 = '<span><a href="' . esc_url( get_permalink( $page->ID ) ) . '">' . '<span>' . get_the_title( $page->ID ) . '</span>' . '</a></span>';
				$parent_id		 = $page->post_parent;
			}

			$breadcrumbs = array_reverse( $breadcrumbs );
			for ( $i = 0; $i < count( $breadcrumbs ); $i++ ) {
				echo $breadcrumbs[ $i ];
				if ( $i != count( $breadcrumbs ) - 1 )
					echo ' ' . $delimiter . ' ';
			}

			echo $delimiter . '<span><a href="' . esc_url( get_permalink() ). '">' . '<span>' . the_title_attribute( 'echo=0' ) . '</span>' . '</a></span>';
		}
		elseif ( is_tag() ) {
			$tag_id = get_term_by( 'name', single_cat_title( '', false ), 'post_tag' );
			if ( $tag_id ) {
				$tag_link = get_tag_link( $tag_id->term_id );
			}

			echo '<span><a href="' . esc_url( $tag_link ). '">' . '<span>' . single_cat_title( '', false ) . '</span>' . '</a></span>';
		} elseif ( is_author() ) {
			global $author;
			$userdata = get_userdata( $author );
			echo '<span><a href="' . esc_url( get_author_posts_url( $userdata->ID ) ) . '">' . '<span>' . $userdata->display_name . '</span>' . '</a></span>';
		} elseif ( is_404() ) {
			echo __( 'Error 404', 'amigo' );
		} elseif ( is_search() ) {
			echo sprintf( __( 'Search Results for "%s"', 'amigo' ), get_search_query() );
		} elseif ( is_day() ) {
			echo '<span><a href="' . esc_url( get_year_link( get_the_time( 'Y' ) ) ) . '">' . '<span>' . get_the_time( __( 'Y', 'amigo' ) ) . '</span>' . '</a></span>' . $delimiter . ' ';
			echo '<span><a href="' . esc_url( get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) ) . '">' . '<span>' . get_the_time( __( 'F', 'amigo' ) ) . '</span>' . '</a></span>' . $delimiter . ' ';
			echo '<span><a href="' . esc_url( get_day_link( get_the_time( 'Y' ), get_the_time( 'm' ), get_the_time( 'd' ) ) ) . '">' . '<span>' . get_the_time( __( 'd', 'amigo' ) ) . '</span>' . '</a></span>';
		} elseif ( is_month() ) {
			echo '<span><a href="' . esc_url( get_year_link( get_the_time( 'Y' ) ) ) . '">' . '<span>' . get_the_time( __( 'Y', 'amigo' ) ) . '</span>' . '</a></span>' . $delimiter . ' ';
			echo '<span><a href="' . esc_url( get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) ) . '">' . '<span>' . get_the_time( __( 'F', 'amigo' ) ) . '</span>' . '</a></span>';
		} elseif ( is_year() ) {
			echo '<span><a href="' . esc_url( get_year_link( get_the_time( 'Y' ) ) ) . '">' . '<span>' . get_the_time( __( 'Y', 'amigo' ) ) . '</span>' . '</a></span>';
		}

		if ( get_query_var( 'paged' ) ) {
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() )
				echo ' (';
			echo __( 'Page', 'amigo' ) . ' ' . get_query_var( 'paged' );
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() )
				echo ')';
		}

		echo '</div></div>';
	}
}

////////////////////////////////////////////////////////////////////
// Social links
////////////////////////////////////////////////////////////////////
if ( !function_exists( 'amigo_social_links' ) ) :

	/**
	 * This function is for social links display on header
	 *
	 * Get links through Theme Options
	 */
	function amigo_social_links() {
		$twp_social_links	 = array( 'twp_social_facebook'	 => __( 'Facebook', 'amigo' ),
			'twp_social_twitter'	 => __( 'Twitter', 'amigo' ),
			'twp_social_google'		 => __( 'Google-Plus', 'amigo' ),
			'twp_social_instagram'	 => __( 'Instagram', 'amigo' ),
			'twp_social_pin'		 => __( 'Pinterest', 'amigo' ),
			'twp_social_youtube'	 => __( 'YouTube', 'amigo' ),
			'twp_social_reddit'		 => __( 'Reddit', 'amigo' ),
		);
		?>
		<div class="social-links">
			<ul>
				<?php
				$i					 = 0;
				$twp_links_output	 = '';
				foreach ( $twp_social_links as $key => $value ) {
					$link = get_theme_mod( $key, '' );
					if ( !empty( $link ) ) {
						$twp_links_output .=
						'<li><a href="' . esc_url( $link ) . '" target="_blank"><i class="fa fa-' . strtolower( $value ) . '"></i></a></li>';
					}
					$i++;
				}
				echo $twp_links_output;
				?>
			</ul>
		</div><!-- .social-links -->
		<?php
	}

endif;

////////////////////////////////////////////////////////////////////
// Remove banner from options page review 
////////////////////////////////////////////////////////////////////
add_filter( 'wp_review_remove_branding', '__return_true' );

////////////////////////////////////////////////////////////////////
// Rating functions  *wp_review_total / wp_review_user_reviews and  wp_review_type / wp_review_user_review_type
////////////////////////////////////////////////////////////////////
function amigo_rating( $postID, $class = 'wp_review_total', $type = 'wp_review_type' ) {
	if ( function_exists( 'mts_get_post_reviews' ) ) {
		$userTotal	 = $reviewtype	 = $total		 = '';
		$userTotal	 = get_post_meta( $postID, $class, true );
		$reviewtype	 = get_post_meta( $postID, $type, true );
		if ( $reviewtype != '' && !empty( $reviewtype ) && $userTotal != '' && !empty( $userTotal ) ) {
			if ( $class == 'wp_review_total' ) {
				$user_icon	 = 'fa fa-user';
				$title		 = __( 'Editor Rating', 'amigo' );
			} else {
				$user_icon	 = 'fa fa-users';
				$title		 = __( 'User Ratings', 'amigo' );
			}
			if ( $reviewtype == 'star' ) {
				$icon = 'fa-star';
			} elseif ( $reviewtype == 'point' ) {
				$icon = 'fa-dot-circle-o';
			} elseif ( $reviewtype == 'percentage' ) {
				$icon = 'fa-percent';
			} else {
				$icon = '';
			}
			echo '<span class="fa ' . $user_icon . '"></span><span class="review-meta users-total car" data-toggle="tooltip" data-placement="right" title="' . $title . '">' . esc_attr( $userTotal ) . '<i class="fa ' . $icon . '"></i></span>';
		}
	}
}

function amigo_review_show_total( $echo = true, $class = 'review-total-only', $post_id = null, $args = array() ) {
	global $post, $wp_review_rating_types;

	if ( empty( $post_id ) )
		$post_id = $post->ID;

	$type		 = wp_review_get_post_review_type( $post_id );
	$user_type	 = wp_review_get_post_user_review_type( $post_id );
	if ( !$type && !$user_type )
		return '';

	wp_enqueue_style( 'wp_review-style' );

	$options				 = get_option( 'wp_review_options' );
	$show_on_thumbnails_type = isset( $options[ 'show_on_thumbnails_type' ] ) ? $options[ 'show_on_thumbnails_type' ] : 'visitors';
	$show_on_thumbnails_type = apply_filters( 'wp_review_thumbnails_total', $show_on_thumbnails_type, $post_id, $args ); // will override option


	$total = get_post_meta( $post_id, 'wp_review_user_reviews', true );

	if ( $user_type == 'point' || $user_type == 'percentage' ) {
		$rating = sprintf( $wp_review_rating_types[ $user_type ][ 'value_text' ], $total );
	} else {
		$rating = wp_review_user_rating( $post_id );
	}
	$review = '';
	if ( !empty( $rating ) && !empty( $total ) ) {
		$review .= '<div class="review-type-' . $type . ' ' . esc_attr( $class ) . ' wp-review-show-total wp-review-total-' . $post_id . ' wp-review-total-' . $type . '"> ';
		$review .= $rating;
		$review .= '</div>';
	}
	$review	 = apply_filters( 'wp_review_show_total', $review, $post_id, $type, $total );
	$review	 = apply_filters( 'wp_review_total_output', $review, $post_id, $type, $total, $class, $args );

	if ( $echo )
		echo $review;
	else
		return $review;
}
