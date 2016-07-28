/*
* Async Google Maps API loading
*/
L.DeferredLayer = L.LayerGroup.extend({
	options: {
		js: [],
		init: null
	},

	_script_cache: {},

	initialize: function(options) {
		L.Util.setOptions(this, options);
		L.LayerGroup.prototype.initialize.apply(this);
		this._loaded = false;
	},

	onAdd: function(map) {
		L.LayerGroup.prototype.onAdd.apply(this, [map]);
		if (this._loaded) return;
		var loaded = function() {
			this._loaded = true;
			var l = this.options.init();
			if (l)
				this.addLayer(l);
		};
		this._loadScripts(this.options.js.reverse(), L.Util.bind(loaded, this));
	},

	_loadScripts: function(scripts, cb, args) {
		if (!scripts || scripts.length === 0)
			return cb(args);
		var _this = this, s = scripts.pop(), c;
		c = this._script_cache[s];
		if (c === undefined) {
			c = {url: s, wait: []};
			var script = document.createElement('script');
			script.src = s;
			script.type = 'text/javascript';
			script.onload = function () {
				c.e.readyState = 'completed';
				var i = 0;
				for (i = 0; i < c.wait.length; i++)
					c.wait[i]();
			};
			c.e = script;
			document.getElementsByTagName('head')[0].appendChild(script);
		}
		function _cb() { _this._loadScripts(scripts, cb, args); }
		c.wait.push(_cb);
		if (c.e.readyState === 'completed')
			_cb();
		this._script_cache[s] = c;
	}
});

/*
 * Google Maps plugin - http://psha.org.ru/b/leaflet-plugins.html
*/
if (mapsmarkerjspro.google_maps_api_status == 'enabled') {

	if ( (mapsmarkerjspro.google_deferred_loading == 'disabled') || (mapsmarkerjspro.google_script_loading == 'backend') ) {
		google.load('maps', '3', {'other_params':mapsmarkerjspro.googlemaps_language+mapsmarkerjspro.googlemaps_base_domain+mapsmarkerjspro.googlemaps_libraries+'&key='+mapsmarkerjspro.google_maps_api_key});
	}

	L.Google = L.Class.extend({
		includes: L.Mixin.Events,

		options: {
			minZoom: 0,
			maxZoom: mapsmarkerjspro.maxzoom,
			maxNativeZoom: 21,
			tileSize: 256,
			subdomains: 'abc',
			errorTileUrl: '',
			attribution: '',
			opacity: 1,
			continuousWorld: false,
			noWrap: false,
			mapOptions: {
				backgroundColor: '#F6F6F6'
			}
		},

		// Possible types: SATELLITE, ROADMAP, HYBRID, TERRAIN
		initialize: function (type, options) {
			L.Util.setOptions(this, options);

			this._ready = google.maps.Map !== undefined;
			if (!this._ready) L.Google.asyncWait.push(this);

			this._type = type || 'SATELLITE';
		},

		onAdd: function (map, insertAtTheBottom) {
			this._map = map;
			this._insertAtTheBottom = insertAtTheBottom;

			// create a container div for tiles
			this._initContainer();
			this._initMapObject();

			// set up events
			map.on('viewreset', this._reset, this);

			this._limitedUpdate = L.Util.limitExecByInterval(this._update, 0, this);
			map.on('move drag', this._update, this);
			map.on('zoomanim', this._handleZoomAnim, this);

			map._controlCorners['bottomright'].style.marginBottom = '15px';
			map._controlCorners['bottomleft'].style.marginBottom = '21px';

			this._reset();
			this._update();
			L.polyline([[0, 0], ]).addTo(this._map); //info: temp fix for https://github.com/shramov/leaflet-plugins/issues/156 - check with new leaflet-version!
		},

		onRemove: function (map) {
			map._container.removeChild(this._container);

			map.off('viewreset', this._reset, this);

			map.off('move drag', this._update, this);

			map.off('zoomanim', this._handleZoomAnim, this);

			map._controlCorners.bottomright.style.marginBottom = '0em';
		},

		getAttribution: function () {
			return this.options.attribution;
		},

		setOpacity: function (opacity) {
			this.options.opacity = opacity;
			if (opacity < 1) {
				L.DomUtil.setOpacity(this._container, opacity);
			}
		},

		setElementSize: function (e, size) {
			e.style.width = size.x + 'px';
			e.style.height = size.y + 'px';
		},

		_initContainer: function () {
			var tilePane = this._map._container,
				first = tilePane.firstChild;

			if (!this._container) {
				this._container = L.DomUtil.create('div', 'leaflet-google-layer leaflet-top leaflet-left');
				this._container.id = '_GMapContainer_' + L.Util.stamp(this);
				this._container.style.zIndex = 'auto';
			}

			tilePane.insertBefore(this._container, first);

			this.setOpacity(this.options.opacity);
			this.setElementSize(this._container, this._map.getSize());
		},

		_initMapObject: function () {
			if (!this._ready) return;
			this._google_center = new google.maps.LatLng(0, 0);
			var map = new google.maps.Map(this._container, {
				center: this._google_center,
				zoom: 0,
				tilt: 0,
				mapTypeId: google.maps.MapTypeId[this._type],
				disableDefaultUI: true,
				keyboardShortcuts: false,
				draggable: false,
				disableDoubleClickZoom: true,
				scrollwheel: false,
				streetViewControl: false,
				//styles: this.options.mapOptions.styles, info: not sure if needed as code 3 lines below is added
				backgroundColor: this.options.mapOptions.backgroundColor
			});
			if (mapsmarkerjspro.google_styling_json != 'disabled') {
				var styles = eval(mapsmarkerjspro.google_styling_json);
				map.setOptions({styles: styles});
			}
			var _this = this;
			this._reposition = google.maps.event.addListenerOnce(map, 'center_changed',
				function () { _this.onReposition(); });
			this._google = map;

			google.maps.event.addListenerOnce(map, 'idle',
				function () { _this._checkZoomLevels(); });
			google.maps.event.addListenerOnce(map, 'tilesloaded',
				function () { _this.fire('load'); });
			//Reporting that map-object was initialized.
			this.fire('MapObjectInitialized', {mapObject: map});
		},

		_checkZoomLevels: function () {
			//setting the zoom level on the Google map may result in a different zoom level than the one requested
			//(it won't go beyond the level for which they have data).
			// verify and make sure the zoom levels on both Leaflet and Google maps are consistent
			if ((this._map.getZoom() !== undefined) && (this._google.getZoom() !== this._map.getZoom())) {
				//zoom levels are out of sync. Set the leaflet zoom level to match the google one
				this._map.setZoom(this._google.getZoom());
			}
		},

		_reset: function () {
			this._initContainer();
		},

		_update: function () {
			if (!this._google) return;
			this._resize();

			var center = this._map.getCenter();
			var _center = new google.maps.LatLng(center.lat, center.lng);

			this._google.setCenter(_center);
			if (this._map.getZoom() !== undefined)
				this._google.setZoom(Math.round(this._map.getZoom()));

			this._checkZoomLevels();
		},

		_resize: function () {
			var size = this._map.getSize();
			if (this._container.style.width === size.x &&
					this._container.style.height === size.y)
				return;
			this.setElementSize(this._container, size);
			this.onReposition();
		},


		_handleZoomAnim: function (e) {
			var center = e.center;
			var _center = new google.maps.LatLng(center.lat, center.lng);

			this._google.setCenter(_center);
			this._google.setZoom(Math.round(e.zoom));
		},


		onReposition: function () {
			if (!this._google) return;
			google.maps.event.trigger(this._google, 'resize');
		}
	});

	L.Google.asyncWait = [];
	L.Google.asyncInitialize = function () {
		var i;
		for (i = 0; i < L.Google.asyncWait.length; i++) {
			var o = L.Google.asyncWait[i];
			o._ready = true;
			if (o._container) {
				o._initMapObject();
				o._update();
			}
		}
		L.Google.asyncWait = [];
	};
};

/*
 * bing maps plugins - http://psha.org.ru/b/leaflet-plugins.html
*/
L.BingLayer = L.TileLayer.extend({
    options: {
        subdomains: [0, 1, 2, 3],
        type: this.type,
        attribution: '<a href="https://www.bing.com/maps/" target="_blank">Bing Maps</a>',
        culture: mapsmarkerjspro.bing_culture
    },
    initialize: function (key, options) {
        L.Util.setOptions(this, options);
        this._key = key;
        this._url = null;
		this._providers = [];
        this.metaRequested = false;
    },
    tile2quad: function (x, y, z) {
        var quad = '';
        for (var i = z; i > 0; i--) {
            var digit = 0;
            var mask = 1 << (i - 1);
            if ((x & mask) !== 0) digit += 1;
            if ((y & mask) !== 0) digit += 2;
            quad = quad + digit
        }
        return quad
    },
    getTileUrl: function (tilePoint) {
        var zoom = this._getZoomForUrl();
        var subdomains = this.options.subdomains,
            s = this.options.subdomains[Math.abs((tilePoint.x + tilePoint.y) % subdomains.length)];
        return this._url.replace('{subdomain}', s)
			.replace('{quadkey}', this.tile2quad(tilePoint.x, tilePoint.y, zoom))
			.replace('{culture}', this.options.culture)
    },
    loadMetadata: function () {
		if (this.metaRequested) return;
		this.metaRequested = true;
        var _this = this;
        var cbid = '_bing_metadata_' + L.Util.stamp(this);
        window[cbid] = function (meta) {
            window[cbid] = undefined;
            var e = document.getElementById(cbid);
            e.parentNode.removeChild(e);
            if (meta.errorDetails) {
                throw new Error(meta.errorDetails);
                return
            }
            _this.initMetadata(meta);
        };
		var urlScheme = (document.location.protocol === 'file:') ? 'http' : document.location.protocol.slice(0, -1);
		var url = urlScheme + '://dev.virtualearth.net/REST/v1/Imagery/Metadata/'
		 					+ this.options.type + '?include=ImageryProviders&jsonp=' + cbid +
							'&key=' + this._key + '&UriScheme=' + urlScheme;
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = url;
        script.id = cbid;
        document.getElementsByTagName('head')[0].appendChild(script)
    },
    initMetadata: function (meta) {
		var r = meta.resourceSets[0].resources[0];
        this.options.subdomains = r.imageUrlSubdomains;
        this._url = r.imageUrl;
		if (r.imageryProviders) {
        for (var i = 0; i < r.imageryProviders.length; i++) {
            var p = r.imageryProviders[i];
            for (var j = 0; j < p.coverageAreas.length; j++) {
                var c = p.coverageAreas[j];
                var coverage = {
                    zoomMin: c.zoomMin,
                    zoomMax: c.zoomMax,
                    active: false
                };
                var bounds = new L.LatLngBounds(new L.LatLng(c.bbox[0] + 0.01, c.bbox[1] + 0.01), new L.LatLng(c.bbox[2] - 0.01, c.bbox[3] - 0.01));
                coverage.bounds = bounds;
                coverage.attrib = p.attribution;
                this._providers.push(coverage)
            }
        }
		}
        this._update()
    },
    _update: function () {
        if (this._url === null || !this._map) return;
        this._update_attribution();
        L.TileLayer.prototype._update.apply(this, [])
    },
    _update_attribution: function () {
        var bounds = this._map.getBounds();
        var zoom = this._map.getZoom();
        for (var i = 0; i < this._providers.length; i++) {
            var p = this._providers[i];
            if ((zoom <= p.zoomMax && zoom >= p.zoomMin) && bounds.intersects(p.bounds)) {
                if (!p.active) {
                    if (this._map.attributionControl) {
                        this._map.attributionControl.addAttribution(p.attrib);
                    }
                }
                p.active = true;
            } else {
                if (p.active) {
                    if (this._map.attributionControl) {
                        this._map.attributionControl.removeAttribution(p.attrib);
                    }
                }
                p.active = false;
            }
        }
    },
	onAdd: function (map) {
		this.loadMetadata();
		L.TileLayer.prototype.onAdd.apply(this, [map]);
	},
    onRemove: function (map) {
        for (var i = 0; i < this._providers.length; i++) {
            var p = this._providers[i];
            if (p.active) {
                if (this._map.attributionControl) {
                    this._map.attributionControl.removeAttribution(p.attrib);
                }
                p.active = false;
            }
        }
		L.TileLayer.prototype.onRemove.apply(this, [map]);
    }
});
L.bingLayer = function(key, options) {
    return new L.BingLayer(key, options);
};

