<?php
	//ini_set('display_errors', 1);


	if(array_key_exists('callback', $_GET)) {
		$callback = sanitize_text_field($_GET['callback']);		
	}

	if(isset($callback)) {
		echo $callback."(";
	}

	ob_start();

	$body_content = ob_get_clean();

	$data = array('body_content' => $body_content);
	echo json_encode($data);
	if(isset($callback)) {
		echo ")";
	}

?>