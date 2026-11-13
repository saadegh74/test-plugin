<?php
/**
 * PowerPack Helper.
 *
 * @package PowerPack
 */

namespace PowerpackElements\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class PP_Woo_Helper.
 */
class PP_Woo_Helper {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * [$product_id]
	 *
	 * @var null
	 */
	private static $product_id = null;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Short Description.
	 *
	 * @since 1.3.3
	 */
	public function woo_shop_short_desc() {
		if ( has_excerpt() ) {
			echo '<div class="pp-woo-products-description">';
				echo wp_kses_post( get_the_excerpt() );
			echo '</div>';
		}
	}

	/**
	 * Parent Category.
	 *
	 * @since 1.1.0
	 */
	public function woo_shop_parent_category() {
		if ( apply_filters( 'pp_woo_shop_parent_category', true ) ) : ?>
			<span class="pp-woo-product-category">
				<?php
				global $product;

				$product_categories = function_exists( 'wc_get_product_category_list' ) ? wc_get_product_category_list( get_the_ID(), ',', '', '' ) : $product->get_categories( ',', '', '' );

				$product_categories = wp_strip_all_tags( $product_categories );

				if ( $product_categories ) {
					list( $parent_cat ) = explode( ',', $product_categories );
					$product_cat = apply_filters( 'pp_woo_products_category', $parent_cat, $parent_cat );

					echo esc_html( $product_cat );
				}
				?>
			</span> 
			<?php
		endif;
	}

	/**
	 * Product Categories.
	 *
	 * @since 2.9.11
	 */
	public function woo_shop_product_categories() { ?>
		<span class="pp-woo-product-category">
			<?php
			global $product;

			$product_categories = function_exists( 'wc_get_product_category_list' ) ? wc_get_product_category_list( get_the_ID(), ',', '', '' ) : $product->get_categories( ',', '', '' );

			$product_categories = wp_strip_all_tags( $product_categories );

			if ( $product_categories ) {
				$product_categories_arr = explode( ',', $product_categories );
				$product_categories_list = implode( ', ', $product_categories_arr );

				echo esc_html( $product_categories_list );
			}
			?>
		</span> 
		<?php
	}

	/**
	 * Product Flip Image.
	 *
	 * @param  string $image_size  Size of image.
	 * @since 1.3.3
	 */
	public function woo_shop_product_flip_image( $image_size ) {

		global $product;

		$attachment_ids = $product->get_gallery_image_ids();

		if ( $attachment_ids ) {

			// $image_size = apply_filters( 'single_product_archive_thumbnail_size', 'shop_catalog' );

			echo apply_filters( 'pp_woocommerce_product_flip_image', wp_get_attachment_image( reset( $attachment_ids ), $image_size, false, array( 'class' => 'pp-show-on-hover' ) ) );
		}
	}

	/**
	 * Get ID of last product in the query.
	 */
	public function woo_get_last_product_id() {
		global $wpdb;

		// Getting last Product ID (max value).
		$results = $wpdb->get_col(
			"
			SELECT MAX(ID) FROM {$wpdb->prefix}posts
			WHERE post_type LIKE 'product'
			AND post_status = 'publish'"
		);
		return reset( $results );
	}