/*
 Leaflet.markercluster, Provides Beautiful Animated Marker Clustering functionality for Leaflet, a JS library for interactive maps.
 https://github.com/Leaflet/Leaflet.markercluster (v0.4.0-hotfix1, 27/03/15)
 (c) 2012-2015, Dave Leaver, smartrak
*/
(function (window, document, undefined) {/*
 * L.MarkerClusterGroup extends L.FeatureGroup by clustering the markers contained within
 */

L.MarkerClusterGroup = L.FeatureGroup.extend({

	options: {
		maxClusterRadius: 80, //A cluster will cover at most this many pixels from its center
		iconCreateFunction: null,

		spiderfyOnMaxZoom: true,
		showCoverageOnHover: true,
		zoomToBoundsOnClick: true,
		singleMarkerMode: false,

		disableClusteringAtZoom: null,

		// Setting this to false prevents the removal of any clusters outside of the viewpoint, which
		// is the default behaviour for performance reasons.
		removeOutsideVisibleBounds: true,

		//Whether to animate adding markers after adding the MarkerClusterGroup to the map
		// If you are adding individual markers set to true, if adding bulk markers leave false for massive performance gains.
		animateAddingMarkers: false,

		//Increase to increase the distance away that spiderfied markers appear from the center
		spiderfyDistanceMultiplier: 1,

		// Make it possible to specify a polyline options on a spider leg
		spiderLegPolylineOptions: { weight: 1.5, color: '#222' },

		// When bulk adding layers, adds markers in chunks. Means addLayers may not add all the layers in the call, others will be loaded during setTimeouts
		chunkedLoading: false,
		chunkInterval: 200, // process markers for a maximum of ~ n milliseconds (then trigger the chunkProgress callback)
		chunkDelay: 50, // at the end of each interval, give n milliseconds back to system/browser
		chunkProgress: null, // progress callback: function(processed, total, elapsed) (e.g. for a progress indicator)

		//Options to pass to the L.Polygon constructor
		polygonOptions: {}
	},

	initialize: function (options) {
		L.Util.setOptions(this, options);
		if (!this.options.iconCreateFunction) {
			this.options.iconCreateFunction = this._defaultIconCreateFunction;
		}

		this._featureGroup = L.featureGroup();
		this._featureGroup.on(L.FeatureGroup.EVENTS, this._propagateEvent, this);

		this._nonPointGroup = L.featureGroup();
		this._nonPointGroup.on(L.FeatureGroup.EVENTS, this._propagateEvent, this);

		this._inZoomAnimation = 0;
		this._needsClustering = [];
		this._needsRemoving = []; //Markers removed while we aren't on the map need to be kept track of
		//The bounds of the currently shown area (from _getExpandedVisibleBounds) Updated on zoom/move
		this._currentShownBounds = null;

		this._queue = [];
	},

	addLayer: function (layer) {

		if (layer instanceof L.LayerGroup) {
			var array = [];
			for (var i in layer._layers) {
				array.push(layer._layers[i]);
			}
			return this.addLayers(array);
		}

		//Don't cluster non point data
		if (!layer.getLatLng) {
			this._nonPointGroup.addLayer(layer);
			return this;
		}

		if (!this._map) {
			this._needsClustering.push(layer);
			return this;
		}

		if (this.hasLayer(layer)) {
			return this;
		}


		//If we have already clustered we'll need to add this one to a cluster

		if (this._unspiderfy) {
			this._unspiderfy();
		}

		this._addLayer(layer, this._maxZoom);

		//Work out what is visible
		var visibleLayer = layer,
			currentZoom = this._map.getZoom();
		if (layer.__parent) {
			while (visibleLayer.__parent._zoom >= currentZoom) {
				visibleLayer = visibleLayer.__parent;
			}
		}

		if (this._currentShownBounds.contains(visibleLayer.getLatLng())) {
			if (this.options.animateAddingMarkers) {
				this._animationAddLayer(layer, visibleLayer);
			} else {
				this._animationAddLayerNonAnimated(layer, visibleLayer);
			}
		}
		return this;
	},

	removeLayer: function (layer) {

		if (layer instanceof L.LayerGroup)
		{
			var array = [];
			for (var i in layer._layers) {
				array.push(layer._layers[i]);
			}
			return this.removeLayers(array);
		}

		//Non point layers
		if (!layer.getLatLng) {
			this._nonPointGroup.removeLayer(layer);
			return this;
		}

		if (!this._map) {
			if (!this._arraySplice(this._needsClustering, layer) && this.hasLayer(layer)) {
				this._needsRemoving.push(layer);
			}
			return this;
		}

		if (!layer.__parent) {
			return this;
		}

		if (this._unspiderfy) {
			this._unspiderfy();
			this._unspiderfyLayer(layer);
		}

		//Remove the marker from clusters
		this._removeLayer(layer, true);

		if (this._featureGroup.hasLayer(layer)) {
			this._featureGroup.removeLayer(layer);
			if (layer.clusterShow) {
				layer.clusterShow();
			}
		}

		return this;
	},

	//Takes an array of markers and adds them in bulk
	addLayers: function (layersArray) {
		var fg = this._featureGroup,
			npg = this._nonPointGroup,
			chunked = this.options.chunkedLoading,
			chunkInterval = this.options.chunkInterval,
			chunkProgress = this.options.chunkProgress,
			newMarkers, i, l, m;

		if (this._map) {
			var offset = 0,
				started = (new Date()).getTime();
			var process = L.bind(function () {
				var start = (new Date()).getTime();
				for (; offset < layersArray.length; offset++) {
					if (chunked && offset % 200 === 0) {
						// every couple hundred markers, instrument the time elapsed since processing started:
						var elapsed = (new Date()).getTime() - start;
						if (elapsed > chunkInterval) {
							break; // been working too hard, time to take a break :-)
						}
					}

					m = layersArray[offset];

					//Not point data, can't be clustered
					if (!m.getLatLng) {
						npg.addLayer(m);
						continue;
					}

					if (this.hasLayer(m)) {
						continue;
					}

					this._addLayer(m, this._maxZoom);

					//If we just made a cluster of size 2 then we need to remove the other marker from the map (if it is) or we never will
					if (m.__parent) {
						if (m.__parent.getChildCount() === 2) {
							var markers = m.__parent.getAllChildMarkers(),
								otherMarker = markers[0] === m ? markers[1] : markers[0];
							fg.removeLayer(otherMarker);
						}
					}
				}

				if (chunkProgress) {
					// report progress and time elapsed:
					chunkProgress(offset, layersArray.length, (new Date()).getTime() - started);
				}

				if (offset === layersArray.length) {
					//Update the icons of all those visible clusters that were affected
					this._featureGroup.eachLayer(function (c) {
						if (c instanceof L.MarkerCluster && c._iconNeedsUpdate) {
							c._updateIcon();
						}
					});

					this._topClusterLevel._recursivelyAddChildrenToMap(null, this._zoom, this._currentShownBounds);
				} else {
					setTimeout(process, this.options.chunkDelay);
				}
			}, this);

			process();
		} else {
			newMarkers = [];
			for (i = 0, l = layersArray.length; i < l; i++) {
				m = layersArray[i];

				//Not point data, can't be clustered
				if (!m.getLatLng) {
					npg.addLayer(m);
					continue;
				}

				if (this.hasLayer(m)) {
					continue;
				}

				newMarkers.push(m);
			}
			this._needsClustering = this._needsClustering.concat(newMarkers);
		}
		return this;
	},

	//Takes an array of markers and removes them in bulk
	removeLayers: function (layersArray) {
		var i, l, m,
			fg = this._featureGroup,
			npg = this._nonPointGroup;

		if (this._unspiderfy) {
			this._unspiderfy();
		}

		if (!this._map) {
			for (i = 0, l = layersArray.length; i < l; i++) {
				m = layersArray[i];
				this._arraySplice(this._needsClustering, m);
				npg.removeLayer(m);
			}
			return this;
		}

		for (i = 0, l = layersArray.length; i < l; i++) {
			m = layersArray[i];

			if (!m.__parent) {
				npg.removeLayer(m);
				continue;
			}

			this._removeLayer(m, true, true);

			if (fg.hasLayer(m)) {
				fg.removeLayer(m);
				if (m.clusterShow) {
					m.clusterShow();
				}
			}
		}

		//Fix up the clusters and markers on the map
		this._topClusterLevel._recursivelyAddChildrenToMap(null, this._zoom, this._currentShownBounds);

		fg.eachLayer(function (c) {
			if (c instanceof L.MarkerCluster) {
				c._updateIcon();
			}
		});

		return this;
	},

	//Removes all layers from the MarkerClusterGroup
	clearLayers: function () {
		//Need our own special implementation as the LayerGroup one doesn't work for us

		//If we aren't on the map (yet), blow away the markers we know of
		if (!this._map) {
			this._needsClustering = [];
			delete this._gridClusters;
			delete this._gridUnclustered;
		}

		if (this._noanimationUnspiderfy) {
			this._noanimationUnspiderfy();
		}

		//Remove all the visible layers
		this._featureGroup.clearLayers();
		this._nonPointGroup.clearLayers();

		this.eachLayer(function (marker) {
			delete marker.__parent;
		});

		if (this._map) {
			//Reset _topClusterLevel and the DistanceGrids
			this._generateInitialClusters();
		}

		return this;
	},

	//Override FeatureGroup.getBounds as it doesn't work
	getBounds: function () {
		var bounds = new L.LatLngBounds();

		if (this._topClusterLevel) {
			bounds.extend(this._topClusterLevel._bounds);
		}

		for (var i = this._needsClustering.length - 1; i >= 0; i--) {
			bounds.extend(this._needsClustering[i].getLatLng());
		}

		bounds.extend(this._nonPointGroup.getBounds());

		return bounds;
	},

	//Overrides LayerGroup.eachLayer
	eachLayer: function (method, context) {
		var markers = this._needsClustering.slice(),
			i;

		if (this._topClusterLevel) {
			this._topClusterLevel.getAllChildMarkers(markers);
		}

		for (i = markers.length - 1; i >= 0; i--) {
			method.call(context, markers[i]);
		}

		this._nonPointGroup.eachLayer(method, context);
	},

	//Overrides LayerGroup.getLayers
	getLayers: function () {
		var layers = [];
		this.eachLayer(function (l) {
			layers.push(l);
		});
		return layers;
	},

	//Overrides LayerGroup.getLayer, WARNING: Really bad performance
	getLayer: function (id) {
		var result = null;

		this.eachLayer(function (l) {
			if (L.stamp(l) === id) {
				result = l;
			}
		});

		return result;
	},

	//Returns true if the given layer is in this MarkerClusterGroup
	hasLayer: function (layer) {
		if (!layer) {
			return false;
		}

		var i, anArray = this._needsClustering;

		for (i = anArray.length - 1; i >= 0; i--) {
			if (anArray[i] === layer) {
				return true;
			}
		}

		anArray = this._needsRemoving;
		for (i = anArray.length - 1; i >= 0; i--) {
			if (anArray[i] === layer) {
				return false;
			}
		}

		return !!(layer.__parent && layer.__parent._group === this) || this._nonPointGroup.hasLayer(layer);
	},

	//Zoom down to show the given layer (spiderfying if necessary) then calls the callback
	zoomToShowLayer: function (layer, callback) {

		var showMarker = function () {
			if ((layer._icon || layer.__parent._icon) && !this._inZoomAnimation) {
				this._map.off('moveend', showMarker, this);
				this.off('animationend', showMarker, this);

				if (layer._icon) {
					callback();
				} else if (layer.__parent._icon) {
					var afterSpiderfy = function () {
						this.off('spiderfied', afterSpiderfy, this);
						callback();
					};

					this.on('spiderfied', afterSpiderfy, this);
					layer.__parent.spiderfy();
				}
			}
		};

		if (layer._icon && this._map.getBounds().contains(layer.getLatLng())) {
			//Layer is visible ond on screen, immediate return
			callback();
		} else if (layer.__parent._zoom < this._map.getZoom()) {
			//Layer should be visible at this zoom level. It must not be on screen so just pan over to it
			this._map.on('moveend', showMarker, this);
			this._map.panTo(layer.getLatLng());
		} else {
			var moveStart = function () {
				this._map.off('movestart', moveStart, this);
				moveStart = null;
			};

			this._map.on('movestart', moveStart, this);
			this._map.on('moveend', showMarker, this);
			this.on('animationend', showMarker, this);
			layer.__parent.zoomToBounds();

			if (moveStart) {
				//Never started moving, must already be there, probably need clustering however
				showMarker.call(this);
			}
		}
	},

	//Overrides FeatureGroup.onAdd
	onAdd: function (map) {
		this._map = map;
		var i, l, layer;

		if (!isFinite(this._map.getMaxZoom())) {
			throw "Map has no maxZoom specified";
		}

		this._featureGroup.onAdd(map);
		this._nonPointGroup.onAdd(map);

		if (!this._gridClusters) {
			this._generateInitialClusters();
		}

		for (i = 0, l = this._needsRemoving.length; i < l; i++) {
			layer = this._needsRemoving[i];
			this._removeLayer(layer, true);
		}
		this._needsRemoving = [];

		//Remember the current zoom level and bounds
		this._zoom = this._map.getZoom();
		this._currentShownBounds = this._getExpandedVisibleBounds();

		this._map.on('zoomend', this._zoomEnd, this);
		this._map.on('moveend', this._moveEnd, this);

		if (this._spiderfierOnAdd) { //TODO FIXME: Not sure how to have spiderfier add something on here nicely
			this._spiderfierOnAdd();
		}

		this._bindEvents();

		//Actually add our markers to the map:
		l = this._needsClustering;
		this._needsClustering = [];
		this.addLayers(l);
	},

	//Overrides FeatureGroup.onRemove
	onRemove: function (map) {
		map.off('zoomend', this._zoomEnd, this);
		map.off('moveend', this._moveEnd, this);

		this._unbindEvents();

		//In case we are in a cluster animation
		this._map._mapPane.className = this._map._mapPane.className.replace(' leaflet-cluster-anim', '');

		if (this._spiderfierOnRemove) { //TODO FIXME: Not sure how to have spiderfier add something on here nicely
			this._spiderfierOnRemove();
		}



		//Clean up all the layers we added to the map
		this._hideCoverage();
		this._featureGroup.onRemove(map);
		this._nonPointGroup.onRemove(map);

		this._featureGroup.clearLayers();

		this._map = null;
	},

	getVisibleParent: function (marker) {
		var vMarker = marker;
		while (vMarker && !vMarker._icon) {
			vMarker = vMarker.__parent;
		}
		return vMarker || null;
	},

	//Remove the given object from the given array
	_arraySplice: function (anArray, obj) {
		for (var i = anArray.length - 1; i >= 0; i--) {
			if (anArray[i] === obj) {
				anArray.splice(i, 1);
				return true;
			}
		}
	},

	//Internal function for removing a marker from everything.
	//dontUpdateMap: set to true if you will handle updating the map manually (for bulk functions)
	_removeLayer: function (marker, removeFromDistanceGrid, dontUpdateMap) {
		var gridClusters = this._gridClusters,
			gridUnclustered = this._gridUnclustered,
			fg = this._featureGroup,
			map = this._map;

		//Remove the marker from distance clusters it might be in
		if (removeFromDistanceGrid) {
			for (var z = this._maxZoom; z >= 0; z--) {
				if (!gridUnclustered[z].removeObject(marker, map.project(marker.getLatLng(), z))) {
					break;
				}
			}
		}

		//Work our way up the clusters removing them as we go if required
		var cluster = marker.__parent,
			markers = cluster._markers,
			otherMarker;

		//Remove the marker from the immediate parents marker list
		this._arraySplice(markers, marker);

		while (cluster) {
			cluster._childCount--;

			if (cluster._zoom < 0) {
				//Top level, do nothing
				break;
			} else if (removeFromDistanceGrid && cluster._childCount <= 1) { //Cluster no longer required
				//We need to push the other marker up to the parent
				otherMarker = cluster._markers[0] === marker ? cluster._markers[1] : cluster._markers[0];

				//Update distance grid
				gridClusters[cluster._zoom].removeObject(cluster, map.project(cluster._cLatLng, cluster._zoom));
				gridUnclustered[cluster._zoom].addObject(otherMarker, map.project(otherMarker.getLatLng(), cluster._zoom));

				//Move otherMarker up to parent
				this._arraySplice(cluster.__parent._childClusters, cluster);
				cluster.__parent._markers.push(otherMarker);
				otherMarker.__parent = cluster.__parent;

				if (cluster._icon) {
					//Cluster is currently on the map, need to put the marker on the map instead
					fg.removeLayer(cluster);
					if (!dontUpdateMap) {
						fg.addLayer(otherMarker);
					}
				}
			} else {
				cluster._recalculateBounds();
				if (!dontUpdateMap || !cluster._icon) {
					cluster._updateIcon();
				}
			}

			cluster = cluster.__parent;
		}

		delete marker.__parent;
	},

	_isOrIsParent: function (el, oel) {
		while (oel) {
			if (el === oel) {
				return true;
			}
			oel = oel.parentNode;
		}
		return false;
	},

	_propagateEvent: function (e) {
		if (e.layer instanceof L.MarkerCluster) {
			//Prevent multiple clustermouseover/off events if the icon is made up of stacked divs (Doesn't work in ie <= 8, no relatedTarget)
			if (e.originalEvent && this._isOrIsParent(e.layer._icon, e.originalEvent.relatedTarget)) {
				return;
			}
			e.type = 'cluster' + e.type;
		}

		this.fire(e.type, e);
	},

	//Default functionality
	_defaultIconCreateFunction: function (cluster) {
		var childCount = cluster.getChildCount();

		var c = ' marker-cluster-';
		if (childCount < 10) {
			c += 'small';
		} else if (childCount < 100) {
			c += 'medium';
		} else {
			c += 'large';
		}

		return new L.DivIcon({ html: '<div><span>' + childCount + '</span></div>', className: 'marker-cluster' + c, iconSize: new L.Point(40, 40) });
	},

	_bindEvents: function () {
		var map = this._map,
		    spiderfyOnMaxZoom = this.options.spiderfyOnMaxZoom,
		    showCoverageOnHover = this.options.showCoverageOnHover,
		    zoomToBoundsOnClick = this.options.zoomToBoundsOnClick;

		//Zoom on cluster click or spiderfy if we are at the lowest level
		if (spiderfyOnMaxZoom || zoomToBoundsOnClick) {
			this.on('clusterclick', this._zoomOrSpiderfy, this);
		}

		//Show convex hull (boundary) polygon on mouse over
		if (showCoverageOnHover) {
			this.on('clustermouseover', this._showCoverage, this);
			this.on('clustermouseout', this._hideCoverage, this);
			map.on('zoomend', this._hideCoverage, this);
		}
	},

	_zoomOrSpiderfy: function (e) {
		var map = this._map;
		if (e.layer._bounds._northEast.equals(e.layer._bounds._southWest)) {
			if (this.options.spiderfyOnMaxZoom) {
				e.layer.spiderfy();
			}
		} else if (map.getMaxZoom() === map.getZoom()) {
			if (this.options.spiderfyOnMaxZoom) {
				e.layer.spiderfy();
			}
		} else if (this.options.zoomToBoundsOnClick) {
			e.layer.zoomToBounds();
		}

		// Focus the map again for keyboard users.
		if (e.originalEvent && e.originalEvent.keyCode === 13) {
			map._container.focus();
		}
	},

	_showCoverage: function (e) {
		var map = this._map;
		if (this._inZoomAnimation) {
			return;
		}
		if (this._shownPolygon) {
			map.removeLayer(this._shownPolygon);
		}
		if (e.layer.getChildCount() > 2 && e.layer !== this._spiderfied) {
			this._shownPolygon = new L.Polygon(e.layer.getConvexHull(), this.options.polygonOptions);
			map.addLayer(this._shownPolygon);
		}
	},

	_hideCoverage: function () {
		if (this._shownPolygon) {
			this._map.removeLayer(this._shownPolygon);
			this._shownPolygon = null;
		}
	},

	_unbindEvents: function () {
		var spiderfyOnMaxZoom = this.options.spiderfyOnMaxZoom,
			showCoverageOnHover = this.options.showCoverageOnHover,
			zoomToBoundsOnClick = this.options.zoomToBoundsOnClick,
			map = this._map;

		if (spiderfyOnMaxZoom || zoomToBoundsOnClick) {
			this.off('clusterclick', this._zoomOrSpiderfy, this);
		}
		if (showCoverageOnHover) {
			this.off('clustermouseover', this._showCoverage, this);
			this.off('clustermouseout', this._hideCoverage, this);
			map.off('zoomend', this._hideCoverage, this);
		}
	},

	_zoomEnd: function () {
		if (!this._map) { //May have been removed from the map by a zoomEnd handler
			return;
		}
		this._mergeSplitClusters();

		this._zoom = this._map._zoom;
		this._currentShownBounds = this._getExpandedVisibleBounds();
	},

	_moveEnd: function () {
		if (this._inZoomAnimation) {
			return;
		}

		var newBounds = this._getExpandedVisibleBounds();

		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, this._zoom, newBounds);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, this._map._zoom, newBounds);

		this._currentShownBounds = newBounds;
		return;
	},

	_generateInitialClusters: function () {
		var maxZoom = this._map.getMaxZoom(),
			radius = this.options.maxClusterRadius,
			radiusFn = radius;

		//If we just set maxClusterRadius to a single number, we need to create
		//a simple function to return that number. Otherwise, we just have to
		//use the function we've passed in.
		if (typeof radius !== "function") {
			radiusFn = function () { return radius; };
		}

		if (this.options.disableClusteringAtZoom) {
			maxZoom = this.options.disableClusteringAtZoom - 1;
		}
		this._maxZoom = maxZoom;
		this._gridClusters = {};
		this._gridUnclustered = {};

		//Set up DistanceGrids for each zoom
		for (var zoom = maxZoom; zoom >= 0; zoom--) {
			this._gridClusters[zoom] = new L.DistanceGrid(radiusFn(zoom));
			this._gridUnclustered[zoom] = new L.DistanceGrid(radiusFn(zoom));
		}

		this._topClusterLevel = new L.MarkerCluster(this, -1);
	},

	//Zoom: Zoom to start adding at (Pass this._maxZoom to start at the bottom)
	_addLayer: function (layer, zoom) {
		var gridClusters = this._gridClusters,
		    gridUnclustered = this._gridUnclustered,
		    markerPoint, z;

		if (this.options.singleMarkerMode) {
			layer.options.icon = this.options.iconCreateFunction({
				getChildCount: function () {
					return 1;
				},
				getAllChildMarkers: function () {
					return [layer];
				}
			});
		}

		//Find the lowest zoom level to slot this one in
		for (; zoom >= 0; zoom--) {
			markerPoint = this._map.project(layer.getLatLng(), zoom); // calculate pixel position

			//Try find a cluster close by
			var closest = gridClusters[zoom].getNearObject(markerPoint);
			if (closest) {
				closest._addChild(layer);
				layer.__parent = closest;
				return;
			}

			//Try find a marker close by to form a new cluster with
			closest = gridUnclustered[zoom].getNearObject(markerPoint);
			if (closest) {
				var parent = closest.__parent;
				if (parent) {
					this._removeLayer(closest, false);
				}

				//Create new cluster with these 2 in it

				var newCluster = new L.MarkerCluster(this, zoom, closest, layer);
				gridClusters[zoom].addObject(newCluster, this._map.project(newCluster._cLatLng, zoom));
				closest.__parent = newCluster;
				layer.__parent = newCluster;

				//First create any new intermediate parent clusters that don't exist
				var lastParent = newCluster;
				for (z = zoom - 1; z > parent._zoom; z--) {
					lastParent = new L.MarkerCluster(this, z, lastParent);
					gridClusters[z].addObject(lastParent, this._map.project(closest.getLatLng(), z));
				}
				parent._addChild(lastParent);

				//Remove closest from this zoom level and any above that it is in, replace with newCluster
				for (z = zoom; z >= 0; z--) {
					if (!gridUnclustered[z].removeObject(closest, this._map.project(closest.getLatLng(), z))) {
						break;
					}
				}

				return;
			}

			//Didn't manage to cluster in at this zoom, record us as a marker here and continue upwards
			gridUnclustered[zoom].addObject(layer, markerPoint);
		}

		//Didn't get in anything, add us to the top
		this._topClusterLevel._addChild(layer);
		layer.__parent = this._topClusterLevel;
		return;
	},

	//Enqueue code to fire after the marker expand/contract has happened
	_enqueue: function (fn) {
		this._queue.push(fn);
		if (!this._queueTimeout) {
			this._queueTimeout = setTimeout(L.bind(this._processQueue, this), 300);
		}
	},
	_processQueue: function () {
		for (var i = 0; i < this._queue.length; i++) {
			this._queue[i].call(this);
		}
		this._queue.length = 0;
		clearTimeout(this._queueTimeout);
		this._queueTimeout = null;
	},

	//Merge and split any existing clusters that are too big or small
	_mergeSplitClusters: function () {

		//Incase we are starting to split before the animation finished
		this._processQueue();

		if (this._zoom < this._map._zoom && this._currentShownBounds.intersects(this._getExpandedVisibleBounds())) { //Zoom in, split
			this._animationStart();
			//Remove clusters now off screen
			this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, this._zoom, this._getExpandedVisibleBounds());

			this._animationZoomIn(this._zoom, this._map._zoom);

		} else if (this._zoom > this._map._zoom) { //Zoom out, merge
			this._animationStart();

			this._animationZoomOut(this._zoom, this._map._zoom);
		} else {
			this._moveEnd();
		}
	},

	//Gets the maps visible bounds expanded in each direction by the size of the screen (so the user cannot see an area we do not cover in one pan)
	_getExpandedVisibleBounds: function () {
		if (!this.options.removeOutsideVisibleBounds) {
			return this._map.getBounds();
		}

		var map = this._map,
			bounds = map.getBounds(),
			sw = bounds._southWest,
			ne = bounds._northEast,
			latDiff = L.Browser.mobile ? 0 : Math.abs(sw.lat - ne.lat),
			lngDiff = L.Browser.mobile ? 0 : Math.abs(sw.lng - ne.lng);

		return new L.LatLngBounds(
			new L.LatLng(sw.lat - latDiff, sw.lng - lngDiff, true),
			new L.LatLng(ne.lat + latDiff, ne.lng + lngDiff, true));
	},

	//Shared animation code
	_animationAddLayerNonAnimated: function (layer, newCluster) {
		if (newCluster === layer) {
			this._featureGroup.addLayer(layer);
		} else if (newCluster._childCount === 2) {
			newCluster._addToMap();

			var markers = newCluster.getAllChildMarkers();
			this._featureGroup.removeLayer(markers[0]);
			this._featureGroup.removeLayer(markers[1]);
		} else {
			newCluster._updateIcon();
		}
	}
});

L.MarkerClusterGroup.include(!L.DomUtil.TRANSITION ? {

	//Non Animated versions of everything
	_animationStart: function () {
		//Do nothing...
	},
	_animationZoomIn: function (previousZoomLevel, newZoomLevel) {
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel, this._getExpandedVisibleBounds());

		//We didn't actually animate, but we use this event to mean "clustering animations have finished"
		this.fire('animationend');
	},
	_animationZoomOut: function (previousZoomLevel, newZoomLevel) {
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel, this._getExpandedVisibleBounds());

		//We didn't actually animate, but we use this event to mean "clustering animations have finished"
		this.fire('animationend');
	},
	_animationAddLayer: function (layer, newCluster) {
		this._animationAddLayerNonAnimated(layer, newCluster);
	}
} : {

	//Animated versions here
	_animationStart: function () {
		this._map._mapPane.className += ' leaflet-cluster-anim';
		this._inZoomAnimation++;
	},
	_animationEnd: function () {
		if (this._map) {
			this._map._mapPane.className = this._map._mapPane.className.replace(' leaflet-cluster-anim', '');
		}
		this._inZoomAnimation--;
		this.fire('animationend');
	},
	_animationZoomIn: function (previousZoomLevel, newZoomLevel) {
		var bounds = this._getExpandedVisibleBounds(),
		    fg = this._featureGroup,
		    i;

		//Add all children of current clusters to map and remove those clusters from map
		this._topClusterLevel._recursively(bounds, previousZoomLevel, 0, function (c) {
			var startPos = c._latlng,
				markers = c._markers,
				m;

			if (!bounds.contains(startPos)) {
				startPos = null;
			}

			if (c._isSingleParent() && previousZoomLevel + 1 === newZoomLevel) { //Immediately add the new child and remove us
				fg.removeLayer(c);
				c._recursivelyAddChildrenToMap(null, newZoomLevel, bounds);
			} else {
				//Fade out old cluster
				c.clusterHide();
				c._recursivelyAddChildrenToMap(startPos, newZoomLevel, bounds);
			}

			//Remove all markers that aren't visible any more
			//TODO: Do we actually need to do this on the higher levels too?
			for (i = markers.length - 1; i >= 0; i--) {
				m = markers[i];
				if (!bounds.contains(m._latlng)) {
					fg.removeLayer(m);
				}
			}

		});

		this._forceLayout();

		//Update opacities
		this._topClusterLevel._recursivelyBecomeVisible(bounds, newZoomLevel);
		//TODO Maybe? Update markers in _recursivelyBecomeVisible
		fg.eachLayer(function (n) {
			if (!(n instanceof L.MarkerCluster) && n._icon) {
				n.clusterShow();
			}
		});

		//update the positions of the just added clusters/markers
		this._topClusterLevel._recursively(bounds, previousZoomLevel, newZoomLevel, function (c) {
			c._recursivelyRestoreChildPositions(newZoomLevel);
		});

		//Remove the old clusters and close the zoom animation
		this._enqueue(function () {
			//update the positions of the just added clusters/markers
			this._topClusterLevel._recursively(bounds, previousZoomLevel, 0, function (c) {
				fg.removeLayer(c);
				c.clusterShow();
			});

			this._animationEnd();
		});
	},

	_animationZoomOut: function (previousZoomLevel, newZoomLevel) {
		this._animationZoomOutSingle(this._topClusterLevel, previousZoomLevel - 1, newZoomLevel);

		//Need to add markers for those that weren't on the map before but are now
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel, this._getExpandedVisibleBounds());
		//Remove markers that were on the map before but won't be now
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel, this._getExpandedVisibleBounds());
	},
	_animationZoomOutSingle: function (cluster, previousZoomLevel, newZoomLevel) {
		var bounds = this._getExpandedVisibleBounds();

		//Animate all of the markers in the clusters to move to their cluster center point
		cluster._recursivelyAnimateChildrenInAndAddSelfToMap(bounds, previousZoomLevel + 1, newZoomLevel);

		var me = this;

		//Update the opacity (If we immediately set it they won't animate)
		this._forceLayout();
		cluster._recursivelyBecomeVisible(bounds, newZoomLevel);

		//TODO: Maybe use the transition timing stuff to make this more reliable
		//When the animations are done, tidy up
		this._enqueue(function () {

			//This cluster stopped being a cluster before the timeout fired
			if (cluster._childCount === 1) {
				var m = cluster._markers[0];
				//If we were in a cluster animation at the time then the opacity and position of our child could be wrong now, so fix it
				m.setLatLng(m.getLatLng());
				if (m.clusterShow) {
					m.clusterShow();
				}
			} else {
				cluster._recursively(bounds, newZoomLevel, 0, function (c) {
					c._recursivelyRemoveChildrenFromMap(bounds, previousZoomLevel + 1);
				});
			}
			me._animationEnd();
		});
	},
	_animationAddLayer: function (layer, newCluster) {
		var me = this,
			fg = this._featureGroup;

		fg.addLayer(layer);
		if (newCluster !== layer) {
			if (newCluster._childCount > 2) { //Was already a cluster

				newCluster._updateIcon();
				this._forceLayout();
				this._animationStart();

				layer._setPos(this._map.latLngToLayerPoint(newCluster.getLatLng()));
				layer.clusterHide();

				this._enqueue(function () {
					fg.removeLayer(layer);
					layer.clusterShow();

					me._animationEnd();
				});

			} else { //Just became a cluster
				this._forceLayout();

				me._animationStart();
				me._animationZoomOutSingle(newCluster, this._map.getMaxZoom(), this._map.getZoom());
			}
		}
	},

	//Force a browser layout of stuff in the map
	// Should apply the current opacity and location to all elements so we can update them again for an animation
	_forceLayout: function () {
		//In my testing this works, infact offsetWidth of any element seems to work.
		//Could loop all this._layers and do this for each _icon if it stops working

		L.Util.falseFn(document.body.offsetWidth);
	}
});

