<?php
/**
 * ویجت دکمه ورود / پرونده کاربر برای قرارگیری در هدر المنتور
 * نسخه نهایی ۵.۶ - کامل، بدون خطای سینتکس، دارای کنترل نمایش متن در موبایل و مخفی‌سازی بج صفر
 * Location: /inc/elementor/widgets/class-widget-vault-header-auth.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Vault_Header_Auth_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'madenna_vault_header_auth';
    }

    public function get_title() {
        return 'ورود / پرونده کاربر (هدر مادنا)';
    }

    public function get_icon() {
        return 'eicon-lock-user';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        // ۱. تنظیمات محتوا و نمایش واکنش‌گرا
        $this->start_controls_section('sec_content', [
            'label' => 'تنظیمات دکمه هدر',
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('login_btn_text', [
            'label'   => 'متن دکمه (حالت مهمان)',
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'ورود | ثبت‌نام',
        ]);

        $this->add_control('dashboard_url', [
            'label'       => 'لینک صفحه پروفایل کاربری',
            'type'        => \Elementor\Controls_Manager::URL,
            'placeholder' => home_url('/my-vault/'),
            'default'     => ['url' => home_url('/my-vault/')],
        ]);

        $this->add_control('show_badge', [
            'label'   => 'نمایش شمارنده نوتیفیکیشن (تست/اعلان جدید)',
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ]);

        $this->add_responsive_control('show_user_name_text', [
            'label'          => 'نمایش متن نام کاربر (دسکتاپ / موبایل)',
            'type'           => \Elementor\Controls_Manager::CHOOSE,
            'options'        => [
                'inline-block' => ['title' => 'نمایش', 'icon' => 'eicon-eye'],
                'none'         => ['title' => 'مخفی (فقط آواتار)', 'icon' => 'eicon-eye-close'],
            ],
            'default'        => 'inline-block',
            'tablet_default' => 'inline-block',
            'mobile_default' => 'inline-block',
            'selectors'      => [
                '{{WRAPPER}} .mad-vault-header-user-btn .mad-user-name-txt' => 'display: {{VALUE}} !important;',
            ],
        ]);

        $this->add_responsive_control('show_guest_btn_text', [
            'label'          => 'نمایش متن دکمه مهمان (دسکتاپ / موبایل)',
            'type'           => \Elementor\Controls_Manager::CHOOSE,
            'options'        => [
                'inline-block' => ['title' => 'نمایش', 'icon' => 'eicon-eye'],
                'none'         => ['title' => 'مخفی (فقط آیکون)', 'icon' => 'eicon-eye-close'],
            ],
            'default'        => 'inline-block',
            'tablet_default' => 'inline-block',
            'mobile_default' => 'inline-block',
            'selectors'      => [
                '{{WRAPPER}} .mad-vault-header-login-btn .mad-guest-btn-txt' => 'display: {{VALUE}} !important;',
            ],
        ]);

        $this->add_responsive_control('align', [
            'label'     => 'ترازبندی',
            'type'      => \Elementor\Controls_Manager::CHOOSE,
            'options'   => [
                'flex-start' => ['title' => 'راست', 'icon' => 'eicon-text-align-right'],
                'center'     => ['title' => 'وسط', 'icon' => 'eicon-text-align-center'],
                'flex-end'   => ['title' => 'چپ', 'icon' => 'eicon-text-align-left'],
            ],
            'selectors' => [
                '{{WRAPPER}} .mad-vault-header-auth-wrap' => 'justify-content: {{VALUE}} !important; width: 100%;',
            ],
        ]);

        $this->end_controls_section();

        // ۲. استایل دکمه مهمان
        $this->start_controls_section('sec_style_guest', [
            'label' => 'استایل دکمه ورود (مهمان)',
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'guest_btn_typography',
            'selector' => '{{WRAPPER}} a.mad-vault-header-login-btn',
        ]);

        $this->start_controls_tabs('guest_btn_tabs');

        $this->start_controls_tab('guest_btn_normal', ['label' => 'عادی']);
        $this->add_control('guest_btn_color', [
            'label'     => 'رنگ متن و آیکون',
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} a.mad-vault-header-login-btn' => 'color: {{VALUE}} !important;'],
        ]);
        $this->add_control('guest_btn_bg', [
            'label'     => 'رنگ پس‌زمینه',
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#0f5650',
            'selectors' => ['{{WRAPPER}} a.mad-vault-header-login-btn' => 'background-color: {{VALUE}} !important;'],
        ]);
        $this->end_controls_tab();

        $this->start_controls_tab('guest_btn_hover', ['label' => 'هاور']);
        $this->add_control('guest_btn_hover_color', [
            'label'     => 'رنگ متن (هاور)',
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} a.mad-vault-header-login-btn:hover, {{WRAPPER}} a.mad-vault-header-login-btn:focus' => 'color: {{VALUE}} !important;'],
        ]);
        $this->add_control('guest_btn_hover_bg', [
            'label'     => 'رنگ پس‌زمینه (هاور)',
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#093c37',
            'selectors' => ['{{WRAPPER}} a.mad-vault-header-login-btn:hover, {{WRAPPER}} a.mad-vault-header-login-btn:focus' => 'background-color: {{VALUE}} !important;'],
        ]);
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control('guest_btn_padding', [
            'label'      => 'پدینگ داخلی',
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'separator'  => 'before',
            'selectors'  => ['{{WRAPPER}} a.mad-vault-header-login-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
        ]);

        $this->add_responsive_control('guest_btn_radius', [
            'label'      => 'انحنای حاشیه',
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => ['{{WRAPPER}} a.mad-vault-header-login-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
        ]);

        $this->end_controls_section();

        // ۳. استایل دکمه پروفایل کاربر (لاگین‌شده)
        $this->start_controls_section('sec_style_user', [
            'label' => 'استایل دکمه پروفایل (کاربر لاگین‌شده)',
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'user_btn_typography',
            'selector' => '{{WRAPPER}} a.mad-vault-header-user-btn .mad-user-name-txt',
        ]);

        $this->add_control('user_btn_text_color', [
            'label'     => 'رنگ نام کاربر',
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#191c1c',
            'selectors' => ['{{WRAPPER}} a.mad-vault-header-user-btn' => 'color: {{VALUE}} !important;'],
        ]);

        $this->add_control('user_btn_bg_color', [
            'label'     => 'رنگ پس‌زمینه کادر',
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f8faf8',
            'selectors' => ['{{WRAPPER}} a.mad-vault-header-user-btn' => 'background-color: {{VALUE}} !important;'],
        ]);

        $this->add_control('user_btn_border_color', [
            'label'     => 'رنگ کادر دور',
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#bfc9c6',
            'selectors' => ['{{WRAPPER}} a.mad-vault-header-user-btn' => 'border-color: {{VALUE}} !important;'],
        ]);

        $this->add_responsive_control('user_avatar_size', [
            'label'      => 'اندازه آواتار (پیکسل)',
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 24, 'max' => 64]],
            'selectors'  => [
                '{{WRAPPER}} .mad-vault-header-avatar, {{WRAPPER}} .mad-vault-header-avatar svg' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
            ],
        ]);

        $this->add_responsive_control('user_btn_padding', [
            'label'      => 'پدینگ کادر پروفایل',
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} a.mad-vault-header-user-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s          = $this->get_settings_for_display();
        $login_txt  = !empty($s['login_btn_text']) ? esc_html($s['login_btn_text']) : 'ورود | ثبت‌نام';
        $dash_url   = !empty($s['dashboard_url']['url']) ? esc_url($s['dashboard_url']['url']) : esc_url(home_url('/my-vault/'));
        $show_badge = ($s['show_badge'] ?? 'yes') === 'yes';

        wp_enqueue_style('madenna-vault-css', MADENNA_CORE_URL . 'assets/css/madenna-vault.css', [], '5.6.0');
        wp_enqueue_script('madenna-vault-js', MADENNA_CORE_URL . 'assets/js/madenna-vault.js', ['jquery'], '5.6.0', true);

        echo '<div class="mad-vault-header-auth-wrap" data-dashboard-url="' . esc_attr($dash_url) . '" data-show-badge="' . ($show_badge ? 'yes' : 'no') . '">';

        if (!is_user_logged_in()) {
            ?>
            <a href="#madenna-login" role="button" id="mad-vault-header-login-btn" class="mad-vault-header-login-btn" onclick="if(window.madennaOpenVaultModal){window.madennaOpenVaultModal('header');} return false;">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                <span class="mad-guest-btn-txt"><?php echo $login_txt; ?></span>
            </a>
            <?php
        } else {
            $user_id      = get_current_user_id();
            $user         = wp_get_current_user();
            $name         = ($user && !empty($user->display_name)) ? $user->display_name : 'کاربر مادنا';
            $gender       = get_user_meta($user_id, 'madenna_gender', true) ?: 'male';
            $unseen_count = 0;

            if (class_exists('Madenna_User_Vault') && method_exists('Madenna_User_Vault', 'get_unseen_badge_count')) {
                $unseen_count = (int) Madenna_User_Vault::get_unseen_badge_count($user_id);
            }
            $badge_hidden_cls = ($unseen_count <= 0) ? 'is-hidden' : '';
            $badge_inline_vis = ($unseen_count > 0) ? 'display:inline-flex;' : 'display:none !important;';
            ?>
            <a href="<?php echo $dash_url; ?>" class="mad-vault-header-user-btn" title="مشاهده پرونده و سوابق">
                <?php if ($show_badge): ?>
                    <span class="mad-vault-header-badge <?php echo esc_attr($badge_hidden_cls); ?>" style="<?php echo esc_attr($badge_inline_vis); ?>"><?php echo $unseen_count > 0 ? intval($unseen_count) : ''; ?></span>
                <?php endif; ?>
                <div class="mad-vault-header-avatar <?php echo esc_attr($gender); ?>">
                    <?php 
                    if (class_exists('Madenna_User_Vault') && method_exists('Madenna_User_Vault', 'get_avatar_svg')) {
                        echo Madenna_User_Vault::get_avatar_svg($gender, 36);
                    }
                    ?>
                </div>
                <span class="mad-user-name-txt" style="font-size:13px; font-weight:800;"><?php echo esc_html($name); ?></span>
            </a>
            <?php
        }

        echo '</div>';
    }
}