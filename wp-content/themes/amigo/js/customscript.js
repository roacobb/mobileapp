// menu dropdown link clickable
( function ( $ ) {
    "use strict";
    jQuery( document ).ready( function ( $ ) {
        $( '.navbar .dropdown > a, .dropdown-menu > li > a' ).click( function () {
            location.href = this.href;
        } );
    } );
} )( jQuery );

// scroll to top button
( function ( $ ) {
    "use strict";
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
} )( jQuery );
// Content slider in single post
( function ( $ ) {
    "use strict";
    jQuery( document ).ready( function ( $ ) {
        var myDiv = document.getElementById( 'custom-box' );
        if ( myDiv ) {
            $( window ).scroll( function () {
                var distanceTop = $( '#custom-box' ).offset().top - $( window ).height();

                if ( $( window ).scrollTop() > distanceTop )
                    $( '#slidebox' ).animate( { 'right': '0px' }, 300 );
                else
                    $( '#slidebox' ).stop( true ).animate( { 'right': '-430px' }, 100 );
            } );

            $( '#slidebox .close-me' ).bind( 'click', function () {
                $( this ).parent().remove();
            } );
        }
        ;
    } );
} )( jQuery );
// FlexSlider
( function ( $ ) {
    "use strict";
    jQuery( document ).ready( function ( $ ) {
        $( window ).load( function () {
            $( '#slider' ).flexslider( {
                animation: "slide",
                controlNav: false,
                animationLoop: false,
                slideshow: false,
                start: function ( slider ) {
                    slider.removeClass( 'slider-loading' );
                }
            } );
        } );
    } );
} )( jQuery );
// FlexSlider Carousel
( function ( $ ) {
    "use strict";
    jQuery( document ).ready( function ( $ ) {
        var $window = $( window ),
            flexslider;
        // tiny helper function to add breakpoints

        function getGridSize() {
            return ( window.innerWidth < 520 ) ? 1 :
                ( window.innerWidth < 768 ) ? 2 :
                ( window.innerWidth < 1170 ) ? 3 :
                ( window.innerWidth < 1440 ) ? 4 :
                ( window.innerWidth < 1650 ) ? 5 : 6;
        }
        $( window ).load( function () {
            var autoplayCarousel = $( '#carousel-home' ).data( 'slide' )
            $( '#carousel-home' ).flexslider( {
                animation: "slide",
                controlNav: false,
                animationLoop: false,
                slideshow: autoplayCarousel,
                itemWidth: 438,
                itemMargin: 0,
                minItems: getGridSize(),
                maxItems: getGridSize(),
                start: function ( slider ) {
                    flexslider = slider; //Initializing flexslider here.
                    slider.removeClass( 'carousel-loading' );
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
} )( jQuery );
// FlexSlider Carousel - related
( function ( $ ) {
    "use strict";
    jQuery( document ).ready( function ( $ ) {
        var $window = $( window ),
            flexslider;
        // tiny helper function to add breakpoints

        function getGridSize() {
            return ( window.innerWidth < 520 ) ? 1 :
                ( window.innerWidth < 768 ) ? 2 : 3;
        }
        $( window ).load( function () {
            $( '#carousel-related' ).flexslider( {
                animation: "slide",
                controlNav: false,
                animationLoop: false,
                slideshow: true,
                itemWidth: 234,
                itemMargin: 0,
                minItems: getGridSize(),
                maxItems: getGridSize(),
                start: function ( slider ) {
                    flexslider = slider; //Initializing flexslider here.
                    slider.removeClass( 'carousel-loading' );
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
} )( jQuery );
// Tooltip
( function ( $ ) {
    "use strict";
    jQuery( document ).ready( function ( $ ) {
        $( function () {
            $( '[data-toggle="tooltip"]' ).tooltip()
        } )
    } );
} )( jQuery );