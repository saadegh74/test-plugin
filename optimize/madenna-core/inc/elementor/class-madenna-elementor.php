<?php
/**
 * ثبت کامل ویجت‌ها و تگ‌های داینامیک المنتور به شکل ایمن و بدون خطای صفحه سفید
 * Location: /inc/elementor/class-madenna-elementor.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Elementor_Init {

    public function __construct() {
        add_action('elementor/frontend/after_register_styles', [$this, 'register_styles']);
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/dynamic_tags/register', [$this, 'register_dynamic_tags']);
    }

    public function register_styles() {
        wp_register_style(
            'madenna-elementor-widgets',
            MADENNA_CORE_URL . 'assets/css/elementor-widgets.css',
            [],
            defined('MADENNA_CORE_VERSION') ? MADENNA_CORE_VERSION : '8.3.1'
        );
        wp_register_style(
            'madenna-vault-css',
            MADENNA_CORE_URL . 'assets/css/madenna-vault.css',
            [],
            defined('MADENNA_CORE_VERSION') ? MADENNA_CORE_VERSION : '8.3.1'
        );
    }

    public function register_widgets($widgets_manager) {
        $widget_files = [
            'class-widget-auto-faq.php'          => 'Madenna_Auto_FAQ_Elementor_Widget',
            'class-widget-categories.php'        => 'Madenna_Categories_Elementor_Widget',
            'class-widget-tools.php'             => 'Madenna_Tools_Elementor_Widget',
            'class-widget-dynamic-icon.php'      => 'Madenna_Dynamic_Icon_Elementor_Widget',
            'class-widget-search.php'            => 'Madenna_Search_Elementor_Widget',
            'class-widget-filters.php'           => 'Madenna_Filters_Elementor_Widget',
            'class-widget-tool-renderer.php'     => 'Madenna_Tool_Renderer_Elementor_Widget',
            'class-widget-user-vault.php'        => 'Madenna_User_Vault_Elementor_Widget',
            'class-widget-vault-header-auth.php' => 'Madenna_Vault_Header_Auth_Widget',
        ];

        foreach ($widget_files as $file => $class_name) {
            $path = MADENNA_CORE_DIR . 'inc/elementor/widgets/' . $file;
            if (file_exists($path)) {
                require_once $path;
                if (class_exists($class_name)) {
                    $widgets_manager->register(new $class_name());
                }
            }
        }
    }

    public function register_dynamic_tags($dynamic_tags_manager) {
        \Elementor\Plugin::$instance->dynamic_tags->register_group('madenna_group', [
            'title' => '✨ فیلدهای اختصاصی مادنا'
        ]);

        $dynamic_tags_manager->register(new \Madenna_Text_Dynamic_Tag());
        $dynamic_tags_manager->register(new \Madenna_Color_Dynamic_Tag());
        $dynamic_tags_manager->register(new \Madenna_Media_Dynamic_Tag());
    }
}

/* ========================================================================= */
/* ELEMENTOR DYNAMIC TAG CLASSES                                             */
/* ========================================================================= */

if (!class_exists('Madenna_Text_Dynamic_Tag')) {
    class Madenna_Text_Dynamic_Tag extends \Elementor\Core\DynamicTags\Tag {
        public function get_name() { return 'madenna_text_tag'; }
        public function get_title() { return 'متن و محتوای مادنا'; }
        public function get_group() { return 'madenna_group'; }
        public function get_categories() { return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY]; }
        protected function register_controls() {
            $this->add_control('field_key', [
                'label' => 'انتخاب فیلد',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '_article_subtitle'     => 'مقاله - زیرعنوان',
                    '_article_reading_time' => 'مقاله - زمان مطالعه',
                    '_article_icon_class'   => 'مقاله - کلاس آیکون (فونت)',
                    '_tool_subtitle'        => 'ابزار - زیرعنوان',
                    '_tool_icon_class'      => 'ابزار - کلاس آیکون (فونت)',
                    'cat_subtitle'          => 'دسته‌بندی - زیرعنوان',
                    'cat_excerpt'           => 'دسته‌بندی - چکیده',
                ],
            ]);
        }
        public function render() {
            $key = $this->get_settings('field_key');
            if (!$key) return;

            if (strpos($key, 'cat_') === 0 && (is_tax('madenna_cat') || is_category() || is_tag())) {
                $obj = get_queried_object();
                if (isset($obj->term_id)) {
                    echo wp_kses_post(get_term_meta($obj->term_id, $key, true));
                }
            } else {
                echo wp_kses_post(get_post_meta(get_the_ID(), $key, true));
            }
        }
    }
}