L.markerClusterGroup = function (options) {
	return new L.MarkerClusterGroup(options);
};


L.MarkerCluster = L.Marker.extend({
	initialize: function (group, zoom, a, b) {

		L.Marker.prototype.initialize.call(this, a ? (a._cLatLng || a.getLatLng()) : new L.LatLng(0, 0), { icon: this });


		this._group = group;
		this._zoom = zoom;

		this._markers = [];
		this._childClusters = [];
		this._childCount = 0;
		this._iconNeedsUpdate = true;

		this._bounds = new L.LatLngBounds();

		if (a) {
			this._addChild(a);
		}
		if (b) {
			this._addChild(b);
		}
	},

	//Recursively retrieve all child markers of this cluster
	getAllChildMarkers: function (storageArray) {
		storageArray = storageArray || [];

		for (var i = this._childClusters.length - 1; i >= 0; i--) {
			this._childClusters[i].getAllChildMarkers(storageArray);
		}

		for (var j = this._markers.length - 1; j >= 0; j--) {
			storageArray.push(this._markers[j]);
		}

		return storageArray;
	},

	//Returns the count of how many child markers we have
	getChildCount: function () {
		return this._childCount;
	},

	//Zoom to the minimum of showing all of the child markers, or the extents of this cluster
	zoomToBounds: function () {
		var childClusters = this._childClusters.slice(),
			map = this._group._map,
			boundsZoom = map.getBoundsZoom(this._bounds),
			zoom = this._zoom + 1,
			mapZoom = map.getZoom(),
			i;

		//calculate how far we need to zoom down to see all of the markers
		while (childClusters.length > 0 && boundsZoom > zoom) {
			zoom++;
			var newClusters = [];
			for (i = 0; i < childClusters.length; i++) {
				newClusters = newClusters.concat(childClusters[i]._childClusters);
			}
			childClusters = newClusters;
		}

		if (boundsZoom > zoom) {
			this._group._map.setView(this._latlng, zoom);
		} else if (boundsZoom <= mapZoom) { //If fitBounds wouldn't zoom us down, zoom us down instead
			this._group._map.setView(this._latlng, mapZoom + 1);
		} else {
			this._group._map.fitBounds(this._bounds);
		}
	},

	getBounds: function () {
		var bounds = new L.LatLngBounds();
		bounds.extend(this._bounds);
		return bounds;
	},

	_updateIcon: function () {
		this._iconNeedsUpdate = true;
		if (this._icon) {
			this.setIcon(this);
		}
	},

	//Cludge for Icon, we pretend to be an icon for performance
	createIcon: function () {
		if (this._iconNeedsUpdate) {
			this._iconObj = this._group.options.iconCreateFunction(this);
			this._iconNeedsUpdate = false;
		}
		return this._iconObj.createIcon();
	},
	createShadow: function () {
		return this._iconObj.createShadow();
	},


	_addChild: function (new1, isNotificationFromChild) {

		this._iconNeedsUpdate = true;
		this._expandBounds(new1);

		if (new1 instanceof L.MarkerCluster) {
			if (!isNotificationFromChild) {
				this._childClusters.push(new1);
				new1.__parent = this;
			}
			this._childCount += new1._childCount;
		} else {
			if (!isNotificationFromChild) {
				this._markers.push(new1);
			}
			this._childCount++;
		}

		if (this.__parent) {
			this.__parent._addChild(new1, true);
		}
	},

	//Expand our bounds and tell our parent to
	_expandBounds: function (marker) {
		var addedCount,
		    addedLatLng = marker._wLatLng || marker._latlng;

		if (marker instanceof L.MarkerCluster) {
			this._bounds.extend(marker._bounds);
			addedCount = marker._childCount;
		} else {
			this._bounds.extend(addedLatLng);
			addedCount = 1;
		}

		if (!this._cLatLng) {
			// when clustering, take position of the first point as the cluster center
			this._cLatLng = marker._cLatLng || addedLatLng;
		}

		// when showing clusters, take weighted average of all points as cluster center
		var totalCount = this._childCount + addedCount;

		//Calculate weighted latlng for display
		if (!this._wLatLng) {
			this._latlng = this._wLatLng = new L.LatLng(addedLatLng.lat, addedLatLng.lng);
		} else {
			this._wLatLng.lat = (addedLatLng.lat * addedCount + this._wLatLng.lat * this._childCount) / totalCount;
			this._wLatLng.lng = (addedLatLng.lng * addedCount + this._wLatLng.lng * this._childCount) / totalCount;
		}
	},

	//Set our markers position as given and add it to the map
	_addToMap: function (startPos) {
		if (startPos) {
			this._backupLatlng = this._latlng;
			this.setLatLng(startPos);
		}
		this._group._featureGroup.addLayer(this);
	},

	_recursivelyAnimateChildrenIn: function (bounds, center, maxZoom) {
		this._recursively(bounds, 0, maxZoom - 1,
			function (c) {
				var markers = c._markers,
					i, m;
				for (i = markers.length - 1; i >= 0; i--) {
					m = markers[i];

					//Only do it if the icon is still on the map
					if (m._icon) {
						m._setPos(center);
						m.clusterHide();
					}
				}
			},
			function (c) {
				var childClusters = c._childClusters,
					j, cm;
				for (j = childClusters.length - 1; j >= 0; j--) {
					cm = childClusters[j];
					if (cm._icon) {
						cm._setPos(center);
						cm.clusterHide();
					}
				}
			}
		);
	},

	_recursivelyAnimateChildrenInAndAddSelfToMap: function (bounds, previousZoomLevel, newZoomLevel) {
		this._recursively(bounds, newZoomLevel, 0,
			function (c) {
				c._recursivelyAnimateChildrenIn(bounds, c._group._map.latLngToLayerPoint(c.getLatLng()).round(), previousZoomLevel);

				//TODO: depthToAnimateIn affects _isSingleParent, if there is a multizoom we may/may not be.
				//As a hack we only do a animation free zoom on a single level zoom, if someone does multiple levels then we always animate
				if (c._isSingleParent() && previousZoomLevel - 1 === newZoomLevel) {
					c.clusterShow();
					c._recursivelyRemoveChildrenFromMap(bounds, previousZoomLevel); //Immediately remove our children as we are replacing them. TODO previousBounds not bounds
				} else {
					c.clusterHide();
				}

				c._addToMap();
			}
		);
	},

	_recursivelyBecomeVisible: function (bounds, zoomLevel) {
		this._recursively(bounds, 0, zoomLevel, null, function (c) {
			c.clusterShow();
		});
	},

	_recursivelyAddChildrenToMap: function (startPos, zoomLevel, bounds) {
		this._recursively(bounds, -1, zoomLevel,
			function (c) {
				if (zoomLevel === c._zoom) {
					return;
				}

				//Add our child markers at startPos (so they can be animated out)
				for (var i = c._markers.length - 1; i >= 0; i--) {
					var nm = c._markers[i];

					if (!bounds.contains(nm._latlng)) {
						continue;
					}

					if (startPos) {
						nm._backupLatlng = nm.getLatLng();

						nm.setLatLng(startPos);
						if (nm.clusterHide) {
							nm.clusterHide();
						}
					}

					c._group._featureGroup.addLayer(nm);
				}
			},
			function (c) {
				c._addToMap(startPos);
			}
		);
	},

	_recursivelyRestoreChildPositions: function (zoomLevel) {
		//Fix positions of child markers
		for (var i = this._markers.length - 1; i >= 0; i--) {
			var nm = this._markers[i];
			if (nm._backupLatlng) {
				nm.setLatLng(nm._backupLatlng);
				delete nm._backupLatlng;
			}
		}

		if (zoomLevel - 1 === this._zoom) {
			//Reposition child clusters
			for (var j = this._childClusters.length - 1; j >= 0; j--) {
				this._childClusters[j]._restorePosition();
			}
		} else {
			for (var k = this._childClusters.length - 1; k >= 0; k--) {
				this._childClusters[k]._recursivelyRestoreChildPositions(zoomLevel);
			}
		}
	},

	_restorePosition: function () {
		if (this._backupLatlng) {
			this.setLatLng(this._backupLatlng);
			delete this._backupLatlng;
		}
	},

	//exceptBounds: If set, don't remove any markers/clusters in it
	_recursivelyRemoveChildrenFromMap: function (previousBounds, zoomLevel, exceptBounds) {
		var m, i;
		this._recursively(previousBounds, -1, zoomLevel - 1,
			function (c) {
				//Remove markers at every level
				for (i = c._markers.length - 1; i >= 0; i--) {
					m = c._markers[i];
					if (!exceptBounds || !exceptBounds.contains(m._latlng)) {
						c._group._featureGroup.removeLayer(m);
						if (m.clusterShow) {
							m.clusterShow();
						}
					}
				}
			},
			function (c) {
				//Remove child clusters at just the bottom level
				for (i = c._childClusters.length - 1; i >= 0; i--) {
					m = c._childClusters[i];
					if (!exceptBounds || !exceptBounds.contains(m._latlng)) {
						c._group._featureGroup.removeLayer(m);
						if (m.clusterShow) {
							m.clusterShow();
						}
					}
				}
			}
		);
	},

	//Run the given functions recursively to this and child clusters
	// boundsToApplyTo: a L.LatLngBounds representing the bounds of what clusters to recurse in to
	// zoomLevelToStart: zoom level to start running functions (inclusive)
	// zoomLevelToStop: zoom level to stop running functions (inclusive)
	// runAtEveryLevel: function that takes an L.MarkerCluster as an argument that should be applied on every level
	// runAtBottomLevel: function that takes an L.MarkerCluster as an argument that should be applied at only the bottom level
	_recursively: function (boundsToApplyTo, zoomLevelToStart, zoomLevelToStop, runAtEveryLevel, runAtBottomLevel) {
		var childClusters = this._childClusters,
		    zoom = this._zoom,
		    i, c;

		if (zoomLevelToStart > zoom) { //Still going down to required depth, just recurse to child clusters
			for (i = childClusters.length - 1; i >= 0; i--) {
				c = childClusters[i];
				if (boundsToApplyTo.intersects(c._bounds)) {
					c._recursively(boundsToApplyTo, zoomLevelToStart, zoomLevelToStop, runAtEveryLevel, runAtBottomLevel);
				}
			}
		} else { //In required depth

			if (runAtEveryLevel) {
				runAtEveryLevel(this);
			}
			if (runAtBottomLevel && this._zoom === zoomLevelToStop) {
				runAtBottomLevel(this);
			}

			//TODO: This loop is almost the same as above
			if (zoomLevelToStop > zoom) {
				for (i = childClusters.length - 1; i >= 0; i--) {
					c = childClusters[i];
					if (boundsToApplyTo.intersects(c._bounds)) {
						c._recursively(boundsToApplyTo, zoomLevelToStart, zoomLevelToStop, runAtEveryLevel, runAtBottomLevel);
					}
				}
			}
		}
	},

	_recalculateBounds: function () {
		var markers = this._markers,
			childClusters = this._childClusters,
			i;

		this._bounds = new L.LatLngBounds();
		delete this._wLatLng;

		for (i = markers.length - 1; i >= 0; i--) {
			this._expandBounds(markers[i]);
		}
		for (i = childClusters.length - 1; i >= 0; i--) {
			this._expandBounds(childClusters[i]);
		}
	},


	//Returns true if we are the parent of only one cluster and that cluster is the same as us
	_isSingleParent: function () {
		//Don't need to check this._markers as the rest won't work if there are any
		return this._childClusters.length > 0 && this._childClusters[0]._childCount === this._childCount;
	}
});



/*
* Extends L.Marker to include two extra methods: clusterHide and clusterShow.
*
* They work as setOpacity(0) and setOpacity(1) respectively, but
* they will remember the marker's opacity when hiding and showing it again.
*
*/


L.Marker.include({

	clusterHide: function () {
		this.options.opacityWhenUnclustered = this.options.opacity || 1;
		return this.setOpacity(0);
	},

	clusterShow: function () {
		var ret = this.setOpacity(this.options.opacity || this.options.opacityWhenUnclustered);
		delete this.options.opacityWhenUnclustered;
		return ret;
	}

});





L.DistanceGrid = function (cellSize) {
	this._cellSize = cellSize;
	this._sqCellSize = cellSize * cellSize;
	this._grid = {};
	this._objectPoint = { };
};

L.DistanceGrid.prototype = {

	addObject: function (obj, point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    grid = this._grid,
		    row = grid[y] = grid[y] || {},
		    cell = row[x] = row[x] || [],
		    stamp = L.Util.stamp(obj);

		this._objectPoint[stamp] = point;

		cell.push(obj);
	},

	updateObject: function (obj, point) {
		this.removeObject(obj);
		this.addObject(obj, point);
	},

	//Returns true if the object was found
	removeObject: function (obj, point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    grid = this._grid,
		    row = grid[y] = grid[y] || {},
		    cell = row[x] = row[x] || [],
		    i, len;

		delete this._objectPoint[L.Util.stamp(obj)];

		for (i = 0, len = cell.length; i < len; i++) {
			if (cell[i] === obj) {

				cell.splice(i, 1);

				if (len === 1) {
					delete row[x];
				}

				return true;
			}
		}

	},

	eachObject: function (fn, context) {
		var i, j, k, len, row, cell, removed,
		    grid = this._grid;

		for (i in grid) {
			row = grid[i];

			for (j in row) {
				cell = row[j];

				for (k = 0, len = cell.length; k < len; k++) {
					removed = fn.call(context, cell[k]);
					if (removed) {
						k--;
						len--;
					}
				}
			}
		}
	},

	getNearObject: function (point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    i, j, k, row, cell, len, obj, dist,
		    objectPoint = this._objectPoint,
		    closestDistSq = this._sqCellSize,
		    closest = null;

		for (i = y - 1; i <= y + 1; i++) {
			row = this._grid[i];
			if (row) {

				for (j = x - 1; j <= x + 1; j++) {
					cell = row[j];
					if (cell) {

						for (k = 0, len = cell.length; k < len; k++) {
							obj = cell[k];
							dist = this._sqDist(objectPoint[L.Util.stamp(obj)], point);
							if (dist < closestDistSq) {
								closestDistSq = dist;
								closest = obj;
							}
						}
					}
				}
			}
		}
		return closest;
	},

	_getCoord: function (x) {
		return Math.floor(x / this._cellSize);
	},

	_sqDist: function (p, p2) {
		var dx = p2.x - p.x,
		    dy = p2.y - p.y;
		return dx * dx + dy * dy;
	}
};


/* Copyright (c) 2012 the authors listed at the following URL, and/or
the authors of referenced articles or incorporated external code:
http://en.literateprograms.org/Quickhull_(Javascript)?action=history&offset=20120410175256

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Retrieved from: http://en.literateprograms.org/Quickhull_(Javascript)?oldid=18434
*/

(function () {
	L.QuickHull = {

		/*
		 * @param {Object} cpt a point to be measured from the baseline
		 * @param {Array} bl the baseline, as represented by a two-element
		 *   array of latlng objects.
		 * @returns {Number} an approximate distance measure
		 */
		getDistant: function (cpt, bl) {
			var vY = bl[1].lat - bl[0].lat,
				vX = bl[0].lng - bl[1].lng;
			return (vX * (cpt.lat - bl[0].lat) + vY * (cpt.lng - bl[0].lng));
		},

		/*
		 * @param {Array} baseLine a two-element array of latlng objects
		 *   representing the baseline to project from
		 * @param {Array} latLngs an array of latlng objects
		 * @returns {Object} the maximum point and all new points to stay
		 *   in consideration for the hull.
		 */
		findMostDistantPointFromBaseLine: function (baseLine, latLngs) {
			var maxD = 0,
				maxPt = null,
				newPoints = [],
				i, pt, d;

			for (i = latLngs.length - 1; i >= 0; i--) {
				pt = latLngs[i];
				d = this.getDistant(pt, baseLine);

				if (d > 0) {
					newPoints.push(pt);
				} else {
					continue;
				}

				if (d > maxD) {
					maxD = d;
					maxPt = pt;
				}
			}

			return { maxPoint: maxPt, newPoints: newPoints };
		},


		/*
		 * Given a baseline, compute the convex hull of latLngs as an array
		 * of latLngs.
		 *
		 * @param {Array} latLngs
		 * @returns {Array}
		 */
		buildConvexHull: function (baseLine, latLngs) {
			var convexHullBaseLines = [],
				t = this.findMostDistantPointFromBaseLine(baseLine, latLngs);

			if (t.maxPoint) { // if there is still a point "outside" the base line
				convexHullBaseLines =
					convexHullBaseLines.concat(
						this.buildConvexHull([baseLine[0], t.maxPoint], t.newPoints)
					);
				convexHullBaseLines =
					convexHullBaseLines.concat(
						this.buildConvexHull([t.maxPoint, baseLine[1]], t.newPoints)
					);
				return convexHullBaseLines;
			} else {  // if there is no more point "outside" the base line, the current base line is part of the convex hull
				return [baseLine[0]];
			}
		},

		/*
		 * Given an array of latlngs, compute a convex hull as an array
		 * of latlngs
		 *
		 * @param {Array} latLngs
		 * @returns {Array}
		 */
		getConvexHull: function (latLngs) {
			// find first baseline
			var maxLat = false, minLat = false,
				maxPt = null, minPt = null,
				i;

			for (i = latLngs.length - 1; i >= 0; i--) {
				var pt = latLngs[i];
				if (maxLat === false || pt.lat > maxLat) {
					maxPt = pt;
					maxLat = pt.lat;
				}
				if (minLat === false || pt.lat < minLat) {
					minPt = pt;
					minLat = pt.lat;
				}
			}
			var ch = [].concat(this.buildConvexHull([minPt, maxPt], latLngs),
								this.buildConvexHull([maxPt, minPt], latLngs));
			return ch;
		}
	};
}());

L.MarkerCluster.include({
	getConvexHull: function () {
		var childMarkers = this.getAllChildMarkers(),
			points = [],
			p, i;

		for (i = childMarkers.length - 1; i >= 0; i--) {
			p = childMarkers[i].getLatLng();
			points.push(p);
		}

		return L.QuickHull.getConvexHull(points);
	}
});


//This code is 100% based on https://github.com/jawj/OverlappingMarkerSpiderfier-Leaflet
//Huge thanks to jawj for implementing it first to make my job easy :-)

