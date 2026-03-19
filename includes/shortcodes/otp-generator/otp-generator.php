<?php
defined( 'ABSPATH' ) || exit;

/**
** Shortcode: [otp_generator]
** Renders a button that triggers an AJAX request to generate and display a 6-digit OTP.
**/
class Surgewpb_Otp_Generator {

	public $tag      = 'otp_generator';
	public $use_ajax = true;
	public $libs     = [];

	/**
	** Register AJAX handlers on instantiation so they are available on all requests,
	** including admin-ajax.php, regardless of whether the shortcode is on the current page.
	**/
	public function __construct() {
		add_action( 'wp_ajax_surgewpb_generate_otp',        [ $this, 'ajax_generate_otp' ] );
		add_action( 'wp_ajax_nopriv_surgewpb_generate_otp', [ $this, 'ajax_generate_otp' ] );
	}

	public function render( $atts, $content = null ) {
		Surgewpb_Asset_Loader::load( 'otp-generator', $this->use_ajax, $this->libs );

		ob_start();
		?>
		<div class="surgewpb-otp-generator" data-surgewpb-module="otp_generator">
			<button class="surgewpb-otp-btn" type="button">
				<?php esc_html_e( 'Generate OTP', 'surgewpb' ); ?>
			</button>
			<div class="surgewpb-otp-result" aria-live="polite"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	** Generate a cryptographically random 6-digit OTP and return it as JSON.
	**/
	public function ajax_generate_otp() {
		check_ajax_referer( 'surgewpb_nonce', 'nonce' );

		$otp = str_pad( (string) random_int( 0, 999999 ), 6, '0', STR_PAD_LEFT );

		wp_send_json_success( [ 'otp' => $otp ] );
	}
}
