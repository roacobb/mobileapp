<?php
//info prevent file from being accessed directly
if (basename($_SERVER['SCRIPT_FILENAME']) == 'showmap.php') { die ("Please do not access this file directly. Thanks!<br/><a href='https://www.mapsmarker.com/go'>www.mapsmarker.com</a>"); }
	global $wpdb, $allowedtags, $locale, $current_user, $is_chrome;
	$lmm_options = get_option( 'leafletmapsmarker_options' );
	include_once( ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php' ); //info: for is_plugin_active()
	require_once( LEAFLET_PLUGIN_DIR . 'inc' . DIRECTORY_SEPARATOR . 'globals.php' );

	//info filters of texts
	$text_directions 			   = apply_filters('mmp_text_directions', esc_attr__('Directions','lmm'));
	$text_export_kml   			   = apply_filters('mmp_text_export_kml', esc_attr__('Export as KML for Google Earth/Google Maps','lmm'));
	$text_fullscreen   			   = apply_filters('mmp_text_fullscreen', esc_attr__('Open standalone map in fullscreen mode','lmm'));
	$text_georss 	   			   = apply_filters('mmp_text_georss',		esc_attr__('Export as GeoRSS','lmm'));
	$text_wikitude 	   			   = apply_filters('mmp_text_wikitude', 	esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm'));
	$text_qr 		   			   = apply_filters('mmp_text_qr', 		esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm'));
	$text_geojson 	   			   = apply_filters('mmp_text_geojson', esc_attr__('Export as GeoJSON','lmm'));
	$text_download_gpx 		 	   = apply_filters('mmp_text_download_gpx', esc_attr__('download GPX file','lmm'));
	$text_show_marker_on_map 	   = apply_filters('mmp_text_show_marker_on_map', esc_attr__('show marker on map','lmm'));
	$text_edit_layer  			   = apply_filters('mmp_text_edit_layer', esc_attr__('edit layer','lmm'));
	$text_edit_marker  			   = apply_filters('mmp_text_edit_marker', esc_attr__('edit marker','lmm'));
	$text_show_embedded_fullscreen = apply_filters('mmp_text_show_embedded_fullscreen', __('Show embedded map in full-screen mode','lmm'));
	$text_loading_map 			   = apply_filters('mmp_text_loading_map', __('loading map - please wait...','lmm'));
	$text_gpx_track_name 		   = apply_filters('mmp_text_gpx_track_name', __('Track name','lmm'));
	$text_gpx_start_track 		   = apply_filters('mmp_text_gpx_start_track', __('Start','lmm'));
	$text_gpx_end_track 		   = apply_filters('mmp_text_gpx_end_track', __('End','lmm'));
	$text_gpx_distance 			   = apply_filters('mmp_text_gpx_distance', __('Distance','lmm'));
	$text_gpx_moving_time 		   = apply_filters('mmp_text_gpx_moving_time', __('Moving time','lmm'));
	$text_gpx_duration 			   = apply_filters('mmp_text_gpx_duration', __('Duration','lmm'));
	$text_gpx_pace 				   = apply_filters('mmp_text_gpx_pace', __('Pace','lmm'));
	$text_gpx_heart_rate 		   = apply_filters('mmp_text_gpx_heart_rate', __('Heart rate','lmm'));
	$text_gpx_elevation  		   = apply_filters('mmp_text_gpx_elevation', __('Elevation','lmm'));
	$text_gpx_full_elevation_data  = apply_filters('mmp_text_gpx_full_elevation_data', __('Full elevation data','lmm'));
	$text_calculated_map_center    = apply_filters('mmp_text_calculated_map_center', esc_attr__('calculated from map center','lmm'));
	$text_edit 					   = apply_filters('mmp_text_edit', __('edit','lmm'));
	$text_map 					   = apply_filters('mmp_text_map', __('Map','lmm'));
	$text_legend 				   = apply_filters('mmp_text_legend', __('Legend','lmm'));
	$text_showme_where_i 		   = apply_filters('mmp_text_showme_where_i', esc_attr__('Show me where I am','lmm'));

	//info: initiate filter_details
	$filter_details = false;

	//info: set custom marker icon dir/url
	if ( $lmm_options['defaults_marker_custom_icon_url_dir'] == 'no' ) {
		$defaults_marker_icon_url = LEAFLET_PLUGIN_ICONS_URL;
	} else {
		$defaults_marker_icon_url = htmlspecialchars($lmm_options['defaults_marker_icon_url']);
	}
	//info: set marker shadow url
	if ( $lmm_options['defaults_marker_icon_shadow_url_status'] == 'default' ) {
		if ( $lmm_options['defaults_marker_icon_shadow_url'] == NULL ) {
			$marker_shadow_url = '';
		} else {
			$marker_shadow_url = LEAFLET_PLUGIN_URL . 'leaflet-dist/images/marker-shadow.png';
		}
	} else {
		$marker_shadow_url = htmlspecialchars($lmm_options['defaults_marker_icon_shadow_url']);
	}
	$uid = substr(md5(''.rand()), 0, 8);
	extract(shortcode_atts(array(
		'mapname' => 'lmm_map_'.$uid,
		'layer' => '',
		'marker' => '',
		'paneltext' => '',
		//info: marker + partially layer elements
		'markername' => '',
		'basemap' => '',
		'lat' => '',
		'lon' => '',
		'icon' => '',
		'popuptext' => '',
		'zoom' => '',
		'openpopup' => '',
		'mapwidth' => '',
		'mapwidthunit' => '',
		'mapheight' => '',
		'panel' => '',
		'createdby' => '',
		'createdon' => '',
		'updatedby' => '',
		'updatedon' => '',
		'controlbox' => '',
		'overlays_custom' => '',
		'overlays_custom2' => '',
		'overlays_custom3' => '',
		'overlays_custom4' => '',
		'wms' => '',
		'wms2' => '',
		'wms3' => '',
		'wms4' => '',
		'wms5' => '',
		'wms6' => '',
		'wms7' => '',
		'wms8' => '',
		'wms9' => '',
		'wms10' => '',
		'kml_timestamp' => '',
		'address' => '',
		'gpx_url' => '',
		'gpx_panel' => '',
		//info: additional layer elements
		'name' => '',
		'layerzoom' => '',
		'layerviewlat' => '',
		'layerviewlon' => '',
		'listmarkers' => '',
		'multi_layer_map' => '',
		'multi_layer_map_list' => '',
		'clustering' => '',
		//info: legacy for shortcodes added directly
		'mlat' => '',
		'mlon' => '',
		'highlightmarker' => '',
		//info: override global map settings
		'dragging' => '',
		'touchzoom' => '',
		'scrollwheelzoom' => '',
		'doubleclickzoom' => '',
		'boxzoom' => '',
		'trackresize' => '',
		'worldcopyjump' => '',
		'closepopuponclick' => '',
		'keyboard' => '',
		'keyboardpanoffset' => '',
		'keyboardzoomoffset' => '',
		'inertia' => '',
		'inertiadeceleration' => '',
		'inertiamaxspeed' => '',
		'zoomcontrol' => '',
		'crs' => '',
		'fullscreencontrol' => '',
		'maxzoom' => '',
		'tap' => '',
		'taptolerance' => '',
		'bounceAtZoomLimits' => ''
	), $atts));

	//info: prepare layers
	if (!empty($layer)) {
		$table_name_layers = $wpdb->prefix.'leafletmapsmarker_layers';
		$table_name_markers = $wpdb->prefix.'leafletmapsmarker_markers';
		$row = $wpdb->get_row('SELECT `id`,`name`,`basemap`,`mapwidth`,`mapheight`,`mapwidthunit`,`panel`,`layerzoom`,`layerviewlat`,`layerviewlon`,`controlbox`,`overlays_custom`,`overlays_custom2`,`overlays_custom3`,`overlays_custom4`,`wms`,`wms2`,`wms3`,`wms4`,`wms5`,`wms6`,`wms7`,`wms8`,`wms9`,`wms10`,`listmarkers`,`multi_layer_map`,`multi_layer_map_list`,`clustering`,`gpx_url`,`gpx_panel`, `mlm_filter`, `mlm_filter_details` FROM `'.$table_name_layers.'` WHERE `id`='.intval($layer), ARRAY_A);
		$id = $row['id'];
		$filters_active_sort_order = ($lmm_options['mlm_filter_active_sort_order'] == 'DESC')?SORT_DESC:SORT_ASC;
		$filters_inactive_sort_order = ($lmm_options['mlm_filter_inactive_sort_order'] == 'DESC')?SORT_DESC:SORT_ASC;
		$filter_details = json_decode($row['mlm_filter_details'], true);
		if($filter_details){
			$filter_show_markercount = (isset($lmm_options['mlm_filter_controlbox_markercount']) && $lmm_options['mlm_filter_controlbox_markercount'] == '1')?'1':'0';
			$filter_show_icon = (isset($lmm_options['mlm_filter_controlbox_icon']) && $lmm_options['mlm_filter_controlbox_icon'] == '1')?'1':'0';
			$filter_show_name = (isset($lmm_options['mlm_filter_controlbox_name']) && $lmm_options['mlm_filter_controlbox_name'] == '1')?'1':'0';
			//info: in case all of the 3 attributes unchecked, display the name
			if($filter_show_markercount == '0' && $filter_show_icon == '0' && $filter_show_name == '0'){
				$filter_show_name = '1';
			}
			$active_layers_order  = $lmm_options['mlm_filter_active_sort_order'];
			$active_layers_orderby  = ($lmm_options['mlm_filter_active_orderby'] == 'id')?'layer_id':$lmm_options['mlm_filter_active_orderby'];
			//info: order active layers
			$prepare_active_ordered_filters = array();
			foreach($filter_details as $key => $value){
				if($value['status'] == 'active'){
					$prepare_active_ordered_filters[$key] = $value;
					$filter_details[$key]['markercount'] = intval($wpdb->get_var( "SELECT COUNT(*) FROM $table_name_markers WHERE layer LIKE concat('%\"',$key,'\"%')" ));
					$prepare_active_ordered_filters[$key]['markercount'] = intval($filter_details[$key]['markercount']);
					$filter_details[$key]['name'] = stripslashes($filter_details[$key]['name']);
					$filter_details[$key]['icon'] = esc_url($filter_details[$key]['icon']);
				}
			}
			if($lmm_options['mlm_filter_active_orderby'] == 'id'){
				if($filters_active_sort_order === SORT_DESC){
					krsort( $prepare_active_ordered_filters );
				}else{
					ksort( $prepare_active_ordered_filters );
				}
			}else{
				$prepare_active_ordered_filters = array_sort( $prepare_active_ordered_filters , $lmm_options['mlm_filter_active_orderby'], $filters_active_sort_order);
			}
			//info: order inactive layers
			$prepare_inactive_ordered_filters = array();
			foreach($filter_details as $key => $value){
				if($value['status'] == 'inactive'){
					$prepare_inactive_ordered_filters[$key] = $value;
					$filter_details[$key]['markercount'] =  intval($wpdb->get_var( "SELECT COUNT(*) FROM $table_name_markers WHERE layer LIKE concat('%\"',$key,'\"%')" ));
					$prepare_inactive_ordered_filters[$key]['markercount'] = intval($filter_details[$key]['markercount']);
					$filter_details[$key]['name'] = stripslashes($filter_details[$key]['name']);
					$filter_details[$key]['icon'] = esc_url($filter_details[$key]['icon']);
				}
			}

			if($lmm_options['mlm_filter_inactive_orderby'] == 'id'){
				if($filters_inactive_sort_order === SORT_DESC){
					krsort( $prepare_inactive_ordered_filters );
				}else{
					ksort( $prepare_inactive_ordered_filters );
				}
			}else{
				$prepare_inactive_ordered_filters = array_sort( $prepare_inactive_ordered_filters, $lmm_options['mlm_filter_inactive_orderby'], $filters_inactive_sort_order);
			}
			//info: combine active and inactive layers
			$ordered_filters = array();
			$i = 0;
			foreach($prepare_active_ordered_filters as $layer_id => $detail){
				$ordered_filters[$i] = $detail;
				$ordered_filters[$i]['id'] = $layer_id;
				$i++;
			}
			foreach($prepare_inactive_ordered_filters as $layer_id => $detail){
				$ordered_filters[$i] = $detail;
				$ordered_filters[$i]['id'] = $layer_id;
				$i++;
			}
		}
		if (empty($basemap)) { $basemap = $row['basemap']; } else { $basemap = in_array($basemap, array('osm_mapnik','mapquest_osm','mapquest_aerial','googleLayer_roadmap','googleLayer_satellite','googleLayer_hybrid','googleLayer_terrain','bingaerial','bingaerialwithlabels','bingroad','ogdwien_basemap','ogdwien_satellite','mapbox','mapbox2','mapbox3','custom_basemap','custom_basemap2','custom_basemap3','empty_basemap')) ? $basemap : $lmm_options[ 'standard_basemap' ]; }
		//info: fallback for existing maps if Google API is disabled
		if (($lmm_options['google_maps_api_status'] == 'disabled') && (($basemap == 'googleLayer_roadmap') || ($basemap == 'googleLayer_satellite') || ($basemap == 'googleLayer_hybrid') || ($basemap == 'googleLayer_terrain')) ) {
			$basemap = 'osm_mapnik';
		}
		if (empty($layerviewlon)) { $lon = $row['layerviewlon']; } else { $lon = floatval($layerviewlon); }
		if (empty($layerviewlat)) { $lat = $row['layerviewlat']; } else { $lat = floatval($layerviewlat); }
		if (empty($layerzoom)) { $zoom = $row['layerzoom']; } else { $zoom = intval($layerzoom); }
		if (empty($mapwidth)) { $mapwidth = $row['mapwidth']; } else { $mapwidth = intval($mapwidth); }
		if (empty($mapwidthunit)) { $mapwidthunit = $row['mapwidthunit']; } else { $mapwidthunit = (($mapwidthunit == 'px') || ($mapwidthunit == '%')) ? $mapwidthunit : $lmm_options[ 'defaults_marker_mapwidthunit' ]; }
		if (empty($mapheight)) { $mapheight = $row['mapheight']; } else { $mapheight = intval($mapheight); }
		if ( isset($panel) && (($panel == '0') || ($panel == '1')) ) { $panel = $panel; } else { $panel = $row['panel']; } //info: reversed!
		if (empty($paneltext)) { $paneltext = ($row['name'] == NULL) ? '&nbsp;' : htmlspecialchars($row['name']); } else { $paneltext = (empty($paneltext)) ? '&nbsp;' : htmlspecialchars($paneltext); }
		if ( isset($controlbox) && (($controlbox == '0') || ($controlbox == '1') || ($controlbox == '2')) ) { $controlbox = $controlbox; } else { $controlbox = $row['controlbox']; } //info: reversed!
		if ( isset($overlays_custom) && (($overlays_custom == '0') || ($overlays_custom == '1')) ) { $overlays_custom = $overlays_custom; } else { $overlays_custom = $row['overlays_custom']; } //info: reversed!
		if ( isset($overlays_custom2) && (($overlays_custom2 == '0') || ($overlays_custom2 == '1')) ) { $overlays_custom2 = $overlays_custom2; } else { $overlays_custom2 = $row['overlays_custom2']; } //info: reversed!
		if ( isset($overlays_custom3) && (($overlays_custom3 == '0') || ($overlays_custom3 == '1')) ) { $overlays_custom3 = $overlays_custom3; } else { $overlays_custom3 = $row['overlays_custom3']; } //info: reversed!
		if ( isset($overlays_custom4) && (($overlays_custom4 == '0') || ($overlays_custom4 == '1')) ) { $overlays_custom4 = $overlays_custom4; } else { $overlays_custom4 = $row['overlays_custom4']; } //info: reversed!
		if ( isset($wms) && (($wms == '0') || ($wms == '1')) ) { $wms = $wms; } else { $wms = $row['wms']; } //info: reversed!
		if ( isset($wms2) && (($wms2 == '0') || ($wms2 == '1')) ) { $wms2 = $wms; } else { $wms2 = $row['wms2']; } //info: reversed!
		if ( isset($wms3) && (($wms3 == '0') || ($wms3 == '1')) ) { $wms3 = $wms; } else { $wms3 = $row['wms3']; } //info: reversed!
		if ( isset($wms4) && (($wms4 == '0') || ($wms4 == '1')) ) { $wms4 = $wms; } else { $wms4 = $row['wms4']; } //info: reversed!
		if ( isset($wms5) && (($wms5 == '0') || ($wms5 == '1')) ) { $wms5 = $wms; } else { $wms5 = $row['wms5']; } //info: reversed!
		if ( isset($wms6) && (($wms6 == '0') || ($wms6 == '1')) ) { $wms6 = $wms; } else { $wms6 = $row['wms6']; } //info: reversed!
		if ( isset($wms7) && (($wms7 == '0') || ($wms7 == '1')) ) { $wms7 = $wms; } else { $wms7 = $row['wms7']; } //info: reversed!
		if ( isset($wms8) && (($wms8 == '0') || ($wms8 == '1')) ) { $wms8 = $wms; } else { $wms8 = $row['wms8']; } //info: reversed!
		if ( isset($wms9) && (($wms9 == '0') || ($wms9 == '1')) ) { $wms9 = $wms; } else { $wms9 = $row['wms9']; } //info: reversed!
		if ( isset($wms10) && (($wms10 == '0') || ($wms10 == '1')) ) { $wms10 = $wms; } else { $wms10 = $row['wms10']; } //info: reversed!
		if ( isset($listmarkers) && (($listmarkers == '0') || ($listmarkers == '1')) ) { $listmarkers = $listmarkers; } else { $listmarkers = $row['listmarkers']; } //info: reversed!
		if ( isset($multi_layer_map) && (($multi_layer_map == '0') || ($multi_layer_map == '1')) ) { $multi_layer_map = $multi_layer_map; } else { $multi_layer_map = $row['multi_layer_map']; } //info: reversed!
		if (empty($multi_layer_map_list)) {
			if($filter_details){
				$multi_layer_map_list = implode(',', array_keys($prepare_active_ordered_filters));
			}else{
				$multi_layer_map_list = $row['multi_layer_map_list'];
			}
		} else {
			$multi_layer_map_list = esc_sql($multi_layer_map_list);
		}
		$multi_layer_map_list_exploded = explode(",", $multi_layer_map_list);
		if ( isset($clustering) && (($clustering == '0') || ($clustering == '1')) ) { $clustering = $clustering; } else { $clustering = $row['clustering']; } //info: reversed!
		if (empty($gpx_url)) { $gpx_url = $row['gpx_url']; } else { $gpx_url = esc_url($gpx_url); }
		if ( isset($gpx_panel) && (($gpx_panel == '0') || ($gpx_panel == '1')) ) { $gpx_panel = $gpx_panel; } else { $gpx_panel = $row['gpx_panel']; } //info: reversed!
		if ( isset($row['mlm_filter']) && $row['mlm_filter'] == '1' ){ $filters_collapsed = 'true'; }elseif($row['mlm_filter'] == '2'){ $filters_collapsed = 'false'; }else{ $filters_collapsed = 'hidden'; }
	}
	//info: prepare markers
	if (!empty($marker))  {
			$table_name_markers = $wpdb->prefix.'leafletmapsmarker_markers';
				$row = $wpdb->get_row('SELECT `id`,`markername`,`basemap`,`layer`,`lat`,`lon`,`icon`,`popuptext`,`zoom`,`openpopup`,`mapwidth`,`mapwidthunit`,`mapheight`,`panel`,`controlbox`,`overlays_custom`,`overlays_custom2`,`overlays_custom3`,`overlays_custom4`,`wms`,`wms2`,`wms3`,`wms4`,`wms5`,`wms6`,`wms7`,`wms8`,`wms9`,`wms10`,`address`,`gpx_url`,`gpx_panel` FROM `'.$table_name_markers.'` WHERE `id`='.intval($marker), ARRAY_A);
				if(!empty($row)) {
					$id = $row['id'];
					if (empty($markername)) { $markername = esc_js($row['markername']); } else { $markername = esc_js($markername); }
					if (empty($basemap)) { $basemap = $row['basemap']; } else { $basemap = in_array($basemap, array('osm_mapnik','mapquest_osm','mapquest_aerial','googleLayer_roadmap','googleLayer_satellite','googleLayer_hybrid','googleLayer_terrain','bingaerial','bingaerialwithlabels','bingroad','ogdwien_basemap','ogdwien_satellite','mapbox','mapbox2','mapbox3','custom_basemap','custom_basemap2','custom_basemap3','empty_basemap')) ? $basemap : $lmm_options[ 'standard_basemap' ]; }
					//info: fallback for existing maps if Google API is disabled
					if (($lmm_options['google_maps_api_status'] == 'disabled') && (($basemap == 'googleLayer_roadmap') || ($basemap == 'googleLayer_satellite') || ($basemap == 'googleLayer_hybrid') || ($basemap == 'googleLayer_terrain')) ) {
						$basemap = 'osm_mapnik';
					}
					if (empty($lon)) { $lon = $row['lon']; } else { $lon = floatval($lon); }
					if (empty($lat)) { $lat = $row['lat']; } else { $lat = floatval($lat); }
					if (empty($icon)) { $icon = $row['icon']; } else { $icon = esc_js($icon); }
					if (empty($popuptext)) { $popuptext = $row['popuptext']; }
					if (empty($zoom)) { $zoom = $row['zoom']; } else { $zoom = intval($zoom); }
					if ( isset($openpopup) && (($openpopup == '0') || ($openpopup == '1')) ) { $openpopup = ($openpopup == 1) ? '.openPopup()' : ''; } else { $openpopup = ($row['openpopup'] == 1) ? '.openPopup()' : ''; } //info: reversed!
					$mopenpopup = $openpopup;
					//$layer = $row['layer']; //info: not needed in showmap.php, would overwrite if (!empty($layer))-check!
					$mlat = $lat;
					$mlon = $lon;
					$mpopuptext = $popuptext;
					$micon = $icon;
					if (empty($mapwidth)) { $mapwidth = $row['mapwidth']; } else { $mapwidth = intval($mapwidth); }
					if (empty($mapwidthunit)) { $mapwidthunit = $row['mapwidthunit']; } else { $mapwidthunit = (($mapwidthunit == 'px') || ($mapwidthunit == '%')) ? $mapwidthunit : $lmm_options[ 'defaults_marker_mapwidthunit' ]; }
					if (empty($mapheight)) { $mapheight = $row['mapheight']; } else { $mapheight = intval($mapheight); }
					if ( isset($panel) && (($panel == '0') || ($panel == '1')) ) { $panel = $panel; } else { $panel = $row['panel']; } //info: reversed!
					if (empty($paneltext)) { $paneltext = ($row['markername'] == NULL) ? '&nbsp;' : htmlspecialchars($row['markername']); } else { $paneltext = (empty($paneltext)) ? '&nbsp;' : htmlspecialchars($paneltext); }
					if ( isset($controlbox) && (($controlbox == '0') || ($controlbox == '1') || ($controlbox == '2')) ) { $controlbox = $controlbox; } else { $controlbox = $row['controlbox']; } //info: reversed!
					if ( isset($overlays_custom) && (($overlays_custom == '0') || ($overlays_custom == '1')) ) { $overlays_custom = $overlays_custom; } else { $overlays_custom = $row['overlays_custom']; } //info: reversed!
					if ( isset($overlays_custom2) && (($overlays_custom2 == '0') || ($overlays_custom2 == '1')) ) { $overlays_custom2 = $overlays_custom2; } else { $overlays_custom2 = $row['overlays_custom2']; } //info: reversed!
					if ( isset($overlays_custom3) && (($overlays_custom3 == '0') || ($overlays_custom3 == '1')) ) { $overlays_custom3 = $overlays_custom3; } else { $overlays_custom3 = $row['overlays_custom3']; } //info: reversed!
					if ( isset($overlays_custom4) && (($overlays_custom4 == '0') || ($overlays_custom4 == '1')) ) { $overlays_custom4 = $overlays_custom4; } else { $overlays_custom4 = $row['overlays_custom4']; } //info: reversed!
					if ( isset($wms) && (($wms == '0') || ($wms == '1')) ) { $wms = $wms; } else { $wms = $row['wms']; } //info: reversed!
					if ( isset($wms2) && (($wms2 == '0') || ($wms2 == '1')) ) { $wms2 = $wms; } else { $wms2 = $row['wms2']; } //info: reversed!
					if ( isset($wms3) && (($wms3 == '0') || ($wms3 == '1')) ) { $wms3 = $wms; } else { $wms3 = $row['wms3']; } //info: reversed!
					if ( isset($wms4) && (($wms4 == '0') || ($wms4 == '1')) ) { $wms4 = $wms; } else { $wms4 = $row['wms4']; } //info: reversed!
					if ( isset($wms5) && (($wms5 == '0') || ($wms5 == '1')) ) { $wms5 = $wms; } else { $wms5 = $row['wms5']; } //info: reversed!
					if ( isset($wms6) && (($wms6 == '0') || ($wms6 == '1')) ) { $wms6 = $wms; } else { $wms6 = $row['wms6']; } //info: reversed!
					if ( isset($wms7) && (($wms7 == '0') || ($wms7 == '1')) ) { $wms7 = $wms; } else { $wms7 = $row['wms7']; } //info: reversed!
					if ( isset($wms8) && (($wms8 == '0') || ($wms8 == '1')) ) { $wms8 = $wms; } else { $wms8 = $row['wms8']; } //info: reversed!
					if ( isset($wms9) && (($wms9 == '0') || ($wms9 == '1')) ) { $wms9 = $wms; } else { $wms9 = $row['wms9']; } //info: reversed!
					if ( isset($wms10) && (($wms10 == '0') || ($wms10 == '1')) ) { $wms10 = $wms; } else { $wms10 = $row['wms10']; } //info: reversed!
					if (empty($address)) { $address = htmlspecialchars($row['address']); }
					$listmarkers = 0;
					if (empty($gpx_url)) { $gpx_url = $row['gpx_url']; } else { $gpx_url = esc_url($gpx_url); }
					if ( isset($gpx_panel) && (($gpx_panel == '0') || ($gpx_panel == '1')) ) { $gpx_panel = $gpx_panel; } else { $gpx_panel = $row['gpx_panel']; } //info: reversed!
				}
	}
	//info: prepare markers only added by shortcode and not defined in backend
	if (empty($layer) and empty($marker)) {
		if (empty($basemap)) { $basemap = $lmm_options['defaults_marker_shortcode_basemap']; } else { $basemap = in_array($basemap, array('osm_mapnik','mapquest_osm','mapquest_aerial','googleLayer_roadmap','googleLayer_satellite','googleLayer_hybrid','googleLayer_terrain','bingaerial','bingaerialwithlabels','bingroad','ogdwien_basemap','ogdwien_satellite','mapbox','mapbox2','mapbox3','custom_basemap','custom_basemap2','custom_basemap3','empty_basemap')) ? $basemap : $lmm_options[ 'defaults_marker_shortcode_basemap' ]; }
		$lon = floatval($lon);
		$lat = floatval($lat);
		if (empty($icon)) { $icon = ($lmm_options[ 'defaults_marker_icon' ] == NULL) ? '' : $lmm_options[ 'defaults_marker_icon' ]; } else { $icon = esc_js($icon); }
		$micon = $icon;
		$mpopuptext = $popuptext;
		if (empty($zoom)) { $zoom = intval($lmm_options[ 'defaults_marker_shortcode_zoom' ]); } else { $zoom = intval($zoom); }
		if ( isset($openpopup) && (($openpopup == '0') || ($openpopup == '1')) ) { $openpopup = ($openpopup == 1) ? '.openPopup()' : ''; } else { $openpopup = ''; } //info: reversed!
		$mopenpopup = $openpopup;
		if (empty($mapwidth)) { $mapwidth = intval($lmm_options[ 'defaults_marker_shortcode_mapwidth' ]); } else { $mapwidth = intval($mapwidth); }
		if (empty($mapwidthunit)) { $mapwidthunit = $lmm_options[ 'defaults_marker_shortcode_mapwidthunit' ]; } else { $mapwidthunit = (($mapwidthunit == 'px') || ($mapwidthunit == '%')) ? $mapwidthunit : $lmm_options[ 'defaults_marker_shortcode_mapwidthunit' ]; }
		if (empty($mapheight)) { $mapheight =  intval($lmm_options[ 'defaults_marker_shortcode_mapheight' ]); } else { $mapheight = intval($mapheight); }
		//info: panel+paneltext not supported (no ID for API links)
		//info: createdby/on, updatedby/on
		if ( isset($controlbox) && (($controlbox == '0') || ($controlbox == '1') || ($controlbox == '2')) ) { $controlbox = $controlbox; } else { $controlbox = $lmm_options[ 'defaults_marker_shortcode_controlbox' ]; } //info: reversed!
		if ( isset($overlays_custom) && (($overlays_custom == '0') || ($overlays_custom == '1')) ) { $overlays_custom = $overlays_custom; } else { $overlays_custom = (isset($lmm_options[ 'defaults_marker_shortcode_overlays_custom_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($overlays_custom2) && (($overlays_custom2 == '0') || ($overlays_custom2 == '1')) ) { $overlays_custom2 = $overlays_custom2; } else { $overlays_custom2 = (isset($lmm_options[ 'defaults_marker_shortcode_overlays_custom2_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($overlays_custom) && (($overlays_custom == '0') || ($overlays_custom == '1')) ) { $overlays_custom3 = $overlays_custom3; } else { $overlays_custom3 = (isset($lmm_options[ 'defaults_marker_shortcode_overlays_custom3_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($overlays_custom4) && (($overlays_custom4 == '0') || ($overlays_custom4 == '1')) ) { $overlays_custom4 = $overlays_custom4; } else { $overlays_custom4 = (isset($lmm_options[ 'defaults_marker_shortcode_overlays_custom4_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms) && (($wms == '0') || ($wms == '1')) ) { $wms = $wms; } else { $wms = (isset($lmm_options[ 'defaults_marker_shortcode_wms_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms2) && (($wms2 == '0') || ($wms2 == '1')) ) { $wms2 = $wms2; } else { $wms2 = (isset($lmm_options[ 'defaults_marker_shortcode_wms2_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms3) && (($wms3 == '0') || ($wms3 == '1')) ) { $wms3 = $wms3; } else { $wms3 = (isset($lmm_options[ 'defaults_marker_shortcode_wms3_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms4) && (($wms4 == '0') || ($wms4 == '1')) ) { $wms4 = $wms4; } else { $wms4 = (isset($lmm_options[ 'defaults_marker_shortcode_wms4_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms5) && (($wms5 == '0') || ($wms5 == '1')) ) { $wms5 = $wms5; } else { $wms5 = (isset($lmm_options[ 'defaults_marker_shortcode_wms5_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms6) && (($wms6 == '0') || ($wms6 == '1')) ) { $wms6 = $wms6; } else { $wms6 = (isset($lmm_options[ 'defaults_marker_shortcode_wms6_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms7) && (($wms7 == '0') || ($wms7 == '1')) ) { $wms7 = $wms7; } else { $wms7 = (isset($lmm_options[ 'defaults_marker_shortcode_wms7_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms8) && (($wms8 == '0') || ($wms8 == '1')) ) { $wms8 = $wms8; } else { $wms8 = (isset($lmm_options[ 'defaults_marker_shortcode_wms8_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms9) && (($wms9 == '0') || ($wms9 == '1')) ) { $wms9 = $wms9; } else { $wms9 = (isset($lmm_options[ 'defaults_marker_shortcode_wms9_active' ])) ? 1 : 0; } //info: reversed!
		if ( isset($wms10) && (($wms10 == '0') || ($wms10 == '1')) ) { $wms10 = $wms10; } else { $wms10 = (isset($lmm_options[ 'defaults_marker_shortcode_wms10_active' ])) ? 1 : 0; } //info: reversed!
		if (empty($address)) { $address = ''; } else { $address = htmlspecialchars($address); }
		$listmarkers = 0;
		if (empty($gpx_url)) { $gpx_url = ''; } else { $gpx_url = esc_url($gpx_url); }
		if ( isset($gpx_panel) && (($gpx_panel == '0') || ($gpx_panel == '1')) ) { $gpx_panel = $gpx_panel; } else { $gpx_panel = 0; } //info: reversed!
		//info: legacy
		if (!empty($mlat)) { $lat = $mlat; } else { $mlat = $lat; }
		if (!empty($mlon)) { $lon = $mlon; } else { $mlon = $lon; }
	}

	//info: show static image with link in feeds
	if (is_feed()) {
		if ($lat != NULL) { //info: marker exists?
			if (empty($layer)) {
			$lmm_out = '<p>' . $paneltext . '<br/><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?marker=' . $id . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/map-rss-feed.png"/><br/>' . $text_show_embedded_fullscreen . '</a></p>';
			}
			if (empty($marker)) {
			$lmm_out = '<p>' . $paneltext . '<br/><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?layer=' . $id . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/map-rss-feed.png"/><br/>' . $text_show_embedded_fullscreen . '</a></p>';
			}
			//return $lmm_out;
		}
	} else {

	//info: check if layer/marker ID exists
	if ($lat == NULL) {
		//info: save to option to display as admin notice
		$shortcode_errors = get_option('leafletmapsmarkerpro_deleted_maps_errors');
		if ($shortcode_errors === FALSE) {
			$shortcode_errors = array();
			array_push($shortcode_errors,get_permalink());
			update_option('leafletmapsmarkerpro_deleted_maps_errors', $shortcode_errors);
		} else if (is_array($shortcode_errors)) {
			if( !in_array(get_permalink(),$shortcode_errors)) {
				array_push($shortcode_errors,get_permalink());
				update_option('leafletmapsmarkerpro_deleted_maps_errors', $shortcode_errors);
			}
		}
		$lmm_out = '<div id="lmm_error" style="margin:10px 0;">'.PHP_EOL;
			if (empty($layer)) {
				$lmm_out .= sprintf( esc_attr__('Error: a marker with the ID %1$s does not exist!','lmm'), $marker) . '<br/>';
			}
			if (empty($marker)) {
				$lmm_out .= sprintf( esc_attr__('Error: a layer with the ID %1$s does not exist!','lmm'), $layer) . '<br/>';
			}
		$lmm_out .= '<a href="https://www.mapsmarker.com" target="_blank" title="' . esc_attr__('Go to plugin website','lmm') . '"><img style="border:1px solid #ccc;" src="' . LEAFLET_PLUGIN_URL . 'inc/img/map-deleted-image.png" width="244" height="224" /></a></div>';
	} else {

	//info: starting output on frontend
	$lmm_out = '';
	if (!empty($layer)) {
		$css_classes = 'layermap layer-'. intval($layer);
	} else if (!empty($marker))  {
		$css_classes = 'markermap marker-'. intval($marker);
	} else {
		$css_classes = '';
	}
	$lmm_out .= '<div id="lmm_'.$uid.'" style="width:' . $mapwidth.$mapwidthunit . ';" class="mapsmarker ' . $css_classes . '">'.PHP_EOL;

	//info: markercluster progress bar
	if (isset($clustering)) {
		if ($clustering == '1') {
			if ($mapwidthunit == '%') {
				$mcpb_left = 'left:36%;';
				$mcpb_top = 'top:90px;';
				$mcpb_width = 'width:200px;';
			} else {
				$mcpb_top = 'top:' . (($mapheight/2)+40) . 'px;';
				if ($mapwidth >= 200) {
					$mcpb_left = 'left:' . (($mapwidth/2)-100) . 'px;';
					$mcpb_width = 'width:200px;';
				} else {
					$mcpb_left = 'left:2%;';
					$mcpb_width = 'width:95%;';
				}
			}
			$lmm_out .= '<div id="lmm_'.$uid.'-progress" class="markercluster-progress" style="' . $mcpb_left . $mcpb_top . $mcpb_width . '"><div id="lmm_'.$uid.'-progress-bar" class="markercluster-progress-bar"></div></div>'.PHP_EOL;
		}
	}

	//info: panel for layer/marker name and API URLs
	if ($panel == 1) {
		if (!empty($marker)) {
			$panel_background = htmlspecialchars(addslashes($lmm_options[ 'defaults_marker_panel_background_color' ]));
		} else if (!empty($layer)) {
			$panel_background = htmlspecialchars(addslashes($lmm_options[ 'defaults_layer_panel_background_color' ]));
		} else {
			$panel_background = '';
		}
		$lmm_out .= '<div id="lmm_panel_'.$uid.'" class="lmm-panel" style="background:' . $panel_background . ';">'.PHP_EOL;

		if (!empty($marker))
		{
			$lmm_out .= '<div id="lmm_panel_api_'.$uid.'" class="lmm-panel-api">';
			if ( (isset($lmm_options[ 'defaults_marker_panel_directions' ] ) == TRUE ) && ( $lmm_options[ 'defaults_marker_panel_directions' ] == 1 ) ) {
					if ($lmm_options['directions_provider'] == 'googlemaps') {
						if ( isset($lmm_options['google_maps_base_domain_custom']) && ($lmm_options['google_maps_base_domain_custom'] == NULL) ) { $gmaps_base_domain_directions = $lmm_options['google_maps_base_domain']; } else { $gmaps_base_domain_directions = htmlspecialchars($lmm_options['google_maps_base_domain_custom']); }
						if ((isset($lmm_options[ 'directions_googlemaps_route_type_walking' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_walking' ] == 1 )) { $directions_transport_type_icon = 'icon-walk.png'; } else { $directions_transport_type_icon = 'icon-car.png'; }
						if ( $address != NULL ) { $google_from = urlencode($address); } else { $google_from = $lat . ',' . $lon; }
						$avoidhighways = (isset($lmm_options[ 'directions_googlemaps_route_type_highways' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_highways' ] == 1 ) ? '&dirflg=h' : '';
						$avoidtolls = (isset($lmm_options[ 'directions_googlemaps_route_type_tolls' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_tolls' ] == 1 ) ? '&dirflg=t' : '';
						$publictransport = (isset($lmm_options[ 'directions_googlemaps_route_type_public_transport' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_public_transport' ] == 1 ) ? '&dirflg=r' : '';
						$walking = (isset($lmm_options[ 'directions_googlemaps_route_type_walking' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_walking' ] == 1 ) ? '&dirflg=w' : '';
						//info: Google language localization (directions)
						if ($lmm_options['google_maps_language_localization'] == 'browser_setting') {
							$google_language = '';
						} else if ($lmm_options['google_maps_language_localization'] == 'wordpress_setting') {
							if ( $locale != NULL ) { $google_language = '&hl=' . substr($locale, 0, 2); } else { $google_language =  '&hl=en'; }
						} else {
							$google_language = '&hl=' . $lmm_options['google_maps_language_localization'];
						}
						$lmm_out .= '<a href="https://' . $gmaps_base_domain_directions . '/maps?daddr=' . $google_from . '&amp;t=' . $lmm_options[ 'directions_googlemaps_map_type' ] . '&amp;layer=' . $lmm_options[ 'directions_googlemaps_traffic' ] . '&amp;doflg=' . $lmm_options[ 'directions_googlemaps_distance_units' ] . $avoidhighways . $avoidtolls . $publictransport . $walking . $google_language . '&amp;om=' . $lmm_options[ 'directions_googlemaps_overview_map' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img alt="' . esc_attr__('Get directions','lmm') . '" src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $directions_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" /></a>';
					} else if ($lmm_options['directions_provider'] == 'yours') {
						if ($lmm_options[ 'directions_yours_type_of_transport' ] == 'motorcar') { $directions_transport_type_icon = 'icon-car.png'; } else if ($lmm_options[ 'directions_yours_type_of_transport' ] == 'bicycle') { $directions_transport_type_icon = 'icon-bicycle.png'; } else if ($lmm_options[ 'directions_yours_type_of_transport' ] == 'foot') { $directions_transport_type_icon = 'icon-walk.png'; }
						$lmm_out .= '<a href="http://www.yournavigation.org/?tlat=' . $lat . '&amp;tlon=' . $lon . '&amp;v=' . $lmm_options[ 'directions_yours_type_of_transport' ] . '&amp;fast=' . $lmm_options[ 'directions_yours_route_type' ] . '&amp;layer=' . $lmm_options[ 'directions_yours_layer' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $directions_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
					} else if ($lmm_options['directions_provider'] == 'ors') {
						if ($lmm_options[ 'directions_ors_routeOpt' ] == 'Pedestrian') { $directions_transport_type_icon = 'icon-walk.png'; } else if ($lmm_options[ 'directions_ors_routeOpt' ] == 'Bicycle') { $directions_transport_type_icon = 'icon-bicycle.png'; } else { $directions_transport_type_icon = 'icon-car.png'; }
						$lmm_out .= '<a href="http://openrouteservice.org/?pos=' . $lon . ',' . $lat . '&amp;wp=' . $lon . ',' . $lat . '&amp;zoom=' . $zoom . '&amp;routeWeigh=' . $lmm_options[ 'directions_ors_routeWeigh' ] . '&amp;routeOpt=' . $lmm_options[ 'directions_ors_routeOpt' ] . '&amp;layer=' . $lmm_options[ 'directions_ors_layer' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $directions_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
					} else if ($lmm_options['directions_provider'] == 'bingmaps') {
						if ( $address != NULL ) { $bing_to = '_' . urlencode($address); } else { $bing_to = ''; }
						$lmm_out .= '<a href="https://www.bing.com/maps/default.aspx?v=2&amp;rtp=pos___e_~pos.' . $lat . '_' . $lon . $bing_to .'" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-car.png" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
					}
			}
			if ( (isset($lmm_options[ 'defaults_marker_panel_kml' ] ) == TRUE ) && ( $lmm_options[ 'defaults_marker_panel_kml' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-kml.php?marker=' . $id . '&amp;name=' . $lmm_options[ 'misc_kml' ] . '" style="text-decoration:none;" title="' . $text_export_kml . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" alt="' . $text_export_kml . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_marker_panel_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'defaults_marker_panel_fullscreen' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?marker=' . $id . '" style="text-decoration:none;" title="' . $text_fullscreen . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" alt="' . $text_fullscreen . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_marker_panel_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'defaults_marker_panel_qr_code' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-qr.php?marker=' . $id . '" target="_blank" title="' . $text_qr . '" rel="nofollow"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-qr-code.png" width="14" height="14" alt="' . $text_qr . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_marker_panel_geojson' ] ) == TRUE ) && ( $lmm_options[ 'defaults_marker_panel_geojson' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?marker=' . $id . '&amp;callback=jsonp&amp;full=yes&amp;full_icon_url=yes" style="text-decoration:none;" title="' . $text_geojson . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-json.png" width="14" height="14" alt="' . $text_geojson . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_marker_panel_georss' ] ) == TRUE ) && ( $lmm_options[ 'defaults_marker_panel_georss' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-georss.php?marker=' . $id . '" style="text-decoration:none;" title="' . $text_georss . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-georss.png" width="14" height="14" alt="' . $text_georss . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_marker_panel_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'defaults_marker_panel_wikitude' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-wikitude.php?marker=' . $id . '" style="text-decoration:none;" title="' . $text_wikitude . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-wikitude.png" width="14" height="14" alt="' . $text_wikitude . '" class="lmm-panel-api-images" /></a>';
			}
		$lmm_out .= '</div><div id="lmm_panel_text_'.$uid.'" class="lmm-panel-text" style="' . htmlspecialchars(addslashes($lmm_options[ 'defaults_marker_panel_paneltext_css' ])) . '">' . stripslashes($paneltext) . '</div>';
		}

		if (!empty($layer) && empty($marker)) //info: check if problems get reported - fix for marker name shown twice when layer+marker map on 1 page
		{
			$lmm_out .= '<div id="lmm_panel_api_'.$uid.'" class="lmm-panel-api">';
			if ( (isset($lmm_options[ 'defaults_layer_panel_kml' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_kml' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-kml.php?layer=' . $id . '&amp;name=' . $lmm_options[ 'misc_kml' ] . '" style="text-decoration:none;" title="' . $text_export_kml . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" alt="' . $text_export_kml . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_panel_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_fullscreen' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?layer=' . $id . '" style="text-decoration:none;" title="' . $text_fullscreen . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" alt="' . $text_fullscreen . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_panel_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_qr_code' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-qr.php?layer=' . $id . '" target="_blank" title="' . $text_qr . '" rel="nofollow"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-qr-code.png" width="14" height="14" alt="' . $text_qr . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_panel_geojson' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_geojson' ] == 1 ) ) {
				if ($multi_layer_map == 0 ) { $geojson_api_link = $id; } else { $geojson_api_link = $multi_layer_map_list; }
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?layer=' . $geojson_api_link . '&amp;callback=jsonp&amp;full=yes&amp;full_icon_url=yes" style="text-decoration:none;" title="' . $text_geojson . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-json.png" width="14" height="14" alt="' . $text_geojson . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_panel_georss' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_georss' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-georss.php?layer=' . $id . '" style="text-decoration:none;" title="' . $text_georss . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-georss.png" width="14" height="14" alt="' . $text_georss . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_panel_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_wikitude' ] == 1 ) ) {
				$lmm_out .= '<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-wikitude.php?layer=' . $id . '" style="text-decoration:none;" title="' . $text_wikitude . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-wikitude.png" width="14" height="14" alt="' . $text_wikitude . '" class="lmm-panel-api-images" /></a>';
			}
		$lmm_out .= '</div><div id="lmm_panel_text_'.$uid.'" class="lmm-panel-text" style="' . htmlspecialchars(addslashes($lmm_options[ 'defaults_layer_panel_paneltext_css' ])) . '">' . stripslashes($paneltext) . '</div>'.PHP_EOL;
		}
	$lmm_out .= '</div>'.PHP_EOL; //info: <!--end lmm-panel-->
	}
	$admin_error_link = (current_user_can( 'manage_options' )) ? '<br/><small><a href="https://www.mapsmarker.com/wp_footer" target="_blank" style="text-decoration:none;">' . __('click here for troubleshooting','lmm') . '</a></small>' : '';
	$lmm_out .= '<div id="'.$mapname.'" class="lmm-map" style="background:#f6f6f6;border:1px solid #ccc;height:'.$mapheight.'px; overflow:hidden;padding:0;"><p style="font-size:80%;color:#9f9e9e;margin-left:5px;" class="lmm-loading">' . $text_loading_map . $admin_error_link . '<noscript><br/><strong>' . __('Map could not be loaded - please enable Javascript!','lmm') . '</strong><br/><a style="text-decoration:none;" href="https://www.mapsmarker.com/js-disabled" target="_blank">&rarr; ' . __('more information','lmm') . '</a></noscript></p></div>'. PHP_EOL;

	//info: add gpx panel
	if ($gpx_url != NULL) {
		$gpx_panel_state = ($gpx_panel == 1) ? 'block' : 'none';
		$lmm_out .= '<div id="gpx-panel-' . $uid . '" class="gpx-panel" style="display:' . $gpx_panel_state . '; background: ' . htmlspecialchars(addslashes($lmm_options[ 'defaults_marker_panel_background_color' ])) . ';">'.PHP_EOL;
		if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') { $gpx_unit_distance = 'km'; $gpx_unit_elevation = 'm'; } else { $gpx_unit_distance = 'mi'; $gpx_unit_elevation = 'ft'; }
		if ( (isset($lmm_options[ 'gpx_metadata_name' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_name' ] == 1 ) ) {
			$gpx_metadata_name = '<label for="gpx-name">' . $text_gpx_track_name . ':</label> <span id="gpx-name" class="gpx-name"></span>';
		} else { $gpx_metadata_name = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_start' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_start' ] == 1 ) ) {
			$gpx_metadata_start = '<label for="gpx-start">' . $text_gpx_start_track . ':</label> <span id="gpx-start" class="gpx-start"></span>';
		} else { $gpx_metadata_start = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_end' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_end' ] == 1 ) ) {
			$gpx_metadata_end = '<label for="gpx-end">' . $text_gpx_end_track . ':</label> <span id="gpx-end" class="gpx-end"></span>';
		} else { $gpx_metadata_end = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_distance' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_distance' ] == 1 ) ) {
			$gpx_metadata_distance = '<label for="gpx-distance">' . $text_gpx_distance . ':</label> <span id="gpx-distance"><span class="gpx-distance"></span> ' . $gpx_unit_distance . '</span>';
		} else { $gpx_metadata_distance = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_duration_moving' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_moving' ] == 1 ) ) {
			$gpx_metadata_duration_moving = '<label for="gpx-duration-moving">' . $text_gpx_moving_time . ':</label> <span id="gpx-duration-moving" class="gpx-duration-moving"></span> ';
		} else { $gpx_metadata_duration_moving = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_duration_total' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_total' ] == 1 ) ) {
			$gpx_metadata_duration_total = '<label for="gpx-duration-total">' . $text_gpx_duration . ':</label> <span id="gpx-duration-total" class="gpx-duration-total"></span> ';
		} else { $gpx_metadata_duration_total = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_avpace' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avpace' ] == 1 ) ) {
			$gpx_metadata_avpace = '<label for="gpx-avpace">&#216;&nbsp;' . $text_gpx_pace . ':</label> <span id="gpx-avpace"><span class="gpx-avpace"></span>/' . $gpx_unit_distance . '</span>';
		} else { $gpx_metadata_avpace = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_avhr' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avhr' ] == 1 ) ) {
			$gpx_metadata_avhr = '<label for="gpx-avghr">&#216;&nbsp;' . $text_gpx_heart_rate . ':</label> <span id="gpx-avghr" class="gpx-avghr"></span>';
		} else { $gpx_metadata_avhr = NULL; }
		if ( ((isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 )) ) {
			$gpx_metadata_elevation_title = '<label for="gpx-elevation">' . $text_gpx_elevation . ':</label> <span id="gpx-elevation">';
		} else { $gpx_metadata_elevation_title = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 ) ) {
			$gpx_metadata_elev_gain = '+<span class="gpx-elevation-gain"></span>' . $gpx_unit_elevation;
		} else { $gpx_metadata_elev_gain = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 ) ) {
			$gpx_metadata_elev_loss = '-<span class="gpx-elevation-loss"></span>' . $gpx_unit_elevation;
		} else { $gpx_metadata_elev_loss = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 ) ) {
			$gpx_metadata_elev_net = '(' . __('net','lmm') . ': <span class="gpx-elevation-net"></span>' . $gpx_unit_elevation . ')</span>'; //info: </span> ->elevation-ID
		} else { $gpx_metadata_elev_net = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_full' ] == 1 ) ) {
			$gpx_metadata_elev_full = '<br/><label for="gpx-elevation-full">' . $text_gpx_full_elevation_data . ':</label><br/><span id="gpx-elevation-full" class="gpx-elevation-full"></span>';
		} else { $gpx_metadata_elev_full = NULL; }
		if ( (isset($lmm_options[ 'gpx_metadata_hr_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_hr_full' ] == 1 ) ) {
			$gpx_metadata_hr_full = '<br/><label for="gpx-heartrate-full">' . __('Full heart rate data','lmm') . ':</label><br/><span id="gpx-heartrate-full" class="gpx-heartrate-full"></span>';
		} else { $gpx_metadata_hr_full = NULL; }
		$gpx_metadata_elevation_array = array($gpx_metadata_elevation_title, $gpx_metadata_elev_gain, $gpx_metadata_elev_loss, $gpx_metadata_elev_net);
		$gpx_metadata_elevation = implode(' ',$gpx_metadata_elevation_array);
		if ($gpx_metadata_elevation == '   ') { $gpx_metadata_elevation = NULL; } //info: for no trailing |
		$gpx_metadata_array_all = array($gpx_metadata_name, $gpx_metadata_start, $gpx_metadata_end, $gpx_metadata_distance, $gpx_metadata_duration_moving, $gpx_metadata_duration_total, $gpx_metadata_avpace, $gpx_metadata_avhr, $gpx_metadata_elevation, $gpx_metadata_elev_full, $gpx_metadata_hr_full);

		$gpx_metadata_array_not_null = array();
		foreach ($gpx_metadata_array_all as $key => $value) {
			if (is_null($value) === false) {
				$gpx_metadata_array_not_null[$key] = $value;
			}
		}
		$gpx_metadata = implode(' <span class="gpx-delimiter">|</span> ',$gpx_metadata_array_not_null);
		$lmm_out .= $gpx_metadata;
		if ( (isset($lmm_options[ 'gpx_metadata_gpx_download' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_gpx_download' ] == 1 ) ) {
			if (empty($layer)) { $map_type = 'marker'; } else if (empty($marker)) {	$map_type = 'layer'; }
			$lmm_out .= ' <span class="gpx-delimiter">|</span> <span id="gpx-download"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-download.php?map_type=' . $map_type . '&map_id=' . $id . '&format=gpx" title="' . $text_download_gpx . '">' . $text_download_gpx . ' <img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-download-gpx.png" width="10" height="10" alt="' . $text_download_gpx . '" class="lmm-icon-download-gpx"></a></span>'.PHP_EOL;
		}
		$lmm_out .= '</div>';
	}


	//info: prepare popuptext for hidden div incl. do_shortcode-support
	if ( ((empty($layer) && empty($marker))) || (!empty($marker)) ) {

		//info: prepare directionslink for marker popups
		if ($lmm_options['directions_popuptext_panel'] == 'yes') {

			$mpopuptext_css = ($mpopuptext != NULL) ? "border-top:1px solid #f0f0e7;padding-top:5px;margin-top:5px;clear:both;" : "";
			$mpopuptext = $mpopuptext . '<div class=\'popup-directions\' style=\'' . $mpopuptext_css . '\'>' . $address . ' (';

			if ($lmm_options['directions_provider'] == 'googlemaps') {
				if ( isset($lmm_options['google_maps_base_domain_custom']) && ($lmm_options['google_maps_base_domain_custom'] == NULL) ) { $gmaps_base_domain_directions = $lmm_options['google_maps_base_domain']; } else { $gmaps_base_domain_directions = htmlspecialchars($lmm_options['google_maps_base_domain_custom']); }
				if ( $address != NULL ) { $google_from = urlencode($address); } else { $google_from = $lat . ',' . $lon; }
				$avoidhighways = (isset($lmm_options[ 'directions_googlemaps_route_type_highways' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_highways' ] == 1 ) ? '&dirflg=h' : '';
				$avoidtolls = (isset($lmm_options[ 'directions_googlemaps_route_type_tolls' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_tolls' ] == 1 ) ? '&dirflg=t' : '';
				$publictransport = (isset($lmm_options[ 'directions_googlemaps_route_type_public_transport' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_public_transport' ] == 1 ) ? '&dirflg=r' : '';
				$walking = (isset($lmm_options[ 'directions_googlemaps_route_type_walking' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_walking' ] == 1 ) ? '&dirflg=w' : '';
				//info: Google language localization (directions)
				if ($lmm_options['google_maps_language_localization'] == 'browser_setting') {
					$google_language = '';
				} else if ($lmm_options['google_maps_language_localization'] == 'wordpress_setting') {
					if ( $locale != NULL ) { $google_language = '&hl=' . substr($locale, 0, 2); } else { $google_language =  '&hl=en'; }
				} else {
					$google_language = '&hl=' . $lmm_options['google_maps_language_localization'];
				}
				$mpopuptext = $mpopuptext . "<a href=http://" . $gmaps_base_domain_directions . "/maps?daddr=" . $google_from . "&t=" . $lmm_options[ 'directions_googlemaps_map_type' ] . "&layer=" . $lmm_options[ 'directions_googlemaps_traffic' ] . "&doflg=" . $lmm_options[ 'directions_googlemaps_distance_units' ] . $avoidhighways . $avoidtolls . $publictransport . $walking . $google_language . "&om=" . $lmm_options[ 'directions_googlemaps_overview_map' ] . " target='_blank' title='" . esc_attr__('Get directions','lmm') . "'>" . $text_directions . "</a>";
			} else if ($lmm_options['directions_provider'] == 'yours') {
				$mpopuptext = $mpopuptext . "<a href=http://www.yournavigation.org/?tlat=" . $lat . "&tlon=" . $lon . "&v=" . $lmm_options[ 'directions_yours_type_of_transport' ] . "&fast=" . $lmm_options[ 'directions_yours_route_type' ] . "&layer=" . $lmm_options[ 'directions_yours_layer' ] . " target='_blank' title='" . esc_attr__('Get directions','lmm') . "'>" . $text_directions . "</a>";
			} else if ($lmm_options['directions_provider'] == 'ors') {
				$mpopuptext = $mpopuptext . "<a href=http://openrouteservice.org/?pos=" . $lon . "," . $lat . "&wp=" . $lon . "," . $lat . "&zoom=" . $zoom . "&routeWeigh=" . $lmm_options[ 'directions_ors_routeWeigh' ] . "&routeOpt=" . $lmm_options[ 'directions_ors_routeOpt' ] . "&layer=" . $lmm_options[ 'directions_ors_layer' ] . " target='_blank' title='" . esc_attr__('Get directions','lmm') . "'>" . $text_directions . "</a>";
			} else if ($lmm_options['directions_provider'] == 'bingmaps') {
				if ( $address != NULL ) { $bing_to = '_' . urlencode($address); } else { $bing_to = ''; }
				$mpopuptext = $mpopuptext . "<a href=https://www.bing.com/maps/default.aspx?v=2&rtp=pos___e_~pos." . $lat . "_" . $lon . $bing_to . " target='_blank' title='" . esc_attr__('Get directions','lmm') . "'>" . $text_directions . "</a>";
			}
			$mpopuptext = $mpopuptext . ')</div>';
		}

		if ($lmm_options['defaults_marker_popups_add_markername'] == 'true') {
			if ($markername != NULL) {
				$markername_popup_hidden = '<div class="popup-markername"  style="border-bottom:1px solid #f0f0e7;padding-bottom:5px;margin-bottom:6px;">' . stripslashes(strip_tags(htmlspecialchars_decode($markername))) . '</div>';
			} else {
				$markername_popup_hidden = '';
			}
		} else {
			$markername_popup_hidden = '';
		}
		//info: sanitize popuptext (line breaks for HTML lists)
		$sanitize_popuptext_from = array(
			'#<ul(.*?)>(\s)*(<br\s*/?>)*(\s)*<li(.*?)>#si',
			'#</li>(\s)*(<br\s*/?>)*(\s)*<li(.*?)>#si',
			'#</li>(\s)*(<br\s*/?>)*(\s)*</ul>#si',
			'#<ol(.*?)>(\s)*(<br\s*/?>)*(\s)*<li(.*?)>#si',
			'#</li>(\s)*(<br\s*/?>)*(\s)*</ol>#si',
			'#(<br\s*/?>){1}\s*<ul(.*?)>#si',
			'#(<br\s*/?>){1}\s*<ol(.*?)>#si',
			'#</ul>\s*(<br\s*/?>){1}#si',
			'#</ol>\s*(<br\s*/?>){1}#si',
		);
		$sanitize_popuptext_to = array(
			'<ul$1><li$5>',
			'</li><li$4>',
			'</li></ul>',
			'<ol$1><li$5>',
			'</li></ol>',
			'<ul$2>',
			'<ol$2>',
			'</ul>',
			'</ol>'
		);
		$mpopuptext_sanitized = preg_replace($sanitize_popuptext_from, $sanitize_popuptext_to, stripslashes(preg_replace( '/(\015\012)|(\015)|(\012)/','<br />', $mpopuptext)));
		$lmm_out .= "<div style='display:none' id='" . $mapname . "-popuptext-hidden'>".$markername_popup_hidden.do_shortcode($mpopuptext_sanitized)."</div>".PHP_EOL;
	}

	//info: add geo microformats for layer maps
	if (!empty($layer) && empty($marker))
	{
	$table_name_markers = $wpdb->prefix.'leafletmapsmarker_markers';
	$table_name_layers = $wpdb->prefix.'leafletmapsmarker_layers';
	$layer_mark_list_microformats = $wpdb->get_results('SELECT l.id as lid,l.name as lname, m.lon as mlon, m.lat as mlat, m.markername as markername,m.id as markerid FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id = ' . intval($layer) . ' LIMIT 250', ARRAY_A);
		if (count($layer_mark_list_microformats) < 1) {
			$lmm_out .= '<div id="lmm_geo_tags_'.$uid.'" class="lmm-geo-tags geo">' . $paneltext . ': <span class="latitude">' . $lat . '</span>, <span class="longitude">' . $lon . '</span></div>'.PHP_EOL;
		} else {
			foreach ($layer_mark_list_microformats as $row){
				$lmm_out .= '<div id="lmm_geo_tags_'.$uid.'_'.$row['markerid'].'" class="lmm-geo-tags geo">' . htmlspecialchars($row['markername']) . ': <span class="latitude">' . $row['mlat'] . '</span>, <span class="longitude">' . $row['mlon'] . '</span></div>'.PHP_EOL;
			}
		}
	}
	//info: add geo microformats for marker maps
	if (!empty($marker))
	{
	//info: add geo microformats
	$lmm_out .= '<div id="lmm_geo_tags_'.$uid.'" class="lmm-geo-tags geo">'.PHP_EOL;
	$lmm_out .= '<span class="paneltext">' . $paneltext . '</span>'.PHP_EOL;
	$lmm_out .= '<span class="latitude">' . $lat . '</span>, <span class="longitude">' . $lon . '</span>'.PHP_EOL;
	$lmm_out .= '<span class="popuptext">' . strip_tags($mpopuptext) .'</span>'.PHP_EOL;
	$lmm_out .= '</div>'.PHP_EOL;
	}
	//info: add geo microformats for marker maps added directly via shortcode
	if (empty($layer) && empty($marker))
	{
	//info: add geo microformats
	$lmm_out .= '<div id="lmm_geo_tags_'.$uid.'" class="lmm-geo-tags geo">'.PHP_EOL;
	$lmm_out .= '<span class="latitude">' . $mlat . '</span>, <span class="longitude">' . $mlon . '</span>'.PHP_EOL;
	$lmm_out .= '</div>'.PHP_EOL;
	}

	//info: preload area for CSS background images (home button etc)
	$lmm_out .= '<div class="lmm-preload-area"></div>'.PHP_EOL;

	//info: display a list of markers under the map
	if ( !empty($layer) && empty($marker) && ($listmarkers == 1) )
	{
	//info: sqls for singe and multi-layer-maps
	if ($multi_layer_map == 0) {

	if($lmm_options[ 'defaults_layer_listmarkers_show_distance_unit' ] == 'km' || $lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance.km' ){ //info: needed fallback as setting name has changed
			$distance_query = " ( 6371 * acos( cos( radians(m.lat) ) * cos( radians( l.layerviewlat ) ) * cos( radians( l.layerviewlon ) - radians(m.lon) ) + sin( radians(m.lat) ) * sin(radians(l.layerviewlat)) ) ) AS distance,";
			if($lmm_options[ 'defaults_layer_listmarkers_order_by' ] != 'distance_layer_center' && $lmm_options[ 'defaults_layer_listmarkers_order_by' ] != 'distance_current_position'){
				$layer_marker_list = $wpdb->get_results('SELECT  '. $distance_query .' l.id as lid, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid, m.createdon as mcreatedon, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp, m.zoom as mzoom FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$id.' ORDER BY '. $lmm_options[ 'defaults_layer_listmarkers_order_by' ] .' ' . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . ' LIMIT ' . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]), ARRAY_A);
			}else{
				$layer_marker_list = $wpdb->get_results('SELECT  '. $distance_query .' l.id as lid, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid, m.createdon as mcreatedon, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp, m.zoom as mzoom FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$id.' ORDER BY distance ' . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . ' LIMIT ' . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]), ARRAY_A);
			}
		}else if($lmm_options[ 'defaults_layer_listmarkers_show_distance_unit' ] == 'mile' ||  $lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance.mile' ){
			$distance_query = " ( 3959 * acos( cos( radians(m.lat) ) * cos( radians( l.layerviewlat ) ) * cos( radians( l.layerviewlon ) - radians(m.lon) ) + sin( radians(m.lat) ) * sin(radians(l.layerviewlat)) ) ) AS distance,";
			if($lmm_options[ 'defaults_layer_listmarkers_order_by' ] != 'distance_layer_center' && $lmm_options[ 'defaults_layer_listmarkers_order_by' ] != 'distance_current_position'){
				$layer_marker_list = $wpdb->get_results('SELECT '. $distance_query .' l.id as lid, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid, m.createdon as mcreatedon, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp, m.zoom as mzoom FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$id.' ORDER BY '. $lmm_options[ 'defaults_layer_listmarkers_order_by' ]  .' ' . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . ' LIMIT ' . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]), ARRAY_A);
			}else{
				$layer_marker_list = $wpdb->get_results('SELECT '. $distance_query .' l.id as lid, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid, m.createdon as mcreatedon, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp, m.zoom as mzoom FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$id.' ORDER BY distance ' . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . ' LIMIT ' . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]), ARRAY_A);
			}
		}else{
			$layer_marker_list = $wpdb->get_results('SELECT l.id as lid, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid, m.createdon as mcreatedon, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp, m.zoom as mzoom FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$id.' ORDER BY ' . $lmm_options[ 'defaults_layer_listmarkers_order_by' ] . ' ' . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . ' LIMIT ' . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]), ARRAY_A);
		}

	} else if ($multi_layer_map == 1) {
			$distance_query = '';
			if( $lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance_layer_center' || $lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance.km' || $lmm_options['defaults_layer_listmarkers_show_distance'] == 1){ //info: needed fallback as setting name has changed
				if( $lmm_options[ 'defaults_layer_listmarkers_show_distance_unit' ] == 'km' || $lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance.km' ){
					$distance_query = " ( 6371 * acos( cos( radians(m.lat) ) * cos( radians( l.layerviewlat ) ) * cos( radians( l.layerviewlon ) - radians(m.lon) ) + sin( radians(m.lat) ) * sin(radians(l.layerviewlat)) ) ) AS distance,";
				}else if( $lmm_options[ 'defaults_layer_listmarkers_show_distance_unit' ] == 'mile' || $lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance.mile' ){
					$distance_query = " ( 3959 * acos( cos( radians(m.lat) ) * cos( radians( l.layerviewlat ) ) * cos( radians( l.layerviewlon ) - radians(m.lon) ) + sin( radians(m.lat) ) * sin(radians(l.layerviewlat)) ) ) AS distance,";
				}
			}
			//info: set sort order for multi-layer-maps based on list-marker-setting
			if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.id') {
				$sort_order_mlm = 'markerid';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.markername') {
				$sort_order_mlm = 'markername';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.popuptext') {
				$sort_order_mlm = 'mpopuptext';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.icon') {
				$sort_order_mlm = 'micon';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.createdby') {
				$sort_order_mlm = 'mcreatedby';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.createdon') {
				$sort_order_mlm = 'mcreatedon';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.updatedby') {
				$sort_order_mlm = 'mupdatedby';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.updatedon') {
				$sort_order_mlm = 'mupdatedon';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.layer') {
				$sort_order_mlm = 'mlayer';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.address') {
				$sort_order_mlm = 'maddress';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.kml_timestamp') {
				$sort_order_mlm = 'mkml_timestamp';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'distance_layer_center' || $lmm_options['defaults_layer_listmarkers_order_by'] == 'distance.km' || $lmm_options['defaults_layer_listmarkers_order_by'] == 'distance.mile') {
				$sort_order_mlm = 'distance';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'distance_current_position' || $lmm_options['defaults_layer_listmarkers_order_by'] == 'distance.km' || $lmm_options['defaults_layer_listmarkers_order_by'] == 'distance.mile') {
				$sort_order_mlm = 'distance';
			}

			if ( (count($multi_layer_map_list_exploded) == 1) && ($multi_layer_map_list != 'all') && ($multi_layer_map_list != NULL) ) { //info: only 1 layer selected
				$mlm_query = "SELECT ". $distance_query ." l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $multi_layer_map_list . "' ORDER BY " . $sort_order_mlm . " " . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . " LIMIT " . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]);
				$layer_marker_list = $wpdb->get_results($mlm_query, ARRAY_A);
			} //info: end (count($multi_layer_map_list_exploded) == 1) && ($multi_layer_map_list != 'all') && ($multi_layer_map_list != NULL)
			else if ( (count($multi_layer_map_list_exploded) > 1 ) && ($multi_layer_map_list != 'all') ) {
				$first_mlm_id = $multi_layer_map_list_exploded[0];
				$other_mlm_ids = array_slice($multi_layer_map_list_exploded,1);
				$mlm_query = "(SELECT ". $distance_query ." l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $first_mlm_id . "')";
				foreach ($other_mlm_ids as $row) {
					$mlm_query .= " UNION (SELECT ". $distance_query ." l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $row . "')";
				}
				$mlm_query .= " ORDER BY " . $sort_order_mlm . " " . $lmm_options['defaults_layer_listmarkers_sort_order'] . " LIMIT " . intval($lmm_options['defaults_layer_listmarkers_limit']) . "";
				$layer_marker_list = $wpdb->get_results($mlm_query, ARRAY_A);
			} //info: end else if ( (count($multi_layer_map_list_exploded) > 1 ) && ($multi_layer_map_list != 'all')
			else if ($multi_layer_map_list == 'all') {
				$first_mlm_id = '0';
				$mlm_all_layers = $wpdb->get_results( "SELECT id FROM $table_name_layers", ARRAY_A );
				$other_mlm_ids = array_slice($mlm_all_layers,1);
				$mlm_query = "(SELECT  ". $distance_query ." l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $first_mlm_id . "')";
				foreach ($other_mlm_ids as $row) {
					$mlm_query .= " UNION (SELECT ". $distance_query ." l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $row['id'] . "')";
				}
				$mlm_query .= " ORDER BY " . $sort_order_mlm . " " . $lmm_options['defaults_layer_listmarkers_sort_order'] . " LIMIT " . intval($lmm_options['defaults_layer_listmarkers_limit']) . "";
				$layer_marker_list = $wpdb->get_results($mlm_query, ARRAY_A);
			} //info: end else if ($multi_layer_map_list == 'all')
			else { //info: if ($multi_layer_map == 1) but no layers selected
				$layer_marker_list = array();
			}
	} //info: end main - else if ($multi_layer_map == 1)

	//info: set list markers width to be 100% of maps width
	if ($mapwidthunit == '%') {
		$layer_marker_list_width = '100%';
	} else {
		$layer_marker_list_width = $mapwidth.$mapwidthunit;
	}
	$lmm_out .= '<div id="lmm_listmarkers_'.$uid.'" class="lmm-listmarkers" style="width:' . $layer_marker_list_width . ';">'.PHP_EOL;
	$lmm_out .= '<input type="hidden" id="'.$uid.'_multi_layer_map" name="multi_layer_map" value="' . $multi_layer_map . '" />';
	$lmm_out .= '<input type="hidden" id="'.$uid.'_multi_layer_map_list" name="multi_layer_map_list" value="' . $multi_layer_map_list. '" />';
	$lmm_out .= '<table style="width:' . $layer_marker_list_width . ';" id="lmm_listmarkers_table_'.$uid.'" class="lmm-listmarkers-table" data-mapname="'. 'layermap_'. intval($layer) .'">';
	$order_by = $lmm_options['defaults_layer_listmarkers_order_by'];
	if($lmm_options['defaults_layer_listmarkers_action_bar'] != 'hide'){
		$lmm_out .= '<tr id="search_markers_row_'.$uid.'">'.PHP_EOL;
		$lmm_out .= '	<td colspan="2" class="lmm-search-markers-row">'.PHP_EOL;
		if($lmm_options['defaults_layer_listmarkers_action_bar'] != 'show-sort-order-selection-only'){
			$defaults_layer_listmarkers_searchtext = ($lmm_options['defaults_layer_listmarkers_searchtext'] == NULL) ? __('Search markers','lmm') : esc_attr(strip_tags($lmm_options['defaults_layer_listmarkers_searchtext']));
			$defaults_layer_listmarkers_searchtext_hover = ($lmm_options['defaults_layer_listmarkers_searchtext_hover'] == NULL) ? __('start typing to find marker entries based on markername or popuptext','lmm') : esc_attr(strip_tags( $lmm_options['defaults_layer_listmarkers_searchtext_hover']));
			$lmm_out .= '		<input id="search_markers_'.$uid.'" class="lmm-search-markers" type="text" value="" data-mapid="'.$uid.'" placeholder="'.$defaults_layer_listmarkers_searchtext.'" title="'. $defaults_layer_listmarkers_searchtext_hover .'" />'.PHP_EOL;
		}
		if($lmm_options['defaults_layer_listmarkers_action_bar'] == 'show-sort-order-selection-only' || $lmm_options['defaults_layer_listmarkers_action_bar'] == 'show-full'){
			$order_class = ($lmm_options[ 'defaults_layer_listmarkers_sort_order' ] == 'ASC')?'up':'down';
			$order_hover_text = ($order_class == 'up')?__('sort order ascending','lmm'):__('sort order descending','lmm');
			$order_value_hover_text = ($order_class == 'down')?__('ascending','lmm'):__('descending','lmm');
			$order_text = mmp_get_order_text($order_by);
			$lmm_out .= '<div class="dropdown" title="' . esc_attr__('sort order','lmm') . '">'.PHP_EOL;
			$lmm_out .= '			  <button class="dropbtn '. $order_class .'" title="'.$order_hover_text.'">'. $order_text .'</button>'.PHP_EOL;
			$lmm_out .= '			  <div class="dropdown-content" data-mapid="'.$uid.'">'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_id']) && $lmm_options['defaults_layer_listmarkers_sort_id'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), 'ID') . '" data-sortby="m.id" class="lmm-sort-by ' . ($order_by == 'm.id'?$order_class:'') .'">ID</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_markername']) && $lmm_options['defaults_layer_listmarkers_sort_markername'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('marker name','lmm'), $order_value_hover_text) . '" data-sortby="m.markername" class="lmm-sort-by ' . ($order_by == 'm.markername'?$order_class:'') .'">'.__('marker name','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_popuptext']) && $lmm_options['defaults_layer_listmarkers_sort_popuptext'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('popuptext','lmm'), $order_value_hover_text) . '" data-sortby="m.popuptext" class="lmm-sort-by ' . ($order_by == 'm.popuptext'?$order_class:'') .'">'.__('popuptext','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_icon']) && $lmm_options['defaults_layer_listmarkers_sort_icon'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('icon','lmm'), $order_value_hover_text) . '" data-sortby="m.icon" class="lmm-sort-by ' . ($order_by == 'm.icon'?$order_class:'') .'">'.__('icon','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_created_by']) && $lmm_options['defaults_layer_listmarkers_sort_created_by'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('created by','lmm'), $order_value_hover_text) . '" data-sortby="m.createdby" class="lmm-sort-by ' . ($order_by == 'm.createdby'?$order_class:'') .'">'.__('created by','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_created_on']) && $lmm_options['defaults_layer_listmarkers_sort_created_on'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('created on','lmm'), $order_value_hover_text) . '" data-sortby="m.createdon" class="lmm-sort-by ' . ($order_by == 'm.createdon'?$order_class:'') .'">'.__('created on','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_updated_by']) && $lmm_options['defaults_layer_listmarkers_sort_updated_by'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('updated by','lmm'), $order_value_hover_text) . '" data-sortby="m.updatedby" class="lmm-sort-by ' . ($order_by == 'm.updatedby'?$order_class:'') .'">'.__('updated by','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_updated_on']) && $lmm_options['defaults_layer_listmarkers_sort_updated_on'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('updated on','lmm'), $order_value_hover_text) . '" data-sortby="m.updatedon" class="lmm-sort-by ' . ($order_by == 'm.updatedon'?$order_class:'') .'">'.__('updated on','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_layer_id']) && $lmm_options['defaults_layer_listmarkers_sort_layer_id'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('layer ID','lmm'), $order_value_hover_text) . '" data-sortby="m.layer" class="lmm-sort-by ' . ($order_by == 'm.layer'?$order_class:'') .'">'.__('layer ID','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_address']) && $lmm_options['defaults_layer_listmarkers_sort_address'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('address','lmm'), $order_value_hover_text) . '" data-sortby="m.address" class="lmm-sort-by ' . ($order_by == 'm.address'?$order_class:'') .'">'.__('address','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_kml_timestamp']) && $lmm_options['defaults_layer_listmarkers_sort_kml_timestamp'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('KML timestamp','lmm'), $order_value_hover_text) . '" data-sortby="m.kml_timestamp" class="lmm-sort-by ' . ($order_by == 'm.kml_timestamp'?$order_class:'') .'">'.__('KML timestamp','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_distance_layer_center']) && $lmm_options['defaults_layer_listmarkers_sort_distance_layer_center'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('distance from layer center','lmm'), $order_value_hover_text) . '" data-sortby="distance_layer_center" class="lmm-sort-by ' . ($order_by == 'distance_layer_center'?$order_class:'') .'">'.__('distance from layer center','lmm').'</a>'.PHP_EOL;
			if(isset($lmm_options['defaults_layer_listmarkers_sort_distance_current_pos']) && $lmm_options['defaults_layer_listmarkers_sort_distance_current_pos'] == 1)
				$lmm_out .= '<a href="javascript:void(0)" title="' . sprintf(esc_attr__('click to sort list by %1$s %2$s','lmm'), __('distance from current position','lmm'), $order_value_hover_text) . '" data-sortby="distance_current_position"  class="lmm-sort-by ' . ($order_by == 'distance_current_position'?$order_class:'') .'">'.__('distance from current position','lmm').'</a>'.PHP_EOL;
			$lmm_out .= '			  </div>'.PHP_EOL;
			$lmm_out .= '			</div>'.PHP_EOL;
		}
		$lmm_out .= '	</td>'.PHP_EOL;
		$lmm_out .= '</tr>'.PHP_EOL;
	}
	foreach ($layer_marker_list as $row){
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_icon' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_icon' ] == 1 ) ) {
			$lmm_out .= '<tr id="marker_'.$row['markerid'].'"><td class="lmm-listmarkers-icon">';
			if ($lmm_options['defaults_layer_listmarkers_link_action'] != 'disabled') {
				$listmarkers_href_a = '<a href="javascript:void(0);" onclick="javascript:listmarkers_action_' . $uid . '(' . $row['markerid'] . ')">';
				$listmarkers_href_b = '</a>';
			} else {
				$listmarkers_href_a = '';
				$listmarkers_href_b = '';
			}
			if ($lmm_options['defaults_marker_popups_add_markername'] == 'true') {
				$markername_on_hover = 'title="' . stripslashes(htmlspecialchars($row['markername'])) . '"';
			} else {
				$markername_on_hover = '';
			}
			if ($row['micon'] != null) {
				$lmm_out .= $listmarkers_href_a . '<img style="border-radius:0;box-shadow:none;" width="' . intval($lmm_options[ 'defaults_marker_icon_iconsize_x' ]) . '" height="' . intval($lmm_options[ 'defaults_marker_icon_iconsize_y' ]) . '" alt="marker icon" src="' . $defaults_marker_icon_url . '/'.$row['micon'].'" ' . $markername_on_hover . ' />' . $listmarkers_href_b;
			} else {
				$lmm_out .= $listmarkers_href_a . '<img style="border-radius:0;box-shadow:none;" alt="marker icon" src="' . LEAFLET_PLUGIN_URL . 'leaflet-dist/images/marker.png" ' . $markername_on_hover . ' />' . $listmarkers_href_b;
			};
		} else {
			$lmm_out .= '<tr><td>';
		};
		$lmm_out .= '</td><td class="lmm-listmarkers-popuptext"><div class="lmm-listmarkers-panel-icons">';
		
		$edit_link = (current_user_can( $lmm_options[ 'capabilities_edit_others' ]))?'<a title="' . esc_attr__('Edit marker','lmm') . ' (ID ' . $row['markerid'].')" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id=' . $row['markerid'].'"><img class="lmm-panel-api-images" style="margin-right:3px !important;" src="' . LEAFLET_PLUGIN_URL . '/inc/img/icon-map-edit.png" width="16" height="16" alt="' . esc_attr__('Edit marker','lmm') . ' ID ' . $row['markerid'] . '"></a>':'';
		$lmm_out .= $edit_link;
		
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_directions' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_directions' ] == 1 ) ) {
			if ($lmm_options['directions_provider'] == 'googlemaps') {
				if ( isset($lmm_options['google_maps_base_domain_custom']) && ($lmm_options['google_maps_base_domain_custom'] == NULL) ) { $gmaps_base_domain_directions = $lmm_options['google_maps_base_domain']; } else { $gmaps_base_domain_directions = htmlspecialchars($lmm_options['google_maps_base_domain_custom']); }
				if ((isset($lmm_options[ 'directions_googlemaps_route_type_walking' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_walking' ] == 1 )) { $directions_transport_type_icon = 'icon-walk.png'; } else { $directions_transport_type_icon = 'icon-car.png'; }
				if ( $row['maddress'] != NULL ) { $google_from = urlencode($row['maddress']); } else { $google_from = $row['mlat'] . ',' . $row['mlon']; }
				$avoidhighways = (isset($lmm_options[ 'directions_googlemaps_route_type_highways' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_highways' ] == 1 ) ? '&dirflg=h' : '';
				$avoidtolls = (isset($lmm_options[ 'directions_googlemaps_route_type_tolls' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_tolls' ] == 1 ) ? '&dirflg=t' : '';
				$publictransport = (isset($lmm_options[ 'directions_googlemaps_route_type_public_transport' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_public_transport' ] == 1 ) ? '&dirflg=r' : '';
				$walking = (isset($lmm_options[ 'directions_googlemaps_route_type_walking' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_walking' ] == 1 ) ? '&dirflg=w' : '';
				//info: Google language localization (directions)
				if ($lmm_options['google_maps_language_localization'] == 'browser_setting') {
					$google_language = '';
				} else if ($lmm_options['google_maps_language_localization'] == 'wordpress_setting') {
					if ( $locale != NULL ) { $google_language = '&hl=' . substr($locale, 0, 2); } else { $google_language =  '&hl=en'; }
				} else {
					$google_language = '&hl=' . $lmm_options['google_maps_language_localization'];
				}
				$lmm_out .= '<a href="https://' . $gmaps_base_domain_directions . '/maps?daddr=' . $google_from . '&amp;t=' . $lmm_options[ 'directions_googlemaps_map_type' ] . '&amp;layer=' . $lmm_options[ 'directions_googlemaps_traffic' ] . '&amp;doflg=' . $lmm_options[ 'directions_googlemaps_distance_units' ] . $avoidhighways . $avoidtolls . $publictransport . $walking . $google_language . '&amp;om=' . $lmm_options[ 'directions_googlemaps_overview_map' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img alt="' . esc_attr__('Get directions','lmm') . '" src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $directions_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" /></a>';
			} else if ($lmm_options['directions_provider'] == 'yours') {
				if ($lmm_options[ 'directions_yours_type_of_transport' ] == 'motorcar') { $directions_transport_type_icon = 'icon-car.png'; } else if ($lmm_options[ 'directions_yours_type_of_transport' ] == 'bicycle') { $directions_transport_type_icon = 'icon-bicycle.png'; } else if ($lmm_options[ 'directions_yours_type_of_transport' ] == 'foot') { $directions_transport_type_icon = 'icon-walk.png'; }
				$lmm_out .= '<a href="http://www.yournavigation.org/?tlat=' . $row['mlat'] . '&amp;tlon=' . $row['mlon'] . '&amp;v=' . $lmm_options[ 'directions_yours_type_of_transport' ] . '&amp;fast=' . $lmm_options[ 'directions_yours_route_type' ] . '&amp;layer=' . $lmm_options[ 'directions_yours_layer' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $directions_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
			} else if ($lmm_options['directions_provider'] == 'ors') {
				if ($lmm_options[ 'directions_ors_routeOpt' ] == 'Pedestrian') { $directions_transport_type_icon = 'icon-walk.png'; } else if ($lmm_options[ 'directions_ors_routeOpt' ] == 'Bicycle') { $directions_transport_type_icon = 'icon-bicycle.png'; } else { $directions_transport_type_icon = 'icon-car.png'; }
				$lmm_out .= '<a href="http://openrouteservice.org/?pos=' . $row['mlon'] . ',' . $row['mlat'] . '&amp;wp=' . $row['mlon'] . ',' . $row['mlat'] . '&amp;zoom=' . $row['mzoom'] . '&amp;routeOpt=' . $lmm_options[ 'directions_ors_routeOpt' ] . '&amp;layer=' . $lmm_options[ 'directions_ors_layer' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $directions_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
			} else if ($lmm_options['directions_provider'] == 'bingmaps') {
				if ( $row['maddress'] != NULL ) { $bing_to = '_' . urlencode($row['maddress']); } else { $bing_to = ''; }
				$lmm_out .= '<a href="https://www.bing.com/maps/default.aspx?v=2&rtp=pos___e_~pos.' . $row['mlat'] . '_' . $row['mlon'] . $bing_to . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-car.png" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
			}
		}
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_fullscreen' ] == 1 ) ) {
			$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?marker=' . $row['markerid'] . '" style="text-decoration:none;" title="' . $text_fullscreen . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" alt="' . $text_fullscreen . '" class="lmm-panel-api-images" /></a>';
		}
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_kml' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_kml' ] == 1 ) ) {
			$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-kml.php?marker=' . $row['markerid'] . '&amp;name=' . $lmm_options[ 'misc_kml' ] . '" style="text-decoration:none;" title="' . $text_export_kml . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" alt="' . $text_export_kml . '" class="lmm-panel-api-images" /></a>';
		}
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_qr_code' ] == 1 ) ) {
			$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-qr.php?marker=' . $row['markerid'] . '" target="_blank" title="' . $text_qr . '" rel="nofollow"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-qr-code.png" width="14" height="14" alt="' . $text_qr . '" class="lmm-panel-api-images" /></a>';
		}
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_geojson' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_geojson' ] == 1 ) ) {
			$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?marker=' . $row['markerid'] . '&amp;callback=jsonp&amp;full=yes&amp;full_icon_url=yes" style="text-decoration:none;" title="' . $text_geojson . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-json.png" width="14" height="14" alt="' . $text_geojson . '" class="lmm-panel-api-images" /></a>';
		}
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_georss' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_georss' ] == 1 ) ) {
			$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-georss.php?marker=' . $row['markerid'] . '" style="text-decoration:none;" title="' . $text_georss . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-georss.png" width="14" height="14" alt="' . $text_georss . '" class="lmm-panel-api-images" /></a>';
		}
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_wikitude' ] == 1 ) ) {
			$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-wikitude.php?marker=' . $row['markerid'] . '" style="text-decoration:none;" title="' . $text_wikitude . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-wikitude.png" width="14" height="14" alt="' . $text_wikitude . '" class="lmm-panel-api-images" /></a>';
		}
		if ( isset($lmm_options[ 'defaults_layer_listmarkers_show_distance' ]  ) && ( $lmm_options[ 'defaults_layer_listmarkers_show_distance' ] == 1 ) && ($lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance_layer_center' || $lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance_current_position' || $lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance.km' || $lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance.mile') ) { //info: needed fallback as setting name has changed
			if ($lmm_options['defaults_layer_listmarkers_show_distance_unit'] == 'km') {
				$lmm_out .= '<br/><br/><span class="lmm-distance" title="' . $text_calculated_map_center . '">' . __('distance', 'lmm').': ' . round($row['distance'], intval($lmm_options[ 'defaults_layer_listmarkers_show_distance_precision' ])) . ' ' . __('km','lmm') . '</span>';
			} else if($lmm_options['defaults_layer_listmarkers_show_distance_unit'] == 'mile') {
				$lmm_out .= '<br/><br/><span class="lmm-distance" title="' . $text_calculated_map_center . '">' . __('distance', 'lmm').': ' . round($row['distance'], intval($lmm_options[ 'defaults_layer_listmarkers_show_distance_precision' ])) . ' ' . __('miles','lmm') . '</span>';
			}
		}
		$lmm_out .= '</div>';
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_markername' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_markername' ] == 1 ) ) {
			if ($lmm_options['defaults_layer_listmarkers_link_action'] != 'disabled') {
				$lmm_out .= '<span class="lmm-listmarkers-markername"><a title="' . $text_show_marker_on_map . '" href="javascript:void(0);" onclick="javascript:listmarkers_action_' . $uid . '(' . $row['markerid'] . ')">' . stripslashes(htmlspecialchars($row['markername'])) . '</a></span> ';
			} else {
				$lmm_out .= '<span class="lmm-listmarkers-markername">' . stripslashes(htmlspecialchars($row['markername'])) . '</span> ';
			}
		}
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_popuptext' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_popuptext' ] == 1 ) ) {
			$sanitize_popuptext_from = array(
				'#<ul(.*?)>(\s)*(<br\s*/?>)*(\s)*<li(.*?)>#si',
				'#</li>(\s)*(<br\s*/?>)*(\s)*<li(.*?)>#si',
				'#</li>(\s)*(<br\s*/?>)*(\s)*</ul>#si',
				'#<ol(.*?)>(\s)*(<br\s*/?>)*(\s)*<li(.*?)>#si',
				'#</li>(\s)*(<br\s*/?>)*(\s)*</ol>#si',
				'#(<br\s*/?>){1}\s*<ul(.*?)>#si',
				'#(<br\s*/?>){1}\s*<ol(.*?)>#si',
				'#</ul>\s*(<br\s*/?>){1}#si',
				'#</ol>\s*(<br\s*/?>){1}#si',
			);
			$sanitize_popuptext_to = array(
				'<ul$1><li$5>',
				'</li><li$4>',
				'</li></ul>',
				'<ol$1><li$5>',
				'</li></ol>',
				'<ul$2>',
				'<ol$2>',
				'</ul>',
				'</ol>'
			);
			$popuptext_sanitized = preg_replace($sanitize_popuptext_from, $sanitize_popuptext_to, stripslashes(preg_replace( '/(\015\012)|(\015)|(\012)/','<br />', $row['mpopuptext'])));
			$lmm_out .= '<br/><span class="lmm-listmarkers-popuptext-only">' . do_shortcode($popuptext_sanitized) . '</span>';
		}
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_address' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_address' ] == 1 ) ) {
			if ( $row['mpopuptext'] == NULL ) {
				$lmm_out .= stripslashes(htmlspecialchars($row['maddress']));
			} else if ( ($row['mpopuptext'] != NULL) && ($row['maddress'] != NULL) ) {
				$lmm_out .= '<div class="lmm-listmarkers-hr">' . stripslashes(htmlspecialchars($row['maddress'])) . '</div>';
			}
		}
		$lmm_out .= '</td></tr>';
	} //info: end foreach

   //info: adding info if more markers are available than listed in markers list
   $markercount = 0;
   if ($multi_layer_map == 0) {
		$markercount = $wpdb->get_var('SELECT count(*) FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.intval($id));
   } else if ( ($multi_layer_map == 1) && ( $multi_layer_map_list == 'all' ) ) {
		$markercount = intval($wpdb->get_var('SELECT COUNT(*) FROM '.$table_name_markers));
   } else if ( ($multi_layer_map == 1) && ( $multi_layer_map_list != NULL ) && ($multi_layer_map_list != 'all') ) {
		foreach ($multi_layer_map_list_exploded as $mlmrowcount){
		$mlm_count_temp{$mlmrowcount} = $wpdb->get_var('SELECT count(*) FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.intval($mlmrowcount));
		}
		$markercount = array_sum($mlm_count_temp);
   } else if ( ($multi_layer_map == 1) && ( $multi_layer_map_list == NULL ) ) {
		$markercount = 0;
   }
   if ($markercount > $lmm_options[ 'defaults_layer_listmarkers_limit' ]) {
	$asc_desc = ($lmm_options['defaults_layer_listmarkers_sort_order'] == 'ASC') ? __('ascending','lmm') : __('descending','lmm');
	if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.id') {
		$orderby = 'ID';
	} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.markername') {
		$orderby = __('marker name','lmm');
	} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.createdon') {
		$orderby = __('created on','lmm');
	} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.updatedon') {
		$orderby = __('updated on','lmm');
	} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.layer') {
		$orderby = __('layer ID','lmm');
	} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.address') {
		$orderby = __('address','lmm');
	} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'distance_layer_center') {
		if($lmm_options['defaults_layer_listmarkers_show_distance_unit'] == 'km')
			$orderby = __('distance from layer center in km','lmm');
		else
			$orderby = __('distance from layer center in mile','lmm');
	} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'distance_current_position') {
		if($lmm_options['defaults_layer_listmarkers_show_distance_unit'] == 'km' || $lmm_options['defaults_layer_listmarkers_order_by'] == 'distance.km')
			$orderby = __('distance from layer center in km','lmm');
		else
			$orderby = __('distance from layer center in mile','lmm');
	}
	$lmm_out .= '<tr id="pagination_row_'.$uid.'"><td colspan="2">' . lmm_get_markers_pagination($uid, $markercount, $multi_layer_map, $multi_layer_map_list, $order_by) . '</td></tr>';
	}else{
		$lmm_out .= '<input type="hidden" id="markers_per_page_'.$uid.'"  value="'.intval($lmm_options[ "defaults_layer_listmarkers_limit" ]).'" data-mapid="'.$uid.'" />';
		$lmm_out .= '<input type="hidden" id="'.$uid.'_orderby" name="orderby" value="' . $order_by . '" />';
		$lmm_out .= '<input type="hidden" id="'.$uid.'_order" name="order" value="' . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . '" />';
		$lmm_out .= '<input type="hidden" id="'.$uid.'_markercount" name="markercount" value="' . $markercount. '" />';
	}
	$lmm_out .= '<input type="hidden" id="'.$uid.'_id" name="id" value="' . $id. '" />';
	$lmm_out .= '</table></div>';
	} //info: end display a list of markers under the map

	//info: add js to footer / part 1/2
	if ($lmm_options['misc_javascript_header_footer_pro'] == 'footer') {
		$lmm_out .= '</div>'; //info: end leaflet_maps_marker_$uid
		global $lmmjs_out;
	} else {
		$lmmjs_out = '<script type="text/javascript">'.PHP_EOL;
 	}
	if ( $lmm_options['misc_backlinks'] == 'show' ) {
		$lmmjs_out .= '/* Maps created with Maps Marker Pro - #1 premium mapping plugin for WordPress (www.mapsmarker.com) */'.PHP_EOL;
	}
	if (!empty($layer)) {
		$mapname_js = 'layermap_' . intval($layer);
		$mapid_js = intval($layer);
	} else if (!empty($marker)) {
		$mapname_js = 'markermap_' . intval($marker);
		$mapid_js = intval($marker);
	} else if (empty($layer) and empty($marker)) {
		$mapname_js = 'markermap_' . str_replace(array('.',','),'_', abs($mlat)) . '_'  . str_replace(array('.',','),'_', abs($mlon));
		$mapid_js = str_replace(array('.',','),'_', abs($mlat)) . '_'  . str_replace(array('.',','),'_', abs($mlon));
	}

	//info: to make maps easier accessible from outside the plugin
	$lmmjs_out .= 'var mapid_js = "' . $mapid_js . '";'.PHP_EOL;
	$lmmjs_out .= 'var mapname_js = "' . $mapname_js . '";'.PHP_EOL;
	$lmmjs_out .= 'MMP.maps["' . $mapid_js . '"] =  "' . $mapname_js . '";'.PHP_EOL;

	$lmmjs_out .= 'var '.$mapname_js.' = {};'.PHP_EOL;
	$lmmjs_out .= 'var markerID_' . $mapname_js . ' = {};'.PHP_EOL;
	//info: define attribution links as variables to allow dynamic change through layer control box
	if ( $lmm_options['misc_backlinks'] == 'show' ) {
		$attrib_prefix_affiliate = ($lmm_options['affiliate_id'] == NULL) ? 'go' : intval($lmm_options['affiliate_id']) . '.html';
		$attrib_prefix = '<a href=\"https://www.mapsmarker.com/' . $attrib_prefix_affiliate . '\" target=\"_blank\" title=\"' . esc_attr__('Maps Marker Pro - #1 mapping plugin for WordPress','lmm') . '\">MapsMarker.com</a> (<a href=\"http://www.leafletjs.com\" target=\"_blank\" title=\"' . esc_attr__('Leaflet Maps Marker is based on the javascript library Leaflet maintained by Vladimir Agafonkin and Cloudmade','lmm') . '\">Leaflet</a>/<a href=\"https://mapicons.mapsmarker.com\" target=\"_blank\" title=\"' . esc_attr__('Leaflet Maps Marker uses icons from the Maps Icons Collection maintained by Nicolas Mollet','lmm') . '\">icons</a>/<a href=\"http://www.visualead.com/go\" target=\"_blank\" rel=\"nofollow\" title=\"' . esc_attr__('Visual QR codes for fullscreen maps are created by Visualead.com','lmm') . '\">QR</a>)';
	} else {
		$attrib_prefix = '';
	}
	//info: add edit link
	if ( (current_user_can($lmm_options[ 'capabilities_edit_others' ])) || ((current_user_can( $lmm_options[ 'capabilities_edit' ]) && ( $current_user->user_login == $createdby))) ) {
		if (!empty($layer)) {
			$attrib_prefix = '<a href=\"' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $id . '\"><img style=\"display:inline;\" src=\"' . LEAFLET_PLUGIN_URL . '/inc/img/icon-map-edit.png\" width=\"16\" height=\"16\" alt=\"' . $text_edit_layer . ' ID ' . $id . '\" title=\"' . $text_edit_layer . ' ID ' . $id . '\"></a> ' . $attrib_prefix;
		} else if (!empty($marker)) {
			$attrib_prefix = '<a href=\"' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id=' . $id . '\"><img style=\"display:inline;\" src=\"' . LEAFLET_PLUGIN_URL . '/inc/img/icon-map-edit.png\" width=\"16\" height=\"16\" alt=\"' . $text_edit_marker . ' ID ' . $id . '\" title=\"' . $text_edit_marker . ' ID ' . $id . '\"></a> ' . $attrib_prefix;
		} else if (empty($layer) && empty($marker)) {
			$attrib_prefix = '<img style=\"display:inline;\" src=\"' . LEAFLET_PLUGIN_URL . '/inc/img/icon-map-edit.png\" width=\"16\" height=\"16\" alt=\"' . __('marker created directly by using shortcode - no edit link available','lmm') . '\" title=\"' . __('marker created directly by using shortcode - no edit link available','lmm') . '\"></a> ' . $attrib_prefix;
		}
	}
	$osm_editlink = ($lmm_options['misc_map_osm_editlink'] == 'show') ? '&nbsp;(<a href=\"http://www.openstreetmap.org/edit?editor=' . $lmm_options['misc_map_osm_editlink_editor'] . '&amp;lat=' . $lat . '&amp;lon=' . $lon . '&zoom=' . $zoom . '\" target=\"_blank\" title=\"' . esc_attr__('help OpenStreetMap.org to improve map details','lmm') . '\">' . $text_edit . '</a>)' : '';
	$attrib_osm_mapnik = $text_map.': &copy; <a href=\"http://www.openstreetmap.org/copyright\" target=\"_blank\">' . __('OpenStreetMap contributors','lmm') . '</a>' . $osm_editlink;
	$attrib_mapquest_osm = $text_map.': Tiles Courtesy of <a href=\"http://www.mapquest.com/\" target=\"_blank\">MapQuest</a> <img src=\"' . LEAFLET_PLUGIN_URL . 'inc/img/logo-mapquest.png\" style=\"display:inline;\" /> - &copy; <a href=\"http://www.openstreetmap.org/copyright\" target=\"_blank\">' . __('OpenStreetMap contributors','lmm') . '</a>' . $osm_editlink;
	$attrib_mapquest_aerial = $text_map.': <a href=\"http://www.mapquest.com/\" target=\"_blank\">MapQuest</a> <img src=\"' . LEAFLET_PLUGIN_URL . 'inc/img/logo-mapquest.png\" style=\"display:inline;\" />, Portions Courtesy NASA/JPL-Caltech and U.S. Depart. of Agriculture, Farm Service Agency';
	$attrib_ogdwien_basemap = $text_map.': ' . __("City of Vienna","lmm") . ' (<a href=\"http://data.wien.gv.at\" target=\"_blank\" style=\"\">data.wien.gv.at</a>)';
	$attrib_ogdwien_satellite = $text_map.': ' . __("City of Vienna","lmm") . ' (<a href=\"http://data.wien.gv.at\" target=\"_blank\">data.wien.gv.at</a>)';
	$attrib_custom_basemap = $text_map.': ' . addslashes(wp_kses($lmm_options[ 'custom_basemap_attribution' ], $allowedtags));
	$attrib_custom_basemap2 = $text_map.': ' . addslashes(wp_kses($lmm_options[ 'custom_basemap2_attribution' ], $allowedtags));
	$attrib_custom_basemap3 = $text_map.': ' . addslashes(wp_kses($lmm_options[ 'custom_basemap3_attribution' ], $allowedtags));
	//info: prepare global map settings: use shortcode parameters if available first or apply filters()
	$dragging_setting_prepare  	  = empty($dragging) ? apply_filters('mmp_setting_map_dragging', $lmm_options['misc_map_dragging']) : ( ((strtolower($dragging) == 'true') || (strtolower($dragging) == 'false') || (strtolower($dragging) == 'false-touch')	) ? strtolower($dragging) : $lmm_options['misc_map_dragging'] );
	$dragging_setting			  = ($dragging_setting_prepare == 'false-touch') ? '!L.Browser.mobile' : $dragging_setting_prepare;
	$touchzoom_setting 			  = empty($touchzoom) ? apply_filters('mmp_setting_map_touchzoom', $lmm_options['misc_map_touchzoom']) : ( ((strtolower($touchzoom) == 'true') || (strtolower($touchzoom) == 'false')) ? strtolower($touchzoom) : $lmm_options['misc_map_touchzoom'] );
	$scrollwheelzoom_setting 	  = empty($scrollwheelzoom) ? apply_filters('mmp_setting_map_scrollwheelzoom', $lmm_options['misc_map_scrollwheelzoom']) : ( ((strtolower($scrollwheelzoom) == 'true') || (strtolower($scrollwheelzoom) == 'true-fullscreen-only') || (strtolower($scrollwheelzoom) == 'false')) ? strtolower($scrollwheelzoom) : $lmm_options['misc_map_scrollwheelzoom'] );
	if ($scrollwheelzoom_setting == 'true-fullscreen-only') { $scrollwheelzoom_setting = 'false'; } //info: true for leaflet-fullscreen.php only
	$doubleclickzoom_setting 	  = empty($doubleclickzoom) ? apply_filters('mmp_setting_map_doubleclickzoom', $lmm_options['misc_map_doubleclickzoom']) : ( ((strtolower($doubleclickzoom) == 'true') || (strtolower($doubleclickzoom) == 'false')) ? strtolower($doubleclickzoom) : $lmm_options['misc_map_doubleclickzoom'] );
	$boxzoom_setting 			  = empty($boxzoom) ? apply_filters('mmp_setting_map_boxzoom', $lmm_options['map_interaction_options_boxzoom']) : ( ((strtolower($boxzoom) == 'true') || (strtolower($boxzoom) == 'false')) ? strtolower($boxzoom) : $lmm_options['map_interaction_options_boxzoom'] );
	$trackresize_setting 		  = empty($trackresize) ? apply_filters('mmp_setting_map_trackresize', $lmm_options['misc_map_trackresize']) : ( ((strtolower($trackresize) == 'true') || (strtolower($trackresize) == 'false')) ? strtolower($trackresize) : $lmm_options['misc_map_trackresize'] );
	$worldcopyjump_setting 		  = empty($worldcopyjump) ? apply_filters('mmp_setting_map_worldcopyjump', $lmm_options['map_interaction_options_worldcopyjump']) : ( ((strtolower($worldcopyjump) == 'true') || (strtolower($worldcopyjump) == 'false')) ? strtolower($worldcopyjump) : $lmm_options['misc_map_closepopuponclick'] );
	$closepopuponclick_setting 	  = empty($closepopuponclick) ? apply_filters('mmp_setting_map_closepopuponclick', $lmm_options['misc_map_closepopuponclick']) : ( ((strtolower($closepopuponclick) == 'true') || (strtolower($closepopuponclick) == 'false')) ? strtolower($closepopuponclick) : $lmm_options['misc_map_closepopuponclick'] );
	$keyboard_setting 			  = empty($keyboard) ? apply_filters('mmp_setting_map_keyboard', $lmm_options['map_keyboard_navigation_options_keyboard']) : ( ((strtolower($keyboard) == 'true') || (strtolower($keyboard) == 'false')) ? strtolower($keyboard) : $lmm_options['map_keyboard_navigation_options_keyboard'] );
	$keyboardpanoffset_setting 	  = empty($keyboardpanoffset) ? apply_filters('mmp_setting_map_keyboardpanoffset', $lmm_options['map_keyboard_navigation_options_keyboardpanoffset']) : $keyboardpanoffset;
	$keyboardzoomoffset_setting   = empty($keyboardzoomoffset) ? apply_filters('mmp_setting_map_keyboardzoomoffset', $lmm_options['map_keyboard_navigation_options_keyboardzoomoffset']) : $keyboardzoomoffset;
	$inertia_setting 			  = empty($inertia) ? apply_filters('mmp_setting_map_inertia', $lmm_options['map_panning_inertia_options_inertia']) : ( ((strtolower($inertia) == 'true') || (strtolower($inertia) == 'false')) ? strtolower($inertia) : $lmm_options['map_panning_inertia_options_inertia'] );
	$inertia_deceleration_setting = empty($inertia_deceleration) ? apply_filters('mmp_setting_map_inertia_deceleration', $lmm_options['map_panning_inertia_options_inertiadeceleration']) : $inertia_deceleration;
	$inertiamaxspeed_setting 	  = empty($inertiamaxspeed) ? apply_filters('mmp_setting_map_inertiamaxspeed', $lmm_options['map_panning_inertia_options_inertiamaxspeed']) : $inertiamaxspeed;
	$zoomcontrol_setting 		  = empty($zoomcontrol) ? apply_filters('mmp_setting_map_zoomcontrol', $lmm_options['misc_map_zoomcontrol']) : ( ((strtolower($zoomcontrol) == 'true') || (strtolower($zoomcontrol) == 'false')) ? strtolower($zoomcontrol) : $lmm_options['misc_map_zoomcontrol'] );
	$crs_setting 				  = empty($crs) ? apply_filters('mmp_setting_map_crs', $lmm_options['misc_projections']) : $crs;
	$fullscreencontrol_setting	  = empty($fullscreencontrol) ? apply_filters('mmp_setting_map_fullscreencontrol', $lmm_options['map_fullscreen_button']) : ( ((strtolower($fullscreencontrol) == 'true') || (strtolower($fullscreencontrol) == 'false')) ? strtolower($fullscreencontrol) : $lmm_options['map_fullscreen_button'] );
	$maxzoom_setting			  = empty($maxzoom) ? apply_filters('mmp_setting_map_maxzoom', $lmm_options['global_maxzoom_level']) : $maxzoom;
	$tap_setting 				  = empty($tap) ? apply_filters('mmp_setting_map_tab', $lmm_options['map_interaction_options_tap']) : ( ((strtolower($tap) == 'true') || (strtolower($tap) == 'false')) ? strtolower($tap) : $lmm_options['map_interaction_options_tap'] );
	$taptolerance_setting 		  = empty($taptolerance) ? apply_filters('mmp_setting_map_taptolerance', $lmm_options['map_interaction_options_taptolerance']) : $taptolerance;
	$bounceatzoomlimits_setting	  = empty($bounceatzoomlimits) ? apply_filters('mmp_setting_map_bounceatzoomlimits', $lmm_options['map_interaction_options_bounceatzoomlimits']) : ( ((strtolower($bounceatzoomlimits) == 'true') || (strtolower($bounceatzoomlimits) == 'false')) ? strtolower($bounceatzoomlimits) : $lmm_options['map_interaction_options_bounceatzoomlimits'] );

	$lmmjs_out .= $mapname_js.' = new L.Map("'.$mapname.'", { dragging: ' . $dragging_setting . ', touchZoom: ' . $touchzoom_setting . ', scrollWheelZoom: ' . $scrollwheelzoom_setting . ', doubleClickZoom: ' . $doubleclickzoom_setting . ', boxzoom: ' . $boxzoom_setting . ', trackResize: ' . $trackresize_setting . ', worldCopyJump: ' . $worldcopyjump_setting . ', closePopupOnClick: ' . $closepopuponclick_setting . ', keyboard: ' . $keyboard_setting . ', keyboardPanOffset: ' . intval($keyboardpanoffset_setting) . ', keyboardZoomOffset: ' . intval($keyboardzoomoffset_setting) . ', inertia: ' . $inertia_setting . ', inertiaDeceleration: ' . intval($inertia_deceleration_setting) . ', inertiaMaxSpeed: ' . intval($inertiamaxspeed_setting) . ', zoomControl: ' . $zoomcontrol_setting . ', crs: ' . esc_js($crs_setting) . ', fullscreenControl: ' . $fullscreencontrol_setting . ', tap: ' . $tap_setting . ', tapTolerance: ' . intval($taptolerance_setting) . ', bounceAtZoomLimits: ' . $bounceatzoomlimits_setting . ' });'.PHP_EOL;

	$lmmjs_out .= $mapname_js.'.attributionControl.setPrefix("' . $attrib_prefix . '");'.PHP_EOL;
	//info: define basemaps
	if (substr($locale, 0, 2) != 'zh') { //info: conditional ssl loading (performance issues in China)
		$protocol_handler = 'https';
		$mapquest_ssl = '-s';
		$mapbox_ssl = '&secure=1';
	} else {
		$protocol_handler = 'http';
		$mapquest_ssl = '';
		$mapbox_ssl = '';
	}
	$lmmjs_out .= 'var osm_mapnik = new L.TileLayer("' . $protocol_handler . '://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 18, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_osm_mapnik . '", detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	$lmmjs_out .= 'var mapquest_osm = new L.TileLayer("' . $protocol_handler . '://{s}' . $mapquest_ssl . '.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 17, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_mapquest_osm . '", subdomains: ["otile1","otile2","otile3","otile4"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	$lmmjs_out .= 'var mapquest_aerial = new L.TileLayer("' . $protocol_handler . '://{s}' . $mapquest_ssl . '.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.png", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 11, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_mapquest_aerial . '", subdomains: ["otile1","otile2","otile3","otile4"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	if ($lmm_options['google_maps_api_status'] == 'enabled') {
		if ($lmm_options['google_maps_api_deferred_loading'] == 'enabled') {
			//info: Google language localization (JSON API)
			if ($lmm_options['google_maps_language_localization'] == 'browser_setting') {
				$google_language = '';
			} else if ($lmm_options['google_maps_language_localization'] == 'wordpress_setting') {
				if ( $locale != NULL ) { $google_language = "&language=" . substr($locale, 0, 2); } else { $google_language =  '&language=en'; }
			} else {
				$google_language = "&language=" . $lmm_options['google_maps_language_localization'];
			}
			//info: Google API key
			if ( isset($lmm_options['google_maps_api_key']) && ($lmm_options['google_maps_api_key'] != NULL) ) { $google_maps_api_key = '&key=' . $lmm_options['google_maps_api_key']; } else { $google_maps_api_key = ''; }
			$google_js_url = $protocol_handler . '://maps.googleapis.com/maps/api/js?callback=L.Google.asyncInitialize' . $google_maps_api_key . $google_language;
			$lmmjs_out .= 'var deferred_google_layers = {
	roadmap: { name: "roadmap", js: ["' . $google_js_url . '"], init: function() {return new L.Google("ROADMAP", {detectRetina: ' . $lmm_options['map_retina_detection'] . '}); }},
	satellite: { name: "satellite", js: ["' . $google_js_url . '"], init: function() {return new L.Google("SATELLITE", {detectRetina: ' . $lmm_options['map_retina_detection'] . '}); }},
	hybrid: { name: "hybrid", js: ["' . $google_js_url . '"], init: function() {return new L.Google("HYBRID", {detectRetina: ' . $lmm_options['map_retina_detection'] . '}); }},
	terrain: { name: "terrain", js: ["' . $google_js_url . '"], init: function() {return new L.Google("TERRAIN", {detectRetina: ' . $lmm_options['map_retina_detection'] . '}); }}
};
var googleLayer_roadmap = new L.DeferredLayer(deferred_google_layers.roadmap);
var googleLayer_satellite = new L.DeferredLayer(deferred_google_layers.satellite);
var googleLayer_hybrid = new L.DeferredLayer(deferred_google_layers.hybrid);
var googleLayer_terrain = new L.DeferredLayer(deferred_google_layers.terrain);'.PHP_EOL;
		} else { //info: undeferred loading
			$lmmjs_out .= 'var googleLayer_roadmap = new L.Google("ROADMAP", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
			$lmmjs_out .= 'var googleLayer_satellite = new L.Google("SATELLITE", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
			$lmmjs_out .= 'var googleLayer_hybrid = new L.Google("HYBRID", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
			$lmmjs_out .= 'var googleLayer_terrain = new L.Google("TERRAIN", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		}
	}
	if ( isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL ) ) {
		$lmmjs_out .= 'var bingaerial = new L.BingLayer("' . htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]) . '", {type: "Aerial", maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 19, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		$lmmjs_out .= 'var bingaerialwithlabels = new L.BingLayer("' . htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]) . '", {type: "AerialWithLabels", maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 19, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		$lmmjs_out .= 'var bingroad = new L.BingLayer("' . htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]) . '", {type: "Road", maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 19, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	};
	$lmmjs_out .= 'var ogdwien_basemap = new L.TileLayer("' . $protocol_handler . '://{s}.wien.gv.at/wmts/fmzk/pastell/google3857/{z}/{y}/{x}.jpeg", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 19, minZoom: 11, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_ogdwien_basemap . '", subdomains: ["maps","maps1", "maps2", "maps3"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	$lmmjs_out .= 'var ogdwien_satellite = new L.TileLayer("' . $protocol_handler . '://{s}.wien.gv.at/wmts/lb/farbe/google3857/{z}/{y}/{x}.jpeg", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 19, minZoom: 11, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_ogdwien_satellite . '", subdomains: ["maps","maps1", "maps2", "maps3"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	//info: MapBox basemaps
	if ($lmm_options[ 'mapbox_access_token' ] != NULL) {
		$lmmjs_out .= 'var mapbox = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v4/' . htmlspecialchars($lmm_options[ 'mapbox_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox_map' ]) . '/{z}/{x}/{y}.png?access_token=' . esc_js($lmm_options[ 'mapbox_access_token' ]) . $mapbox_ssl . '", {minZoom: ' . intval($lmm_options[ 'mapbox_minzoom' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	} else {  //info: v3 fallback for default maps
		$lmmjs_out .= 'var mapbox = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v3/' . htmlspecialchars($lmm_options[ 'mapbox_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox_map' ]) . '/{z}/{x}/{y}.png", {minZoom: ' . intval($lmm_options[ 'mapbox_minzoom' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($lmm_options[ 'mapbox2_access_token' ] != NULL) {
		$lmmjs_out .= 'var mapbox2 = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v4/' . htmlspecialchars($lmm_options[ 'mapbox2_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox2_map' ]) . '/{z}/{x}/{y}.png?access_token=' . esc_js($lmm_options[ 'mapbox2_access_token' ]) . $mapbox_ssl . '", {minZoom: ' . intval($lmm_options[ 'mapbox2_minzoom' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox2_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox2_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	} else {
		$lmmjs_out .= 'var mapbox2 = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v3/' . htmlspecialchars($lmm_options[ 'mapbox2_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox2_map' ]) . '/{z}/{x}/{y}.png", {minZoom: ' . intval($lmm_options[ 'mapbox2_minzoom' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox2_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox2_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($lmm_options[ 'mapbox3_access_token' ] != NULL) {
		$lmmjs_out .= 'var mapbox3 = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v4/' . htmlspecialchars($lmm_options[ 'mapbox3_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox3_map' ]) . '/{z}/{x}/{y}.png?access_token=' . esc_js($lmm_options[ 'mapbox3_access_token' ]) . $mapbox_ssl . '", {minZoom: ' . intval($lmm_options[ 'mapbox3_minzoom' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox3_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox3_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	} else {
		$lmmjs_out .= 'var mapbox3 = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v3/' . htmlspecialchars($lmm_options[ 'mapbox3_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox3_map' ]) . '/{z}/{x}/{y}.png", {minZoom: ' . intval($lmm_options[ 'mapbox3_minzoom' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox3_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox3_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	//info: check if subdomains are set for custom basemaps
	$custom_basemap_subdomains = ((isset($lmm_options[ 'custom_basemap_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'custom_basemap_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'custom_basemap_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$custom_basemap2_subdomains = ((isset($lmm_options[ 'custom_basemap2_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'custom_basemap2_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'custom_basemap2_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$custom_basemap3_subdomains = ((isset($lmm_options[ 'custom_basemap3_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'custom_basemap3_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'custom_basemap3_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	//info: define custom basemaps
	$error_tile_url_custom_basemap = ($lmm_options['custom_basemap_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
	$error_tile_url_custom_basemap2 = ($lmm_options['custom_basemap2_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
	$error_tile_url_custom_basemap3 = ($lmm_options['custom_basemap3_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
	$lmmjs_out .= 'var custom_basemap = new L.TileLayer("' . str_replace('"','&quot;',$lmm_options[ 'custom_basemap_tileurl' ]) . '", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'custom_basemap_maxzoom' ]) . ', minZoom: ' . intval($lmm_options[ 'custom_basemap_minzoom' ]) . ', tms: ' . $lmm_options[ 'custom_basemap_tms' ] . ', ' . $error_tile_url_custom_basemap . 'attribution: "' . $attrib_custom_basemap . '"' . $custom_basemap_subdomains . ', continuousWorld: ' . $lmm_options[ 'custom_basemap_continuousworld_enabled' ] . ', noWrap: ' . $lmm_options[ 'custom_basemap_nowrap_enabled' ] . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	$lmmjs_out .= 'var custom_basemap2 = new L.TileLayer("' . str_replace('"','&quot;',$lmm_options[ 'custom_basemap2_tileurl' ]) . '", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'custom_basemap2_maxzoom' ]) . ', minZoom: ' . intval($lmm_options[ 'custom_basemap2_minzoom' ]) . ', tms: ' . $lmm_options[ 'custom_basemap2_tms' ] . ', ' . $error_tile_url_custom_basemap2 . 'attribution: "' . $attrib_custom_basemap2 . '"' . $custom_basemap2_subdomains . ', continuousWorld: ' . $lmm_options[ 'custom_basemap2_continuousworld_enabled' ] . ', noWrap: ' . $lmm_options[ 'custom_basemap2_nowrap_enabled' ] . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	$lmmjs_out .= 'var custom_basemap3 = new L.TileLayer("' . str_replace('"','&quot;',$lmm_options[ 'custom_basemap3_tileurl' ]) . '", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'custom_basemap3_maxzoom' ]) . ', minZoom: ' . intval($lmm_options[ 'custom_basemap3_minzoom' ]) . ', tms: ' . $lmm_options[ 'custom_basemap3_tms' ] . ', ' . $error_tile_url_custom_basemap3 . 'attribution: "' . $attrib_custom_basemap3 . '"' . $custom_basemap3_subdomains . ', continuousWorld: ' . $lmm_options[ 'custom_basemap3_continuousworld_enabled' ] . ', noWrap: ' . $lmm_options[ 'custom_basemap3_nowrap_enabled' ] . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	$lmmjs_out .= 'var empty_basemap = new L.TileLayer("");'.PHP_EOL;

	//info: check if subdomains are set for custom overlays
	$overlays_custom_subdomains = ((isset($lmm_options[ 'overlays_custom_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'overlays_custom_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'overlays_custom_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$overlays_custom2_subdomains = ((isset($lmm_options[ 'overlays_custom2_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'overlays_custom2_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'overlays_custom2_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$overlays_custom3_subdomains = ((isset($lmm_options[ 'overlays_custom3_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'overlays_custom3_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'overlays_custom3_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$overlays_custom4_subdomains = ((isset($lmm_options[ 'overlays_custom4_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'overlays_custom4_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'overlays_custom4_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$error_tile_url_overlays_custom = ($lmm_options['overlays_custom_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
	$error_tile_url_overlays_custom2 = ($lmm_options['overlays_custom2_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
	$error_tile_url_overlays_custom3 = ($lmm_options['overlays_custom3_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
	$error_tile_url_overlays_custom4 = ($lmm_options['overlays_custom4_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';

	//info: define overlays
	$lmmjs_out .= 'var overlays_custom = new L.TileLayer("' . str_replace('"','&quot;',$lmm_options[ 'overlays_custom_tileurl' ]) . '", {tms: ' . $lmm_options[ 'overlays_custom_tms' ] . ', ' . $error_tile_url_overlays_custom . 'attribution: "' . addslashes(wp_kses($lmm_options[ 'overlays_custom_attribution' ], $allowedtags)) . '", opacity: ' . floatval($lmm_options[ 'overlays_custom_opacity' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'overlays_custom_maxzoom' ]) . ', minZoom: ' . intval($lmm_options[ 'overlays_custom_minzoom' ]) . $overlays_custom_subdomains . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	$lmmjs_out .= 'var overlays_custom2 = new L.TileLayer("' . str_replace('"','&quot;',$lmm_options[ 'overlays_custom2_tileurl' ]) . '", {tms: ' . $lmm_options[ 'overlays_custom2_tms' ] . ', ' . $error_tile_url_overlays_custom2 . 'attribution: "' . addslashes(wp_kses($lmm_options[ 'overlays_custom2_attribution' ], $allowedtags)) . '", opacity: ' . floatval($lmm_options[ 'overlays_custom2_opacity' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'overlays_custom2_maxzoom' ]) . ', minZoom: ' . intval($lmm_options[ 'overlays_custom2_minzoom' ]) . $overlays_custom2_subdomains . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	$lmmjs_out .= 'var overlays_custom3 = new L.TileLayer("' . str_replace('"','&quot;',$lmm_options[ 'overlays_custom3_tileurl' ]) . '", {tms: ' . $lmm_options[ 'overlays_custom3_tms' ] . ', ' . $error_tile_url_overlays_custom3 . 'attribution: "' . addslashes(wp_kses($lmm_options[ 'overlays_custom3_attribution' ], $allowedtags)) . '", opacity: ' . floatval($lmm_options[ 'overlays_custom3_opacity' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'overlays_custom3_maxzoom' ]) . ', minZoom: ' . intval($lmm_options[ 'overlays_custom3_minzoom' ]) . $overlays_custom3_subdomains . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	$lmmjs_out .= 'var overlays_custom4 = new L.TileLayer("' . str_replace('"','&quot;',$lmm_options[ 'overlays_custom4_tileurl' ]) . '", {tms: ' . $lmm_options[ 'overlays_custom4_tms' ] . ', ' . $error_tile_url_overlays_custom4 . 'attribution: "' . addslashes(wp_kses($lmm_options[ 'overlays_custom4_attribution' ], $allowedtags)) . '", opacity: ' . floatval($lmm_options[ 'overlays_custom4_opacity' ]) . ', maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: ' . intval($lmm_options[ 'overlays_custom4_maxzoom' ]) . ', minZoom: ' . intval($lmm_options[ 'overlays_custom4_minzoom' ]) . $overlays_custom_subdomains . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;

	//info: check if subdomains are set for wms layers
	$wms_subdomains = ((isset($lmm_options[ 'wms_wms_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$wms2_subdomains = ((isset($lmm_options[ 'wms_wms2_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms2_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms2_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$wms3_subdomains = ((isset($lmm_options[ 'wms_wms3_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms3_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms3_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$wms4_subdomains = ((isset($lmm_options[ 'wms_wms4_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms4_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms4_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$wms5_subdomains = ((isset($lmm_options[ 'wms_wms5_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms5_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms5_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$wms6_subdomains = ((isset($lmm_options[ 'wms_wms6_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms6_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms6_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$wms7_subdomains = ((isset($lmm_options[ 'wms_wms7_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms7_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms7_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$wms8_subdomains = ((isset($lmm_options[ 'wms_wms8_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms8_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms8_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$wms9_subdomains = ((isset($lmm_options[ 'wms_wms9_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms9_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms9_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	$wms10_subdomains = ((isset($lmm_options[ 'wms_wms10_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms10_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms10_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
	//info: define wms legends
	$wms_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	$wms2_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms2_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms2_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms2_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms2_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	$wms3_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms3_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms3_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms3_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms3_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	$wms4_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms4_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms4_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms4_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms4_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	$wms5_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms5_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms5_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms5_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms5_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	$wms6_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms6_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms6_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms6_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms6_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	$wms7_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms7_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms7_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms7_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms7_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	$wms8_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms8_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms8_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms8_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms8_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	$wms9_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms9_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms9_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms9_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms9_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	$wms10_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms10_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms10_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms10_legend' ] != NULL )) ? ' (<a href=\"' . wp_kses($lmm_options[ 'wms_wms10_legend' ], $allowedtags) . '\" target=\"_blank\">' . $text_legend . '</a>)' : '') .'';
	//info: define wms layers
	if ($wms == 1) {
	$lmmjs_out .= 'var wms = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms_baseurl' ]) . '", {wmsid: "wms", layers: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms_layers' ])) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms_format' ])) . '", attribution: "' . $wms_attribution . '", transparent: "' . $lmm_options[ 'wms_wms_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms_version' ])) . '"' . $wms_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($wms2 == 1) {
	$lmmjs_out .= 'var wms2 = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms2_baseurl' ]) . '", {wmsid: "wms2", layers: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms2_layers' ])) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms2_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms2_format' ])) . '", attribution: "' . $wms2_attribution . '", transparent: "' . $lmm_options[ 'wms_wms2_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms2_version' ])) . '"' . $wms2_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($wms3 == 1) {
	$lmmjs_out .= 'var wms3 = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms3_baseurl' ]) . '", {wmsid: "wms3", layers: "' . htmlspecialchars(htmlspecialchars(addslashes($lmm_options[ 'wms_wms3_layers' ]))) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms3_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms3_format' ])) . '", attribution: "' . $wms3_attribution . '", transparent: "' . $lmm_options[ 'wms_wms3_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms3_version' ])) . '"' . $wms3_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($wms4 == 1) {
	$lmmjs_out .= 'var wms4 = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms4_baseurl' ]) . '", {wmsid: "wms4", layers: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms4_layers' ])) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms4_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms4_format' ])) . '", attribution: "' . $wms4_attribution . '", transparent: "' . $lmm_options[ 'wms_wms4_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms4_version' ])) . '"' . $wms4_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($wms5 == 1) {
	$lmmjs_out .= 'var wms5 = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms5_baseurl' ]) . '", {wmsid: "wms5", layers: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms5_layers' ])) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms5_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms5_format' ])) . '", attribution: "' . $wms5_attribution . '", transparent: "' . $lmm_options[ 'wms_wms5_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms5_version' ])) . '"' . $wms5_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($wms6 == 1) {
	$lmmjs_out .= 'var wms6 = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms6_baseurl' ]) . '", {wmsid: "wms6", layers: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms6_layers' ])) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms6_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms6_format' ])) . '", attribution: "' . $wms6_attribution . '", transparent: "' . $lmm_options[ 'wms_wms6_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms6_version' ])) . '"' . $wms6_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($wms7 == 1) {
	$lmmjs_out .= 'var wms7 = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms7_baseurl' ]) . '", {wmsid: "wms7", layers: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms7_layers' ])) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms7_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms7_format' ])) . '", attribution: "' . $wms7_attribution . '", transparent: "' . $lmm_options[ 'wms_wms7_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms7_version' ])) . '"' . $wms7_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($wms8 == 1) {
	$lmmjs_out .= 'var wms8 = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms8_baseurl' ]) . '", {wmsid: "wms8", layers: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms8_layers' ])) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms8_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms8_format' ])) . '", attribution: "' . $wms8_attribution . '", transparent: "' . $lmm_options[ 'wms_wms8_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms8_version' ])) . '"' . $wms8_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($wms9 == 1) {
	$lmmjs_out .= 'var wms9 = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms9_baseurl' ]) . '", {wmsid: "wms9", layers: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms9_layers' ])) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms9_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms9_format' ])) . '", attribution: "' . $wms9_attribution . '", transparent: "' . $lmm_options[ 'wms_wms9_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms9_version' ])) . '"' . $wms9_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	if ($wms10 == 1) {
	$lmmjs_out .= 'var wms10 = new L.TileLayer.WMS("' . htmlspecialchars($lmm_options[ 'wms_wms10_baseurl' ]) . '", {wmsid: "wms10", layers: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms10_layers' ])) . '", styles: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms10_styles' ])) . '", format: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms10_format' ])) . '", attribution: "' . $wms10_attribution . '", transparent: "' . $lmm_options[ 'wms_wms10_transparent' ] . '", errorTileUrl: "' . LEAFLET_PLUGIN_URL  . 'inc/img/error-tile-image.png", version: "' . htmlspecialchars(addslashes($lmm_options[ 'wms_wms10_version' ])) . '"' . $wms10_subdomains  . ', detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
	}
	//info: controlbox - basemaps
	$lmmjs_out .= 'var layersControl_'.$mapname_js.' = new L.Control.Layers('.PHP_EOL;
	$lmmjs_out .= '{';
	$basemaps_available = '';
	if ( (isset($lmm_options[ 'controlbox_osm_mapnik' ]) == TRUE ) && ($lmm_options[ 'controlbox_osm_mapnik' ] == 1) )
		$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_osm_mapnik' ])) . "': osm_mapnik,";
	if ( (isset($lmm_options[ 'controlbox_mapquest_osm' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapquest_osm' ] == 1) )
		$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_mapquest_osm' ])) . "': mapquest_osm,";
	if ( (isset($lmm_options[ 'controlbox_mapquest_aerial' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapquest_aerial' ] == 1) )
		$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_mapquest_aerial' ])) . "': mapquest_aerial,";
	if ( (isset($lmm_options[ 'controlbox_googleLayer_roadmap' ]) == TRUE ) && ($lmm_options[ 'controlbox_googleLayer_roadmap' ] == 1) && ($lmm_options['google_maps_api_status'] == 'enabled') )
		$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_googleLayer_roadmap' ])) . "': googleLayer_roadmap,";
	if ( (isset($lmm_options[ 'controlbox_googleLayer_satellite' ]) == TRUE ) && ($lmm_options[ 'controlbox_googleLayer_satellite' ] == 1) && ($lmm_options['google_maps_api_status'] == 'enabled') )
		$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_googleLayer_satellite' ])) . "': googleLayer_satellite,";
	if ( (isset($lmm_options[ 'controlbox_googleLayer_hybrid' ]) == TRUE ) && ($lmm_options[ 'controlbox_googleLayer_hybrid' ] == 1) && ($lmm_options['google_maps_api_status'] == 'enabled') )
		$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_googleLayer_hybrid' ])) . "': googleLayer_hybrid,";
	if ( (isset($lmm_options[ 'controlbox_googleLayer_terrain' ]) == TRUE ) && ($lmm_options[ 'controlbox_googleLayer_terrain' ] == 1) && ($lmm_options['google_maps_api_status'] == 'enabled') )
		$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_googleLayer_terrain' ])) . "': googleLayer_terrain,";
	if ( isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL ) ) {
		if ( (isset($lmm_options[ 'controlbox_bingaerial' ]) == TRUE ) && ($lmm_options[ 'controlbox_bingaerial' ] == 1) )
			$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_bingaerial' ])) . "': bingaerial,";
		if ( (isset($lmm_options[ 'controlbox_bingaerialwithlabels' ]) == TRUE ) && ($lmm_options[ 'controlbox_bingaerialwithlabels' ] == 1) )
			$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_bingaerialwithlabels' ])) . "': bingaerialwithlabels,";
		if ( (isset($lmm_options[ 'controlbox_bingroad' ]) == TRUE ) && ($lmm_options[ 'controlbox_bingroad' ] == 1) )
			$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_bingroad' ])) . "': bingroad,";
	};
	if ( (((isset($lmm_options[ 'controlbox_ogdwien_basemap' ]) == TRUE ) && ($lmm_options[ 'controlbox_ogdwien_basemap' ] == 1)) && ((($lat <= '48.326583')  && ($lat >= '48.114308')) && (($lon <= '16.55056')  && ($lon >= '16.187325')) )) || ($basemap == 'ogdwien_basemap') )
		$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_ogdwien_basemap' ])) . "': ogdwien_basemap,";
	if ( (((isset($lmm_options[ 'controlbox_ogdwien_satellite' ]) == TRUE ) && ($lmm_options[ 'controlbox_ogdwien_satellite' ] == 1)) && ((($lat <= '48.326583')  && ($lat >= '48.114308')) && (($lon <= '16.55056')  && ($lon >= '16.187325')) )) || ($basemap == 'ogdwien_satellite') )
		$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_ogdwien_satellite' ])) . "': ogdwien_satellite,";
	if ( (isset($lmm_options[ 'controlbox_mapbox' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapbox' ] == 1) )
		$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'mapbox_name' ]))."': mapbox,";
	if ( (isset($lmm_options[ 'controlbox_mapbox2' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapbox2' ] == 1) )
		$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'mapbox2_name' ]))."': mapbox2,";
	if ( (isset($lmm_options[ 'controlbox_mapbox3' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapbox3' ] == 1) )
		$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'mapbox3_name' ]))."': mapbox3,";
	if ( (isset($lmm_options[ 'controlbox_custom_basemap' ]) == TRUE ) && ($lmm_options[ 'controlbox_custom_basemap' ] == 1) )
		$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'custom_basemap_name' ]))."': custom_basemap,";
	if ( (isset($lmm_options[ 'controlbox_custom_basemap2' ]) == TRUE ) && ($lmm_options[ 'controlbox_custom_basemap2' ] == 1) )
		$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'custom_basemap2_name' ]))."': custom_basemap2,";
	if ( (isset($lmm_options[ 'controlbox_custom_basemap3' ]) == TRUE ) && ($lmm_options[ 'controlbox_custom_basemap3' ] == 1) )
		$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'custom_basemap3_name' ]))."': custom_basemap3,";
	if ( (isset($lmm_options[ 'controlbox_empty_basemap' ]) == TRUE ) && ($lmm_options[ 'controlbox_empty_basemap' ] == 1) )
		$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'empty_basemap_name' ]))."': empty_basemap,";
	//info: needed for IE7 compatibility
	$lmmjs_out .= substr($basemaps_available, 0, -1);
	$lmmjs_out .= '},'.PHP_EOL;

	//info: controlbox - add available overlays
	$lmmjs_out .= '{';
	$overlays_custom_available = '';
	if ( ((isset($lmm_options[ 'overlays_custom' ] ) == TRUE ) && ( $lmm_options[ 'overlays_custom' ] == 1 )) || ($overlays_custom == 1) )
		$overlays_custom_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'overlays_custom_name' ]))."': overlays_custom,";
	if ( ((isset($lmm_options[ 'overlays_custom2' ] ) == TRUE ) && ( $lmm_options[ 'overlays_custom2' ] == 1 )) || ($overlays_custom2 == 1) )
		$overlays_custom_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'overlays_custom2_name' ]))."': overlays_custom2,";
	if ( ((isset($lmm_options[ 'overlays_custom3' ] ) == TRUE ) && ( $lmm_options[ 'overlays_custom3' ] == 1 )) || ($overlays_custom3 == 1) )
		$overlays_custom_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'overlays_custom3_name' ]))."': overlays_custom3,";
	if ( ((isset($lmm_options[ 'overlays_custom4' ] ) == TRUE ) && ( $lmm_options[ 'overlays_custom4' ] == 1 )) || ($overlays_custom4 == 1) )
		$overlays_custom_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'overlays_custom4_name' ]))."': overlays_custom4,";
	//info: needed for IE7 compatibility
	$lmmjs_out .= substr($overlays_custom_available, 0, -1);
	$lmmjs_out .= '},'.PHP_EOL;

	//info: controlbox - hidden / collapsed / expanded status
	if ( (isset($controlbox) == TRUE ) && ( $controlbox == 0 ) )
		$lmmjs_out .= '{ } );'.PHP_EOL;
	if ( (isset($controlbox) == TRUE ) && ( $controlbox == 1 ) )
		$lmmjs_out .= '{ collapsed: true } );'.PHP_EOL;
	if ( (isset($controlbox) == TRUE ) && ( $controlbox == 2 ) )
		$lmmjs_out .= '{ collapsed: false } );'.PHP_EOL;
	$lmmjs_out .= $mapname_js.'.setView(new L.LatLng('.$lat.', '.$lon.'), '.$zoom.');'.PHP_EOL;
	$lmmjs_out .= $mapname_js.'.addLayer(' . $basemap . ')';
	//info: controlbox - check active overlays on marker/layer level
	if ( (isset($overlays_custom) == TRUE) && ($overlays_custom == 1) )
		$lmmjs_out .= ".addLayer(overlays_custom)";
	if ( (isset($overlays_custom2) == TRUE) && ($overlays_custom2 == 1) )
		$lmmjs_out .= ".addLayer(overlays_custom2)";
	if ( (isset($overlays_custom3) == TRUE) && ($overlays_custom3 == 1) )
		$lmmjs_out .= ".addLayer(overlays_custom3)";
	if ( (isset($overlays_custom4) == TRUE) && ($overlays_custom4 == 1) )
		$lmmjs_out .= ".addLayer(overlays_custom4)";
	//info: controlbox - add active overlays on marker level
	if ( $wms == 1 )
		$lmmjs_out .= ".addLayer(wms)";
	if ( $wms2 == 1 )
		$lmmjs_out .= ".addLayer(wms2)";
	if ( $wms3 == 1 )
		$lmmjs_out .= ".addLayer(wms3)";
	if ( $wms4 == 1 )
		$lmmjs_out .= ".addLayer(wms4)";
	if ( $wms5 == 1 )
		$lmmjs_out .= ".addLayer(wms5)";
	if ( $wms6 == 1 )
		$lmmjs_out .= ".addLayer(wms6)";
	if ( $wms7 == 1 )
		$lmmjs_out .= ".addLayer(wms7)";
	if ( $wms8 == 1 )
		$lmmjs_out .= ".addLayer(wms8)";
	if ( $wms9 == 1 )
		$lmmjs_out .= ".addLayer(wms9)";
	if ( $wms10 == 1 )
		$lmmjs_out .= ".addLayer(wms10)";
	$lmmjs_out .= ( (isset($controlbox) == TRUE) && ($controlbox != 0) ) ? ".addControl(layersControl_".$mapname_js.");" : ";".PHP_EOL;

	//info: add minimap
	if ($lmm_options['minimap_status'] != 'hidden') {
		$lmmjs_out .= PHP_EOL.'var osm_mapnik_minimap = new L.TileLayer("' . $protocol_handler . '://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 18, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_osm_mapnik . '", detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		$lmmjs_out .= 'var mapquest_osm_minimap = new L.TileLayer("' . $protocol_handler . '://{s}' . $mapquest_ssl . '.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 17, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_mapquest_osm . '", subdomains: ["otile1","otile2","otile3","otile4"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		$lmmjs_out .= 'var mapquest_aerial_minimap = new L.TileLayer("' . $protocol_handler . '://{s}' . $mapquest_ssl . '.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.png", {maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 11, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_mapquest_aerial . '", subdomains: ["otile1","otile2","otile3","otile4"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		if ($lmm_options['google_maps_api_status'] == 'enabled') {
			if ($lmm_options['google_maps_api_deferred_loading'] == 'enabled') {
				$lmmjs_out .= 'var deferred_google_layers_minimap = {
	roadmap: { name: "roadmap minimap", js: ["' . $google_js_url . '"], init: function() {return new L.Google("ROADMAP", {detectRetina: ' . $lmm_options['map_retina_detection'] . '}); }},
	satellite: { name: "satellite minimap", js: ["' . $google_js_url . '"], init: function() {return new L.Google("SATELLITE", {detectRetina: ' . $lmm_options['map_retina_detection'] . '}); }},
	hybrid: { name: "hybrid minimap", js: ["' . $google_js_url . '"], init: function() {return new L.Google("HYBRID", {detectRetina: ' . $lmm_options['map_retina_detection'] . '}); }},
	terrain: { name: "terrain minimap", js: ["' . $google_js_url . '"], init: function() {return new L.Google("TERRAIN", {detectRetina: ' . $lmm_options['map_retina_detection'] . '}); }}
};'.PHP_EOL;
				$lmmjs_out .= 'var googleLayer_roadmap_minimap = new L.DeferredLayer(deferred_google_layers_minimap.roadmap);'.PHP_EOL;
				$lmmjs_out .= 'var googleLayer_satellite_minimap = new L.DeferredLayer(deferred_google_layers_minimap.satellite);'.PHP_EOL;
				$lmmjs_out .= 'var googleLayer_hybrid_minimap = new L.DeferredLayer(deferred_google_layers_minimap.hybrid);'.PHP_EOL;
				$lmmjs_out .= 'var googleLayer_terrain_minimap = new L.DeferredLayer(deferred_google_layers_minimap.terrain);'.PHP_EOL;
			} else { //info: undeferred loading
				$lmmjs_out .= 'var googleLayer_roadmap_minimap = new L.Google("ROADMAP", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
				$lmmjs_out .= 'var googleLayer_satellite_minimap = new L.Google("SATELLITE", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
				$lmmjs_out .= 'var googleLayer_hybrid_minimap = new L.Google("HYBRID", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
				$lmmjs_out .= 'var googleLayer_terrain_minimap = new L.Google("TERRAIN", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
			}
		}
		//info: bing minimaps
		if ( isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL ) ) {
			$lmmjs_out .= 'var bingaerial_minimap = new L.BingLayer("' . htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]) . '", {type: "Aerial", maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 19, minZoom: 1});'.PHP_EOL;
			$lmmjs_out .= 'var bingaerialwithlabels_minimap = new L.BingLayer("' . htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]) . '", {type: "AerialWithLabels", maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 19, minZoom: 1});'.PHP_EOL;
			$lmmjs_out .= 'var bingroad_minimap = new L.BingLayer("' . htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]) . '", {type: "Road", maxZoom: ' . $maxzoom_setting . ', maxNativeZoom: 19, minZoom: 1});'.PHP_EOL;
		};
		if ($lmm_options['minimap_zoomLevelFixed'] != NULL) { $zoomlevelfixed =  'zoomLevelFixed: ' . intval($lmm_options['minimap_zoomLevelFixed']) . ','; } else { $zoomlevelfixed = ''; }
		if ($lmm_options['minimap_basemap'] == 'automatic') {
			if ($basemap == 'osm_mapnik') {
				$minimap_basemap = 'osm_mapnik_minimap';
			} else if ($basemap == 'mapquest_osm') {
				$minimap_basemap = 'mapquest_osm_minimap';
			} else if ($basemap == 'mapquest_aerial') {
				$minimap_basemap = 'mapquest_aerial_minimap';
			} else if (($basemap == 'googleLayer_roadmap') && ($lmm_options['google_maps_api_status'] == 'enabled')) {
				$minimap_basemap = 'googleLayer_roadmap_minimap';
			} else if (($basemap == 'googleLayer_satellite') && ($lmm_options['google_maps_api_status'] == 'enabled')) {
				$minimap_basemap = 'googleLayer_satellite_minimap';
			} else if (($basemap == 'googleLayer_hybrid') && ($lmm_options['google_maps_api_status'] == 'enabled')) {
				$minimap_basemap = 'googleLayer_hybrid_minimap';
			} else if (($basemap == 'googleLayer_terrain') && ($lmm_options['google_maps_api_status'] == 'enabled')) {
				$minimap_basemap = 'googleLayer_terrain_minimap';
			} else if ( (isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL )) && ($basemap == 'bingaerial')){
				$minimap_basemap = 'bingaerial_minimap';
			} else if ( (isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL )) && ($basemap == 'bingaerialwithlabels')){
				$minimap_basemap = 'bingaerialwithlabels_minimap';
			} else if ( (isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL )) && ($basemap == 'bingroad')){
				$minimap_basemap = 'bingroad_minimap';
			} else {
				$minimap_basemap = 'osm_mapnik_minimap';
			}
		} else {
			$minimap_basemap = $lmm_options['minimap_basemap'];
			if (($lmm_options['google_maps_api_status'] == 'disabled') && (($minimap_basemap == 'googleLayer_roadmap') || ($minimap_basemap == 'googleLayer_satellite') || ($minimap_basemap == 'googleLayer_hybrid') || ($minimap_basemap == 'googleLayer_terrain')) ) {
				$minimap_basemap = 'osm_mapnik_minimap';
			}
		}
		$minimap_minimized = ($lmm_options['minimap_status'] == 'collapsed') ? 'true' : 'false';
		if ( wp_is_mobile() ) {	$lmmjs_out .= ' setTimeout(function(){ try{'.PHP_EOL; } //info: workaround for #191
			$lmmjs_out .= "var miniMap_" . $mapname_js . " = new L.Control.MiniMap(" . $minimap_basemap . ", {position: '" . $lmm_options['minimap_position'] . "', width: " . intval($lmm_options['minimap_width']) . ", height: " . intval($lmm_options['minimap_height']) . ", collapsedWidth: " . intval($lmm_options['minimap_collapsedWidth']) . ", collapsedHeight: " . intval($lmm_options['minimap_collapsedHeight']) . ", zoomLevelOffset: " . intval($lmm_options['minimap_zoomLevelOffset']) . ", " . $zoomlevelfixed . " zoomAnimation: " . $lmm_options['minimap_zoomAnimation'] . ", toggleDisplay: " . $lmm_options['minimap_toggleDisplay'] . ", autoToggleDisplay: " . $lmm_options['minimap_autoToggleDisplay'] . ", minimized: " . $minimap_minimized . "}).addTo(" . $mapname_js . ");".PHP_EOL;
		if ( wp_is_mobile() ) {	$lmmjs_out .= '}catch(e){} }, 1000);'.PHP_EOL; }

	}
	//info: filter details
	if($filter_details){
		$lmmjs_out .= 'var ordered_filter_details_'.$mapname_js.' = '. json_encode($ordered_filters) .';'.PHP_EOL;
		$lmmjs_out .= 'var filter_details_'.$mapname_js.' = '. json_encode($filter_details) .';'.PHP_EOL;
		$lmmjs_out .= 'var called_layers_'.$mapname_js.' = [];'.PHP_EOL;
		$lmmjs_out .= 'var active_layers_'.$mapname_js.' = [];'.PHP_EOL;
		$lmmjs_out .= 'var filtered_layers_'.$mapname_js.' = [];'.PHP_EOL;
		$lmmjs_out .= 'var active_layers_order = "'.$active_layers_order.'";'.PHP_EOL;
		$lmmjs_out .= 'var active_layers_orderby = "'.$active_layers_orderby.'";'.PHP_EOL;
		$lmmjs_out .= 'var filter_show_markercount = "'.$filter_show_markercount.'";'.PHP_EOL;
		$lmmjs_out .= 'var filter_show_icon = "'.$filter_show_icon.'";'.PHP_EOL;
		$lmmjs_out .= 'var filter_show_name = "'.$filter_show_name.'";'.PHP_EOL;

		$filters_collapsed_option = ($filters_collapsed!= 'hidden')?'"collapsed":'.$filters_collapsed.',':'';
		$lmmjs_out .= 'var filters_options_'.$mapname_js.' = {"position":"'.$lmm_options['mlm_filter_controlbox_position'].'",'.$filters_collapsed_option.'};'.PHP_EOL;
	}
	//info: gpx tracks
	if ($gpx_url != NULL) {
		if (preg_match("|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i", $gpx_url)) { //info: dont break map
			$gpx_track_color = '#' . str_replace('#', '', htmlspecialchars($lmm_options['gpx_track_color']));
			$gpx_startIconUrl = ($lmm_options['gpx_startIconUrl'] == NULL) ? LEAFLET_PLUGIN_URL . 'leaflet-dist/images/gpx-icon-start.png' : trim(htmlspecialchars($lmm_options['gpx_startIconUrl']));
			$gpx_endIconUrl = ($lmm_options['gpx_endIconUrl'] == NULL) ? LEAFLET_PLUGIN_URL . 'leaflet-dist/images/gpx-icon-end.png' : trim(htmlspecialchars($lmm_options['gpx_endIconUrl']));
			$gpx_shadowUrl = ($lmm_options['gpx_shadowUrl'] == NULL) ? LEAFLET_PLUGIN_URL . 'leaflet-dist/images/gpx-icon-shadow.png' : trim(htmlspecialchars($lmm_options['gpx_shadowUrl']));
			if ( (isset($lmm_options[ 'gpx_metadata_name' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_name' ] == 1 ) ) {
				$gpx_metadata_name_js = 'if (gpx.get_name() != undefined) { _c("gpx-name").innerHTML = gpx.get_name(); } else { _c("gpx-name").innerHTML = "n/a"; }';
		} else { $gpx_metadata_name_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_start' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_start' ] == 1 ) ) {
			$gpx_metadata_start_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-start").innerHTML = gpx.get_start_time().toDateString() + ", " + gpx.get_start_time().toLocaleTimeString(); } else { _c("gpx-start").innerHTML = "n/a"; }';
		} else { $gpx_metadata_start_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_end' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_end' ] == 1 ) ) {
			$gpx_metadata_end_js = 'if (gpx.get_end_time() != undefined) { _c("gpx-end").innerHTML = gpx.get_end_time().toDateString() + ", " + gpx.get_end_time().toLocaleTimeString(); } else { _c("gpx-end").innerHTML = "n/a"; }';
		} else { $gpx_metadata_end_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_distance' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_distance' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_distance_js = 'if (gpx.get_distance() != "0") { _c("gpx-distance").innerHTML = (gpx.get_distance()/1000).toFixed(2); } else { _c("gpx-distance").innerHTML = "n/a"; }';
			} else {
				$gpx_metadata_distance_js = 'if (gpx.get_distance() != "0") { _c("gpx-distance").innerHTML = gpx.get_distance_imp().toFixed(2); } else { _c("gpx-distance").innerHTML = "n/a"; }';
			}
		} else { $gpx_metadata_distance_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_duration_moving' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_moving' ] == 1 ) ) {
			$gpx_metadata_duration_moving_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-duration-moving").innerHTML = gpx.get_duration_string(gpx.get_moving_time()); } else { _c("gpx-duration-moving").innerHTML = "n/a"; }';
		} else { $gpx_metadata_duration_moving_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_duration_total' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_total' ] == 1 ) ) {
			$gpx_metadata_duration_total_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-duration-total").innerHTML = gpx.get_duration_string(gpx.get_total_time()); } else { _c("gpx-duration-total").innerHTML = "n/a"; }';
		} else { $gpx_metadata_duration_total_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_avpace' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avpace' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_avpace_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-avpace").innerHTML = gpx.get_duration_string(gpx.get_moving_pace(), true); } else { _c("gpx-avpace").innerHTML = "n/a"; }';
			} else {
			$gpx_metadata_avpace_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-avpace").innerHTML = gpx.get_duration_string(gpx.get_moving_pace_imp(), true); } else { _c("gpx-avpace").innerHTML = "n/a"; }';
			}
		} else { $gpx_metadata_avpace_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_avhr' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avhr' ] == 1 ) ) {
			$gpx_metadata_avhr_js = 'if (isNaN(gpx.get_average_hr())) { _c("gpx-avghr").innerHTML = "n/a"; } else { _c("gpx-avghr").innerHTML = gpx.get_average_hr() + "bpm"; }';
		} else { $gpx_metadata_avhr_js = ''; }
		if ( ((isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 )) ) {
			$gpx_metadata_elevation_title_js = '';
		} else { $gpx_metadata_elevation_title_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_elev_gain_js = '_c("gpx-elevation-gain").innerHTML = gpx.get_elevation_gain().toFixed(0);';
			} else {
				$gpx_metadata_elev_gain_js = '_c("gpx-elevation-gain").innerHTML = gpx.to_ft(gpx.get_elevation_gain()).toFixed(0);';
			}
		} else { $gpx_metadata_elev_gain_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_elev_loss_js = '_c("gpx-elevation-loss").innerHTML = gpx.get_elevation_loss().toFixed(0);';
			} else {
				$gpx_metadata_elev_loss_js = '_c("gpx-elevation-loss").innerHTML = gpx.to_ft(gpx.get_elevation_loss()).toFixed(0);';
			}
		} else { $gpx_metadata_elev_loss_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_elev_net_js = '_c("gpx-elevation-net").innerHTML  = gpx.get_elevation_gain().toFixed(0) - gpx.get_elevation_loss().toFixed(0);';
			} else {
				$gpx_metadata_elev_net_js = '_c("gpx-elevation-net").innerHTML  = gpx.to_ft(gpx.get_elevation_gain() - gpx.get_elevation_loss()).toFixed(0);';
			}
		} else { $gpx_metadata_elev_net_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_full' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_elev_full_js = '_c("gpx-elevation-full").innerHTML    = gpx.get_elevation_data();';
			} else {
				$gpx_metadata_elev_full_js = '_c("gpx-elevation-full").innerHTML    = gpx.get_elevation_data_imp();';
			}
		} else { $gpx_metadata_elev_full_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_hr_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_hr_full' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_hr_full_js = '_c("gpx-heartrate-full").innerHTML    = gpx.get_heartrate_data();';
			} else {
				$gpx_metadata_hr_full_js = '_c("gpx-heartrate-full").innerHTML    = gpx.get_heartrate_data_imp();';
			}
		} else { $gpx_metadata_hr_full_js = ''; }

			//info: load gpx_content
			$gpx_content_array = wp_remote_get( $gpx_url, array( "sslverify" => false, "timeout" => 30 ) );

			//info: do not load GPX if error on wp_remote_get occured
			if (!is_wp_error($gpx_content_array)) {
				$gpx_content = esc_js(str_replace("\xEF\xBB\xBF",'',$gpx_content_array['body'])); //info: replace UTF8-BOM for Chrome
				//info: check if WP DEBUG or other additional on-screen warnings or errors brake GeoJSON array validity
				if ( (strrpos($gpx_content, '&lt;?xml') !== FALSE) && (strrpos($gpx_content, '&lt;?xml') != 0) ){
					$position = strrpos($gpx_content, '&lt;?xml');
					$gpx_content = substr($gpx_content, $position);
				}
			} else {
				$gpx_content = '';
			}

$lmmjs_out .= 'function display_gpx_' . $uid . '() {
	var gpx_panel = document.getElementById("gpx-panel-' . $uid . '");
	var gpx_url = "'.$gpx_url.'";
	function _c(c) { return gpx_panel.querySelectorAll("."+c)[0]; }
	var gpx_track = new L.GPX(gpx_url, {
		gpx_content: "'.$gpx_content.'",
		async: true,
		max_point_interval: ' . intval($lmm_options['gpx_max_point_interval']) . ',
		marker_options: {
			startIconUrl: "' . $gpx_startIconUrl . '",
			endIconUrl: "' . $gpx_endIconUrl . '",
			shadowUrl: "' . $gpx_shadowUrl . '",
			iconSize: [' . $lmm_options['gpx_iconSize_x'] . ', ' . $lmm_options['gpx_iconSize_y'] . '],
			shadowSize: [' . $lmm_options['gpx_shadowSize_x'] . ', ' . $lmm_options['gpx_shadowSize_y'] . '],
			iconAnchor: [' . $lmm_options['gpx_iconAnchor_x'] . ', ' . $lmm_options['gpx_iconAnchor_y'] . '],
			shadowAnchor: [' . $lmm_options['gpx_shadowAnchor_x'] . ', ' . $lmm_options['gpx_shadowAnchor_y'] . '],
			className: "lmm_gpx_icons"
		},
		polyline_options: {
			color: "' . $gpx_track_color . '",
			weight: ' . intval($lmm_options['gpx_track_weight']) . ',
			opacity: "' . str_replace(',', '.', floatval($lmm_options['gpx_track_opacity'])) . '",
			smoothFactor: "' . str_replace(',', '.', floatval($lmm_options['gpx_track_smoothFactor'])) . '",
			clickable: ' . $lmm_options['gpx_track_clickable'] . ',
			noClip: ' . $lmm_options['gpx_track_noClip'] . '
		}
	}).addTo(' . $mapname_js . ');
	gpx_track.on("gpx_loaded", function(e) {
		var gpx = e.target;
		' . $gpx_metadata_name_js . '
		' . $gpx_metadata_start_js . '
		' . $gpx_metadata_end_js . '
		' . $gpx_metadata_distance_js . '
		' . $gpx_metadata_duration_moving_js . '
		' . $gpx_metadata_duration_total_js . '
		' . $gpx_metadata_avpace_js . '
		' . $gpx_metadata_avhr_js . '
		' . $gpx_metadata_elev_gain_js . '
		' . $gpx_metadata_elev_loss_js . '
		' . $gpx_metadata_elev_net_js . '
		' . $gpx_metadata_elev_full_js . '
		' . $gpx_metadata_hr_full_js . '
	});
}
display_gpx_' . $uid . '();'.PHP_EOL;
		}
	}
	//info: add scale control
	if ( $lmm_options['map_scale_control'] == 'enabled' ) {
		$lmmjs_out .= "var scale_".$mapname_js." = L.control.scale({position:'" . $lmm_options['map_scale_control_position'] . "', maxWidth: " . intval($lmm_options['map_scale_control_maxwidth']) . ", metric: " . $lmm_options['map_scale_control_metric'] . ", imperial: " . $lmm_options['map_scale_control_imperial'] . ", updateWhenIdle: " . $lmm_options['map_scale_control_updatewhenidle'] . "});".PHP_EOL;
		$lmmjs_out .= "scale_".$mapname_js.".addTo(".$mapname_js.");".PHP_EOL;
	}

	//info: add geolocate control
	if ($lmm_options['geolocate_status'] == 'true') {
		if ( ($is_chrome === TRUE) && (is_ssl() === FALSE) ) { $onlocationerror = ', onLocationError: function () {}'; } else { $onlocationerror = ''; }
		$lmmjs_out .= "var locatecontrol_".$mapname_js." = L.control.locate({ position: '" . $lmm_options[ 'geolocate_position' ] . "', drawCircle: " . $lmm_options[ 'geolocate_drawCircle' ] . ", follow: " . $lmm_options[ 'geolocate_follow' ] . ", setView: " . $lmm_options[ 'geolocate_setView' ] . ", keepCurrentZoomLevel: " . $lmm_options[ 'geolocate_keepCurrentZoomLevel' ] . ", remainActive: " . $lmm_options[ 'geolocate_remainActive' ] . ", circleStyle: {" . htmlspecialchars($lmm_options[ 'geolocate_circleStyle' ]) . "}, markerStyle: {" . htmlspecialchars($lmm_options[ 'geolocate_markerStyle' ]) . "}, followCircleStyle: {" . htmlspecialchars($lmm_options[ 'geolocate_followCircleStyle' ]) . "}, followMarkerStyle: {" . htmlspecialchars($lmm_options[ 'geolocate_followMarkerStyle' ]) . "}, icon: '" . $lmm_options[ 'geolocate_icon' ] . "', circlePadding: " . htmlspecialchars($lmm_options[ 'geolocate_circlePadding' ]) . ", metric: " . $lmm_options[ 'geolocate_units' ] . ", showPopup: " . $lmm_options[ 'geolocate_showPopup' ] . ", strings: { title: '" . $text_showme_where_i . "', metersUnit: '" . __('meters','lmm') . "', feetUnit: '" . __('feet','lmm') . "', popup: '" . sprintf(__('You are within %1$s %2$s from this point','lmm'), '{distance}', '{unit}') . "', outsideMapBoundsMsg: '" . __('You seem located outside the boundaries of the map','lmm') . "' }, locateOptions: {" . htmlspecialchars($lmm_options[ 'geolocate_locateOptions' ]) . "}" . $onlocationerror . " }).addTo(" . $mapname_js . ");".PHP_EOL;
		if ( $lmm_options['geolocate_autostart'] == 'true' ) {
			$lmmjs_out .= "locatecontrol_".$mapname_js.".start();";
		}
	}

	if (!(empty($mlat) or empty($mlon)) ) {
	if ($lmm_options[ 'defaults_marker_icon_title' ] == 'show') { $defaults_marker_icon_title = "title: '" . strip_tags(htmlspecialchars_decode($markername)) . "', "; } else { $defaults_marker_icon_title = ""; };
	$lmmjs_out .= 'var marker_'.$mapname_js.' = new L.Marker(new L.LatLng('.$mlat.', '.$mlon.'),{ ' . $defaults_marker_icon_title . ' opacity: ' . floatval($lmm_options[ 'defaults_marker_icon_opacity' ]) . ', alt: "' . strip_tags(htmlspecialchars_decode($markername)) . '"});'.PHP_EOL;
	 if ($micon == NULL) {
		  $lmmjs_out .= "marker_".$mapname_js.".options.icon = new L.Icon({iconUrl: '" . LEAFLET_PLUGIN_URL . "leaflet-dist/images/marker.png',iconSize: [" . intval($lmm_options[ 'defaults_marker_icon_iconsize_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_iconsize_y' ]) . "],iconAnchor: [" . intval($lmm_options[ 'defaults_marker_icon_iconanchor_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_iconanchor_y' ]) . "],popupAnchor: [" . intval($lmm_options[ 'defaults_marker_icon_popupanchor_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_popupanchor_y' ]) . "],shadowUrl: '" . $marker_shadow_url . "',shadowSize: [" . intval($lmm_options[ 'defaults_marker_icon_shadowsize_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_shadowsize_y' ]) . "],shadowAnchor: [" . intval($lmm_options[ 'defaults_marker_icon_shadowanchor_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_shadowanchor_y' ]) . "],className: 'lmm_marker_icon_default'});".PHP_EOL;
	  } else {
		  $lmmjs_out .= "marker_".$mapname_js.".options.icon = new L.Icon({iconUrl: '" . $defaults_marker_icon_url . "/" . $icon . "',iconSize: [" . intval($lmm_options[ 'defaults_marker_icon_iconsize_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_iconsize_y' ]) . "],iconAnchor: [" . intval($lmm_options[ 'defaults_marker_icon_iconanchor_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_iconanchor_y' ]) . "],popupAnchor: [" . intval($lmm_options[ 'defaults_marker_icon_popupanchor_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_popupanchor_y' ]) . "],shadowUrl: '" . $marker_shadow_url . "',shadowSize: [" . intval($lmm_options[ 'defaults_marker_icon_shadowsize_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_shadowsize_y' ]) . "],shadowAnchor: [" . intval($lmm_options[ 'defaults_marker_icon_shadowanchor_x' ]) . ", " . intval($lmm_options[ 'defaults_marker_icon_shadowanchor_y' ]) . "],className: 'lmm_marker_icon_" . substr($icon, 0, -4) . "'});".PHP_EOL;
	};
	if ( (empty($mpopuptext)) && ($lmm_options['directions_popuptext_panel'] == 'no') ) $lmmjs_out .= 'marker_'.$mapname_js.'.options.clickable = false;'.PHP_EOL;
	$lmmjs_out .= $mapname_js.'.addLayer(marker_'.$mapname_js.');'.PHP_EOL;

	if (!empty($mpopuptext)) {
			$lmmjs_out .= 'marker_'.$mapname_js.'.bindPopup(document.getElementById("' . $mapname . '-popuptext-hidden").innerHTML, {maxWidth: ' . intval($lmm_options['defaults_marker_popups_maxwidth']) . ', minWidth: ' . intval($lmm_options['defaults_marker_popups_minwidth']) . ', maxHeight: ' . intval($lmm_options['defaults_marker_popups_maxheight']) . ', autoPan: ' . $lmm_options['defaults_marker_popups_autopan'] . ', closeButton: ' . $lmm_options['defaults_marker_popups_closebutton'] . ', autoPanPadding: new L.Point(' . intval($lmm_options['defaults_marker_popups_autopanpadding_x']) . ', ' . intval($lmm_options['defaults_marker_popups_autopanpadding_y']) . ')})'.$mopenpopup.';'.PHP_EOL;
			$lmmjs_out .= 'marker_'.$mapname_js.'.on("popupclose", function(e) { '.$mapname_js.'.setView(new L.LatLng('.$lat.', '.$lon.'), '.$mapname_js.'.getZoom()); });'.PHP_EOL;
		}
	} else if (!empty($geojson) or !empty($geojsonurl) or !empty($layer) ) {
		$lmmjs_out .= 'var geojsonObj_'.$mapname_js.', mapIcon, marker_clickable, marker_title;'.PHP_EOL;
		//info: load GeoJSON for layer maps
		if (!empty($layer)) {
			$lmmjs_out .= 'var xhReq_'.$mapname_js.' = new XMLHttpRequest();'.PHP_EOL;
			if ($multi_layer_map == 0 ) { $geojson_api_link = $id; } else { $geojson_api_link = $multi_layer_map_list; }
			if ($lmm_options['async_geojson_loading'] == 'enabled') {
				$lmmjs_out .= 'xhReq_'.$mapname_js.'.open("GET", "' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?layer=' . $geojson_api_link . '&full=no&full_icon_url=no&listmarkers='.$listmarkers.'", true);'.PHP_EOL; //info: for caching add &timestamp=' . time() . '
				$lmmjs_out .= 'xhReq_'.$mapname_js.'.onreadystatechange = function xhReq_'.$uid.'(e) { if (xhReq_'.$mapname_js.'.readyState === 4) { if (xhReq_'.$mapname_js.'.status === 200) {'.PHP_EOL; //info: async 1/2
			} else {
				$lmmjs_out .= 'xhReq_'.$mapname_js.'.open("GET", "' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?layer=' . $geojson_api_link . '&full=no&full_icon_url=no&listmarkers='.$listmarkers.'", false);'.PHP_EOL;
				$lmmjs_out .= 'xhReq_'.$mapname_js.'.send(null);'.PHP_EOL;
			}

			//info: check if WP DEBUG or other additional on-screen warnings or errors brake GeoJSON array validity
			$lmmjs_out .= 'if (xhReq_'.$mapname_js.'.responseText.indexOf(\'{"type"\') != 0) {
	var '.$mapname_js.'_position = xhReq_'.$mapname_js.'.responseText.indexOf(\'{"type"\');
	try { geojsonObj_'.$mapname_js.' = JSON.parse(xhReq_'.$mapname_js.'.responseText.slice('.$mapname_js.'_position)); } catch (e) { console.log("' . esc_attr__('Error - invalid GeoJSON object:','lmm') . ' "+e.message); }'.PHP_EOL;
			$lmmjs_out .= '} else {
	try { geojsonObj_'.$mapname_js.' = JSON.parse(xhReq_'.$mapname_js.'.responseText); } catch (e) { console.log("' . esc_attr__('Error - invalid GeoJSON object:','lmm') . ' "+e.message); }'.PHP_EOL;
			$lmmjs_out .= '}'.PHP_EOL;
		}
		//info: clustering 1/2
		if (!empty($layer)) {
			if ($clustering == '1') {
				$polygon_options_stroke = 'stroke: ' . $lmm_options['clustering_polygonOptions_stroke'];
				$polygon_options_color = 'color: \'#' . str_replace('#', '', htmlspecialchars($lmm_options['clustering_polygonOptions_color'])) . '\'';
				$polygon_options_weight = 'weight: ' . str_replace(',', '.', floatval($lmm_options['clustering_polygonOptions_weight']));
				$polygon_options_opacity = 'opacity: ' . str_replace(',', '.', floatval($lmm_options['clustering_polygonOptions_opacity']));
				$polygon_options_fillcolor = 'fillColor: \'#' . str_replace('#', '', htmlspecialchars($lmm_options['clustering_polygonOptions_fillColor'])) . '\'';
				$polygon_options_fillopacity = 'fillOpacity: ' . str_replace(',', '.', floatval($lmm_options['clustering_polygonOptions_fillopacity']));
				$polygon_options_clickable= 'clickable: ' . $lmm_options['clustering_polygonOptions_clickable'];
				if ($lmm_options['clustering_polygonOptions_fill'] == 'auto') {
					$polygon_options_array = array($polygon_options_stroke, $polygon_options_color, $polygon_options_weight, $polygon_options_opacity, $polygon_options_fillcolor, $polygon_options_fillopacity, $polygon_options_clickable);
				} else {
					$polygon_options_fill = 'fill: false';
					$polygon_options_array = array($polygon_options_stroke, $polygon_options_color, $polygon_options_weight, $polygon_options_opacity, $polygon_options_fill, $polygon_options_fillcolor, $polygon_options_fillopacity, $polygon_options_clickable);
				}
				$polygon_options = implode(', ',$polygon_options_array);
				//info: spiderLegPolylineOptions
				$spiderLeg_polyline_options_color = 'color: \'#' . str_replace('#', '', htmlspecialchars($lmm_options['clustering_spiderLegPolylineOptions_color'])) . '\'';
				$spiderLeg_polyline__options_weight = 'weight: ' . str_replace(',', '.', floatval($lmm_options['clustering_spiderLegPolylineOptions_weight']));
				$spiderLeg_polyline_options_opacity = 'opacity: ' . str_replace(',', '.', floatval($lmm_options['clustering_spiderLegPolylineOptions_opacity']));
				$spiderLeg_polyline_options = $spiderLeg_polyline_options_color . ',' . $spiderLeg_polyline__options_weight . ',' . $spiderLeg_polyline_options_opacity;
				//info: markercluster progress bar
				$lmmjs_out .= "var progress = document.getElementById('lmm_".$uid."-progress');".PHP_EOL;
				$lmmjs_out .= "var progressBar = document.getElementById('lmm_".$uid."-progress-bar');".PHP_EOL;
				$lmmjs_out .= "function updateProgressBar".$uid."(processed, total, elapsed, layersArray) {
		if (elapsed > 1000) {
		progress.style.display = 'block';
		progressBar.style.width = Math.round(processed/total*100) + '%';
	}
	if (processed === total) {
		progress.style.display = 'none';
	}
}".PHP_EOL;
				if($filter_details){
					$lmmjs_out .= 'markercluster_'.$mapname_js.' = new L.markerClusterGroup.layerSupport({ zoomToBoundsOnClick: ' . $lmm_options['clustering_zoomToBoundsOnClick'] . ', showCoverageOnHover: ' . $lmm_options['clustering_showCoverageOnHover'] . ', spiderfyOnMaxZoom: ' . $lmm_options['clustering_spiderfyOnMaxZoom'] . ', animateAddingMarkers: ' . $lmm_options['clustering_animateAddingMarkers'] . ', disableClusteringAtZoom: ' . intval($lmm_options['clustering_disableClusteringAtZoom']) . ', maxClusterRadius: ' . intval($lmm_options['clustering_maxClusterRadius']) . ', polygonOptions: {' . $polygon_options . '}, singleMarkerMode: ' . $lmm_options['clustering_singleMarkerMode'] . ', spiderfyDistanceMultiplier: ' . intval($lmm_options['clustering_spiderfyDistanceMultiplier']) . ', spiderLegPolylineOptions: {' . $spiderLeg_polyline_options . '}, chunkedLoading: true, chunkProgress: updateProgressBar'.$uid.' });'.PHP_EOL; //info: var removed - setting global for listmarkers action
				}else{
					$lmmjs_out .= 'markercluster_'.$mapname_js.' = new L.MarkerClusterGroup({ zoomToBoundsOnClick: ' . $lmm_options['clustering_zoomToBoundsOnClick'] . ', showCoverageOnHover: ' . $lmm_options['clustering_showCoverageOnHover'] . ', spiderfyOnMaxZoom: ' . $lmm_options['clustering_spiderfyOnMaxZoom'] . ', animateAddingMarkers: ' . $lmm_options['clustering_animateAddingMarkers'] . ', disableClusteringAtZoom: ' . intval($lmm_options['clustering_disableClusteringAtZoom']) . ', maxClusterRadius: ' . intval($lmm_options['clustering_maxClusterRadius']) . ', polygonOptions: {' . $polygon_options . '}, singleMarkerMode: ' . $lmm_options['clustering_singleMarkerMode'] . ', spiderfyDistanceMultiplier: ' . intval($lmm_options['clustering_spiderfyDistanceMultiplier']) . ', spiderLegPolylineOptions: {' . $spiderLeg_polyline_options . '}, chunkedLoading: true, chunkProgress: updateProgressBar'.$uid.' });'.PHP_EOL; //info: var removed - setting global for listmarkers action
				}
			}
		}
			if($filter_details){
				$lmmjs_out .= 'var overlays_filters_'.$mapname_js.' = [];'.PHP_EOL;
			}
			$lmmjs_out .= $mapname_js.'_geojson_markers = L.geoJson(geojsonObj_'.$mapname_js.', {'.PHP_EOL; //info: var removed - setting global for listmarkers action
			$lmmjs_out .= '		onEachFeature: function(feature, marker) {'.PHP_EOL;
			$lmmjs_out .= '			markerID_' . $mapname_js . '[feature.properties.markerid] = marker;'.PHP_EOL;
			$lmmjs_out .= '			var requested_layer = feature.properties.requested_layer;'.PHP_EOL;
			if($filter_details){
				$lmmjs_out .= '			if (requested_layer.length > 0){'.PHP_EOL;
				$lmmjs_out .= '				jQuery.each(requested_layer, function(index, layer){'.PHP_EOL;
				$lmmjs_out .= '					if(typeof filtered_layers_'.$mapname_js.'[layer] != "object"){'.PHP_EOL;
				$lmmjs_out .= '						filtered_layers_'.$mapname_js.'[layer] = L.layerGroup();'.PHP_EOL;
				$lmmjs_out .= '					}'.PHP_EOL;
				$lmmjs_out .= '					marker.addTo(filtered_layers_'.$mapname_js.'[layer]);'.PHP_EOL;
				$lmmjs_out .= '					if(typeof filter_details_'.$mapname_js.'[layer] !="undefined"){'.PHP_EOL;
				$lmmjs_out .= '						filtered_layers_'.$mapname_js.'[layer]["status"] = filter_details_'.$mapname_js.'[layer]["status"];'.PHP_EOL;
				$lmmjs_out .= '						filtered_layers_'.$mapname_js.'[layer]["name"] = filter_details_'.$mapname_js.'[layer]["name"];'.PHP_EOL;
				$lmmjs_out .= '						filtered_layers_'.$mapname_js.'[layer]["markercount"] = filter_details_'.$mapname_js.'[layer]["markercount"];'.PHP_EOL;
				$lmmjs_out .= '						filtered_layers_'.$mapname_js.'[layer]["layer_id"] = layer;'.PHP_EOL;
				$lmmjs_out .= '					}'.PHP_EOL;
				$lmmjs_out .= '				});'.PHP_EOL;
				$lmmjs_out .= '			}'.PHP_EOL;
			}
		if ($lmm_options['directions_popuptext_panel'] == 'yes') {
			$lmmjs_out .= 'if (feature.properties.text != "") { var css = "border-top:1px solid #f0f0e7;padding-top:5px;margin-top:5px;clear:both;"; } else { var css = ""; }'.PHP_EOL;
			if ($lmm_options['defaults_marker_popups_add_markername'] == 'true') {
				$lmmjs_out .= 'if (feature.properties.markername != "") { var divmarkername1 = "<div class=\"popup-markername\"  style=\"border-bottom:1px solid #f0f0e7;padding-bottom:5px;margin-bottom:6px;\">"; var divmarkername2 = "</div>" } else { var divmarkername1 = ""; var divmarkername2 = ""; }'.PHP_EOL;
				$lmmjs_out .= 'marker.bindPopup(divmarkername1+feature.properties.markername+divmarkername2+feature.properties.text+"<div class=\"popup-directions\" style=\""+css+"\">"+feature.properties.address+" <a href=\""+feature.properties.dlink+"\" target=\"_blank\" title=\"' . esc_attr__('Get directions','lmm') . '\">(' . $text_directions . ')</a></div>", {'.PHP_EOL;
			} else {
				$lmmjs_out .= 'marker.bindPopup(feature.properties.text+"<div class=\"popup-directions\" style=\""+css+"\">"+feature.properties.address+" <a href=\""+feature.properties.dlink+"\" target=\"_blank\" title=\"' . esc_attr__('Get directions','lmm') . '\">(' . $text_directions . ')</a></div>", {'.PHP_EOL;
			}
			$lmmjs_out .= '		maxWidth: ' . intval($lmm_options['defaults_marker_popups_maxwidth']) . ', '.PHP_EOL;
			$lmmjs_out .= '		minWidth: ' . intval($lmm_options['defaults_marker_popups_minwidth']) . ', '.PHP_EOL;
			$lmmjs_out .= '		maxHeight: ' . intval($lmm_options['defaults_marker_popups_maxheight']) . ', '.PHP_EOL;
			$lmmjs_out .= '		autoPan: ' . $lmm_options['defaults_marker_popups_autopan'] . ', '.PHP_EOL;
			$lmmjs_out .= '		closeButton: ' . $lmm_options['defaults_marker_popups_closebutton'] . ', '.PHP_EOL;
			$lmmjs_out .= '		autoPanPadding: new L.Point(' . intval($lmm_options['defaults_marker_popups_autopanpadding_x']) . ', ' . intval($lmm_options['defaults_marker_popups_autopanpadding_y']) . ')'.PHP_EOL;
			$lmmjs_out .= '});'.PHP_EOL;
		} else {
			if ($lmm_options['defaults_marker_popups_add_markername'] == 'true') {
				$lmmjs_out .= 'if (feature.properties.markername != "") { var divmarkername1 = "<div class=\"popup-markername\"  style=\"border-bottom:1px solid #f0f0e7;padding-bottom:5px;margin-bottom:6px;\">"; var divmarkername2 = "</div>" } else { var divmarkername1 = ""; var divmarkername2 = ""; }'.PHP_EOL;
				$lmmjs_out .= 'marker.bindPopup(divmarkername1+feature.properties.markername+divmarkername2+feature.properties.text, {'.PHP_EOL;
			} else {
				$lmmjs_out .= 'marker.bindPopup(feature.properties.text, {'.PHP_EOL;
			}
			$lmmjs_out .= '		maxWidth: ' . intval($lmm_options['defaults_marker_popups_maxwidth']) . ', '.PHP_EOL;
			$lmmjs_out .= '		minWidth: ' . intval($lmm_options['defaults_marker_popups_minwidth']) . ', '.PHP_EOL;
			$lmmjs_out .= '		maxHeight: ' . intval($lmm_options['defaults_marker_popups_maxheight']) . ', '.PHP_EOL;
			$lmmjs_out .= '		autoPan: ' . $lmm_options['defaults_marker_popups_autopan'] . ', '.PHP_EOL;
			$lmmjs_out .= '		closeButton: ' . $lmm_options['defaults_marker_popups_closebutton'] . ', '.PHP_EOL;
			$lmmjs_out .= '		autoPanPadding: new L.Point(' . intval($lmm_options['defaults_marker_popups_autopanpadding_x']) . ', ' . intval($lmm_options['defaults_marker_popups_autopanpadding_y']) . ')'.PHP_EOL;
			$lmmjs_out .= '});'.PHP_EOL;
		}
		$lmmjs_out .= '},'.PHP_EOL;
		$lmmjs_out .= 'pointToLayer: function (feature, latlng) {'.PHP_EOL;
		//info: keep GeoJSON small for internal use
		$lmmjs_out .= " if (feature.properties.iconUrl == undefined) {".PHP_EOL;
		$lmmjs_out .= '	mapIcon = L.icon({ '.PHP_EOL;
		$lmmjs_out .= "		iconUrl: (feature.properties.icon != '') ? '" . $defaults_marker_icon_url . "/' + feature.properties.icon : '" . LEAFLET_PLUGIN_URL . "leaflet-dist/images/marker.png" . "',".PHP_EOL;
		$lmmjs_out .= '		iconSize: [' . intval($lmm_options[ 'defaults_marker_icon_iconsize_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_iconsize_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= '		iconAnchor: [' . intval($lmm_options[ 'defaults_marker_icon_iconanchor_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_iconanchor_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= '		popupAnchor: [' . intval($lmm_options[ 'defaults_marker_icon_popupanchor_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_popupanchor_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= "		shadowUrl: '" . $marker_shadow_url . "',".PHP_EOL;
		$lmmjs_out .= '		shadowSize: [' . intval($lmm_options[ 'defaults_marker_icon_shadowsize_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_shadowsize_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= '		shadowAnchor: [' . intval($lmm_options[ 'defaults_marker_icon_shadowanchor_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_shadowanchor_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= "		className: (feature.properties.icon == '') ? 'lmm_marker_icon_default' : 'lmm_marker_icon_'+ feature.properties.icon.slice(0,-4)".PHP_EOL;
		$lmmjs_out .= '	});'.PHP_EOL;
		//info: use full icon url for external use
		$lmmjs_out .= '} else {'.PHP_EOL;
		$lmmjs_out .= '	mapIcon = L.icon({ '.PHP_EOL;
		$lmmjs_out .= "		iconUrl: feature.properties.iconUrl,".PHP_EOL;
		$lmmjs_out .= '		iconSize: [' . intval($lmm_options[ 'defaults_marker_icon_iconsize_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_iconsize_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= '		iconAnchor: [' . intval($lmm_options[ 'defaults_marker_icon_iconanchor_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_iconanchor_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= '		popupAnchor: [' . intval($lmm_options[ 'defaults_marker_icon_popupanchor_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_popupanchor_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= "		shadowUrl: '" . $marker_shadow_url . "',".PHP_EOL;
		$lmmjs_out .= '		shadowSize: [' . intval($lmm_options[ 'defaults_marker_icon_shadowsize_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_shadowsize_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= '		shadowAnchor: [' . intval($lmm_options[ 'defaults_marker_icon_shadowanchor_x' ]) . ', ' . intval($lmm_options[ 'defaults_marker_icon_shadowanchor_y' ]) . '],'.PHP_EOL;
		$lmmjs_out .= "		className: (feature.properties.icon == '') ? 'lmm_marker_icon_default' : 'lmm_marker_icon_'+ feature.properties.icon.slice(0,-4)".PHP_EOL;
		$lmmjs_out .= '	});'.PHP_EOL;
		$lmmjs_out .= '};'.PHP_EOL;

		$lmmjs_out .= 'if (feature.properties.text != "" || (feature.properties.dlink != "" && feature.properties.dlink != undefined)) { marker_clickable = true } else { marker_clickable = false };'.PHP_EOL;

		if ($lmm_options[ 'defaults_marker_icon_title' ] == 'show') {
		$lmmjs_out .= "if (feature.properties.markername == '') { marker_title = '' } else { marker_title = feature.properties.markername };".PHP_EOL;
		}
		$lmmjs_out .= 'return L.marker(latlng, {icon: mapIcon, clickable: marker_clickable, title: marker_title, alt: marker_title, opacity: ' . floatval($lmm_options[ 'defaults_marker_icon_opacity' ]) . '});'.PHP_EOL;
		$lmmjs_out .= '}});'.PHP_EOL;
		if($filter_details){
			//info: add layers with markercount = 0
			$lmmjs_out .= 'jQuery.each(filter_details_'.$mapname_js.', function(lid, filter){'.PHP_EOL;
			$lmmjs_out .= '	if(filter.status == "active"){'.PHP_EOL;
			$lmmjs_out .= '		called_layers_'.$mapname_js.'[lid] = true;'.PHP_EOL;
			$lmmjs_out .= '		active_layers_'.$mapname_js.'.push(lid);'.PHP_EOL;
			$lmmjs_out .= '	}'.PHP_EOL;
			$lmmjs_out .= '	if(filter.markercount == "0" && filter.status == "active"){'.PHP_EOL;
			$lmmjs_out .= '		filtered_layers_'.$mapname_js.'[lid] = L.layerGroup();'.PHP_EOL;
			$lmmjs_out .= '		filtered_layers_'.$mapname_js.'[lid]["status"] = filter.status;'.PHP_EOL;
			$lmmjs_out .= '		filtered_layers_'.$mapname_js.'[lid]["name"] = filter.name;'.PHP_EOL;
			$lmmjs_out .= '		filtered_layers_'.$mapname_js.'[lid]["layer_id"] = lid;'.PHP_EOL;
			$lmmjs_out .= '		filtered_layers_'.$mapname_js.'[lid]["markercount"] = 0;'.PHP_EOL;
			$lmmjs_out .= '		called_layers_'.$mapname_js.'[lid] = true;'.PHP_EOL;
			$lmmjs_out .= '		active_layers_'.$mapname_js.'.push(lid);'.PHP_EOL;
			$lmmjs_out .= '	}'.PHP_EOL;
			$lmmjs_out .= '});'.PHP_EOL;
		}
		//info: clustering 2/2
		if ($clustering == '1') {
			if($filter_details){
				$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] !== true){'.PHP_EOL;
				$lmmjs_out .= '	filtered_layers_'.$mapname_js.'.sort(function(a, b){'.PHP_EOL;
				$lmmjs_out .= '		  	if(active_layers_orderby =="name"){'.PHP_EOL;
				$lmmjs_out .= ' 			b[active_layers_orderby] = b[active_layers_orderby].toLowerCase();'.PHP_EOL;
				$lmmjs_out .= ' 			a[active_layers_orderby] = a[active_layers_orderby].toLowerCase();'.PHP_EOL;
				$lmmjs_out .= ' 	    }'.PHP_EOL;
				$lmmjs_out .= '		if(active_layers_order == "DESC"){'.PHP_EOL;
				$lmmjs_out .= '			if(b[active_layers_orderby] < a[active_layers_orderby])	return -1;'.PHP_EOL;
				$lmmjs_out .= '			else if(b[active_layers_orderby] > a[active_layers_orderby])	return 1;'.PHP_EOL;
				$lmmjs_out .= '			return 0;'.PHP_EOL;
				$lmmjs_out .= '		}else{'.PHP_EOL;
				$lmmjs_out .= '		if(b[active_layers_orderby] < a[active_layers_orderby])	return 1;'.PHP_EOL;
				$lmmjs_out .= '			else if(b[active_layers_orderby] > a[active_layers_orderby])	return -1;'.PHP_EOL;
				$lmmjs_out .= '			return 0;'.PHP_EOL;
				$lmmjs_out .= '		}'.PHP_EOL;
				$lmmjs_out .= '	});'.PHP_EOL;
				$lmmjs_out .= '}'.PHP_EOL;

				$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] !== true){'.PHP_EOL;
				$lmmjs_out .= 'jQuery.each(filtered_layers_'.$mapname_js.', function(lid, group){'.PHP_EOL;
				$lmmjs_out .= '		try{'.PHP_EOL;
				$lmmjs_out .= '			if(filter_show_markercount != "0"){ var markercount = "&nbsp;&nbsp;<span class=\"mlm-filters-markercount\" title=\"' . esc_attr__('number of markers','lmm') . '\">[ " + filter_details_'.$mapname_js.'[group.layer_id].markercount + " ]</span>"; }else{ var markercount = ""; } '.PHP_EOL;
				$lmmjs_out .= '			if(filter_details_'.$mapname_js.'[group.layer_id]["icon"] != "" && filter_show_icon != "0"){  var filter_icon = "<img src=\'"+ filter_details_'.$mapname_js.'[group.layer_id]["icon"] +"\' />"; }else{ var filter_icon = "";  }'.PHP_EOL;
				$lmmjs_out .= '			if(filter_show_name != "0"){ var filter_name = filter_details_'.$mapname_js.'[group.layer_id]["name"]; }else{ var filter_name = "";  }'.PHP_EOL;
				$lmmjs_out .= '			group["markercount"] = filter_details_'.$mapname_js.'[group.layer_id].markercount;'.PHP_EOL;
				$lmmjs_out .= '			overlays_filters_'.$mapname_js.'[ "<span class=\"mlm-filters-icon\">" + filter_icon + "</span><span class=\"mlm-filters-layername\">" + filter_name + "</span>" + markercount   ] = group;'.PHP_EOL;
				$lmmjs_out .= '			if(group["status"] == "active"){'.PHP_EOL;
				$lmmjs_out .= '				if(called_layers_'.$mapname_js.'[group.layer_id] == true){'.PHP_EOL;
				$lmmjs_out .= '					markercluster_'.$mapname_js.'.addLayer(group);'.PHP_EOL;
				$lmmjs_out .= '				}'.PHP_EOL;
				$lmmjs_out .= '				called_layers_'.$mapname_js.'[group.layer_id] = false;'.PHP_EOL;
				$lmmjs_out .= '			}'.PHP_EOL;
				$lmmjs_out .= '		}catch(n){}'.PHP_EOL;
				$lmmjs_out .= '	});'.PHP_EOL;
				$lmmjs_out .= '}'.PHP_EOL;
				//info: add inactive layers to the controlbox
				$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] !== true){'.PHP_EOL;
				$lmmjs_out .= 'jQuery.each(ordered_filter_details_'.$mapname_js.', function(lid, filter){'.PHP_EOL;
				$lmmjs_out .= '		if(filter["status"] == "inactive"){'.PHP_EOL;
				$lmmjs_out .= '			filtered_layers_'.$mapname_js.'[filter.id] = L.layerGroup();'.PHP_EOL;
				$lmmjs_out .= '			filtered_layers_'.$mapname_js.'[filter.id]["layer_id"] =filter.id;'.PHP_EOL;
				$lmmjs_out .= '			filtered_layers_'.$mapname_js.'[filter.id]["markercount"] = filter.markercount;'.PHP_EOL;
				$lmmjs_out .= '			if(filter_show_markercount != "0"){ var markercount = "&nbsp;&nbsp;<span class=\"mlm-filters-markercount\" title=\"' . esc_attr__('number of markers','lmm') . '\">[ " + filter.markercount + " ]</span>"; }else{ var markercount = ""; } '.PHP_EOL;
				$lmmjs_out .= '			if(filter_details_'.$mapname_js.'[filter.id]["icon"] != "" && filter_show_icon != "0"){ var filter_icon = "<img class=\"mlm-filters-icon\" src=\'"+ filter_details_'.$mapname_js.'[filter.id]["icon"] +"\' />"; }else{ var filter_icon = "";  }'.PHP_EOL;
				$lmmjs_out .= '			if(filter_show_name != "0"){ var filter_name = filter_details_'.$mapname_js.'[filter.id]["name"]; }else{ var filter_name = "";  }'.PHP_EOL;
				$lmmjs_out .= '			overlays_filters_'.$mapname_js.'[ "<span class=\"mlm-filters-icon\">" + filter_icon + "</span><span class=\"mlm-filters-layername\">" + filter_name + "</span>" + markercount  ] = filtered_layers_'.$mapname_js.'[filter.id];'.PHP_EOL;
				$lmmjs_out .= '		}'.PHP_EOL;
				$lmmjs_out .= '	});'.PHP_EOL;
				$lmmjs_out .= '}'.PHP_EOL;

				if ($filters_collapsed != 'hidden') {
					$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] !== true){'.PHP_EOL;
					$lmmjs_out .= '		L.control.filters(null, overlays_filters_'.$mapname_js.', filters_options_'.$mapname_js.').addTo('.$mapname_js.');'.PHP_EOL;
					$lmmjs_out .= '}'.PHP_EOL;
				}

				$lmmjs_out .= $mapname_js.'_geojson_markers.addTo(markercluster_'.$mapname_js.');'.PHP_EOL;
				$lmmjs_out .= $mapname_js . '.addLayer(markercluster_'.$mapname_js.');'.PHP_EOL;

				$lmmjs_out .= $mapname_js . '.on("overlayadd",function(){'.PHP_EOL;
					$lmmjs_out .= '	jQuery(".lmm-filter").click(function(){'.PHP_EOL;
					$lmmjs_out .= '		var layer_id = jQuery(this).attr("id");'.PHP_EOL;
					$lmmjs_out .= '		if(jQuery(this).is(":checked")){'.PHP_EOL;
					$lmmjs_out .= ' 		if(called_layers_'.$mapname_js.'[layer_id] !== true && active_layers_'.$mapname_js.'.indexOf(layer_id) == -1){'.PHP_EOL;
					$lmmjs_out .= '				xhReq_'.$mapname_js.'.open("GET","' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?layer="+ layer_id +"&full=no&full_icon_url=no&listmarkers='. $listmarkers .'",true);'.PHP_EOL;
					$lmmjs_out .= '				xhReq_'.$mapname_js.'.send();'.PHP_EOL;
					$lmmjs_out .= '				called_layers_'.$mapname_js.'[layer_id] = true;'.PHP_EOL;
					$lmmjs_out .= '				window["mmp_no_controlbox_'.$mapname_js.'"] = true;'.PHP_EOL;
					$lmmjs_out .= '			}'.PHP_EOL;
					if($listmarkers == 1){
						$lmmjs_out .= '			jQuery.each(filtered_layers_'.$mapname_js.',function(index,l){'.PHP_EOL;
						$lmmjs_out .= '				if(typeof l != "undefined"){'.PHP_EOL;
						$lmmjs_out .= '					if(l.layer_id == layer_id){'.PHP_EOL;
						$lmmjs_out .= '						window["current_called_layer"] = index;'.PHP_EOL;
						$lmmjs_out .= '						lmm_add_to_list_'.$mapname_js.'();'.PHP_EOL;
						$lmmjs_out .= '					}'.PHP_EOL;
						$lmmjs_out .= '				}'.PHP_EOL;
						$lmmjs_out .= '			});'.PHP_EOL;
						$lmmjs_out .= '			mmp_calculate_total_markers_'.$uid.'();'.PHP_EOL;
						//info: re-draw pagination
						$lmmjs_out .= '			mmp_debounce(mmp_askForMarkersFromPagination(null, 1, "'.$uid.'"),100);'.PHP_EOL;
						$lmmjs_out .= '		}else{'.PHP_EOL;
						$lmmjs_out .= '			mmp_calculate_total_markers_'.$uid.'();'.PHP_EOL;
						//info: re-draw pagination
						$lmmjs_out .= '			mmp_debounce(mmp_askForMarkersFromPagination(null, 1, "'.$uid.'"),100);'.PHP_EOL;
						$lmmjs_out .= '			jQuery.each(filtered_layers_'.$mapname_js.',function(index,l){'.PHP_EOL;
						$lmmjs_out .= '				if(typeof l != "undefined"){'.PHP_EOL;
						$lmmjs_out .= '					if(l.layer_id == layer_id){'.PHP_EOL;
						$lmmjs_out .= '						jQuery.each(filtered_layers_'.$mapname_js.'[index]._layers,function(i,marker){'.PHP_EOL;
						$lmmjs_out .= '							jQuery("#marker_" + marker.feature.properties.markerid ).hide();'.PHP_EOL;
						$lmmjs_out .= '						});'.PHP_EOL;
						$lmmjs_out .= '					}'.PHP_EOL;
						$lmmjs_out .= '				}'.PHP_EOL;
						$lmmjs_out .= '			});'.PHP_EOL;
					}
					$lmmjs_out .= '		}'.PHP_EOL;
					$lmmjs_out .= '	});'.PHP_EOL;
				$lmmjs_out .= '});'.PHP_EOL;
				if($listmarkers == 1){
					//info: add layers to the markers list
					$lmmjs_out .= 'function lmm_add_to_list_'.$mapname_js.'(){'.PHP_EOL;
					$lmmjs_out .= '	if(typeof window["current_called_layer"] != "undefined" && typeof filtered_layers_'.$mapname_js.'[window["current_called_layer"]] != "undefined"){'.PHP_EOL;
					$lmmjs_out .= '		jQuery.each(filtered_layers_'.$mapname_js.'[window["current_called_layer"]]._layers,function(index,marker){'.PHP_EOL;
					$lmmjs_out .= '	 		if(marker.hasOwnProperty("feature")){'.PHP_EOL;
					$lmmjs_out .= '	 			if(marker.feature.hasOwnProperty("properties")){'.PHP_EOL;
					$lmmjs_out .= '	 				if(marker.feature.properties.hasOwnProperty("html_row")){'.PHP_EOL;
					$lmmjs_out .= '						if(jQuery("#marker_" + marker.feature.properties.markerid).length != 0){;'.PHP_EOL;
					$lmmjs_out .= '							jQuery("#marker_" + marker.feature.properties.markerid).show();'.PHP_EOL;
					$lmmjs_out .= '						}else{'.PHP_EOL;
					$lmmjs_out .= '							var markerid = marker.feature.properties.markerid;'.PHP_EOL;
					$lmmjs_out .= '							if(marker._icon!= null){'.PHP_EOL;
					$lmmjs_out .= '								var icon_url = marker._icon.src;'.PHP_EOL;
					$lmmjs_out .= '							}else{'.PHP_EOL;
					$lmmjs_out .= '								var icon_url = "";'.PHP_EOL;
					$lmmjs_out .= '							}'.PHP_EOL;
					$lmmjs_out .= '							var marker_dlink = marker.feature.properties.dlink;'.PHP_EOL;
					$lmmjs_out .= '							var marker_row =  marker.feature.properties.html_row.split("{uid}").join("'.$uid.'");'.PHP_EOL;
					$lmmjs_out .= '							var marker_row_decoded =  jQuery("<textarea />").html(marker_row).text();'.PHP_EOL;
					$lmmjs_out .= '							if(jQuery("#lmm_listmarkers_table_'.$uid.'").length == 0){'.PHP_EOL;
					$lmmjs_out .= '								jQuery("#lmm_'.$uid.'").after("<table style=\"width:'.$layer_marker_list_width.';\" id=\"lmm_listmarkers_table_'.$uid.'\"  class=\"lmm-listmarkers-table\">").append(marker_row_decoded);'.PHP_EOL;
					$lmmjs_out .= '							}else{'.PHP_EOL;
					$lmmjs_out .= '								jQuery("#pagination_row_'.$uid.'").before(marker_row_decoded);'.PHP_EOL;
					$lmmjs_out .= '							}'.PHP_EOL;
					$lmmjs_out .= '						}'.PHP_EOL;
					$lmmjs_out .= '					}'.PHP_EOL;
					$lmmjs_out .= '				}'.PHP_EOL;
					$lmmjs_out .= '		 	 }'.PHP_EOL;
					$lmmjs_out .= '		});'.PHP_EOL;
					$lmmjs_out .= '	}'.PHP_EOL;
					$lmmjs_out .= '}'.PHP_EOL;
					//info: call the function to add the markers of the ajax request.
					$lmmjs_out .= 'lmm_add_to_list_'.$mapname_js.'();'.PHP_EOL;
					//info re-calculate toatal markers when the AJAX call finished.
					$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] === true){'.PHP_EOL;
					$lmmjs_out .= '		mmp_calculate_total_markers_'.$uid.'();'.PHP_EOL;
					$lmmjs_out .= '}'.PHP_EOL;
				}

			}else{
				$lmmjs_out .= $mapname_js.'_geojson_markers.addTo(markercluster_'.$mapname_js.');'.PHP_EOL;
				$lmmjs_out .= $mapname_js . '.addLayer(markercluster_'.$mapname_js.');'.PHP_EOL;
			}
		} else {
			if($filter_details){
				$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] !== true){'.PHP_EOL;
				$lmmjs_out .= '		filtered_layers_'.$mapname_js.'.sort(function(a, b){'.PHP_EOL;
				$lmmjs_out .= '		  	if(active_layers_orderby =="name"){'.PHP_EOL;
				$lmmjs_out .= ' 			b[active_layers_orderby] = b[active_layers_orderby].toLowerCase();'.PHP_EOL;
				$lmmjs_out .= ' 			a[active_layers_orderby] = a[active_layers_orderby].toLowerCase();'.PHP_EOL;
				$lmmjs_out .= ' 	    }'.PHP_EOL;
				$lmmjs_out .= '			if(active_layers_order == "DESC"){'.PHP_EOL;
				$lmmjs_out .= '				if(b[active_layers_orderby] < a[active_layers_orderby])	return -1;'.PHP_EOL;
				$lmmjs_out .= '				else if(b[active_layers_orderby] > a[active_layers_orderby])	return 1;'.PHP_EOL;
				$lmmjs_out .= '				return 0;'.PHP_EOL;
				$lmmjs_out .= '			}else{'.PHP_EOL;
				$lmmjs_out .= '				if(b[active_layers_orderby] < a[active_layers_orderby])	return 1;'.PHP_EOL;
				$lmmjs_out .= '				else if(b[active_layers_orderby] > a[active_layers_orderby])	return -1;'.PHP_EOL;
				$lmmjs_out .= '				return 0;'.PHP_EOL;
				$lmmjs_out .= ' 		}'.PHP_EOL;
				$lmmjs_out .= '		});'.PHP_EOL;
				$lmmjs_out .= ' }'.PHP_EOL;
				//info: add inactive layers to the controlbox
				$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] !== true){'.PHP_EOL;
				$lmmjs_out .= '		jQuery.each(ordered_filter_details_'.$mapname_js.', function(lid, filter){'.PHP_EOL;
				$lmmjs_out .= '			if(filter["status"] == "inactive"){'.PHP_EOL;
				$lmmjs_out .= '				filtered_layers_'.$mapname_js.'[filter.id] = L.layerGroup();'.PHP_EOL;
				$lmmjs_out .= ' 			filtered_layers_'.$mapname_js.'[filter.id]["layer_id"] =filter.id; '.PHP_EOL;
				$lmmjs_out .= ' 			filtered_layers_'.$mapname_js.'[filter.id]["markercount"] = filter_details_'.$mapname_js.'[filter.id].markercount; '.PHP_EOL;
				$lmmjs_out .= '				if(filter_show_markercount != "0"){ var markercount = "&nbsp;&nbsp;<span class=\"mlm-filters-markercount\" title=\"' . esc_attr__('number of markers','lmm') . '\">[ " + filter_details_'.$mapname_js.'[filter.id].markercount + " ]</span>"; }else{ var markercount = ""; } '.PHP_EOL;
				$lmmjs_out .= '				if(filter_details_'.$mapname_js.'[filter.id]["icon"] != "" && filter_show_icon != "0"){  var filter_icon = "<img src=\'"+ filter_details_'.$mapname_js.'[filter.id]["icon"] +"\' />"; }else{ var filter_icon = "";  }'.PHP_EOL;
				$lmmjs_out .= '				if(filter_show_name != "0"){ var filter_name = filter_details_'.$mapname_js.'[filter.id]["name"]; }else{ var filter_name = "";  }'.PHP_EOL;
				$lmmjs_out .= '				overlays_filters_'.$mapname_js.'[ "<span class=\"mlm-filters-icon\">" + filter_icon + "</span><span class=\"mlm-filters-layername\">" + filter_name + "</span>" + markercount ] = L.layerGroup();'.PHP_EOL;
				$lmmjs_out .= '			}'.PHP_EOL;
				$lmmjs_out .= '		});'.PHP_EOL;
				$lmmjs_out .= '}'.PHP_EOL;

				$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] !== true){'.PHP_EOL;
				$lmmjs_out .= '		jQuery.each(filtered_layers_'.$mapname_js.', function(lid, group){ '.PHP_EOL;
				$lmmjs_out .= '				try{ '.PHP_EOL;
				$lmmjs_out .= '					if(filter_show_markercount != "0"){ var markercount = "&nbsp;&nbsp;<span class=\"mlm-filters-markercount\" title=\"' . esc_attr__('number of markers','lmm') . '\">[ " + filter_details_'.$mapname_js.'[group.layer_id].markercount + " ]</span>"; }else{ var markercount = ""; } '.PHP_EOL;
				$lmmjs_out .= '					if(filter_details_'.$mapname_js.'[group.layer_id]["icon"] != "" && filter_show_icon != "0"){  var filter_icon = "<img src=\'"+ filter_details_'.$mapname_js.'[group.layer_id]["icon"] +"\' />"; }else{ var filter_icon = "";  }'.PHP_EOL;
				$lmmjs_out .= '					if(filter_show_name != "0"){ var filter_name = filter_details_'.$mapname_js.'[group.layer_id]["name"]; }else{ var filter_name = "";  }'.PHP_EOL;
				$lmmjs_out .= '					group["markercount"] = filter_details_'.$mapname_js.'[group.layer_id].markercount;'.PHP_EOL;
				$lmmjs_out .= '					overlays_filters_'.$mapname_js.'[ "<span class=\"mlm-filters-icon\">" + filter_icon + "</span><span class=\"mlm-filters-layername\">" + filter_name  + "</span>" +  markercount  ] = group;'.PHP_EOL;
				$lmmjs_out .= '					if(group["status"] == "active"){'.PHP_EOL;
				$lmmjs_out .= '						if(called_layers_'.$mapname_js.'[group.layer_id] == true){'.PHP_EOL;
				$lmmjs_out .= '							'.$mapname_js.'.addLayer(group);'.PHP_EOL;
				$lmmjs_out .= '						}'.PHP_EOL;
				$lmmjs_out .= '						called_layers_'.$mapname_js.'[group.layer_id] = false;'.PHP_EOL;
				$lmmjs_out .= '					}'.PHP_EOL;
				$lmmjs_out .= '				}catch(n){}'.PHP_EOL;
				$lmmjs_out .= '		});'.PHP_EOL;
				$lmmjs_out .= '}'.PHP_EOL;

				if ($filters_collapsed != 'hidden') {
					$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] !== true){'.PHP_EOL;
					$lmmjs_out .= '		L.control.filters(null, overlays_filters_'.$mapname_js.', filters_options_'.$mapname_js.').addTo(' . $mapname_js . ');'.PHP_EOL;
					$lmmjs_out .= '}'.PHP_EOL;
				}
					$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] !== true){'.PHP_EOL;
					$lmmjs_out .= 'jQuery(".lmm-filter").on("click", function(){'.PHP_EOL;
					$lmmjs_out .= '		if(jQuery(this).is(":checked")){'.PHP_EOL;
					$lmmjs_out .= '         var layer_id = jQuery(this).attr("id");'.PHP_EOL;
					$lmmjs_out .= '         if(called_layers_'.$mapname_js.'[layer_id] !== true && active_layers_'.$mapname_js.'.indexOf(layer_id) == -1){'.PHP_EOL;
					$lmmjs_out .= '				xhReq_'.$mapname_js.'.open("GET", "' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?layer="+ layer_id +"&full=no&full_icon_url=no&listmarkers='. $listmarkers .'", true);'.PHP_EOL;
					$lmmjs_out .= '				xhReq_'.$mapname_js.'.send(null);'.PHP_EOL;
					$lmmjs_out .= '				called_layers_'.$mapname_js.'[layer_id] = true;'.PHP_EOL;
					$lmmjs_out .= '				window["mmp_no_controlbox_'.$mapname_js.'"] = true;'.PHP_EOL;
					$lmmjs_out .= '			}'.PHP_EOL;
					$lmmjs_out .= '		}'.PHP_EOL;
					$lmmjs_out .= '		mmp_calculate_total_markers_'.$uid.'();'.PHP_EOL;
					//info: re-draw pagination
					$lmmjs_out .= '		mmp_debounce(mmp_askForMarkersFromPagination(null, 1, "'.$uid.'"),100);'.PHP_EOL;
					$lmmjs_out .= '});'.PHP_EOL;
					$lmmjs_out .= '}'.PHP_EOL;
					if($listmarkers == 1){
						//info: Dynamic markers list.
						$lmmjs_out .= $mapname_js . '.on("layeradd",function(marker){'.PHP_EOL;
							$lmmjs_out .= 'if(typeof marker.layer.feature != "undefined"){'.PHP_EOL;
							$lmmjs_out .= '	 if(marker.hasOwnProperty("feature")){'.PHP_EOL;
							$lmmjs_out .= '	 	if(marker.feature.hasOwnProperty("properties")){'.PHP_EOL;
							$lmmjs_out .= '	 		if(marker.feature.properties.hasOwnProperty("html_row")){'.PHP_EOL;
							$lmmjs_out .= '				if(jQuery("#marker_" + marker.layer.feature.properties.markerid).length != 0){;'.PHP_EOL;
							$lmmjs_out .= '					jQuery("#marker_" + marker.layer.feature.properties.markerid).show();'.PHP_EOL;
							$lmmjs_out .= '				}else{'.PHP_EOL;
							$lmmjs_out .= '					var markerid = marker.layer.feature.properties.markerid;'.PHP_EOL;
							$lmmjs_out .= '					var icon_url = marker.layer._icon.src;'.PHP_EOL;
							$lmmjs_out .= '					var marker_dlink = marker.layer.feature.properties.dlink;'.PHP_EOL;
							$lmmjs_out .= '					var marker_row =  marker.layer.feature.properties.html_row.split("{uid}").join("'.$uid.'");'.PHP_EOL;
							$lmmjs_out .= '					var marker_row_decoded =  jQuery("<textarea />").html(marker_row).text();'.PHP_EOL;
							$lmmjs_out .= '					if(jQuery("#lmm_listmarkers_table_'.$uid.'").length == 0){'.PHP_EOL;
							$lmmjs_out .= '						jQuery("#lmm_'.$uid.'").after("<table style=\"width:'.($listmarkers == 1?$layer_marker_list_width:'100%').';\" id=\"lmm_listmarkers_table_'.$uid.'\"  class=\"lmm-listmarkers-table\">").append(marker_row_decoded);'.PHP_EOL;
							$lmmjs_out .= '					}else{'.PHP_EOL;
							$lmmjs_out .= '						jQuery("#pagination_row_'.$uid.'").before(marker_row_decoded);'.PHP_EOL;
							$lmmjs_out .= '					}'.PHP_EOL;
							$lmmjs_out .= '				}'.PHP_EOL;
							$lmmjs_out .= '	 	 	}'.PHP_EOL;
							$lmmjs_out .= '	  	}'.PHP_EOL;
							$lmmjs_out .= '	  }'.PHP_EOL;
							$lmmjs_out .= '}'.PHP_EOL;
						$lmmjs_out .= '});'.PHP_EOL;
						// TODO: (Search "leafletjs filters and markercluster") Needs working to remove both markers in and out clusters.
						$lmmjs_out .= $mapname_js . '.on("layerremove",function(marker){'.PHP_EOL;
							$lmmjs_out .= 'if(typeof marker.layer.feature != "undefined"){'.PHP_EOL;
							$lmmjs_out .= '		jQuery("#marker_" + marker.layer.feature.properties.markerid ).hide();'.PHP_EOL;
							$lmmjs_out .= '}'.PHP_EOL;
						$lmmjs_out .= '});'.PHP_EOL;
						//info re-calculate total markers when the AJAX call finished.
						$lmmjs_out .= 'if(window["mmp_no_controlbox_'.$mapname_js.'"] === true){'.PHP_EOL;
						$lmmjs_out .= '		mmp_calculate_total_markers_'.$uid.'();'.PHP_EOL;
						$lmmjs_out .= '}'.PHP_EOL;
					}
			}else{
				//info: @TODO Needs testing
				$lmmjs_out .= $mapname_js.'_geojson_markers.addTo(' . $mapname_js . ');'.PHP_EOL;
			}
		}
		//info: calculate total markers
		$lmmjs_out .= 'window["mmp_calculate_total_markers_'.$uid.'"] = function(){'.PHP_EOL;
		$lmmjs_out .= '	var current_total_markers =0;'.PHP_EOL;
		$lmmjs_out .= '	var activated_layers =[];'.PHP_EOL;
		$lmmjs_out .= '	jQuery.each(filtered_layers_'.$mapname_js.',function (i,layer){'.PHP_EOL;
		$lmmjs_out .= '		if(typeof layer != "undefined"){'.PHP_EOL;
		$lmmjs_out .= '			if(layer.hasOwnProperty("_map")){'.PHP_EOL;
		$lmmjs_out .= '				if(layer._map != null){'.PHP_EOL;
		$lmmjs_out .= '					current_total_markers = current_total_markers + parseInt(layer.markercount);'.PHP_EOL;
		$lmmjs_out .= '					activated_layers.push(layer.layer_id);'.PHP_EOL;
		$lmmjs_out .= '				}'.PHP_EOL;
		$lmmjs_out .= '			}'.PHP_EOL;
		$lmmjs_out .= '		}'.PHP_EOL;
		$lmmjs_out .= '	});'.PHP_EOL;
		$lmmjs_out .= '	jQuery(".markercount_'.$uid.'").html(current_total_markers);'.PHP_EOL;
		$lmmjs_out .= '	jQuery("#'.$uid.'_multi_layer_map_list").val(activated_layers.join(","));'.PHP_EOL;
		$lmmjs_out .= '	return current_total_markers;'.PHP_EOL;
		$lmmjs_out .= "}".PHP_EOL;
		//info: reset the map
		if($lmm_options['map_home_button'] == 'true-ondemand'){
			$lmmjs_out .= $mapname_js.'.on("moveend",function(e){'.PHP_EOL;
			$lmmjs_out .= '		jQuery("#leaflet-control-zoomhome-'.$uid.'").show();'.PHP_EOL;
			$lmmjs_out .= '});'.PHP_EOL;
		}
		if ($lmm_options['async_geojson_loading'] == 'enabled') {
			$lmmjs_out .= '} else { if (window.console) { console.error(xhReq_'.$mapname_js.'.statusText); } } } }; xhReq_'.$mapname_js.'.onerror = function xhReq_'.$uid.'(e) { if (window.console) { console.error(xhReq_'.$mapname_js.'.statusText); } }; xhReq_'.$mapname_js.'.send(null);'.PHP_EOL; //info: async 2/2
		}
		if($lmm_options['defaults_layer_listmarkers_order_by'] == 'distance_current_position'){
			if($listmarkers == 1 && isset($lmm_options['defaults_layer_listmarkers_sort_distance_current_pos'])){
				$lmmjs_out .= 'jQuery(document).ready(function(){'.PHP_EOL;
				$lmmjs_out .= '	jQuery("#search_markers_row_'.$uid.' a[data-sortby=\'distance_current_position\']").each(function(){'.PHP_EOL;
				$lmmjs_out .= '		var ev = {};'.PHP_EOL;
				$lmmjs_out .= '		ev.target = this;'.PHP_EOL;
				$lmmjs_out .= '		mmp_askForMarkersFromDropdown(ev);'.PHP_EOL;
				$lmmjs_out .= '	});'.PHP_EOL;
				$lmmjs_out .= '});'.PHP_EOL;
			}
		}
	}

	//info: support for responsive templates - check offsetWidth >0 needed as map resize to 100% with certain tab plugins
	if ( ($mapwidthunit != '%') && ($lmm_options['misc_responsive_support'] == 'enabled') ) {
		$lmmjs_out .= "function lmm_resizeMap_".$mapname_js."() {
	var mapid = document.getElementById('lmm_".$uid."');
	if( (mapid.parentNode.offsetWidth >0) && (mapid.parentNode.offsetWidth < ".$mapwidth.") ) {
		mapid.style.width = '100%';".PHP_EOL;
			if ($listmarkers == 1) {
				$lmmjs_out .= "		document.getElementById('lmm_listmarkers_".$uid."').style.width = '100%';".PHP_EOL;
				$lmmjs_out .= "		document.getElementById('lmm_listmarkers_table_".$uid."').style.width = '100%'; ".PHP_EOL;
			}
			$lmmjs_out .= "\t\t".$mapname_js.".invalidateSize();
	}".PHP_EOL;
	$lmmjs_out .= "}".PHP_EOL;
	$lmmjs_out .= "lmm_resizeMap_".$mapname_js."();".PHP_EOL;
	}
	//info: fix for incomplete map tiles display after device orientation change on mobile devices
	if ( wp_is_mobile() ) {
		$lmmjs_out .= "window.addEventListener('orientationchange', function lmm_invalidateSize_".$mapname_js."() { ".$mapname_js.".invalidateSize();}, false);".PHP_EOL;
	}
	//info: fix for loading maps in bootstrap tabs, jQuery UI tabs & jQuery Mobile
	$lmmjs_out .= "if (typeof jQuery().modal == 'function') {
	jQuery(document).ready(function($) {
		".$mapname_js.".invalidateSize();
		jQuery('a[data-toggle=\"tab\"],.tabbed-area a,.nav-tabs a').on('shown.bs.tab', function (e) {
			".$mapname_js.".invalidateSize();
		});
	});
}
if (typeof jQuery.ui != 'undefined') {
	jQuery(document).ready(function($) {
		".$mapname_js.".invalidateSize();
		jQuery('.ui-tabs').on('tabsactivate', function(event, ui) {
			".$mapname_js.".invalidateSize();
		});
	});
}
if (typeof jQuery.mobile != 'undefined') {
	jQuery(document).bind('pageinit', function( event, data ){
		".$mapname_js.".invalidateSize();
	});".PHP_EOL;
	if ($lmm_options['misc_javascript_header_footer_pro'] == 'footer') {
		$jquery_mobile_warning = (current_user_can( 'manage_options' )) ? '	if (window.console) { console.log("' . esc_attr__('Warning: your site is using jQuery Mobile which can cause Maps Marker Pro maps to break. If this is true on your site, please navigate to Maps Marker (Pro)-Settings / Misc / General settings and set the option -Where to insert Javascript files on frontend?- to -header+inline javascript- to fix this issue.','lmm') . '"); }'.PHP_EOL : '';
		$lmmjs_out .= $jquery_mobile_warning;
	}
	$lmmjs_out .= "}";
	//info: fix for loading maps in woocommmerce tabs / setTimeout needed
    if (is_plugin_active('woocommerce/woocommerce.php') ) {
		$lmmjs_out .= "
jQuery(document).ready(function($) {
	".$mapname_js.".invalidateSize();
	jQuery('.wc-tabs ul.tabs li a, .woocommerce-tabs ul.tabs li a, ul.wc-tabs li a').click(function(){
		setTimeout(function(){
			".$mapname_js.".invalidateSize();
		}, 1);
	});
});";
	}

	//info: fix tabs switching for WPBakery Visual Composer (since 4.7?)
    if (is_plugin_active('js_composer/js_composer.php') ) {
$lmmjs_out .= "
jQuery(document).ready(function($) {
	".$mapname_js.".invalidateSize();
	jQuery('.vc_tta').on('show.vc.tab, show.vc.accordion', function(event, ui) {
		".$mapname_js.".invalidateSize();
	});
});";
	}

	//info: open popup when clicking on icon or marker name in list of markers
	if (!empty($layer) && ($listmarkers == 1)) {
		if ($lmm_options['defaults_layer_listmarkers_link_action'] != 'disabled') {
			$lmmjs_out .= PHP_EOL . "function listmarkers_action_" . $uid . "(id) {".PHP_EOL;
			$lmmjs_out .= "	var newlocation = markerID_" . $mapname_js . "[id].getLatLng();".PHP_EOL;
			$lmmjs_out .= "	" . $mapname_js.".setView(newlocation," . $mapname_js . ".getZoom());".PHP_EOL;
			if($lmm_options['defaults_layer_listmarkers_link_action_zoom'] == 'marker-zoom'){
				if($filter_details && $clustering == '1'){
					$lmmjs_out .= '	' . $mapname_js.".setZoom( markerID_" . $mapname_js . "[id].feature.properties.zoom );" . PHP_EOL;
					$lmmjs_out .= 'window.markercluster_' . $mapname_js . '.disableClustering();'.PHP_EOL;
					$lmmjs_out .= "	" . $mapname_js.".setView(newlocation);".PHP_EOL;
					$lmmjs_out .= '	setTimeout(function(){ try{markercluster_'.$mapname_js.'.getVisibleParent(markerID_'.$mapname_js.'[id]).spiderfy();}catch(e){} }, 300);'.PHP_EOL;
				}else{
					$lmmjs_out .= '	' . $mapname_js.".setZoom( markerID_" . $mapname_js . "[id].feature.properties.zoom );" . PHP_EOL;
				}
			}
			//info: jump to top/map
			$lmmjs_out .= '	window.location.href = "#lmm_'.$uid.'"'.PHP_EOL;
			if ($clustering == '1') {
					if(!$filter_details){
						$lmmjs_out .= "	markercluster_" . $mapname_js . ".clearLayers();".PHP_EOL;
						$lmmjs_out .= $mapname_js . "_geojson_markers.addTo(" . $mapname_js . ");".PHP_EOL;
					}
			}
			if ($lmm_options['defaults_layer_listmarkers_link_action'] == 'setview-open') {
				$lmmjs_out .= "	markerID_" . $mapname_js . "[id].openPopup();".PHP_EOL;
			}
			//info: change url for permalinks (not supported in IE8+9)
			$lmmjs_out .= '	if (history.pushState) {'.PHP_EOL;
			$lmmjs_out .= '		window.history.pushState(null, null, "?highlightmarker="+[id]);'.PHP_EOL;
			$lmmjs_out .= '	}'.PHP_EOL;
			$lmmjs_out .= "}";
		}
	}
	//info: open the highlighted marker (on layer maps only)
	if (!empty($layer)) {
		//info: open highlighted marker via the URL
		if((isset($_GET['highlightmarker']) && is_numeric($_GET['highlightmarker'])) || $highlightmarker != NULL){
			$highlight = (isset($_GET['highlightmarker']) && is_numeric($_GET['highlightmarker']))?$_GET['highlightmarker']:$highlightmarker;
			if($clustering == '1'){
				$lmmjs_out .= PHP_EOL.'function waitForHighlightedMarkers_' . $mapname_js . '(){'.PHP_EOL;
				$lmmjs_out .= '	if(typeof markerID_' . $mapname_js . '['.intval($highlight).'] !== "undefined" && markercluster_'.$mapname_js.'.getVisibleParent(markerID_'.$mapname_js.'['. intval($highlight) .']) != null){'.PHP_EOL;
				$lmmjs_out .= '		var newlocation = markerID_' . $mapname_js . '['.intval($highlight).'].getLatLng();'.PHP_EOL;
				$lmmjs_out .= '		' . $mapname_js.".setView(newlocation);".PHP_EOL;
				$lmmjs_out .= '		try{ markercluster_'.$mapname_js.'.getVisibleParent(markerID_'.$mapname_js.'['. intval($highlight) .']).spiderfy(); }catch(e){}'.PHP_EOL;
				$lmmjs_out .= "		markerID_" . $mapname_js . "[" . intval($highlight) . "].openPopup();".PHP_EOL;
				$lmmjs_out .= '	} else {'.PHP_EOL;
				$lmmjs_out .= '		setTimeout(function(){'.PHP_EOL;
				$lmmjs_out .= '			waitForHighlightedMarkers_' . $mapname_js . '();'.PHP_EOL;
				$lmmjs_out .= '		},250);'.PHP_EOL;
				$lmmjs_out .= '	}'.PHP_EOL;
				$lmmjs_out .= '}'.PHP_EOL;
				$lmmjs_out .= 'waitForHighlightedMarkers_' . $mapname_js . '();';
			}else{
				$lmmjs_out .= PHP_EOL.$mapname_js . '.on("layeradd",function(e){'.PHP_EOL;
				$lmmjs_out .= '	if("feature" in e.layer){'.PHP_EOL;
				$lmmjs_out .= '		if(e.layer.feature.properties.markerid == "'.intval($highlight).'"){'.PHP_EOL;
				$lmmjs_out .= '			' . $mapname_js.'.setView(new L.LatLng(e.layer.feature.geometry.coordinates[1], e.layer.feature.geometry.coordinates[0]));'.PHP_EOL;
				$lmmjs_out .= "			markerID_" . $mapname_js . "[" . intval($highlight) . "].openPopup();".PHP_EOL;
				$lmmjs_out .= '		} '.PHP_EOL;
				$lmmjs_out .= "	}".PHP_EOL;
				$lmmjs_out .= "});";
			}
		}
	}
	//info: reset control
	if($lmm_options['map_home_button'] != 'false'){
		$zoomhome_ondemand = ($lmm_options['map_home_button'] == 'true-ondemand')?'true':'false';
		$reenableclustering = ($filter_details && $clustering == '1')?'true':'false';
		$lmmjs_out .= PHP_EOL.'var reset_control_'.$mapname_js.' = L.Control.zoomHome({position: "'. $lmm_options['map_home_button_position'] .'", mapId: "'.$uid.'", mapnameJS: "'.$mapname_js.'", ondemand: '.$zoomhome_ondemand.', zoomHomeTitle:"'.esc_attr__('reset map view','lmm').'", reenableClustering:"' .$reenableclustering.'" });'.PHP_EOL;
		$lmmjs_out .= 'reset_control_'.$mapname_js.'.addTo('.$mapname_js.');';
	}
	//info: leaflet-hash.js (incompatible with jQuery mobile)
	if ($lmm_options['leaflet_hash_status'] == 'enabled') {
		$lmmjs_out .= PHP_EOL . 'if (typeof jQuery.mobile == "undefined") { var maphash_' . $uid . ' = new L.Hash(' . $mapname_js . '); }'.PHP_EOL;
	}
	//info: center map on popup instead of marker
	if ($lmm_options['defaults_marker_popups_center_map'] == 'true') {
		$lmmjs_out .= PHP_EOL . $mapname_js.".on('popupopen', function(e) {".PHP_EOL;
		$lmmjs_out .= "	var px = ".$mapname_js.".project(e.popup._latlng);".PHP_EOL;
		$lmmjs_out .= "	px.y -= e.popup._container.clientHeight/2;".PHP_EOL;
		$lmmjs_out .= "	".$mapname_js.".panTo(".$mapname_js.".unproject(px),{animate: true});".PHP_EOL;
		$lmmjs_out .= "});".PHP_EOL;
	}
	//info: reset the map
	if($lmm_options['map_home_button'] == 'true-ondemand'){
		$lmmjs_out .= $mapname_js.'.on("moveend",function(e){'.PHP_EOL;
		$lmmjs_out .= '	jQuery("#leaflet-control-zoomhome-'.$uid.'").show();'.PHP_EOL;
		$lmmjs_out .= '});';
	}
	//info: add tab/hidden-div compatibility
	if ($lmm_options['misc_tab_hidden_div_compatibility'] == 'enabled'){
		$lmmjs_out .='var interval_'.$mapname_js.' = setInterval(function () {'.PHP_EOL;
		$lmmjs_out .=' 	if(jQuery('. $mapname_js .'.getContainer()).is(":visible")) {'.PHP_EOL;
		$lmmjs_out .=' 		'. $mapname_js .'.invalidateSize();'.PHP_EOL;
		$lmmjs_out .=' 		clearInterval(interval_'.$mapname_js.');'.PHP_EOL;
		$lmmjs_out .=' 	}'.PHP_EOL;
		$lmmjs_out .='}, 200);';
	}
	//info: show alternative error on gelocation fail for Google Chrome
	if ( ($is_chrome === TRUE) && (is_ssl() === FALSE) ) { 
		$lmmjs_out .= $mapname_js . '.on("locationerror",function(e){'.PHP_EOL;
		$lmmjs_out .= '	alert("' . sprintf(esc_attr__('Error: with Google Chrome version 50 or higher your current location can only be retrieved by secured websites using https - see %1$s for more details!','lmm'), 'https://www.mapsmarker.com/chrome-geolocation-error') . '");'.PHP_EOL;
		$lmmjs_out .= '});';
	}

	//info: add js to footer / part 2/2
	if ($lmm_options['misc_javascript_header_footer_pro'] == 'footer') {
		global $wp_scripts;
		wp_enqueue_script( 'show_map' );
		$wp_scripts->add_data( 'show_map', 'data', $lmmjs_out );
	} else {
		$lmmjs_out .= '</script>'.PHP_EOL;
		$lmmjs_out .= '</div>'; //info: end leaflet_maps_marker_$uid
		$lmm_out = $lmm_out . $lmmjs_out;
	}

	//info: if do_shortcode() within template files is used to show maps or for shortcodes in widgets
	global $wp_styles;
	if ( function_exists( 'is_rtl' ) && is_rtl() ) {
		$css_enqueue_handle = 'leafletmapsmarker-rtl';
	} else {
		$css_enqueue_handle = 'leafletmapsmarker';
	}
	if (!wp_style_is( $css_enqueue_handle, 'done' )) {
		wp_enqueue_style($css_enqueue_handle);
		//info: override max image width in popups
		$lmm_custom_css = ".leaflet-popup-content img { " . htmlspecialchars($lmm_options['defaults_marker_popups_image_css']) . " } .marker-cluster-small { background-color: " . htmlspecialchars($lmm_options['clustering_color_small']) . "; } .marker-cluster-small div { background-color: " . htmlspecialchars($lmm_options['clustering_color_small_inner']) . "; color: " . htmlspecialchars($lmm_options['clustering_color_small_text']) . "; } .marker-cluster-medium { background-color: " . htmlspecialchars($lmm_options['clustering_color_medium']) . "; } .marker-cluster-medium div { background-color: " . htmlspecialchars($lmm_options['clustering_color_medium_inner']) . ";  color: " . htmlspecialchars($lmm_options['clustering_color_medium_text']) . "; } .marker-cluster-large { background-color: " . htmlspecialchars($lmm_options['clustering_color_large']) . "; } .marker-cluster-large div { background-color: " . htmlspecialchars($lmm_options['clustering_color_large_inner']) . ";  color: " . htmlspecialchars($lmm_options['clustering_color_large_text']) . "; }";
		wp_add_inline_style($css_enqueue_handle,$lmm_custom_css);
	}
  } //info: end (!is_feed())
}
?>