// menu dropdown link clickable
jQuery( document ).ready( function ( $ ) {
    $( '.navbar .dropdown > a, .dropdown-menu > li > a, .navbar .dropdown-submenu > a, .widget-menu .dropdown > a, .widget-menu .dropdown-submenu > a' ).click( function () {
        location.href = this.href;
    } );
} );

// scroll to top button
jQuery( document ).ready( function ( $ ) {
    $( "#back-top" ).hide();
    $( function () {
        $( window ).scroll( function () {
            if ( $( this ).scrollTop() > 100 ) {
                $( '#back-top' ).fadeIn();
            } else {
                $( '#back-top' ).fadeOut();
            }
        } );

        // scroll body to 0px on click
        $( '#back-top a' ).click( function () {
            $( 'body,html' ).animate( {
                scrollTop: 0
            }, 800 );
            return false;
        } );
    } );
} );
// Tooltip
jQuery( document ).ready( function ( $ ) {
    $( function () {
        $( '[data-toggle="tooltip"]' ).tooltip()
    } )
} );
// Tooltip to compare and wishlist
jQuery( document ).ready( function ( $ ) {
    $( ".compare.button" ).attr( 'data-toggle', 'tooltip' );
    $( ".compare.button" ).attr( 'title', 'Compare Product' );
    $( ".yith-wcqv-button.button" ).attr( 'data-toggle', 'tooltip' );
    $( ".yith-wcqv-button.button" ).attr( 'title', 'Quick View' );
} );
// Wishlist count ajax update
jQuery( document ).ready( function ( $ ) {
    $( 'body' ).on( 'added_to_wishlist', function () {
        $( '.top-wishlist .count' ).load( yith_wcwl_plugin_ajax_web_url + ' .top-wishlist span', { action: 'yith_wcwl_update_single_product_list' } );
    } );
} );
// FlexSlider homepage
jQuery( document ).ready( function ( $ ) {
    var $window = $( window ),
        flexslider;
    // tiny helper function to add breakpoints

    function getGridSize() {
        return ( window.innerWidth < 520 ) ? 1 :
            ( window.innerWidth < 830 ) ? 2 :
            ( window.innerWidth < 1170 ) ? 3 :
            ( window.innerWidth < 1450 ) ? 4 : 5;
    }
    $( window ).load( function () {
        $( '#carousel-home' ).flexslider( {
            animation: "slide",
            controlNav: false,
            animationLoop: false,
            slideshow: true,
            itemWidth: 270,
            itemMargin: 40,
            minItems: getGridSize(),
            maxItems: getGridSize(),
            start: function ( slider ) {
                flexslider = slider; //Initializing flexslider here.
                slider.removeClass( 'loading-hide' );
            }
        } );
        $window.resize( function () {
            var gridSize = getGridSize();
            if ( flexslider ) {
                flexslider.vars.minItems = gridSize;
                flexslider.vars.maxItems = gridSize;
            }
        } );
    } );
} );
// Header after pseudo element height fix
jQuery( document ).ready( function ( $ ) {
    updateContainer();
    $( window ).on('load resize', function () {
        updateContainer();
    } );
} );
function updateContainer() {
    currentHeight = jQuery( '.header-right' ).outerHeight();
    if ( jQuery( window ).width() > 991 ) {
        jQuery( ".header-right-triangle" ).css( { "border-top-width": currentHeight } );
    }
}

