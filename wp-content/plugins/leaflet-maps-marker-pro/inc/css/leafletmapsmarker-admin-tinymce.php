<?php
//info: construct path to wp-load.php
while(!is_file('wp-load.php')) {
	if(is_dir('..' . DIRECTORY_SEPARATOR)) chdir('..' . DIRECTORY_SEPARATOR);
	else die('Error: Could not construct path to wp-load.php - please check <a href="https://www.mapsmarker.com/path-error">https://www.mapsmarker.com/path-error</a> for more details');
}
include( 'wp-load.php' );

global $current_user,$wp_version;
$lmm_options = get_option( 'leafletmapsmarker_options' );
$tinymce_custom_css_nonce = (isset($_GET['nonce']) ? $_GET['nonce'] : '');

if ( (current_user_can($lmm_options[ 'capabilities_view_others' ])) && (wp_verify_nonce($tinymce_custom_css_nonce, 'tinymce-custom-css-nonce')) ) {

	header('Content-Type: text/css; charset=UTF-8'); //info: to prevent console warning on chrome
	if (version_compare($wp_version,"3.9-alpha",">=")){
		echo "
		a { text-decoration:none; }
		a:hover { text-decoration:underline; }
		img { " . $lmm_options['defaults_marker_popups_image_css'] . " }
		";
	} else {
		echo "
		html .mcecontentbody {
			font: 12px/1.4 'Helvetica Neue',Arial,Helvetica,sans-serif;
			max-width:" . intval($lmm_options['defaults_marker_popups_maxwidth']) . "px;
			word-wrap: break-word;
		}
		.mcecontentbody a {
			text-decoration:none;
		}
		.mcecontentbody a:hover {
			text-decoration:underline;
		}
		.mcecontentbody img {
			" . $lmm_options['defaults_marker_popups_image_css'] . "
		}
		";
	}
}
//info: no security check failed message in order not to break TinyMCE CSS on errors
?>