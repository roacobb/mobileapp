<?php
/**
 * Cardio Theme Customizer
 *
 * @package Cardio
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function cardio_customize_register( $wp_customize ) {
	
function cardio_sanitize_dropdown_pages( $page_id, $setting ) {
	// Ensure $input is an absolute integer.
	$page_id = absint( $page_id );
	
	// If $page_id is an ID of a published page, return it; otherwise, return the default.
	return ( 'publish' == get_post_status( $page_id ) ? $page_id : $setting->default );
}

function cardio_sanitize_checkbox( $input ) {
    if ( $input == 1 ) {
        return 1;
    } else {
        return '';
    }
}

//Add a class for titles
    class cardio_info extends WP_Customize_Control {
        public $type = 'info';
        public $label = '';
        public function render_content() {
        ?>
			<h3 style="text-decoration: underline; color: #DA4141; text-transform: uppercase;"><?php echo esc_html( $this->label ); ?></h3>
        <?php
        }
    }
	
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	
	
	$wp_customize->add_setting('color_scheme', array(
		'default' => '#e25050',
		'sanitize_callback'	=> 'sanitize_hex_color',
	));
	
	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'color_scheme',array(
			'label' => __('Color Scheme','cardio'),
			'description'	=> __('<strong>Change color.</strong>','cardio'),
			'section' => 'colors',
			'settings' => 'color_scheme'
		))
	);
	
	// Slider Section Start		
	$wp_customize->add_section(
        'slider_section',
        array(
            'title' => __('Slider Settings', 'cardio'),
            'priority' => null,
			'description'	=> __('Recommended image size (1420x567)','cardio'),	
        )
    );
	
	$wp_customize->add_setting('page-setting7',array(
			'default' => '0',
			'capability' => 'edit_theme_options',
			'sanitize_callback'	=> 'absint'
	));
	
	$wp_customize->add_control('page-setting7',array(
			'type'	=> 'dropdown-pages',
			'label'	=> __('Select page for slide one:','cardio'),
			'section'	=> 'slider_section'
	));	
	
	$wp_customize->add_setting('page-setting8',array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback'	=> 'absint'
	));
	
	$wp_customize->add_control('page-setting8',array(
			'type'	=> 'dropdown-pages',
			'label'	=> __('Select page for slide two:','cardio'),
			'section'	=> 'slider_section'
	));	
	
	$wp_customize->add_setting('page-setting9',array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback'	=> 'absint'
	));
	
	$wp_customize->add_control('page-setting9',array(
			'type'	=> 'dropdown-pages',
			'label'	=> __('Select page for slide three:','cardio'),
			'section'	=> 'slider_section'
	));	
	
	
	$wp_customize->add_setting('hide_slider',array(
			'default' => true,
			'sanitize_callback' => 'cardio_sanitize_checkbox',
			'capability' => 'edit_theme_options',
	));	 

	$wp_customize->add_control( 'hide_slider', array(
		   'settings' => 'hide_slider',
    	   'section'   => 'slider_section',
    	   'label'     => __('Check this to hide slider','cardio'),
    	   'type'      => 'checkbox'
     ));
	 
	 $wp_customize->add_setting('slidelink_text',array(
	 		'default'	=> __('Read More','cardio'),
			'sanitize_callback'	=> 'sanitize_text_field'
	 ));
	 
	 $wp_customize->add_control('slidelink_text',array(
	 		'settings'	=> 'slidelink_text',
			'section'	=> 'slider_section',
			'label'		=> __('Add text for slide link button','cardio'),
			'type'		=> 'text'
	 ));	
	
	// Slider Section End
	
	$wp_customize->add_section(
        'welcome_section',
        array(
            'title' => __('Welcome Section', 'cardio'),
            'priority' => null,
			'description'	=> __('Add welcome box content here.','cardio'),	
        )
    );
	
	$wp_customize->add_setting('welcome_title',array(
			'default'	=> __('Welcome to Fitnesstime','cardio'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('welcome_title',array(
			'settings'	=> 'welcome_title',
			'section'	=> 'welcome_section',
			'label'		=> __('Add welcome title and subtitle here.','cardio'),
			'type'		=> 'text'
	));
	
	$wp_customize->add_setting('welcome_subtitle',array(
			'default'	=> __('Take care of your body. Its the only place you have to live.','cardio'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('welcome_subtitle',array(
			'settings'	=> 'welcome_subtitle',
			'section'	=> 'welcome_section',
			'type'		=> 'text'
	));
	
	$wp_customize->add_setting('hide_title',array(
			'default' => false,
			'sanitize_callback' => 'cardio_sanitize_checkbox',
			'capability' => 'edit_theme_options',
	));	 

	$wp_customize->add_control( 'hide_title', array(
		   'settings' => 'hide_title',
    	   'section'   => 'welcome_section',
    	   'label'     => __('Check this to display title and subtitle','cardio'),
    	   'type'      => 'checkbox'
     ));
		
	// Page settings 
	$wp_customize->add_setting(
    'page-setting1',
		array(
			'sanitize_callback' => 'cardio_sanitize_dropdown_pages',
		)
	);
 
	$wp_customize->add_control(
		'page-setting1',
		array(
			'type' => 'dropdown-pages',
			'label' => esc_html__('Choose a page for box one:','cardio'),
			'section' => 'welcome_section',
		)
	);
	
	$wp_customize->add_setting(
    'page-setting2',
		array(
			'sanitize_callback' => 'cardio_sanitize_dropdown_pages',
		)
	);
	
	$wp_customize->add_control(
		'page-setting2',
		array(
			'type' => 'dropdown-pages',
			'label' => esc_html__('Choose a page for box Two:','cardio'),
			'section' => 'welcome_section',
		)
	);
	
	$wp_customize->add_setting(
    'page-setting3',
		array(
			'sanitize_callback' => 'cardio_sanitize_dropdown_pages',
		)
	);
	
	$wp_customize->add_control(
		'page-setting3',
		array(
			'type' => 'dropdown-pages',
			'label' => esc_html__('Choose a page for box Three:','cardio'),
			'section' => 'welcome_section',
		)
	);
	
	$wp_customize->add_setting(
    'page-setting4',
		array(
			'sanitize_callback' => 'cardio_sanitize_dropdown_pages',
		)
	);
	
	$wp_customize->add_control(
		'page-setting4',
		array(
			'type' => 'dropdown-pages',
			'label' => esc_html__('Choose a page for box Four:','cardio'),
			'section' => 'welcome_section',
		)
	);
	
	$wp_customize->add_setting('box_hide',array(
		'sanitize_callback'	=> 'cardio_sanitize_checkbox'
	));
	
	$wp_customize->add_control('box_hide',array(
			'label'	=> __('Check this to hide services box','cardio'),
			'setting'	=> 'box_hide',
			'section'	=> 'welcome_section',
			'type'	=> 'checkbox'
	));
	
}
add_action( 'customize_register', 'cardio_customize_register' );

function cardio_css(){
		?>
        <style>
				a, 
				.tm_client strong,
				.postmeta a:hover,
				#sidebar ul li a:hover,
				.blog-post h3.entry-title,
				.woocommerce ul.products li.product .price,
				.top-social a:hover,
				.footer-menu ul li a:hover,
				.logo a:hover{
					color:<?php echo esc_html(get_theme_mod('color_scheme','#e25050')); ?>;
				}
				a.blog-more:hover,
				.nav-links .current, 
				.nav-links a:hover,
				#commentform input#submit,
				input.search-submit,
				.nivo-controlNav a.active,
				.blog-date .date,
				a.read-more:hover,
				.main-nav ul li a:hover,
				#slider .top-bar h2,
				.entry-content p input[type="submit"],
				.main-nav ul li:hover a.parent,
				.toggle a{
					background-color:<?php echo esc_html(get_theme_mod('color_scheme','#e25050')); ?>;
				}
				.welcome-box{
					border:1px solid <?php echo esc_html(get_theme_mod('color_scheme','#e25050')); ?>;
				}
				@media screen and (min-width:1025px){
					.main-nav ul li:hover ul{background-color:<?php echo esc_html(get_theme_mod('color_scheme','#e25050')); ?>;}
				}
		</style>
	<?php }
add_action('wp_head','cardio_css');

function cardio_custom_customize_enqueue() {
	wp_enqueue_script( 'cardio-custom-customize', get_template_directory_uri() . '/js/custom.customize.js', array( 'jquery', 'customize-controls' ), false, true );
	wp_localize_script( 'cardio-custom-customize', 'cardiojsvar', array(
	'upgrade' => __('Upgrade to PRO Version', 'cardio')
	));

}
add_action( 'customize_controls_enqueue_scripts', 'cardio_custom_customize_enqueue' );