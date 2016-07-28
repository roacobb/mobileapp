<?php

	// prevent newrelic injected JavaScript breaking JSON
	if(extension_loaded('newrelic')){
	    newrelic_disable_autorum();
	}



	if(get_option('ml_debug') == 'true'){
		ini_set('display_errors', true);
	}

	if(!$post_id){
		$post_id = htmlspecialchars(esc_attr(sanitize_text_field($_GET['post_id']))); // sanitize
        $post = get_post($post_id);
	}

	if(!$post){
		header("HTTP/1.1 404 Not Found");
	}

	$ltr_rtl = get_option('ml_rtl_text_enable') == '1' ? 'rtl' : 'ltr';


?><!DOCTYPE html><html dir="<?php echo $ltr_rtl; ?>"><head><meta charset="utf-8"><meta name="description" content=""><meta name="keywords" content=""><meta name="language" content="en"/><meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1, user-scalable=no">
<link href="<?php echo plugins_url('wp2android-mobile-app-plugin/post/css/styles.css'); ?>" rel="stylesheet" media="all" /><link href="<?php echo plugins_url('wp2android-mobile-app-plugin/post/css/_typeplate.css'); ?>" rel="stylesheet" media="all" />
<?php
	$custom_css = stripslashes(get_option('ml_post_custom_css'));
	echo $custom_css ? '<style type="text/css" media="screen">' . $custom_css . '</style>' : '';

	$custom_js = stripslashes(get_option('ml_post_custom_js'));


	eval(stripslashes(get_option('ml_post_head'))); // PHP in HEAD
?>
</head><body class="mb_body">
<?php

?>
</body></html>
