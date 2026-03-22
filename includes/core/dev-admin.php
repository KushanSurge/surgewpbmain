<?php
defined( 'ABSPATH' ) || exit;

if ( ! SURGEWPBP_IS_DEV ) {
	return;
}

/**
** Dev Mode Admin — registers SurgeWP Dev menu pages for scaffolding
** shortcodes and function files. Only active when SURGEWPBP_IS_DEV is true.
**/
add_action( 'admin_menu', 'surgewpb_dev_register_menus' );

function surgewpb_dev_register_menus() {
	add_menu_page(
		'SurgeWP Dev',
		'SurgeWP Dev',
		'manage_options',
		'surgewpb-dev',
		'surgewpb_dev_shortcodes_page',
		'dashicons-hammer',
		80
	);

	add_submenu_page(
		'surgewpb-dev',
		'Shortcodes',
		'Shortcodes',
		'manage_options',
		'surgewpb-dev',
		'surgewpb_dev_shortcodes_page'
	);

	add_submenu_page(
		'surgewpb-dev',
		'Functions',
		'Functions',
		'manage_options',
		'surgewpb-dev-functions',
		'surgewpb_dev_functions_page'
	);
}


// ---------------------------------------------------------------------------
// Shortcodes Page
// ---------------------------------------------------------------------------

