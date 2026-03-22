<?php
defined( 'ABSPATH' ) || exit;

/**
** Asset Loader — enqueues shortcode-specific JS/CSS on demand during render.
** Prevents duplicate enqueues and handles AJAX localization per shortcode.
**/
class Surgewpb_Asset_Loader {

	private static $enqueued = [];

	/**
	** Called from within a shortcode's render() method.
	** Loads declared libraries, then enqueues the shortcode's own CSS and JS if they exist.
	**/
	public static function load( $shortcode_name, $use_ajax = false, $libs = [] ) {
		if ( ! empty( $libs ) ) {
			Surgewpb_Lib_Loader::load_libs( $libs );
		}

		$base_dir   = SURGEWPB_DIR . 'includes/shortcodes/' . $shortcode_name . '/';
		$base_url   = SURGEWPB_URL . 'includes/shortcodes/' . $shortcode_name . '/';
		$css_handle = 'surgewpb-' . $shortcode_name . '-css';
		$js_handle  = 'surgewpb-' . $shortcode_name . '-js';

		$ver = SURGEWPBP_IS_DEV ? time() : SURGEWPB_VERSION;

		if ( file_exists( $base_dir . $shortcode_name . '.css' ) && ! in_array( $css_handle, self::$enqueued, true ) ) {
			wp_enqueue_style(
				$css_handle,
				$base_url . $shortcode_name . '.css',
				[ 'surgewpb-common-css' ],
				$ver
			);
			self::$enqueued[] = $css_handle;
		}

		if ( file_exists( $base_dir . $shortcode_name . '.js' ) && ! in_array( $js_handle, self::$enqueued, true ) ) {
			wp_enqueue_script(
				$js_handle,
				$base_url . $shortcode_name . '.js',
				[ 'surgewpb-common-js' ],
				$ver,
				true
			);

			/**
			** Localize AJAX data directly on the shortcode script when use_ajax is enabled.
			**/
			if ( $use_ajax ) {
				$object_name = 'surgewpb_' . str_replace( '-', '_', $shortcode_name ) . '_data';
				wp_localize_script( $js_handle, $object_name, [
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'surgewpb_nonce' ),
				] );
			}

			self::$enqueued[] = $js_handle;
		}
	}
}
