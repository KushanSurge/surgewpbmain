/**
** SurgeWP Boilerplate — Common JS
** Provides: namespace, AJAX helper, module auto-init, post-loop Load More.
**/
( function ( $, surgewpb ) {
	'use strict';

	surgewpb.data    = window.surgewpb_data || {};
	surgewpb.modules = surgewpb.modules     || {};

	/**
	** AJAX Helper — wraps $.post with automatic action and nonce injection.
	** Usage: surgewpb.ajax( 'surgewpb_my_action', { key: val }, callback );
	**/
	surgewpb.ajax = function ( action, data, callback ) {
		var payload = $.extend( {}, data, {
			action : action,
			nonce  : surgewpb.data.nonce
		} );

		$.post( surgewpb.data.ajax_url, payload, function ( response ) {
			if ( typeof callback === 'function' ) {
				callback( response );
			}
		} );
	};

	/**
	** Module Auto-Initialization — finds every [data-surgewpb-module] element and
	** calls the matching function registered in surgewpb.modules.
	**/
	surgewpb.init = function () {
		$( '[data-surgewpb-module]' ).each( function () {
			var key = $( this ).data( 'surgewpb-module' );

			if ( typeof surgewpb.modules[ key ] === 'function' ) {
				surgewpb.modules[ key ]( this );
			}
		} );
	};

	/**
	** Post Loop — Load More handler.
	** Reads offset and per-page from button data attributes, appends returned HTML,
	** and hides the button when no more posts exist.
	**/
	surgewpb.postLoop = {
		init: function () {
			$( document ).on( 'click', '.surgewpb-load-more', function () {
				var $btn    = $( this );
				var $wrap   = $btn.closest( '.surgewpb-post-loop' );
				var $list   = $wrap.find( '.surgewpb-posts-list' );
				var offset  = parseInt( $btn.data( 'offset' ), 10 );
				var perPage = parseInt( $btn.data( 'per-page' ), 10 ) || 5;

				$btn.prop( 'disabled', true ).text( surgewpb.data.i18n ? surgewpb.data.i18n.loading : 'Loading…' );

				surgewpb.ajax( 'surgewpb_load_more_posts', {
					offset   : offset,
					per_page : perPage
				}, function ( response ) {
					if ( response.success && response.data.html ) {
						$list.append( response.data.html );
						$btn.data( 'offset', offset + perPage );
						$btn.prop( 'disabled', false ).text( surgewpb.data.i18n ? surgewpb.data.i18n.load_more : 'Load More' );
					}

					if ( ! response.success || response.data.no_more ) {
						$btn.hide();
					}
				} );
			} );
		}
	};

	$( document ).ready( function () {
		surgewpb.init();
		surgewpb.postLoop.init();
	} );

} )( jQuery, window.surgewpb = window.surgewpb || {} );
