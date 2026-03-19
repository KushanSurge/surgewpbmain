<?php
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
** WP-CLI Command Class — provides `wp surgewpb make:shortcode <name>`.
**/
class Surgewpb_CLI {

	/**
	** Generate a new shortcode module: folder, PHP class, JS, and CSS.
	**
	** ## USAGE
	**
	**     wp surgewpb make:shortcode <name>
	**
	** ## EXAMPLES
	**
	**     wp surgewpb make:shortcode my-slider
	**/
	public function make_shortcode( $args ) {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( 'Shortcode name required. Usage: wp surgewpb make:shortcode <name>' );
			return;
		}

		$raw_name = strtolower( trim( $args[0] ) );
		$name     = preg_replace( '/[^a-z0-9\-]/', '-', $raw_name );
		$name     = trim( $name, '-' );
		$class    = 'Surgewpb_' . implode( '_', array_map( 'ucfirst', explode( '-', $name ) ) );
		$tag      = str_replace( '-', '_', $name );
		$dir      = SURGEWPB_DIR . 'includes/shortcodes/' . $name . '/';

		if ( is_dir( $dir ) ) {
			WP_CLI::error( "Shortcode '{$name}' already exists at {$dir}" );
			return;
		}

		wp_mkdir_p( $dir );

		file_put_contents( $dir . $name . '.php', self::php_template( $name, $class, $tag ) );
		file_put_contents( $dir . $name . '.js',  self::js_template( $name ) );
		file_put_contents( $dir . $name . '.css', self::css_template( $name ) );

		WP_CLI::success( "Created shortcode '{$name}' at includes/shortcodes/{$name}/" );
		WP_CLI::log( "  - {$name}.php  (class {$class}, tag [{$tag}])" );
		WP_CLI::log( "  - {$name}.js" );
		WP_CLI::log( "  - {$name}.css" );
	}

	/**
	** PHP class template for a generated shortcode module.
	**/
	private static function php_template( $name, $class, $tag ) {
		return <<<PHP
<?php
defined( 'ABSPATH' ) || exit;

/**
** Shortcode: [{$tag}]
**/
class {$class} {

	public \$tag      = '{$tag}';
	public \$use_ajax = false;
	public \$libs     = [];

	public function render( \$atts, \$content = null ) {
		Surgewpb_Asset_Loader::load( '{$name}', \$this->use_ajax, \$this->libs );

		\$atts = shortcode_atts( [], \$atts, \$this->tag );

		ob_start();
		?>
		<div class="surgewpb-{$name}" data-surgewpb-module="<?php echo esc_attr( '{$tag}' ); ?>">
			<!-- {$name} output -->
		</div>
		<?php
		return ob_get_clean();
	}
}
PHP;
	}

	/**
	** JS module template for a generated shortcode.
	**/
	private static function js_template( $name ) {
		$module_key = str_replace( '-', '_', $name );
		return <<<JS
( function ( surgewpb ) {
	'use strict';

	surgewpb.modules.{$module_key} = function ( element ) {
		var \$ = jQuery;
		var \$el = \$( element );

		// Initialise {$name} module on element
	};

} )( window.surgewpb = window.surgewpb || { modules: {} } );
JS;
	}

	/**
	** CSS template for a generated shortcode.
	**/
	private static function css_template( $name ) {
		return <<<CSS
/* [{$name}] shortcode styles */
.surgewpb-{$name} {
	/* add styles here */
}
CSS;
	}
}

/**
** Register CLI subcommands under the 'surgewpb' parent command.
**/
$surgewpb_cli = new Surgewpb_CLI();
WP_CLI::add_command( 'surgewpb make:shortcode', [ $surgewpb_cli, 'make_shortcode' ] );
