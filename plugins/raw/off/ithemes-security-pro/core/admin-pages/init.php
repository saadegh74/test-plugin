<?php


final class ITSEC_Admin_Page_Loader {
	private $page_id;

	public function __construct() {
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'add_admin_pages' ) );
		} else {
			add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		}

		add_action( 'wp_ajax_itsec_logs_page', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_itsec_help_page', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_itsec_debug_page', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_itsec-set-user-setting', array( $this, 'handle_user_setting' ) );

		// Filters for validating user settings
		add_filter( 'itsec-user-setting-valid-itsec-settings-view', array( $this, 'validate_view' ), null, 2 );

		add_action( 'show_user_profile', array( $this, 'render_profile_fields' ), 9 );
		add_action( 'edit_user_profile', array( $this, 'render_profile_fields' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_profile_scripts' ) );
	}

	public function add_admin_pages() {
		$onboarded  = ITSEC_Core::is_onboarded();
		$capability = ITSEC_Core::get_required_cap();
		$page_refs  = array();
		$menu_icon  = $this->get_icon();

		if ( $onboarded && current_user_can( 'itsec_dashboard_menu' ) ) {
			$parent = 'itsec-dashboard';
			add_menu_page( __( 'Security', 'it-l10n-ithemes-security-pro' ), __( 'Security', 'it-l10n-ithemes-security-pro' ), 'itsec_dashboard_menu', $parent, array( $this, 'show_page' ), $menu_icon );
			$page_refs[] = add_submenu_page( $parent, __( 'Dashboard', 'it-l10n-ithemes-security-pro' ), __( 'Dashboard', 'it-l10n-ithemes-security-pro' ), 'itsec_dashboard_menu', 'itsec-dashboard', array( $this, 'show_page' ) );
		} else {
			$parent = 'itsec';
			add_menu_page( __( 'Setup', 'it-l10n-ithemes-security-pro' ), __( 'Security', 'it-l10n-ithemes-security-pro' ), $capability, $parent, array( $this, 'show_page' ), $menu_icon );
		}

		if ( $onboarded ) {
			$page_refs[] = add_submenu_page( $parent, __( 'Site Scans', 'it-l10n-ithemes-security-pro' ), __( 'Site Scans', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec-site-scan', array( $this, 'show_page' ) );
			$page_refs[] = add_submenu_page( $parent, __( 'Firewall', 'it-l10n-ithemes-security-pro' ), __( 'Firewall', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec-firewall', array( $this, 'show_page' ) );
			$page_refs[] = add_submenu_page( $parent, __( 'Vulnerabilities', 'it-l10n-ithemes-security-pro' ), __( 'Vulnerabilities', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec-vulnerabilities', array( $this, 'show_page' ) );
			$page_refs[] = add_submenu_page( $parent, __( 'User Security', 'it-l10n-ithemes-security-pro' ), __( 'User Security', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec-user-security', array( $this, 'show_page' ) );
		}

		$page_refs[] = add_submenu_page( $parent, __( 'Kadence Security Settings', 'it-l10n-ithemes-security-pro' ), $onboarded ? __( 'Settings', 'it-l10n-ithemes-security-pro' ) : __( 'Setup', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec', array(
			$this,
			'show_page'
		) );

		if ( $onboarded ) {
			$page_refs[] = add_submenu_page( $parent, __( 'Tools', 'it-l10n-ithemes-security-pro' ), __( 'Tools' ), $capability, 'itsec-tools', array( $this, 'show_page' ) );
		}

		$page_refs = apply_filters( 'itsec-admin-page-refs', $page_refs, $capability, array( $this, 'show_page' ), $parent );

		if ( $onboarded ) {
			$page_refs[] = add_submenu_page( $parent, __( 'Kadence Security Logs', 'it-l10n-ithemes-security-pro' ), __( 'Logs', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec-logs', array( $this, 'show_page' ) );
		}

		if ( ! ITSEC_Core::is_pro() || ITSEC_Core::is_development() ) {
			$page_refs[] = add_submenu_page( $parent, '', '<span style="color:#7ABEED">' . __( 'Get More Security', 'it-l10n-ithemes-security-pro' ) . '</span>', $capability, 'itsec-go-pro', array( $this, 'show_page' ) );
		}

		if ( defined( 'ITSEC_DEBUG' ) && ITSEC_DEBUG ) {
			$page_refs[] = add_submenu_page( $parent, __( 'Kadence Security Debug', 'it-l10n-ithemes-security-pro' ), __( 'Debug', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec-debug', array( $this, 'show_page' ) );
		}

		foreach ( $page_refs as $page_ref ) {
			add_action( "load-$page_ref", array( $this, 'load' ) );
			add_action( "admin_print_scripts-$page_ref", array( $this, 'enqueue' ), 0 );
		}
	}

	private function get_page_id() {
		global $plugin_page;

		if ( isset( $this->page_id ) ) {
			return $this->page_id;
		}

		if ( wp_doing_ajax() ) {
			if ( isset( $_REQUEST['action'] ) && preg_match( '/^itsec_(.+)_page$/', $_REQUEST['action'], $match ) ) {
				$this->page_id = $match[1];
			}
		} elseif ( strpos( $plugin_page, 'itsec-' ) === 0 ) {
			$this->page_id = substr( $plugin_page, 6 );
		} elseif ( strpos( $plugin_page, 'itsec' ) === 0 ) {
			$this->page_id = 'settings';
		}

		if ( ! isset( $this->page_id ) ) {
			$this->page_id = '';
		}

		return $this->page_id;
	}

	public function load() {
		$this->load_file( 'page-%s.php' );
	}

	public function enqueue() {
		foreach ( ITSEC_Modules::get_available_modules() as $module ) {
			$handle = "itsec-{$module}-global";

			if ( wp_script_is( $handle, 'registered' ) ) {
				wp_enqueue_script( $handle );
			}

			if ( wp_style_is( $handle, 'registered' ) ) {
				wp_enqueue_style( $handle );
			}
		}

		ITSEC_Lib::preload_request_for_data_store(
			'ithemes-security/core',
			'receiveIndex',
			'/ithemes-security/v1',
			[ 'context' => 'help' ]
		);

		ITSEC_Lib::preload_request_for_data_store(
			'ithemes-security/modules',
			'receiveModules',
			'/ithemes-security/v1/modules',
			[ 'context' => 'edit', '_embed' => 1 ]
		);
	}

	public function show_page() {
		$page_id = $this->get_page_id();

		if ( 'settings' === $page_id ) {
			$url = network_admin_url( 'admin.php?page=itsec' );
		} else {
			$url = network_admin_url( 'admin.php?page=itsec-' . $this->get_page_id() );
		}

		do_action( 'itsec-page-show', $url );
	}

	public function handle_ajax_request() {
		$this->load_file( 'page-%s.php' );

		do_action( 'itsec-page-ajax' );
	}

	/**
	 * Render the profile fields for managing user security.
	 *
	 * @param WP_User $user
	 */
	public function render_profile_fields( $user ) {
		?>
		<div id="itsec-profile-root" data-user="<?php echo esc_attr( $user->ID ); ?>" data-can-manage="<?php echo esc_attr( ITSEC_Core::current_user_can_manage() ); ?>"></div>
		<noscript>
			<div class="notice notice-warning notice-alt below-h2"><p><?php esc_html_e( 'You must enable JavaScript to manage Kadence Security Settings.', 'it-l10n-ithemes-security-pro' ); ?></p></div>
		</noscript>
		<?php
	}

	/**
	 * Enqueues JavaScript for the profile fields manager.
	 *
	 * @return void
	 */
	public function enqueue_profile_scripts() {
		global $pagenow, $user_id;

		if ( $pagenow !== 'profile.php' && $pagenow !== 'user-edit.php' ) {
			return;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return;
		}

		$preload_requests = [];
		$preload = ITSEC_Lib::preload_rest_requests( $preload_requests );

		wp_enqueue_script( 'itsec-pages-profile' );
		wp_enqueue_style( 'itsec-pages-profile' );
		wp_add_inline_script(
			'itsec-pages-profile',
			sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload ) )
		);

		$request = new WP_REST_Request( 'GET', '/wp/v2/users/me' );
		$request->set_query_params( [ 'context' => 'edit' ] );
		$response = rest_do_request( $request );

		if ( ! $response->is_error() ) {
			wp_add_inline_script( 'itsec-pages-profile', sprintf(
				"wp.data.dispatch('%s').receiveCurrentUserId( %d );",
				'ithemes-security/core',
				$response->get_data()['id']
			) );
			wp_add_inline_script( 'itsec-pages-profile', sprintf(
				"wp.data.dispatch('%s').receiveUser( %s );",
				'ithemes-security/core',
				wp_json_encode( rest_get_server()->response_to_data( $response, false ) )
			) );
		}

		foreach ( ITSEC_Modules::get_active_modules_to_run() as $module ) {
			$handle = "itsec-{$module}-profile";

			if ( wp_script_is( $handle, 'registered' ) ) {
				wp_enqueue_script( $handle );
			}

			if ( wp_style_is( $handle, 'registered' ) ) {
				wp_enqueue_style( $handle );
			}
		}

		/**
		 * Fires when scripts are enqueued for the User Profile JS code.
		 *
		 * @param WP_User $user
		 */
		do_action( 'itsec_enqueue_profile', $user );
	}

	private function load_file( $file ) {
		$id = $this->get_page_id();

		if ( empty( $id ) ) {
			if ( isset( $GLOBALS['pagenow'], $_GET['page'] ) && 'admin.php' === $GLOBALS['pagenow'] && strpos( $_GET['page'], 'itsec-' ) === 0 ) {
				$id = substr( $_GET['page'], 6 );
			} else {
				return;
			}
		}

		$id = str_replace( '_', '-', $id );

		$file = __DIR__ . '/' . sprintf( $file, $id );
		$file = apply_filters( "itsec-admin-page-file-path-$id", $file );

		if ( is_file( $file ) ) {
			require_once( $file );
		}
	}

	public function handle_user_setting() {
		if ( 'itsec-settings-view' !== $_REQUEST['setting'] ) {
			wp_send_json_error();
		}

		$_REQUEST['setting'] = sanitize_title_with_dashes( $_REQUEST['setting'] );

		if ( ! wp_verify_nonce( $_REQUEST['itsec-user-setting-nonce'], 'set-user-setting-' . $_REQUEST['setting'] ) ) {
			wp_send_json_error();
		}

		if ( ! apply_filters( 'itsec-user-setting-valid-' . $_REQUEST['setting'], true, $_REQUEST['value'] ) ) {
			wp_send_json_error();
		}

		if ( false === update_user_meta( get_current_user_id(), $_REQUEST['setting'], $_REQUEST['value'] ) ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	public function validate_view( $valid, $view ) {
		return in_array( $view, array( 'grid', 'list' ) );
	}

	private function get_icon(): string {
		$icon = apply_filters( 'itsec_dashboard_icon', '' );
		if ( $icon !== '' ) {
			return $icon;
		}

		$icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#a7aaad" d="M460.027 182.286c0 0 5.03125 122.901 -78.5205 219.659c-74.0283 85.7529 -135.929 106.236 -149.045 109.785c-0.805664 0.175781 -1.62695 0.268555 -2.48535 0.268555s-1.69531 -0.0927734 -2.50098 -0.268555c-12.9365 -3.50391 -74.9268 -23.8525 -149.135 -109.785c-83.5518 -96.7578 -78.3408 -219.659 -78.3408 -219.659v-147.518l-0.00292969 -0.00292969c0 -4.31348 3.50195 -7.81543 7.81641 -7.81543c0.0605469 0 0.121094 0.000976562 0.182617 0.00195312c0 0 165.71 -27.9404 221.456 -26.9521s218.644 28.0469 222.894 28.1201c4.25098 0.0732422 7.68164 3.54785 7.68164 7.81641v146.35zM347.727 375.4424l0.224609 -0.224609c73.9395 -85.707 71.0195 -198.008 70.8848 -198.996v-112.569c-0.0771484 -1.64746 -1.18652 -3.02734 -2.69531 -3.50391c-58.9785 -15.1436 -120.498 -23.2676 -184.173 -23.2686h-3.18945c-63.8691 0.238281 -125.64 8.37207 -184.937 23.3584c-1.50879 0.476562 -2.61816 1.85645 -2.69531 3.50391v112.3c-0.0449219 1.16797 -3.00879 113.469 71.1543 199.176c50.1758 58.2617 93.2539 83.3721 114.995 93.6592c0.794922 0.302734 1.66309 0.46875 2.56445 0.46875c0.900391 0 1.76172 -0.166016 2.55664 -0.46875c21.9209 -10.0176 64.9541 -35.0381 115.31 -93.4346zM377.778 179.681c0 1.0332 1.39258 98.8242 -60.8662 171.19c-34.2744 39.71 -63.9668 61.9004 -84.3154 73.9834c-0.674805 0.336914 -1.44629 0.526367 -2.25098 0.526367s-1.56641 -0.189453 -2.24121 -0.526367c-20.4834 -12.1729 -50.1758 -34.3633 -84.3604 -73.9834c-61.0459 -70.4795 -61.0459 -164.722 -61.0459 -170.696v-81.0361c0.0537109 -1.58594 1.16602 -2.90723 2.65039 -3.2793c46.6045 -9.61523 94.3613 -14.7793 143.744 -15.0029h2.875c0.078125 0 -0.357422 0.0117188 -0.279297 0.0117188c49.1494 0 97.126 5.05566 143.439 14.6768c1.49316 0.358398 2.6123 1.68652 2.65039 3.2793v80.8564z"/></svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $icon );
	}
}

new ITSEC_Admin_Page_Loader();
