<?php
/**
 * ویجت نمایش پرونده کاربری، دسته‌بندی‌های پیش‌فرض، فیلتر زیردسته و تاریخچه تکرار ابزار، و مودال نمایش نتیجه
 * نسخه نهایی ۸.۲ - نمایش خالص نتیجه نهایی در پروفایل (حذف کامل فرم‌ها و مراحل قبل از نتیجه)
 * Location: /inc/elementor/widgets/class-widget-user-vault.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_User_Vault_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'madenna_user_vault';
    }

    public function get_title() {
        return 'داشبورد پرونده کاربری (Vault)';
    }

    public function get_icon() {
        return 'eicon-folder-o';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        $this->start_controls_section('sec_content', [
            'label' => 'تنظیمات عمومی پرونده',
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('empty_text', [
            'label'   => 'متن زمان خالی بودن پرونده',
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'هنوز نتیجه‌ای در پرونده شما ثبت نشده است.',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $this->render_vault_dashboard_content($settings);
    }

    public function render_vault_dashboard_content($settings = []) {
        wp_enqueue_style('madenna-vault-fa6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');
        $vault_ver = defined('MADENNA_CORE_VERSION') ? MADENNA_CORE_VERSION : '8.3.0';
        wp_enqueue_style('madenna-vault-css', MADENNA_CORE_URL . 'assets/css/madenna-vault.css', ['madenna-vault-fa6'], $vault_ver);
        wp_enqueue_script('madenna-vault-js', MADENNA_CORE_URL . 'assets/js/madenna-vault.js', ['jquery'], $vault_ver, true);

        if (!is_user_logged_in()) {
            ?>
            <div class="mad-vault-guest-box" style="background: #ffffff; border: 1px solid #bfc9c6; border-radius: 20px; padding: 44px 24px; text-align: center; font-family: 'Vazirmatn', sans-serif; direction: rtl; max-width: 580px; margin: 24px auto;">
                <div style="width: 68px; height: 68px; background: #c2e7e2; color: #0f5650; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 28px;">
                    <i class="fa-solid fa-user-astronaut"></i>
                </div>
                <h3 style="font-size: 20px; font-weight: 900; color: #191c1c; margin: 0 0 10px;">پرونده شخصی ابزارها و تست‌های شما</h3>
                <p style="font-size: 13.5px; color: #3f4947; max-width: 440px; margin: 0 auto 22px; line-height: 1.8;">
                    برای مشاهده تاریخچه کامل ابزارهایی که استفاده کرده‌اید، کارنامه‌های تحلیلی، پیشنهادات اختصاصی و اعلانات، وارد حساب کاربری خود شوید.
                </p>
                <a href="#madenna-login" role="button" class="mad-vault-btn-primary mad-vault-open-modal-trigger" onclick="if(window.madennaOpenVaultModal){window.madennaOpenVaultModal('header');} return false;" style="max-width: 250px; margin: 0 auto; text-decoration: none;">
                    <span>ورود یا عضویت سریع</span>
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
            <?php
            return;
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $user    = wp_get_current_user();
        $mobile  = get_user_meta($user_id, 'madenna_mobile', true) ?: $user->user_login;
        $raw_gender       = trim((string) get_user_meta($user_id, 'madenna_gender', true));
        $has_valid_gender = in_array($raw_gender, ['male', 'female'], true);
        $gender           = $has_valid_gender ? $raw_gender : 'male';
        $birthdate        = trim((string) get_user_meta($user_id, 'madenna_birthdate', true));
        $b_year = '';
        $b_month = '';
        $b_day = '';
        if (!empty($birthdate)) {
            $b_parts = preg_split('/[\/\-\.\s]+/', trim($birthdate));
            if (count($b_parts) >= 3) {
                if (strlen($b_parts[0]) === 4) {
                    $b_year  = intval($b_parts[0]);
                    $b_month = intval($b_parts[1]);
                    $b_day   = intval($b_parts[2]);
                } elseif (strlen($b_parts[2]) === 4) {
                    $b_year  = intval($b_parts[2]);
                    $b_month = intval($b_parts[1]);
                    $b_day   = intval($b_parts[0]);
                }
            }
        }
        $shamsi_months = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
            10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
        ];
        $has_valid_birthdate = (!empty($b_year) && !empty($b_month) && !empty($b_day));
        $is_profile_complete = ($has_valid_gender && $has_valid_birthdate);
        $birthdate_display_text = 'ثبت‌نشده';
        if ($has_valid_birthdate) {
            $birthdate_display_text = sprintf('%d %s %d', $b_day, $shamsi_months[$b_month] ?? '', $b_year);
        } elseif (!empty($birthdate)) {
            $birthdate_display_text = esc_html($birthdate);
        }
        $name      = $user->display_name;

        if (class_exists('Madenna_User_Vault')) {
            Madenna_User_Vault::mark_vault_as_seen($user_id);
        }

        $table = $wpdb->prefix . 'madenna_user_results';
        $records = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table) {
            $records = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table} WHERE user_id = %d AND user_deleted = 0 ORDER BY created_at DESC LIMIT 200",
                $user_id
            ));
        }

        $parent_categories = get_terms([
            'taxonomy'   => 'madenna_cat',
            'parent'     => 0,
            'hide_empty' => false,
        ]);
        if (is_wp_error($parent_categories)) {
            $parent_categories = [];
        }

        $sub_categories = [];
        $all_terms = get_terms([
            'taxonomy'   => 'madenna_cat',
            'hide_empty' => false,
        ]);
        $term_by_id   = [];
        $term_by_slug = [];
        if (!is_wp_error($all_terms)) {
            foreach ($all_terms as $t) {
                $term_by_id[$t->term_id] = $t;
                $term_by_slug[$t->slug]  = $t;
                if ($t->parent > 0) {
                    $sub_categories[] = $t;
                }
            }
        }

        $tool_total_counts = [];
        $tool_Filter_meta  = [];
        $used_tool_ids     = [];

        if (!empty($records)) {
            foreach ($records as $r) {
                $tid = (int) $r->tool_id;
                if ($tid <= 0 || get_post_type($tid) !== 'tools') {
                    continue;
                }
                if (!isset($tool_total_counts[$tid])) {
                    $tool_total_counts[$tid] = 0;
                }
                $tool_total_counts[$tid]++;
                $used_tool_ids[$tid] = $tid;
            }
        }

        $tool_seen_so_far = [];
        $enriched_records = [];

        if (!empty($records)) {
            foreach ($records as $row) {
                $tid = (int) $row->tool_id;
                if ($tid <= 0 || get_post_type($tid) !== 'tools') {
                    continue;
                }

                if (!isset($tool_seen_so_far[$tid])) {
                    $tool_seen_so_far[$tid] = 0;
                }
                $attempt_number = $tool_total_counts[$tid] - $tool_seen_so_far[$tid];
                $tool_seen_so_far[$tid]++;

                $parent_slugs = [];
                $sub_slugs    = [];
                $primary_sub_name    = '';
                $primary_parent_name = '';

                $post_terms = wp_get_post_terms($tid, 'madenna_cat');
                if (!empty($post_terms) && !is_wp_error($post_terms)) {
                    foreach ($post_terms as $pt) {
                        if ($pt->parent > 0) {
                            $sub_slugs[] = $pt->slug;
                            if (!$primary_sub_name) {
                                $primary_sub_name = $pt->name;
                            }
                            if (isset($term_by_id[$pt->parent])) {
                                $parent_slugs[] = $term_by_id[$pt->parent]->slug;
                                if (!$primary_parent_name) {
                                    $primary_parent_name = $term_by_id[$pt->parent]->name;
                                }
                            }
                        } else {
                            $parent_slugs[] = $pt->slug;
                            if (!$primary_parent_name) {
                                $primary_parent_name = $pt->name;
                            }
                        }
                    }
                }

                if (!empty($row->category_slug) && isset($term_by_slug[$row->category_slug])) {
                    $db_term = $term_by_slug[$row->category_slug];
                    if ($db_term->parent > 0) {
                        $sub_slugs[] = $db_term->slug;
                        if (!$primary_sub_name) {
                            $primary_sub_name = $db_term->name;
                        }
                        if (isset($term_by_id[$db_term->parent])) {
                            $parent_slugs[] = $term_by_id[$db_term->parent]->slug;
                            if (!$primary_parent_name) {
                                $primary_parent_name = $term_by_id[$db_term->parent]->name;
                            }
                        }
                    } else {
                        $parent_slugs[] = $db_term->slug;
                        if (!$primary_parent_name) {
                            $primary_parent_name = $db_term->name;
                        }
                    }
                }

                $parent_slugs = array_values(array_unique(array_filter($parent_slugs)));
                $sub_slugs    = array_values(array_unique(array_filter($sub_slugs)));

                $badge_cat_name = $primary_sub_name ?: ($primary_parent_name ?: 'عمومی');
                $tool_title     = $tid > 0 ? get_the_title($tid) : $row->result_title;
                $tool_link      = $tid > 0 ? get_permalink($tid) : '#';
                $icon_cls       = $tid > 0 ? (get_post_meta($tid, '_tool_icon_class', true) ?: 'fa-solid fa-calculator') : 'fa-solid fa-calculator';
                $icon_bg        = $tid > 0 ? (get_post_meta($tid, '_tool_color_accent', true) ?: '#e6f4f1') : '#e6f4f1';
                $icon_color     = $tid > 0 ? (get_post_meta($tid, '_tool_color_primary', true) ?: '#0f5650') : '#0f5650';
                $shamsi_date    = class_exists('Madenna_User_Vault') ? Madenna_User_Vault::to_shamsi($row->created_at, 'full') : $row->created_at;

                if (!isset($tool_Filter_meta[$tid])) {
                    $tool_Filter_meta[$tid] = [
                        'id'           => $tid,
                        'title'        => $tool_title,
                        'count'        => $tool_total_counts[$tid],
                        'parent_slugs' => $parent_slugs,
                        'sub_slugs'    => $sub_slugs,
                    ];
                } else {
                    $tool_Filter_meta[$tid]['parent_slugs'] = array_values(array_unique(array_merge($tool_Filter_meta[$tid]['parent_slugs'], $parent_slugs)));
                    $tool_Filter_meta[$tid]['sub_slugs']    = array_values(array_unique(array_merge($tool_Filter_meta[$tid]['sub_slugs'], $sub_slugs)));
                }

                $enriched_records[] = [
                    'id'              => (int) $row->id,
                    'tool_id'         => $tid,
                    'tool_title'      => $tool_title,
                    'tool_link'       => $tool_link,
                    'icon_cls'        => $icon_cls,
                    'icon_bg'         => $icon_bg,
                    'icon_color'      => $icon_color,
                    'parent_slugs'    => $parent_slugs,
                    'sub_slugs'       => $sub_slugs,
                    'sub_cat_name'    => $badge_cat_name,
                    'attempt_num'     => $attempt_number,
                    'total_for_tool'  => $tool_total_counts[$tid],
                    'shamsi_date'     => $shamsi_date,
                    'result_html'     => $row->result_html,
                    'inputs_json'     => $row->inputs_json ?? '',
                ];
            }
        }

        // بارگذاری کامل استایل‌ها، کتابخانه‌های ۶گانه و آماده‌سازی نقشه HTML و JS ابزارها
        $tool_js_map   = [];
        $tool_html_map = [];
        if (!empty($used_tool_ids)) {
            wp_enqueue_script('madenna-tailwind-cdn', 'https://cdn.tailwindcss.com', [], null, false);
            wp_enqueue_script('madenna-chartjs-cdn', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
            wp_enqueue_script('madenna-mathjs-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/mathjs/11.8.0/math.js', [], '11.8.0', true);
            wp_enqueue_script('madenna-confetti-cdn', 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js', [], '1.6.0', true);
            wp_enqueue_script('madenna-anime-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js', [], '3.2.1', true);
            wp_enqueue_script('madenna-html2canvas-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', [], '1.4.1', true);
            wp_enqueue_script('madenna-jspdf-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', [], '2.5.1', true);

            $upload_dir = wp_upload_dir();
            foreach ($used_tool_ids as $utid) {
                $css_file_path = $upload_dir['basedir'] . '/madenna-tools/tool-' . $utid . '.css';
                $css_file_url  = $upload_dir['baseurl'] . '/madenna-tools/tool-' . $utid . '.css';
                if (file_exists($css_file_path)) {
                    wp_enqueue_style('madenna-tool-style-' . $utid, $css_file_url, [], filemtime($css_file_path));
                }
                $raw_css = get_post_meta($utid, '_tool_css', true);
                if (!empty($raw_css)) {
                    echo '<style id="madenna-vault-inline-css-' . esc_attr($utid) . '">' . $raw_css . '</style>';
                }

                $raw_js = get_post_meta($utid, '_tool_js', true);
                if (empty($raw_js)) {
                    $js_file_path = $upload_dir['basedir'] . '/madenna-tools/tool-' . $utid . '.js';
                    if (file_exists($js_file_path)) {
                        $raw_js = file_get_contents($js_file_path);
                    }
                }
                if (!empty($raw_js)) {
                    $tool_js_map[$utid] = $raw_js;
                }

                $raw_html = get_post_meta($utid, '_tool_html', true);
                if (!empty($raw_html)) {
                    $tool_html_map[$utid] = $raw_html;
                }
            }
        }

        $notices_data  = class_exists('Madenna_User_Vault') ? Madenna_User_Vault::get_matched_notices_for_user($user_id) : ['notifications' => [], 'offers' => []];
        $offers_list   = $notices_data['offers'] ?? [];
        $notifs_list   = $notices_data['notifications'] ?? [];
        $logout_url    = wp_logout_url(home_url('/'));
        $total_rec_cnt = count($enriched_records);
        ?>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <style>
            .mad-vault-result-modal-body [data-vault-hidden-step="true"] {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                max-height: 0 !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                pointer-events: none !important;
            }
            .mad-vault-result-modal-body .hidden {
                display: none !important;
            }
            .mad-vault-result-modal-body [data-vault-single-col="true"] {
                display: block !important;
                grid-template-columns: 1fr !important;
            }
            .mad-vault-result-modal-body [data-vault-single-col="true"] > *:not([data-vault-hidden-step="true"]) {
                width: 100% !important;
                max-width: 100% !important;
            }
        </style>
        <script>
            if (typeof window.tailwind !== 'undefined') {
                window.tailwind.config = { corePlugins: { preflight: false } };
            }
        </script>

        <div class="mad-vault-dashboard-wrapper" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-profile-complete="<?php echo $is_profile_complete ? 'true' : 'false'; ?>" data-user-gender="<?php echo esc_attr($has_valid_gender ? $raw_gender : ''); ?>" data-user-birthdate="<?php echo esc_attr($birthdate); ?>">
            
            <!-- ۱. هدر پروفایل و ویرایش آنی نام و جنسیت + دکمه خروج زیر باکس سوابق -->
            <div class="mad-vault-db-header">
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <div id="mad-vault-profile-avatar-box" style="width: 66px; height: 66px; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                        <?php 
                        if (class_exists('Madenna_User_Vault') && method_exists('Madenna_User_Vault', 'get_avatar_svg')) {
                            echo Madenna_User_Vault::get_avatar_svg($gender, 66);
                        }
                        ?>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h2 id="mad-vault-display-name" style="font-size: 19px; font-weight: 900; color: #191c1c; margin: 0;"><?php echo esc_html($name); ?></h2>
                            <button type="button" id="mad-vault-toggle-edit-btn" style="background: #f2f4f2 !important; border: 1px solid #bfc9c6 !important; border-radius: 8px; padding: 4px 10px; font-size: 11.5px; color: #0f5650 !important; cursor: pointer; font-weight: 800; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fa-solid fa-pen-to-square"></i> ویرایش
                            </button>
                        </div>
                        <span style="font-size: 12.5px; color: #3f4947; display: block; margin-top: 4px;">
                            شماره همراه: <strong dir="ltr"><?php echo esc_html($mobile); ?></strong>
                            <span style="margin-right: 14px;">تاریخ تولد: <strong id="mad-vault-display-birthdate"><?php echo esc_html($birthdate_display_text); ?></strong></span>
                        </span>

                        <div id="mad-vault-edit-profile-box" style="display: none; margin-top: 12px; background: #f8faf8; padding: 12px; border-radius: 12px; border: 1px solid #bfc9c6;">
                            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                <input type="text" id="mad-vault-edit-name-input" value="<?php echo esc_attr($name); ?>" placeholder="نام و نام خانوادگی" style="height: 36px; border-radius: 8px; border: 1px solid #bfc9c6; padding: 0 10px; font-size: 13px; font-family: inherit;">
                                <select id="mad-vault-edit-gender-select" style="height: 36px; border-radius: 8px; border: 1px solid #bfc9c6; padding: 0 10px; font-size: 13px; font-family: inherit;">
                                    <option value="" <?php selected($has_valid_gender, false); ?>>جنسیت...</option>
                                    <option value="male" <?php selected($raw_gender, 'male'); ?>>آقا</option>
                                    <option value="female" <?php selected($raw_gender, 'female'); ?>>خانم</option>
                                </select>
                                <div style="display: inline-flex; align-items: center; gap: 4px; background: #ffffff; border: 1px solid #bfc9c6; border-radius: 8px; padding: 0 6px; height: 36px;">
                                    <span style="font-size: 11px; color: #64748b; font-weight: 700; margin-left: 2px;">تولد:</span>
                                    <select id="mad-vault-edit-bday" style="border: none !important; outline: none; font-size: 12px; font-family: inherit; background: transparent; cursor: pointer; padding: 0 2px;">
                                        <option value="">روز</option>
                                        <?php for ($d = 1; $d <= 31; $d++): ?>
                                            <option value="<?php echo $d; ?>" <?php selected($b_day, $d); ?>><?php echo $d; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <span style="color: #cbd5e1;">/</span>
                                    <select id="mad-vault-edit-bmonth" style="border: none !important; outline: none; font-size: 12px; font-family: inherit; background: transparent; cursor: pointer; padding: 0 2px;">
                                        <option value="">ماه</option>
                                        <?php foreach ($shamsi_months as $m_num => $m_name): ?>
                                            <option value="<?php echo $m_num; ?>" <?php selected($b_month, $m_num); ?>><?php echo esc_html($m_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span style="color: #cbd5e1;">/</span>
                                    <select id="mad-vault-edit-byear" style="border: none !important; outline: none; font-size: 12px; font-family: inherit; background: transparent; cursor: pointer; padding: 0 2px;">
                                        <option value="">سال</option>
                                        <?php for ($y = 1403; $y >= 1320; $y--): ?>
                                            <option value="<?php echo $y; ?>" <?php selected($b_year, $y); ?>><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <input type="hidden" id="mad-vault-edit-birthdate-input" value="<?php echo esc_attr($birthdate); ?>">
                                </div>
                                <button type="button" id="mad-vault-save-profile-btn" style="height: 36px; background: #0f5650 !important; color: #fff !important; border: none !important; border-radius: 8px; padding: 0 14px; font-size: 12.5px; font-weight: 800; cursor: pointer;">ذخیره تغییرات</button>
                                <button type="button" id="mad-vault-cancel-profile-btn" style="height: 36px; background: #e2e8f0 !important; color: #334155 !important; border: none !important; border-radius: 8px; padding: 0 10px; font-size: 12px; font-weight: 700; cursor: pointer;">انصراف</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mad-vault-db-stats-actions">
                    <div style="background: #f8faf8; padding: 10px 18px; border-radius: 14px; text-align: center; border: 1px solid #e1e3e1;">
                        <span id="mad-vault-total-records-num" style="display: block; font-size: 22px; font-weight: 900; color: #0f5650; line-height: 1.1;"><?php echo intval($total_rec_cnt); ?></span>
                        <span style="font-size: 11.5px; color: #3f4947; font-weight: 700;">کل سوابق ثبت‌شده</span>
                    </div>
                    <a href="<?php echo esc_url($logout_url); ?>" class="mad-vault-logout-btn" title="خروج از حساب کاربری">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>خروج از حساب</span>
                    </a>
                </div>
            </div>

            <!-- ۲. تب‌های اصلی پرونده کاربری -->
            <div class="mad-vault-main-tabs-row">
                <button type="button" class="mad-vault-main-tab active" data-panel="history">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>سوابق و نتایج من (<b id="mad-vault-tab-history-count"><?php echo intval($total_rec_cnt); ?></b>)</span>
                </button>
                <button type="button" class="mad-vault-main-tab" data-panel="offers">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>مقالات و ابزارهای پیشنهادی (<b id="mad-vault-offers-count"><?php echo count($offers_list); ?></b>)</span>
                </button>
                <button type="button" class="mad-vault-main-tab" data-panel="notifications">
                    <i class="fa-solid fa-bell"></i>
                    <span>اعلانات سیستم (<b id="mad-vault-notifs-count"><?php echo count($notifs_list); ?></b>)</span>
                </button>
            </div>

            <!-- ۳. پنل اول: سوابق و نتایج من -->
            <div id="mad-vault-panel-history" class="mad-vault-panel">
                
                <!-- ردیف اول: دسته‌بندی‌های مادر به صورت پیش‌فرض -->
                <div class="mad-vault-parent-cats-bar" id="mad-vault-parent-cats-bar">
                    <button type="button" class="mad-vault-cat-pill active" data-parent-slug="all">
                        <i class="fa-solid fa-border-all"></i>
                        <span>همه دسته‌ها</span>
                    </button>
                    <?php foreach ($parent_categories as $pcat): 
                        $p_icon = get_term_meta($pcat->term_id, 'cat_icon_class', true) ?: 'fa-solid fa-folder';
                    ?>
                        <button type="button" class="mad-vault-cat-pill" data-parent-slug="<?php echo esc_attr($pcat->slug); ?>" data-parent-id="<?php echo esc_attr($pcat->term_id); ?>">
                            <i class="<?php echo esc_attr($p_icon); ?>"></i>
                            <span><?php echo esc_html($pcat->name); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- ردیف دوم: فیلتر دوگانه (زیردسته + فیلتر ابزار و تاریخچه تکرار) -->
                <div class="mad-vault-filters-bar">
                    <div class="mad-vault-filter-item">
                        <label for="mad-vault-subcat-select"><i class="fa-solid fa-filter"></i> فیلتر زیردسته:</label>
                        <select id="mad-vault-subcat-select" class="mad-vault-filter-select">
                            <option value="all">همه زیردسته‌ها</option>
                            <?php foreach ($sub_categories as $scat): 
                                $parent_term_obj = isset($term_by_id[$scat->parent]) ? $term_by_id[$scat->parent] : null;
                                $p_slug = $parent_term_obj ? $parent_term_obj->slug : '';
                            ?>
                                <option value="<?php echo esc_attr($scat->slug); ?>" data-parent-slug="<?php echo esc_attr($p_slug); ?>">
                                    <?php echo esc_html($scat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mad-vault-filter-item">
                        <label for="mad-vault-tool-select"><i class="fa-solid fa-screwdriver-wrench"></i> فیلتر ابزار و تاریخچه تکرار:</label>
                        <select id="mad-vault-tool-select" class="mad-vault-filter-select">
                            <option value="all">همه ابزارهای تست‌شده (<?php echo count($tool_Filter_meta); ?> ابزار)</option>
                            <?php foreach ($tool_Filter_meta as $tmeta): ?>
                                <option value="<?php echo esc_attr($tmeta['id']); ?>"
                                        data-tool-title="<?php echo esc_attr($tmeta['title']); ?>"
                                        data-parent-cats="<?php echo esc_attr(implode(',', $tmeta['parent_slugs'])); ?>"
                                        data-sub-cats="<?php echo esc_attr(implode(',', $tmeta['sub_slugs'])); ?>">
                                    <?php echo esc_html($tmeta['title'] . ' (' . $tmeta['count'] . ' بار تست شده)'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- ردیف سوم: لیست خلاصه سوابق -->
                <?php if (empty($enriched_records)): ?>
                    <div class="mad-vault-empty-state">
                        <i class="fa-solid fa-box-open" style="font-size: 36px; color: #94a3b8; margin-bottom: 10px; display: block;"></i>
                        <p style="margin: 0; font-size: 14px;"><?php echo esc_html($settings['empty_text'] ?? 'هنوز نتیجه‌ای در پرونده شما ثبت نشده است.'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="mad-vault-history-list" id="mad-vault-history-list">
                        <?php foreach ($enriched_records as $rec): ?>
                            <div class="mad-vault-history-row"
                                 data-record-id="<?php echo esc_attr($rec['id']); ?>"
                                 data-parent-cats="<?php echo esc_attr(implode(',', $rec['parent_slugs'])); ?>"
                                 data-sub-cats="<?php echo esc_attr(implode(',', $rec['sub_slugs'])); ?>"
                                 data-tool-id="<?php echo esc_attr($rec['tool_id']); ?>"
                                 data-tool-title="<?php echo esc_attr($rec['tool_title']); ?>"
                                 data-tool-url="<?php echo esc_url($rec['tool_link']); ?>"
                                 data-shamsi-date="<?php echo esc_attr($rec['shamsi_date']); ?>"
                                 data-subcat-name="<?php echo esc_attr($rec['sub_cat_name']); ?>">
                                
                                <div class="mad-vault-history-info">
                                    <div class="mad-vault-history-icon" style="background: <?php echo esc_attr($rec['icon_bg']); ?>; color: <?php echo esc_attr($rec['icon_color']); ?>;">
                                        <i class="<?php echo esc_attr($rec['icon_cls']); ?>"></i>
                                    </div>
                                    <div class="mad-vault-history-texts">
                                        <div class="mad-vault-history-title-row">
                                            <h4 class="mad-vault-history-title"><?php echo esc_html($rec['tool_title']); ?></h4>
                                            <span class="mad-vault-subcat-badge"><?php echo esc_html($rec['sub_cat_name']); ?></span>
                                            <?php if ($rec['total_for_tool'] > 1): ?>
                                                <span class="mad-vault-attempt-badge">نوبت <?php echo intval($rec['attempt_num']); ?> از <?php echo intval($rec['total_for_tool']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mad-vault-history-meta">
                                            <span><i class="fa-regular fa-calendar-check"></i> <?php echo esc_html($rec['shamsi_date']); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mad-vault-history-actions">
                                    <button type="button" class="mad-vault-view-result-btn" data-open-record="<?php echo esc_attr($rec['id']); ?>">
                                        <i class="fa-solid fa-eye"></i>
                                        <span>مشاهده نتیجه</span>
                                    </button>
                                    <?php if ($rec['tool_id'] > 0): ?>
                                        <a href="<?php echo esc_url($rec['tool_link']); ?>" class="mad-vault-retry-tool-btn">
                                            <i class="fa-solid fa-rotate-right"></i>
                                            <span>تست مجدد</span>
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" class="mad-vault-delete-record-btn" data-delete-record="<?php echo esc_attr($rec['id']); ?>" title="حذف از تاریخچه من">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>

                                <template id="mad-vault-res-tpl-<?php echo esc_attr($rec['id']); ?>"><?php echo $rec['result_html']; ?></template>
                                <script type="application/json" id="mad-vault-inputs-<?php echo esc_attr($rec['id']); ?>"><?php echo !empty($rec['inputs_json']) ? $rec['inputs_json'] : '{}'; ?></script>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="mad-vault-history-pagination" class="mad-vault-pagination-bar" style="display: none;"></div>

                    <div id="mad-vault-no-filter-match" class="mad-vault-empty-state" style="display: none; margin-top: 12px;">
                        <i class="fa-solid fa-magnifying-glass" style="font-size: 28px; color: #94a3b8; margin-bottom: 8px; display: block;"></i>
                        <p style="margin: 0; font-size: 13.5px;">هیچ سابقه‌ای در این دسته‌بندی یا فیلتر یافت نشد.</p>
                    </div>
                <?php endif; ?>

            </div>

            <!-- ۴. پنل دوم: مقالات و ابزارهای پیشنهادی -->
            <div id="mad-vault-panel-offers" class="mad-vault-panel" style="display: none;">
                <?php if (empty($offers_list)): ?>
                    <div class="mad-vault-empty-state">در حال حاضر پیشنهاد جدیدی برای شما ثبت نشده است.</div>
                <?php else: ?>
                    <div id="mad-vault-offers-list">
                        <?php foreach ($offers_list as $op): ?>
                            <div class="mad-vault-recommend-card mad-vault-paginated-offer">
                                <div style="display: flex; align-items: flex-start; gap: 14px; flex: 1; min-width: 240px;">
                                    <div style="width: 48px; height: 48px; border-radius: 14px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                        <i class="<?php echo esc_attr($op['icon_class'] ?? 'fa-solid fa-star'); ?>"></i>
                                    </div>
                                    <div>
                                        <?php if (!empty($op['badge_text'])): ?>
                                            <span style="display: inline-block; background: #fef3c7; color: #b45309; font-size: 11px; font-weight: 800; padding: 2px 10px; border-radius: 999px; margin-bottom: 6px;"><?php echo esc_html($op['badge_text']); ?></span>
                                        <?php endif; ?>
                                        <h4 style="margin: 0 0 6px; font-size: 15px; font-weight: 900; color: #191c1c;"><?php echo esc_html($op['title']); ?></h4>
                                        <?php if (!empty($op['subtitle'])): ?>
                                            <p style="margin: 0 0 6px; font-size: 12.5px; color: #475569; font-weight: 600;"><?php echo esc_html($op['subtitle']); ?></p>
                                        <?php endif; ?>
                                        <div style="font-size: 13px; color: #3f4947; line-height: 1.7;"><?php echo wp_kses_post($op['content']); ?></div>
                                    </div>
                                </div>
                                <?php if (!empty($op['post_url'])): ?>
                                    <a href="<?php echo esc_url($op['post_url']); ?>" class="mad-vault-btn-primary" style="width: auto; padding: 0 20px; height: 42px; text-decoration: none; font-size: 12.5px;">
                                        <span><?php echo esc_html($op['btn_text'] ?? 'مشاهده محتوا ←'); ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="mad-vault-offers-pagination" class="mad-vault-pagination-bar" style="display: none;"></div>
                <?php endif; ?>
            </div>

            <!-- ۵. پنل سوم: اعلانات سیستم -->
            <div id="mad-vault-panel-notifications" class="mad-vault-panel" style="display: none;">
                <?php if (empty($notifs_list)): ?>
                    <div class="mad-vault-empty-state">اعلان جدیدی برای شما وجود ندارد.</div>
                <?php else: ?>
                    <div id="mad-vault-notifs-list">
                        <?php foreach ($notifs_list as $nf): ?>
                            <div class="mad-vault-paginated-notif" style="background: #ffffff; border: 1px solid #bfc9c6; border-right: 4px solid #0f5650; border-radius: 16px; padding: 18px 20px; margin-bottom: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <h4 style="margin: 0; font-size: 15px; font-weight: 900; color: #191c1c;"><?php echo esc_html($nf['title']); ?></h4>
                                    <span style="font-size: 11.5px; color: #64748b;"><?php echo esc_html($nf['created_at']); ?></span>
                                </div>
                                <div style="font-size: 13px; color: #3f4947; line-height: 1.8;"><?php echo wp_kses_post($nf['content']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="mad-vault-notifs-pagination" class="mad-vault-pagination-bar" style="display: none;"></div>
                <?php endif; ?>
            </div>

        </div>

        <!-- ۶. مودال تایید حذف سابقه از تاریخچه -->
        <div id="mad-vault-delete-confirm-modal" class="mad-vault-overlay" style="display: none; position: fixed; top: 0; right: 0; bottom: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.72); z-index: 9999999; align-items: center; justify-content: center; padding: 16px; direction: rtl; font-family: 'Vazirmatn', sans-serif;">
            <div style="background: #ffffff; width: 100%; max-width: 390px; border-radius: 20px; border: 1px solid #bfc9c6; padding: 24px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.28); margin: auto;">
                <div style="width: 54px; height: 54px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 14px;">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h4 style="margin: 0 0 8px; font-size: 16px; font-weight: 900; color: #191c1c;">حذف نتیجه از سوابق</h4>
                <p style="margin: 0 0 20px; font-size: 13px; color: #3f4947; line-height: 1.7;">آیا از حذف این مورد از تاریخچه پرونده کاربری خود مطمئن هستید؟</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <button type="button" id="mad-vault-confirm-delete-yes" style="height: 42px; border-radius: 11px; border: none !important; background: #dc2626 !important; color: #ffffff !important; font-size: 13px; font-weight: 800; cursor: pointer; font-family: inherit;">بله، حذف شود</button>
                    <button type="button" id="mad-vault-confirm-delete-no" style="height: 42px; border-radius: 11px; border: 1px solid #bfc9c6 !important; background: #f8faf8 !important; color: #191c1c !important; font-size: 13px; font-weight: 800; cursor: pointer; font-family: inherit;">انصراف</button>
                </div>
            </div>
        </div>

        <!-- ۷. مودال عریض نمایش نتیجه ابزار با استایل و JS کامل ابزار -->
        <div id="mad-vault-result-preview-modal" class="mad-vault-result-modal-overlay" style="display: none; position: fixed; top: 0; right: 0; bottom: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.76); z-index: 9999998; align-items: center; justify-content: center; padding: 20px; direction: rtl;">
            <div class="mad-vault-result-modal-dialog">
                <div class="mad-vault-result-modal-header">
                    <div class="mad-vault-result-modal-meta">
                        <span style="display: inline-flex; align-items: center; gap: 5px; background: #0f5650; color: #ffffff; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 999px;">مادِنا | madenna.ir</span>
                        <h3 id="mad-res-modal-title" style="margin: 0; font-size: 16px; font-weight: 900; color: #191c1c;">نتیجه ابزار</h3>
                        <span id="mad-res-modal-badge" class="mad-vault-subcat-badge"></span>
                        <span id="mad-res-modal-date" style="font-size: 12px; color: #64748b; font-weight: 600;"></span>
                    </div>
                    <div class="mad-vault-result-modal-top-actions">
                        <button type="button" id="mad-res-modal-print-btn" class="mad-res-modal-action-btn">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>دانلود / چاپ PDF</span>
                        </button>
                        <a href="#" id="mad-res-modal-retry-link" class="mad-res-modal-action-btn primary">
                            <i class="fa-solid fa-rotate-right"></i>
                            <span>انجام مجدد ابزار</span>
                        </a>
                        <button type="button" id="mad-res-modal-close-btn" class="mad-vault-result-modal-close" aria-label="بستن">&times;</button>
                    </div>
                </div>
                <div id="mad-vault-result-modal-body-wrap" class="mad-vault-result-modal-body-wrap">
                    <div id="mad-vault-result-modal-body" class="mad-vault-result-modal-body"></div>
                    <div id="mad-vault-profile-gate-overlay" class="mad-vault-profile-gate-overlay" style="display: none;">
                        <div class="mad-vault-profile-gate-card">
                            <div class="mad-vault-gate-icon">
                                <i class="fa-solid fa-user-lock"></i>
                            </div>
                            <h4 class="mad-vault-gate-title">تکمیل جنسیت و تاریخ تولد برای مشاهده نتیجه</h4>
                            <p class="mad-vault-gate-desc">برای مشاهده کامل کارنامه و تحلیل‌های ثبت‌شده در پرونده (و پر شدن خودکار ابزارهای بعدی)، لطفاً جنسیت و تاریخ تولد خود را مشخص کنید.</p>

                            <div class="mad-vault-gate-field">
                                <label class="mad-vault-gate-label">جنسیت شما:</label>
                                <div class="mad-vault-gate-gender-options" id="mad-vault-gate-gender-options">
                                    <button type="button" class="mad-vault-gate-gender-btn <?php echo ($raw_gender === 'male') ? 'active' : ''; ?>" data-gate-gender="male">
                                        <i class="fa-solid fa-mars"></i>
                                        <span>آقا</span>
                                    </button>
                                    <button type="button" class="mad-vault-gate-gender-btn <?php echo ($raw_gender === 'female') ? 'active' : ''; ?>" data-gate-gender="female">
                                        <i class="fa-solid fa-venus"></i>
                                        <span>خانم</span>
                                    </button>
                                </div>
                            </div>

                            <div class="mad-vault-gate-field">
                                <label class="mad-vault-gate-label">تاریخ تولد (شمسی):</label>
                                <div class="mad-vault-gate-bdate-grid">
                                    <select id="mad-vault-gate-bday" class="mad-vault-gate-select">
                                        <option value="">روز</option>
                                        <?php for ($d = 1; $d <= 31; $d++): ?>
                                            <option value="<?php echo $d; ?>" <?php selected($b_day, $d); ?>><?php echo $d; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select id="mad-vault-gate-bmonth" class="mad-vault-gate-select">
                                        <option value="">ماه</option>
                                        <?php foreach ($shamsi_months as $m_num => $m_name): ?>
                                            <option value="<?php echo $m_num; ?>" <?php selected($b_month, $m_num); ?>><?php echo esc_html($m_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select id="mad-vault-gate-byear" class="mad-vault-gate-select">
                                        <option value="">سال</option>
                                        <?php for ($y = 1403; $y >= 1320; $y--): ?>
                                            <option value="<?php echo $y; ?>" <?php selected($b_year, $y); ?>><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <span id="mad-vault-gate-error" class="mad-vault-gate-error"></span>

                            <button type="button" id="mad-vault-gate-save-btn" class="mad-vault-gate-submit-btn">
                                <i class="fa-solid fa-unlock-keyhole"></i>
                                <span>ثبت اطلاعات و نمایش نتیجه</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        const madVaultToolJsMap   = <?php echo wp_json_encode($tool_js_map); ?>;
        const madVaultToolHtmlMap = <?php echo wp_json_encode($tool_html_map); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.querySelector('.mad-vault-dashboard-wrapper');
            if (!wrapper) return;

            const ajaxUrl = wrapper.getAttribute('data-ajax-url') || '/wp-admin/admin-ajax.php';
            let isProfileComplete   = wrapper.getAttribute('data-profile-complete') === 'true';
            let currentUserGender   = wrapper.getAttribute('data-user-gender') || '';
            let currentUserBirthdate = wrapper.getAttribute('data-user-birthdate') || '';
            const resModalBodyWrap  = document.getElementById('mad-vault-result-modal-body-wrap');
            const gateOverlay       = document.getElementById('mad-vault-profile-gate-overlay');
            const gateSaveBtn       = document.getElementById('mad-vault-gate-save-btn');
            const gateErrorEl       = document.getElementById('mad-vault-gate-error');
            const gateGenderBtns    = document.querySelectorAll('.mad-vault-gate-gender-btn');
            const gateDaySel        = document.getElementById('mad-vault-gate-bday');
            const gateMonthSel      = document.getElementById('mad-vault-gate-bmonth');
            const gateYearSel       = document.getElementById('mad-vault-gate-byear');
            let gateSelectedGender  = (currentUserGender === 'male' || currentUserGender === 'female') ? currentUserGender : '';
            const ITEMS_PER_PAGE = 12;
            let historyCurrentPage = 1;
            let offersCurrentPage  = 1;
            let notifsCurrentPage  = 1;
            let pendingDeleteRow   = null;

            const resModal = document.getElementById('mad-vault-result-preview-modal');
            if (resModal && resModal.parentElement !== document.body) {
                document.body.appendChild(resModal);
            }
            const delModal = document.getElementById('mad-vault-delete-confirm-modal');
            if (delModal && delModal.parentElement !== document.body) {
                document.body.appendChild(delModal);
            }

            // ۱. جابه‌جایی تب‌های اصلی پروفایل
            const mainTabs = wrapper.querySelectorAll('.mad-vault-main-tab');
            mainTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    mainTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    const target = this.getAttribute('data-panel');
                    ['history', 'offers', 'notifications'].forEach(p => {
                        const el = document.getElementById('mad-vault-panel-' + p);
                        if (el) el.style.display = (p === target) ? 'block' : 'none';
                    });
                });
            });

            // ۲. ویرایش نام و جنسیت درجا
            const toggleEditBtn = document.getElementById('mad-vault-toggle-edit-btn');
            const cancelEditBtn = document.getElementById('mad-vault-cancel-profile-btn');
            const editBox       = document.getElementById('mad-vault-edit-profile-box');
            const saveProfBtn   = document.getElementById('mad-vault-save-profile-btn');
            const genderSelect  = document.getElementById('mad-vault-edit-gender-select');
            const avatarBox     = document.getElementById('mad-vault-profile-avatar-box');

            if (toggleEditBtn && editBox) {
                toggleEditBtn.addEventListener('click', () => {
                    editBox.style.display = (editBox.style.display === 'none') ? 'block' : 'none';
                });
            }
            if (cancelEditBtn && editBox) {
                cancelEditBtn.addEventListener('click', () => {
                    editBox.style.display = 'none';
                });
            }
            if (genderSelect && avatarBox && window.madennaVaultAvatars) {
                genderSelect.addEventListener('change', function() {
                    const g = this.value;
                    avatarBox.innerHTML = (g === 'female') ? window.madennaVaultAvatars.female : window.madennaVaultAvatars.male;
                });
            }
            if (saveProfBtn) {
                saveProfBtn.addEventListener('click', async function() {
                    const newName      = document.getElementById('mad-vault-edit-name-input').value.trim();
                    const newGender    = document.getElementById('mad-vault-edit-gender-select').value;
                    const bDayEl       = document.getElementById('mad-vault-edit-bday');
                    const bMonthEl     = document.getElementById('mad-vault-edit-bmonth');
                    const bYearEl      = document.getElementById('mad-vault-edit-byear');
                    let newBirthdate   = '';
                    if (bYearEl && bYearEl.value && bMonthEl && bMonthEl.value && bDayEl && bDayEl.value) {
                        const y = bYearEl.value.trim();
                        const m = String(bMonthEl.value.trim()).padStart(2, '0');
                        const d = String(bDayEl.value.trim()).padStart(2, '0');
                        newBirthdate = `${y}/${m}/${d}`;
                    } else if (document.getElementById('mad-vault-edit-birthdate-input')) {
                        newBirthdate = document.getElementById('mad-vault-edit-birthdate-input').value.trim();
                    }
                    if (!newName) return;

                    const origText = saveProfBtn.textContent;
                    saveProfBtn.textContent = 'در حال ذخیره...';
                    saveProfBtn.disabled = true;

                    try {
                        const fd = new FormData();
                        fd.append('action', 'madenna_vault_update_profile');
                        fd.append('full_name', newName);
                        fd.append('gender', newGender);
                        fd.append('birthdate', newBirthdate);

                        const res  = await fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                        const json = await res.json();
                        if (json.success && json.data) {
                            if (json.data.gender === 'male' || json.data.gender === 'female') {
                                currentUserGender = json.data.gender;
                                gateSelectedGender = json.data.gender;
                                wrapper.setAttribute('data-user-gender', json.data.gender);
                            }
                            if (json.data.birthdate !== undefined) {
                                currentUserBirthdate = json.data.birthdate || '';
                                wrapper.setAttribute('data-user-birthdate', currentUserBirthdate);
                            }
                            isProfileComplete = ((currentUserGender === 'male' || currentUserGender === 'female') && currentUserBirthdate.trim() !== '');
                            wrapper.setAttribute('data-profile-complete', isProfileComplete ? 'true' : 'false');
                            document.getElementById('mad-vault-display-name').textContent = json.data.name;
                            if (json.data.avatar_svg && avatarBox) {
                                avatarBox.innerHTML = json.data.avatar_svg;
                            }
                            const headerName   = document.querySelector('.mad-vault-header-user-btn .mad-user-name-txt');
                            const headerAvatar = document.querySelector('.mad-vault-header-avatar');
                            if (headerName) headerName.textContent = json.data.name;
                            if (headerAvatar && json.data.header_avatar_svg) {
                                headerAvatar.innerHTML = json.data.header_avatar_svg;
                            }
                            const dispBdate = document.getElementById('mad-vault-display-birthdate');
                            if (dispBdate) {
                                const shamsiMonths = {1:'فروردین',2:'اردیبهشت',3:'خرداد',4:'تیر',5:'مرداد',6:'شهریور',7:'مهر',8:'آبان',9:'آذر',10:'دی',11:'بهمن',12:'اسفند'};
                                const rawB = json.data.birthdate || '';
                                let formattedB = rawB;
                                if (rawB) {
                                    const bp = rawB.split('/');
                                    if (bp.length === 3) {
                                        formattedB = `${parseInt(bp[2],10)} ${shamsiMonths[parseInt(bp[1],10)]||bp[1]} ${bp[0]}`;
                                    }
                                }
                                dispBdate.textContent = formattedB || 'ثبت‌نشده';
                            }
                            if (document.getElementById('mad-vault-edit-birthdate-input')) {
                                document.getElementById('mad-vault-edit-birthdate-input').value = json.data.birthdate || '';
                            }
                            editBox.style.display = 'none';
                        }
                    } catch (e) {}

                    saveProfBtn.textContent = origText;
                    saveProfBtn.disabled = false;
                });
            }

            // ۳. سیستم فیلتر آنی دسته‌بندی مادر + زیردسته + ابزار و تاریخچه تکرار + صفحه‌بندی ۱۲تایی
            const catPills     = wrapper.querySelectorAll('.mad-vault-cat-pill');
            const subcatSelect = document.getElementById('mad-vault-subcat-select');
            const toolSelect   = document.getElementById('mad-vault-tool-select');
            const noMatchBox   = document.getElementById('mad-vault-no-filter-match');
            const historyPager = document.getElementById('mad-vault-history-pagination');
            let activeParentSlug = 'all';

            function getAllHistoryRows() {
                return Array.from(wrapper.querySelectorAll('.mad-vault-history-row'));
            }

            function renderPaginationControls(container, currentPage, totalPages, onPageSelect) {
                if (!container) return;
                if (totalPages <= 1) {
                    container.style.display = 'none';
                    container.innerHTML = '';
                    return;
                }
                container.style.display = 'flex';
                container.innerHTML = '';

                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'mad-vault-page-btn';
                prevBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
                prevBtn.disabled = (currentPage <= 1);
                prevBtn.addEventListener('click', () => {
                    if (currentPage > 1) onPageSelect(currentPage - 1);
                });
                container.appendChild(prevBtn);

                for (let p = 1; p <= totalPages; p++) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'mad-vault-page-btn' + (p === currentPage ? ' active' : '');
                    btn.textContent = p.toLocaleString('fa-IR');
                    btn.addEventListener('click', () => onPageSelect(p));
                    container.appendChild(btn);
                }

                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'mad-vault-page-btn';
                nextBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
                nextBtn.disabled = (currentPage >= totalPages);
                nextBtn.addEventListener('click', () => {
                    if (currentPage < totalPages) onPageSelect(currentPage + 1);
                });
                container.appendChild(nextBtn);
            }

            function syncToolSelectDropdown() {
                if (!toolSelect) return;
                const selectedSub = subcatSelect ? subcatSelect.value : 'all';
                const allRows = getAllHistoryRows();

                const countsPerTool = {};
                const titlesPerTool = {};

                allRows.forEach(row => {
                    const tid   = row.getAttribute('data-tool-id');
                    const title = row.getAttribute('data-tool-title') || 'ابزار';
                    const pCats = (row.getAttribute('data-parent-cats') || '').split(',').filter(Boolean);
                    const sCats = (row.getAttribute('data-sub-cats') || '').split(',').filter(Boolean);

                    const matchParent = (activeParentSlug === 'all' || pCats.includes(activeParentSlug));
                    const matchSub    = (selectedSub === 'all' || sCats.includes(selectedSub));

                    if (matchParent && matchSub && tid) {
                        countsPerTool[tid] = (countsPerTool[tid] || 0) + 1;
                        titlesPerTool[tid] = title;
                    }
                });

                const currentVal = toolSelect.value;
                const uniqueToolIds = Object.keys(countsPerTool);

                Array.from(toolSelect.options).forEach(opt => {
                    if (opt.value === 'all') {
                        opt.textContent = `همه ابزارهای تست‌شده (${uniqueToolIds.length} ابزار)`;
                        return;
                    }
                    const tid = opt.value;
                    const cnt = countsPerTool[tid] || 0;
                    const baseTitle = opt.getAttribute('data-tool-title') || titlesPerTool[tid] || opt.textContent;
                    if (cnt > 0) {
                        opt.style.display = '';
                        opt.disabled = false;
                        opt.textContent = `${baseTitle} (${cnt} بار تست شده)`;
                    } else {
                        opt.style.display = 'none';
                        opt.disabled = true;
                    }
                });

                if (currentVal !== 'all' && !countsPerTool[currentVal]) {
                    toolSelect.value = 'all';
                }
            }

            function applyHistoryFilters(resetPage = true) {
                if (resetPage) historyCurrentPage = 1;

                const selectedSub  = subcatSelect ? subcatSelect.value : 'all';
                const selectedTool = toolSelect ? toolSelect.value : 'all';
                const historyRows  = getAllHistoryRows();
                const matchedRows  = [];

                historyRows.forEach(row => {
                    const rowParents = (row.getAttribute('data-parent-cats') || '').split(',').filter(Boolean);
                    const rowSubs    = (row.getAttribute('data-sub-cats') || '').split(',').filter(Boolean);
                    const rowTool    = row.getAttribute('data-tool-id') || '';

                    const matchParent = (activeParentSlug === 'all' || rowParents.includes(activeParentSlug));
                    const matchSub    = (selectedSub === 'all' || rowSubs.includes(selectedSub));
                    const matchTool   = (selectedTool === 'all' || rowTool === selectedTool);

                    if (matchParent && matchSub && matchTool) {
                        matchedRows.push(row);
                    } else {
                        row.classList.add('mad-vault-row-hidden');
                        row.style.setProperty('display', 'none', 'important');
                    }
                });

                const totalPages = Math.max(1, Math.ceil(matchedRows.length / ITEMS_PER_PAGE));
                if (historyCurrentPage > totalPages) {
                    historyCurrentPage = totalPages;
                }

                const startIdx = (historyCurrentPage - 1) * ITEMS_PER_PAGE;
                const endIdx   = startIdx + ITEMS_PER_PAGE;

                matchedRows.forEach((row, idx) => {
                    if (idx >= startIdx && idx < endIdx) {
                        row.classList.remove('mad-vault-row-hidden');
                        row.style.removeProperty('display');
                    } else {
                        row.classList.add('mad-vault-row-hidden');
                        row.style.setProperty('display', 'none', 'important');
                    }
                });

                if (noMatchBox) {
                    noMatchBox.style.display = (matchedRows.length === 0 && historyRows.length > 0) ? 'block' : 'none';
                }

                renderPaginationControls(historyPager, historyCurrentPage, totalPages, function(newPage) {
                    historyCurrentPage = newPage;
                    applyHistoryFilters(false);
                });
            }

            function refreshAllCountersAndToolRepeats() {
                const allRows = getAllHistoryRows();
                const totalCount = allRows.length;

                const headerTotalEl = document.getElementById('mad-vault-total-records-num');
                const tabHistoryEl  = document.getElementById('mad-vault-tab-history-count');
                if (headerTotalEl) headerTotalEl.textContent = totalCount;
                if (tabHistoryEl) tabHistoryEl.textContent   = totalCount;

                const rowsByTool = {};
                allRows.forEach(r => {
                    const tid = r.getAttribute('data-tool-id');
                    if (!rowsByTool[tid]) rowsByTool[tid] = [];
                    rowsByTool[tid].push(r);
                });

                Object.keys(rowsByTool).forEach(tid => {
                    const list = rowsByTool[tid];
                    const totalForThisTool = list.length;
                    list.forEach((r, idx) => {
                        const attemptNum = totalForThisTool - idx;
                        const badgeEl = r.querySelector('.mad-vault-attempt-badge');
                        if (totalForThisTool <= 1) {
                            if (badgeEl) badgeEl.remove();
                        } else if (badgeEl) {
                            badgeEl.textContent = `نوبت ${attemptNum} از ${totalForThisTool}`;
                        }
                    });
                });

                syncToolSelectDropdown();
            }

            catPills.forEach(pill => {
                pill.addEventListener('click', function() {
                    catPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    activeParentSlug = this.getAttribute('data-parent-slug') || 'all';

                    if (subcatSelect) {
                        subcatSelect.value = 'all';
                        Array.from(subcatSelect.options).forEach(opt => {
                            if (opt.value === 'all') return;
                            const optParent = opt.getAttribute('data-parent-slug') || '';
                            const showOpt = (activeParentSlug === 'all' || optParent === activeParentSlug);
                            opt.style.display = showOpt ? '' : 'none';
                            opt.disabled = !showOpt;
                        });
                    }
                    syncToolSelectDropdown();
                    applyHistoryFilters(true);
                });
            });

            if (subcatSelect) {
                subcatSelect.addEventListener('change', () => {
                    syncToolSelectDropdown();
                    applyHistoryFilters(true);
                });
            }
            if (toolSelect) {
                toolSelect.addEventListener('change', () => applyHistoryFilters(true));
            }

            syncToolSelectDropdown();
            applyHistoryFilters(true);

            function setupSimpleListPagination(itemSelector, pagerId, currentPageRefSetter, getPage) {
                const items = Array.from(wrapper.querySelectorAll(itemSelector));
                const pager = document.getElementById(pagerId);
                if (!items.length || !pager) return;

                function renderPage(page) {
                    currentPageRefSetter(page);
                    const totalPages = Math.max(1, Math.ceil(items.length / ITEMS_PER_PAGE));
                    const start = (page - 1) * ITEMS_PER_PAGE;
                    const end   = start + ITEMS_PER_PAGE;
                    items.forEach((el, idx) => {
                        el.style.display = (idx >= start && idx < end) ? '' : 'none';
                    });
                    renderPaginationControls(pager, page, totalPages, renderPage);
                }
                renderPage(getPage());
            }

            setupSimpleListPagination('.mad-vault-paginated-offer', 'mad-vault-offers-pagination', p => { offersCurrentPage = p; }, () => offersCurrentPage);
            setupSimpleListPagination('.mad-vault-paginated-notif', 'mad-vault-notifs-pagination', p => { notifsCurrentPage = p; }, () => notifsCurrentPage);

            // =========================================================================
            // ۴. موتور Hydration غیرمخرب برای نمایش نتیجه ابزار با ۱۰۰٪ CSS و JS اصلی
            // =========================================================================
            const resModalBody   = document.getElementById('mad-vault-result-modal-body');
            const resModalTitle  = document.getElementById('mad-res-modal-title');
            const resModalBadge  = document.getElementById('mad-res-modal-badge');
            const resModalDate   = document.getElementById('mad-res-modal-date');
            const resRetryLink   = document.getElementById('mad-res-modal-retry-link');
            const resCloseBtn    = document.getElementById('mad-res-modal-close-btn');
            const resPrintBtn    = document.getElementById('mad-res-modal-print-btn');

            function restoreChartsInContainer(scopeEl) {
                if (!scopeEl) return;
                scopeEl.querySelectorAll('canvas').forEach(cvs => {
                    const cfgStr  = cvs.getAttribute('data-vault-chart-config');
                    const dataUrl = cvs.getAttribute('data-vault-canvas-state');
                    if (cfgStr && window.Chart) {
                        try {
                            const existing = typeof window.Chart.getChart === 'function' ? window.Chart.getChart(cvs) : null;
                            if (existing) existing.destroy();
                            const parsedCfg = JSON.parse(cfgStr);
                            if (parsedCfg.options) {
                                parsedCfg.options.animation = false;
                            }
                            new window.Chart(cvs.getContext('2d'), parsedCfg);
                            return;
                        } catch (e) {}
                    }
                    if (dataUrl) {
                        const img = new Image();
                        img.onload = function() {
                            const savedW = parseInt(cvs.getAttribute('data-vault-w'), 10) || img.naturalWidth || 500;
                            const savedH = parseInt(cvs.getAttribute('data-vault-h'), 10) || img.naturalHeight || 320;
                            cvs.width  = savedW;
                            cvs.height = savedH;
                            const ctx  = cvs.getContext('2d');
                            if (ctx) {
                                ctx.clearRect(0, 0, cvs.width, cvs.height);
                                ctx.drawImage(img, 0, 0, cvs.width, cvs.height);
                            }
                        };
                        img.src = dataUrl;
                    }
                });
            }

            function buildHydratableToolScript(rawJs) {
                if (!rawJs || typeof rawJs !== 'string') return '';
                const fnNames = new Set();
                let m;
                const fnDeclRegex = /function\s+([a-zA-Z0-9_$]+)\s*\(/g;
                while ((m = fnDeclRegex.exec(rawJs)) !== null) {
                    if (m[1] && !['init', 'reset', 'restart'].includes(m[1])) {
                        fnNames.add(m[1]);
                    }
                }
                const varFnRegex = /(?:const|let|var)\s+([a-zA-Z0-9_$]+)\s*=\s*(?:async\s*)?(?:function\b|\([^)]*\)\s*=>|[a-zA-Z0-9_$]+\s*=>)/g;
                while ((m = varFnRegex.exec(rawJs)) !== null) {
                    if (m[1] && !['init', 'reset', 'restart'].includes(m[1])) {
                        fnNames.add(m[1]);
                    }
                }
                if (fnNames.size === 0) return rawJs;

                let exportLines = '\n;try {\n';
                fnNames.forEach(fn => {
                    exportLines += `if (typeof ${fn} === 'function') { window.${fn} = ${fn}; }\n`;
                });
                exportLines += '} catch(e) {}\n';

                const trimmed = rawJs.trim();
                const iifeEndMatch = trimmed.match(/(\}\s*\)\s*\([^)]*\)\s*;?\s*|\}\s*\([^)]*\)\s*\)\s*;?\s*)$/);
                if (iifeEndMatch) {
                    const idx = trimmed.lastIndexOf(iifeEndMatch[0]);
                    const candidate = trimmed.slice(0, idx) + exportLines + trimmed.slice(idx);
                    try {
                        new Function(candidate);
                        return candidate;
                    } catch (e) {}
                }

                const fallbackCandidate = rawJs + exportLines;
                try {
                    new Function(fallbackCandidate);
                    return fallbackCandidate;
                } catch (e) {
                    return rawJs;
                }
            }

            function lockDomState(rootEl) {
                const lockedElements = [];
                const lockedTexts    = [];

                const walker = document.createTreeWalker(rootEl, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT, null, false);
                let node = walker.nextNode();
                while (node) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        lockedElements.push({
                            el: node,
                            className: typeof node.className === 'string' ? node.className : null,
                            cssText: node.style ? node.style.cssText : '',
                            hiddenStep: node.getAttribute('data-vault-hidden-step'),
                            singleCol: node.getAttribute('data-vault-single-col'),
                            childNodes: Array.from(node.childNodes)
                        });
                    } else if (node.nodeType === Node.TEXT_NODE) {
                        lockedTexts.push({
                            node: node,
                            text: node.nodeValue
                        });
                    }
                    node = walker.nextNode();
                }

                return function restoreDomState() {
                    lockedTexts.forEach(item => {
                        if (item.node.nodeValue !== item.text) {
                            item.node.nodeValue = item.text;
                        }
                    });
                    lockedElements.forEach(item => {
                        const el = item.el;
                        if (item.className !== null && typeof el.className === 'string' && el.className !== item.className) {
                            el.className = item.className;
                        }
                        if (el.style && el.style.cssText !== item.cssText) {
                            el.style.cssText = item.cssText;
                        }
                        if (item.hiddenStep === 'true') {
                            el.setAttribute('data-vault-hidden-step', 'true');
                        }
                        if (item.singleCol === 'true') {
                            el.setAttribute('data-vault-single-col', 'true');
                        }
                        if (item.childNodes.length > 0 && el.childNodes.length === 0) {
                            item.childNodes.forEach(c => el.appendChild(c));
                        }
                    });
                };
            }

            function findResultContainerInToolRoot(toolRoot) {
                if (!toolRoot) return null;

                function hasInteractiveInputs(el) {
                    if (!el) return false;
                    return !!el.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea');
                }

                function hasQuestionOrIntroScreens(el) {
                    if (!el) return false;
                    return !!el.querySelector(
                        '#madenna-tool-inputs, [data-tool-inputs], #quiz-screen, #question-screen, #quiz-section, #questions-section, #start-screen, #welcome-screen, #intro-screen, #input-section, #inputs-section, #form-section'
                    );
                }

                function isInputOrQuizNode(node) {
                    if (!node || node.nodeType !== 1) return false;
                    const id  = (node.id || '').toLowerCase();
                    const cls = (typeof node.className === 'string' ? node.className : '').toLowerCase();
                    if (node.tagName === 'FORM') return true;
                    if (hasInteractiveInputs(node) || hasQuestionOrIntroScreens(node)) return true;
                    const kwList = ['quiz', 'question', 'intro', 'welcome', 'start-screen', 'start-view', 'input-sec', 'inputs-sec', 'form-sec'];
                    for (let i = 0; i < kwList.length; i++) {
                        if ((id.includes(kwList[i]) || cls.includes(kwList[i])) && !id.includes('result') && !cls.includes('result')) {
                            return true;
                        }
                    }
                    return false;
                }

                function climbToOutermostResultWrapper(startEl) {
                    let best = startEl;
                    let p = startEl.parentElement;
                    while (p && p !== toolRoot && !p.hasAttribute('data-vault-full-snapshot')) {
                        if (hasInteractiveInputs(p) || hasQuestionOrIntroScreens(p)) break;
                        const sibs = Array.from(p.children).filter(c => !['STYLE', 'LINK', 'SCRIPT'].includes(c.tagName));
                        const hasQuizOrInputSib = sibs.some(s => {
                            if (s === best) return false;
                            const sid  = (s.id || '').toLowerCase();
                            const scls = (typeof s.className === 'string' ? s.className : '').toLowerCase();
                            if (sid.includes('modal') || scls.includes('modal') || s.getAttribute('role') === 'dialog') return false;
                            if (s.getAttribute('data-vault-hidden-step') === 'true') return true;
                            if (isInputOrQuizNode(s)) return true;
                            if (s.getAttribute('data-initial-visible') === 'true' && best.getAttribute('data-was-hidden-initially') === 'true') return true;
                            return false;
                        });
                        if (hasQuizOrInputSib) break;
                        best = p;
                        p = p.parentElement;
                    }
                    return best;
                }

                // ۱. بررسی شناسه‌های اصلی کانتینر نتیجه (از بیرونی‌ترین به داخلی‌ترین)
                const explicitSelectors = [
                    '#madenna-tool-result',
                    '[data-tool-result="true"]',
                    '.madenna-tool-result',
                    '#result-screen', '#results-screen',
                    '#result-section', '#results-section',
                    '#result-view', '#results-view',
                    '#result-container', '#results-container',
                    '#final-result', '#quiz-result',
                    '#result', '#results',
                    '#report-section', '#output-section',
                    '#result-panel', '#results-panel',
                    '#result-box', '#result-card'
                ];

                for (let i = 0; i < explicitSelectors.length; i++) {
                    const found = toolRoot.querySelector(explicitSelectors[i]);
                    if (found && found !== toolRoot && !found.hasAttribute('data-vault-full-snapshot') && found.innerText.trim().length > 15) {
                        if (!hasInteractiveInputs(found) && !hasQuestionOrIntroScreens(found)) {
                            return climbToOutermostResultWrapper(found);
                        }
                    }
                }

                // ۲. بررسی کاندیداهای عمومی از بیرونی‌ترین به داخلی‌ترین (بدون reverse)
                const broadCandidates = Array.from(toolRoot.querySelectorAll(
                    '[id*="result" i], [class*="result-screen" i], [class*="result-section" i], [class*="result-container" i], [class*="result-panel" i], [class*="result-box" i], [class*="result-card" i], [id*="report" i], [id*="output" i]'
                ));

                for (let j = 0; j < broadCandidates.length; j++) {
                    const cand = broadCandidates[j];
                    if (cand === toolRoot || cand.hasAttribute('data-vault-full-snapshot')) continue;
                    if (['BUTTON', 'SPAN', 'I', 'INPUT', 'LABEL', 'H1', 'H2', 'H3', 'H4', 'P', 'A'].includes(cand.tagName)) continue;
                    if (hasInteractiveInputs(cand) || hasQuestionOrIntroScreens(cand)) continue;

                    if (cand.innerText.trim().length > 25) {
                        return climbToOutermostResultWrapper(cand);
                    }
                }

                // ۳. تشخیص ستون نتیجه در ماشین‌حساب‌های دو ستونی (Grid / Flex)
                const grids = Array.from(toolRoot.querySelectorAll('[class*="grid"], [class*="flex"]'));
                for (let g = 0; g < grids.length; g++) {
                    const gridEl = grids[g];
                    const cols = Array.from(gridEl.children).filter(c => !['STYLE', 'LINK', 'SCRIPT'].includes(c.tagName));
                    if (cols.length >= 2 && cols.length <= 4) {
                        const inputCols  = cols.filter(c => hasInteractiveInputs(c));
                        const outputCols = cols.filter(c => !hasInteractiveInputs(c) && !hasQuestionOrIntroScreens(c) && c.innerText.trim().length > 25);
                        if (inputCols.length >= 1 && outputCols.length === 1) {
                            return outputCols[0];
                        }
                    }
                }

                return null;
            }

            function forceHideStep(el) {
                if (!el || el.nodeType !== 1) return;
                el.setAttribute('data-vault-hidden-step', 'true');
                el.classList.add('hidden');
                if (el.style) {
                    el.style.setProperty('display', 'none', 'important');
                }
            }

            function forceShowStep(el) {
                if (!el || el.nodeType !== 1) return;
                el.removeAttribute('data-vault-hidden-step');
                el.classList.remove('hidden', 'mad-vault-blurred', 'mad-vault-unlocked');
                if (el.style && el.style.display === 'none') {
                    el.style.removeProperty('display');
                }
            }

            function isolateResultViewInModal(toolRoot) {
                if (!toolRoot) return;

                function isInputOrQuizSibling(sib, currentResultBranch) {
                    if (!sib || sib.nodeType !== 1) return false;
                    const sid  = (sib.id || '').toLowerCase();
                    const scls = (typeof sib.className === 'string' ? sib.className : '').toLowerCase();
                    if (sib.tagName === 'FORM') return true;
                    if (sib.getAttribute('data-vault-hidden-step') === 'true') return true;
                    if (sib.classList.contains('hidden') || (sib.style && sib.style.display === 'none')) return true;
                    if (sib.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea')) return true;
                    if (sib.querySelector('#madenna-tool-inputs, [data-tool-inputs], #quiz-screen, #question-screen, #quiz-section, #questions-section, #start-screen, #welcome-screen, #intro-screen')) return true;

                    const kwList = ['quiz', 'question', 'intro', 'welcome', 'start-screen', 'start-view', 'input-sec', 'inputs-sec', 'form-sec', 'header'];
                    for (let i = 0; i < kwList.length; i++) {
                        if ((sid.includes(kwList[i]) || scls.includes(kwList[i])) && !sid.includes('result') && !scls.includes('result')) {
                            return true;
                        }
                    }
                    if (sib.getAttribute('data-initial-visible') === 'true' && currentResultBranch.getAttribute('data-was-hidden-initially') === 'true') {
                        return true;
                    }
                    return false;
                }

                const targetResult = findResultContainerInToolRoot(toolRoot);
                if (targetResult && targetResult !== toolRoot) {
                    forceShowStep(targetResult);

                    // اطمینان از اینکه هیچ بخشی در داخل خود کانتینر نتیجه به اشتباه data-vault-hidden-step نگرفته باشد
                    targetResult.querySelectorAll('[data-vault-hidden-step="true"]').forEach(innerEl => {
                        if (!innerEl.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea') && innerEl.tagName !== 'FORM') {
                            innerEl.removeAttribute('data-vault-hidden-step');
                            if (innerEl.style && innerEl.style.display === 'none') {
                                innerEl.style.removeProperty('display');
                            }
                        }
                    });

                    let curr = targetResult;
                    while (curr && curr !== toolRoot) {
                        const parent = curr.parentElement;
                        if (!parent) break;

                        forceShowStep(parent);

                        let hidSibling = false;
                        Array.from(parent.children).forEach(sib => {
                            if (sib === curr) return;
                            if (['STYLE', 'LINK', 'SCRIPT'].includes(sib.tagName)) return;

                            const sibId  = (sib.id || '').toLowerCase();
                            const sibCls = (typeof sib.className === 'string' ? sib.className : '').toLowerCase();
                            const isModal = sibId.includes('modal') || sibCls.includes('modal') || sib.getAttribute('role') === 'dialog';

                            if (isModal) {
                                sib.removeAttribute('data-vault-hidden-step');
                                sib.classList.add('hidden');
                                return;
                            }

                            if (isInputOrQuizSibling(sib, curr)) {
                                forceHideStep(sib);
                                hidSibling = true;
                            }
                        });

                        if (hidSibling) {
                            const pCls = typeof parent.className === 'string' ? parent.className : '';
                            if (pCls.includes('grid') || pCls.includes('flex-row') || pCls.includes('md:flex') || pCls.includes('lg:flex')) {
                                parent.setAttribute('data-vault-single-col', 'true');
                                parent.style.setProperty('display', 'block', 'important');
                            }
                        }

                        curr = parent;
                    }
                }

                // مخفی‌سازی قطعی بخش‌های فرم، سوالات و ورودی‌ها که خارج از کادر نتیجه هستند
                const nonResultSelectors = [
                    '#madenna-tool-inputs',
                    '[data-tool-inputs]',
                    '#quiz-section', '#questions-section',
                    '#question-screen', '#quiz-screen',
                    '#start-screen', '#welcome-screen', '#intro-screen',
                    '#input-section', '#inputs-section', '#form-section',
                    '[id*="question-card" i]', '[id*="quiz-box" i]',
                    '[id*="input-panel" i]', '[id*="form-panel" i]',
                    '[id*="quiz-view" i]', '[id*="intro-view" i]', '[id*="start-view" i]',
                    'form'
                ];
                toolRoot.querySelectorAll(nonResultSelectors.join(',')).forEach(el => {
                    if (targetResult && (el === targetResult || el.contains(targetResult) || targetResult.contains(el))) return;
                    if (el.querySelector('#madenna-tool-result, [data-tool-result="true"]')) return;
                    forceHideStep(el);
                });

                // مخفی‌سازی ستون یا باکس حاوی اینپوت در ماشین‌حساب‌ها بدون دست زدن به کانتینر نتیجه
                toolRoot.querySelectorAll('input:not([type="hidden"]):not([readonly]), select, textarea').forEach(inp => {
                    if (targetResult && targetResult.contains(inp)) return;
                    let panel = inp.parentElement;
                    while (panel && panel !== toolRoot && !panel.hasAttribute('data-vault-full-snapshot')) {
                        const parent = panel.parentElement;
                        if (!parent || parent === toolRoot || parent.hasAttribute('data-vault-full-snapshot')) break;
                        if (targetResult && (parent === targetResult || parent.contains(targetResult))) break;
                        panel = parent;
                    }
                    if (panel && panel !== toolRoot && (!targetResult || (!panel.contains(targetResult) && !targetResult.contains(panel)))) {
                        forceHideStep(panel);
                    }
                });
            }

            function syncGateFormInputs() {
                gateGenderBtns.forEach(btn => {
                    const g = btn.getAttribute('data-gate-gender');
                    btn.classList.toggle('active', g === gateSelectedGender);
                });
                if (currentUserBirthdate) {
                    const bp = currentUserBirthdate.split('/');
                    if (bp.length === 3) {
                        if (gateYearSel) gateYearSel.value = String(parseInt(bp[0], 10) || '');
                        if (gateMonthSel) gateMonthSel.value = String(parseInt(bp[1], 10) || '');
                        if (gateDaySel) gateDaySel.value = String(parseInt(bp[2], 10) || '');
                    }
                }
                if (gateErrorEl) gateErrorEl.textContent = '';
            }

            function applyProfileGateStateInModal() {
                if (!resModalBody) return;
                if (!isProfileComplete) {
                    resModalBody.classList.add('mad-vault-result-locked-blur');
                    if (resModalBodyWrap) resModalBodyWrap.classList.add('is-locked');
                    if (gateOverlay) gateOverlay.style.display = 'flex';
                    if (resPrintBtn) resPrintBtn.style.display = 'none';
                    syncGateFormInputs();
                } else {
                    resModalBody.classList.remove('mad-vault-result-locked-blur');
                    if (resModalBodyWrap) resModalBodyWrap.classList.remove('is-locked');
                    if (gateOverlay) gateOverlay.style.display = 'none';
                    if (resPrintBtn) resPrintBtn.style.display = '';
                }
            }

            gateGenderBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    gateSelectedGender = this.getAttribute('data-gate-gender') || '';
                    gateGenderBtns.forEach(b => b.classList.toggle('active', b === this));
                    if (gateErrorEl) gateErrorEl.textContent = '';
                });
            });

            if (gateSaveBtn) {
                gateSaveBtn.addEventListener('click', async function() {
                    if (gateErrorEl) gateErrorEl.textContent = '';
                    const dVal = gateDaySel ? gateDaySel.value.trim() : '';
                    const mVal = gateMonthSel ? gateMonthSel.value.trim() : '';
                    const yVal = gateYearSel ? gateYearSel.value.trim() : '';

                    if (gateSelectedGender !== 'male' && gateSelectedGender !== 'female') {
                        if (gateErrorEl) gateErrorEl.textContent = 'لطفاً جنسیت خود (آقا یا خانم) را انتخاب کنید.';
                        return;
                    }
                    if (!dVal || !mVal || !yVal) {
                        if (gateErrorEl) gateErrorEl.textContent = 'لطفاً روز، ماه و سال تولد خود را به صورت کامل انتخاب کنید.';
                        return;
                    }

                    const newBirthdate = `${yVal}/${String(mVal).padStart(2, '0')}/${String(dVal).padStart(2, '0')}`;
                    const currentNameEl = document.getElementById('mad-vault-display-name');
                    const currentName = currentNameEl ? currentNameEl.textContent.trim() : '';

                    const origHtml = gateSaveBtn.innerHTML;
                    gateSaveBtn.disabled = true;
                    gateSaveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>در حال ثبت اطلاعات...</span>';

                    try {
                        const fd = new FormData();
                        fd.append('action', 'madenna_vault_update_profile');
                        fd.append('full_name', currentName);
                        fd.append('gender', gateSelectedGender);
                        fd.append('birthdate', newBirthdate);

                        const res = await fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                        const json = await res.json();
                        if (json && json.success && json.data) {
                            currentUserGender = json.data.gender || gateSelectedGender;
                            currentUserBirthdate = json.data.birthdate || newBirthdate;
                            isProfileComplete = true;
                            wrapper.setAttribute('data-profile-complete', 'true');
                            wrapper.setAttribute('data-user-gender', currentUserGender);
                            wrapper.setAttribute('data-user-birthdate', currentUserBirthdate);

                            if (window.madennaVaultConfig) {
                                window.madennaVaultConfig.user_gender = currentUserGender;
                                window.madennaVaultConfig.user_birthdate = currentUserBirthdate;
                            }

                            if (json.data.avatar_svg && avatarBox) {
                                avatarBox.innerHTML = json.data.avatar_svg;
                            }
                            const headerAvatar = document.querySelector('.mad-vault-header-avatar');
                            if (headerAvatar && json.data.header_avatar_svg) {
                                headerAvatar.innerHTML = json.data.header_avatar_svg;
                            }

                            if (genderSelect) genderSelect.value = currentUserGender;
                            const editDayEl = document.getElementById('mad-vault-edit-bday');
                            const editMonthEl = document.getElementById('mad-vault-edit-bmonth');
                            const editYearEl = document.getElementById('mad-vault-edit-byear');
                            const editHiddenBdate = document.getElementById('mad-vault-edit-birthdate-input');
                            if (editDayEl) editDayEl.value = String(parseInt(dVal, 10));
                            if (editMonthEl) editMonthEl.value = String(parseInt(mVal, 10));
                            if (editYearEl) editYearEl.value = String(parseInt(yVal, 10));
                            if (editHiddenBdate) editHiddenBdate.value = currentUserBirthdate;

                            const dispBdate = document.getElementById('mad-vault-display-birthdate');
                            if (dispBdate) {
                                const shamsiMonths = {1:'فروردین',2:'اردیبهشت',3:'خرداد',4:'تیر',5:'مرداد',6:'شهریور',7:'مهر',8:'آبان',9:'آذر',10:'دی',11:'بهمن',12:'اسفند'};
                                dispBdate.textContent = `${parseInt(dVal, 10)} ${shamsiMonths[parseInt(mVal, 10)] || mVal} ${yVal}`;
                            }

                            applyProfileGateStateInModal();
                            setTimeout(() => {
                                restoreChartsInContainer(resModalBody);
                            }, 50);
                        } else {
                            if (gateErrorEl) gateErrorEl.textContent = (json && json.data && json.data.message) ? json.data.message : 'خطا در ثبت اطلاعات. مجدداً تلاش کنید.';
                        }
                    } catch (err) {
                        if (gateErrorEl) gateErrorEl.textContent = 'خطا در برقراری ارتباط با سرور.';
                    }

                    gateSaveBtn.disabled = false;
                    gateSaveBtn.innerHTML = origHtml;
                });
            }

            function openResultPreviewModal(row) {
                if (!row || !resModal || !resModalBody) return;
                const recId      = row.getAttribute('data-record-id');
                const toolId     = row.getAttribute('data-tool-id');
                const toolTitle  = row.getAttribute('data-tool-title') || 'نتیجه ابزار';
                const toolUrl    = row.getAttribute('data-tool-url') || '#';
                const shamsiDate = row.getAttribute('data-shamsi-date') || '';
                const subcatName = row.getAttribute('data-subcat-name') || '';
                const tpl        = document.getElementById('mad-vault-res-tpl-' + recId);
                const inputsEl   = document.getElementById('mad-vault-inputs-' + recId);

                if (!tpl) return;

                resModalTitle.textContent = toolTitle;
                resModalBadge.textContent = subcatName;
                resModalDate.textContent  = shamsiDate;
                resRetryLink.href         = toolUrl;

                const savedHtml = tpl.innerHTML;
                const fullBaseHtml = (madVaultToolHtmlMap && madVaultToolHtmlMap[toolId]) ? madVaultToolHtmlMap[toolId] : '';

                const tempCheck = document.createElement('div');
                tempCheck.innerHTML = savedHtml;
                const isFullSnapshot = !!tempCheck.querySelector('[data-vault-full-snapshot="true"]');

                if (isFullSnapshot || !fullBaseHtml) {
                    resModalBody.innerHTML = `<div id="madenna-tool-${toolId}" class="madenna-tool-wrapper madenna-tool-renderer-wrap" style="width:100%;">${savedHtml}</div>`;
                } else {
                    resModalBody.innerHTML = `<div id="madenna-tool-${toolId}" class="madenna-tool-wrapper madenna-tool-renderer-wrap" style="width:100%;">${fullBaseHtml}</div>`;
                    const toolRoot = resModalBody.firstElementChild;
                    const savedFirstEl = tempCheck.firstElementChild;
                    const savedId = savedFirstEl ? savedFirstEl.id : '';

                    let replacedTarget = false;
                    if (savedId && toolRoot) {
                        const targetInBase = toolRoot.querySelector('#' + CSS.escape(savedId));
                        if (targetInBase) {
                            targetInBase.outerHTML = savedHtml;
                            replacedTarget = true;
                        }
                    }

                    if (!replacedTarget && toolRoot) {
                        Array.from(toolRoot.children).forEach(ch => {
                            if (['STYLE', 'LINK', 'SCRIPT'].includes(ch.tagName)) return;
                            const cid = (ch.id || '').toLowerCase();
                            if (cid.includes('modal') || ch.getAttribute('role') === 'dialog') {
                                ch.classList.add('hidden');
                            } else {
                                forceHideStep(ch);
                            }
                        });
                        const resWrapDiv = document.createElement('div');
                        resWrapDiv.innerHTML = savedHtml;
                        toolRoot.appendChild(resWrapDiv);
                    }
                }

                const toolRoot = resModalBody.firstElementChild;
                if (toolRoot) {
                    toolRoot.querySelectorAll('.mad-vault-blurred').forEach(b => b.classList.remove('mad-vault-blurred'));

                    if (inputsEl && inputsEl.textContent) {
                        try {
                            const savedInputs = JSON.parse(inputsEl.textContent);
                            Object.keys(savedInputs).forEach(k => {
                                const inp = toolRoot.querySelector('#' + CSS.escape(k)) || toolRoot.querySelector(`[name="${CSS.escape(k)}"]`);
                                if (inp) {
                                    if (inp.type === 'checkbox' || inp.type === 'radio') {
                                        inp.checked = (String(inp.value) === String(savedInputs[k]));
                                    } else {
                                        inp.value = savedInputs[k];
                                    }
                                }
                            });
                        } catch (e) {}
                    }

                    isolateResultViewInModal(toolRoot);
                }

                applyProfileGateStateInModal();

                resModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                if (toolRoot && madVaultToolJsMap && madVaultToolJsMap[toolId]) {
                    const restoreLockedState = lockDomState(toolRoot);

                    const origDocAddEvent = document.addEventListener;
                    const origWinAddEvent = window.addEventListener;
                    const origScrollTo    = window.scrollTo;

                    document.addEventListener = function(type, listener, options) {
                        if (type === 'DOMContentLoaded') {
                            if (typeof listener === 'function') {
                                try { listener(new Event('DOMContentLoaded')); } catch (e) {}
                            }
                            return;
                        }
                        return origDocAddEvent.call(document, type, listener, options);
                    };
                    window.addEventListener = function(type, listener, options) {
                        if (type === 'load' || type === 'DOMContentLoaded') {
                            if (typeof listener === 'function') {
                                try { listener(new Event(type)); } catch (e) {}
                            }
                            return;
                        }
                        return origWinAddEvent.call(window, type, listener, options);
                    };
                    window.scrollTo = function() {};

                    try {
                        const runnableJs = buildHydratableToolScript(madVaultToolJsMap[toolId]);
                        const scriptEl   = document.createElement('script');
                        scriptEl.textContent = runnableJs;
                        resModalBody.appendChild(scriptEl);
                    } catch (e) {}

                    document.addEventListener = origDocAddEvent;
                    window.addEventListener   = origWinAddEvent;
                    window.scrollTo           = origScrollTo;

                    restoreLockedState();
                    isolateResultViewInModal(toolRoot);
                }

                setTimeout(() => {
                    if (toolRoot) isolateResultViewInModal(toolRoot);
                    restoreChartsInContainer(resModalBody);
                }, 60);
            }

            if (resModalBody) {
                let preClickHiddenSet = new Set();

                resModalBody.addEventListener('pointerdown', function() {
                    preClickHiddenSet = new Set(Array.from(resModalBody.querySelectorAll('.hidden')));
                }, true);

                resModalBody.addEventListener('click', function(e) {
                    const btn = e.target.closest('button, a, [role="tab"], [data-tab], [data-tab-target], [data-target], [onclick]');
                    if (!btn) return;

                    const btnText    = (btn.innerText || '').trim();
                    const onClickStr = (btn.getAttribute('onclick') || '');
                    const onClickLow = onClickStr.toLowerCase();

                    if (btnText.includes('مجدد') || btnText.includes('دوباره') || onClickLow.includes('reset') || onClickLow.includes('restart')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (resRetryLink && resRetryLink.href && resRetryLink.href !== '#') {
                            window.location.href = resRetryLink.href;
                        }
                        return;
                    }

                    setTimeout(() => {
                        const postClickHidden = Array.from(resModalBody.querySelectorAll('.hidden'));
                        const domChangedByToolJs = (
                            postClickHidden.length !== preClickHiddenSet.size ||
                            postClickHidden.some(el => !preClickHiddenSet.has(el))
                        );

                        if (domChangedByToolJs) {
                            restoreChartsInContainer(resModalBody);
                            return;
                        }

                        const parentInnerModal = btn.closest('[id*="modal" i], [role="dialog"]');
                        if (parentInnerModal && resModalBody.contains(parentInnerModal)) {
                            if (btnText === '×' || btnText === '✕' || btnText.includes('بستن') || btnText.includes('متوجه شدم') || btn.querySelector('.fa-xmark, .fa-times') || onClickLow.includes('close')) {
                                parentInnerModal.classList.add('hidden');
                                return;
                            }
                        }

                        const modalTargetAttr = btn.getAttribute('data-modal-target') || btn.getAttribute('data-modal-toggle');
                        if (modalTargetAttr || onClickLow.includes('modal')) {
                            let mEl = modalTargetAttr ? resModalBody.querySelector('#' + CSS.escape(modalTargetAttr.replace(/^#/, ''))) : null;
                            if (!mEl && onClickStr) {
                                const mMatch = onClickStr.match(/['"]([^'"]+)['"]/);
                                if (mMatch && mMatch[1]) {
                                    mEl = resModalBody.querySelector('#' + CSS.escape(mMatch[1].replace(/^#/, '')));
                                }
                            }
                            if (!mEl) {
                                mEl = resModalBody.querySelector('[id*="modal" i].hidden, [role="dialog"].hidden');
                            }
                            if (mEl) {
                                mEl.classList.remove('hidden');
                                mEl.style.removeProperty('display');
                                return;
                            }
                        }

                        let tabTargetId = btn.getAttribute('data-tab-target') || btn.getAttribute('data-tab') || btn.getAttribute('data-target') || btn.getAttribute('aria-controls');
                        if (!tabTargetId && onClickStr) {
                            const m = onClickStr.match(/['"]([^'"]+)['"]/);
                            if (m && m[1]) tabTargetId = m[1];
                        }
                        const tabBar = btn.parentElement;
                        const siblingBtns = tabBar ? Array.from(tabBar.children).filter(c => c.tagName === btn.tagName) : [];

                        if (tabTargetId || btn.getAttribute('role') === 'tab' || (siblingBtns.length >= 2 && siblingBtns.length <= 8)) {
                            let targetPanel = null;
                            if (tabTargetId) {
                                const cid = tabTargetId.replace(/^#/, '');
                                targetPanel = resModalBody.querySelector('#' + CSS.escape(cid)) ||
                                              resModalBody.querySelector('#tab-' + CSS.escape(cid)) ||
                                              resModalBody.querySelector('#panel-' + CSS.escape(cid)) ||
                                              resModalBody.querySelector(`[data-tab-content="${cid}"], [data-panel="${cid}"], [id*="${cid}" i]`);
                            }
                            if (!targetPanel && siblingBtns.length >= 2 && tabBar.parentElement) {
                                const idx = siblingBtns.indexOf(btn);
                                const cands = [tabBar.parentElement, tabBar.parentElement.parentElement].filter(Boolean);
                                for (const cand of cands) {
                                    const divs = Array.from(cand.children).filter(el => el !== tabBar && el.tagName === 'DIV' && !el.id.toLowerCase().includes('modal') && el.getAttribute('data-vault-hidden-step') !== 'true');
                                    if (divs.length === siblingBtns.length && divs[idx]) {
                                        targetPanel = divs[idx];
                                        break;
                                    }
                                    for (const d of divs) {
                                        const subDivs = Array.from(d.children).filter(el => el.tagName === 'DIV' && el.getAttribute('data-vault-hidden-step') !== 'true');
                                        if (subDivs.length === siblingBtns.length && subDivs[idx]) {
                                            targetPanel = subDivs[idx];
                                            break;
                                        }
                                    }
                                    if (targetPanel) break;
                                }
                            }
                            if (targetPanel && targetPanel.parentElement) {
                                const activeSample = siblingBtns.find(b => b !== btn && (b.className.includes('bg-[#0f5650]') || b.className.includes('bg-white') || b.classList.contains('active')));
                                if (activeSample) {
                                    const actCls   = activeSample.className;
                                    const inactCls = btn.className;
                                    siblingBtns.forEach(b => { b.className = inactCls; b.classList.remove('active'); });
                                    btn.className = actCls;
                                    btn.classList.add('active');
                                }
                                Array.from(targetPanel.parentElement.children).forEach(sib => {
                                    if (sib === tabBar || sib.id.toLowerCase().includes('modal') || sib.getAttribute('data-vault-hidden-step') === 'true') return;
                                    if (sib === targetPanel) {
                                        sib.classList.remove('hidden');
                                        sib.style.removeProperty('display');
                                    } else if (sib.tagName === targetPanel.tagName) {
                                        sib.classList.add('hidden');
                                    }
                                });
                                restoreChartsInContainer(targetPanel);
                            }
                        }
                    }, 20);
                });
            }

            wrapper.addEventListener('click', function(e) {
                const viewBtn = e.target.closest('.mad-vault-view-result-btn');
                if (viewBtn) {
                    const row = viewBtn.closest('.mad-vault-history-row');
                    openResultPreviewModal(row);
                    return;
                }

                const delBtn = e.target.closest('.mad-vault-delete-record-btn');
                if (delBtn) {
                    const row = delBtn.closest('.mad-vault-history-row');
                    if (!row || !delModal) return;
                    pendingDeleteRow = row;
                    delModal.style.display = 'flex';
                }
            });

            const confirmDelYes = document.getElementById('mad-vault-confirm-delete-yes');
            const confirmDelNo  = document.getElementById('mad-vault-confirm-delete-no');

            if (confirmDelNo && delModal) {
                confirmDelNo.addEventListener('click', () => {
                    pendingDeleteRow = null;
                    delModal.style.display = 'none';
                });
                delModal.addEventListener('click', (e) => {
                    if (e.target === delModal) {
                        pendingDeleteRow = null;
                        delModal.style.display = 'none';
                    }
                });
            }

            if (confirmDelYes && delModal) {
                confirmDelYes.addEventListener('click', async function() {
                    if (!pendingDeleteRow) return;
                    const rowToRemove = pendingDeleteRow;
                    const recId       = rowToRemove.getAttribute('data-record-id');

                    const origTxt = confirmDelYes.textContent;
                    confirmDelYes.textContent = 'در حال حذف...';
                    confirmDelYes.disabled = true;

                    try {
                        const fd = new FormData();
                        fd.append('action', 'madenna_vault_delete_record');
                        fd.append('record_id', recId);

                        const res  = await fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                        const json = await res.json();
                        if (json && json.success) {
                            rowToRemove.remove();
                            refreshAllCountersAndToolRepeats();
                            applyHistoryFilters(false);
                        }
                    } catch (err) {}

                    confirmDelYes.textContent = origTxt;
                    confirmDelYes.disabled = false;
                    pendingDeleteRow = null;
                    delModal.style.display = 'none';
                });
            }

            function closeResultModal() {
                if (!resModal) return;
                resModal.style.display = 'none';
                resModalBody.innerHTML = '';
                resModalBody.classList.remove('mad-vault-result-locked-blur');
                if (resModalBodyWrap) resModalBodyWrap.classList.remove('is-locked');
                if (gateOverlay) gateOverlay.style.display = 'none';
                document.body.style.overflow = '';
            }

            if (resCloseBtn) resCloseBtn.addEventListener('click', closeResultModal);
            if (resModal) {
                resModal.addEventListener('click', function(e) {
                    if (e.target === resModal) closeResultModal();
                });
            }

            if (resPrintBtn) {
                resPrintBtn.addEventListener('click', function() {
                    if (!resModalBody || !isProfileComplete) return;

                    const cloneForPrint = resModalBody.cloneNode(true);
                    cloneForPrint.querySelectorAll('[data-vault-hidden-step="true"], [id*="modal" i].hidden, [role="dialog"].hidden, script').forEach(m => m.remove());

                    const origCanvases  = resModalBody.querySelectorAll('canvas');
                    const printCanvases = cloneForPrint.querySelectorAll('canvas');
                    origCanvases.forEach((origCvs, i) => {
                        if (!printCanvases[i]) return;
                        try {
                            const dataUrl = origCvs.toDataURL('image/png');
                            const img = document.createElement('img');
                            img.src = dataUrl;
                            img.style.maxWidth = '100%';
                            img.style.height = 'auto';
                            printCanvases[i].parentNode.replaceChild(img, printCanvases[i]);
                        } catch (e) {}
                    });

                    const printWin = window.open('', '_blank', 'width=980,height=780');
                    if (!printWin) return;
                    const title = resModalTitle ? resModalTitle.textContent : 'کارنامه ابزار مادنا';
                    const date  = resModalDate ? resModalDate.textContent : '';
                    printWin.document.write(`
                        <!DOCTYPE html>
                        <html dir="rtl" lang="fa">
                        <head>
                            <meta charset="UTF-8">
                            <title>${title} | مادنا (madenna.ir)</title>
                            <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
                            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
                            <script src="https://cdn.tailwindcss.com"><\/script>
                            <style>
                                body, * { font-family: 'Vazirmatn', sans-serif !important; }
                                body { direction: rtl; background: #ffffff; padding: 24px; color: #191c1c; }
                                .print-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f5650; padding-bottom: 14px; margin-bottom: 22px; }
                                @media print { .no-print { display: none !important; } }
                            </style>
                        </head>
                        <body>
                            <div class="print-header">
                                <div>
                                    <div style="display:inline-block; background:#0f5650; color:#fff; font-size:11px; font-weight:800; padding:3px 10px; border-radius:999px; margin-bottom:6px;">مادِنا | madenna.ir</div>
                                    <h2 style="margin:0; color:#0f5650; font-size:18px; font-weight:900;">${title}</h2>
                                </div>
                                <span style="font-size:12px; color:#64748b; font-weight:700;">${date}</span>
                            </div>
                            <div>${cloneForPrint.innerHTML}</div>
                            <script>
                                setTimeout(function() { window.print(); }, 650);
                            <\/script>
                        </body>
                        </html>
                    `);
                    printWin.document.close();
                });
            }
        });
        </script>
        <?php
    }
}