L.MarkerCluster.include({

	_2PI: Math.PI * 2,
	_circleFootSeparation: 25, //related to circumference of circle
	_circleStartAngle: Math.PI / 6,

	_spiralFootSeparation:  28, //related to size of spiral (experiment!)
	_spiralLengthStart: 11,
	_spiralLengthFactor: 5,

	_circleSpiralSwitchover: 9, //show spiral instead of circle from this marker count upwards.
								// 0 -> always spiral; Infinity -> always circle

	spiderfy: function () {
		if (this._group._spiderfied === this || this._group._inZoomAnimation) {
			return;
		}

		var childMarkers = this.getAllChildMarkers(),
			group = this._group,
			map = group._map,
			center = map.latLngToLayerPoint(this._latlng),
			positions;

		this._group._unspiderfy();
		this._group._spiderfied = this;

		//TODO Maybe: childMarkers order by distance to center

		if (childMarkers.length >= this._circleSpiralSwitchover) {
			positions = this._generatePointsSpiral(childMarkers.length, center);
		} else {
			center.y += 10; //Otherwise circles look wrong
			positions = this._generatePointsCircle(childMarkers.length, center);
		}

		this._animationSpiderfy(childMarkers, positions);
	},

	unspiderfy: function (zoomDetails) {
		/// <param Name="zoomDetails">Argument from zoomanim if being called in a zoom animation or null otherwise</param>
		if (this._group._inZoomAnimation) {
			return;
		}
		this._animationUnspiderfy(zoomDetails);

		this._group._spiderfied = null;
	},

	_generatePointsCircle: function (count, centerPt) {
		var circumference = this._group.options.spiderfyDistanceMultiplier * this._circleFootSeparation * (2 + count),
			legLength = circumference / this._2PI,  //radius from circumference
			angleStep = this._2PI / count,
			res = [],
			i, angle;

		res.length = count;

		for (i = count - 1; i >= 0; i--) {
			angle = this._circleStartAngle + i * angleStep;
			res[i] = new L.Point(centerPt.x + legLength * Math.cos(angle), centerPt.y + legLength * Math.sin(angle))._round();
		}

		return res;
	},

	_generatePointsSpiral: function (count, centerPt) {
		var legLength = this._group.options.spiderfyDistanceMultiplier * this._spiralLengthStart,
			separation = this._group.options.spiderfyDistanceMultiplier * this._spiralFootSeparation,
			lengthFactor = this._group.options.spiderfyDistanceMultiplier * this._spiralLengthFactor,
			angle = 0,
			res = [],
			i;

		res.length = count;

		for (i = count - 1; i >= 0; i--) {
			angle += separation / legLength + i * 0.0005;
			res[i] = new L.Point(centerPt.x + legLength * Math.cos(angle), centerPt.y + legLength * Math.sin(angle))._round();
			legLength += this._2PI * lengthFactor / angle;
		}
		return res;
	},

	_noanimationUnspiderfy: function () {
		var group = this._group,
			map = group._map,
			fg = group._featureGroup,
			childMarkers = this.getAllChildMarkers(),
			m, i;

		this.setOpacity(1);
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			fg.removeLayer(m);

			if (m._preSpiderfyLatlng) {
				m.setLatLng(m._preSpiderfyLatlng);
				delete m._preSpiderfyLatlng;
			}
			if (m.setZIndexOffset) {
				m.setZIndexOffset(0);
			}

			if (m._spiderLeg) {
				map.removeLayer(m._spiderLeg);
				delete m._spiderLeg;
			}
		}

		group._spiderfied = null;
	}
});

L.MarkerCluster.include(!L.DomUtil.TRANSITION ? {
	//Non Animated versions of everything
	_animationSpiderfy: function (childMarkers, positions) {
		var group = this._group,
			map = group._map,
			fg = group._featureGroup,
			i, m, leg, newPos;

		for (i = childMarkers.length - 1; i >= 0; i--) {
			newPos = map.layerPointToLatLng(positions[i]);
			m = childMarkers[i];

			m._preSpiderfyLatlng = m._latlng;
			m.setLatLng(newPos);
			if (m.setZIndexOffset) {
				m.setZIndexOffset(1000000); //Make these appear on top of EVERYTHING
			}

			fg.addLayer(m);

			var legOptions = this._group.options.spiderLegPolylineOptions;
			leg = new L.Polyline([this._latlng, newPos], legOptions);
			map.addLayer(leg);
			m._spiderLeg = leg;
		}
		this.setOpacity(0.3);
		group.fire('spiderfied');
	},

	_animationUnspiderfy: function () {
		this._noanimationUnspiderfy();
	}
} : {
	//Animated versions here
	SVG_ANIMATION: (function () {
		return document.createElementNS('http://www.w3.org/2000/svg', 'animate').toString().indexOf('SVGAnimate') > -1;
	}()),

	_animationSpiderfy: function (childMarkers, positions) {
		var me = this,
			group = this._group,
			map = group._map,
			fg = group._featureGroup,
			thisLayerPos = map.latLngToLayerPoint(this._latlng),
			i, m, leg, newPos;

		//Add markers to map hidden at our center point
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			//If it is a marker, add it now and we'll animate it out
			if (m.setOpacity) {
				m.setZIndexOffset(1000000); //Make these appear on top of EVERYTHING
				m.clusterHide();

				fg.addLayer(m);

				m._setPos(thisLayerPos);
			} else {
				//Vectors just get immediately added
				fg.addLayer(m);
			}
		}

		group._forceLayout();
		group._animationStart();

		var initialLegOpacity = L.Path.SVG ? 0 : 0.3,
			xmlns = L.Path.SVG_NS;


		for (i = childMarkers.length - 1; i >= 0; i--) {
			newPos = map.layerPointToLatLng(positions[i]);
			m = childMarkers[i];

			//Move marker to new position
			m._preSpiderfyLatlng = m._latlng;
			m.setLatLng(newPos);

			if (m.setOpacity) {
				m.clusterShow();
			}


			//Add Legs.
			var legOptions = this._group.options.spiderLegPolylineOptions;
			if (legOptions.opacity === undefined) {
				legOptions.opacity = initialLegOpacity;
			}
			leg = new L.Polyline([me._latlng, newPos], legOptions);
			map.addLayer(leg);
			m._spiderLeg = leg;

			//Following animations don't work for canvas
			if (!L.Path.SVG || !this.SVG_ANIMATION) {
				continue;
			}

			//How this works:
			//http://stackoverflow.com/questions/5924238/how-do-you-animate-an-svg-path-in-ios
			//http://dev.opera.com/articles/view/advanced-svg-animation-techniques/

			//Animate length
			var length = leg._path.getTotalLength();
			leg._path.setAttribute("stroke-dasharray", length + "," + length);

			var anim = document.createElementNS(xmlns, "animate");
			anim.setAttribute("attributeName", "stroke-dashoffset");
			anim.setAttribute("begin", "indefinite");
			anim.setAttribute("from", length);
			anim.setAttribute("to", 0);
			anim.setAttribute("dur", 0.25);
			leg._path.appendChild(anim);
			anim.beginElement();

			//Animate opacity
			anim = document.createElementNS(xmlns, "animate");
			anim.setAttribute("attributeName", "stroke-opacity");
			anim.setAttribute("attributeName", "stroke-opacity");
			anim.setAttribute("begin", "indefinite");
			anim.setAttribute("from", 0);
			anim.setAttribute("to", 0.5);
			anim.setAttribute("dur", 0.25);
			leg._path.appendChild(anim);
			anim.beginElement();
		}
		me.setOpacity(0.3);

		//Set the opacity of the spiderLegs back to their correct value
		// The animations above override this until they complete.
		// If the initial opacity of the spiderlegs isn't 0 then they appear before the animation starts.
		if (L.Path.SVG) {
			this._group._forceLayout();

			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i]._spiderLeg;

				m.options.opacity = 0.5;
				m._path.setAttribute('stroke-opacity', 0.5);
			}
		}

		setTimeout(function () {
			group._animationEnd();
			group.fire('spiderfied');
		}, 200);
	},

	_animationUnspiderfy: function (zoomDetails) {
		var group = this._group,
			map = group._map,
			fg = group._featureGroup,
			thisLayerPos = zoomDetails ? map._latLngToNewLayerPoint(this._latlng, zoomDetails.zoom, zoomDetails.center) : map.latLngToLayerPoint(this._latlng),
			childMarkers = this.getAllChildMarkers(),
			svg = L.Path.SVG && this.SVG_ANIMATION,
			m, i, a;

		group._animationStart();

		//Make us visible and bring the child markers back in
		this.setOpacity(1);
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			//Marker was added to us after we were spidified
			if (!m._preSpiderfyLatlng) {
				continue;
			}

			//Fix up the location to the real one
			m.setLatLng(m._preSpiderfyLatlng);
			delete m._preSpiderfyLatlng;
			//Hack override the location to be our center
			if (m.setOpacity) {
				m._setPos(thisLayerPos);
				m.clusterHide();
			} else {
				fg.removeLayer(m);
			}

			//Animate the spider legs back in
			if (svg) {
				a = m._spiderLeg._path.childNodes[0];
				a.setAttribute('to', a.getAttribute('from'));
				a.setAttribute('from', 0);
				a.beginElement();

				a = m._spiderLeg._path.childNodes[1];
				a.setAttribute('from', 0.5);
				a.setAttribute('to', 0);
				a.setAttribute('stroke-opacity', 0);
				a.beginElement();

				m._spiderLeg._path.setAttribute('stroke-opacity', 0);
			}
		}

		setTimeout(function () {
			//If we have only <= one child left then that marker will be shown on the map so don't remove it!
			var stillThereChildCount = 0;
			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i];
				if (m._spiderLeg) {
					stillThereChildCount++;
				}
			}


			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i];

				if (!m._spiderLeg) { //Has already been unspiderfied
					continue;
				}


				if (m.setOpacity) {
					m.clusterShow();
					m.setZIndexOffset(0);
				}

				if (stillThereChildCount > 1) {
					fg.removeLayer(m);
				}

				map.removeLayer(m._spiderLeg);
				delete m._spiderLeg;
			}
			group._animationEnd();
		}, 200);
	}
});


L.MarkerClusterGroup.include({
	//The MarkerCluster currently spiderfied (if any)
	_spiderfied: null,

	_spiderfierOnAdd: function () {
		this._map.on('click', this._unspiderfyWrapper, this);

		if (this._map.options.zoomAnimation) {
			this._map.on('zoomstart', this._unspiderfyZoomStart, this);
		}
		//Browsers without zoomAnimation or a big zoom don't fire zoomstart
		this._map.on('zoomend', this._noanimationUnspiderfy, this);

		if (L.Path.SVG && !L.Browser.touch) {
			this._map._initPathRoot();
			//Needs to happen in the pageload, not after, or animations don't work in webkit
			//  http://stackoverflow.com/questions/8455200/svg-animate-with-dynamically-added-elements
			//Disable on touch browsers as the animation messes up on a touch zoom and isn't very noticable
		}
	},

	_spiderfierOnRemove: function () {
		this._map.off('click', this._unspiderfyWrapper, this);
		this._map.off('zoomstart', this._unspiderfyZoomStart, this);
		this._map.off('zoomanim', this._unspiderfyZoomAnim, this);

		this._unspiderfy(); //Ensure that markers are back where they should be
	},


	//On zoom start we add a zoomanim handler so that we are guaranteed to be last (after markers are animated)
	//This means we can define the animation they do rather than Markers doing an animation to their actual location
	_unspiderfyZoomStart: function () {
		if (!this._map) { //May have been removed from the map by a zoomEnd handler
			return;
		}

		this._map.on('zoomanim', this._unspiderfyZoomAnim, this);
	},
	_unspiderfyZoomAnim: function (zoomDetails) {
		//Wait until the first zoomanim after the user has finished touch-zooming before running the animation
		if (L.DomUtil.hasClass(this._map._mapPane, 'leaflet-touching')) {
			return;
		}

		this._map.off('zoomanim', this._unspiderfyZoomAnim, this);
		this._unspiderfy(zoomDetails);
	},


	_unspiderfyWrapper: function () {
		/// <summary>_unspiderfy but passes no arguments</summary>
		this._unspiderfy();
	},

	_unspiderfy: function (zoomDetails) {
		if (this._spiderfied) {
			this._spiderfied.unspiderfy(zoomDetails);
		}
	},

	_noanimationUnspiderfy: function () {
		if (this._spiderfied) {
			this._spiderfied._noanimationUnspiderfy();
		}
	},

	//If the given layer is currently being spiderfied then we unspiderfy it so it isn't on the map anymore etc
	_unspiderfyLayer: function (layer) {
		if (layer._spiderLeg) {
			this._featureGroup.removeLayer(layer);

			layer.setOpacity(1);
			//Position will be fixed up immediately in _animationUnspiderfy
			layer.setZIndexOffset(0);

			this._map.removeLayer(layer._spiderLeg);
			delete layer._spiderLeg;
		}
	}
});


}(window, document));

/*
* MiniMap plugin by Norkart, https://github.com/Norkart/Leaflet-MiniMap
* Last commits included: 13/04/2016 ( 3.3.0 )
*/
// Following https://github.com/Leaflet/Leaflet/blob/master/PLUGIN-GUIDE.md
(function (factory, window) {

	// define an AMD module that relies on 'leaflet'
	if (typeof define === 'function' && define.amd) {
		define(['leaflet'], factory);

	// define a Common JS module that relies on 'leaflet'
	} else if (typeof exports === 'object') {
		module.exports = factory(require('leaflet'));
	}

	// attach your plugin to the global 'L' variable
	if (typeof window !== 'undefined' && window.L) {
		window.L.Control.MiniMap = factory(L);
		window.L.control.minimap = function (layer, options) {
			return new window.L.Control.MiniMap(layer, options);
		};
	}
}(function (L) {

	var MiniMap = L.Control.extend({
		options: {
			position: 'bottomright',
			toggleDisplay: false,
			zoomLevelOffset: -5,
			zoomLevelFixed: false,
			centerFixed: false,
			zoomAnimation: false,
			autoToggleDisplay: false,
			minimized: false,
			width: 150,
			height: 150,
			collapsedWidth: 19,
			collapsedHeight: 19,
			aimingRectOptions: {color: "#ff7800", weight: 1, clickable: false},
			shadowRectOptions: {color: "#000000", weight: 1, clickable: false, opacity: 0, fillOpacity: 0},
			strings: {hideText: mapsmarkerjspro.minimap_hide, showText: mapsmarkerjspro.minimap_show},
			mapOptions: {}  // Allows definition / override of Leaflet map options.
		},

		// layer is the map layer to be shown in the minimap
		initialize: function (layer, options) {
			L.Util.setOptions(this, options);
			// Make sure the aiming rects are non-clickable even if the user tries to set them clickable (most likely by forgetting to specify them false)
			this.options.aimingRectOptions.clickable = false;
			this.options.shadowRectOptions.clickable = false;
			this._layer = layer;
		},

		onAdd: function (map) {

			this._mainMap = map;

			// Creating the container and stopping events from spilling through to the main map.
			this._container = L.DomUtil.create('div', 'leaflet-control-minimap');
			this._container.style.width = this.options.width + 'px';
			this._container.style.height = this.options.height + 'px';
			L.DomEvent.disableClickPropagation(this._container);
			L.DomEvent.on(this._container, 'mousewheel', L.DomEvent.stopPropagation);

			var mapOptions = {
				attributionControl: false,
				dragging: !this.options.centerFixed,
				zoomControl: false,
				zoomAnimation: this.options.zoomAnimation,
				autoToggleDisplay: this.options.autoToggleDisplay,
				touchZoom: this.options.centerFixed ? 'center' : !this._isZoomLevelFixed(),
				scrollWheelZoom: this.options.centerFixed ? 'center' : !this._isZoomLevelFixed(),
				doubleClickZoom: this.options.centerFixed ? 'center' : !this._isZoomLevelFixed(),
				boxZoom: !this._isZoomLevelFixed(),
				crs: map.options.crs
			};
			mapOptions = L.Util.extend(this.options.mapOptions, mapOptions);  // merge with priority of the local mapOptions object.

			this._miniMap = new L.Map(this._container, mapOptions);

			this._miniMap.addLayer(this._layer);

			// These bools are used to prevent infinite loops of the two maps notifying each other that they've moved.
			this._mainMapMoving = false;
			this._miniMapMoving = false;

			// Keep a record of this to prevent auto toggling when the user explicitly doesn't want it.
			this._userToggledDisplay = false;
			this._minimized = false;

			if (this.options.toggleDisplay) {
				this._addToggleButton();
			}

			this._miniMap.whenReady(L.Util.bind(function () {
				this._aimingRect = L.rectangle(this._mainMap.getBounds(), this.options.aimingRectOptions).addTo(this._miniMap);
				this._shadowRect = L.rectangle(this._mainMap.getBounds(), this.options.shadowRectOptions).addTo(this._miniMap);
				this._mainMap.on('moveend', this._onMainMapMoved, this);
				this._mainMap.on('move', this._onMainMapMoving, this);
				this._miniMap.on('movestart', this._onMiniMapMoveStarted, this);
				this._miniMap.on('move', this._onMiniMapMoving, this);
				this._miniMap.on('moveend', this._onMiniMapMoved, this);
			}, this));

			return this._container;
		},

		addTo: function (map) {
			L.Control.prototype.addTo.call(this, map);

			var center = this.options.centerFixed || this._mainMap.getCenter();
			this._miniMap.setView(center, this._decideZoom(true));
			this._setDisplay(this.options.minimized);
			return this;
		},

		onRemove: function (map) {
			this._mainMap.off('moveend', this._onMainMapMoved, this);
			this._mainMap.off('move', this._onMainMapMoving, this);
			this._miniMap.off('moveend', this._onMiniMapMoved, this);

			this._miniMap.removeLayer(this._layer);
		},

		changeLayer: function (layer) {
			this._miniMap.removeLayer(this._layer);
			this._layer = layer;
			this._miniMap.addLayer(this._layer);
		},

		_addToggleButton: function () {
			this._toggleDisplayButton = this.options.toggleDisplay ? this._createButton(
				'', this.options.strings.hideText, ('leaflet-control-minimap-toggle-display leaflet-control-minimap-toggle-display-' +
				this.options.position), this._container, this._toggleDisplayButtonClicked, this) : undefined;

			this._toggleDisplayButton.style.width = this.options.collapsedWidth + 'px';
			this._toggleDisplayButton.style.height = this.options.collapsedHeight + 'px';
		},

		_createButton: function (html, title, className, container, fn, context) {
			var link = L.DomUtil.create('a', className, container);
			link.innerHTML = html;
			link.href = '#';
			link.title = title;

			var stop = L.DomEvent.stopPropagation;

			L.DomEvent
				.on(link, 'click', stop)
				.on(link, 'mousedown', stop)
				.on(link, 'dblclick', stop)
				.on(link, 'click', L.DomEvent.preventDefault)
				.on(link, 'click', fn, context);

			return link;
		},

		_toggleDisplayButtonClicked: function () {
			this._userToggledDisplay = true;
			if (!this._minimized) {
				this._minimize();
				this._toggleDisplayButton.title = this.options.strings.showText;
			} else {
				this._restore();
				this._toggleDisplayButton.title = this.options.strings.hideText;
			}
		},

		_setDisplay: function (minimize) {
			if (minimize !== this._minimized) {
				if (!this._minimized) {
					this._minimize();
				} else {
					this._restore();
				}
			}
		},

		_minimize: function () {
			// hide the minimap
			if (this.options.toggleDisplay) {
				this._container.style.width = this.options.collapsedWidth + 'px';
				this._container.style.height = this.options.collapsedHeight + 'px';
				this._toggleDisplayButton.className += (' minimized-' + this.options.position);
			} else {
				this._container.style.display = 'none';
			}
			this._minimized = true;
		},

		_restore: function () {
			if (this.options.toggleDisplay) {
				this._container.style.width = this.options.width + 'px';
				this._container.style.height = this.options.height + 'px';
				this._toggleDisplayButton.className = this._toggleDisplayButton.className
					.replace('minimized-'	+ this.options.position, '');
			} else {
				this._container.style.display = 'block';
			}
			this._minimized = false;
		},

		_onMainMapMoved: function (e) {
			if (!this._miniMapMoving) {
				var center = this.options.centerFixed || this._mainMap.getCenter();

				this._mainMapMoving = true;
				this._miniMap.setView(center, this._decideZoom(true));
				this._setDisplay(this._decideMinimized());
			} else {
				this._miniMapMoving = false;
			}
			this._aimingRect.setBounds(this._mainMap.getBounds());
		},

		_onMainMapMoving: function (e) {
			this._aimingRect.setBounds(this._mainMap.getBounds());
		},

		_onMiniMapMoveStarted: function (e) {
			if (!this.options.centerFixed) {
				var lastAimingRect = this._aimingRect.getBounds();
				var sw = this._miniMap.latLngToContainerPoint(lastAimingRect.getSouthWest());
				var ne = this._miniMap.latLngToContainerPoint(lastAimingRect.getNorthEast());
				this._lastAimingRectPosition = {sw: sw, ne: ne};
			}
		},

		_onMiniMapMoving: function (e) {
			if (!this.options.centerFixed) {
				if (!this._mainMapMoving && this._lastAimingRectPosition) {
					this._shadowRect.setBounds(new L.LatLngBounds(this._miniMap.containerPointToLatLng(this._lastAimingRectPosition.sw), this._miniMap.containerPointToLatLng(this._lastAimingRectPosition.ne)));
					this._shadowRect.setStyle({opacity: 1, fillOpacity: 0.3});
				}
			}
		},

		_onMiniMapMoved: function (e) {
			if (!this._mainMapMoving) {
				this._miniMapMoving = true;
				this._mainMap.setView(this._miniMap.getCenter(), this._decideZoom(false));
				this._shadowRect.setStyle({opacity: 0, fillOpacity: 0});
			} else {
				this._mainMapMoving = false;
			}
		},

		_isZoomLevelFixed: function () {
			var zoomLevelFixed = this.options.zoomLevelFixed;
			return this._isDefined(zoomLevelFixed) && this._isInteger(zoomLevelFixed);
		},

		_decideZoom: function (fromMaintoMini) {
			if (!this._isZoomLevelFixed()) {
				if (fromMaintoMini) {
					return this._mainMap.getZoom() + this.options.zoomLevelOffset;
				} else {
					var currentDiff = this._miniMap.getZoom() - this._mainMap.getZoom();
					var proposedZoom = this._miniMap.getZoom() - this.options.zoomLevelOffset;
					var toRet;

					if (currentDiff > this.options.zoomLevelOffset && this._mainMap.getZoom() < this._miniMap.getMinZoom() - this.options.zoomLevelOffset) {
						// This means the miniMap is zoomed out to the minimum zoom level and can't zoom any more.
						if (this._miniMap.getZoom() > this._lastMiniMapZoom) {
							// This means the user is trying to zoom in by using the minimap, zoom the main map.
							toRet = this._mainMap.getZoom() + 1;
							// Also we cheat and zoom the minimap out again to keep it visually consistent.
							this._miniMap.setZoom(this._miniMap.getZoom() - 1);
						} else {
							// Either the user is trying to zoom out past the mini map's min zoom or has just panned using it, we can't tell the difference.
							// Therefore, we ignore it!
							toRet = this._mainMap.getZoom();
						}
					} else {
						// This is what happens in the majority of cases, and always if you configure the min levels + offset in a sane fashion.
						toRet = proposedZoom;
					}
					this._lastMiniMapZoom = this._miniMap.getZoom();
					return toRet;
				}
			} else {
				if (fromMaintoMini) {
					return this.options.zoomLevelFixed;
				} else {
					return this._mainMap.getZoom();
				}
			}
		},

		_decideMinimized: function () {
			if (this._userToggledDisplay) {
				return this._minimized;
			}

			if (this.options.autoToggleDisplay) {
				if (this._mainMap.getBounds().contains(this._miniMap.getBounds())) {
					return true;
				}
				return false;
			}

			return this._minimized;
		},

		_isInteger: function (value) {
			return typeof value === 'number';
		},

		_isDefined: function (value) {
			return typeof value !== 'undefined';
		}
	});

	L.Map.mergeOptions({
		miniMapControl: false
	});

	L.Map.addInitHook(function () {
		if (this.options.miniMapControl) {
			this.miniMapControl = (new MiniMap()).addTo(this);
		}
	});

	return MiniMap;

}, window));

