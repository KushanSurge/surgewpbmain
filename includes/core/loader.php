<?php
defined( 'ABSPATH' ) || exit;

/**
** Shortcode Loader — discovers all shortcode modules, registers AJAX handlers unconditionally,
** and conditionally registers shortcodes only when they appear in the current page content.
**/
class Surgewpb_Loader {

	private static $instance = null;
	private $instances       = [];

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	** Load all modules immediately (for AJAX handler registration),
	** then hook shortcode registration into 'wp' when post content is available.
	**/
	public function boot() {
		$this->load_modules();
		add_action( 'wp', [ $this, 'register_shortcodes' ] );
	}

	/**
	** Scan shortcodes directory, require each PHP file, and instantiate each class.
	** Constructors handle AJAX registration so this runs on every request.
	**/
	private function load_modules() {
		$dir     = SURGEWPB_DIR . 'includes/shortcodes/';
		$folders = glob( $dir . '*', GLOB_ONLYDIR );

		if ( empty( $folders ) ) {
			return;
		}

		foreach ( $folders as $folder ) {
			$name     = basename( $folder );
			$php_file = $folder . '/' . $name . '.php';

			if ( ! file_exists( $php_file ) ) {
				continue;
			}

			require_once $php_file;

			$class = $this->folder_to_class( $name );

			if ( ! class_exists( $class ) ) {
				continue;
			}

			$instance = new $class();

			if ( ! isset( $instance->tag ) ) {
				continue;
			}

			$this->instances[ $name ] = $instance;
		}
	}

	/**
	** Check each loaded module against current post content and register only matching shortcodes.
	**/
	public function register_shortcodes() {
		foreach ( $this->instances as $instance ) {
			if ( $this->shortcode_in_content( $instance->tag ) ) {
				add_shortcode( $instance->tag, [ $instance, 'render' ] );
			}
		}
	}

	/**
	** Detect shortcode in current post content using has_shortcode with strpos fallback.
	**/
	private function shortcode_in_content( $tag ) {
		global $post;

		if ( empty( $post->post_content ) ) {
			return false;
		}

		if ( has_shortcode( $post->post_content, $tag ) ) {
			return true;
		}

		return strpos( $post->post_content, '[' . $tag ) !== false;
	}

	/**
	** Convert a kebab-case folder name to a Surgewpb_Pascal_Case class name.
	** Example: otp-generator → Surgewpb_Otp_Generator
	**/
	private function folder_to_class( $folder_name ) {
		$parts = explode( '-', $folder_name );
		$parts = array_map( 'ucfirst', $parts );
		return 'Surgewpb_' . implode( '_', $parts );
	}
}
