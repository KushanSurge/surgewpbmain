/**
** Post Slider module — initialises a Swiper instance on the element carrying
** data-surgewpb-module="post_slider". Auto-initialised by common JS on document ready.
**/
( function ( surgewpb ) {
	'use strict';

	surgewpb.modules.post_slider = function ( element ) {
		var $el      = jQuery( element );
		var swiperEl = $el.find( '.surgewpb-post-slider__swiper' )[ 0 ];

		// Bail if the Swiper container is missing or the Swiper library didn't load.
		if ( ! swiperEl || typeof Swiper === 'undefined' ) {
			return;
		}

		new Swiper( swiperEl, {
			loop:  true,  // Wraps around seamlessly from last slide back to first.
			speed: 500,   // Transition duration in ms.

			autoplay: {
				delay:                3000,  // Time each slide stays visible before advancing.
				disableOnInteraction: false, // Keep autoplay running after the user swipes.
			},

			slidesPerView: 1,  // One post title visible at a time.
			spaceBetween:  0,

			// Scope navigation elements to this instance using DOM references,
			// so multiple sliders on the same page don't interfere with each other.
			navigation: {
				nextEl: $el.find( '.surgewpb-post-slider__next' )[ 0 ],
				prevEl: $el.find( '.surgewpb-post-slider__prev' )[ 0 ],
			},

			pagination: {
				el:        $el.find( '.surgewpb-post-slider__pagination' )[ 0 ],
				clickable: true, // Clicking a dot jumps directly to that slide.
			},
		} );
	};

} )( window.surgewpb = window.surgewpb || { modules: {} } );
