<?php
/**
 * راه‌اندازی ساختار (پست‌تایپ‌ها، تکسونومی‌ها و ریدایرکت‌ها)
 * Location: /inc/class-madenna-setup.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Setup {

    public function __construct() {
        add_action('init', [$this, 'register_post_types_and_taxonomies'], 5);
        add_action('init', [$this, 'add_rewrite_rules'], 20);
        add_action('admin_init', [$this, 'sync_elementor_cpt_support']);
        add_filter('term_link', [$this, 'filter_term_links'], 10, 3);
        add_filter('request', [$this, 'handle_flat_parent_category_query']);
        add_filter('template_include', [$this, 'load_custom_templates']);
        add_action('rest_after_insert_tools', [$this, 'compile_rest_tool_assets'], 10, 2);
    }

    public function compile_rest_tool_assets($post, $request) {
        $post_id = $post->ID;
        $css = get_post_meta($post_id, '_tool_css', true);
        $js  = get_post_meta($post_id, '_tool_js', true);
        $dir = wp_upload_dir()['basedir'] . '/madenna-tools';
        if (!file_exists($dir)) wp_mkdir_p($dir);
        if (!empty($css)) @file_put_contents("$dir/tool-{$post_id}.css", Madenna_Helpers::minify_code($css, 'css'));
        if (!empty($js))  @file_put_contents("$dir/tool-{$post_id}.js", "(function(){\n" . Madenna_Helpers::minify_code($js, 'js') . "\n})();");
    }

    public function register_post_types_and_taxonomies() {
        register_post_type('articles', [
            'labels'             => ['name' => 'مقالات مادنا', 'singular_name' => 'مقاله', 'menu_name' => 'مقالات مادنا', 'add_new' => 'افزودن مقاله', 'edit_item' => 'ویرایش مقاله'],
            'public'             => true, 'show_ui' => true, 'show_in_rest' => true,
            'rewrite'            => ['slug' => 'article', 'with_front' => false],
            'has_archive'        => false, 'menu_position' => 5, 'menu_icon' => 'dashicons-media-document',
            'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'elementor'],
        ]);

        register_post_type('tools', [
            'labels'             => ['name' => 'ابزارهای مادنا', 'singular_name' => 'ابزار', 'menu_name' => 'ابزارهای مادنا', 'add_new' => 'افزودن ابزار', 'edit_item' => 'ویرایش ابزار'],
            'public'             => true, 'show_ui' => true, 'show_in_rest' => true,
            'rewrite'            => ['slug' => 'tools', 'with_front' => false],
            'has_archive'        => 'tools', 'menu_position' => 6, 'menu_icon' => 'dashicons-hammer',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'elementor'],
        ]);

        register_taxonomy('madenna_cat', ['articles', 'tools'], [
            'hierarchical'      => true, 'labels' => ['name' => 'دسته‌‌بندی‌های مادنا', 'menu_name' => 'دسته‌بندی‌ها'],
            'show_ui'           => true, 'show_admin_column' => true, 'show_in_rest' => true,
            'query_var'         => true, 'rewrite' => false,
        ]);

        // تعریف callback امنیتی احراز هویت متادیتاهای REST
        $auth_cb = function() {
            return current_user_can('edit_posts');
        };

        // ثبت فیلدهای متادیتا در REST API برای همگام‌سازی از گوگل شیت
        $tool_metas = [
            '_tool_subtitle', '_tool_html', '_tool_css', '_tool_js', 
            '_tool_force_site_font', '_tool_icon_class', '_tool_color_primary', 
            '_tool_color_secondary', '_tool_color_accent', '_yoast_wpseo_title', '_yoast_wpseo_metadesc'
        ];
        foreach ($tool_metas as $meta_key) {
            register_post_meta('tools', $meta_key, [
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'string',
                'auth_callback' => $auth_cb,
            ]);
        }

        register_post_meta('tools', '_tool_libraries', [
            'show_in_rest' => [
                'schema' => ['type' => 'array', 'items' => ['type' => 'string']]
            ],
            'single' => true, 'type' => 'array', 'auth_callback' => $auth_cb,
        ]);

        register_post_meta('tools', '_tool_faqs', [
            'show_in_rest' => [
                'schema' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => ['q' => ['type' => 'string'], 'a' => ['type' => 'string']]
                    ]
                ]
            ],
            'single' => true, 'type' => 'array', 'auth_callback' => $auth_cb,
        ]);

        // متادیتاهای مقالات در REST API
        $article_metas = [
            '_article_subtitle', '_article_audio_url', '_article_icon_class', 
            '_yoast_wpseo_title', '_yoast_wpseo_metadesc'
        ];
        foreach ($article_metas as $meta_key) {
            register_post_meta('articles', $meta_key, [
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'string',
                'auth_callback' => $auth_cb,
            ]);
        }

        register_post_meta('articles', '_article_reading_time', [
            'show_in_rest' => true, 'single' => true, 'type' => 'integer', 'auth_callback' => $auth_cb,
        ]);

        register_post_meta('articles', '_article_faqs', [
            'show_in_rest' => [
                'schema' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => ['q' => ['type' => 'string'], 'a' => ['type' => 'string']]
                    ]
                ]
            ],
            'single' => true, 'type' => 'array', 'auth_callback' => $auth_cb,
        ]);
                        
        // اجازه ذخیره متادیتاهای دارای آندرلاین در REST API
        add_filter('is_protected_meta', function ($protected, $meta_key) {
            if (strpos($meta_key, '_tool_') === 0 || strpos($meta_key, '_article_') === 0 || strpos($meta_key, '_yoast_') === 0) {
                return false;
            }
            return $protected;
        }, 10, 2);
    }

    public function add_rewrite_rules() {
        add_rewrite_rule('^cate/([^/]+)/?$', 'index.php?madenna_cat=$matches[1]', 'top');
    }

    public function filter_term_links($url, $term, $taxonomy) {
        if ($taxonomy !== 'madenna_cat' || !is_object($term) || is_wp_error($term)) return $url;
        return (isset($term->parent) && $term->parent == 0) ? home_url('/' . $term->slug . '/') : home_url('/cate/' . $term->slug . '/');
    }

    public function handle_flat_parent_category_query($query_vars) {
        if (is_admin() || isset($_GET['elementor-preview']) || isset($_GET['preview']) || (isset($_GET['action']) && $_GET['action'] === 'elementor') || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) return $query_vars;

        $potential_slug = $query_vars['pagename'] ?? ($query_vars['name'] ?? '');
        if (!empty($potential_slug) && empty($query_vars['post_type'])) {
            $page_check = get_page_by_path($potential_slug, OBJECT, 'page');
            if ($page_check) return $query_vars;

            // بهینه‌سازی: استفاده از کش شیء وردپرس جهت جلوگیری از اجرای کوئری مکرر دیتابیس در هر لود صفحه
            $cache_key = 'madenna_flat_cat_' . md5($potential_slug);
            $term = wp_cache_get($cache_key, 'madenna_terms');

            if (false === $term) {
                $term = get_term_by('slug', $potential_slug, 'madenna_cat');
                wp_cache_set($cache_key, $term ?: 0, 'madenna_terms', HOUR_IN_SECONDS);
            }

            if (is_object($term) && !is_wp_error($term) && $term->parent == 0) {
                unset($query_vars['pagename'], $query_vars['name']);
                $query_vars['madenna_cat'] = $term->slug;
            }
        }
        return $query_vars;
    }

    public function sync_elementor_cpt_support() {
        $cpt_support = get_option('elementor_cpt_support');
        if (!is_array($cpt_support)) $cpt_support = ['page', 'post'];
        
        $needs_update = false;
        foreach (['articles', 'tools'] as $cpt) {
            if (!in_array($cpt, $cpt_support)) {
                $cpt_support[] = $cpt;
                $needs_update = true;
            }
        }
        if ($needs_update) {
            update_option('elementor_cpt_support', $cpt_support);
        }
    }

    public function load_custom_templates($template) {
        if (is_tax('madenna_cat') && file_exists(MADENNA_CORE_DIR . 'templates/taxonomy-madenna_cat.php')) {
            return MADENNA_CORE_DIR . 'templates/taxonomy-madenna_cat.php';
        }
        return $template;
    }
}
