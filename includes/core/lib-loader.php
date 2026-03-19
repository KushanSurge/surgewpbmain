<?php
defined( 'ABSPATH' ) || exit;

/**
** Library Loader — registers and enqueues shared third-party assets declared by shortcodes.
** Definitions live in includes/libs/<key>.php and are loaded once per page.
**/
class Surgewpb_Lib_Loader {

	private static $loaded      = [];
	private static $definitions = [];

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
	** Load and cache a library definition from includes/libs/<key>.php.
	**/
	private static function get_definition( $lib_key ) {
		if ( isset( self::$definitions[ $lib_key ] ) ) {
			return self::$definitions[ $lib_key ];
		}

		$lib_file = SURGEWPB_DIR . 'includes/libs/' . sanitize_file_name( $lib_key ) . '.php';

		if ( ! file_exists( $lib_file ) ) {
			return null;
		}

		$definition                    = require $lib_file;
		self::$definitions[ $lib_key ] = $definition;

		return $definition;
	}
}