function surgewpb_dev_shortcodes_page() {
	$notice = '';

	if ( isset( $_POST['surgewpb_create_shortcode'] ) ) {
		check_admin_referer( 'surgewpb_create_shortcode' );

		$raw  = strtolower( trim( sanitize_text_field( $_POST['shortcode_name'] ?? '' ) ) );
		$name = trim( preg_replace( '/[^a-z0-9\-]/', '-', $raw ), '-' );

		if ( empty( $name ) ) {
			$notice = surgewpb_dev_notice( 'Shortcode name is required.', 'error' );
		} else {
			$dir = SURGEWPB_DIR . 'includes/shortcodes/' . $name . '/';

			if ( is_dir( $dir ) ) {
				$notice = surgewpb_dev_notice( "Shortcode <strong>{$name}</strong> already exists.", 'error' );
			} else {
				wp_mkdir_p( $dir );

				$class = 'Surgewpb_' . implode( '_', array_map( 'ucfirst', explode( '-', $name ) ) );
				$tag   = str_replace( '-', '_', $name );

				file_put_contents( $dir . $name . '.php', surgewpb_dev_php_template( $name, $class, $tag ) );
				file_put_contents( $dir . $name . '.js',  surgewpb_dev_js_template( $name ) );
				file_put_contents( $dir . $name . '.css', surgewpb_dev_css_template( $name ) );

				$notice = surgewpb_dev_notice(
					"Created <strong>{$name}</strong> in <code>includes/shortcodes/{$name}/</code> — PHP class <code>{$class}</code>, tag <code>[{$tag}]</code>.",
					'success'
				);
			}
		}
	}

	$existing = surgewpb_dev_list_shortcodes();
	?>
	<div class="wrap">
		<h1>SurgeWP Dev &mdash; Shortcodes</h1>
		<?php echo $notice; ?>

		<h2>Create New Shortcode</h2>
		<form method="post">
			<?php wp_nonce_field( 'surgewpb_create_shortcode' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="shortcode_name">Name</label></th>
					<td>
						<input
							type="text"
							id="shortcode_name"
							name="shortcode_name"
							class="regular-text"
							placeholder="e.g. my-slider"
							pattern="[a-z0-9\-]+"
							required
						/>
						<p class="description">Lowercase letters, numbers, and hyphens only. Generates a folder, PHP class, JS module, and CSS file.</p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" name="surgewpb_create_shortcode" class="button button-primary">Generate Shortcode</button>
			</p>
		</form>

		<hr />

		<h2>Existing Shortcodes</h2>
		<?php if ( empty( $existing ) ) : ?>
			<p>No shortcodes found in <code>includes/shortcodes/</code>.</p>
		<?php else : ?>
			<table class="widefat striped" style="max-width:700px;">
				<thead>
					<tr>
						<th>Name</th>
						<th>Tag</th>
						<th>Files</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $existing as $sc ) : ?>
						<tr>
							<td><code><?php echo esc_html( $sc['name'] ); ?></code></td>
							<td><code>[<?php echo esc_html( $sc['tag'] ); ?>]</code></td>
							<td><?php echo esc_html( implode( ', ', $sc['files'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

function surgewpb_dev_list_shortcodes() {
	$dir     = SURGEWPB_DIR . 'includes/shortcodes/';
	$folders = glob( $dir . '*', GLOB_ONLYDIR );
	$result  = [];

	foreach ( (array) $folders as $folder ) {
		$name  = basename( $folder );
		$files = array_map( 'basename', glob( $folder . '/*' ) ?: [] );
		$result[] = [
			'name'  => $name,
			'tag'   => str_replace( '-', '_', $name ),
			'files' => $files,
		];
	}

	return $result;
}


// ---------------------------------------------------------------------------
// Functions Page
// ---------------------------------------------------------------------------

function surgewpb_dev_functions_page() {
	$notice = '';

	if ( isset( $_POST['surgewpb_create_function'] ) ) {
		check_admin_referer( 'surgewpb_create_function' );

		$raw      = strtolower( trim( sanitize_text_field( $_POST['function_name'] ?? '' ) ) );
		$filename = trim( preg_replace( '/[^a-z0-9\-]/', '-', $raw ), '-' );

		if ( empty( $filename ) ) {
			$notice = surgewpb_dev_notice( 'File name is required.', 'error' );
		} else {
			$dir  = SURGEWPB_DIR . 'includes/functions/';
			$path = $dir . $filename . '.php';

			wp_mkdir_p( $dir );

			if ( file_exists( $path ) ) {
				$notice = surgewpb_dev_notice( "File <strong>{$filename}.php</strong> already exists.", 'error' );
			} else {
				file_put_contents( $path, surgewpb_dev_function_template( $filename ) );
				$notice = surgewpb_dev_notice(
					"Created <strong>{$filename}.php</strong> in <code>includes/functions/</code>. It will be auto-loaded on every request.",
					'success'
				);
			}
		}
	}

	$existing = glob( SURGEWPB_DIR . 'includes/functions/*.php' ) ?: [];
	?>
	<div class="wrap">
		<h1>SurgeWP Dev &mdash; Functions</h1>
		<?php echo $notice; ?>

		<h2>Create New Function File</h2>
		<p>Files created here are placed in <code>includes/functions/</code> and automatically required on every page load.</p>
		<form method="post">
			<?php wp_nonce_field( 'surgewpb_create_function' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="function_name">File Name</label></th>
					<td>
						<input
							type="text"
							id="function_name"
							name="function_name"
							class="regular-text"
							placeholder="e.g. custom-queries"
							pattern="[a-z0-9\-]+"
							required
						/>
						<p class="description">Lowercase letters, numbers, and hyphens. A <code>.php</code> extension is added automatically.</p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" name="surgewpb_create_function" class="button button-primary">Create File</button>
			</p>
		</form>

		<hr />

		<h2>Existing Function Files</h2>
		<?php if ( empty( $existing ) ) : ?>
			<p>No files found in <code>includes/functions/</code>.</p>
		<?php else : ?>
			<table class="widefat striped" style="max-width:500px;">
				<thead>
					<tr><th>File</th></tr>
				</thead>
				<tbody>
					<?php foreach ( $existing as $file ) : ?>
						<tr><td><code><?php echo esc_html( basename( $file ) ); ?></code></td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}


// ---------------------------------------------------------------------------
// File Templates
// ---------------------------------------------------------------------------

function surgewpb_dev_php_template( $name, $class, $tag ) {
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

function surgewpb_dev_js_template( $name ) {
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

function surgewpb_dev_css_template( $name ) {
	return <<<CSS
/* [{$name}] shortcode styles */
.surgewpb-{$name} {
	/* add styles here */
}
CSS;
}

function surgewpb_dev_function_template( $name ) {
	return <<<PHP
<?php
defined( 'ABSPATH' ) || exit;

/**
** {$name} — auto-loaded from includes/functions/
**/

PHP;
}


// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function surgewpb_dev_notice( $message, $type = 'success' ) {
	$class = 'notice notice-' . esc_attr( $type ) . ' is-dismissible';
	return '<div class="' . $class . '"><p>' . $message . '</p></div>';
}
