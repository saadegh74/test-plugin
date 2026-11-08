<?php
use PowerpackElements\Classes\PP_Admin_Settings;

$current_tab  = isset( $_REQUEST['tab'] ) ? esc_attr( $_REQUEST['tab'] ) : 'general';
$settings     = PP_Admin_Settings::get_settings();

?>
<style>
#wpcontent {
	padding: 0;
}
#footer-left {
	display: none;
}
.pp-settings-wrap {
	margin: 0;
}
.pp-settings-wrap * {
	box-sizing: border-box;
}
.pp-notices-target {
	margin: 0;
}
.pp-settings-header {
	display: flex;
	align-items: center;
	padding: 0 20px;
	background: #fff;
	box-shadow: 0 1px 8px 0 rgba(0,0,0,0.05);
}
.pp-settings-header h3 {
	margin: 0;
	font-weight: 500;
}
.pp-settings-header h3 .dashicons {
	color: #a2a2a2;
	vertical-align: text-bottom;
}
.pp-settings-tabs {
	margin-left: 30px;
}
.pp-settings-tabs a,
.pp-settings-tabs a:hover,
.pp-settings-tabs a.nav-tab-active {
	background: none;
	border: none;
	box-shadow: none;
}
.pp-settings-tabs a {
	font-weight: 500;
	padding: 0 10px;
	color: #5f5f5f;
}
.pp-settings-tabs a.nav-tab-active {
	color: #333;
}
.pp-settings-tabs a > span {
	display: block;
	padding: 10px 0;
	border-bottom: 3px solid transparent;
}
.pp-settings-tabs a.nav-tab-active > span {
	border-bottom: 3px solid #0073aa;
}
.pp-settings-content {
	padding: 20px;
}
.pp-settings-content > form {
	background: #fff;
	padding: 10px 30px;
	box-shadow: 1px 1px 10px 0 rgba(0,0,0,0.05);
}
.pp-settings-content > form .form-table th {
	font-weight: 500;
	width: 230px;
}
.pp-settings-section {
	margin-bottom: 20px;
}
.pp-settings-section .pp-settings-section-title {
	font-weight: 300;
	font-size: 22px;
	border-bottom: 1px solid #eee;
	padding-bottom: 15px;
}
.pp-settings-section .pp-modules-manager-filters {
	float: right;
}
.pp-settings-section .pp-settings-elements-grid {
	max-width: 1220px;
}
.pp-settings-section .pp-settings-elements-grid > tbody {
	display: flex;
	align-items: center;
	flex-direction: row;
	flex-wrap: wrap;
}
.pp-settings-section .pp-settings-elements-grid > tbody tr {
	background: #f3f5f6;
	margin-right: 10px;
	margin-bottom: 10px;
	padding: 12px;
	border-radius: 5px;
}
.pp-settings-section .pp-settings-elements-grid > tbody tr th,
.pp-settings-section .pp-settings-elements-grid > tbody tr td {
	padding: 0;
}
.pp-settings-section .pp-settings-elements-grid th > label {
	user-select: none;
}
.pp-settings-section .toggle-all-widgets,
.pp-settings-section .toggle-all-extensions {
	margin-bottom: 10px;
}
.pp-settings-section .pp-admin-field-toggle {
	position: relative;
	display: inline-block;
	width: 35px;
	height: 16px;
}
.pp-settings-section .pp-admin-field-toggle input {
	opacity: 0;
	width: 0;
	height: 0;
}
.pp-settings-section .pp-admin-field-toggle .pp-admin-field-toggle-slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #fff;
	border: 1px solid #7e8993;
	border-radius: 34px;
	-webkit-transition: .4s;
	transition: .4s;
}
.pp-settings-section .pp-admin-field-toggle .pp-admin-field-toggle-slider:before {
	border-radius: 50%;
	position: absolute;
	content: "";
	height: 10px;
	width: 10px;
	left: 2px;
	bottom: 2px;
	background-color: #7e8993;
	-webkit-transition: .4s;
	transition: .4s;
}
.pp-settings-section .pp-admin-field-toggle input[type="checkbox"]:checked + .pp-admin-field-toggle-slider:before {
	background-color: #0071a1;
	-webkit-transform: translateX(19px);
	-ms-transform: translateX(19px);
	transform: translateX(19px);
}
.pp-settings-section .pp-admin-field-toggle input:focus + .pp-admin-field-toggle-slider {
	border-color: #0071a1;
	box-shadow: 0 0 2px 1px #0071a1;
	transition: 0s;
}
</style>

<div class="wrap pp-settings-wrap">

	<div class="pp-settings-header">
		<h3>
			<span class="dashicons dashicons-admin-settings"></span>
			<span>
			<?php
				$admin_label = $settings['admin_label'];
				$admin_label = trim( $admin_label ) !== '' ? trim( $admin_label ) : 'PowerPack';
				echo sprintf( esc_html__( '%s Settings', 'powerpack' ), $admin_label );
			?>
			</span>
		</h3>
		<div class="pp-settings-tabs wp-clearfix">
			<?php self::render_tabs( $current_tab ); ?>
		</div>
	</div>

	<div class="pp-settings-content">
		<h2 class="pp-notices-target"></h2>
		<?php \PowerpackElements\Classes\PP_Admin_Settings::render_update_message(); ?>
		<form method="post" id="pp-settings-form" action="<?php echo self::get_form_action( '&tab=' . $current_tab ); ?>">
			<?php self::render_setting_page(); ?>
			<?php
			if ( 'white-label' !== $current_tab ) {
				submit_button();
			} else {
				if ( 'off' === $settings['hide_wl_settings'] ) {
					submit_button();
				}
			}
			?>
		</form>

		<?php if ( 'on' != $settings['hide_support'] ) { ?>
			<br />
			<h2><?php esc_html_e( 'Support', 'powerpack' ); ?></h2>
			<p>
				<?php
					$support_link = $settings['support_link'];
					$support_link = ! empty( $support_link ) ? $support_link : 'https://powerpackelements.com/contact/';
					esc_html_e( 'For submitting any support queries, feedback, bug reports or feature requests, please visit', 'powerpack' ); ?> <a href="<?php echo $support_link; ?>" target="_blank"><?php esc_html_e( 'this link', 'powerpack' ); ?></a>
			</p>
		<?php } ?>
	</div>
</div>
