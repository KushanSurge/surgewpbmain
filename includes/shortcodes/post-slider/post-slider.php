<?php
defined( 'ABSPATH' ) || exit;

/**
** Shortcode: [post_slider]
** Displays a Swiper slider of post titles linking to their respective posts.
** Accepts: posts_per_page, post_type
**
** Usage:
**   [post_slider]
**   [post_slider posts_per_page="5" post_type="post"]
**/
class Surgewpb_Post_Slider {

	public $tag      = 'post_slider';
	public $use_ajax = false;
	public $libs     = [ 'swiper' ]; // Pulls in Swiper CSS + JS via the library registry.

	public function render( $atts, $content = null ) {
		// Load Swiper from the library registry and this shortcode's own CSS + JS.
		Surgewpb_Asset_Loader::load( 'post-slider', $this->use_ajax, $this->libs );

		// Merge user-supplied attributes with defaults.
		$atts = shortcode_atts( [
			'posts_per_page' => 8,
			'post_type'      => 'post',
		], $atts, $this->tag );

		// Sanitize: posts_per_page must be a positive integer, post_type a plain key.
		$per_page  = absint( $atts['posts_per_page'] );
		$post_type = sanitize_key( $atts['post_type'] );

		// no_found_rows skips the SQL COUNT(*) since we don't need pagination here.
		$query = new WP_Query( [
			'post_type'      => $post_type,
			'posts_per_page' => $per_page,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		] );

		// Render nothing if there are no posts to show.
		if ( ! $query->have_posts() ) {
			return '';
		}

		ob_start();
		?>
		<?php
		/**
		 * data-surgewpb-module triggers auto-initialisation by common JS,
		 * which passes this element into surgewpb.modules.post_slider().
		 *
		 * Swiper requires:  .swiper > .swiper-wrapper > .swiper-slide
		 * Navigation and pagination elements are passed to Swiper via JS selectors.
		 */
		?>
		<div class="surgewpb-post-slider" data-surgewpb-module="post_slider">
			<div class="swiper surgewpb-post-slider__swiper">
				<div class="swiper-wrapper">
					<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						<div class="swiper-slide surgewpb-post-slider__slide">
							<a class="surgewpb-post-slider__link" href="<?php the_permalink(); ?>">
								<span class="surgewpb-post-slider__title"><?php the_title(); ?></span>
							</a>
						</div>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>

				<!-- Prev/next arrows — Swiper injects its own icons via ::after pseudo-elements -->
				<div class="swiper-button-prev surgewpb-post-slider__prev"></div>
				<div class="swiper-button-next surgewpb-post-slider__next"></div>

				<!-- Clickable dot pagination -->
				<div class="swiper-pagination surgewpb-post-slider__pagination"></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
