/**
** OTP Generator module — registered into surgewpb.modules and auto-initialised
** by common JS on any element carrying data-surgewpb-module="otp_generator".
**/
( function ( surgewpb ) {
	'use strict';

	surgewpb.modules.otp_generator = function ( element ) {
		var $ = jQuery;
		var $el  = $( element );
		var $btn = $el.find( '.surgewpb-otp-btn' );
		var $out = $el.find( '.surgewpb-otp-result' );

		$btn.on( 'click', function () {
			$btn.prop( 'disabled', true );

			surgewpb.ajax( 'surgewpb_generate_otp', {}, function ( response ) {
				if ( response.success ) {
					$out.text( response.data.otp );
				} else {
					$out.text( 'Error. Please try again.' );
				}

				$btn.prop( 'disabled', false );
			} );
		} );
	};

} )( window.surgewpb = window.surgewpb || { modules: {} } );
