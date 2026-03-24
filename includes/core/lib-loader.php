<?php
defined( 'ABSPATH' ) || exit;

/**
** Library Loader — registers and enqueues shared third-party assets declared by shortcodes.
** All definitions live in includes/libs/libraries.php and are loaded once per page.
**/
class Surgewpb_Lib_Loader {

	private static $loaded      = [];
	private static $definitions = null;

	/**
	** Enqueue one or more libraries by their key (e.g. 'swiper', 'aos').
	** Skips any library that has already been loaded this request.
	**/
	public static function load_libs( array $libs ) {
		foreach ( $libs as $lib_key ) {
			if ( in_array( $lib_key, self::$loaded, true ) ) {
				continue;
			}

			$definition = self::get_definition( $lib_key );

			if ( empty( $definition ) ) {
				continue;
			}

			foreach ( $definition['css'] as $index => $css_path ) {
				$handle = $definition['handle'] . ( $index > 0 ? '-' . $index : '' );
				wp_enqueue_style( $handle, SURGEWPB_URL . $css_path, [], SURGEWPB_VERSION );
			}

			foreach ( $definition['js'] as $index => $js_path ) {
				$handle = $definition['handle'] . ( $index > 0 ? '-' . $index : '' );
				wp_enqueue_script( $handle, SURGEWPB_URL . $js_path, [], SURGEWPB_VERSION, true );
			}

			self::$loaded[] = $lib_key;
		}
	}

	/**
	** Load and cache a library definition from includes/libs/libraries.php.
	**/
	private static function get_definition( $lib_key ) {
		if ( null === self::$definitions ) {
			self::$definitions = require SURGEWPB_DIR . 'includes/libs/libraries.php';
		}

		return self::$definitions[ $lib_key ] ?? null;
	}
}
