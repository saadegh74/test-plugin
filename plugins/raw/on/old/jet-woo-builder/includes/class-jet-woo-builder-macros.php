<?php
/**
 * JetWooBuilder Macros Class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Builder_Macros' ) ) {

	/**
	 * Define Jet_Woo_Builder_Macros class
	 */
	class Jet_Woo_Builder_Macros {

		/**
		 * Return available macros list
		 *
		 * @return mixed|void
		 */
		public function get_all() {
			return apply_filters( 'jet-woo-builder/macros/macros-list', array(
				'percentage_sale' => array( $this, 'get_percentage_sale' ),
				'numeric_sale'    => array( $this, 'get_numeric_sale' ),
			) );
		}

		/**
		 * Return verbose macros list
		 *
		 * @return string
		 */
		public function verbose_macros_list() {

			$macros = $this->get_all();
			$result = '';
			$sep    = '';

			foreach ( $macros as $key => $data ) {
				$result .= $sep . '%' . $key . '%';
				$sep    = ', ';
			}

			return $result;

		}

		/**
		 * Get percentage sale.
		 *
		 * Returns percentage sale for current product.
		 *
		 * @since 1.4.0
		 * @since 2.1.8 Refactored. Removed unused parameter.
		 * @since 2.1.13 Better RTL support.
		 *
		 * @return string
		 */
		public function get_percentage_sale() {

			global $product;

			if ( ! is_a( $product, 'WC_Product' ) ) {
				return null;
			}

			$percentage_sale = '';

			if ( 'variable' === $product->get_type() ) {
				$prices = $this->sort_variation_price( $product );

				if ( $prices['regular_min'] > 0 && $prices['regular_max'] > 0 ) {
					$percentage_min  = round( 100 - ( $prices['sale_min'] / $prices['regular_min'] * 100 ) );
					$percentage_max  = round( 100 - ( $prices['sale_max'] / $prices['regular_max'] * 100 ) );
					$percentage_sale = ( ( $percentage_min != $percentage_max ) ? ( $percentage_min . '-' . $percentage_max ) : $percentage_max );
				}
			} else {
				$regular_price = (float) $product->get_regular_price();
				$sale_price    = (float) $product->get_price();

				if ( $sale_price > 0 && $regular_price > 0 ) {
					$percentage_sale = round( 100 - ( $sale_price / $regular_price * 100 ) );
				}
			}

			return is_rtl() ? '&#37;' . $percentage_sale : $percentage_sale . '&#37;';

		}

		/**
		 * Get numeric sale.
		 *
		 * Can be used for sale price budge. Returns numeric sale for current product.
		 *
		 * @since 1.4.0
		 * @since 2.1.13 Better RTL support.
		 * @since 2.3.5 Use WooCommerce price formatting and calculate variable
		 *              product discounts per variation.
		 *
		 * @return string
		 */
		public function get_numeric_sale() {

			global $product;

			if ( ! is_a( $product, 'WC_Product' ) ) {
				return null;
			}

			$discounts = $this->get_numeric_sale_discounts( $product );

			/**
			 * Filters numeric sale discounts before formatting.
			 *
			 * @since 2.3.5
			 *
			 * @param float[]    $discounts Numeric sale discounts.
			 * @param WC_Product $product   Product instance.
			 */
			$discounts = apply_filters( 'jet-woo-builder/macros/numeric-sale/discounts', $discounts, $product );
			$discounts = array_filter( array_map( 'floatval', (array) $discounts ), function ( $discount ) {
				return $discount > 0;
			} );

			if ( empty( $discounts ) ) {
				$numeric_sale = '';
			} else {
				$minimum_discount = min( $discounts );
				$maximum_discount = max( $discounts );
				$numeric_sale     = $this->format_numeric_sale( $minimum_discount );

				if ( $minimum_discount !== $maximum_discount ) {
					$numeric_sale = sprintf(
						'%1$s&ndash;%2$s',
						$numeric_sale,
						$this->format_numeric_sale( $maximum_discount )
					);
				}
			}

			/**
			 * Filters the formatted numeric sale macro value.
			 *
			 * @since 2.3.5
			 *
			 * @param string     $numeric_sale Formatted numeric sale value.
			 * @param float[]    $discounts    Numeric sale discounts.
			 * @param WC_Product $product      Product instance.
			 */
			$numeric_sale = apply_filters( 'jet-woo-builder/macros/numeric-sale', $numeric_sale, $discounts, $product );

			if ( '' !== $numeric_sale ) {
				// Isolate the complete amount or range from the surrounding RTL text.
				$numeric_sale = "\u{2066}" . $numeric_sale . "\u{2069}";
			}

			return $numeric_sale;

		}

		/**
		 * Get numeric sale discounts.
		 *
		 * Returns discounts based on prices formatted for the shop display context.
		 *
		 * @since 2.3.5
		 *
		 * @param WC_Product $product Product instance.
		 *
		 * @return float[]
		 */
		private function get_numeric_sale_discounts( $product ) {

			$discounts = array();

			if ( $product->is_type( 'variable' ) ) {
				$prices = $product->get_variation_prices( true );

				foreach ( $prices['regular_price'] as $variation_id => $regular_price ) {
					$sale_price = $prices['sale_price'][ $variation_id ] ?? '';
					$discount   = $this->calculate_numeric_sale_discount( $regular_price, $sale_price );

					if ( $discount > 0 ) {
						$discounts[] = $discount;
					}
				}
			} elseif ( $product->is_on_sale() ) {
				$regular_price = $product->get_regular_price();
				$sale_price    = $product->get_sale_price();

				if ( '' !== $regular_price && '' !== $sale_price ) {
					$regular_price = wc_get_price_to_display( $product, array( 'price' => $regular_price ) );
					$sale_price    = wc_get_price_to_display( $product, array( 'price' => $sale_price ) );
					$discount      = $this->calculate_numeric_sale_discount( $regular_price, $sale_price );

					if ( $discount > 0 ) {
						$discounts[] = $discount;
					}
				}
			}

			return $discounts;

		}

		/**
		 * Calculate and normalize numeric sale discount.
		 *
		 * @since 2.3.5
		 *
		 * @param string|float $regular_price Regular product price.
		 * @param string|float $sale_price    Sale product price.
		 *
		 * @return float
		 */
		private function calculate_numeric_sale_discount( $regular_price, $sale_price ) {

			$regular_price = (float) $regular_price;
			$sale_price    = (float) $sale_price;

			if ( $regular_price <= 0 || $sale_price < 0 || $sale_price >= $regular_price ) {
				return 0.0;
			}

			return round( $regular_price - $sale_price, wc_get_price_decimals() );

		}

		/**
		 * Format numeric sale according to WooCommerce price settings.
		 *
		 * @since 2.3.5
		 *
		 * @param float $discount Numeric sale discount.
		 *
		 * @return string
		 */
		private function format_numeric_sale( $discount ) {

			return wc_price( $discount, array( 'in_span' => false ) );

		}

		/**
		 * Sort prices for variable product
		 *
		 * @param $product
		 *
		 * @return array
		 */
		public function sort_variation_price( $product ) {

			$prices = $product->get_variation_prices( true );

			foreach ( $prices['price'] as $product_id => $price ) {
				$product_obj                    = wc_get_product( $product_id );
				$prices['price'][ $product_id ] = wc_get_price_to_display( $product_obj );
			}

			asort( $prices['price'] );
			asort( $prices['regular_price'] );

			return array(
				'sale_min'    => current( $prices['price'] ),
				'sale_max'    => end( $prices['price'] ),
				'regular_min' => current( $prices['regular_price'] ),
				'regular_max' => end( $prices['regular_price'] ),
			);

		}

		/**
		 * Do macros inside string
		 *
		 * @param      $string
		 * @param null $field_value
		 *
		 * @return string|string[]|null
		 */
		public function do_macros( $string = '', $field_value = null ) {

			$macros = $this->get_all();

			return preg_replace_callback(
				'/%([a-z_-]+)(\|[a-z0-9_-]+)?%/',
				function ( $matches ) use ( $macros, $field_value ) {

					$found = $matches[1];

					if ( ! isset( $macros[ $found ] ) ) {
						return $matches[0];
					}

					$cb = $macros[ $found ];

					if ( ! is_callable( $cb ) ) {
						return $matches[0];
					}

					$args = isset( $matches[2] ) ? ltrim( $matches[2], '|' ) : false;

					return call_user_func( $cb, $field_value, $args );

				}, $string
			);

		}

	}

}