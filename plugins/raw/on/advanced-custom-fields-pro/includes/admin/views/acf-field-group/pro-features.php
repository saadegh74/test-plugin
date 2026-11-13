<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2026 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

$acf_field_group_pro_features_title = __( 'Unlock Advanced Features and Build Even More with ACF PRO', 'acf' );
$acf_learn_more_text                = __( 'Learn More', 'acf' );
$acf_learn_more_link                = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'metabox' );
$acf_learn_more_target              = '_blank';
$acf_pricing_text                   = __( 'View Pricing & Upgrade', 'acf' );
$acf_pricing_link                   = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/pro/', 'ACF upgrade', 'metabox_pricing', 'pricing-table' );
$acf_more_tools_link                = acf_add_url_utm_tags( 'https://wpengine.com/developer/', 'bx_prod_referral', 'acf_free_plugin_cta_panel_logo', false, 'acf_plugin', 'referral' );
$acf_wpengine_logo_link             = acf_add_url_utm_tags( 'https://wpengine.com/', 'bx_prod_referral', 'acf_free_plugin_cta_panel_logo', false, 'acf_plugin', 'referral' );

if ( acf_is_pro() ) {
	if ( ! acf_pro_get_license_key() && acf_pro_is_updates_page_visible() ) {
		$acf_learn_more_target = '';
		$acf_learn_more_text   = __( 'Manage License', 'acf' );
		$acf_learn_more_link   = esc_url( admin_url( 'edit.php?post_type=acf-field-group&page=acf-settings-updates#acf_pro_license' ) );
	} elseif ( acf_pro_is_license_expired() ) {
		$acf_pricing_text = __( 'Renew License', 'acf' );
		$acf_pricing_link = acf_add_url_utm_tags( acf_pro_get_manage_license_url(), 'ACF renewal', 'metabox' );
	}
}

?>


<script type="text/javascript">
	( function ( $, undefined ) {
		$( document ).ready( function() {
			if ( 'field_group' === acf.get( 'screen' ) ) {
				$( '#acf-field-group-options' ).after( $( '#tmpl-acf-field-group-pro-features' ).css( 'display', 'block' ) );
			} else {
				$( '#tmpl-acf-field-group-pro-features' ).appendTo( '#wpbody-content .wrap' ).css( 'display', 'block' );
			}
		} );
	} )( jQuery );
</script>
