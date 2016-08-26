<?php
/**
 * The main Kirki object
 *
 * @package     Kirki
 * @category    Core
 * @author      Aristeides Stathopoulos
 * @copyright   Copyright (c) 2015, Aristeides Stathopoulos
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Early exit if the class already exists
if ( class_exists( 'Kirki_Toolkit' ) ) {
	return;
}

class Kirki_Toolkit {

	/** @var Kirki The only instance of this class */
	public static $instance = null;

	public static $version = '1.0.2';

	public $font_registry = null;
	public $scripts       = null;
	public $api           = null;
	public $styles        = array();

	/**
	 * Access the single instance of this class
	 * @return Kirki
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new Kirki_Toolkit();
		}
		return self::$instance;
	}

	/**
	 * Shortcut method to get the translation strings
	 */
	public static function i18n() {

		$i18n = array(
			'background-color'      => __( 'Background Color', 'i-max' ),
			'background-image'      => __( 'Background Image', 'i-max' ),
			'no-repeat'             => __( 'No Repeat', 'i-max' ),
			'repeat-all'            => __( 'Repeat All', 'i-max' ),
			'repeat-x'              => __( 'Repeat Horizontally', 'i-max' ),
			'repeat-y'              => __( 'Repeat Vertically', 'i-max' ),
			'inherit'               => __( 'Inherit', 'i-max' ),
			'background-repeat'     => __( 'Background Repeat', 'i-max' ),
			'cover'                 => __( 'Cover', 'i-max' ),
			'contain'               => __( 'Contain', 'i-max' ),
			'background-size'       => __( 'Background Size', 'i-max' ),
			'fixed'                 => __( 'Fixed', 'i-max' ),
			'scroll'                => __( 'Scroll', 'i-max' ),
			'background-attachment' => __( 'Background Attachment', 'i-max' ),
			'left-top'              => __( 'Left Top', 'i-max' ),
			'left-center'           => __( 'Left Center', 'i-max' ),
			'left-bottom'           => __( 'Left Bottom', 'i-max' ),
			'right-top'             => __( 'Right Top', 'i-max' ),
			'right-center'          => __( 'Right Center', 'i-max' ),
			'right-bottom'          => __( 'Right Bottom', 'i-max' ),
			'center-top'            => __( 'Center Top', 'i-max' ),
			'center-center'         => __( 'Center Center', 'i-max' ),
			'center-bottom'         => __( 'Center Bottom', 'i-max' ),
			'background-position'   => __( 'Background Position', 'i-max' ),
			'background-opacity'    => __( 'Background Opacity', 'i-max' ),
			'ON'                    => __( 'ON', 'i-max' ),
			'OFF'                   => __( 'OFF', 'i-max' ),
			'all'                   => __( 'All', 'i-max' ),
			'cyrillic'              => __( 'Cyrillic', 'i-max' ),
			'cyrillic-ext'          => __( 'Cyrillic Extended', 'i-max' ),
			'devanagari'            => __( 'Devanagari', 'i-max' ),
			'greek'                 => __( 'Greek', 'i-max' ),
			'greek-ext'             => __( 'Greek Extended', 'i-max' ),
			'khmer'                 => __( 'Khmer', 'i-max' ),
			'latin'                 => __( 'Latin', 'i-max' ),
			'latin-ext'             => __( 'Latin Extended', 'i-max' ),
			'vietnamese'            => __( 'Vietnamese', 'i-max' ),
			'serif'                 => _x( 'Serif', 'font style', 'i-max' ),
			'sans-serif'            => _x( 'Sans Serif', 'font style', 'i-max' ),
			'monospace'             => _x( 'Monospace', 'font style', 'i-max' ),
		);

		$config = apply_filters( 'kirki/config', array() );

		if ( isset( $config['i18n'] ) ) {
			$i18n = wp_parse_args( $config['i18n'], $i18n );
		}

		return $i18n;

	}

	/**
	 * Shortcut method to get the font registry.
	 */
	public static function fonts() {
		return self::get_instance()->font_registry;
	}

	/**
	 * Constructor is private, should only be called by get_instance()
	 */
	private function __construct() {
	}

}
