<?php
defined( 'ABSPATH' ) || exit;

/**
** Shortcode: [post_loop]
** Displays the latest posts with a Load More button powered by common JS AJAX.
** Accepts: posts_per_page, post_type
**/
class Surgewpb_Post_Loop {

	public $tag      = 'post_loop';
	public $use_ajax = false;
	public $libs     = [];

	/**
	** Register AJAX handler unconditionally so Load More works on all requests.
	**/
	public function __construct() {
		add_action( 'wp_ajax_surgewpb_load_more_posts',        [ $this, 'ajax_load_more_posts' ] );
		add_action( 'wp_ajax_nopriv_surgewpb_load_more_posts', [ $this, 'ajax_load_more_posts' ] );
	}

	public function render( $atts, $content = null ) {
		Surgewpb_Asset_Loader::load( 'post-loop', $this->use_ajax, $this->libs );

		$atts = shortcode_atts( [
			'posts_per_page' => 5,
			'post_type'      => 'post',
		], $atts, $this->tag );

		$per_page  = absint( $atts['posts_per_page'] );
		$post_type = sanitize_key( $atts['post_type'] );

		$query = new WP_Query( [
			'post_type'      => $post_type,
			'posts_per_page' => $per_page,
			'post_status'    => 'publish',
			'no_found_rows'  => false,
		] );

		ob_start();
		?>
		<div class="surgewpb-post-loop">
			<ul class="surgewpb-posts-list">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<li class="surgewpb-post-item">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					</li>
				<?php endwhile; ?>
			</ul>

			<?php wp_reset_postdata(); ?>

			<?php if ( $query->found_posts > $per_page ) : ?>
				<button
					class="surgewpb-load-more"
					type="button"
					data-offset="<?php echo esc_attr( $per_page ); ?>"
					data-per-page="<?php echo esc_attr( $per_page ); ?>"
				>
					<?php esc_html_e( 'Load More', 'surgewpb' ); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	** Return the next batch of posts as HTML for the Load More button.
	**/
	public function ajax_load_more_posts() {
		check_ajax_referer( 'surgewpb_nonce', 'nonce' );

		$offset   = absint( $_POST['offset']   ?? 0 );
		$per_page = absint( $_POST['per_page'] ?? 5 );

		$query = new WP_Query( [
			'post_type'      => 'post',
			'posts_per_page' => $per_page,
			'offset'         => $offset,
			'post_status'    => 'publish',
			'no_found_rows'  => false,
		] );

		if ( ! $query->have_posts() ) {
			wp_send_json_success( [ 'html' => '', 'no_more' => true ] );
		}

		$html = '';

		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= '<li class="surgewpb-post-item">';
			$html .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
			$html .= '<p>' . esc_html( get_the_excerpt() ) . '</p>';
			$html .= '</li>';
		}

		wp_reset_postdata();

		/**
		** Compare offset + batch size against total published post count to determine
		** whether more posts remain after this batch.
		**/
		$total_posts = (int) wp_count_posts( 'post' )->publish;
		$no_more     = ( $offset + $per_page ) >= $total_posts;

		wp_send_json_success( [
			'html'    => $html,
			'no_more' => $no_more,
		] );
	}
}