	/**
	 * [default] Show Default data in Elementor Editor Mode
	 *
	 * @param  string $widgets  Widget Name.
	 * @param  array  $settings Widget Settings.
	 * @return [html]
	 */
	public function default( $widgets = '', $settings = array(), $widget = array() ) {
		global $post, $product;
		if ( 'product' === get_post_type() ) {
			self::$product_id = $product->get_id();
		} else {
			self::$product_id = $this->woo_get_last_product_id();
			$product          = wc_get_product( $this->woo_get_last_product_id() );
		}

		switch ( $widgets ) {

			case 'pp-woo-add-to-cart':
				ob_start();
				echo '<div class="product">';
				do_action( 'woocommerce_' . $product->get_type() . '_add_to_cart' );
				echo '</div>';
				return ob_get_clean();
				break;

			case 'pp-woo-product-price':
				ob_start();
				?>
				<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ); ?>"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
				<?php
				return ob_get_clean();
				break;

			case 'pp-woo-product-short-description':
				ob_start();
				$short_description = get_the_excerpt( self::$product_id );
				$short_description = apply_filters( 'woocommerce_short_description', $short_description );
				if ( empty( $short_description ) ) {
					return;
				}
				?>
					<div class="woocommerce-product-details__short-description"><?php echo wp_kses_post( $short_description ); ?></div>
				<?php
				return ob_get_clean();
				break;

			case 'pp-woo-product-content':
				ob_start();
				$description = get_post_field( 'post_content', self::$product_id );
				if ( empty( $description ) ) {
					return;
				}
				return $description .= ob_get_clean();
				break;

			case 'pp-woo-product-rating':
				if ( 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
					return;
				}
				ob_start();
				$rating_count = $product->get_rating_count();
				$review_count = $product->get_review_count();
				$average      = $product->get_average_rating();

				if ( $rating_count > 0 ) :
					?>
					<div class="product">
						<div class="woocommerce-product-rating">
							<?php echo wc_get_rating_html( $average, $rating_count ); // PHPCS:ignore ?>
							<?php if ( comments_open() ) : ?>
								<?php //phpcs:disable ?>
								<a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'powerpack' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a>
								<?php // phpcs:enable ?>
							<?php endif ?>
						</div>
					</div>
				<?php else : ?>
					<?php echo '<div class="pp-nodata">' . esc_attr__( 'No ratings found!', 'powerpack' ) . '</div>'; ?>
					<?php
				endif;
				break;

			case 'pp-woo-product-images':
				ob_start();
				if ( $product->is_on_sale() ) {
					echo apply_filters( 'woocommerce_sale_flash', '<span class="onsale">' . esc_html__( 'Sale!', 'powerpack' ) . '</span>', $post, $product );
				}
				$columns         = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
				$thumbnail_id    = $product->get_image_id();
				$wrapper_classes = apply_filters(
					'woocommerce_single_product_image_gallery_classes',
					array(
						'woocommerce-product-gallery',
						'woocommerce-product-gallery--' . ( $product->get_image_id() ? 'with-images' : 'without-images' ),
						'woocommerce-product-gallery--columns-' . absint( $columns ),
						'images',
					)
				);

				if ( function_exists( 'wc_get_gallery_image_html' ) ) {
					?>
					<div class="product">
						<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
							<figure class="woocommerce-product-gallery__wrapper">
								<?php
								if ( $product->get_image_id() ) {
									$html = wc_get_gallery_image_html( $thumbnail_id, true );
								} else {
									$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
									$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'powerpack' ) );
									$html .= '</div>';
								}

								echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

								$attachment_ids = $product->get_gallery_image_ids();
								if ( $attachment_ids && $product->get_image_id() ) {
									foreach ( $attachment_ids as $attachment_id ) {
										echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', wc_get_gallery_image_html( $attachment_id ), $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
									}
								}

								?>
							</figure>
						</div>
					</div>
					<?php
				}
				return ob_get_clean();
				break;

			case 'pp-woo-product-meta':
				ob_start();
				?>
					<div class="product">
						<div class="product_meta">

							<?php do_action( 'woocommerce_product_meta_start' ); ?>

							<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>

								<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'powerpack' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'powerpack' ); ?></span></span>

							<?php endif; ?>

							<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'powerpack' ) . ' ', '</span>' ); ?>

							<?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'powerpack' ) . ' ', '</span>' ); ?>

							<?php do_action( 'woocommerce_product_meta_end' ); ?>

						</div>
					</div>
				<?php
				return ob_get_clean();
				break;

			case 'pp-woo-product-additional-information':
				ob_start();
				wc_get_template( 'single-product/tabs/additional-information.php' );
				return ob_get_clean();
				break;

			case 'pp-woo-product-tabs':
				setup_postdata( $product->get_id() );
				ob_start();
				add_filter( 'woocommerce_product_tabs', array( $widget, 'add_tabs' ) );
				if ( 'elementor_library' === get_post_type() ) {
					add_filter( 'the_content', array( $this, 'product_content' ) );
				}
				wc_get_template( 'single-product/tabs/tabs.php' );
				remove_filter( 'woocommerce_product_tabs', array( $widget, 'add_tabs' ) );
				return ob_get_clean();
				break;

			case 'pp-woo-product-reviews':
				ob_start();
				if ( comments_open() ) {
					comments_template();
				}
				return ob_get_clean();
				break;

			case 'pp-woo-product-stock':
				ob_start();
				$availability = $product->get_availability();
				?>
					<div class="product"><p class="stock <?php echo esc_attr( $availability['class'] ); ?>"><?php echo wp_kses_post( $availability['availability'] ); ?></p></div>
				<?php
				return ob_get_clean();
				break;

			case 'pp-woo-product-upsell':
				ob_start();

				$product_per_page = '-1';
				$columns          = 4;
				$orderby          = 'rand';
				$order            = 'desc';
				if ( ! empty( $settings['columns'] ) ) {
					$columns = $settings['columns'];
				}
				if ( ! empty( $settings['orderby'] ) ) {
					$orderby = $settings['orderby'];
				}
				if ( ! empty( $settings['order'] ) ) {
					$order = $settings['order'];
				}

				woocommerce_upsell_display( $product_per_page, $columns, $orderby, $order );

				return ob_get_clean();
				break;
		}
	}
}