if (!class_exists('Madenna_Color_Dynamic_Tag')) {
    class Madenna_Color_Dynamic_Tag extends \Elementor\Core\DynamicTags\Tag {
        public function get_name() { return 'madenna_color_tag'; }
        public function get_title() { return 'پالت رنگ‌های مادنا'; }
        public function get_group() { return 'madenna_group'; }
        public function get_categories() { return [\Elementor\Modules\DynamicTags\Module::COLOR_CATEGORY]; }
        protected function register_controls() {
            $this->add_control('field_key', [
                'label' => 'انتخاب رنگ',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '_tool_color_primary'   => 'ابزار - رنگ اصلی',
                    '_tool_color_secondary' => 'ابزار - رنگ ثانویه',
                    '_tool_color_accent'    => 'ابزار - رنگ اکسنت (پس‌زمینه آیکون)',
                    'cat_color_primary'     => 'دسته‌بندی - رنگ اصلی',
                    'cat_color_secondary'   => 'دسته‌بندی - رنگ ثانویه',
                    'cat_color_accent'      => 'دسته‌بندی - رنگ اکسنت',
                    'cat_icon_color'        => 'دسته‌بندی - رنگ آیکون',
                    'cat_icon_bg'           => 'دسته‌بندی - پس‌زمینه آیکون',
                ],
            ]);
        }
        public function render() {
            $key = $this->get_settings('field_key');
            if (!$key) return;

            if (strpos($key, 'cat_') === 0 && (is_tax('madenna_cat') || is_category() || is_tag())) {
                $obj = get_queried_object();
                if (isset($obj->term_id)) {
                    echo esc_attr(get_term_meta($obj->term_id, $key, true));
                }
            } else {
                echo esc_attr(get_post_meta(get_the_ID(), $key, true));
            }
        }
    }
}

if (!class_exists('Madenna_Media_Dynamic_Tag')) {
    class Madenna_Media_Dynamic_Tag extends \Elementor\Core\DynamicTags\Data_Tag {
        public function get_name() { return 'madenna_media_tag'; }
        public function get_title() { return 'رسانه و لینک مادنا (صوت/تصویر)'; }
        public function get_group() { return 'madenna_group'; }
        public function get_categories() {
            return [
                \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
                \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
                \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY
            ];
        }
        protected function register_controls() {
            $this->add_control('field_key', [
                'label' => 'انتخاب فیلد رسانه',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '_article_audio_url' => 'مقاله - لینک فایل صوتی (پادکست)',
                    '_article_svg_url'   => 'مقاله - فایل SVG آپلود شده',
                    '_tool_icon_url'     => 'ابزار - فایل SVG آپلود شده',
                    'cat_icon_url'       => 'دسته‌بندی - فایل SVG آپلود شده',
                    'cat_image'          => 'دسته‌بندی - تصویر شاخص سفارشی',
                ],
            ]);
        }
        public function get_value(array $options = []) {
            $key = $this->get_settings('field_key');
            if (!$key) return [];

            $url = '';
            if (strpos($key, 'cat_') === 0 && (is_tax('madenna_cat') || is_category() || is_tag())) {
                $obj = get_queried_object();
                if (isset($obj->term_id)) {
                    $url = get_term_meta($obj->term_id, $key, true);
                }
            } else {
                $url = get_post_meta(get_the_ID(), $key, true);
            }

            if (empty($url)) return [];

            return [
                'id'  => attachment_url_to_postid($url),
                'url' => $url,
            ];
        }
    }
}