/*
* Fullscreen plugin by https://github.com/mapbox/Leaflet.fullscreen
* License: https://github.com/mapbox/Leaflet.fullscreen/blob/master/LICENSE
*/
L.Control.Fullscreen = L.Control.extend({
    options: {
        position: mapsmarkerjspro.fullscreen_button_position,
        title: {
            'false': mapsmarkerjspro.fullscreen_button_title,
			'true': mapsmarkerjspro.fullscreen_button_title_exit
        }
    },

    onAdd: function (map) {
        var container = L.DomUtil.create('div', 'leaflet-control-fullscreen leaflet-bar leaflet-control');

        this.link = L.DomUtil.create('a', 'leaflet-control-fullscreen-button leaflet-bar-part', container);
        this.link.href = '#';

        this._map = map;
        this._map.on('fullscreenchange', this._toggleTitle, this);
        this._toggleTitle();

        L.DomEvent.on(this.link, 'click', this._click, this);

        return container;
    },

    _click: function (e) {
        L.DomEvent.stopPropagation(e);
        L.DomEvent.preventDefault(e);
        this._map.toggleFullscreen();
    },

    _toggleTitle: function() {
        this.link.title = this.options.title[this._map.isFullscreen()];
    }
});

L.Map.include({
    isFullscreen: function () {
        return this._isFullscreen || false;
    },

    toggleFullscreen: function () {
        var container = this.getContainer();
        if (this.isFullscreen()) {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else {
                L.DomUtil.removeClass(container, 'leaflet-pseudo-fullscreen');
                this._setFullscreen(false);
                this.invalidateSize();
                this.fire('fullscreenchange');
            }
        } else {
            if (container.requestFullscreen) {
                container.requestFullscreen();
            } else if (container.mozRequestFullScreen) {
                container.mozRequestFullScreen();
            } else if (container.webkitRequestFullscreen) {
                container.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            } else if (container.msRequestFullscreen) {
                container.msRequestFullscreen();
            } else {
                L.DomUtil.addClass(container, 'leaflet-pseudo-fullscreen');
                this._setFullscreen(true);
                this.invalidateSize();
                this.fire('fullscreenchange');
            }
        }
    },

    _setFullscreen: function(fullscreen) {
        this._isFullscreen = fullscreen;
        var container = this.getContainer();
        if (fullscreen) {
            L.DomUtil.addClass(container, 'leaflet-fullscreen-on');
        } else {
            L.DomUtil.removeClass(container, 'leaflet-fullscreen-on');
        }
    },

    _onFullscreenChange: function (e) {
        var fullscreenElement =
            document.fullscreenElement ||
            document.mozFullScreenElement ||
            document.webkitFullscreenElement ||
            document.msFullscreenElement;

        if (fullscreenElement === this.getContainer() && !this._isFullscreen) {
            this._setFullscreen(true);
            this.fire('fullscreenchange');
        } else if (fullscreenElement !== this.getContainer() && this._isFullscreen) {
            this._setFullscreen(false);
            this.fire('fullscreenchange');
        }
    }
});

L.Map.mergeOptions({
    fullscreenControl: false
});

L.Map.addInitHook(function () {
    if (this.options.fullscreenControl) {
        this.fullscreenControl = new L.Control.Fullscreen();
        this.addControl(this.fullscreenControl);
    }

    var fullscreenchange;

    if ('onfullscreenchange' in document) {
        fullscreenchange = 'fullscreenchange';
    } else if ('onmozfullscreenchange' in document) {
        fullscreenchange = 'mozfullscreenchange';
    } else if ('onwebkitfullscreenchange' in document) {
        fullscreenchange = 'webkitfullscreenchange';
    } else if ('onmsfullscreenchange' in document) {
        fullscreenchange = 'MSFullscreenChange';
    }

    if (fullscreenchange) {
        var onFullscreenChange = L.bind(this._onFullscreenChange, this);

        this.whenReady(function () {
            L.DomEvent.on(document, fullscreenchange, onFullscreenChange);
        });

        this.on('unload', function () {
            L.DomEvent.off(document, fullscreenchange, onFullscreenChange);
        });
    }
});

L.control.fullscreen = function (options) {
    return new L.Control.Fullscreen(options);
};

/*
* GPX plugin, Copyright (C) 2011-2012 Pavel Shramov, Copyright (C) 2013 Maxime Petazzoni <maxime.petazzoni@bulix.org>
* License: https://github.com/mpetazzoni/leaflet-gpx/blob/master/LICENSE
*/
var _SECOND_IN_MILLIS = 1000;
var _MINUTE_IN_MILLIS = 60 * _SECOND_IN_MILLIS;
var _HOUR_IN_MILLIS = 60 * _MINUTE_IN_MILLIS;
L.GPX = L.FeatureGroup.extend({
	initialize: function(gpx, options) {
		L.Util.setOptions(this, options);
		if (mapsmarkerjspro.gpx_icons_status == 'show') { //info: added by RH
			L.GPXTrackIcon = L.Icon.extend({ options: options.marker_options });
		}
		this._gpx = gpx;
		this._layers = {};
		this._info = {
			name: null,
			length: 0.0,
			elevation: {gain: 0.0, loss: 0.0, _points: []},
			hr: {avg: 0, _total: 0, _points: []},
			duration: {start: null, end: null, moving: 0, total: 0},
		};
		if (gpx) {
			this._prepare_parsing(gpx, options, this.options.async);
		}
	},

	get_duration_string: function(duration, hidems) {
		var s = '';

		if (duration >= _HOUR_IN_MILLIS) {
			s += Math.floor(duration / _HOUR_IN_MILLIS) + ':';
			duration = duration % _HOUR_IN_MILLIS;
		}

		var mins = Math.floor(duration / _MINUTE_IN_MILLIS);
		duration = duration % _MINUTE_IN_MILLIS;
		if (mins < 10) s += '0';
		s += mins + '\'';

		var secs = Math.floor(duration / _SECOND_IN_MILLIS);
		duration = duration % _SECOND_IN_MILLIS;
		if (secs < 10) s += '0';
		s += secs;

		if (!hidems && duration > 0) s += '.' + Math.round(Math.floor(duration)*1000)/1000;
		else s += '"';
		return s;
	},

	to_miles:            function(v) { return v / 1.60934; },
	to_ft:               function(v) { return v * 3.28084; },
	m_to_km:             function(v) { return v / 1000; },
	m_to_mi:             function(v) { return v / 1609.34; },

	get_name:            function() { return this._info.name; },
	get_distance:        function() { return this._info.length; },
	get_distance_imp:    function() { return this.to_miles(this.m_to_km(this.get_distance())); },

	get_start_time:      function() { return this._info.duration.start; },
	get_end_time:        function() { return this._info.duration.end; },
	get_moving_time:     function() { return this._info.duration.moving; },
	get_total_time:      function() { return this._info.duration.total; },

	get_moving_pace:     function() { return this.get_moving_time() / this.m_to_km(this.get_distance()); },
	get_moving_pace_imp: function() { return this.get_moving_time() / this.get_distance_imp(); },

	get_elevation_gain:     function() { return this._info.elevation.gain; },
	get_elevation_loss:     function() { return this._info.elevation.loss; },
	get_elevation_data:     function() {
		var _this = this;
		return this._info.elevation._points.map(
			function(p) {
				return _this._prepare_data_point(p, _this.m_to_km, null,
				function(a, b) {
					return a.toFixed(2) + ' km, ' + b.toFixed(0) + ' m';
				});
			});
	},
	get_elevation_data_imp: function() {
		var _this = this;
		return this._info.elevation._points.map(
			function(p) {
				return _this._prepare_data_point(p, _this.m_to_mi, _this.to_ft,
				function(a, b) {
					return a.toFixed(2) + ' mi, ' + b.toFixed(0) + ' ft';
				});
			});
	},

	get_average_hr:         function() { return this._info.hr.avg; },
	get_heartrate_data:     function() {
		var _this = this;
		return this._info.hr._points.map(
			function(p) {
				return _this._prepare_data_point(p, _this.m_to_km, null,
				function(a, b) {
					return a.toFixed(2) + ' km, ' + b.toFixed(0) + ' bpm';
				});
			});
	},
	get_heartrate_data_imp: function() {
		var _this = this;
		return this._info.hr._points.map(
			function(p) {
				return _this._prepare_data_point(p, _this.m_to_mi, null,
				function(a, b) {
					return a.toFixed(2) + ' mi, ' + b.toFixed(0) + ' bpm';
				});
			});
	},

	//**************************************************************************/
	// Private methods
	//**************************************************************************/

	_htmlspecialchars_decode: function htmlspecialchars_decode (string, quote_style) {
		// http://kevin.vanzonneveld.net
		// *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
		// *     returns 1: '<p>this -> &quot;</p>'
		// *     example 2: htmlspecialchars_decode("&amp;quot;");
		// *     returns 2: '&quot;'
		var optTemp = 0,
		i = 0,
		noquotes = false;
		if (typeof quote_style === 'undefined') {
			quote_style = 2;
		}
		string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
		var OPTS = {
			'ENT_NOQUOTES': 0,
			'ENT_HTML_QUOTE_SINGLE': 1,
			'ENT_HTML_QUOTE_DOUBLE': 2,
			'ENT_COMPAT': 2,
			'ENT_QUOTES': 3,
			'ENT_IGNORE': 4
		};
		if (quote_style === 0) {
			noquotes = true;
		}
		if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
			quote_style = [].concat(quote_style);
			for (i = 0; i < quote_style.length; i++) {
				// Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
				if (OPTS[quote_style[i]] === 0) {
					noquotes = true;
				} else if (OPTS[quote_style[i]]) {
					optTemp = optTemp | OPTS[quote_style[i]];
				}
			}
			quote_style = optTemp;
		}
		if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
			string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
			// string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
		}
		if (!noquotes) {
			string = string.replace(/&quot;/g, '"');
		}
		// Put this in last place to avoid escape being double-decoded
		string = string.replace(/&amp;/g, '&');

		return string;
	},

	_merge_objs: function(a, b) {
		var _ = {};
		for (var attr in a) { _[attr] = a[attr]; }
		for (var attr in b) { _[attr] = b[attr]; }
		return _;
	},

	_prepare_data_point: function(p, trans1, trans2, trans_tooltip) {
		var r = [trans1 && trans1(p[0]) || p[0], trans2 && trans2(p[1]) || p[1]];
		r.push(trans_tooltip && trans_tooltip(r[0], r[1]) || (r[0] + ': ' + r[1]));
		return r;
	},

	_prepare_parsing: function(input, options, async) {
		var _this = this;
		var cb = function(gpx, options) {
			var layers = _this._parse_gpx_data(gpx, options);
			if (!layers) return;
			_this.addLayer(layers);
			setTimeout(function(){ _this.fire('gpx_loaded') }, 0);
		}

		if (async == undefined) async = this.options.async;
		if (options == undefined) options = this.options;

		var gpx_content_to_parse = this._htmlspecialchars_decode(this.options.gpx_content);
		var xmlDoc = this._parse_xml(gpx_content_to_parse);
		if(xmlDoc){
			cb(this._parse_xml(gpx_content_to_parse), options);
		} else{
			if (window.console) { console.log('error parsing xml'); }
		}
	},

	_parse_xml: function(xmlStr){
		if (typeof window.DOMParser != "undefined") {
			return ( new window.DOMParser() ).parseFromString(xmlStr, "text/xml");
		} else if (typeof window.ActiveXObject != "undefined" && new window.ActiveXObject("Microsoft.XMLDOM")) {
			var xmlDoc = new window.ActiveXObject("Microsoft.XMLDOM");
			xmlDoc.async = "false";
			xmlDoc.loadXML(xmlStr);
			return xmlDoc;
		} else {
			throw new Error("No XML parser found");
		}
	},

	_parse_gpx_data: function(xml, options) {
		var j, i, el, layers = [];
		var tags = [['rte','rtept'], ['trkseg','trkpt']];
		var name = xml.getElementsByTagName('name');
		if (name.length > 0) {
			this._info.name = name[0].textContent || name[0].text || name[0].innerText;
		}
		for (j = 0; j < tags.length; j++) {
			el = xml.getElementsByTagName(tags[j][0]);
			for (i = 0; i < el.length; i++) {
					var coords = this._parse_trkseg(el[i], xml, options, tags[j][1]);
					if (coords.length === 0) continue;

					// add track
					var l = new L.Polyline(coords, options.polyline_options);
					this.fire('addline', { line: l })
					layers.push(l);

					if (mapsmarkerjspro.gpx_icons_status == 'show') { //info: added by RH
						// add start pin
						var p = new L.Marker(coords[0], {
						  clickable: false,
							icon: new L.GPXTrackIcon({iconUrl: options.marker_options.startIconUrl})
						});
						this.fire('addpoint', { point: p });
						layers.push(p);
						// add end pin
						p = new L.Marker(coords[coords.length-1], {
						  clickable: false,
						  icon: new L.GPXTrackIcon({iconUrl: options.marker_options.endIconUrl})
						});
						this.fire('addpoint', { point: p });
						layers.push(p);
					}
			}
		}

		this._info.hr.avg = Math.round(this._info.hr._total / this._info.hr._points.length);

		if (!layers.length) return;
		var layer = layers[0];
		if (layers.length > 1)
		  layer = new L.FeatureGroup(layers);
		return layer;
	},

	_parseDate_for_IE8: function(input) {
		var iso = /^(\d{4})(?:-?W(\d+)(?:-?(\d+)D?)?|(?:-(\d+))?-(\d+))(?:[T ](\d+):(\d+)(?::(\d+)(?:\.(\d+))?)?)?(?:Z(-?\d*))?$/;
		var parts = input.match(iso);
		if (parts == null) {
			throw new Error("Invalid Date");
		}
		var year = Number(parts[1]);
		if (typeof parts[2] != "undefined") {
			/* Convert weeks to days, months 0 */
			var weeks = Number(parts[2]) - 1;
			var days  = Number(parts[3]);
			if (typeof days == "undefined") {
				days = 0;
			}
			days += weeks * 7;
			var months = 0;
		} else {
			if (typeof parts[4] != "undefined") {
				var months = Number(parts[4]) - 1;
			} else {
				/* it's an ordinal date... */
				var months = 0;
			}
			var days   = Number(parts[5]);
		}

		if (typeof parts[6] != "undefined" &&
			typeof parts[7] != "undefined")
		{
			var hours        = Number(parts[6]);
			var minutes      = Number(parts[7]);

			if (typeof parts[8] != "undefined") {
				var seconds      = Number(parts[8]);

				if (typeof parts[9] != "undefined") {
					var fractional   = Number(parts[9]);
					var milliseconds = fractional / 100;
				} else {
					var milliseconds = 0
				}
			} else {
				var seconds      = 0;
				var milliseconds = 0;
			}
		}
		else {
			var hours        = 0;
			var minutes      = 0;
			var seconds      = 0;
			var fractional   = 0;
			var milliseconds = 0;
		}

		if (typeof parts[10] != "undefined") {
			/* Timezone adjustment, offset the minutes appropriately */
			var localzone = -(new Date().getTimezoneOffset());
			var timezone  = parts[10] * 60;
			minutes = Number(minutes) + (timezone - localzone);
		}
		return new Date(year, months, days, hours, minutes, seconds, milliseconds);
	},

	_parse_trkseg: function(line, xml, options, tag) {
		var el = line.getElementsByTagName(tag);
		if (!el.length) return [];
		var coords = [];
		var last = null;

		for (var i = 0; i < el.length; i++) {
			var _, ll = new L.LatLng(
				el[i].getAttribute('lat'),
				el[i].getAttribute('lon'));
			ll.meta = { time: null, ele: null, hr: null };

			_ = el[i].getElementsByTagName('time');
			if (_.length > 0) {
				if (window.addEventListener) {
					var time_temp = Date.parse(_[0].textContent);
					ll.meta.time = new Date(time_temp);
				} else { //IE8
					ll.meta.time = this._parseDate_for_IE8(_[0].text);
				}
			}

			_ = el[i].getElementsByTagName('ele');
			if (_.length > 0) {
				ll.meta.ele = parseFloat(_[0].textContent || _[0].text || _[0].innerText); //IE8
			}

			/*IE9+only _ = el[i].getElementsByTagNameNS('*', 'hr');*/
			_ = el[i].getElementsByTagName('hr');

			if (_.length > 0) {
				ll.meta.hr = parseInt(_[0].textContent || _[0].text || _[0].innerText); //IE8
				this._info.hr._points.push([this._info.length, ll.meta.hr]);
				this._info.hr._total += ll.meta.hr;
			}

			this._info.elevation._points.push([this._info.length, ll.meta.ele]);
			this._info.duration.end = ll.meta.time;

			if (last != null) {
				this._info.length += this._dist3d(last, ll);

				var t = ll.meta.ele - last.meta.ele;
				if (t > 0) this._info.elevation.gain += t;
				else this._info.elevation.loss += Math.abs(t);

				t = Math.abs(ll.meta.time - last.meta.time);
				this._info.duration.total += t;
				if (t < options.max_point_interval) this._info.duration.moving += t;
			} else {
				this._info.duration.start = ll.meta.time;
			}
			last = ll;
			coords.push(ll);
		}
		return coords;
	},

	_dist2d: function(a, b) {
		var R = 6371000;
		var dLat = this._deg2rad(b.lat - a.lat);
		var dLon = this._deg2rad(b.lng - a.lng);
		var r = Math.sin(dLat/2) *
				Math.sin(dLat/2) +
				Math.cos(this._deg2rad(a.lat)) *
				Math.cos(this._deg2rad(b.lat)) *
				Math.sin(dLon/2) *
				Math.sin(dLon/2);
		var c = 2 * Math.atan2(Math.sqrt(r), Math.sqrt(1-r));
		var d = R * c;
		return d;
	},

	_dist3d: function(a, b) {
		var planar = this._dist2d(a, b);
		var height = Math.abs(b.meta.ele - a.meta.ele);
		return Math.sqrt(Math.pow(planar, 2) + Math.pow(height, 2));
	},

	_deg2rad: function(deg) {
		return deg * Math.PI / 180;
	},
});

