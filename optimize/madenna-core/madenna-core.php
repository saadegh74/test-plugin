<?php
/**
 * Plugin Name: Madenna Core
 * Plugin URI: https://madenna.ir
 * Description: افزونه هسته مادنا برای مدیریت اختصاصی مقالات، ابزارها، دسته‌بندی‌های فلت، همگام‌سازی دوطرفه، و المان‌های پیشرفته المنتور.
 * Version: 4.3.3
 * Author: Madenna Team
 * Text Domain: madenna-core
 * Location: /madenna-core.php
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MADENNA_CORE_DIR', plugin_dir_path(__FILE__));
define('MADENNA_CORE_URL', plugin_dir_url(__FILE__));
define('MADENNA_CORE_VERSION', '8.3.1');

// بارگذاری فایل‌های اصلی و حیاتی
require_once MADENNA_CORE_DIR . 'inc/class-madenna-user-vault.php';
require_once MADENNA_CORE_DIR . 'inc/class-madenna-helpers.php';
require_once MADENNA_CORE_DIR . 'inc/class-madenna-setup.php';
require_once MADENNA_CORE_DIR . 'inc/class-madenna-admin.php';
require_once MADENNA_CORE_DIR . 'inc/class-madenna-frontend.php';

// بهینه‌سازی عملکرد: بارگذاری فایل هوش مصنوعی منحصراً در محیط مدیریت یا درخواست‌های ایجکس ادمین
if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
    if (file_exists(MADENNA_CORE_DIR . 'inc/class-madenna-ai-wizard.php')) {
        require_once MADENNA_CORE_DIR . 'inc/class-madenna-ai-wizard.php';
    }
}

class Madenna_Core {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // راه‌اندازی تمام بخش‌های تفکیک شده افزونه
        new Madenna_Setup();
        new Madenna_Admin();
        new Madenna_Frontend();
        new Madenna_User_Vault();

        // راه‌اندازی المنتور با تاخیر (فقط زمانی که هسته المنتور لود شده باشد)
        add_action('elementor/init', [$this, 'init_elementor']);
    }

    public function init_elementor() {
        require_once MADENNA_CORE_DIR . 'inc/elementor/class-madenna-elementor.php';
        new Madenna_Elementor_Init();
    }

    public static function activate() {
        $setup = new Madenna_Setup();
        $setup->register_post_types_and_taxonomies();
        $setup->add_rewrite_rules();
        flush_rewrite_rules();

        Madenna_User_Vault::create_vault_table();

        $upload_dir = wp_upload_dir();
        $tools_dir = $upload_dir['basedir'] . '/madenna-tools';
        if (!file_exists($tools_dir)) wp_mkdir_p($tools_dir);
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }
}

// استارت افزونه
Madenna_Core::get_instance();
register_activation_hook(__FILE__, ['Madenna_Core', 'activate']);
register_deactivation_hook(__FILE__, ['Madenna_Core', 'deactivate']);
