<?php
/*
    List all layers - Maps Marker Pro
*/
//info prevent file from being accessed directly
if (basename($_SERVER['SCRIPT_FILENAME']) == 'leaflet-list-layers.php') { die ("Please do not access this file directly. Thanks!<br/><a href='https://www.mapsmarker.com/go'>www.mapsmarker.com</a>"); }
require_once( LEAFLET_PLUGIN_DIR . 'inc' . DIRECTORY_SEPARATOR . 'globals-backend.php' );

global $wpdb, $current_user;
$table_name_layers = $wpdb->prefix.'leafletmapsmarker_layers';
$table_name_markers = $wpdb->prefix.'leafletmapsmarker_markers';
$lmm_options = get_option( 'leafletmapsmarker_options' );
include('inc' . DIRECTORY_SEPARATOR . 'admin-header.php');
$duplicateselected = ( isset($_POST['bulkactions-layers']) && ($_POST['bulkactions-layers'] == 'duplicateselected') ) ? '1' : '0';
$duplicatelayerandmarkers = ( isset($_POST['bulkactions-layers']) && ($_POST['bulkactions-layers'] == 'duplicatelayerandmarkers') ) ? '1' : '0';
$deleteselected = ( isset($_POST['bulkactions-layers']) && ($_POST['bulkactions-layers'] == 'deleteselected') ) ? '1' : '0';
$deleteassignselected = ( isset($_POST['bulkactions-layers']) && ($_POST['bulkactions-layers'] == 'deleteassignselected') ) ? '1' : '0';
$massactionnonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : (isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '');
if ( ($deleteselected == '1') && isset($_POST['checkedlayers']) && current_user_can( $lmm_options[ 'capabilities_delete_others' ]) ) {
	if (! wp_verify_nonce($massactionnonce, 'massaction-nonce') ) die('<br/>'.__('Security check failed - please call this function from the according admin page!','lmm').'');
	$checked_layers_prepared = implode(",", $_POST['checkedlayers']);
	$checked_layers = preg_replace('/[a-z|A-Z| |\=]/', '', $checked_layers_prepared);
	$layers = explode(',', htmlspecialchars($checked_layers));
	foreach($layers as $l){
		$multi_layer_map_list_exploded = $wpdb->get_var($wpdb->prepare('SELECT l.multi_layer_map_list FROM `'.$table_name_layers.'` as l WHERE l.id=%d',$l));
		if(!is_null($multi_layer_map_list_exploded)){
			$multi_layer_map_list_exploded = explode(",", $multi_layer_map_list_exploded);
		}
		if(is_null($multi_layer_map_list_exploded)){
			$markers_to_delete = $wpdb->get_results("SELECT id,layer FROM `$table_name_markers` WHERE `layer` LIKE '%\"".$l."\"%'");
		} else {
			//info: delete markers of mlm layers
			$markers_to_delete = array();
			foreach($multi_layer_map_list_exploded as $lid){
				$markers = $wpdb->get_results("SELECT id,layer FROM `$table_name_markers` WHERE `layer` LIKE '%\"".$lid."\"%'");
				if(!is_null($markers)){
					$markers_to_delete = array_merge($markers_to_delete,$markers);
				}
			}
		}
		foreach($markers_to_delete as $row){
			$layer = json_decode($row->layer);
			if(count($layer) === 1){
				$wpdb->query("DELETE FROM `$table_name_markers` WHERE `id` =".$row->id);
			} else {
				if(!is_null($multi_layer_map_list_exploded)){
					foreach($multi_layer_map_list_exploded as $mlm_layer){
						$key = array_search($mlm_layer, $layer);
						if($key !== FALSE)
							unset( $layer[$key] );
					}
				}
				$key = array_search($l, $layer);
				if($key !== FALSE)
					unset($layer[$key]);
				$layer = array_values( $layer );
				$wpdb->update($table_name_markers,array('layer' => json_encode($layer) ), array('id' => $row->id));
			}
		}
	}
	if(!is_null($multi_layer_map_list_exploded)){
		$wpdb->query( "DELETE FROM `$table_name_layers` WHERE `id` IN (" . htmlspecialchars(implode(',',$multi_layer_map_list_exploded)) . ")");
	}
	$wpdb->query("DELETE FROM `$table_name_layers` WHERE `id` IN (" . htmlspecialchars($checked_layers) . ")");
	$wpdb->query("OPTIMIZE TABLE `$table_name_layers`");
	$wpdb->query("OPTIMIZE TABLE `$table_name_markers`");
	echo '<p><div class="notice notice-success is-dismissible" style="padding:10px;">' . sprintf(__('The selected layers (IDs %s) and all assigned markers have been deleted (or the reference to the layer has been removed if marker was assigned to multiple layers)','lmm'), htmlspecialchars($checked_layers)) . ' ' . __('as well as optionally included sublayers if multi-layer-maps were selected','lmm') . '</div>';
} else if ( ($deleteassignselected == '1') && isset($_POST['checkedlayers']) && current_user_can( $lmm_options[ 'capabilities_edit_others' ]) ) {
	if (! wp_verify_nonce($massactionnonce, 'massaction-nonce') ) die('<br/>'.__('Security check failed - please call this function from the according admin page!','lmm').'');
	$checked_layers_prepared = implode(",", $_POST['checkedlayers']);
	$checked_layers = preg_replace('/[a-z|A-Z| |\=]/', '', $checked_layers_prepared);
	foreach ($_POST['checkedlayers'] as $row){
		$multi_layer_map_list_exploded = $wpdb->get_var($wpdb->prepare('SELECT l.multi_layer_map_list FROM `'.$table_name_layers.'` as l WHERE l.id = %d ', $row));
		if(!is_null($multi_layer_map_list_exploded)){
			$multi_layer_map_list_exploded = explode(",", $multi_layer_map_list_exploded);
		}
		if(is_null($multi_layer_map_list_exploded)){
			$original_markers = $wpdb->get_results("SELECT id,layer FROM $table_name_markers WHERE layer LIKE '%\"". $row ."\"%' ");
		} else {
			//info: delete markers of mlm layers
			$original_markers = array();
			foreach($multi_layer_map_list_exploded as $lid){
				$markers = $wpdb->get_results(" SELECT id,layer FROM `$table_name_markers` WHERE `layer` LIKE '%\"".$lid."\"%' ");
				if(!is_null($markers)){
					$original_markers =  array_merge($original_markers, $markers);
				}
			}
		}
		foreach($original_markers as $marker){
			$new_layer = json_decode($marker->layer, true);
			$key = array_search($row, $new_layer);
			unset( $new_layer[$key] );
			if(!is_null($multi_layer_map_list_exploded)){
				foreach($multi_layer_map_list_exploded as $l){
					$key = array_search($l, $new_layer);
					unset( $new_layer[$key] );
				}
			}
			if(array_search($_POST['layer'], $new_layer)===FALSE){
				$new_layer[] = $_POST['layer'];
			}
			$new_layer = array_values( $new_layer );
			$wpdb->query("UPDATE $table_name_markers SET layer = '" . json_encode($new_layer) . "' where id = " . $marker->id . "");

		}
	}
	if(!is_null($multi_layer_map_list_exploded)){
		$wpdb->query("DELETE FROM `$table_name_layers` WHERE `id` IN (" . htmlspecialchars(implode(',',$multi_layer_map_list_exploded)) . ")");
	}
	$wpdb->query("DELETE FROM `$table_name_layers` WHERE `id` IN (" . htmlspecialchars($checked_layers) . ")");

	echo '<p><div class="notice notice-success is-dismissible" style="padding:10px;">' . sprintf(__('The selected layers (IDs %1$s) have been deleted and the markers have been assigned to %2$s','lmm'), htmlspecialchars($checked_layers) .' ' . __('as well as optionally included sublayers if multi-layer-maps were selected','lmm'), htmlspecialchars($_POST['layer'])) . '</div>';
} else if ( ($duplicateselected == '1') && isset($_POST['checkedlayers']) && current_user_can( $lmm_options[ 'capabilities_edit_others' ]) ) {
	if (! wp_verify_nonce($massactionnonce, 'massaction-nonce') ) die('<br/>'.__('Security check failed - please call this function from the according admin page!','lmm').'');
	global $current_user;
	$selected_layers = $_POST['checkedlayers'];
	$new_layer_ids = array();
	foreach ($_POST['checkedlayers'] as $row){
		$result = $wpdb->get_row( $wpdb->prepare('SELECT * FROM `'.$table_name_layers.'` WHERE `id`= %d',$row), ARRAY_A);
		$sql_duplicate = $wpdb->prepare( "INSERT INTO `$table_name_layers` (`name`, `basemap`, `layerzoom`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `layerviewlat`, `layerviewlon`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `listmarkers`, `multi_layer_map`, `multi_layer_map_list`, `address`, `clustering`, `gpx_url`, `gpx_panel`) VALUES (%s, %s, %d, %d, %s, %d, %d, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %d, %s, %d)", $result['name'], $result['basemap'], $result['layerzoom'], $result['mapwidth'], $result['mapwidthunit'], $result['mapheight'], $result['panel'], $result['layerviewlat'], $result['layerviewlon'], $current_user->user_login, current_time('mysql',0), $current_user->user_login, current_time('mysql',0), $result['conrolbox'], $result['overlays_custom'], $result['overlays_custom2'], $result['overlays_custom3'], $result['overlays_custom4'], $result['wms'], $result['wms2'], $result['wms3'], $result['wms4'], $result['wms5'], $result['wms6'], $result['wms7'], $result['wms8'], $result['wms9'], $result['wms10'], $result['listmarkers'], $result['multi_layer_map'], $result['multi_layer_map_list'], $result['address'], $result['clustering'], $result['gpx_url'], $result['gpx_panel'] );
		$wpdb->query( $sql_duplicate );
		$new_layer_ids[] = $wpdb->insert_id;
	}
	$wpdb->query( "OPTIMIZE TABLE `$table_name_layers`" );
	$checked_layers_prepared = implode(",", $_POST['checkedlayers']);
	$checked_layers = preg_replace('/[a-z|A-Z| |\=]/', '', $checked_layers_prepared);
	echo '<p><div class="notice notice-success is-dismissible" style="padding:10px;">' . __('The selected layers have been duplicated. New layer IDs:','lmm') . ' ';
	foreach ($new_layer_ids as $new_id){
		echo '<a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $new_id . '">' . $new_id . '</a> - ';
	}
	echo '</div>';
} else if ( ($duplicatelayerandmarkers == '1') && isset($_POST['checkedlayers']) && current_user_can( $lmm_options[ 'capabilities_edit_others' ]) ) {
	if (! wp_verify_nonce($massactionnonce, 'massaction-nonce') ) die('<br/>'.__('Security check failed - please call this function from the according admin page!','lmm').'');
	global $current_user;
	$selected_layers = $_POST['checkedlayers'];
	$new_layer_ids = array();
	foreach ($_POST['checkedlayers'] as $row){
		$result = $wpdb->get_row( $wpdb->prepare('SELECT * FROM `'.$table_name_layers.'` WHERE `id`= %d',$row), ARRAY_A);
		$sql_duplicate = $wpdb->prepare( "INSERT INTO `$table_name_layers` (`name`, `basemap`, `layerzoom`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `layerviewlat`, `layerviewlon`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `listmarkers`, `multi_layer_map`, `multi_layer_map_list`, `address`, `clustering`, `gpx_url`, `gpx_panel`) VALUES (%s, %s, %d, %d, %s, %d, %d, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %d, %s, %d)", $result['name'], $result['basemap'], $result['layerzoom'], $result['mapwidth'], $result['mapwidthunit'], $result['mapheight'], $result['panel'], $result['layerviewlat'], $result['layerviewlon'], $current_user->user_login, current_time('mysql',0), $current_user->user_login, current_time('mysql',0), $result['conrolbox'], $result['overlays_custom'], $result['overlays_custom2'], $result['overlays_custom3'], $result['overlays_custom4'], $result['wms'], $result['wms2'], $result['wms3'], $result['wms4'], $result['wms5'], $result['wms6'], $result['wms7'], $result['wms8'], $result['wms9'], $result['wms10'], $result['listmarkers'], $result['multi_layer_map'], $result['multi_layer_map_list'], $result['address'], $result['clustering'], $result['gpx_url'], $result['gpx_panel'] );
		$wpdb->query( $sql_duplicate );
		$new_layer_ids[] = $wpdb->insert_id;
		$layer_id = $wpdb->insert_id;
		//info: duplicate the markers.
		$duplicated_markers = $wpdb->get_results("SELECT * FROM `$table_name_markers` WHERE layer LIKE '%\"". $row ."\"%'", ARRAY_A);
		foreach($duplicated_markers as $result){
			$layer_duplicate = json_encode(array(strval($layer_id)));
			if ($result['kml_timestamp'] == NULL) {
				$sql_duplicate = $wpdb->prepare( "INSERT INTO `$table_name_markers` (`markername`, `basemap`, `layer`, `lat`, `lon`, `icon`, `popuptext`, `zoom`, `openpopup`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `address`, `gpx_url`, `gpx_panel`) VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %s, %d, %d, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %d )", $result['markername'], $result['basemap'], $layer_duplicate, $result['lat'], $result['lon'], $result['icon'], $result['popuptext'], $result['zoom'], $result['openpopup'], $result['mapwidth'], $result['mapwidthunit'], $result['mapheight'], $result['panel'], $current_user->user_login, current_time('mysql',0), $current_user->user_login, current_time('mysql',0), $result['controlbox'], $result['overlays_custom'], $result['overlays_custom2'], $result['overlays_custom3'], $result['overlays_custom4'], $result['wms'], $result['wms2'], $result['wms3'], $result['wms4'], $result['wms5'], $result['wms6'], $result['wms7'], $result['wms8'], $result['wms9'], $result['wms10'], $result['address'], $result['gpx_url'], $result['gpx_panel'] );
			} else if ($result['kml_timestamp'] != NULL) {
				$sql_duplicate = $wpdb->prepare( "INSERT INTO `$table_name_markers` (`markername`, `basemap`, `layer`, `lat`, `lon`, `icon`, `popuptext`, `zoom`, `openpopup`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `kml_timestamp`, `address`, `gpx_url`, `gpx_panel`) VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %s, %d, %d, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s, %d )", $result['markername'], $result['basemap'], $layer_duplicate, $result['lat'], $result['lon'], $result['icon'], $result['popuptext'], $result['zoom'], $result['openpopup'], $result['mapwidth'], $result['mapwidthunit'], $result['mapheight'], $result['panel'], $current_user->user_login, current_time('mysql',0), $current_user->user_login, current_time('mysql',0), $result['controlbox'], $result['overlays_custom'], $result['overlays_custom2'], $result['overlays_custom3'], $result['overlays_custom4'], $result['wms'], $result['wms2'], $result['wms3'], $result['wms4'], $result['wms5'], $result['wms6'], $result['wms7'], $result['wms8'], $result['wms9'], $result['wms10'], $result['kml_timestamp'], $result['address'], $result['gpx_url'], $result['gpx_panel'] );
			}
			$wpdb->query( $sql_duplicate );
			$new_marker_ids[] = $wpdb->insert_id;
		}

	}
	$wpdb->query( "OPTIMIZE TABLE `$table_name_layers`" );
	$checked_layers_prepared = implode(",", $_POST['checkedlayers']);
	$checked_layers = preg_replace('/[a-z|A-Z| |\=]/', '', $checked_layers_prepared);
	echo '<p><div class="notice notice-success is-dismissible" style="padding:10px;">' . __('The selected layers have been duplicated. New layer IDs:','lmm') . ' ';
	foreach ($new_layer_ids as $new_id){
		echo '<a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $new_id . '">' . $new_id . '</a> - ';
	}
	if(isset($new_marker_ids) && !empty($new_marker_ids)){
		echo '<p>'.__('new markers IDs').'</p>';
		foreach ($new_marker_ids as $new_id){
			echo '<a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id=' . $new_id . '">' . $new_id . '</a> - ';
		}
	}
	echo '</div>';
	} else {
	?>
	<h1><?php _e('List all layers','lmm') ?></h1>
	<?php
	//info: security check if input variable is valid
	$columnsort_values = array('id','multi_layer_map','name','m.panel','zoom','basemap','createdon','createdby','updatedon','updatedby','controlbox');
	$columnsort_input = isset($_GET['orderby']) ? esc_sql($_GET['orderby']) : $lmm_options[ 'misc_layer_listing_sort_order_by' ];
	$columnsort = (in_array($columnsort_input, $columnsort_values)) ? $columnsort_input : $lmm_options[ 'misc_layer_listing_sort_order_by' ];
	//info: security check if input variable is valid
	$columnsortorder_values = array('asc','desc','ASC','DESC');
	$columnsortorder_input = isset($_GET['order']) ? esc_sql($_GET['order']) : $lmm_options[ 'misc_layer_listing_sort_sort_order' ];
	$columnsortorder = (in_array($columnsortorder_input, $columnsortorder_values)) ? $columnsortorder_input : $lmm_options[ 'misc_layer_listing_sort_sort_order' ];

	$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
	$searchtext = isset($_POST['searchtext']) ? '%' .esc_sql($_POST['searchtext']) . '%' : (isset($_GET['searchtext']) ? '%' . esc_sql($_GET['searchtext']) : '') . '%';
	if ($action == 'search') {
		$layersearchnonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
		if (! wp_verify_nonce($layersearchnonce, 'layersearch-nonce') ) die('<br/>'.__('Security check failed - please call this function from the according admin page!','lmm').'');
		$lcount = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `$table_name_layers` WHERE id like '%s' OR name like '%s' OR address like '%s'", $searchtext, $searchtext, $searchtext)));
		$layerlist = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `$table_name_layers` WHERE id like '%s' OR name like '%s' OR address like '%s' order by $columnsort $columnsortorder", $searchtext, $searchtext, $searchtext), ARRAY_A);
	} else {
		$capabilities_view = (isset($lmm_options['capabilities_view_others']))?$lmm_options['capabilities_view_others']:'edit_posts';
		$createdby_query = (!current_user_can($capabilities_view))?" createdby = '". $current_user->user_login ."' ":'1=1';
		$layerlist = $wpdb->get_results( "SELECT * FROM `$table_name_layers` WHERE $createdby_query AND `id`>0 ORDER BY `$columnsort` $columnsortorder", ARRAY_A );
		$lcount = intval($wpdb->get_var('SELECT COUNT(*)-1 FROM '.$table_name_layers.' WHERE '.$createdby_query));
		$lcount = ($lcount == -1)?$lcount+1:$lcount;
	}
	?>
	<div style="float:right;">
	<?php $nonce= wp_create_nonce  ('layersearch-nonce'); ?>
		<form method="POST">
			<?php wp_nonce_field('layersearch-nonce'); ?>
			<input type="hidden" name="action" value="search" />
			<input type="text" id="searchtext" name="searchtext" value="<?php echo (isset($_POST['searchtext']) != NULL) ? htmlspecialchars(stripslashes($_POST['searchtext'])) : "" ?>"/>
			<input type="submit" class="button" name="searchsubmit" value="<?php _e('Search layers', 'lmm') ?>"/>
		</form>
		<?php echo $showall = (isset($_POST['searchtext']) != NULL) ? "<a style=\"text-decoration:none;\" href=\"" . LEAFLET_WP_ADMIN_URL . "admin.php?page=leafletmapsmarker_layers\">" . __('list all layers','lmm') . "</a>" : ""; ?>
	</div>

	<div style="display:inline;">
	<p>
	<span id="exportlinkstext"><a style="text-decoration:none;cursor:pointer;"><?php _e('Show API links for all markers','lmm'); ?></a></span></div>
	</p>
	<div id="exportlinks" style="display:none;">
	<p>
	<?php echo "<a href=\"" . LEAFLET_PLUGIN_URL . "leaflet-kml.php?layer=all&name=" . $lmm_options[ 'misc_kml' ] . "\" style=\"text-decoration:none;\"><img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-kml.png\" /></a> <a href=\"" . LEAFLET_PLUGIN_URL . "leaflet-kml.php?layer=all&name=" . $lmm_options[ 'misc_kml' ] . "\" style=\"text-decoration:none;\">" . __('Export all markers as KML','lmm') . "</a> <a href=\"https://www.mapsmarker.com/kml\" target=\"_blank\" title=\"" . esc_attr__('Click here for more information on how to use as KML in Google Earth or Google Maps','lmm') . "\"> <img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-question-mark.png\" width=\"12\" height=\"12\" border=\"0\"/></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a target=\"_blank\" href=\"" . LEAFLET_PLUGIN_URL . "leaflet-geojson.php?layer=all&full=yes&full_icon_url=yes\" style=\"text-decoration:none;\"><img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-json.png\" /></a> <a target=\"_blank\" href=\"" . LEAFLET_PLUGIN_URL . "leaflet-geojson.php?layer=all&full=yes\" style=\"text-decoration:none;\">" . __('Export all markers as GeoJSON','lmm') . "</a> <a href=\"https://www.mapsmarker.com/geojson\" target=\"_blank\" title=\"" . esc_attr__('Click here for more information on how to integrate GeoJSON into external websites or apps','lmm') . "\"> <img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-question-mark.png\" width=\"12\" height=\"12\" border=\"0\"/></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a target=\"_blank\" href=\"" . LEAFLET_PLUGIN_URL . "leaflet-georss.php?layer=all\" style=\"text-decoration:none;\"><img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-georss.png\" /></a> <a target=\"_blank\" href=\"" . LEAFLET_PLUGIN_URL . "leaflet-georss.php?layer=all\" style=\"text-decoration:none;\">" . __('Subscribe to markers via GeoRSS','lmm') . "</a> <a href=\"https://www.mapsmarker.com/georss\" target=\"_blank\" title=\"" . esc_attr__('Click here for more information on how to subscribe to new markers via GeoRSS','lmm') . "\"> <img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-question-mark.png\" width=\"12\" height=\"12\" border=\"0\"/></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a target=\"_blank\" href=\"" . LEAFLET_PLUGIN_URL . "leaflet-wikitude.php?layer=all\" style=\"text-decoration:none;\"><img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-wikitude.png\" /></a> <a target=\"_blank\" href=\"" . LEAFLET_PLUGIN_URL . "leaflet-wikitude.php?layer=all\" style=\"text-decoration:none;\">" . __('Export all markers as ARML for Wikitude','lmm') . "</a> <a href=\"https://www.mapsmarker.com/wikitude\" target=\"_blank\" title=\"" . esc_attr__('Click here for more information on how to display in Wikitude Augmented-Reality browser','lmm') . "\"> <img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-question-mark.png\" width=\"12\" height=\"12\" border=\"0\"/></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href=\"" . LEAFLET_PLUGIN_URL . "leaflet-geositemap.php\" style=\"text-decoration:none;\"><img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-sitemap.png\" /></a> <a target=\"_blank\" href=\"" . LEAFLET_PLUGIN_URL . "leaflet-geositemap.php\" style=\"text-decoration:none;\">" . __('Geo Sitemap','lmm') . "</a>&nbsp;<a href=\"https://www.mapsmarker.com/geo-sitemap\" target=\"_blank\" title=\"" . esc_attr__('Click here for more information on how to submit your Geo Sitemap to Google','lmm') . "\"><img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-question-mark.png\" width=\"12\" height=\"12\" border=\"0\"/></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href=\"https://www.mapsmarker.com/mapsmarker-api\" style=\"text-decoration:none;\"><img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-menu-page.png\" /></a> <a href=\"" . LEAFLET_WP_ADMIN_URL . "admin.php?page=leafletmapsmarker_settings#lmm-misc-section9\" title=\"" . esc_attr__('Click here for more information on how to use the MapsMarker API','lmm') . "\" style=\"text-decoration:none;\">Maps Marker API</a>"; ?>
	</p>
	</div>
	<p>
	<?php _e('Total','lmm') ?>: <?php echo $lcount; ?> <?php  echo $lcount_singular_plural = ($lcount == 1) ? __('layer','lmm') : __('layers','lmm'); ?>
	</p>
	<?php
	$getorder = isset($_GET['order']) ? $_GET['order'] : $lmm_options[ 'misc_layer_listing_sort_sort_order' ];
	if ($getorder == 'asc') { $sortorder = 'desc'; } else { $sortorder= 'asc'; };
	if ($getorder == 'asc') { $sortordericon = 'asc'; } else { $sortordericon = 'desc'; };
	?>
	<form method="POST" id="form-list-layers">
		<table cellspacing="0" id="list-layers" class="wp-list-table widefat fixed striped posts">
		  <thead>
		  <tr>
			<th class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>
			<th class="manage-column before_primary column-id sortable  <?php echo $sortordericon; ?>" id="id" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=id&order=<?php echo $sortorder; ?>"><span>ID</span><span class="sorting-indicator"></span></a></th>
			<th class="manage-column before_primary column-type sortable <?php echo $sortordericon; ?>" id="type" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=multi_layer_map&order=<?php echo $sortorder; ?>"><span><?php _e('Type', 'lmm') ?></span><span class="sorting-indicator"></span></a></th>
			<th class="manage-column column-layername column-primary sortable <?php echo $sortordericon; ?>" id="name" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=name&order=<?php echo $sortorder; ?>"><span><?php _e('Name', 'lmm') ?></span><span class="sorting-indicator"></span></a></th>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_address' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_address' ] == 1 )) { ?>
			<th class="manage-column column-address" scope="col"><?php _e('Location', 'lmm') ?></th><?php } ?>
			<th class="manage-column column-count" scope="col">#&nbsp;<?php _e('Markers', 'lmm') ?></th>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_layercenter' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_layercenter' ] == 1 )) { ?>
			<th class="manage-column column-coords" scope="col"><?php _e('Layer center', 'lmm') ?></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_mapsize' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_mapsize' ] == 1 )) { ?>
			<th class="manage-column column-mapsize" scope="col"><?php _e('Map size','lmm') ?></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_panelstatus' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_panelstatus' ] == 1 )) { ?>
			<th class="manage-column column-openpopup"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=m.panel&order=<?php echo $sortorder; ?>"><span><?php _e('Panel status', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_zoom' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_zoom' ] == 1 )) { ?>
			<th class="manage-column column-zoom" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=zoom&order=<?php echo $sortorder; ?>"><span><?php _e('Zoom', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_basemap' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_basemap' ] == 1 )) { ?>
			<th class="manage-column column-basemap" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=basemap&order=<?php echo $sortorder; ?>"><span><?php _e('Basemap', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_createdby' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_createdby' ] == 1 )) { ?>
			<th class="manage-column column-createdby" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=createdby&order=<?php echo $sortorder; ?>"><span><?php _e('Created by', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_createdon' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_createdon' ] == 1 )) { ?>
			<th class="manage-column column-createdon" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=createdon&order=<?php echo $sortorder; ?>"><span><?php _e('Created on', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_updatedby' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_updatedby' ] == 1 )) { ?>
			<th class="manage-column column-updatedby" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=updatedby&order=<?php echo $sortorder; ?>"><span><?php _e('Updated by', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_updatedon' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_updatedon' ] == 1 )) { ?>
			<th class="manage-column column-updatedon" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=updatedon&order=<?php echo $sortorder; ?>"><span><?php _e('Updated on', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_controlbox' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_controlbox' ] == 1 )) { ?>
			<th class="manage-column column-code" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=controlbox&order=<?php echo $sortorder; ?>"><span><?php _e('Controlbox status', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_used_in_content' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_used_in_content' ] == 1 )) { ?>
			<th class="manage-column column-usedincontent" scope="col"><?php _e('Used in content','lmm') ?></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_shortcode' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_shortcode' ] == 1 )) { ?>
			<th class="manage-column column-code" scope="col"><?php _e('Shortcode', 'lmm') ?></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_kml' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_kml' ] == 1 )) { ?>
			<th class="manage-column column-kml" scope="col">KML<a href="https://www.mapsmarker.com/kml" target="_blank" title="<?php esc_attr_e('Click here for more information on how to use as KML in Google Earth or Google Maps','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_fullscreen' ] == 1 )) { ?>
			<th class="manage-column column-fullscreen" scope="col"><?php _e('Fullscreen', 'lmm') ?><span title="<?php esc_attr_e('Open standalone map in fullscreen mode','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></span></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_qr_code' ] == 1 )) { ?>
			<th class="manage-column column-qr-code" scope="col"><?php _e('QR code', 'lmm') ?><span title="<?php esc_attr_e('Create QR code image for standalone map in fullscreen mode','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></span></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_geojson' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_geojson' ] == 1 )) { ?>
			<th class="manage-column column-geojson" scope="col">GeoJSON<a href="https://www.mapsmarker.com/geojson" target="_blank" title="<?php esc_attr_e('Click here for more information on how to integrate GeoJSON into external websites or apps','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_georss' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_georss' ] == 1 )) { ?>
			<th class="manage-column column-georss" scope="col">GeoRSS<a href="https://www.mapsmarker.com/georss" target="_blank" title="<?php esc_attr_e('Click here for more information on how to subscribe to new markers via GeoRSS','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_wikitude' ] == 1 )) { ?>
			<th class="manage-column column-wikitude" scope="col">Wikitude<a href="https://www.mapsmarker.com/wikitude" target="_blank" title="<?php esc_attr_e('Click here for more information on how to display in Wikitude Augmented-Reality browser','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
		</tr>
		  </thead>
		  <tfoot>
		  <tr>
			<th class="manage-column column-cb check-column " scope="col"><input type="checkbox"></th>
			<th class="manage-column before_primary column-id sortable  <?php echo $sortordericon; ?>" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=id&order=<?php echo $sortorder; ?>"><span>ID</span><span class="sorting-indicator"></span></a></th>
			<th class="manage-column before_primary column-type sortable <?php echo $sortordericon; ?>" id="type" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=multi_layer_map&order=<?php echo $sortorder; ?>"><span><?php _e('Type', 'lmm') ?></span><span class="sorting-indicator"></span></a></th>
			<th class="manage-column column-layername column-primary sortable <?php echo $sortordericon; ?>" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=name&order=<?php echo $sortorder; ?>"><span><?php _e('Name', 'lmm') ?></span><span class="sorting-indicator"></span></a></th>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_address' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_address' ] == 1 )) { ?>
			<th class="manage-column column-address" scope="col"><?php _e('Location', 'lmm') ?></th><?php } ?>
			<th class="manage-column column-count" scope="col">#&nbsp;<?php _e('Markers', 'lmm') ?></th>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_layercenter' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_layercenter' ] == 1 )) { ?>
			<th class="manage-column column-coords" scope="col"><?php _e('Layer center', 'lmm') ?></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_mapsize' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_mapsize' ] == 1 )) { ?>
			<th class="manage-column column-mapsize" scope="col"><?php _e('Map size','lmm') ?></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_panelstatus' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_panelstatus' ] == 1 )) { ?>
			<th class="manage-column column-openpopup"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=m.panel&order=<?php echo $sortorder; ?>"><span><?php _e('Panel status', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_zoom' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_zoom' ] == 1 )) { ?>
			<th class="manage-column column-zoom" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=zoom&order=<?php echo $sortorder; ?>"><span><?php _e('Zoom', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_basemap' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_basemap' ] == 1 )) { ?>
			<th class="manage-column column-basemap" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=basemap&order=<?php echo $sortorder; ?>"><span><?php _e('Basemap', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_createdby' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_createdby' ] == 1 )) { ?>
			<th class="manage-column column-createdby" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=createdby&order=<?php echo $sortorder; ?>"><span><?php _e('Created by', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_createdon' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_createdon' ] == 1 )) { ?>
			<th class="manage-column column-createdon" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=createdon&order=<?php echo $sortorder; ?>"><span><?php _e('Created on', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_updatedby' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_updatedby' ] == 1 )) { ?>
			<th class="manage-column column-updatedby" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=updatedby&order=<?php echo $sortorder; ?>"><span><?php _e('Updated by', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_updatedon' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_updatedon' ] == 1 )) { ?>
			<th class="manage-column column-updatedon" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=updatedon&order=<?php echo $sortorder; ?>"><span><?php _e('Updated on', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_controlbox' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_controlbox' ] == 1 )) { ?>
			<th class="manage-column column-code" scope="col"><a href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_layers&orderby=controlbox&order=<?php echo $sortorder; ?>"><span><?php _e('Controlbox status', 'lmm') ?></span><span class="sorting-indicator"></span></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_used_in_content' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_used_in_content' ] == 1 )) { ?>
			<th class="manage-column column-usedincontent" scope="col"><?php _e('Used in content','lmm') ?></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_shortcode' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_shortcode' ] == 1 )) { ?>
			<th class="manage-column column-code" scope="col"><?php _e('Shortcode', 'lmm') ?></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_kml' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_kml' ] == 1 )) { ?>
			<th class="manage-column column-kml" scope="col">KML<a href="https://www.mapsmarker.com/kml" target="_blank" title="<?php esc_attr_e('Click here for more information on how to use as KML in Google Earth or Google Maps','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_fullscreen' ] == 1 )) { ?>
			<th class="manage-column column-fullscreen" scope="col"><?php _e('Fullscreen', 'lmm') ?><span title="<?php esc_attr_e('Open standalone map in fullscreen mode','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></span></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_qr_code' ] == 1 )) { ?>
			<th class="manage-column column-qr-code" scope="col"><?php _e('QR code', 'lmm') ?><span title="<?php esc_attr_e('Create QR code image for standalone map in fullscreen mode','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></span></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_geojson' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_geojson' ] == 1 )) { ?>
			<th class="manage-column column-geojson" scope="col">GeoJSON<a href="https://www.mapsmarker.com/geojson" target="_blank" title="<?php esc_attr_e('Click here for more information on how to integrate GeoJSON into external websites or apps','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_georss' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_georss' ] == 1 )) { ?>
			<th class="manage-column column-georss" scope="col">GeoRSS<a href="https://www.mapsmarker.com/georss" target="_blank" title="<?php esc_attr_e('Click here for more information on how to subscribe to new markers via GeoRSS','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
			<?php if ((isset($lmm_options[ 'misc_layer_listing_columns_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_wikitude' ] == 1 )) { ?>
			<th class="manage-column column-wikitude" scope="col">Wikitude<a href="https://www.mapsmarker.com/wikitude" target="_blank" title="<?php esc_attr_e('Click here for more information on how to display in Wikitude Augmented-Reality browser','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
		</tr>
		  </tfoot>
		  <tbody id="the-list">
		<?php
			function lmm_check_capability_edit($createdby) {
				global $current_user;
				$lmm_options = get_option( 'leafletmapsmarker_options' );
				if ( current_user_can( $lmm_options[ 'capabilities_edit_others' ]) ) {
					return true;
				}
				if ( current_user_can( $lmm_options[ 'capabilities_edit' ]) && ( $current_user->user_login == $createdby) ) {
					return true;
				}
				return false;
		  }
		  function lmm_check_capability_delete($createdby) {
			global $current_user;
			$lmm_options = get_option( 'leafletmapsmarker_options' );
			if ( current_user_can( $lmm_options[ 'capabilities_delete_others' ]) ) {
				return true;
			}
			if ( current_user_can( $lmm_options[ 'capabilities_delete' ]) && ( $current_user->user_login == $createdby) ) {
				return true;
			}
			return false;
		  }

		  $layernonce = wp_create_nonce('layer-nonce'); //for delete-links
		  if (count($layerlist) < 1)
			echo '<tr><td colspan="7">' . __('No layer created yet', 'lmm') . '</td></tr>';
		  else
			foreach ($layerlist as $row)
				{
				$markercount = 0; //info: needed for multi-layer-map count-bug
				if (lmm_check_capability_delete($row['createdby']) == TRUE) {
					$delete_link_layer  = '&nbsp;|&nbsp;<a style="color:red;" onclick="lmm_delete_layer( '.$row['id'].' )" href="javascript:void(0)" class="delete">' . __('delete layer','lmm') . '</a>';
				} else {
					$delete_link_layer = '';
				}

				if (lmm_check_capability_edit($row['createdby']) == TRUE) {
					$edit_link_layer = '<a title="' . esc_attr__('Edit', 'lmm') . ' &laquo;' . htmlspecialchars($row['name']) . '&raquo;" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $row['id'] . '" class="row-title">' . stripslashes(htmlspecialchars($row['name'])) . '</a></strong><br><div class="row-actions"><a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $row['id'] . '">' . __('edit layer','lmm') . '</a>';
				} else {
					$edit_link_layer = '<a title="' . esc_attr__('view', 'lmm') . ' &laquo;' . htmlspecialchars($row['name']) . '&raquo;" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $row['id'] . '" class="row-title">' . stripslashes(htmlspecialchars($row['name'])) . '</a></strong><br><div class="row-actions"><a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $row['id'] . '">' . __('view layer','lmm') . '</a>';
				}
				$column_address = ((isset($lmm_options[ 'misc_layer_listing_columns_address' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_address' ] == 1 )) ? '<td class="lmm-border" data-colname="'.esc_attr__('Location','lmm').'">' . $row['address'] . '</td>' : '';
				if ($row['multi_layer_map'] == 0) {
					$markercount = $wpdb->get_var('SELECT count(*) FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$row['id']);
				} else 	if ( ($row['multi_layer_map'] == 1) && ( $row['multi_layer_map_list'] == 'all' ) ) {
					$markercount = intval($wpdb->get_var('SELECT COUNT(*) FROM '.$table_name_markers));
				} else 	if ( ($row['multi_layer_map'] == 1) && ( $row['multi_layer_map_list'] != NULL ) && ($row['multi_layer_map_list'] != 'all') ) {
					$multi_layer_map_list_exploded = explode(",", $wpdb->get_var('SELECT l.multi_layer_map_list FROM `'.$table_name_layers.'` as l WHERE l.id='.$row['id']));
					foreach ($multi_layer_map_list_exploded as $mlmrowcount){
						$mlm_count_temp{$mlmrowcount} = $wpdb->get_var('SELECT count(*) FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$mlmrowcount);
						$markercount = $markercount + $mlm_count_temp{$mlmrowcount};
					}
				} else 	if ( ($row['multi_layer_map'] == 1) && ( $row['multi_layer_map_list'] == NULL ) ) {
					$markercount = 0;
				}
				$multi_layer_map_type = ($row['multi_layer_map'] == 0) ? '&nbsp;&nbsp;<img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-layer.png" width="16" height="16" title="' . esc_attr__('single layer map','lmm') . '" />' : '&nbsp;&nbsp;<img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-multi_layer_map.png" width="16" height="16" title="' . esc_attr__('multi layer map','lmm') . '" />';
				$openpanelstatus = ($row['panel'] == 1) ? __('visible','lmm') : __('hidden','lmm');
				if ($row['controlbox'] == 0) { $controlboxstatus = __('hidden','lmm'); } else if ($row['controlbox'] == 1) { $controlboxstatus = __('collapsed (except on mobiles)','lmm'); } else if ($row['controlbox'] == 2) { $controlboxstatus = __('expanded','lmm'); };

				 //info: set column display variables - need for for-each
				 $column_layercenter = ((isset($lmm_options[ 'misc_layer_listing_columns_layercenter' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_layercenter' ] == 1 )) ? '<td class="lmm-border" data-colname="'.esc_attr__('Layer Center', 'lmm').'">Lat: ' . $row['layerviewlat'] . '<br/>Lon: ' . $row['layerviewlon'] . '</td>' : '';
				 $column_mapsize = ((isset($lmm_options[ 'misc_layer_listing_columns_mapsize' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_mapsize' ] == 1 )) ? '<td class="lmm-border" data-colname="'.esc_attr__('Width', 'lmm').'">' . __('Width','lmm') . ': '.$row['mapwidth'].$row['mapwidthunit'].'<br/>' . __('Height','lmm') . ': '.$row['mapheight'].'px</td>' : '';
				 $column_panelstatus = ((isset($lmm_options[ 'misc_layer_listing_columns_panelstatus' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_panelstatus' ] == 1 )) ?
				'<td class="lmm-border" data-colname="'.esc_attr__('Panel Status', 'lmm').'">' . $openpanelstatus . '</td>' : '';
				 $column_zoom = ((isset($lmm_options[ 'misc_layer_listing_columns_zoom' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_zoom' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border centeralize" data-colname="'.esc_attr__('Zoom', 'lmm').'">' . $row['layerzoom'] . '</td>' : '';
				 $column_controlbox = ((isset($lmm_options[ 'misc_layer_listing_columns_controlbox' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_controlbox' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border centeralize" data-colname="'.esc_attr__('Control box status', 'lmm').'">' . $controlboxstatus . '</td>' : '';
				 //info: workaround - select shortcode on input focus doesnt work on iOS
				 global $wp_version;
				 if ( version_compare( $wp_version, '3.4', '>=' ) ) {
					 $is_ios = wp_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] );
					 $shortcode_select = ( $is_ios ) ? '' : 'onfocus="this.select();" readonly="readonly"';
				 } else {
					 $shortcode_select = '';
				 }
				 $column_shortcode = ((isset($lmm_options[ 'misc_layer_listing_columns_shortcode' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_shortcode' ] == 1 )) ? '<td class="lmm-border" data-colname="Shortcode"><input ' . $shortcode_select . ' style="width:95%;background:#f3efef;" type="text" value="[' . htmlspecialchars($lmm_options[ 'shortcode' ]) . ' layer=&quot;' . $row['id'] . '&quot;]"></td>' : '';
				 $column_kml = ((isset($lmm_options[ 'misc_layer_listing_columns_kml' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_kml' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border centeralize" data-colname="'.esc_attr__('KML', 'lmm').'"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-kml.php?layer=' . $row['id'] . '&name=' . $lmm_options[ 'misc_kml' ] . '" title="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" alt="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '" /><br/>KML</a></td>' : '';
				 $column_fullscreen = ((isset($lmm_options[ 'misc_layer_listing_columns_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_fullscreen' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border centeralize" data-colname="'.esc_attr__('Fullscreen', 'lmm').'"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?layer=' . $row['id'] . '" target="_blank" title="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" alt="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '"><br/>' . __('Fullscreen','lmm') . '</a></td>' : '';
				 $column_qr_code = ((isset($lmm_options[ 'misc_layer_listing_columns_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_qr_code' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border centeralize"  data-colname="'.esc_attr__('QR Code', 'lmm').'"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-qr.php?layer=' . $row['id'] . '" target="_blank" title="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-qr-code.png" width="14" height="14" alt="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '"><br/>' . __('QR code','lmm') . '</a></td>' : '';
				if ($row['multi_layer_map'] == 0) {
					 $column_geojson = ((isset($lmm_options[ 'misc_layer_listing_columns_geojson' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_geojson' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border centeralize"  data-colname="'.esc_attr__('GeoJSON', 'lmm').'"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?layer=' . $row['id'] . '&callback=jsonp&full=yes&full_icon_url=yes" target="_blank" title="' . esc_attr__('Export as GeoJSON','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-json.png" width="14" height="14" alt="' . esc_attr__('Export as GeoJSON','lmm') . '"><br/>GeoJSON</a></td>' : '';
				} else if ($row['multi_layer_map'] == 1) {
					$column_geojson = ((isset($lmm_options[ 'misc_layer_listing_columns_geojson' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_geojson' ] == 1 )) ? '<td class="lmm-border"  data-colname="'.esc_attr__('GeoJSON', 'lmm').'"></td>' : '';
				}
				 $column_georss = ((isset($lmm_options[ 'misc_layer_listing_columns_georss' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_georss' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border centeralize"  data-colname="'.esc_attr__('GeoRSS', 'lmm').'"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-georss.php?layer=' . $row['id'] . '" target="_blank" title="' . esc_attr__('Export as GeoRSS','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-georss.png" width="14" height="14" alt="' . esc_attr__('Export as GeoRSS','lmm') . '"><br/>GeoRSS</a></td>' : '';
				 $column_wikitude = ((isset($lmm_options[ 'misc_layer_listing_columns_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_wikitude' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border centeralize"  data-colname="'.esc_attr__('Wikitude', 'lmm').'"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-wikitude.php?layer=' . $row['id'] . '" target="_blank" title="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-wikitude.png" width="14" height="14" alt="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '"><br/>Wikitude</a></td>' : '';
				 $column_basemap = ((isset($lmm_options[ 'misc_layer_listing_columns_basemap' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_basemap' ] == 1 )) ? '<td class="lmm-border"  data-colname="'.esc_attr__('Basemap', 'lmm').'">' . $row['basemap'] . '</td>' : '';
				 $column_createdby = ((isset($lmm_options[ 'misc_layer_listing_columns_createdby' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_createdby' ] == 1 )) ? '<td class="lmm-border" data-colname="'.esc_attr__('Created By', 'lmm').'">' . $row['createdby'] . '</td>' : '';
				 $column_createdon = ((isset($lmm_options[ 'misc_layer_listing_columns_createdon' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_createdon' ] == 1 )) ? '<td class="lmm-border" data-colname="'.esc_attr__('Created On', 'lmm').'">' . $row['createdon'] . '</td>' : '';
				 $column_updatedby = ((isset($lmm_options[ 'misc_layer_listing_columns_updatedby' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_updatedby' ] == 1 )) ? '<td class="lmm-border" data-colname="'.esc_attr__('Updated By', 'lmm').'">' . $row['updatedby'] . '</td>' : '';
				 $column_updatedon = ((isset($lmm_options[ 'misc_layer_listing_columns_updatedon' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_updatedon' ] == 1 )) ? '<td class="lmm-border" data-colname="'.esc_attr__('Updated On', 'lmm').'">' . $row['updatedon'] . '</td>' : '';
				 $add_new_marker_to_layer = ( $row['multi_layer_map'] == 0 ) ? '&nbsp;|&nbsp;<a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&addtoLayer=' . $row['id'] . '&lat=' . $row['layerviewlat'] . '&lon=' . $row['layerviewlon'] . '&zoom=' . $row['layerzoom'] . '" style="text-decoration:none;">' . __('add new marker to this layer','lmm') . '</a>' : '';
				if (lmm_check_capability_edit($row['createdby']) == TRUE) {
					$css_table_background = '';
				} else {
					$css_table_background = 'background:#efefef;';
				}
				$layer_type = ($row['multi_layer_map'] == 0)?'layer':'mlm';
				echo '<tr valign="middle" class="alternate iedit author-self hentry" id="link-' . $row['id'] . '" style="' . $css_table_background . '">
				<th class="lmm-border check-column " scope="row"><input type="checkbox" value="' . $row['id'] . '" name="checkedlayers[]" data-layertype="'. $layer_type .'"></th>

				<td class="lmm-border before_primary" data-colname="'.esc_attr__('ID', 'lmm').'">'.$row['id'].' </td>
				<td class="lmm-border before_primary" data-colname="'.esc_attr__('Type', 'lmm').'">'.$multi_layer_map_type.'</td>
				<td class="lmm-border column-primary has-row-actions" data-colname="'.esc_attr__('Name', 'lmm').'"><strong>' . $edit_link_layer . $add_new_marker_to_layer . $delete_link_layer . '</span></div> <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button> </td>
				  ' . $column_address . '
				<td class="lmm-border centeralize" style="text-align:center;" data-colname="'.esc_attr__('# Markers', 'lmm').'"><a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $row['id'] . '#assigned_markers" title="' . esc_attr__('show markers assigned to this layer','lmm') . '">'.$markercount.'</a></td>
				  ' . $column_layercenter . '
				  ' . $column_mapsize . '
				  ' . $column_panelstatus . '
				  ' . $column_zoom . '
				  ' . $column_basemap . '
				  ' . $column_createdby . '
				  ' . $column_createdon . '
				  ' . $column_updatedby . '
				  ' . $column_updatedon . '
				  ' . $column_controlbox;
				  echo ((isset($lmm_options[ 'misc_layer_listing_columns_used_in_content' ] ) == TRUE ) && ( $lmm_options[ 'misc_layer_listing_columns_used_in_content' ] == 1 )) ? '<td class="lmm-border" data-colname="'.esc_attr__('Used in content', 'lmm').'">' . lmm_get_map_shortcodes($row['id'], 'layer') . '</td>' : '';
			      echo $column_shortcode . '
				  ' . $column_kml . '
				  ' . $column_fullscreen . '
				  ' . $column_qr_code . '
				  ' . $column_geojson . '
				  ' . $column_georss . '
				  ' . $column_wikitude . '
				</tr>';
				}
		?>
		  </tbody>
		</table>

		<?php
		if ( (current_user_can( $lmm_options[ 'capabilities_delete_others' ])) || (current_user_can( $lmm_options[ 'capabilities_edit_others' ])) ) {
            $delete_edit_others_actions_visibility = '';
			$delete_edit_others_infotext = '';
            wp_nonce_field('massaction-nonce');
        } else {
            $delete_edit_others_actions_visibility = 'disabled="disabled"';
			$delete_edit_others_infotext = __('Your user does not have the right to perform this action','lmm');
        } ?>
		<table cellspacing="0" style="width:auto;margin-top:20px;" class="wp-list-table widefat fixed bookmarks">
		<tr><td>
		<p><b><?php _e('Bulk actions for selected layers','lmm') ?></b></p>
		<p><input <?php echo $delete_edit_others_actions_visibility; ?> type="radio" id="duplicateselected" name="bulkactions-layers" value="duplicateselected" /> <label for="duplicateselected" title="<?php echo $delete_edit_others_infotext; ?>"><?php _e('duplicate layer(s) only','lmm') ?></label></p>
		<p><input <?php echo $delete_edit_others_actions_visibility; ?> type="radio" id="duplicatelayerandmarkers" name="bulkactions-layers" value="duplicatelayerandmarkers" /> <label for="duplicatelayerandmarkers" title="<?php echo $delete_edit_others_infotext; ?>"><?php _e('duplicate layer(s) and assigned markers','lmm') ?></label></p>
		<?php
		if (current_user_can( $lmm_options[ 'capabilities_delete_others' ])) {
			$deleteselected_visibility = '';
			$deleteselected_infotext = '';
		} else {
			$deleteselected_visibility = 'disabled="disabled"';
			$deleteselected_infotext = __('Your user does not have the right to perform this action','lmm');
		} ?>
		<p>
        <input <?php echo $deleteselected_visibility; ?> type="radio" id="deleteselected" name="bulkactions-layers" value="deleteselected" /> <label for="deleteselected" title="<?php echo $deleteselected_infotext; ?>"><?php _e('delete layer(s) and assigned markers','lmm') ?></label></p>
		<?php
		$layerlist = $wpdb->get_results('SELECT * FROM `'.$table_name_layers.'` WHERE `id` > 0 AND `multi_layer_map` = 0', ARRAY_A);
		if (current_user_can( $lmm_options[ 'capabilities_edit_others' ])) {
			$deleteassignselected_visibility = '';
			$deleteassignselected_infotext = '';
		} else {
			$deleteassignselected_visibility = 'disabled="disabled"';
			$deleteassignselected_infotext = __('Your user does not have the right to perform this action','lmm');
		} ?>
        <input <?php echo $deleteassignselected_visibility; ?> type="radio" id="deleteassignselected" name="bulkactions-layers" value="deleteassignselected" /> <label for="deleteassignselected" title="<?php echo $deleteassignselected_infotext;?>"><?php _e('delete layer(s) and assign markers to the following layer:','lmm') ?></label>
        <select id="layer" name="layer">
        <option <?php echo $deleteassignselected_visibility; ?> value="0"><?php _e('unassigned','lmm') ?></option>
        <?php
            foreach ($layerlist as $row) {
	            echo '<option ' . $deleteassignselected_visibility . ' value="' . $row['id'] . '">' . stripslashes(htmlspecialchars($row['name'])) . ' (ID ' . $row['id'] . ')</option>';
			}
        ?>
        </select><br/>
		<input id="bulk-actions-btn" class="button-secondary" type="submit" value="<?php _e('submit', 'lmm') ?>" style="margin: 0 0 5px 18px;" disabled="disabled" />
		</td></tr></table>
	</form>
<?php } //info: end delete/assign selected layers ?>

<script type="text/javascript">
(function($) {
	//info: enable submit button on bulk action selection
	$('#form-list-layers input[name="bulkactions-layers"]').click(function () {
		document.getElementById('bulk-actions-btn').disabled = false;
	});
	//info: show confirm alert for delete bulk action
	$('#form-list-layers').submit(function () {
		if($('input[name="bulkactions-layers"]:checked').val() == 'deleteselected')
		{
			if (confirm('<?php esc_attr_e('Do you really want to delete the selected layer(s) and assigned markers? (cannot be undone)','lmm') ?>')) {
				return true;
			} else {
				return false;
			}
		}
		if($('input[name="bulkactions-layers"]:checked').val() == 'deleteassignselected')
		{
			if (confirm('<?php esc_attr_e('Do you really want to delete the selected layer(s)? (cannot be undone)','lmm') ?>')) {
				return true;
			} else {
				return false;
			}
		}
	});
	//info: show all API links on click on simplified editor
	$('#exportlinkstext').click(function(e) {
			$('#exportlinkstext').hide();
			$('#exportlinks').show();
	});
	$('.toggle-row').click(function(){
		$(this).parent().toggleClass('dynamic_border');
	});
})(jQuery)
</script>

<!--defaults for list-markers-layers.js -->
<input type="hidden" id="defaults_texts_mlm_validation" value="<?php echo __('Attention: multi-layer-maps (including sublayers) and assigned markers cannot be duplicated by using bulk actions. Please duplicate the included sublayers instead, manually create a new multi-layer-map and assign the duplicated layers to that new multi-layer-map.','lmm'); ?>" />
<?php include('inc' . DIRECTORY_SEPARATOR . 'admin-footer.php'); ?>