/*
Copyright (c) 2016 Dominik Moritz, v0.49/14032016
This file is part of the leaflet locate control. It is licensed under the MIT license.
You can find the project at: https://github.com/domoritz/leaflet-locatecontrol
*/
(function (factory, window) {
     // see https://github.com/Leaflet/Leaflet/blob/master/PLUGIN-GUIDE.md#module-loaders
     // for details on how to structure a leaflet plugin.

    // define an AMD module that relies on 'leaflet'
    if (typeof define === 'function' && define.amd) {
        define(['leaflet'], factory);

    // define a Common JS module that relies on 'leaflet'
    } else if (typeof exports === 'object') {
        if (typeof window !== 'undefined' && window.L) {
            module.exports = factory(L);
        } else {
            module.exports = factory(require('leaflet'));
        }
    }

    // attach your plugin to the global 'L' variable
    if(typeof window !== 'undefined' && window.L){
        window.L.Locate = factory(L);
    }

} (function (L) {
    L.Control.Locate = L.Control.extend({
        options: {
            position: 'topleft',
            layer: undefined,  // use your own layer for the location marker
            drawCircle: true,
            follow: false,  // follow with zoom and pan the user's location
            stopFollowingOnDrag: false, // if follow is true, stop following when map is dragged (deprecated)
            // if true locate control remains active on click even if the user's location is in view.
            // clicking control will just pan to location
            remainActive: false,
            markerClass: L.circleMarker, // L.circleMarker or L.marker
            // range circle
            circleStyle: {
                color: '#136AEC',
                fillColor: '#136AEC',
                fillOpacity: 0.15,
                weight: 2,
                opacity: 0.5
            },
            // inner marker
            markerStyle: {
                color: '#136AEC',
                fillColor: '#2A93EE',
                fillOpacity: 0.7,
                weight: 2,
                opacity: 0.9,
                radius: 5
            },
            // changes to range circle and inner marker while following
            // it is only necessary to provide the things that should change
            followCircleStyle: {},
            followMarkerStyle: {
                //color: '#FFA500',
                //fillColor: '#FFB000'
            },
            icon: 'icon-location',  // fa-location-arrow or fa-map-marker //RH-changed
			iconLoading: 'icon-spinner animate-spin', //RH-changed
            iconElementTag: 'span', // span or i
            circlePadding: [0, 0],
            metric: true,
            onLocationError: function(err) {
                // this event is called in case of any location error
                // that is not a time out error.
                alert(err.message);
            },
            onLocationOutsideMapBounds: function(control) {
                // this event is repeatedly called when the location changes
                control.stop();
                alert(control.options.strings.outsideMapBoundsMsg);
            },
            setView: true, // automatically sets the map view to the user's location
            // keep the current map zoom level when displaying the user's location. (if 'false', use maxZoom)
            keepCurrentZoomLevel: false,
            showPopup: true, // display a popup when the user click on the inner marker
            strings: {
                title: "Show me where I am",
                metersUnit: "meters",
                feetUnit: "feet",
                popup: "You are within {distance} {unit} from this point",
                outsideMapBoundsMsg: "You seem located outside the boundaries of the map"
            },
            locateOptions: {
                maxZoom: Infinity,
                watch: true  // if you overwrite this, visualization cannot be updated
            }
        },

        initialize: function (options) {
            L.Map.addInitHook(function () {
                if (this.options.locateControl) {
                    this.addControl(this);
                }
            });

            for (var i in options) {
                if (typeof this.options[i] === 'object') {
                    L.extend(this.options[i], options[i]);
                } else {
                    this.options[i] = options[i];
                }
            }

            L.extend(this.options.locateOptions, {
                setView: false // have to set this to false because we have to
                               // do setView manually
            });
        },

        /**
         * This method launches the location engine.
         * It is called before the marker is updated,
         * event if it does not mean that the event will be ready.
         *
         * Override it if you want to add more functionalities.
         * It should set the this._active to true and do nothing if
         * this._active is not true.
         */
        _activate: function() {
            if (this.options.setView) {
                this._locateOnNextLocationFound = true;
            }

            if(!this._active) {
                this._map.locate(this.options.locateOptions);
            }
            this._active = true;

            if (this.options.follow) {
                this._startFollowing(this._map);
            }
        },

        /**
         * Called to stop the location engine.
         *
         * Override it to shutdown any functionalities you added on start.
         */
        _deactivate: function() {
            this._map.stopLocate();

            this._map.off('dragstart', this._stopFollowing, this);
            if (this.options.follow && this._following) {
                this._stopFollowing(this._map);
            }
        },

        /**
         * Draw the resulting marker on the map.
         *
         * Uses the event retrieved from onLocationFound from the map.
         */
        drawMarker: function(map) {
            if (this._event.accuracy === undefined) {
                this._event.accuracy = 0;
            }

            var radius = this._event.accuracy;
            if (this._locateOnNextLocationFound) {
                if (this._isOutsideMapBounds()) {
                    this.options.onLocationOutsideMapBounds(this);
                } else {
                    // If accuracy info isn't desired, keep the current zoom level
                    if(this.options.keepCurrentZoomLevel) {
                        map.panTo([this._event.latitude, this._event.longitude]);
                    } else {
                        map.fitBounds(this._event.bounds, {
                            padding: this.options.circlePadding,
                            maxZoom: this.options.keepCurrentZoomLevel ?
                            map.getZoom() : this.options.locateOptions.maxZoom
                        });
                    }
                }
                this._locateOnNextLocationFound = false;
            }

            // circle with the radius of the location's accuracy
            var style, o;
            if (this.options.drawCircle) {
                if (this._following) {
                    style = this.options.followCircleStyle;
                } else {
                    style = this.options.circleStyle;
                }

                if (!this._circle) {
                    this._circle = L.circle(this._event.latlng, radius, style)
                    .addTo(this._layer);
                } else {
                    this._circle.setLatLng(this._event.latlng).setRadius(radius);
                    for (o in style) {
                        this._circle.options[o] = style[o];
                    }
                }
            }

            var distance, unit;
            if (this.options.metric) {
                distance = radius.toFixed(0);
                unit =  this.options.strings.metersUnit;
            } else {
                distance = (radius * 3.2808399).toFixed(0);
                unit = this.options.strings.feetUnit;
            }

            // small inner marker
            var mStyle;
            if (this._following) {
                mStyle = this.options.followMarkerStyle;
            } else {
                mStyle = this.options.markerStyle;
            }

            if (!this._marker) {
                this._marker = this.createMarker(this._event.latlng, mStyle)
                .addTo(this._layer);
            } else {
                this.updateMarker(this._event.latlng, mStyle);
            }

            var t = this.options.strings.popup;
            if (this.options.showPopup && t) {
                this._marker.bindPopup(L.Util.template(t, {distance: distance, unit: unit}))
                ._popup.setLatLng(this._event.latlng);
            }

            this._toggleContainerStyle();
        },

        /**
         * Creates the marker.
         *
         * Should return the base marker so it is possible to bind a pop-up if the
         * option is activated.
         *
         * Used by drawMarker, you can ignore it if you have overridden it.
         */
        createMarker: function(latlng, mStyle) {
            return this.options.markerClass(latlng, mStyle);
        },

        /**
         * Updates the marker with current coordinates.
         *
         * Used by drawMarker, you can ignore it if you have overridden it.
         */
        updateMarker: function(latlng, mStyle) {
            this._marker.setLatLng(latlng);
            for (var o in mStyle) {
                this._marker.options[o] = mStyle[o];
            }
        },

        /**
         * Remove the marker from map.
         */
        removeMarker: function() {
            this._layer.clearLayers();
            this._marker = undefined;
            this._circle = undefined;
        },

        onAdd: function (map) {
            var container = L.DomUtil.create('div',
                'leaflet-control-locate leaflet-bar leaflet-control');

            this._layer = this.options.layer || new L.LayerGroup();
            this._layer.addTo(map);
            this._event = undefined;

            // extend the follow marker style and circle from the normal style
            var tmp = {};
            L.extend(tmp, this.options.markerStyle, this.options.followMarkerStyle);
            this.options.followMarkerStyle = tmp;
            tmp = {};
            L.extend(tmp, this.options.circleStyle, this.options.followCircleStyle);
            this.options.followCircleStyle = tmp;

            this._link = L.DomUtil.create('a', 'leaflet-bar-part leaflet-bar-part-single ' + this.options.icon, container); //RH-angepasst
            this._link.href = '#';
            this._link.title = this.options.strings.title;
            this._icon = L.DomUtil.create(this.options.iconElementTag, this.options.icon, this._link);

            L.DomEvent
                .on(this._link, 'click', L.DomEvent.stopPropagation)
                .on(this._link, 'click', L.DomEvent.preventDefault)
                .on(this._link, 'click', function() {
                    var shouldStop = (this._event === undefined ||
                        this._map.getBounds().contains(this._event.latlng) ||
                        !this.options.setView || this._isOutsideMapBounds());
                    if (!this.options.remainActive && (this._active && shouldStop)) {
                        this.stop();
                    } else {
                        this.start();
                    }
                }, this)
                .on(this._link, 'dblclick', L.DomEvent.stopPropagation);

            this._resetVariables();
            this.bindEvents(map);

            return container;
        },

        /**
         * Binds the actions to the map events.
         */
        bindEvents: function(map) {
            map.on('locationfound', this._onLocationFound, this);
            map.on('locationerror', this._onLocationError, this);
            map.on('unload', this.stop, this);
        },

        /**
         * Starts the plugin:
         * - activates the engine
         * - draws the marker (if coordinates available)
         */
        start: function() {
            this._activate();

            if (!this._event) {
                this._setClasses('requesting');
            } else {
                this.drawMarker(this._map);
            }
        },

        /**
         * Stops the plugin:
         * - deactivates the engine
         * - reinitializes the button
         * - removes the marker
         */
        stop: function() {
            this._deactivate();

            this._cleanClasses();
            this._resetVariables();

            this.removeMarker();
        },

        /**
         * Calls deactivate and dispatches an error.
         */
        _onLocationError: function(err) {
            // ignore time out error if the location is watched
            if (err.code == 3 && this.options.locateOptions.watch) {
                return;
            }

            this.stop();
            this.options.onLocationError(err);
        },

        /**
         * Stores the received event and updates the marker.
         */
        _onLocationFound: function(e) {
            // no need to do anything if the location has not changed
            if (this._event &&
                (this._event.latlng.lat === e.latlng.lat &&
                 this._event.latlng.lng === e.latlng.lng &&
                     this._event.accuracy === e.accuracy)) {
                return;
            }

            if (!this._active) {
                return;
            }

            this._event = e;

            if (this.options.follow && this._following) {
                this._locateOnNextLocationFound = true;
            }

            this.drawMarker(this._map);
        },

        /**
         * Dispatches the 'startfollowing' event on map.
         */
        _startFollowing: function() {
            this._map.fire('startfollowing', this);
            this._following = true;
            if (this.options.stopFollowingOnDrag) {
                this._map.on('dragstart', this._stopFollowing, this);
            }
        },

        /**
         * Dispatches the 'stopfollowing' event on map.
         */
        _stopFollowing: function() {
            this._map.fire('stopfollowing', this);
            this._following = false;
            if (this.options.stopFollowingOnDrag) {
                this._map.off('dragstart', this._stopFollowing, this);
            }
            this._toggleContainerStyle();
        },

        /**
         * Check if location is in map bounds
         */
        _isOutsideMapBounds: function() {
            if (this._event === undefined)
                return false;
            return this._map.options.maxBounds &&
                !this._map.options.maxBounds.contains(this._event.latlng);
        },

        /**
         * Toggles button class between following and active.
         */
        _toggleContainerStyle: function() {
            if (!this._container) {
                return;
            }

            if (this._following) {
                this._setClasses('following');
            } else {
                this._setClasses('active');
            }
        },

        /**
         * Sets the CSS classes for the state.
         */
        _setClasses: function(state) {
            if (state == 'requesting') {
                L.DomUtil.removeClasses(this._container, "active following");
                L.DomUtil.addClasses(this._container, "requesting");

                L.DomUtil.removeClasses(this._icon, this.options.icon);
                L.DomUtil.addClasses(this._icon, this.options.iconLoading);
            } else if (state == 'active') {
                L.DomUtil.removeClasses(this._container, "requesting following");
                L.DomUtil.addClasses(this._container, "active");

                L.DomUtil.removeClasses(this._icon, this.options.iconLoading);
                L.DomUtil.addClasses(this._icon, this.options.icon);
            } else if (state == 'following') {
                L.DomUtil.removeClasses(this._container, "requesting");
                L.DomUtil.addClasses(this._container, "active following");

                L.DomUtil.removeClasses(this._icon, this.options.iconLoading);
                L.DomUtil.addClasses(this._icon, this.options.icon);
            }
        },

        /**
         * Removes all classes from button.
         */
        _cleanClasses: function() {
            L.DomUtil.removeClass(this._container, "requesting");
            L.DomUtil.removeClass(this._container, "active");
            L.DomUtil.removeClass(this._container, "following");

            L.DomUtil.removeClasses(this._icon, this.options.iconLoading);
            L.DomUtil.addClasses(this._icon, this.options.icon);
        },

        /**
         * Reinitializes attributes.
         */
        _resetVariables: function() {
            this._active = false;
            this._locateOnNextLocationFound = this.options.setView;
            this._following = false;
        }
    });

    L.control.locate = function (options) {
        return new L.Control.Locate(options);
    };

    (function(){
      // leaflet.js raises bug when trying to addClass / removeClass multiple classes at once
      // Let's create a wrapper on it which fixes it.
      var LDomUtilApplyClassesMethod = function(method, element, classNames) {
        classNames = classNames.split(' ');
        classNames.forEach(function(className) {
            L.DomUtil[method].call(this, element, className);
        });
      };

      L.DomUtil.addClasses = function(el, names) { LDomUtilApplyClassesMethod('addClass', el, names); };
      L.DomUtil.removeClasses = function(el, names) { LDomUtilApplyClassesMethod('removeClass', el, names); };
    })();

    return L.Control.Locate;
}, window));

/*
URL hashes - based on leaflet-hash.js, Copyright (c) 2013 @mlevans, MIT License
https://github.com/mlevans/leaflet-hash
*/
(function(window) {
	var HAS_HASHCHANGE = (function() {
		var doc_mode = window.documentMode;
		return ('onhashchange' in window) &&
			(doc_mode === undefined || doc_mode > 7);
	})();

	L.Hash = function(map) {
		if(typeof window['leaflet_hash_requested'] == 'undefined'){

				this.onHashChange = L.Util.bind(this.onHashChange, this);

				if (map) {
					this.init(map);
				}
				this.events = new Object();
				window['leaflet_hash_requested'] = true;

		}
	};

	L.Hash.parseHash = function(hash) {
		if(hash.indexOf('#') === 0) {
			hash = hash.substr(1);
		}
		var args = hash.split("/");
		if (args.length == 3) {
			var zoom = parseInt(args[0], 10),
			lat = parseFloat(args[1]),
			lon = parseFloat(args[2]);
			if (isNaN(zoom) || isNaN(lat) || isNaN(lon)) {
				return false;
			} else {
				return {
					center: new L.LatLng(lat, lon),
					zoom: zoom
				};
			}
		} else {
			return false;
		}
	};

	L.Hash.formatHash = function(map) {
		var center = map.getCenter(),
		    zoom = map.getZoom(),
		    precision = Math.max(0, Math.ceil(Math.log(zoom) / Math.LN2));

			return "#" + [zoom,
				center.lat.toFixed(precision),
				center.lng.toFixed(precision),
			].join("/");
	},

	L.Hash.prototype = {
		map: null,
		lastHash: null,
		parseHash: L.Hash.parseHash,
		formatHash: L.Hash.formatHash,

		init: function(map) {
			this.map = map;

			// reset the hash
			this.lastHash = null;
			this.onHashChange();
			this.startListening();
		},

		removeFrom: function(map) {
			if (this.changeTimeout) {
				clearTimeout(this.changeTimeout);
			}

			this.stopListening();

			this.map = null;
		},

		onMapMove: function() {
			// bail if we're moving the map (updating from a hash),
			// or if the map is not yet loaded
			if (this.movingMap || !this.map._loaded) {
				return false;
			}
			var args = location.hash.split("/");
			//info: additional check to fix the conflict of possible hashes such as #top #bottom
			var second_char = location.hash.charAt(1);
			if (args.length != 3 && (!isNaN(parseFloat(second_char)) && isFinite(second_char))) {
				return false;
			}
			var hash = this.formatHash(this.map);
			if (this.events['change']) {
				for (var i=0; i<this.events['change'].length; i++) {
					hash = this.events['change'][i](hash);
				}
			}
			if (this.lastHash != hash) {
				location.replace(hash);
				this.lastHash = hash;
				if (this.events['hash']) {
					for (var i=0; i<this.events['hash'].length; i++) {
						this.events['hash'][i](hash);
					}
				}
			}


		},

		movingMap: false,
		update: function() {
			var hash = location.hash;
			if (hash === this.lastHash) {
				return;
			}
			var parsed = this.parseHash(hash);

			if (parsed) {
				this.movingMap = true;

				this.map.setView(parsed.center, parsed.zoom, { animate: false}); //info: PR https://github.com/mlevans/leaflet-hash/pull/37
				if (this.events['update']) {
					for (var i=0; i<this.events['update'].length; i++) {
						this.events['update'][i](hash);
					}
				}
				this.movingMap = false;
			} else {
				this.onMapMove(this.map);
			}
		},
		on: function(event, func) {
			if (! this.events[event]) {
				this.events[event] = [ func ];
			} else {
				this.events[event].push(func);
			}
		},
		off: function(event, func) {
			if (this.events[event]) {
				for (var i=0; i<this.events[event].length; i++) {
					if (this.events[event][i] == func) {
						this.events[event].splice(i);
						return;
					}
				}
			}
		},
		trigger: function(event) {
			if (event == "move") {
				if (! this.movingMap) {
					this.onMapMove();
				}
			}
		},
		// setMovingMap()/clearMovingMap() when making multiple changes that affect hash arguments
		//   ie when moving location and changing visible layers
		setMovingMap: function() {
			this.movingMap = true;
		},
		clearMovingMap: function() {
			this.movingMap = false;
		},
		// defer hash change updates every 100ms
		changeDefer: 100,
		changeTimeout: null,
		onHashChange: function() {
			// throttle calls to update() so that they only happen every
			// `changeDefer` ms
			if (!this.changeTimeout) {
				var that = this;
				this.changeTimeout = setTimeout(function() {
					that.update();
					that.changeTimeout = null;
				}, this.changeDefer);
			}
		},
		isListening: false,
		hashChangeInterval: null,
		startListening: function() {
			if (this.isListening) { return; }
			this.map.on("moveend", this.onMapMove, this);
			if (HAS_HASHCHANGE) {
				L.DomEvent.addListener(window, "hashchange", this.onHashChange);
			} else {
				clearInterval(this.hashChangeInterval);
				this.hashChangeInterval = setInterval(this.onHashChange, 50);
			}
			this.isListening = true;
		},

		stopListening: function() {
			if (!this.isListening) { return; }
			this.map.off("moveend", this.onMapMove, this);
			if (HAS_HASHCHANGE) {
				L.DomEvent.removeListener(window, "hashchange", this.onHashChange);
			} else {
				clearInterval(this.hashChangeInterval);
			}
			this.isListening = false;
		}
	};
	L.hash = function(map) {
		return new L.Hash(map);
	};
	L.Map.include({
		addHash: function(){
			this._hash = L.hash(this);
			return this;
		},

		removeHash: function(){
			this._hash.remove();
			return this;
		}
	});
})(window);

