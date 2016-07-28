<?php
//info: get where the map is being used
function lmm_get_map_shortcodes($id, $type){
global $wpdb;
$lmm_options = get_option( 'leafletmapsmarker_options' );
$shortcode = '[' . $lmm_options['shortcode'] . ' ' . $type . '="' . $id . '"';
$posts = $wpdb->get_results( $wpdb->prepare("SELECT ID,post_title FROM $wpdb->posts WHERE (post_type='post' OR post_type='page') AND post_content LIKE %s ",'%'.$shortcode.'%') );
$result = '<ul style="margin:0px;">';
foreach ($posts as $post) {
	if ($post->post_title != NULL) { $post_title = $post->post_title; } else { $post_title = 'ID ' . $post->ID; }
	$result .= '<li style="margin-bottom:0px;clear:both;">' . ucfirst(get_post_type($post->ID)) . ': <a href="' . get_permalink($post->ID) . '" title="' . esc_attr__('view content','lmm') . '" target="_blank">' . $post_title . '</a>';
	if(current_user_can('edit_others_posts')){
		$result .= '<a style="float:right;" href="' . get_edit_post_link( $post->ID ) . '"> ('.  __('edit','lmm')  . ')</a>';
	}
	$result .= '</li>';
}
$widgets = get_option('widget_text');
if (!empty($widgets)):
	foreach ($widgets as $w_key => $widget) {
		$shortcode = '['.$lmm_options['shortcode'].' '.$type.'="'.$id.'"]';
		if (is_array($widget)) {
			if(isset($widget['text']) && $widget['text']!= ''){
				if(strpos($shortcode, $widget['text']) !== FALSE) {
					$result .= '<li style="margin-bottom:0px;">';
						$result .= sprintf(__('Found in a <a href="%1$s">widget</a>'), admin_url('widgets.php')) . '</a>';
					$result .= '</li>';
				}
			}
		}
	}
endif;
$result .= '</ul>';
if ($result == '<ul style="margin:0px;"></ul>') {
	$result = __('not used in any content','lmm');
}
return $result;
}