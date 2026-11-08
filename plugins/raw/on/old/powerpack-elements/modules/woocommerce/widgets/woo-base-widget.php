<?php
namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Woo_Base_Widget extends Powerpack_Widget {

	/**
	 * Is WooCommerce Feature Active.
	 *
	 * Checks whether a specific WooCommerce feature is active. These checks can sometimes look at multiple WooCommerce
	 * settings at once so this simplifies and centralizes the checking.
	 *
	 * @since 2.9.5
	 *
	 * @param string $feature
	 * @return bool
	 */
	protected function is_wc_feature_active( $feature ) {
		switch ( $feature ) {
			case 'checkout_login_reminder':
				return 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );
			case 'shipping':
				if ( class_exists( 'WC_Shipping_Zones' ) ) {
					$all_zones = \WC_Shipping_Zones::get_zones();
					if ( count( $all_zones ) > 0 ) {
						return true;
					}
				}
				break;
			case 'coupons':
				return function_exists( 'wc_coupons_enabled' ) && wc_coupons_enabled();
			case 'signup_and_login_from_checkout':
				return 'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout' );
			case 'additional_options':
				return ! wc_ship_to_billing_address_only();
			case 'ship_to_billing_address_only':
				return wc_ship_to_billing_address_only();
		}

		return false;
	}

	/**
	 * Init Gettext Modifications
	 *
	 * Should be overridden by a method in the Widget class.
	 *
	 * @since 2.9.5
	 */
	protected function init_gettext_modifications() {
		$this->gettext_modifications = [];
	}

	/**
	 * Filter Gettext.
	 *
	 * Filter runs when text is output to the page using the translation functions (`_e()`, `__()`, etc.)
	 * used to apply text changes from the widget settings.
	 *
	 * This allows us to make text changes without having to ovveride WooCommerce templates, which would
	 * lead to dev tax to keep all the templates up to date with each future WC release.
	 *
	 * @since 2.9.5
	 *
	 * @param string $translation
	 * @param string $text
	 * @param string $domain
	 * @return string
	 */
	public function filter_gettext( $translation, $text, $domain ) {
		if ( 'woocommerce' !== $domain && 'powerpack' !== $domain ) {
			return $translation;
		}

		if ( ! isset( $this->gettext_modifications ) ) {
			$this->init_gettext_modifications();
		}

		return array_key_exists( $text, $this->gettext_modifications ) ? $this->gettext_modifications[ $text ] : $translation;
	}
}