/*
 * Leaflet.MarkerCluster.LayerSupport sub-plugin for Leaflet.markercluster plugin, MIT license (expat type)
 * Copyright (c) 2015 Boris Seang
*/
(function (root, factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['leaflet'], function (L) {
			return (root.L.MarkerClusterGroup.LayerSupport = factory(L));
		});
	} else if (typeof module === 'object' && module.exports) {
		// Node. Does not work with strict CommonJS, but
		// only CommonJS-like environments that support module.exports,
		// like Node.
		module.exports = factory(require('leaflet'));
	} else {
		// Browser globals
		root.L.MarkerClusterGroup.LayerSupport = factory(root.L);
	}
}(this, function (L, undefined) { // Does not actually expect the 'undefined' argument, it is just a trick to have an undefined variable.

	var LMCG = L.MarkerClusterGroup,
	    LMCGproto = LMCG.prototype,
	    EVENTS = L.FeatureGroup.EVENTS;

	/**
	 * Extends the L.MarkerClusterGroup class by mainly overriding methods for
	 * addition/removal of layers, so that they can also be directly added/removed
	 * from the map later on while still clustering in this group.
	 * @type {L.MarkerClusterGroup}
	 */
	var MarkerClusterGroupLayerSupport = LMCG.extend({

		statics: {
			version: '0.1.0'
		},

		options: {
			// Buffer single addLayer and removeLayer requests for efficiency.
			singleAddRemoveBufferDuration: 100 // in ms.
		},

		initialize: function (options) {
			LMCGproto.initialize.call(this, options);

			// Replace the MCG internal featureGroup's so that they directly
			// access the map add/removal methods, bypassing the switch agent.
			this._featureGroup = new _ByPassingFeatureGroup();
			this._featureGroup.on(EVENTS, this._propagateEvent, this);

			this._nonPointGroup = new _ByPassingFeatureGroup();
			this._nonPointGroup.on(EVENTS, this._propagateEvent, this);

			// Keep track of what should be "represented" on map (can be clustered).
			this._layers = {};
			this._proxyLayerGroups = {};
			this._proxyLayerGroupsNeedRemoving = {};

			// Buffer single addLayer and removeLayer requests.
			this._singleAddRemoveBuffer = [];
		},

		/**
		 * Stamps the passed layers as being part of this group, but without adding
		 * them to the map right now.
		 * @param layers L.Layer|Array(L.Layer) layer(s) to be stamped.
		 * @returns {MarkerClusterGroupLayerSupport} this.
		 */
		checkIn: function (layers) {
			var layersArray = this._toArray(layers);

			this._checkInGetSeparated(layersArray);

			return this;
		},

		/**
		 * Un-stamps the passed layers from being part of this group. It has to
		 * remove them from map (if they are) since they will no longer cluster.
		 * @param layers L.Layer|Array(L.Layer) layer(s) to be un-stamped.
		 * @returns {MarkerClusterGroupLayerSupport} this.
		 */
		checkOut: function (layers) {
			var layersArray = this._toArray(layers),
			    separated = this._separateSingleFromGroupLayers(layersArray, {
				    groups: [],
				    singles: []
			    }),
			    groups = separated.groups,
			    singles = separated.singles,
			    i, layer;

			// Un-stamp single layers.
			for (i = 0; i < singles.length; i++) {
				layer = singles[i];
				delete this._layers[L.stamp(layer)];
				delete layer._mcgLayerSupportGroup;
			}

			// Batch remove single layers from MCG.
			// Note: as for standard MCG, if single layers have been added to
			// another MCG in the meantime, their __parent will have changed,
			// so weird things would happen.
			this._originalRemoveLayers(singles);

			// Dismiss Layer Groups.
			for (i = 0; i < groups.length; i++) {
				layer = groups[i];
				this._dismissProxyLayerGroup(layer);
			}

			return this;
		},

		/**
		 * Checks in and adds an array of layers to this group.
		 * Layer Groups are also added to the map to fire their event.
		 * @param layers (L.Layer|L.Layer[]) single and/or group layers to be added.
		 * @returns {MarkerClusterGroupLayerSupport} this.
		 */
		addLayers: function (layers) {
			var layersArray = this._toArray(layers),
			    separated = this._checkInGetSeparated(layersArray),
			    groups = separated.groups,
			    i, group, id;

			// Batch add all single layers.
			this._originalAddLayers(separated.singles);

			// Add Layer Groups to the map so that they are registered there and
			// the map fires 'layeradd' events for them as well.
			for (i = 0; i < groups.length; i++) {
				group = groups[i];
				id = L.stamp(group);
				this._proxyLayerGroups[id] = group;
				delete this._proxyLayerGroupsNeedRemoving[id];
				if (this._map) {
					this._map._originalAddLayer(group);
				}
			}
		},
		addLayer: function (layer) {
			this._bufferSingleAddRemove(layer, "addLayers");
			return this;
		},
		_originalAddLayer: LMCGproto.addLayer,
		_originalAddLayers: LMCGproto.addLayers,

		/**
		 * Removes layers from this group but without check out.
		 * Layer Groups are also removed from the map to fire their event.
		 * @param layers (L.Layer|L.Layer[]) single and/or group layers to be removed.
		 * @returns {MarkerClusterGroupLayerSupport} this.
		 */
		removeLayers: function (layers) {
			var layersArray = this._toArray(layers),
			    separated = this._separateSingleFromGroupLayers(layersArray, {
				    groups: [],
				    singles: []
			    }),
			    groups = separated.groups,
			    singles = separated.singles,
			    i = 0,
			    group, id;

			// Batch remove single layers from MCG.
			this._originalRemoveLayers(singles);

			// Remove Layer Groups from the map so that they are un-registered
			// there and the map fires 'layerremove' events for them as well.
			for (; i < groups.length; i++) {
				group = groups[i];
				id = L.stamp(group);
				delete this._proxyLayerGroups[id];
				if (this._map) {
					this._map._originalRemoveLayer(group);
				} else {
					this._proxyLayerGroupsNeedRemoving[id] = group;
				}
			}

			return this;
		},
		removeLayer: function (layer) {
			this._bufferSingleAddRemove(layer, "removeLayers");
			return this;
		},
		_originalRemoveLayer: LMCGproto.removeLayer,
		_originalRemoveLayers: LMCGproto.removeLayers,

		onAdd: function (map) {
			// Replace the map addLayer and removeLayer methods to place the
			// switch agent that redirects layers when required.
			map._originalAddLayer = map._originalAddLayer || map.addLayer;
			map._originalRemoveLayer = map._originalRemoveLayer || map.removeLayer;
			L.extend(map, _layerSwitchMap);

			// As this plugin allows the Application to add layers on map, some
			// checked in layers might have been added already, whereas LayerSupport
			// did not have a chance to inject the switch agent in to the map
			// (if it was never added to map before). Therefore we need to
			// remove all checked in layers from map!
			var toBeReAdded = this._removePreAddedLayers(map),
			    id, group, i;

			// Normal MCG onAdd.
			LMCGproto.onAdd.call(this, map);

			// If layer Groups are added/removed from this group while it is not
			// on map, Control.Layers gets out of sync until this is added back.

			// Restore proxy Layer Groups that may have been added to this
			// group while it was off map.
			for (id in this._proxyLayerGroups) {
				group = this._proxyLayerGroups[id];
				map._originalAddLayer(group);
			}

			// Remove proxy Layer Groups that may have been removed from this
			// group while it was off map.
			for (id in this._proxyLayerGroupsNeedRemoving) {
				group = this._proxyLayerGroupsNeedRemoving[id];
				map._originalRemoveLayer(group);
				delete this._proxyLayerGroupsNeedRemoving[id];
			}

			// Restore Layers.
			for (i = 0; i < toBeReAdded.length; i++) {
				map.addLayer(toBeReAdded[i]);
			}
		},

		// Do not restore the original map methods when removing the group from it.
		// Leaving them as-is does not harm, whereas restoring the original ones
		// may kill the functionality of potential other LayerSupport groups on
		// the same map. Therefore we do not need to override onRemove.

		_bufferSingleAddRemove: function (layer, operationType) {
			var duration = this.options.singleAddRemoveBufferDuration,
				fn;

			if (duration > 0) {
				this._singleAddRemoveBuffer.push({
					type: operationType,
					layer: layer
				});

				if (!this._singleAddRemoveBufferTimeout) {
					fn = L.bind(this._processSingleAddRemoveBuffer, this);

					this._singleAddRemoveBufferTimeout = setTimeout(fn, duration);
				}
			} else { // If duration <= 0, process synchronously.
				this[operationType](layer);
			}
		},
		_processSingleAddRemoveBuffer: function () {
			// For now, simply cut the processes at each operation change
			// (addLayers, removeLayers).
			var singleAddRemoveBuffer = this._singleAddRemoveBuffer,
			    i = 0,
			    layersBuffer = [],
			    currentOperation,
			    currentOperationType;

			for (; i < singleAddRemoveBuffer.length; i++) {
				currentOperation = singleAddRemoveBuffer[i];
				if (!currentOperationType) {
					currentOperationType = currentOperation.type;
				}
				if (currentOperation.type === currentOperationType) {
					layersBuffer.push(currentOperation.layer);
				} else {
					this[currentOperationType](layersBuffer);
					layersBuffer = [currentOperation.layer];
				}
			}
			this[currentOperationType](layersBuffer);
			singleAddRemoveBuffer.length = 0;
			clearTimeout(this._singleAddRemoveBufferTimeout);
			this._singleAddRemoveBufferTimeout = null;
		},

		_checkInGetSeparated: function (layersArray) {
			var separated = this._separateSingleFromGroupLayers(layersArray, {
				    groups: [],
				    singles: []
			    }),
			    groups = separated.groups,
			    singles = separated.singles,
			    i, layer;

			// Recruit Layer Groups.
			// If they do not already belong to this group, they will be
			// removed from map (together will all child layers).
			for (i = 0; i < groups.length; i++) {
				layer = groups[i];
				this._recruitLayerGroupAsProxy(layer);
			}

			// Stamp single layers.
			for (i = 0; i < singles.length; i++) {
				layer = singles[i];

				// Remove from previous group first.
				this._removeFromOtherGroupsOrMap(layer);

				this._layers[L.stamp(layer)] = layer;
				layer._mcgLayerSupportGroup = this;
			}

			return separated;
		},

		_separateSingleFromGroupLayers: function (inputLayers, output) {
			var groups = output.groups,
			    singles = output.singles,
			    isArray = L.Util.isArray,
			    layer;

			for (var i = 0; i < inputLayers.length; i++) {
				layer = inputLayers[i];

				if (layer instanceof L.LayerGroup) {
					groups.push(layer);
					this._separateSingleFromGroupLayers(layer.getLayers(), output);
					continue;
				} else if (isArray(layer)) {
					this._separateSingleFromGroupLayers(layer, output);
					continue;
				}

				singles.push(layer);
			}

			return output;
		},

		// Recruit the LayerGroup as a proxy, so that any layer that is added
		// to / removed from that group later on is also added to / removed from
		// this group.
		// Check in and addition of already contained markers must be taken care
		// of externally.
		_recruitLayerGroupAsProxy: function (layerGroup) {
			var otherMcgLayerSupportGroup = layerGroup._proxyMcgLayerSupportGroup;

			// If it is not yet in this group, remove it from previous group
			// or from map.
			if (otherMcgLayerSupportGroup) {
				if (otherMcgLayerSupportGroup === this) {
					return;
				}
				// Remove from previous Layer Support group first.
				// It will also be removed from map with child layers.
				otherMcgLayerSupportGroup.checkOut(layerGroup);
			} else {
				this._removeFromOwnMap(layerGroup);
			}

			layerGroup._proxyMcgLayerSupportGroup = this;
			layerGroup._originalAddLayer =
				layerGroup._originalAddLayer || layerGroup.addLayer;
			layerGroup._originalRemoveLayer =
				layerGroup._originalRemoveLayer || layerGroup.removeLayer;
			L.extend(layerGroup, _proxyLayerGroup);
		},

		// Restore the normal LayerGroup behaviour.
		// Removal and check out of contained markers must be taken care of externally.
		_dismissProxyLayerGroup: function (layerGroup) {
			if (layerGroup._proxyMcgLayerSupportGroup === undefined ||
				layerGroup._proxyMcgLayerSupportGroup !== this) {

				return;
			}

			delete layerGroup._proxyMcgLayerSupportGroup;
			layerGroup.addLayer = layerGroup._originalAddLayer;
			layerGroup.removeLayer = layerGroup._originalRemoveLayer;

			var id = L.stamp(layerGroup);
			delete this._proxyLayerGroups[id];
			delete this._proxyLayerGroupsNeedRemoving[id];

			this._removeFromOwnMap(layerGroup);
		},

		_removeFromOtherGroupsOrMap: function (layer) {
			var otherMcgLayerSupportGroup = layer._mcgLayerSupportGroup;

			if (otherMcgLayerSupportGroup) { // It is a Layer Support group.
				if (otherMcgLayerSupportGroup === this) {
					return;
				}
				otherMcgLayerSupportGroup.checkOut(layer);

			} else if (layer.__parent) { // It is in a normal MCG.
				layer.__parent._group.removeLayer(layer);

			} else { // It could still be on a map.
				this._removeFromOwnMap(layer);
			}
		},

		// Remove layers that are being checked in, because they can now cluster.
		_removeFromOwnMap: function (layer) {
			if (layer._map) {
				// This correctly fires layerremove event for Layer Groups as well.
				layer._map.removeLayer(layer);
			}
		},

		// In case checked in layers have been added to map whereas map is not redirected.
		_removePreAddedLayers: function (map) {
			var layers = this._layers,
			    toBeReAdded = [],
				layer;

			for (var id in layers) {
				layer = layers[id];
				if (layer._map) {
					toBeReAdded.push(layer);
					map._originalRemoveLayer(layer);
				}
			}

			return toBeReAdded;
		},

		_toArray: function (item) {
			return L.Util.isArray(item) ? item : [item];
		}

	});

	/**
	 * Extends the FeatureGroup by overriding add/removal methods that directly
	 * access the map original methods, bypassing the switch agent.
	 * Used internally in Layer Support for _featureGroup and _nonPointGroup only.
	 * @type {L.FeatureGroup}
	 * @private
	 */
	var _ByPassingFeatureGroup = L.FeatureGroup.extend({

		// Re-implement just to change the map method.
		addLayer: function (layer) {
			if (this.hasLayer(layer)) {
				return this;
			}

			if ('on' in layer) {
				layer.on(EVENTS, this._propagateEvent, this);
			}

			var id = L.stamp(layer);

			this._layers[id] = layer;

			if (this._map) {
				// Use the original map addLayer.
				this._map._originalAddLayer(layer);
			}

			if (this._popupContent && layer.bindPopup) {
				layer.bindPopup(this._popupContent, this._popupOptions);
			}

			return this.fire('layeradd', {layer: layer});
		},

		// Re-implement just to change the map method.
		removeLayer: function (layer) {
			if (!this.hasLayer(layer)) {
				return this;
			}
			if (layer in this._layers) {
				layer = this._layers[layer];
			}

			if ('off' in layer) {
				layer.off(EVENTS, this._propagateEvent, this);
			}

			var id = L.stamp(layer);

			if (this._map && this._layers[id]) {
				// Use the original map removeLayer.
				this._map._originalRemoveLayer(this._layers[id]);
			}

			delete this._layers[id];

			if (this._popupContent) {
				this.invoke('unbindPopup');
			}

			return this.fire('layerremove', {layer: layer});
		},

		onAdd: function (map) {
			this._map = map;
			// Use the original map addLayer.
			this.eachLayer(map._originalAddLayer, map);
		},

		onRemove: function (map) {
			// Use the original map removeLayer.
			this.eachLayer(map._originalRemoveLayer, map);
			this._map = null;
		}

	});

	/**
	 * Toolbox to equip LayerGroups recruited as proxy.
	 * @type {{addLayer: Function, removeLayer: Function}}
	 * @private
	 */
	var _proxyLayerGroup = {

		// Re-implement to redirect addLayer to Layer Support group instead of map.
		addLayer: function (layer) {
			var id = this.getLayerId(layer);

			this._layers[id] = layer;

			if (this._map) {
				this._proxyMcgLayerSupportGroup.addLayer(layer);
			} else {
				this._proxyMcgLayerSupportGroup.checkIn(layer);
			}

			return this;
		},

		// Re-implement to redirect removeLayer to Layer Support group instead of map.
		removeLayer: function (layer) {

			var id = layer in this._layers ? layer : this.getLayerId(layer);

			this._proxyMcgLayerSupportGroup.removeLayer(layer);

			delete this._layers[id];

			return this;
		}

	};

	/**
	 * Toolbox to equip the Map with a switch agent that redirects layers
	 * addition/removal to their Layer Support group when defined.
	 * @type {{addLayer: Function, removeLayer: Function}}
	 * @private
	 */
	var _layerSwitchMap = {

		addLayer: function (layer) {
			if (layer._mcgLayerSupportGroup) {
				// Use the original MCG addLayer.
				return layer._mcgLayerSupportGroup._originalAddLayer(layer);
			}

			return this._originalAddLayer(layer);
		},

		removeLayer: function (layer) {
			if (layer._mcgLayerSupportGroup) {
				// Use the original MCG removeLayer.
				return layer._mcgLayerSupportGroup._originalRemoveLayer(layer);
			}

			return this._originalRemoveLayer(layer);
		}

	};

	// Supply with a factory for consistency with Leaflet.
	L.markerClusterGroup.layerSupport = function (options) {
		return new L.MarkerClusterGroup.LayerSupport(options);
	};

	// Just return a value to define the module export.
	return MarkerClusterGroupLayerSupport;
}));
/*
Leaflet zoom control with a home button for resetting the view.
*/
(function () {
    "use strict";

    L.Control.ZoomHome = L.Control.extend({
        options: {
            position: 'topleft',
            mapId: '',
            mapnameJS: '',
            ondemand: false,
            zoomHomeTitle: 'Home',
            homeCoordinates: null,
            homeZoom: null,
            reenableClustering: false
        },

        onAdd: function (map) {
            var controlName = 'leaflet-control-zoomhome',
                container = L.DomUtil.create('div', controlName + ' leaflet-bar'),
                options = this.options;

            container.setAttribute('id', 'leaflet-control-zoomhome-' + options.mapId);
            if(options.ondemand == true){
            	container.style.display = 'none';
            }
            if (options.homeCoordinates === null) {
                options.homeCoordinates = map.getCenter();
            }
            if (options.homeZoom === null) {
                options.homeZoom = map.getZoom();
            }

            var zoomHomeText = '<span class="lmm-icon-zoomhome"></span>';
            this._zoomHomeButton = this._createButton(zoomHomeText, options.zoomHomeTitle,
                controlName + '-home lmm-icon-zoomhome', container, this._zoomHome.bind(this));
            return container;
        },

        _zoomHome: function (e) {
            //jshint unused:false 
			var reenableClustering = this.options.reenableClustering;
            this._map.closePopup();
            this._map.setView(this.options.homeCoordinates, this.options.homeZoom, {reset:true,animate:false});
			if (reenableClustering === 'true') {
				window['markercluster_' + this.options.mapnameJS].enableClustering();
			}
            var mapId = this.options.mapId;
            var ondemand = this.options.ondemand;
            setTimeout(function(){
            	if(ondemand == true){
             		document.getElementById('leaflet-control-zoomhome-' + mapId).style.display = 'none';
            	}
            },500);
        },
        _createButton: function (html, title, className, container, fn) {
			var link = L.DomUtil.create('a', className, container);
			link.innerHTML = html;
			link.href = '#';
			link.title = title;

			L.DomEvent
			    .on(link, 'mousedown dblclick', L.DomEvent.stopPropagation)
			    .on(link, 'click', L.DomEvent.stop)
			    .on(link, 'click', fn, this)
			    .on(link, 'click', this._refocusOnMap, this);

			return link;
		}
    });

    L.Control.zoomHome = function (options) {
        return new L.Control.ZoomHome(options);
    };
}());
/*
Javascript Events API for LeafletJS, https://mapsmarker.com/jseventsapi/
*/
var MMP = {
	maps:{
		byId: function(map_id){
				return window[MMP.maps[map_id]];
		},
		onAll: function(event, handler){
			jQuery.each(MMP.maps,function(key, map){
				if(!isNaN(key)){
					window[map].on(event, handler);
				}
			});
		},
		toAll: function(callback){
			jQuery.each(MMP.maps,function(key, map){
				if(!isNaN(key)){
					callback(window[map]);
				}
			});
		}
	},
};

/*
 Maps filters
*/
L.Control.Filters = L.Control.Layers.extend({
	options: {
		position: 'topright',
		autoZIndex: true,
		hideSingleBase: false
	},
	_addItem: function (obj) {
		var label = document.createElement('label'),
		    checked = this._map.hasLayer(obj.layer),
		    input;

		if (obj.overlay) {
			input = document.createElement('input');
			input.type = 'checkbox';
			input.id = (obj.layer["layer_id"]);
			input.setAttribute("markercount",obj.layer["markercount"]);
			input.className = 'leaflet-control-layers-selector lmm-filter';
			input.defaultChecked = checked;
		} else {
			input = this._createRadioElement('leaflet-base-layers', checked);
		}

		input.layerId = L.stamp(obj.layer);

		L.DomEvent.on(input, 'click', this._onInputClick, this);

		var name = document.createElement('span');
		name.innerHTML = ' ' + obj.name;

		// Helps from preventing layer control flicker when checkboxes are disabled
		// https://github.com/Leaflet/Leaflet/issues/2771
		var holder = document.createElement('div');

		label.appendChild(holder);
		holder.appendChild(input);
		holder.appendChild(name);

		var container = obj.overlay ? this._overlaysList : this._baseLayersList;
		container.appendChild(label);

		//this._checkDisabledLayers();
		return label;
	},
	_initLayout: function () {
		var className = 'leaflet-control-layers',
		    container = this._container = L.DomUtil.create('div', className);

		//Makes this work on IE10 Touch devices by stopping it from firing a mouseout event when the touch is released
		container.setAttribute('aria-haspopup', true);

		if (!L.Browser.touch) {
			L.DomEvent
				.disableClickPropagation(container)
				.disableScrollPropagation(container);
		} else {
			L.DomEvent.on(container, 'click', L.DomEvent.stopPropagation);
		}

		var form = this._form = L.DomUtil.create('form', className + '-list');

		if (this.options.collapsed) {
			if (!L.Browser.android) {
				L.DomEvent
				    .on(container, 'mouseover', this._expand, this)
				    .on(container, 'mouseout', this._collapse, this);
			}
			var link = this._layersLink = L.DomUtil.create('a', className + '-toggle lmm-filters-icon' , container);
			link.href = '#';
			link.title = 'Layers';

			if (L.Browser.touch) {
				L.DomEvent
				    .on(link, 'click', L.DomEvent.stop)
				    .on(link, 'click', this._expand, this);
			}
			else {
				L.DomEvent.on(link, 'focus', this._expand, this);
			}
			//Work around for Firefox android issue https://github.com/Leaflet/Leaflet/issues/2033
			L.DomEvent.on(form, 'click', function () {
				setTimeout(L.bind(this._onInputClick, this), 0);
			}, this);

			this._map.on('click', this._collapse, this);
			// TODO keyboard accessibility
		} else {
			this._expand();
		}

		this._baseLayersList = L.DomUtil.create('div', className + '-base', form);
		this._separator = L.DomUtil.create('div', className + '-separator', form);
		this._overlaysList = L.DomUtil.create('div', className + '-overlays', form);

		container.appendChild(form);
	},
});
L.control.filters = function (baseLayers, overlays, options) {
	return new L.Control.Filters(baseLayers, overlays, options);
};

