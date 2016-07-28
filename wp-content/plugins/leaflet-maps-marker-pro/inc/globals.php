<?php
/**
* Advanced sort array.
*
* @param array $array The array to sort
* @param string array key to sort by
* @param integer SORT_ASC or SORT_DESC
* @return array
* @used in: /inc/showmap.php, leaflet-fullscreen.php, leaflet-layer.php
*/
if(!function_exists('array_sort')){
	function array_sort($array, $on, $order=SORT_ASC)
		{
		    $new_array = array();
		    $sortable_array = array();

		    if (count($array) > 0) {
		        foreach ($array as $k => $v) {
		            if (is_array($v)) {
		                foreach ($v as $k2 => $v2) {
		                    if ($k2 == $on) {
		                        $sortable_array[$k] = $v2;
		                    }
		                }
		            } else {
		                $sortable_array[$k] = $v;
		            }
		        }

		        switch ($order) {
		            case SORT_ASC:
		                asort($sortable_array);
		            break;
		            case SORT_DESC:
		                arsort($sortable_array);
		            break;
		        }

		        foreach ($sortable_array as $k => $v) {
		            $new_array[$k] = $array[$k];
		        }
		    }

		    return $new_array;
		}
}
/**
* Render markers list pagination
*
* @param string $uid The id of the map
* @param integer $markercount Total number of markers
* @param boolean $multi_layer_map whether the map is multi-layer or not
* @param string $multi_layer_map_list the sub-layers of the mlm layer
* @param integer $order_by SORT_ASC or SORT_DESC
* @return string
* @used in: /inc/showmap.php, leaflet-layer.php
*/
if(!function_exists('lmm_get_markers_pagination')){
	function lmm_get_markers_pagination($uid, $markercount, $multi_layer_map, $multi_layer_map_list, $order_by){
		//info:  get pagination
		$lmm_options = get_option('leafletmapsmarker_options');
		$order = $lmm_options[ 'defaults_layer_listmarkers_sort_order' ];

		$pager = '<div class="tablenav">';
		if ($markercount > intval($lmm_options[ 'defaults_layer_listmarkers_limit' ])) {
		  $maxpage = intval(ceil($markercount / intval($lmm_options[ 'defaults_layer_listmarkers_limit' ])));
		  if ($maxpage > 1) {
		    $pager .= '<div id="pagination_'.$uid.'" class="tablenav-pages">';
			$pager .= '<span class="markercount_'.$uid.'">'. $markercount.'</span> '.__('markers','lmm');
			$pager .= '<div class="lmm-per-page">';
			$pager .= '<input type="text" id="markers_per_page_'.$uid.'" class="lmm-per-page-input" value="'.intval($lmm_options[ "defaults_layer_listmarkers_limit" ]).'" data-mapid="'.$uid.'" />';
			$pager .= ' '.__('per page','lmm');
			$pager .= '</div>';
			$pager .= '<div class="lmm-pages">';
			$pager .= '<form style="display:inline;" method="POST" action="">' . __('page','lmm') . ' ';
			$pager .= '<input type="hidden" id="'.$uid.'_orderby" name="orderby" value="' . $order_by . '" />';
			$pager .= '<input type="hidden" id="'.$uid.'_order" name="order" value="' . $order . '" />';
			$pager .= '<input type="hidden" id="'.$uid.'_multi_layer_map" name="multi_layer_map" value="' . $multi_layer_map . '" />';
			$pager .= '<input type="hidden" id="'.$uid.'_multi_layer_map_list" name="multi_layer_map_list" value="' . $multi_layer_map_list. '" />';
			$pager .= '<input type="hidden" id="'.$uid.'_markercount" name="markercount" value="' . $markercount. '" />';
		    $radius = 1;
		    $pagenum = 1;
		    if ($pagenum > (2 + $radius * 2)) {
		      foreach (range(1, 1 + $radius) as $num)
		        $pager .= '<a href="#" class="first-page" data-mapid="'.$uid.'">'.$num.'</a>';
		      	$pager .= '...';
		      foreach (range($pagenum - $radius, $pagenum - 1) as $num)
		        $pager .= '<a href="#" class="first-page" data-mapid="'.$uid.'">'.$num.'</a>';
		    }
		    else
		      if ($pagenum > 1)
		        foreach (range(1, $pagenum - 1) as $num)
		          $pager .= '<a href="#" class="first-page" data-mapid="'.$uid.'">'.$num.'</a>';
		    $pager .= '<a href="#" class="first-page current-page">' . $pagenum . '</a>';
		    if (($maxpage - $pagenum) >= (2 + $radius * 2)) {
		      foreach (range($pagenum + 1, $pagenum + $radius) as $num)
		        $pager .= '<a href="#" class="first-page" data-mapid="'.$uid.'">'.$num.'</a>';
		      $pager .= '...';
		      foreach (range($maxpage - $radius, $maxpage) as $num)
		        $pager .= '<a href="#" class="first-page" data-mapid="'.$uid.'">'.$num.'</a>';
		    }
		    else
		      if ($pagenum < $maxpage)
		        foreach (range($pagenum + 1, $maxpage) as $num)
		          $pager .= '<a href="#" class="first-page" data-mapid="'.$uid.'">'.$num.'</a>';
		    $pager .= '</div></form></div>';
		  }
		}
		$pager .= '</div>';
		return $pager;
	}
}
/**
* Convert the order string to a readable text
*
* @param integer $order_by SORT_ASC or SORT_DESC
* @return string
* @used in: inc/ajax-actions-frontend.php, inc/showmap.php
*/
if(!function_exists('mmp_get_order_text')){
	function mmp_get_order_text( $order_by ){
		switch ($order_by) {
			case 'm.id':
				return __('ID','lmm');
				break;
			case 'm.markername':
				return __('marker name','lmm');
				break;
			case 'm.popuptext':
				return __('popuptext','lmm');
				break;
			case 'm.icon':
				return __('icon','lmm');
				break;
			case 'm.createdby':
				return __('created by','lmm');
				break;
			case 'm.createdon':
				return __('created on','lmm');
				break;
			case 'm.updatedby':
				return __('updated by','lmm');
				break;
			case 'm.updatedon':
				return __('updated on','lmm');
				break;
			case 'm.layer':
				return __('layer ID','lmm');
				break;
			case 'm.address':
				return __('address','lmm');
				break;
			case 'm.kml_timestamp':
				return __('KML timestamp','lmm');
				break;
			case 'distance_layer_center':
				return __('distance from layer center','lmm');
				break;
			case 'distance_current_position':
				return __('distance from current position','lmm');
				break;
			default:
				return '';
				break;
		}
	}
}
/**
* Get the HTML row of a marker.
*
* @param object $row The object of the marker
* @return string the html string of the row
* @used in: leaflet-geojson.php
*/
if(!function_exists('lmm_get_marker_list_row')){
	function lmm_get_marker_list_row( $row ){
		$lmm_options = get_option( 'leafletmapsmarker_options' );
		$lmm_out = '';
		if ( $lmm_options['defaults_marker_custom_icon_url_dir'] == 'no' ) {
			$defaults_marker_icon_url = LEAFLET_PLUGIN_ICONS_URL;
		} else {
			$defaults_marker_icon_url = htmlspecialchars($lmm_options['defaults_marker_icon_url']);
		}
		if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_icon' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_icon' ] == 1 ) ) {
				$lmm_out .= '<tr id="marker_'.$row['mid'].'"><td class="lmm-listmarkers-icon">';
				if ($lmm_options['defaults_layer_listmarkers_link_action'] != 'disabled') {
					$listmarkers_href_a = '<a href="javascript:void(0);" onclick="javascript:listmarkers_action_' . '{uid}' . '(' . $row['mid'] . ')">';
					$listmarkers_href_b = '</a>';
				} else {
					$listmarkers_href_a = '';
					$listmarkers_href_b = '';
				}
				if ($lmm_options['defaults_marker_popups_add_markername'] == 'true') {
					$markername_on_hover = 'title="' . stripslashes(htmlspecialchars($row['mmarkername'])) . '"';
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
				$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?marker=' . $row['mid'] . '" style="text-decoration:none;" title="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" alt="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_kml' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_kml' ] == 1 ) ) {
				$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-kml.php?marker=' . $row['mid'] . '&amp;name=' . $lmm_options[ 'misc_kml' ] . '" style="text-decoration:none;" title="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" alt="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_qr_code' ] == 1 ) ) {
				$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-qr.php?marker=' . $row['mid'] . '" target="_blank" title="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '" rel="nofollow"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-qr-code.png" width="14" height="14" alt="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_geojson' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_geojson' ] == 1 ) ) {
				$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?marker=' . $row['mid'] . '&amp;callback=jsonp&amp;full=yes&amp;full_icon_url=yes" style="text-decoration:none;" title="' . esc_attr__('Export as GeoJSON','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-json.png" width="14" height="14" alt="' . esc_attr__('Export as GeoJSON','lmm') . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_georss' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_georss' ] == 1 ) ) {
				$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-georss.php?marker=' . $row['mid'] . '" style="text-decoration:none;" title="' . esc_attr__('Export as GeoRSS','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-georss.png" width="14" height="14" alt="' . esc_attr__('Export as GeoRSS','lmm') . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_wikitude' ] == 1 ) ) {
				$lmm_out .= '&nbsp;<a href="' . LEAFLET_PLUGIN_URL . 'leaflet-wikitude.php?marker=' . $row['mid'] . '" style="text-decoration:none;" title="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-wikitude.png" width="14" height="14" alt="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '" class="lmm-panel-api-images" /></a>';
			}
			if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_distance' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_show_distance' ] == 1 ) ) {
				if ( ($lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance.km') || ($lmm_options['defaults_layer_listmarkers_show_distance_unit'] == 'km') ) { //info: needed fallback as setting name has changed
					$lmm_out .= '<br/><br/><span class="lmm-distance" title="' . esc_attr__('calculated from map center','lmm') . '">' . __('distance', 'lmm').': ' . round($row['distance'], intval($lmm_options[ 'defaults_layer_listmarkers_show_distance_precision' ])) . ' ' . __('km','lmm') . '</span>';
				} else if ( ($lmm_options[ 'defaults_layer_listmarkers_order_by' ] == 'distance.mile') || ($lmm_options['defaults_layer_listmarkers_show_distance_unit'] == 'mile') ) {
					$lmm_out .= '<br/><br/><span class="lmm-distance" title="' . esc_attr__('calculated from map center','lmm') . '">' . __('distance', 'lmm').': ' . round($row['distance'], intval($lmm_options[ 'defaults_layer_listmarkers_show_distance_precision' ])) . ' ' . __('miles','lmm') . '</span>';
				}
			}
			$lmm_out .= '</div>';
			if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_markername' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_markername' ] == 1 ) ) {
				if ($lmm_options['defaults_layer_listmarkers_link_action'] != 'disabled') {
					$lmm_out .= '<span class="lmm-listmarkers-markername"><a title="' . esc_attr__('show marker on map','lmm') . '" href="javascript:void(0);" onclick="javascript:listmarkers_action_' . '{uid}' . '(' . $row['mid'] . ')">' . wp_specialchars_decode(stripslashes(esc_js(preg_replace('/[\x00-\x1F\x7F]/', '', $row['mmarkername'])))) . '</a></span>';
				} else {
					$lmm_out .= '<span class="lmm-listmarkers-markername">' . wp_specialchars_decode(stripslashes(esc_js(preg_replace('/[\x00-\x1F\x7F]/', '', $row['mmarkername'])))) . '</span>';
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
				//info: remove control chars & sanitize output
				$mpopuptext_prepare = preg_replace($sanitize_popuptext_from, $sanitize_popuptext_to,
								stripslashes(
									str_replace("\\\\","/",
									str_replace('"', '\'',
									preg_replace('/[\x00-\x1F\x7F]/', '',
									preg_replace( '/(\015\012)|(\015)|(\012)/','<br />',
									$row['mpopuptext']
								)))))
							);
				//info: second run for do_shortcode() -> do not replace new lines/brs with <br/>!
				$mpopuptext = preg_replace($sanitize_popuptext_from, $sanitize_popuptext_to,
								stripslashes(
									str_replace("\\\\","/",
									str_replace('"', '\'',
									preg_replace('/[\x00-\x1F\x7F]/', '',
									preg_replace( '/(\015\012)|(\015)|(\012)/','',
									do_shortcode($mpopuptext_prepare)
								)))))
							);
				$popuptext_sanitized = $mpopuptext;
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
			return $lmm_out;
	}
}
/**
* Geocode an address
*
* @param object $row The object of the marker
* @return latitude+longitude+address value
* @used in: leaflet-api.php, /inc/import-export/start.php, class-mmpapi.php
*/
if(!function_exists('lmm_getLatLng')){
	function lmm_getLatLng($address) {
		global $locale;
		$lmm_options = get_option( 'leafletmapsmarker_options' );
		$protocol_handler = (substr($locale, 0, 2) == 'zh') ? 'http' : 'https'; //info: conditional ssl loading for Google js (performance issues in China)
		$address_to_geocode = lmm_accent_folding($address);
		//info: Google Maps for Business parameters
		if ($_POST['gmapsbusiness-client'] == NULL)  { $gmapsbusiness_client = ''; } else { $gmapsbusiness_client = '&client=' . $_POST['gmapsbusiness-client']; }
		if ($_POST['gmapsbusiness-signature'] == NULL) { $gmapsbusiness_signature = ''; } else { $gmapsbusiness_signature = '&signature=' . $_POST['gmapsbusiness-signature']; }
		if ($_POST['gmapsbusiness-channel'] == NULL) { $gmapsbusiness_channel = ''; } else { $gmapsbusiness_channel = '&channel=' . $_POST['gmapsbusiness-channel']; }
		$google_api_key = (isset($lmm_options['google_maps_api_key']) && $lmm_options['google_maps_api_key']!='')?'&key=' . trim($lmm_options['google_maps_api_key']):'';
		$url = $protocol_handler . '://maps.googleapis.com/maps/api/geocode/xml?address=' . urlencode($address_to_geocode) . $gmapsbusiness_client . $gmapsbusiness_signature . $gmapsbusiness_channel . $google_api_key;
		$xml_raw = wp_remote_get( $url, array( 'sslverify' => false, 'timeout' => 10 ) );
		$xml = simplexml_load_string($xml_raw['body']);
	
		$response = array();
		$statusCode = $xml->status;
		$error_message = $xml->error_message;
	
		if ( ($statusCode != false) && ($statusCode != NULL) ) {
			if ($statusCode == 'OK') {
				$latDom = $xml->result[0]->geometry->location->lat;
				$lonDom = $xml->result[0]->geometry->location->lng;
				$addressDom = $xml->result[0]->formatted_address;
				if ($latDom != NULL) {
					$response = array (
						'success' 	=> true,
						'lat' 		=> $latDom,
						'lon' 		=> $lonDom,
						'address'	=> $addressDom
					);
					return $response;
				}
			} else if ($statusCode == 'OVER_QUERY_LIMIT') { //info: wait 1.5sec and try again once - with Google Maps API key added
				usleep(1500000);
				$xml_raw = wp_remote_get( $url, array( 'sslverify' => false, 'timeout' => 10 ) );
				$xml = simplexml_load_string($xml_raw['body']);
	
				$response = array();
				$statusCode = $xml->status;
				$error_message = $xml->error_message;
	
				if ( ($statusCode != false) && ($statusCode != NULL) ) {
					if ($statusCode == 'OK') {
						$latDom = $xml->result[0]->geometry->location->lat;
						$lonDom = $xml->result[0]->geometry->location->lng;
						$addressDom = $xml->result[0]->formatted_address;
						if ($latDom != NULL) {
							$response = array (
								'success' 	=> true,
								'lat' 		=> $latDom,
								'lon' 		=> $lonDom,
								'address'	=> $addressDom
							);
							return $response;
						}
					}
				}
			}else if($statusCode == 'REQUEST_DENIED'){ //info: if the API Key has restricted referers, try again without API key
				$url = $protocol_handler . '://maps.googleapis.com/maps/api/geocode/xml?address=' . urlencode($address_to_geocode) . $gmapsbusiness_client . $gmapsbusiness_signature . $gmapsbusiness_channel;
				$xml_raw = wp_remote_get( $url, array( 'sslverify' => false, 'timeout' => 10 ) );
				$xml = simplexml_load_string($xml_raw['body']);
	
				$response = array();
				$statusCode = $xml->status;
				$error_message = $xml->error_message;
	
				if ( ($statusCode != false) && ($statusCode != NULL) ) {
					if ($statusCode == 'OK') {
						$latDom = $xml->result[0]->geometry->location->lat;
						$lonDom = $xml->result[0]->geometry->location->lng;
						$addressDom = $xml->result[0]->formatted_address;
						if ($latDom != NULL) {
							$response = array (
								'success' 	=> true,
								'lat' 		=> $latDom,
								'lon' 		=> $lonDom,
								'address'	=> $addressDom
							);
							return $response;
						}
					} else if ($statusCode == 'OVER_QUERY_LIMIT') { //info: if the API Key has restricted referers, wait 1.5sec and try again once - without Google Maps API key added
						usleep(1500000);
						$xml_raw = wp_remote_get( $url, array( 'sslverify' => false, 'timeout' => 10 ) );
						$xml = simplexml_load_string($xml_raw['body']);
						
						$response = array();
						$statusCode = $xml->status;
						$error_message = $xml->error_message;
	
						if ( ($statusCode != false) && ($statusCode != NULL) ) {
							if ($statusCode == 'OK') {
								$latDom = $xml->result[0]->geometry->location->lat;
								$lonDom = $xml->result[0]->geometry->location->lng;
								$addressDom = $xml->result[0]->formatted_address;
								if ($latDom != NULL) {
									$response = array (
										'success' 	=> true,
										'lat' 		=> $latDom,
										'lon' 		=> $lonDom,
										'address'	=> $addressDom
									);
									return $response;
								}
							}
						}					
					}
				}
			} 
		}
		$response = array (
			'success' => false,
			'message' => $statusCode . ' - ' . $error_message
		);
		return $response;
	}
}