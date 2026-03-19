<?php
defined( 'ABSPATH' ) || exit;

/**
** Shortcode: [helloworld]
** Outputs a simple greeting. No JS or AJAX required.
**/
class Surgewpb_Helloworld {

	public $tag      = 'helloworld';
	public $use_ajax = false;
	public $libs     = [];

	public function render( $atts, $content = null ) {
		Surgewpb_Asset_Loader::load( 'helloworld', $this->use_ajax, $this->libs );

		return '<p class="surgewpb-helloworld">'
			. esc_html__( 'Hello World from SurgeWP Boilerplate', 'surgewpb' )
			. '</p>';
	}
}