/*
dynamic markers list pagination
*/
jQuery(document).on('click','a.first-page',function(e,page_number){
	e.preventDefault();
	mmp_askForMarkersFromPagination(this);
});
function mmp_askForMarkersFromPagination(element,page_number,mapid){
		if(element != null){
			if(jQuery(element).hasClass('current-page')){
				return;
			}
		}
		if(!page_number){
			var page_number = jQuery(element).html();
		}
		if(!mapid){
			var mapid = jQuery(element).attr('data-mapid');
		}
		var per_page = jQuery("#markers_per_page_" + mapid).val();
		//info: consider the search field
		if(typeof jQuery('#search_markers_' + mapid).val()!= "undefined"){
			var search_text = jQuery('#search_markers_' + mapid).val();
		}else{
			var search_text = '';
		}
		if(typeof jQuery('#id').val()!= "undefined"){
			var id = jQuery('#id').val();
		}else{
			var id = jQuery('#' + mapid + '_id' ).val();
		}
		var mapname = jQuery('#lmm_listmarkers_table_' + mapid).attr('data-mapname');
		var layerlat = '';
		var layerlon = '';
		if(typeof window['mmp_cache_current_location_' + mapid] != "undefined"){
			var layerlat = window['mmp_cache_current_location_' + mapid].latitude;
			var layerlon = window['mmp_cache_current_location_' + mapid].longitude;
		}
		if(element != null){
			var page_link_element = element;
		}else{
			var page_link_element = '#pagination_' + mapid + ' .first-page:first';
		}
		jQuery.ajax({
			url:lmm_ajax_vars.lmm_ajax_url,
			data: {
				action: 'mapsmarker_ajax_actions_frontend',
				lmm_ajax_subaction: 'lmm_list_markers',
				lmm_ajax_nonce: lmm_ajax_vars.lmm_ajax_nonce,
				paged: page_number,
				order_by: jQuery('#'+mapid+'_orderby').val(),
				order: jQuery('#'+mapid+'_order').val(),
				multi_layer_map:jQuery('#'+mapid+'_multi_layer_map').val(),
				multi_layer_map_list:jQuery('#'+mapid+'_multi_layer_map_list').val(),
				markercount:jQuery('#'+mapid+'_markercount').val(),
				mapid:mapid,
				layerlat:layerlat,
				layerlon:layerlon,
				per_page: per_page,
				mapname: mapname,
				search_text: search_text,
				id:id,
			},
			beforeSend: function(){
				if(mapid){
					jQuery('.lmm-filter').attr("disabled","disabled");
				}
				if(search_text != ''){
					jQuery('#search_markers_' + mapid).addClass('searchtext_loading');
				}
				jQuery('#pagination_' + mapid).html('<img src="'+   lmm_ajax_vars.lmm_ajax_leaflet_plugin_url	+'inc/img/paging-ajax-loader.gif"/>');
			},
			method:'POST',
			success: function(response){
				var results = response.replace(/^\s*[\r\n]/gm, '');
				var results = results.match(/!!LMM-AJAX-START!!(.*[\s\S]*)!!LMM-AJAX-END!!/)[1];
				var res = JSON.parse(results);
				jQuery('#lmm_listmarkers_table_' + mapid).html(res.rows);
				jQuery('#lmm_listmarkers_table_' + mapid).append( '<tr id="pagination_row_'+mapid+'"><td colspan="2" style="text-align:center"><div class="tablenav"><div id="pagination_' + mapid +'" class="tablenav-pages">' + res.pager + '</div></div></td></tr>');
				jQuery(page_link_element).addClass('current-page');
				if(search_text == ''){
					try{
						window['mmp_calculate_total_markers_'+ mapid]();
					}catch(e){}
				}
				if(mapid){
					jQuery('.lmm-filter').removeAttr("disabled");
				}
			}
		});
}

/*
dynamic markers per page
*/
jQuery(document).on('change','.lmm-per-page-input',function(e){
		var per_page = parseInt(jQuery(this).val());
		var mapid = jQuery(this).attr('data-mapid');
		if(typeof jQuery('#id').val()!= "undefined"){
			var id = jQuery('#id').val();
		}else{
			var id = jQuery( '#' + mapid + '_id' ).val();
		}
		var search_text = jQuery('#search_markers_' + mapid).val();
		if(!isNaN(per_page)){
			jQuery('.current-page').removeClass('current-page');
			var page_link_element = this;
			jQuery.ajax({
				url:lmm_ajax_vars.lmm_ajax_url,
				data: {
					action: 'mapsmarker_ajax_actions_frontend',
					lmm_ajax_subaction: 'lmm_list_markers',
					lmm_ajax_nonce: lmm_ajax_vars.lmm_ajax_nonce,
					paged: 1,
					order_by: jQuery('#'+mapid+'_orderby').val(),
					order: jQuery('#'+mapid+'_order').val(),
					multi_layer_map:jQuery('#'+mapid+'_multi_layer_map').val(),
					multi_layer_map_list:jQuery('#'+mapid+'_multi_layer_map_list').val(),
					markercount:jQuery('#'+mapid+'_markercount').val(),
					mapid:mapid,
					per_page: per_page,
					search_text:search_text,
					id:id,
				},
				beforeSend: function(){
					jQuery('#pagination_' + mapid).html('<img src="'+   lmm_ajax_vars.lmm_ajax_leaflet_plugin_url	+'inc/img/paging-ajax-loader.gif"/>');
					if(search_text != ''){
						jQuery('#search_markers_' + mapid).addClass('searchtext_loading');
					}
				},
				method:'POST',
				success: function(response){
					var results = response.replace(/^\s*[\r\n]/gm, '');
					var results = results.match(/!!LMM-AJAX-START!!(.*[\s\S]*)!!LMM-AJAX-END!!/)[1];
					var res = JSON.parse(results);
					if(typeof res.pager != 'undefined'){
						jQuery('#lmm_listmarkers_table_' + mapid).html(res.rows);
						jQuery('#lmm_listmarkers_table_' + mapid).append( '<tr id="pagination_row_'+mapid+'"><td colspan="2" style="text-align:center"><div class="tablenav"><div id="pagination_' + mapid+ '" class="tablenav-pages">' + res.pager + '</div></div></td></tr>');
					}else{
						jQuery('#pagination_' + mapid).html('');
					}
					jQuery(page_link_element).addClass('current-page');
					if(search_text==''){
						try{
							window['mmp_calculate_total_markers_'+ mapid]();
						}catch(e){}
					}
				}
			});
		}
});

/*
dynamic search
*/
/** info: debounce function to optimize the search field ajax requests. **/
function mmp_debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};
jQuery(document).on('keyup','.lmm-search-markers',mmp_debounce(mmp_askForMarkers,500));
function mmp_askForMarkers(ev){
	if(jQuery(ev.target).val().length > 0){
		var inp = String.fromCharCode(ev.which);
		if (/[a-zA-Z0-9-_ ]/.test(inp) || ev.which == 13){
			var mapid = jQuery(ev.target).attr('data-mapid');
			mmp_get_markers(ev.target, mapid);
		}
	}else if(jQuery(ev.target).val().length == 0){
		if(ev.which == 17 || ev.which == 8){
			var mapid = jQuery(ev.target).attr('data-mapid');
			mmp_get_markers(ev.target, mapid);
		}
	}
}
jQuery(document).on('click','.lmm-sort-by',mmp_debounce(mmp_askForMarkersFromDropdown,500));
function mmp_askForMarkersFromDropdown(ev){
	var mapid = jQuery(ev.target).parent().attr('data-mapid');
	var order_by = jQuery(ev.target).attr('data-sortby');
	var mapname = jQuery('#lmm_listmarkers_table_' + mapid).attr('data-mapname');
	if(order_by == 'distance_current_position'){
		if(typeof window['mmp_cache_current_location_' + mapid] != "undefined"){
			mmp_get_markers(ev.target, mapid, window['mmp_cache_current_location_' + mapid]);
		}else{
			if(typeof window['locatecontrol_' + mapname] != "undefined"){
				window['locatecontrol_' + mapname].start();
				window[mapname].on('locationfound', function(location){
					if(typeof window['mmp_cache_current_location_' + mapid] == "undefined"){
						window['mmp_cache_current_location_' + mapid] = location;
						mmp_get_markers(ev.target, mapid, location);
					}
				});
			}else{
				window[mapname].locate().on('locationfound', function(location){
					if(typeof window['mmp_cache_current_location_' + mapid] == "undefined"){
						window['mmp_cache_current_location_' + mapid] = location;
						mmp_get_markers(ev.target, mapid, location);
					}
				});
			}
		}
	}else{
		mmp_get_markers(ev.target, mapid);
	}
}
function mmp_get_markers(element, mapid, location){
	var search_text = jQuery('#search_markers_' + mapid).val();
	var order_by = jQuery(element).attr('data-sortby');
	var mapname = jQuery('#lmm_listmarkers_table_' + mapid).attr('data-mapname');
	if(typeof jQuery('#id').val()!= "undefined"){
		var id = jQuery('#id').val();
	}else{
		var id = jQuery( '#' + mapid + '_id' ).val();
	}
	//info: in case of order by current center
	if(location){
		var layerlat = location.latitude;
		var layerlon = location.longitude;
	}
	if(jQuery(element).hasClass('lmm-sort-by') && jQuery(element).hasClass('up')){
		var order = 'desc';
		jQuery('.lmm-sort-by').removeClass('up');
		jQuery('.lmm-sort-by').removeClass('down');
		jQuery(element).removeClass('up');
		jQuery(element).addClass('down');
	}else{
		var order = 'asc';
		jQuery('.lmm-sort-by').removeClass('up');
		jQuery('.lmm-sort-by').removeClass('down');
		jQuery(element).removeClass('down');
		jQuery(element).addClass('up');
	}
		var per_page = parseInt(jQuery('#markers_per_page_' + mapid).val());
		jQuery('.current-page').removeClass('current-page');
		var page_link_element = element;
		jQuery.ajax({
			url:lmm_ajax_vars.lmm_ajax_url,
			data: {
				action: 'mapsmarker_ajax_actions_frontend',
				lmm_ajax_subaction: 'lmm_list_markers',
				lmm_ajax_nonce: lmm_ajax_vars.lmm_ajax_nonce,
				paged: 1,
				multi_layer_map:jQuery('#'+mapid+'_multi_layer_map').val(),
				multi_layer_map_list:jQuery('#'+mapid+'_multi_layer_map_list').val(),
				markercount:jQuery('#'+mapid+'_markercount').val(),
				mapid:mapid,
				per_page: per_page,
				search_text:search_text,
				order_by: order_by,
				order: order,
				layerlat:layerlat,
				layerlon:layerlon,
				mapname: mapname,
				id:id
			},
			beforeSend: function(){
				jQuery('#pagination_' + mapid).html('<img src="'+   lmm_ajax_vars.lmm_ajax_leaflet_plugin_url	+'inc/img/paging-ajax-loader.gif"/>');
				if(search_text != ''){
					jQuery('#search_markers_' + mapid).addClass('searchtext_loading');
				}
			},
			method:'POST',
			success: function(response){
				var results = response.replace(/^\s*[\r\n]/gm, '');
				var results = results.match(/!!LMM-AJAX-START!!(.*[\s\S]*)!!LMM-AJAX-END!!/)[1];
				var res = JSON.parse(results);

				jQuery('#lmm_listmarkers_table_' + mapid).html(res.rows);
				if(res.no_pagination == true){
				if(search_text!='' && res.mcount==0){
						jQuery('#lmm_listmarkers_table_' + mapid).append( '<tr id="pagination_row_'+mapid+'"><td colspan="2" style="text-align:center"><div class="tablenav">'+ lmm_ajax_vars.lmm_ajax_text_no_results_found +'</div></td></tr>');
					}else{
						jQuery('#lmm_listmarkers_table_' + mapid).append( '<tr id="pagination_row_'+mapid+'" style="display:none;"><td colspan="2" style="text-align:center"><div class="tablenav"><div id="pagination_' + mapid +'" class="tablenav-pages">' + res.pager + '</div></div></td></tr>');
					}
				}else{
					jQuery('#lmm_listmarkers_table_' + mapid).append( '<tr id="pagination_row_'+mapid+'"><td colspan="2" style="text-align:center"><div class="tablenav"><div id="pagination_' + mapid +'" class="tablenav-pages">' + res.pager + '</div></div></td></tr>');
				}
				jQuery(page_link_element).addClass('current-page');
				if(search_text!=''){
					//info: re-focus on the search field
					jQuery('#search_markers_' + mapid).focus();
					var tmpStr = jQuery('#search_markers_' + mapid).val();
					jQuery('#search_markers_' + mapid).val('');
					jQuery('#search_markers_' + mapid).val(tmpStr);
				}else{
					try{
						window['mmp_calculate_total_markers_'+ mapid]();
					}catch(e){}
				}
			}
		});
}
/**
 * Leaflet.MarkerCluster.Freezable sub-plugin for Leaflet.markercluster plugin.
 * Adds the ability to freeze clusters at a specified zoom.
 * Copyright (c) 2015 Boris Seang
 * https://github.com/ghybs/Leaflet.MarkerCluster.Freezable
 * Distributed under the MIT License (Expat type)
 * Last commit: Dec 3, 2015
 */

// UMD
(function (root, factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['leaflet'], function (L) {
			return (root.L.MarkerClusterGroup = factory(L));
		});
	} else if (typeof module === 'object' && module.exports) {
		// Node. Does not work with strict CommonJS, but
		// only CommonJS-like environments that support module.exports,
		// like Node.
		module.exports = factory(require('leaflet'));
	} else {
		// Browser globals
		root.L.MarkerClusterGroup = factory(root.L);
	}
}(this, function (L, undefined) { // Does not actually expect the 'undefined' argument, it is just a trick to have an undefined variable.

	var LMCG = L.MarkerClusterGroup,
	    LMCGproto = LMCG.prototype;

	LMCG.freezableVersion = '0.1.0';

	LMCG.include({

		_originalOnAdd: LMCGproto.onAdd,

		onAdd: function (map) {
			var frozenZoom = this._zoom;

			this._originalOnAdd(map);

			if (this._frozen) {

				// Restore the specified frozenZoom if necessary.
				if (frozenZoom >= 0 && frozenZoom !== this._zoom) {
					// Undo clusters and markers addition to this._featureGroup.
					this._featureGroup.clearLayers();

					this._zoom = frozenZoom;

					this.addLayers([]);
				}

				// Replace the callbacks on zoomend and moveend events.
				map.off('zoomend', this._zoomEnd, this);
				map.off('moveend', this._moveEnd, this);
				map.on('zoomend moveend', this._viewChangeEndNotClustering, this);
			}
		},

		_originalOnRemove: LMCGproto.onRemove,

		onRemove: function (map) {
			map.off('zoomend moveend', this._viewChangeEndNotClustering, this);
			this._originalOnRemove(map);
		},

		disableClustering: function () {
			return this.freezeAtZoom(this._maxZoom + 1);
		},

		disableClusteringKeepSpiderfy: function () {
			return this.freezeAtZoom(this._maxZoom);
		},

		enableClustering: function () {
			return this.unfreeze();
		},

		unfreeze: function () {
			return this.freezeAtZoom(false);
		},

		freezeAtZoom: function (frozenZoom) {
			this._processQueue();

			var map = this._map;

			// If frozenZoom is not specified, true or NaN, freeze at current zoom.
			// Note: NaN is the only value which is not eaqual to itself.
			if (frozenZoom === undefined || frozenZoom === true || (frozenZoom !== frozenZoom)) {
				// Set to -1 if not on map, as the sign to freeze as soon as it gets added to a map.
				frozenZoom = map ? Math.round(map.getZoom()) : -1;
			} else if (frozenZoom === 'max') {
				// If frozenZoom is "max", freeze at MCG maxZoom + 1 (eliminates all clusters).
				frozenZoom = this._maxZoom + 1;
			} else if (frozenZoom === 'maxKeepSpiderfy') {
				// If "maxKeepSpiderfy", freeze at MCG maxZoom (eliminates all clusters but bottom-most ones).
				frozenZoom = this._maxZoom;
			}

			var requestFreezing = typeof frozenZoom === 'number';

			if (this._frozen) { // Already frozen.
				if (!requestFreezing) { // Unfreeze.
					this._unfreeze();
					return this;
				}
				// Just change the frozen zoom: go straight to artificial zoom.
			} else if (requestFreezing) {
				// Start freezing
				this._initiateFreeze();
			} else { // Not frozen and not requesting freezing => nothing to do.
				return this;
			}

			this._artificialZoomSafe(this._zoom, frozenZoom);
			return this;
		},

		_initiateFreeze: function () {
			var map = this._map;

			// Start freezing
			this._frozen = true;

			if (map) {
				// Change behaviour on zoomEnd and moveEnd.
				map.off('zoomend', this._zoomEnd, this);
				map.off('moveend', this._moveEnd, this);

				map.on('zoomend moveend', this._viewChangeEndNotClustering, this);
			}
		},

		_unfreeze: function () {
			var map = this._map;

			this._frozen = false;

			if (map) {
				// Restore original behaviour on zoomEnd.
				map.off('zoomend moveend', this._viewChangeEndNotClustering, this);

				map.on('zoomend', this._zoomEnd, this);
				map.on('moveend', this._moveEnd, this);

				// Animate.
				this._executeAfterUnspiderfy(function () {
					this._zoomEnd(); // Will set this._zoom at the end.
				}, this);
			}
		},

		_executeAfterUnspiderfy: function (callback, context) {
			// Take care of spiderfied markers!
			// The cluster might be removed, whereas markers are on fake positions.
			if (this._unspiderfy && this._spiderfied) {
				this.once('animationend', function () {
					callback.call(context);
				});
				this._unspiderfy();
				return;
			}

			callback.call(context);
		},

		_artificialZoomSafe: function (previousZoom, targetZoom) {
			this._zoom = targetZoom;

			if (!this._map || previousZoom === targetZoom) {
				return;
			}

			this._executeAfterUnspiderfy(function () {
				this._artificialZoom(previousZoom, targetZoom);
			}, this);
		},

		_artificialZoom: function (previousZoom, targetZoom) {
			if (previousZoom < targetZoom) {
				// Make as if we had instantly zoomed in from previousZoom to targetZoom.
				this._animationStart();
				this._topClusterLevel._recursivelyRemoveChildrenFromMap(
					this._currentShownBounds, previousZoom, this._getExpandedVisibleBounds()
				);
				this._animationZoomIn(previousZoom, targetZoom);

			} else if (previousZoom > targetZoom) {
				// Make as if we had instantly zoomed out from previousZoom to targetZoom.
				this._animationStart();
				this._animationZoomOut(previousZoom, targetZoom);
			}
		},

		_viewChangeEndNotClustering: function () {
			var fg = this._featureGroup,
			    newBounds = this._getExpandedVisibleBounds(),
			    targetZoom = this._zoom;

			// Remove markers and bottom clusters outside newBounds, unless they come
			// from a spiderfied cluster.
			fg.eachLayer(function (layer) {
				if (!newBounds.contains(layer._latlng) && layer.__parent && layer.__parent._zoom < targetZoom) {
					fg.removeLayer(layer);
				}
			});

			// Add markers and bottom clusters in newBounds.
			this._topClusterLevel._recursively(newBounds, -1, targetZoom,
				function (c) { // Add markers from each cluster of lower zoom than targetZoom
					if (c._zoom === targetZoom) { // except targetZoom
						return;
					}

					var markers = c._markers,
					    i = 0,
					    marker;

					for (; i < markers.length; i++) {
						marker = c._markers[i];

						if (!newBounds.contains(marker._latlng)) {
							continue;
						}

						fg.addLayer(marker);
					}
				},
				function (c) { // Add clusters from targetZoom.
					c._addToMap();
				}
			);
		},

		_originalZoomOrSpiderfy: LMCGproto._zoomOrSpiderfy,

		_zoomOrSpiderfy: function (e) {
			if (this._frozen && this.options.spiderfyOnMaxZoom) {
				e.layer.spiderfy();
				if (e.originalEvent && e.originalEvent.keyCode === 13) {
					map._container.focus();
				}
			} else {
				this._originalZoomOrSpiderfy(e);
			}
		}

	});


	// Just return a value to define the module export.
	return LMCG;
}));