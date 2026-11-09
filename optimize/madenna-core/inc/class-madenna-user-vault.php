<?php
/**
 * سیستم جامع مدیریت لید، احراز هویت پیامکی (sms.ir)، پرونده کاربری، تاریخ شمسی، اعلانات و پیشنهاد هوشمند
 * نسخه نهایی ۸.۱ - بدون خطای سینتکس
 * Location: /inc/class-madenna-user-vault.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_User_Vault {

    private static $sms_api_key     = 'mBl7bAPsdoiE4fgUOmOZi7GZSdiIJEgzXEEQUcY8bYhbUSCB';
    private static $sms_template_id = 185056;

    const TABLE_RESULTS = 'madenna_user_results';
    const TABLE_NOTICES = 'madenna_vault_notices';

    public function __construct() {
        add_action('init', [$this, 'maybe_create_tables']);
        add_action('init', [$this, 'track_logged_in_user_visit']);
        add_filter('show_admin_bar', [$this, 'hide_admin_bar_for_leads'], 999);
        add_action('wp_head', [$this, 'maybe_output_noindex_meta'], 1);
        add_filter('wp_robots', [$this, 'filter_vault_page_robots'], 999);
        add_filter('wpseo_robots', [$this, 'filter_seo_plugin_robots'], 999);
        add_filter('rank_math/frontend/robots', [$this, 'filter_rankmath_robots'], 999);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_global_vault_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_vazirmatn_font']);
        add_action('rest_api_init', [$this, 'register_vault_routes']);
        
        add_action('wp_ajax_madenna_vault_update_profile', [$this, 'ajax_update_profile']);
        add_action('wp_ajax_madenna_vault_get_notices', [$this, 'ajax_get_user_notices']);
        add_action('wp_ajax_madenna_vault_save_result', [$this, 'ajax_save_result']);
        add_action('wp_ajax_nopriv_madenna_vault_save_result', [$this, 'ajax_save_result']);
        add_action('wp_ajax_madenna_vault_delete_record', [$this, 'ajax_delete_user_record']);

        add_action('admin_menu', [$this, 'register_leads_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
        add_action('wp_login', [$this, 'record_user_login'], 10, 2);
        add_shortcode('madenna_user_vault', [$this, 'render_user_vault_shortcode']);
    }

    public function hide_admin_bar_for_leads($show) {
        if (!current_user_can('edit_posts')) {
            return false;
        }
        return $show;
    }

    public static function is_vault_profile_page() {
        if (is_admin()) {
            return false;
        }
        $post = get_post();
        if (!$post) {
            return false;
        }
        if (in_array($post->post_name, ['my-vault', 'profile', 'dashboard', 'user-vault'], true)) {
            return true;
        }
        if (has_shortcode($post->post_content, 'madenna_user_vault')) {
            return true;
        }
        $el_data = get_post_meta($post->ID, '_elementor_data', true);
        if (is_string($el_data) && strpos($el_data, 'madenna_user_vault') !== false) {
            return true;
        }
        return false;
    }

    public function maybe_output_noindex_meta() {
        if (self::is_vault_profile_page()) {
            echo '<meta name="robots" content="noindex, nofollow, noarchive" />' . "\n";
        }
    }

    public function filter_vault_page_robots($robots) {
        if (self::is_vault_profile_page()) {
            $robots['noindex']   = true;
            $robots['nofollow']  = true;
            $robots['noarchive'] = true;
            unset($robots['index'], $robots['max-image-preview']);
        }
        return $robots;
    }

    public function filter_seo_plugin_robots($robots_str) {
        if (self::is_vault_profile_page()) {
            return 'noindex, nofollow';
        }
        return $robots_str;
    }

    public function filter_rankmath_robots($robots) {
        if (self::is_vault_profile_page() && is_array($robots)) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'nofollow';
        }
        return $robots;
    }

    public static function gregorian_to_jalali($gy, $gm, $gd) {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + (int)(($gy2 + 3) / 4) - (int)(($gy2 + 99) / 100) + (int)(($gy2 + 399) / 400) + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * (int)($days / 12053));
        $days %= 12053;
        $jy += 4 * (int)($days / 1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        if ($days < 186) {
            $jm = 1 + (int)($days / 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + (int)(($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }
        return [$jy, $jm, $jd];
    }

    public static function to_shamsi($datetime_str, $format = 'full', $persian_digits = true) {
        if (empty($datetime_str) || $datetime_str === '0000-00-00' || $datetime_str === '0000-00-00 00:00:00') {
            return '—';
        }
        $ts = is_numeric($datetime_str) ? (int) $datetime_str : strtotime($datetime_str);
        if (!$ts) {
            return $datetime_str;
        }

        $gy = (int) date('Y', $ts);
        $gm = (int) date('n', $ts);
        $gd = (int) date('j', $ts);
        $time_part = date('H:i', $ts);

        list($jy, $jm, $jd) = self::gregorian_to_jalali($gy, $gm, $gd);

        $months = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
            10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
        ];

        if ($format === 'numeric') {
            $out = sprintf('%04d/%02d/%02d - %s', $jy, $jm, $jd, $time_part);
        } elseif ($format === 'date_only') {
            $out = sprintf('%d %s %d', $jd, $months[$jm] ?? '', $jy);
        } else {
            $out = sprintf('%d %s %d - ساعت %s', $jd, $months[$jm] ?? '', $jy, $time_part);
        }

        if ($persian_digits) {
            $en = ['0','1','2','3','4','5','6','7','8','9'];
            $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            $out = str_replace($en, $fa, $out);
        }

        return $out;
    }

    public function enqueue_global_vault_assets() {
        wp_enqueue_style('madenna-vault-css', MADENNA_CORE_URL . 'assets/css/madenna-vault.css', [], '8.1.0');
        wp_enqueue_script('madenna-vault-js', MADENNA_CORE_URL . 'assets/js/madenna-vault.js', ['jquery'], '8.1.0', true);

        $queried_id   = get_queried_object_id() ?: get_the_ID();
        $is_tool_page = (is_singular('tools') && $queried_id);
        $tool_id      = $is_tool_page ? $queried_id : 0;
        $cat_slug     = '';
        $tool_tag     = '';
        if ($is_tool_page && $queried_id) {
            if (class_exists('Madenna_Helpers') && method_exists('Madenna_Helpers', 'get_primary_category')) {
                $p_cat = Madenna_Helpers::get_primary_category($queried_id);
                if ($p_cat) $cat_slug = $p_cat->slug;
            }
            if (!$cat_slug) {
                $cats = wp_get_post_terms($queried_id, 'madenna_cat');
                $cat_slug = (!empty($cats) && !is_wp_error($cats)) ? $cats[0]->slug : '';
            }
            $t_tags = wp_get_post_terms($queried_id, 'madenna_tool_tag');
            $tool_tag = (!empty($t_tags) && !is_wp_error($t_tags)) ? $t_tags[0]->slug : '';
        }

        $current_user_name      = '';
        $current_user_gender    = 'male';
        $current_user_birthdate = '';
        if (is_user_logged_in()) {
            $uid = get_current_user_id();
            $u = wp_get_current_user();
            $current_user_name      = $u ? $u->display_name : '';
            $current_user_gender    = get_user_meta($uid, 'madenna_gender', true) ?: 'male';
            $current_user_birthdate = get_user_meta($uid, 'madenna_birthdate', true) ?: '';
        }

        wp_localize_script('madenna-vault-js', 'madennaVaultConfig', [
            'ajax_url'       => admin_url('admin-ajax.php'),
            'rest_url'       => esc_url_raw(rest_url('madenna/v1/vault/')),
            'is_logged_in'   => is_user_logged_in(),
            'is_tool_page'   => (bool) $is_tool_page,
            'user_id'        => get_current_user_id(),
            'user_name'      => $current_user_name,
            'user_gender'    => $current_user_gender,
            'user_birthdate' => $current_user_birthdate,
            'tool_id'        => (int) $tool_id,
            'tool_tag'       => $tool_tag,
            'page_id'        => 0,
            'category_slug'  => $cat_slug,
            'nonce'          => wp_create_nonce('wp_rest'),
        ]);
    }

    public function enqueue_admin_vazirmatn_font($hook) {
        if (isset($_GET['page']) && $_GET['page'] === 'madenna-vault-leads') {
            wp_enqueue_style('vazirmatn-font', 'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css', [], '33.003');
        }
    }

    public function track_logged_in_user_visit() {
        if (is_user_logged_in() && !is_admin()) {
            $uid = get_current_user_id();
            $today = current_time('Y-m-d');
            $last_tracked = get_user_meta($uid, 'madenna_last_visit_date', true);
            if ($last_tracked !== $today) {
                $now = current_time('mysql');
                if (!get_user_meta($uid, 'madenna_first_login', true)) {
                    update_user_meta($uid, 'madenna_first_login', $now);
                }
                update_user_meta($uid, 'madenna_last_login', $now);
                update_user_meta($uid, 'madenna_last_visit_date', $today);
            }
        }
    }

    public static function get_avatar_svg($gender = 'male', $size = 56) {
        $s = intval($size);
        if ($gender === 'female') {
            return '<svg viewBox="0 0 100 100" width="' . $s . '" height="' . $s . '" fill="none" xmlns="http://www.w3.org/2000/svg" class="mad-avatar-svg female">
                <circle cx="50" cy="50" r="48" fill="#FEF3C7" stroke="#FCD34D" stroke-width="2.5"/>
                <path d="M24 50C24 28 35 16 50 16C65 16 76 28 76 50C76 64 72 78 68 84H32C28 78 24 64 24 50Z" fill="#3E2723"/>
                <path d="M22 88C24 72 34 66 50 66C66 66 76 72 78 88C69 94 60 97 50 97C40 97 31 94 22 88Z" fill="#D97706"/>
                <path d="M38 66L50 78L62 66" fill="#FDE68A"/>
                <rect x="43" y="56" width="14" height="15" rx="6" fill="#F5CBA7"/>
                <ellipse cx="50" cy="46" rx="19" ry="21" fill="#FFD8B8"/>
                <ellipse cx="38" cy="50" rx="3.5" ry="2" fill="#F4A28C" opacity="0.6"/>
                <ellipse cx="62" cy="50" rx="3.5" ry="2" fill="#F4A28C" opacity="0.6"/>
                <circle cx="41" cy="45" r="2.8" fill="#1E293B"/>
                <circle cx="42" cy="44" r="1" fill="#FFFFFF"/>
                <circle cx="59" cy="45" r="2.8" fill="#1E293B"/>
                <circle cx="60" cy="44" r="1" fill="#FFFFFF"/>
                <path d="M37 39C39 37.5 43 37.5 45 39" stroke="#3E2723" stroke-width="2" stroke-linecap="round"/>
                <path d="M55 39C57 37.5 61 37.5 63 39" stroke="#3E2723" stroke-width="2" stroke-linecap="round"/>
                <path d="M44 54C46.5 57.5 53.5 57.5 56 54" stroke="#E11D48" stroke-width="2.4" stroke-linecap="round"/>
                <path d="M30 42C31 28 40 22 50 22C60 22 69 28 70 42C65 34 58 31 50 34C42 31 35 34 30 42Z" fill="#4E342E"/>
                <circle cx="30" cy="49" r="2.5" fill="#F59E0B"/>
                <circle cx="70" cy="49" r="2.5" fill="#F59E0B"/>
            </svg>';
        }

        return '<svg viewBox="0 0 100 100" width="' . $s . '" height="' . $s . '" fill="none" xmlns="http://www.w3.org/2000/svg" class="mad-avatar-svg male">
            <circle cx="50" cy="50" r="48" fill="#E6F4F1" stroke="#99F6E4" stroke-width="2.5"/>
            <path d="M20 88C22 71 33 65 50 65C67 65 78 71 80 88C71 94 61 97 50 97C39 97 29 94 20 88Z" fill="#0F5650"/>
            <path d="M37 65L50 79L63 65" fill="#C2E7E2"/>
            <rect x="42" y="55" width="16" height="16" rx="7" fill="#F5CBA7"/>
            <circle cx="29" cy="47" r="4" fill="#FFD8B8"/>
            <circle cx="71" cy="47" r="4" fill="#FFD8B8"/>
            <ellipse cx="50" cy="46" rx="20" ry="21" fill="#FFD8B8"/>
            <ellipse cx="38" cy="50" rx="3.5" ry="1.8" fill="#F4A28C" opacity="0.45"/>
            <ellipse cx="62" cy="50" rx="3.5" ry="1.8" fill="#F4A28C" opacity="0.45"/>
            <circle cx="41" cy="45" r="2.8" fill="#0F172A"/>
            <circle cx="42" cy="44" r="1" fill="#FFFFFF"/>
            <circle cx="59" cy="45" r="2.8" fill="#0F172A"/>
            <circle cx="60" cy="44" r="1" fill="#FFFFFF"/>
            <path d="M36 39C39 37.5 43 37.8 45 39" stroke="#1E293B" stroke-width="2.4" stroke-linecap="round"/>
            <path d="M55 39C57 37.8 61 37.5 64 39" stroke="#1E293B" stroke-width="2.4" stroke-linecap="round"/>
            <path d="M44 54C46.5 57.5 53.5 57.5 56 54" stroke="#C2410C" stroke-width="2.4" stroke-linecap="round"/>
            <path d="M29 41C28 26 37 17 50 17C64 17 72 25 71 41C67 32 60 28 50 29C40 28 33 32 29 41Z" fill="#1E293B"/>
            <path d="M36 22C42 16 55 15 63 22" stroke="#334155" stroke-width="3" stroke-linecap="round"/>
        </svg>';
    }

    public static function get_unseen_badge_count($user_id = 0) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) {
            return 0;
        }

        global $wpdb;
        $last_seen = get_user_meta($user_id, 'madenna_vault_last_seen', true);
        if (empty($last_seen)) {
            $last_seen = '2000-01-01 00:00:00';
        }

        $table_results = $wpdb->prefix . self::TABLE_RESULTS;
        $new_results = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_results} WHERE user_id = %d AND user_deleted = 0 AND created_at > %s",
            $user_id,
            $last_seen
        ));

        $notices_data = self::get_matched_notices_for_user($user_id, $last_seen);
        $new_notices  = count($notices_data['notifications']) + count($notices_data['offers']);

        return $new_results + $new_notices;
    }

    public static function mark_vault_as_seen($user_id = 0) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        if ($user_id) {
            update_user_meta($user_id, 'madenna_vault_last_seen', current_time('mysql'));
        }
    }

    public function record_user_login($user_login, $user) {
        if ($user && isset($user->ID)) {
            $now = current_time('mysql');
            if (!get_user_meta($user->ID, 'madenna_first_login', true)) {
                update_user_meta($user->ID, 'madenna_first_login', $now);
            }
            update_user_meta($user->ID, 'madenna_last_login', $now);
        }
    }

    public function maybe_create_tables() {
        if (get_option('madenna_vault_db_version') === '8.1') {
            return;
        }

        global $wpdb;
        self::create_vault_table();

        $table_results = $wpdb->prefix . self::TABLE_RESULTS;
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_results}'") === $table_results) {
            $res_cols = $wpdb->get_col("DESC {$table_results}", 0);
            if (is_array($res_cols) && !in_array('user_deleted', $res_cols)) {
                $wpdb->query("ALTER TABLE {$table_results} ADD COLUMN user_deleted tinyint(1) DEFAULT 0 NOT NULL AFTER inputs_json");
            }
            $wpdb->query(
                "DELETE r FROM {$table_results} r 
                 LEFT JOIN {$wpdb->posts} p ON r.tool_id = p.ID 
                 WHERE r.tool_id = 0 OR p.ID IS NULL OR p.post_type != 'tools'"
            );
        }

        $table_notices = $wpdb->prefix . self::TABLE_NOTICES;
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_notices}'") === $table_notices) {
            $cols = $wpdb->get_col("DESC {$table_notices}", 0);
            if (is_array($cols) && !in_array('recommended_post_id', $cols)) {
                $wpdb->query("ALTER TABLE {$table_notices} ADD COLUMN recommended_post_id bigint(20) unsigned DEFAULT 0 NOT NULL AFTER content");
            }
            if (is_array($cols) && !in_array('recommended_post_type', $cols)) {
                $wpdb->query("ALTER TABLE {$table_notices} ADD COLUMN recommended_post_type varchar(30) DEFAULT '' NOT NULL AFTER recommended_post_id");
            }
        }

        update_option('madenna_vault_db_version', '8.1');
    }

    public static function create_vault_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_results = $wpdb->prefix . self::TABLE_RESULTS;
        $sql_results = "CREATE TABLE {$table_results} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            tool_id bigint(20) unsigned NOT NULL,
            category_slug varchar(120) DEFAULT '' NOT NULL,
            result_title varchar(255) DEFAULT '' NOT NULL,
            result_html longtext NOT NULL,
            inputs_json longtext DEFAULT NULL,
            user_deleted tinyint(1) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY tool_id (tool_id),
            KEY category_slug (category_slug),
            KEY user_deleted (user_deleted)
        ) {$charset_collate};";
        dbDelta($sql_results);

        $table_notices = $wpdb->prefix . self::TABLE_NOTICES;
        $sql_notices = "CREATE TABLE {$table_notices} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            type varchar(30) DEFAULT 'notification' NOT NULL,
            title varchar(255) NOT NULL,
            content longtext NOT NULL,
            recommended_post_id bigint(20) unsigned DEFAULT 0 NOT NULL,
            recommended_post_type varchar(30) DEFAULT '' NOT NULL,
            target_categories text DEFAULT NULL,
            target_first_tool bigint(20) unsigned DEFAULT 0 NOT NULL,
            expires_at date DEFAULT NULL,
            auto_delete tinyint(1) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY type (type),
            KEY expires_at (expires_at)
        ) {$charset_collate};";
        dbDelta($sql_notices);
    }

    public function register_vault_routes() {
        register_rest_route('madenna/v1', '/vault/send-otp', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_send_otp'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('madenna/v1', '/vault/verify-otp', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_verify_otp'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('madenna/v1', '/vault/save-result', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_save_result'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('madenna/v1', '/vault/update-profile', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_update_profile'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('madenna/v1', '/vault/get-notices', [
            'methods'             => 'GET',
            'callback'            => [$this, 'handle_get_user_notices'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function clean_mobile_number($mobile) {
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','۸','۹'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        $mobile  = str_replace($persian, $english, (string) $mobile);
        $mobile  = str_replace($arabic, $english, $mobile);
        $mobile  = preg_replace('/[^0-9]/', '', $mobile);

        if (strpos($mobile, '989') === 0 && strlen($mobile) === 12) {
            $mobile = '0' . substr($mobile, 2);
        } elseif (strpos($mobile, '9') === 0 && strlen($mobile) === 10) {
            $mobile = '0' . $mobile;
        }
        if (preg_match('/^09[0-9]{9}$/', $mobile)) {
            return $mobile;
        }
        return false;
    }

    public function handle_send_otp($request) {
        $params    = $request->get_json_params();
        $raw_phone = $params['mobile'] ?? '';
        $auth_mode = sanitize_text_field($params['auth_mode'] ?? 'register');
        $mobile    = self::clean_mobile_number($raw_phone);

        if (!$mobile) {
            return new WP_REST_Response(['success' => false, 'message' => 'شماره موبایل واردشده معتبر نیست.'], 400);
        }

        $client_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ip_key    = 'mad_otp_ip_' . md5($client_ip);
        $ip_count  = (int) get_transient($ip_key);
        if ($ip_count >= 5) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً ۱۰ دقیقه دیگر تلاش کنید.'
            ], 429);
        }

        if ($auth_mode === 'login') {
            $existing_user = get_user_by('login', $mobile);
            if (!$existing_user) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'حسابی با این شماره یافت نشد. لطفاً از تب «ثبت‌نام» استفاده کنید.'
                ], 404);
            }
        }

        $rate_key = 'mad_otp_limit_' . md5($mobile);
        if (get_transient($rate_key)) {
            return new WP_REST_Response(['success' => false, 'message' => 'کد قبلاً ارسال شده است. لطفاً کمی صبر کنید.'], 429);
        }

        $otp_code = wp_rand(1000, 9999);
        set_transient('mad_otp_code_' . md5($mobile), (string) $otp_code, 180);
        delete_transient('mad_otp_tries_' . md5($mobile));
        set_transient($rate_key, 1, 60);
        set_transient($ip_key, $ip_count + 1, 600);

        $sms_sent = self::send_sms_ir_verify($mobile, $otp_code);
        if (!$sms_sent) {
            delete_transient($rate_key);
            return new WP_REST_Response(['success' => false, 'message' => 'خطا در ارتباط با سامانه پیامکی.'], 500);
        }

        return new WP_REST_Response([
            'success'    => true,
            'message'    => 'کد تایید ۴ رقمی ارسال شد.',
            'expires_in' => 120
        ], 200);
    }

    public static function send_sms_ir_verify($mobile, $code) {
        $payload = [
            'mobile'     => $mobile,
            'templateId' => (int) self::$sms_template_id,
            'parameters' => [
                ['name' => 'CODE', 'value' => (string) $code]
            ]
        ];

        $response = wp_remote_post('https://api.sms.ir/v1/send/verify', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'text/plain',
                'x-api-key'    => self::$sms_api_key,
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return false;
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return (isset($body['status']) && (int) $body['status'] === 1);
    }

    public function handle_verify_otp($request) {
        $params    = $request->get_json_params();
        $mobile    = self::clean_mobile_number($params['mobile'] ?? '');
        $code      = preg_replace('/[^0-9]/', '', $params['code'] ?? '');
        $full_name = sanitize_text_field($params['full_name'] ?? '');
        $gender    = in_array($params['gender'] ?? '', ['male', 'female'], true) ? $params['gender'] : 'male';
        $tool_id   = intval($params['tool_id'] ?? 0);
        $cat_slug  = sanitize_text_field($params['category_slug'] ?? '');

        if (!$mobile || empty($code)) {
            return new WP_REST_Response(['success' => false, 'message' => 'اطلاعات ناقص است.'], 400);
        }

        $tries_key = 'mad_otp_tries_' . md5($mobile);
        $tries     = (int) get_transient($tries_key);
        if ($tries >= 5) {
            delete_transient('mad_otp_code_' . md5($mobile));
            return new WP_REST_Response([
                'success' => false,
                'message' => 'تعداد خطاهای وارد کردن کد بیش از حد مجاز بود. لطفاً کد جدید دریافت کنید.'
            ], 429);
        }

        $saved_code = get_transient('mad_otp_code_' . md5($mobile));
        if (!$saved_code || (string) $saved_code !== (string) $code) {
            set_transient($tries_key, $tries + 1, 180);
            return new WP_REST_Response(['success' => false, 'message' => 'کد واردشده نادرست یا منقضی شده است.'], 400);
        }

        delete_transient('mad_otp_code_' . md5($mobile));
        delete_transient($tries_key);

        $user = get_user_by('login', $mobile);
        $is_new_user = false;
        $now = current_time('mysql');

        if (!$user) {
            $random_pass = wp_generate_password(16, true, true);
            $user_id     = wp_create_user($mobile, $random_pass);
            if (is_wp_error($user_id)) {
                return new WP_REST_Response(['success' => false, 'message' => 'خطا در ساخت حساب کاربری.'], 500);
            }

            $is_new_user = true;
            $display_name = !empty($full_name) ? $full_name : 'کاربر ' . substr($mobile, -4);
            wp_update_user([
                'ID'           => $user_id,
                'display_name' => $display_name,
                'first_name'   => $full_name,
            ]);

            update_user_meta($user_id, 'madenna_mobile', $mobile);
            update_user_meta($user_id, 'madenna_gender', $gender);
            update_user_meta($user_id, 'madenna_first_tool_id', $tool_id);
            update_user_meta($user_id, 'madenna_first_category', $cat_slug);
            update_user_meta($user_id, 'madenna_joined_at', $now);
            update_user_meta($user_id, 'madenna_first_login', $now);
            update_user_meta($user_id, 'show_admin_bar_front', 'false');
        } else {
            $user_id = $user->ID;
            if (!current_user_can('edit_posts')) {
                update_user_meta($user_id, 'show_admin_bar_front', 'false');
            }
            if (!empty($full_name)) {
                wp_update_user([
                    'ID'           => $user_id,
                    'display_name' => $full_name,
                    'first_name'   => $full_name,
                ]);
            }
            if (!empty($params['gender'])) {
                update_user_meta($user_id, 'madenna_gender', $gender);
            }
            if (!get_user_meta($user_id, 'madenna_first_login', true)) {
                update_user_meta($user_id, 'madenna_first_login', $now);
            }
        }

        update_user_meta($user_id, 'madenna_last_login', $now);
        update_user_meta($user_id, 'madenna_last_visit_date', current_time('Y-m-d'));

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);

        $saved_record_id = 0;
        $result_html     = isset($params['result_html']) ? wp_unslash($params['result_html']) : '';
        $inputs_json     = isset($params['inputs_json']) ? wp_unslash($params['inputs_json']) : null;
        if (!empty($result_html)) {
            $saved_record_id = self::insert_tool_result_record($user_id, $tool_id, $cat_slug, $result_html, $params['result_title'] ?? '', $inputs_json);
        }

        $u_obj = get_userdata($user_id);
        $final_gender = get_user_meta($user_id, 'madenna_gender', true) ?: 'male';

        return new WP_REST_Response([
            'success'         => true,
            'user_id'         => $user_id,
            'user_name'       => $u_obj ? $u_obj->display_name : $full_name,
            'gender'          => $final_gender,
            'birthdate'       => get_user_meta($user_id, 'madenna_birthdate', true) ?: '',
            'avatar_svg'      => self::get_avatar_svg($final_gender, 36),
            'unseen_count'    => self::get_unseen_badge_count($user_id),
            'is_new_user'     => $is_new_user,
            'new_nonce'       => wp_create_nonce('wp_rest'),
            'saved_record_id' => $saved_record_id,
        ], 200);
    }

    public function ajax_update_profile() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'ابتدا وارد حساب خود شوید.']);
        }

        $name      = sanitize_text_field($_POST['full_name'] ?? '');
        $gender    = in_array($_POST['gender'] ?? '', ['male', 'female'], true) ? $_POST['gender'] : 'male';
        $birthdate = sanitize_text_field($_POST['birthdate'] ?? '');

        if (!empty($name)) {
            wp_update_user([
                'ID'           => $user_id,
                'display_name' => $name,
                'first_name'   => $name,
            ]);
        }
        update_user_meta($user_id, 'madenna_gender', $gender);
        if (isset($_POST['birthdate'])) {
            update_user_meta($user_id, 'madenna_birthdate', $birthdate);
        }

        wp_send_json_success([
            'name'              => $name,
            'gender'            => $gender,
            'birthdate'         => $birthdate,
            'avatar_svg'        => self::get_avatar_svg($gender, 64),
            'header_avatar_svg' => self::get_avatar_svg($gender, 36),
        ]);
    }

    public function handle_update_profile($request) {
        $params  = $request->get_json_params();
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_REST_Response(['success' => false, 'message' => 'ابتدا وارد شوید.'], 401);
        }

        $name      = sanitize_text_field($params['full_name'] ?? '');
        $gender    = in_array($params['gender'] ?? '', ['male', 'female'], true) ? $params['gender'] : 'male';
        $birthdate = sanitize_text_field($params['birthdate'] ?? '');

        if (!empty($name)) {
            wp_update_user([
                'ID'           => $user_id,
                'display_name' => $name,
                'first_name'   => $name,
            ]);
        }
        update_user_meta($user_id, 'madenna_gender', $gender);
        if (isset($params['birthdate'])) {
            update_user_meta($user_id, 'madenna_birthdate', $birthdate);
        }

        return new WP_REST_Response([
            'success'           => true,
            'name'              => $name,
            'gender'            => $gender,
            'birthdate'         => $birthdate,
            'avatar_svg'        => self::get_avatar_svg($gender, 64),
            'header_avatar_svg' => self::get_avatar_svg($gender, 36),
        ], 200);
    }

    public function ajax_delete_user_record() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'ابتدا وارد حساب خود شوید.']);
        }

        $record_id = intval($_POST['record_id'] ?? 0);
        if (!$record_id) {
            wp_send_json_error(['message' => 'شناسه نامعتبر است.']);
        }

        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_RESULTS;

        $updated = $wpdb->update(
            $table,
            ['user_deleted' => 1],
            ['id' => $record_id, 'user_id' => $user_id],
            ['%d'],
            ['%d', '%d']
        );

        if ($updated !== false) {
            wp_send_json_success(['record_id' => $record_id]);
        }
        wp_send_json_error(['message' => 'خطا در حذف سابقه.']);
    }

    public static function sanitize_snapshot_html($html) {
        if (empty($html)) {
            return '';
        }
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);
        $html = preg_replace_callback('/src=["\']data:image\/[^;]+;base64,([^"\']+)["\']/i', function ($matches) {
            if (strlen($matches[1]) > 220000) {
                return 'src="" data-heavy-image-removed="true"';
            }
            return $matches[0];
        }, $html);

        return $html;
    }

    public static function insert_tool_result_record($user_id, $tool_id, $cat_slug, $result_html, $result_title = '', $inputs_json = null) {
        global $wpdb;
        $user_id     = intval($user_id);
        $tool_id     = intval($tool_id);
        $result_html = self::sanitize_snapshot_html($result_html);

        if (!$user_id || $tool_id <= 0 || get_post_type($tool_id) !== 'tools' || empty($result_html)) {
            return 0;
        }

        $table = $wpdb->prefix . self::TABLE_RESULTS;
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            self::create_vault_table();
        }

        if (empty($cat_slug) && $tool_id > 0) {
            if (class_exists('Madenna_Helpers') && method_exists('Madenna_Helpers', 'get_primary_category')) {
                $primary_term = Madenna_Helpers::get_primary_category($tool_id);
                if ($primary_term) {
                    $cat_slug = $primary_term->slug;
                }
            }
            if (empty($cat_slug)) {
                $cats = wp_get_post_terms($tool_id, 'madenna_cat');
                if (!empty($cats) && !is_wp_error($cats)) {
                    $cat_slug = $cats[0]->slug;
                }
            }
        }

        if (empty($result_title)) {
            $result_title = $tool_id > 0 ? get_the_title($tool_id) : 'نتیجه ابزار مادنا';
        }

        $inserted = $wpdb->insert(
            $table,
            [
                'user_id'       => $user_id,
                'tool_id'       => $tool_id,
                'category_slug' => sanitize_text_field($cat_slug),
                'result_title'  => sanitize_text_field($result_title),
                'result_html'   => $result_html,
                'inputs_json'   => $inputs_json,
                'created_at'    => current_time('mysql'),
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s']
        );

        return $inserted ? (int) $wpdb->insert_id : 0;
    }

    public function ajax_save_result() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'کاربر وارد نشده است.']);
        }

        $tool_id      = intval($_POST['tool_id'] ?? 0);
        $cat_slug     = sanitize_text_field($_POST['category_slug'] ?? '');
        $result_title = sanitize_text_field($_POST['result_title'] ?? '');
        $result_html  = isset($_POST['result_html']) ? wp_unslash($_POST['result_html']) : '';
        $inputs_json  = isset($_POST['inputs_json']) ? wp_unslash($_POST['inputs_json']) : null;

        $record_id = self::insert_tool_result_record($user_id, $tool_id, $cat_slug, $result_html, $result_title, $inputs_json);
        if ($record_id > 0) {
            wp_send_json_success([
                'record_id'    => $record_id,
                'unseen_count' => self::get_unseen_badge_count($user_id),
            ]);
        }
        wp_send_json_error(['message' => 'خطا در ذخیره نتیجه.']);
    }

    public function handle_save_result($request) {
        $params  = $request->get_json_params();
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_REST_Response(['success' => false, 'message' => 'کاربر وارد نشده است.'], 401);
        }

        $tool_id      = intval($params['tool_id'] ?? 0);
        $cat_slug     = sanitize_text_field($params['category_slug'] ?? '');
        $result_title = sanitize_text_field($params['result_title'] ?? '');
        $result_html  = isset($params['result_html']) ? wp_unslash($params['result_html']) : '';
        $inputs_json  = isset($params['inputs']) ? wp_json_encode($params['inputs']) : null;

        $record_id = self::insert_tool_result_record($user_id, $tool_id, $cat_slug, $result_html, $result_title, $inputs_json);

        return new WP_REST_Response([
            'success'      => ($record_id > 0),
            'record_id'    => $record_id,
            'unseen_count' => self::get_unseen_badge_count($user_id),
        ], 200);
    }

    public static function get_matched_notices_for_user($user_id = 0, $after_date = '') {
        global $wpdb;
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) {
            return ['notifications' => [], 'offers' => []];
        }

        $table_notices = $wpdb->prefix . self::TABLE_NOTICES;
        $table_results = $wpdb->prefix . self::TABLE_RESULTS;
        $now = current_time('Y-m-d');

        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_notices} WHERE expires_at IS NOT NULL AND expires_at != '0000-00-00' AND expires_at < %s AND auto_delete = 1",
            $now
        ));

        $first_tool = (int) get_user_meta($user_id, 'madenna_first_tool_id', true);
        $user_cats  = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT category_slug FROM {$table_results} WHERE user_id = %d AND category_slug != ''",
            $user_id
        ));
        $first_cat = get_user_meta($user_id, 'madenna_first_category', true);
        if ($first_cat && !in_array($first_cat, $user_cats)) {
            $user_cats[] = $first_cat;
        }

        $expanded_user_cats = $user_cats;
        foreach ($user_cats as $c_slug) {
            $term_obj = get_term_by('slug', $c_slug, 'madenna_cat');
            if ($term_obj && !is_wp_error($term_obj) && $term_obj->parent > 0) {
                $parent_obj = get_term($term_obj->parent, 'madenna_cat');
                if ($parent_obj && !is_wp_error($parent_obj)) {
                    $expanded_user_cats[] = $parent_obj->slug;
                }
            }
        }
        $expanded_user_cats = array_unique($expanded_user_cats);

        $where_after = '';
        if (!empty($after_date)) {
            $where_after = $wpdb->prepare(" AND created_at > %s", $after_date);
        }

        $raw_notices = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_notices} WHERE (expires_at IS NULL OR expires_at = '0000-00-00' OR expires_at >= %s) {$where_after} ORDER BY created_at DESC",
            $now
        ));

        $notifications = [];
        $offers        = [];

        if (!empty($raw_notices)) {
            foreach ($raw_notices as $row) {
                $match_cat = true;
                if (!empty($row->target_categories)) {
                    $targets = array_filter(array_map('trim', explode(',', $row->target_categories)));
                    if (!empty($targets)) {
                        $intersect = array_intersect($targets, $expanded_user_cats);
                        $match_cat = !empty($intersect);
                    }
                }

                $match_tool = true;
                if (!empty($row->target_first_tool) && (int) $row->target_first_tool > 0) {
                    $match_tool = ($first_tool === (int) $row->target_first_tool);
                }

                if ($match_cat && $match_tool) {
                    $item = [
                        'id'         => $row->id,
                        'title'      => $row->title,
                        'content'    => wpautop($row->content),
                        'expires_at' => ($row->expires_at && $row->expires_at !== '0000-00-00') ? self::to_shamsi($row->expires_at, 'date_only') : '',
                        'created_at' => self::to_shamsi($row->created_at, 'date_only'),
                    ];

                    if ($row->type === 'offer' && !empty($row->recommended_post_id)) {
                        $p_id  = (int) $row->recommended_post_id;
                        $p_obj = get_post($p_id);
                        if ($p_obj) {
                            $pt = $p_obj->post_type;
                            $item['post_id']    = $p_id;
                            $item['post_type']  = $pt;
                            $item['post_title'] = $p_obj->post_title;
                            $item['post_url']   = get_permalink($p_id);
                            $item['subtitle']   = ($pt === 'tools') ? get_post_meta($p_id, '_tool_subtitle', true) : get_post_meta($p_id, '_article_subtitle', true);
                            $item['icon_class'] = ($pt === 'tools') ? (get_post_meta($p_id, '_tool_icon_class', true) ?: 'fa-solid fa-wrench') : (get_post_meta($p_id, '_article_icon_class', true) ?: 'fa-solid fa-book-open');
                            $item['badge_text'] = ($pt === 'tools') ? '🛠️ ابزار پیشنهادی' : '📚 مقاله پیشنهادی';
                            $item['btn_text']   = ($pt === 'tools') ? 'اجرای ابزار آنلاین ←' : 'مطالعه کامل مقاله ←';
                        }
                    }

                    if ($row->type === 'offer') {
                        $offers[] = $item;
                    } else {
                        $notifications[] = $item;
                    }
                }
            }
        }

        return [
            'notifications' => $notifications,
            'offers'        => $offers,
        ];
    }

    public function ajax_get_user_notices() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error();
        }
        $data = self::get_matched_notices_for_user($user_id);
        self::mark_vault_as_seen($user_id);
        wp_send_json_success($data);
    }

    public function handle_get_user_notices($request) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_REST_Response(['success' => false], 401);
        }
        $data = self::get_matched_notices_for_user($user_id);
        self::mark_vault_as_seen($user_id);

        return new WP_REST_Response([
            'success'       => true,
            'notifications' => $data['notifications'],
            'offers'        => $data['offers'],
        ], 200);
    }

    public function render_user_vault_shortcode() {
        if (!class_exists('\Elementor\Widget_Base')) {
            return '';
        }
        if (!class_exists('Madenna_User_Vault_Elementor_Widget')) {
            require_once MADENNA_CORE_DIR . 'inc/elementor/widgets/class-widget-user-vault.php';
        }
        ob_start();
        $widget = new Madenna_User_Vault_Elementor_Widget();
        $widget->render_vault_dashboard_content([]);
        return ob_get_clean();
    }

    public function register_leads_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=tools',
            'مرکز مدیریت لیدها، اعلانات و پیشنهادات',
            '👥 لیدها و اعلانات (Vault)',
            'manage_options',
            'madenna-vault-leads',
            [$this, 'render_leads_admin_page']
        );
    }

    public function handle_admin_actions() {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table_results = $wpdb->prefix . self::TABLE_RESULTS;
        $table_notices = $wpdb->prefix . self::TABLE_NOTICES;

        if (isset($_POST['madenna_delete_users_action']) && check_admin_referer('madenna_vault_admin_nonce')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
            $user_ids = isset($_POST['selected_users']) && is_array($_POST['selected_users']) ? array_map('intval', $_POST['selected_users']) : [];
            foreach ($user_ids as $uid) {
                if ($uid !== get_current_user_id() && !user_can($uid, 'manage_options')) {
                    $wpdb->delete($table_results, ['user_id' => $uid], ['%d']);
                    wp_delete_user($uid);
                }
            }
            wp_redirect(admin_url('edit.php?post_type=tools&page=madenna-vault-leads&msg=users_deleted'));
            exit;
        }

        if (isset($_POST['madenna_create_notice_action']) && check_admin_referer('madenna_vault_admin_nonce')) {
            $type      = in_array($_POST['notice_type'] ?? '', ['notification', 'offer'], true) ? $_POST['notice_type'] : 'notification';
            $title     = sanitize_text_field($_POST['notice_title'] ?? '');
            $content   = wp_kses_post($_POST['notice_content'] ?? '');
            $rec_post  = intval($_POST['recommended_post_id'] ?? 0);
            $rec_type  = '';

            if ($type === 'offer' && $rec_post > 0) {
                $p_obj = get_post($rec_post);
                if ($p_obj) {
                    $rec_type = $p_obj->post_type;
                    if (empty($title)) {
                        $title = $p_obj->post_title;
                    }
                }
            }

            $cats_arr  = isset($_POST['target_categories']) && is_array($_POST['target_categories']) ? array_map('sanitize_text_field', $_POST['target_categories']) : [];
            $f_tool    = intval($_POST['target_first_tool'] ?? 0);
            $expires   = !empty($_POST['expires_at']) ? sanitize_text_field($_POST['expires_at']) : null;
            $auto_del  = !empty($_POST['auto_delete']) ? 1 : 0;

            if (!empty($title)) {
                $wpdb->insert(
                    $table_notices,
                    [
                        'type'                  => $type,
                        'title'                 => $title,
                        'content'               => $content,
                        'recommended_post_id'   => $rec_post,
                        'recommended_post_type' => $rec_type,
                        'target_categories'     => implode(',', $cats_arr),
                        'target_first_tool'     => $f_tool,
                        'expires_at'            => $expires,
                        'auto_delete'           => $auto_del,
                        'created_at'            => current_time('mysql'),
                    ]
                );
            }
            wp_redirect(admin_url('edit.php?post_type=tools&page=madenna-vault-leads&tab=notices&msg=notice_saved'));
            exit;
        }

        if (isset($_GET['del_notice']) && isset($_GET['page']) && $_GET['page'] === 'madenna-vault-leads') {
            $nid = intval($_GET['del_notice']);
            $wpdb->delete($table_notices, ['id' => $nid], ['%d']);
            wp_redirect(admin_url('edit.php?post_type=tools&page=madenna-vault-leads&tab=notices'));
            exit;
        }

        if (isset($_GET['madenna_export_csv']) && $_GET['madenna_export_csv'] === '1') {
            $users_list = $this->get_filtered_leads_from_request();

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=madenna-leads-' . date('Y-m-d') . '.csv');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['شناسه', 'شماره موبایل', 'نام و نام خانوادگی', 'جنسیت', 'اولین ابزار ورودی', 'دسته‌بندی‌های فعال', 'کل استفاده از ابزارها', 'اولین ورود (شمسی)', 'آخرین بازدید (شمسی)']);

            foreach ($users_list as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['mobile'],
                    $row['name'],
                    $row['gender_label'],
                    $row['first_tool_title'],
                    $row['categories'],
                    $row['total_tests'],
                    $row['first_login'],
                    $row['last_login'],
                ]);
            }
            fclose($output);
            exit;
        }
    }

    private function get_filtered_leads_from_request() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_RESULTS;

        $filter_cats       = isset($_GET['filter_cats']) && is_array($_GET['filter_cats']) ? array_filter(array_map('sanitize_text_field', $_GET['filter_cats'])) : [];
        $filter_first_tool = isset($_GET['filter_first_tool']) ? intval($_GET['filter_first_tool']) : 0;
        $inactivity_preset = isset($_GET['inactivity_preset']) ? sanitize_text_field($_GET['inactivity_preset']) : '';
        $not_visited_since = isset($_GET['not_visited_since']) ? sanitize_text_field($_GET['not_visited_since']) : '';

        $users = get_users([
            'role__not_in' => ['administrator', 'editor'],
            'orderby'      => 'registered',
            'order'        => 'DESC',
            'number'       => 2500,
        ]);

        $stats_rows = $wpdb->get_results(
            "SELECT user_id, COUNT(*) as total_cnt, GROUP_CONCAT(DISTINCT NULLIF(category_slug, '') SEPARATOR ',') as cats_csv 
             FROM {$table} 
             GROUP BY user_id"
        );
        $user_stats_map = [];
        if (!empty($stats_rows)) {
            foreach ($stats_rows as $srow) {
                $uid_key = (int) $srow->user_id;
                $cats_arr = !empty($srow->cats_csv) ? array_filter(explode(',', $srow->cats_csv)) : [];
                $user_stats_map[$uid_key] = [
                    'total' => (int) $srow->total_cnt,
                    'cats'  => $cats_arr,
                ];
            }
        }

        $results = [];

        foreach ($users as $u) {
            if (user_can($u->ID, 'manage_options')) {
                continue;
            }

            $mobile      = get_user_meta($u->ID, 'madenna_mobile', true) ?: $u->user_login;
            $gender      = get_user_meta($u->ID, 'madenna_gender', true) ?: 'male';
            $first_tool  = (int) get_user_meta($u->ID, 'madenna_first_tool_id', true);
            $joined      = get_user_meta($u->ID, 'madenna_joined_at', true) ?: $u->user_registered;
            $first_login = get_user_meta($u->ID, 'madenna_first_login', true) ?: $joined;
            $last_login  = get_user_meta($u->ID, 'madenna_last_login', true) ?: $first_login;

            if ($filter_first_tool > 0 && $first_tool !== $filter_first_tool) {
                continue;
            }

            $first_day = date('Y-m-d', strtotime($first_login));
            $last_day  = date('Y-m-d', strtotime($last_login));
            $is_first_visit_only = ($first_day === $last_day);

            $last_seen_ts  = strtotime($last_login);
            $days_inactive = max(0, floor((current_time('timestamp') - $last_seen_ts) / DAY_IN_SECONDS));

            if ($inactivity_preset === 'inactive_7' && $days_inactive < 7) {
                continue;
            } elseif ($inactivity_preset === 'inactive_30' && $days_inactive < 30) {
                continue;
            } elseif ($inactivity_preset === 'active_7' && $days_inactive > 7) {
                continue;
            }

            if (!empty($not_visited_since)) {
                $cutoff_ts = strtotime($not_visited_since . ' 23:59:59');
                if ($last_seen_ts > $cutoff_ts) {
                    continue;
                }
            }

            $user_cats   = $user_stats_map[$u->ID]['cats'] ?? [];
            $total_tests = $user_stats_map[$u->ID]['total'] ?? 0;

            if (!empty($filter_cats)) {
                $intersect = array_intersect($filter_cats, $user_cats);
                if (empty($intersect)) {
                    continue;
                }
            }

            $results[] = [
                'id'                  => $u->ID,
                'mobile'              => $mobile,
                'name'                => $u->display_name,
                'gender'              => $gender,
                'gender_label'        => ($gender === 'female') ? 'خانم' : 'آقا',
                'first_tool_id'       => $first_tool,
                'first_tool_title'    => $first_tool ? get_the_title($first_tool) : 'مستقیم (هدر)',
                'categories'          => !empty($user_cats) ? implode(' , ', $user_cats) : '—',
                'total_tests'         => $total_tests,
                'first_login'         => self::to_shamsi($first_login, 'numeric'),
                'last_login'          => self::to_shamsi($last_login, 'numeric'),
                'is_first_visit_only' => $is_first_visit_only,
                'days_inactive'       => $days_inactive,
            ];
        }

        return $results;
    }

    public function render_leads_admin_page() {
        global $wpdb;
        $active_tab    = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'leads';
        $all_cats      = get_terms(['taxonomy' => 'madenna_cat', 'hide_empty' => false]);
        $all_tools     = get_posts(['post_type' => 'tools', 'posts_per_page' => 200, 'post_status' => 'publish']);
        $all_articles  = get_posts(['post_type' => 'articles', 'posts_per_page' => 200, 'post_status' => 'publish']);

        $filter_cats       = isset($_GET['filter_cats']) && is_array($_GET['filter_cats']) ? array_map('sanitize_text_field', $_GET['filter_cats']) : [];
        $filter_first_tool = isset($_GET['filter_first_tool']) ? intval($_GET['filter_first_tool']) : 0;
        $inactivity_preset = isset($_GET['inactivity_preset']) ? sanitize_text_field($_GET['inactivity_preset']) : '';
        $not_visited_since = isset($_GET['not_visited_since']) ? sanitize_text_field($_GET['not_visited_since']) : '';

        $leads_list   = $this->get_filtered_leads_from_request();
        $csv_url      = add_query_arg('madenna_export_csv', '1');
        $notices_list = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . self::TABLE_NOTICES . " ORDER BY created_at DESC");

        $recommendable_items = [];
        foreach ($all_tools as $t) {
            $t_cats = wp_get_post_terms($t->ID, 'madenna_cat', ['fields' => 'slugs']);
            $recommendable_items[] = [
                'id'    => $t->ID,
                'title' => '🛠️ [ابزار] ' . $t->post_title,
                'type'  => 'tools',
                'cats'  => is_array($t_cats) ? $t_cats : [],
            ];
        }
        foreach ($all_articles as $a) {
            $a_cats = wp_get_post_terms($a->ID, 'madenna_cat', ['fields' => 'slugs']);
            $recommendable_items[] = [
                'id'    => $a->ID,
                'title' => '📚 [مقاله] ' . $a->post_title,
                'type'  => 'articles',
                'cats'  => is_array($a_cats) ? $a_cats : [],
            ];
        }
        ?>
        <style>
            .mad-crm-wide-wrap,
            .mad-crm-wide-wrap * {
                font-family: 'Vazirmatn', Tahoma, sans-serif !important;
                box-sizing: border-box;
            }
            .mad-crm-wide-wrap {
                direction: rtl;
                width: 99%;
                max-width: 100%;
                margin: 18px auto;
            }
            .mad-crm-wide-wrap table.wp-list-table th,
            .mad-crm-wide-wrap table.wp-list-table td {
                padding: 12px 14px !important;
                vertical-align: middle !important;
                font-size: 13px !important;
            }
        </style>

        <div class="wrap mad-crm-wide-wrap">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 22px; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h1 style="font-size: 22px; font-weight: 900; color: #0f5650; margin: 0 0 6px;">👥 مرکز مدیریت لیدها، اعلانات و پیشنهاد هوشمند مادنا</h1>
                        <p style="margin: 0; color: #64748b; font-size: 13px;">بانک لیدهای واقعی با تاریخ هجری شمسی، رصد اولین و آخرین ورود، خروجی اکسل و ارسال پیشنهاد مقاله یا ابزار</p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=tools&page=madenna-vault-leads&tab=leads')); ?>" class="button <?php echo $active_tab === 'leads' ? 'button-primary' : ''; ?>" style="height: 40px; display: inline-flex; align-items: center; padding: 0 20px; border-radius: 10px; font-weight: 800; <?php echo $active_tab === 'leads' ? 'background:#0f5650; border-color:#0f5650;' : ''; ?>">📋 بانک لیدها (<?php echo count($leads_list); ?>)</a>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=tools&page=madenna-vault-leads&tab=notices')); ?>" class="button <?php echo $active_tab === 'notices' ? 'button-primary' : ''; ?>" style="height: 40px; display: inline-flex; align-items: center; padding: 0 20px; border-radius: 10px; font-weight: 800; <?php echo $active_tab === 'notices' ? 'background:#0f5650; border-color:#0f5650;' : ''; ?>">🔔 ارسال اعلان و پیشنهاد محتوا (<?php echo is_array($notices_list) ? count($notices_list) : 0; ?>)</a>
                    </div>
                </div>

                <?php if ($active_tab === 'leads'): ?>
                    <form method="get" action="" id="madenna-crm-filter-form" style="background: #f8faf8; padding: 20px; border-radius: 14px; border: 1px solid #bfc9c6; margin-bottom: 22px;">
                        <input type="hidden" name="post_type" value="tools">
                        <input type="hidden" name="page" value="madenna-vault-leads">
                        <input type="hidden" name="tab" value="leads">

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; align-items: end;">
                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:800; margin-bottom:6px; color:#1e293b;">فیلتر دسته‌بندی‌های فعال کاربر:</label>
                                <button type="button" id="open-cat-filter-modal-btn" class="button" style="width:100%; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:space-between; padding:0 12px; background:#fff; border-color:#cbd5e1; font-weight:700;">
                                    <span id="cat-filter-btn-label"><?php echo empty($filter_cats) ? 'همه دسته‌ها (کلیک برای انتخاب)' : count($filter_cats) . ' دسته‌بندی انتخاب شده'; ?></span>
                                    <span>▼</span>
                                </button>
                            </div>

                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:800; margin-bottom:6px; color:#1e293b;">فیلتر اولین ابزار ورودی:</label>
                                <select name="filter_first_tool" style="width:100%; height:40px; border-radius:8px;">
                                    <option value="0">-- همه ابزارها --</option>
                                    <?php foreach ($all_tools as $t): ?>
                                        <option value="<?php echo esc_attr($t->ID); ?>" <?php selected($filter_first_tool, $t->ID); ?>><?php echo esc_html($t->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:800; margin-bottom:6px; color:#1e293b;">وضعیت مراجعه / عدم فعالیت:</label>
                                <select name="inactivity_preset" style="width:100%; height:40px; border-radius:8px;">
                                    <option value="" <?php selected($inactivity_preset, ''); ?>>همه لیدها</option>
                                    <option value="inactive_7" <?php selected($inactivity_preset, 'inactive_7'); ?>>⚠️ بیش از ۷ روز است که نیامده‌اند</option>
                                    <option value="inactive_30" <?php selected($inactivity_preset, 'inactive_30'); ?>>🚨 بیش از ۳۰ روز است که نیامده‌اند</option>
                                    <option value="active_7" <?php selected($inactivity_preset, 'active_7'); ?>>✅ فعال در ۷ روز اخیر</option>
                                </select>
                            </div>

                            <div>
                                <label style="display:block; font-size:12.5px; font-weight:800; margin-bottom:6px; color:#1e293b;">از این تاریخ به بعد دیگر نیامده‌اند:</label>
                                <input type="date" name="not_visited_since" value="<?php echo esc_attr($not_visited_since); ?>" style="width:100%; height:40px; border-radius:8px;">
                            </div>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; padding-top:14px; border-top:1px dashed #cbd5e1; flex-wrap:wrap; gap:10px;">
                            <div style="display:flex; gap:8px;">
                                <button type="submit" class="button button-primary" style="height:40px; padding:0 24px; border-radius:8px; background:#0f5650; border-color:#0f5650; font-weight:800;">🔍 اعمال فیلتر</button>
                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=tools&page=madenna-vault-leads')); ?>" class="button" style="height:40px; display:inline-flex; align-items:center; border-radius:8px;">پاکسازی فیلترها</a>
                            </div>
                            <a href="<?php echo esc_url($csv_url); ?>" class="button" style="height:40px; display:inline-flex; align-items:center; background:#16a34a; color:#fff; border:none; border-radius:8px; padding:0 18px; font-weight:800;">📥 دانلود اکسل (CSV) از لیست فعلی</a>
                        </div>

                        <div id="crm-cat-picker-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:999999; align-items:center; justify-content:center; padding:20px;">
                            <div style="background:#fff; width:100%; max-width:680px; border-radius:18px; padding:24px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
                                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:12px; margin-bottom:16px;">
                                    <h3 style="margin:0; font-size:16px; color:#0f5650; font-weight:900;">انتخاب دسته‌بندی‌های فعال (یک یا چند مورد)</h3>
                                    <button type="button" id="close-cat-picker-modal" style="background:none; border:none; font-size:22px; cursor:pointer;">&times;</button>
                                </div>
                                <div style="max-height:360px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill, minmax(195px, 1fr)); gap:10px; padding:6px;">
                                    <?php if (!is_wp_error($all_cats)): foreach ($all_cats as $c): ?>
                                        <label style="display:flex; align-items:center; gap:8px; background:#f8fafc; border:1px solid #e2e8f0; padding:10px 12px; border-radius:8px; cursor:pointer; font-size:12.5px; font-weight:<?php echo $c->parent == 0 ? '800' : '600'; ?>;">
                                            <input type="checkbox" class="crm-cat-checkbox" name="filter_cats[]" value="<?php echo esc_attr($c->slug); ?>" <?php checked(in_array($c->slug, $filter_cats)); ?>>
                                            <span><?php echo ($c->parent ? '↳ ' : '📁 ') . esc_html($c->name); ?></span>
                                        </label>
                                    <?php endforeach; endif; ?>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e2e8f0; padding-top:14px; margin-top:16px;">
                                    <button type="button" id="clear-all-cat-checkboxes" class="button">پاک کردن همه تیک‌ها</button>
                                    <button type="button" id="confirm-cat-picker-modal" class="button button-primary" style="background:#0f5650; border-color:#0f5650; padding:0 22px;">تایید انتخاب</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form method="post" action="">
                        <?php wp_nonce_field('madenna_vault_admin_nonce'); ?>
                        <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                            <button type="submit" name="madenna_delete_users_action" class="button" style="color: #b91c1c; border-color: #fca5a5; font-weight:700;" onclick="return confirm('آیا از حذف لیدهای انتخاب‌شده و سوابق آن‌ها مطمئن هستید؟');">🗑️ حذف لیدهای انتخاب‌شده</button>
                            <span style="font-size: 13px; color: #475569;">تعداد لیدهای واقعی مطابق با فیلتر: <b><?php echo count($leads_list); ?></b> کاربر</span>
                        </div>

                        <table class="wp-list-table widefat fixed striped" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                            <thead>
                                <tr style="background:#f8faf8;">
                                    <td class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1"></td>
                                    <th style="font-weight:800;">شماره موبایل</th>
                                    <th style="font-weight:800;">نام و جنسیت</th>
                                    <th style="font-weight:800;">اولین ابزار ورودی</th>
                                    <th style="font-weight:800;">دسته‌های فعال</th>
                                    <th style="font-weight:800; width:100px;">تعداد استفاده</th>
                                    <th style="font-weight:800;">اولین ورود (شمسی)</th>
                                    <th style="font-weight:800;">آخرین بازدید (شمسی)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($leads_list)): ?>
                                    <tr><td colspan="8" style="text-align:center; padding:36px; color:#64748b;">هیچ کاربری (لید) با این شرایط یافت نشد. (مدیران سایت از این لیست مستثنی هستند)</td></tr>
                                <?php else: foreach ($leads_list as $lead): ?>
                                    <tr>
                                        <th scope="row" class="check-column"><input type="checkbox" name="selected_users[]" value="<?php echo esc_attr($lead['id']); ?>"></th>
                                        <td style="font-weight:900; color:#0f5650; direction:ltr; text-align:right;"><?php echo esc_html($lead['mobile']); ?></td>
                                        <td>
                                            <strong><?php echo esc_html($lead['name']); ?></strong>
                                            <span style="font-size:11px; padding:2px 8px; border-radius:6px; margin-right:4px; font-weight:700; background:<?php echo $lead['gender'] === 'female' ? '#fef3c7' : '#e6f4f1'; ?>; color:<?php echo $lead['gender'] === 'female' ? '#b45309' : '#0f5650'; ?>;">
                                                <?php echo esc_html($lead['gender_label']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($lead['first_tool_title']); ?></td>
                                        <td><span style="font-size:12px; color:#334155;"><?php echo esc_html($lead['categories']); ?></span></td>
                                        <td><b style="background:#f1f5f9; padding:4px 12px; border-radius:12px; color:#0f5650;"><?php echo esc_html($lead['total_tests']); ?></b></td>
                                        <td style="color:#475569; direction:ltr; text-align:right;"><?php echo esc_html($lead['first_login']); ?></td>
                                        <td>
                                            <div style="direction:ltr; text-align:right; font-weight:700;"><?php echo esc_html($lead['last_login']); ?></div>
                                            <?php if ($lead['days_inactive'] == 0): ?>
                                                <span style="font-size:11px; color:#15803d; font-weight:800;">● فعال در امروز</span>
                                            <?php elseif ($lead['is_first_visit_only'] && $lead['days_inactive'] <= 7): ?>
                                                <span style="font-size:11px; color:#0f766e; font-weight:700;">🌟 ورود اول (<?php echo esc_html($lead['days_inactive']); ?> روز پیش)</span>
                                            <?php elseif ($lead['days_inactive'] <= 7): ?>
                                                <span style="font-size:11px; color:#0f766e;"><?php echo esc_html($lead['days_inactive']); ?> روز پیش</span>
                                            <?php else: ?>
                                                <span style="font-size:11px; color:#dc2626; font-weight:800;">⚠️ <?php echo esc_html($lead['days_inactive']); ?> روز عدم مراجعه</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </form>

                <?php else: ?>
                    <div style="display: grid; grid-template-columns: 1.05fr 1.35fr; gap: 26px;">
                        
                        <form method="post" action="" style="background: #f8faf8; padding: 24px; border-radius: 16px; border: 1px solid #bfc9c6;">
                            <?php wp_nonce_field('madenna_vault_admin_nonce'); ?>
                            <h3 style="margin-top:0; color:#0f5650; font-weight:900;">➕ ثبت اعلان سیستمی یا پیشنهاد مقاله/ابزار</h3>

                            <p>
                                <label style="font-weight:800; display:block; margin-bottom:6px;">نوع ارسال:</label>
                                <select name="notice_type" id="admin-notice-type-selector" style="width:100%; height:42px; border-radius:8px; font-weight:800;">
                                    <option value="notification">🔔 اعلان سیستمی (پیام متنی عمومی یا هدفمند)</option>
                                    <option value="offer">🎁 پیشنهاد هوشمند مقاله یا ابزار (انتخاب مستقیم محتوا)</option>
                                </select>
                            </p>

                            <div id="admin-offer-content-picker" style="display:none; background:#fffbeb; border:1px solid #fde68a; padding:16px; border-radius:12px; margin-bottom:14px;">
                                <label style="font-weight:800; color:#b45309; display:block; margin-bottom:8px;">🎯 انتخاب مقاله یا ابزار برای پیشنهاد به کاربر:</label>
                                <div style="margin-bottom:10px;">
                                    <span style="font-size:12px; color:#78350f; display:block; margin-bottom:4px;">فیلتر سریع لیست بر اساس دسته‌بندی:</span>
                                    <select id="offer-item-cat-filter" style="width:100%; height:38px; border-radius:8px;">
                                        <option value="">-- نمایش همه مقالات و ابزارها --</option>
                                        <?php if (!is_wp_error($all_cats)): foreach ($all_cats as $c): ?>
                                            <option value="<?php echo esc_attr($c->slug); ?>"><?php echo ($c->parent ? '↳ ' : '') . esc_html($c->name); ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div>
                                    <span style="font-size:12px; color:#78350f; display:block; margin-bottom:4px;">مقاله یا ابزار مورد نظر را انتخاب کنید:</span>
                                    <select name="recommended_post_id" id="recommended-post-select" style="width:100%; height:40px; border-radius:8px;">
                                        <option value="0">-- انتخاب مقاله یا ابزار --</option>
                                        <?php foreach ($recommendable_items as $ritem): ?>
                                            <option value="<?php echo esc_attr($ritem['id']); ?>" data-cats="<?php echo esc_attr(implode(',', $ritem['cats'])); ?>">
                                                <?php echo esc_html($ritem['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <p>
                                <label style="font-weight:800; display:block; margin-bottom:4px;">عنوان (در حالت پیشنهاد، اگر خالی بگذارید نام مقاله/ابزار درج می‌شود):</label>
                                <input type="text" name="notice_title" style="width:100%; height:40px; border-radius:8px;" placeholder="مثال: این ابزار جدید رو از دست نده!">
                            </p>

                            <p>
                                <label style="font-weight:800; display:block; margin-bottom:4px;">توضیح یا پیام همراه:</label>
                                <textarea name="notice_content" rows="3" style="width:100%; border-radius:8px;" placeholder="توضیح کوتاه برای ترغیب کاربر..."></textarea>
                            </p>

                            <div style="background:#ffffff; border:1px solid #cbd5e1; padding:16px; border-radius:12px; margin-bottom:14px;">
                                <label style="font-weight:900; color:#0f5650; display:block; margin-bottom:6px;">👥 جامعه هدف این ارسال:</label>
                                <span style="font-size:12px; color:#64748b; display:block; margin-bottom:10px;">💡 اگر هیچ دسته‌بندی یا ابزاری را انتخاب نکنید، به صورت خودکار برای <b>همه کاربران</b> نمایش داده می‌شود.</span>

                                <label style="font-size:12.5px; font-weight:800; display:block; margin-bottom:6px;">۱. بر اساس دسته‌بندی‌های فعال کاربر (تیک بزنید):</label>
                                <div style="max-height:150px; overflow-y:auto; display:grid; grid-template-columns:1fr 1fr; gap:8px; background:#f8fafc; padding:10px; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:12px;">
                                    <?php if (!is_wp_error($all_cats)): foreach ($all_cats as $c): ?>
                                        <label style="font-size:12px; display:flex; align-items:center; gap:6px;">
                                            <input type="checkbox" name="target_categories[]" value="<?php echo esc_attr($c->slug); ?>">
                                            <span><?php echo ($c->parent ? '↳ ' : '') . esc_html($c->name); ?></span>
                                        </label>
                                    <?php endforeach; endif; ?>
                                </div>

                                <label style="font-size:12.5px; font-weight:800; display:block; margin-bottom:4px;">۲. یا بر اساس اولین ابزار ورودی کاربر:</label>
                                <select name="target_first_tool" style="width:100%; height:38px; border-radius:8px;">
                                    <option value="0">-- همه ابزارها (بدون محدودیت) --</option>
                                    <?php foreach ($all_tools as $t): ?>
                                        <option value="<?php echo esc_attr($t->ID); ?>"><?php echo esc_html($t->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; align-items:center;">
                                <div>
                                    <label style="font-weight:800; font-size:12px; display:block; margin-bottom:4px;">تاریخ انقضا (اختیاری):</label>
                                    <input type="date" name="expires_at" style="width:100%; height:38px; border-radius:8px;">
                                </div>
                                <div style="padding-top:18px;">
                                    <label style="font-size:12.5px; font-weight:700;">
                                        <input type="checkbox" name="auto_delete" value="1" checked> حذف خودکار پس از انقضا
                                    </label>
                                </div>
                            </div>

                            <p style="margin-top:18px;">
                                <button type="submit" name="madenna_create_notice_action" class="button button-primary" style="width:100%; height:44px; background:#0f5650; border-color:#0f5650; border-radius:10px; font-weight:800; font-size:14px;">🚀 انتشار در پرونده کاربران</button>
                            </p>
                        </form>

                        <div>
                            <h3 style="margin-top:0; font-weight:900;">📋 لیست اعلانات و پیشنهادات ثبت‌شده</h3>
                            <table class="wp-list-table widefat fixed striped" style="border-radius:12px; overflow:hidden; border:1px solid #e2e8f0;">
                                <thead>
                                    <tr>
                                        <th style="width:85px; font-weight:800;">نوع</th>
                                        <th style="font-weight:800;">عنوان / محتوای پیشنهادی</th>
                                        <th style="font-weight:800;">جامعه هدف</th>
                                        <th style="width:100px; font-weight:800;">انقضا</th>
                                        <th style="width:65px; font-weight:800;">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($notices_list)): ?>
                                        <tr><td colspan="5" style="text-align:center; padding:30px;">هنوز موردی ثبت نشده است.</td></tr>
                                    <?php else: foreach ($notices_list as $n): ?>
                                        <tr>
                                            <td>
                                                <?php if ($n->type === 'offer'): ?>
                                                    <span style="background:#fef3c7; color:#b45309; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800;">🎁 پیشنهاد</span>
                                                <?php else: ?>
                                                    <span style="background:#e6f4f1; color:#0f5650; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800;">🔔 اعلان</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo esc_html($n->title); ?></strong>
                                                <?php if (!empty($n->recommended_post_id)): ?>
                                                    <div style="font-size:11.5px; color:#0f5650; margin-top:4px; font-weight:700;">🔗 متصل به: <?php echo esc_html(get_the_title($n->recommended_post_id)); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-size:12px;">
                                                <?php 
                                                if (empty($n->target_categories) && empty($n->target_first_tool)) {
                                                    echo '<b style="color:#15803d;">همه کاربران</b>';
                                                } else {
                                                    if (!empty($n->target_categories)) echo '<div>دسته: ' . esc_html($n->target_categories) . '</div>';
                                                    if (!empty($n->target_first_tool)) echo '<div>ابزار: ' . esc_html(get_the_title($n->target_first_tool)) . '</div>';
                                                }
                                                ?>
                                            </td>
                                            <td style="font-size:12px;"><?php echo ($n->expires_at && $n->expires_at !== '0000-00-00') ? esc_html(self::to_shamsi($n->expires_at, 'date_only')) : 'دائمی'; ?></td>
                                            <td>
                                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=tools&page=madenna-vault-leads&tab=notices&del_notice=' . $n->id)); ?>" style="color:#dc2626; font-weight:800; text-decoration:none;" onclick="return confirm('حذف شود؟');">حذف</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                <?php endif; ?>

            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openModalBtn  = document.getElementById('open-cat-filter-modal-btn');
            const closeModalBtn = document.getElementById('close-cat-picker-modal');
            const confirmBtn    = document.getElementById('confirm-cat-picker-modal');
            const clearAllBtn   = document.getElementById('clear-all-cat-checkboxes');
            const modal         = document.getElementById('crm-cat-picker-modal');
            const btnLabel      = document.getElementById('cat-filter-btn-label');

            function updateCatCountLabel() {
                if (!btnLabel) return;
                const checkedCount = document.querySelectorAll('.crm-cat-checkbox:checked').length;
                btnLabel.textContent = checkedCount === 0 ? 'همه دسته‌ها (کلیک برای انتخاب)' : checkedCount + ' دسته‌بندی انتخاب شده';
            }

            if (openModalBtn && modal) {
                openModalBtn.addEventListener('click', () => modal.style.display = 'flex');
                closeModalBtn.addEventListener('click', () => { modal.style.display = 'none'; updateCatCountLabel(); });
                confirmBtn.addEventListener('click', () => { modal.style.display = 'none'; updateCatCountLabel(); });
                clearAllBtn.addEventListener('click', () => {
                    document.querySelectorAll('.crm-cat-checkbox').forEach(cb => cb.checked = false);
                    updateCatCountLabel();
                });
            }

            const typeSelect = document.getElementById('admin-notice-type-selector');
            const offerBox   = document.getElementById('admin-offer-content-picker');
            if (typeSelect && offerBox) {
                typeSelect.addEventListener('change', function() {
                    offerBox.style.display = (this.value === 'offer') ? 'block' : 'none';
                });
            }

            const catFilter = document.getElementById('offer-item-cat-filter');
            const postSelect = document.getElementById('recommended-post-select');
            if (catFilter && postSelect) {
                catFilter.addEventListener('change', function() {
                    const selectedCat = this.value;
                    Array.from(postSelect.options).forEach(opt => {
                        if (opt.value === '0') return;
                        const cats = (opt.getAttribute('data-cats') || '').split(',');
                        if (!selectedCat || cats.includes(selectedCat)) {
                            opt.style.display = '';
                        } else {
                            opt.style.display = 'none';
                        }
                    });
                    postSelect.value = '0';
                });
            }
        });
        </script>
        <?php
    }
}