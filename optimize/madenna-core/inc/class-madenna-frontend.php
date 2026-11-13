<?php
/**
 * مدیریت کدهای سمت کاربر (شورت‌کدها، استایل‌ها، فیلترهای محتوا و AJAX)
 * Location: /inc/class-madenna-frontend.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Frontend {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_shortcode('madenna_tool', [$this, 'render_tool_shortcode']);
        add_filter('the_content', [$this, 'append_tool_content']);
// اصلاح زنجیره بردکرامپ مقالات و ابزارها
        add_filter('wpseo_breadcrumb_links', [$this, 'fix_madenna_breadcrumbs']);
        // افزودن اکشن‌های AJAX برای جستجو و فیلتر زنده
        add_action('wp_ajax_madenna_live_search', [$this, 'handle_live_search']);
        add_action('wp_ajax_nopriv_madenna_live_search', [$this, 'handle_live_search']);
        
        // شمارشگر بازدید پست‌ها
        add_action('wp', [$this, 'track_post_views']);
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style('font-awesome-6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'madenna_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
        
        if (is_singular('tools')) $this->enqueue_tool_files(get_the_ID());
        
        
// لود سیستم Madenna Vault برای ابزارها
        if (is_singular('tools')) {
            $vault_ver = defined('MADENNA_CORE_VERSION') ? MADENNA_CORE_VERSION : '8.3.0';
            wp_enqueue_style('madenna-vault-css', MADENNA_CORE_URL . 'assets/css/madenna-vault.css', [], $vault_ver);
            wp_enqueue_script('madenna-vault-js', MADENNA_CORE_URL . 'assets/js/madenna-vault.js', ['jquery'], $vault_ver, true);

            $cats = wp_get_post_terms(get_the_ID(), 'madenna_cat');
            $cat_slug = (!empty($cats) && !is_wp_error($cats)) ? $cats[0]->slug : '';

            $uid = get_current_user_id();
            $u = is_user_logged_in() ? wp_get_current_user() : null;
            $current_user_name      = $u ? $u->display_name : '';
            $current_user_gender    = $uid ? (get_user_meta($uid, 'madenna_gender', true) ?: '') : '';
            $current_user_birthdate = $uid ? (get_user_meta($uid, 'madenna_birthdate', true) ?: '') : '';

            wp_localize_script('madenna-vault-js', 'madennaVaultConfig', [
                'ajax_url'       => admin_url('admin-ajax.php'),
                'rest_url'       => esc_url_raw(rest_url('madenna/v1/vault/')),
                'is_logged_in'   => is_user_logged_in(),
                'is_tool_page'   => true,
                'user_id'        => $uid,
                'user_name'      => $current_user_name,
                'user_gender'    => $current_user_gender,
                'user_birthdate' => $current_user_birthdate,
                'tool_id'        => get_the_ID(),
                'category_slug'  => $cat_slug,
                'nonce'          => wp_create_nonce('wp_rest'),
            ]);
        }
    }

    private function enqueue_tool_files($tool_id) {
        $upload_dir = wp_upload_dir();
        $css_file = $upload_dir['basedir'] . "/madenna-tools/tool-{$tool_id}.css";
        $js_file  = $upload_dir['basedir'] . "/madenna-tools/tool-{$tool_id}.js";
        
        $libs = get_post_meta($tool_id, '_tool_libraries', true);
        $libs = is_array($libs) ? $libs : [];
        $all_libs = Madenna_Helpers::get_available_tool_libraries();

        foreach ($libs as $lib) {
            if (isset($all_libs[$lib])) {
                $in_footer = ($lib === 'tailwind') ? false : true;
                if (!empty($all_libs[$lib]['js'])) wp_enqueue_script("madenna-lib-{$lib}", $all_libs[$lib]['js'], [], null, $in_footer);
                if (!empty($all_libs[$lib]['css'])) wp_enqueue_style("madenna-lib-{$lib}", $all_libs[$lib]['css'], [], null);
            }
        }
        if (file_exists($css_file) && filesize($css_file) > 0) wp_enqueue_style("madenna-tool-{$tool_id}", $upload_dir['baseurl'] . "/madenna-tools/tool-{$tool_id}.css", [], filemtime($css_file));
        if (file_exists($js_file) && filesize($js_file) > 0) wp_enqueue_script("madenna-tool-{$tool_id}", $upload_dir['baseurl'] . "/madenna-tools/tool-{$tool_id}.js", [], filemtime($js_file), true);
    }

    public function render_tool_shortcode($atts) {
        // اگر آی‌دی داده نشده بود، به صورت هوشمند آی‌دی پست/ابزار جاری را می‌خواند
        $atts = shortcode_atts(['id' => 0], $atts);
        $tool_id = intval($atts['id']);
        if (!$tool_id && is_singular('tools')) {
            $tool_id = get_the_ID();
        }
        if (!$tool_id || get_post_type($tool_id) !== 'tools') return '';
        
        $this->enqueue_tool_files($tool_id);
        $font_style = get_post_meta($tool_id, '_tool_force_site_font', true) === 'yes' ? "<style>#madenna-tool-{$tool_id} * { font-family: inherit !important; }</style>" : '';
                $u = is_user_logged_in() ? wp_get_current_user() : null;
        $uid = get_current_user_id();
        $user_name = $u ? esc_attr($u->display_name) : '';
        $user_gender = $uid ? esc_attr(get_user_meta($uid, 'madenna_gender', true) ?: '') : '';
        $user_birthdate = $uid ? esc_attr(get_user_meta($uid, 'madenna_birthdate', true) ?: '') : '';
        $is_logged_in = is_user_logged_in() ? 'true' : 'false';

        return sprintf(
            '<div class="madenna-tool-wrapper" id="madenna-tool-%d" data-is-logged-in="%s" data-user-id="%d" data-user-name="%s" data-user-gender="%s" data-user-birthdate="%s">%s%s</div>',
            $tool_id,
            $is_logged_in,
            $uid,
            $user_name,
            $user_gender,
            $user_birthdate,
            $font_style,
            get_post_meta($tool_id, '_tool_html', true)
        );
    }

    public function append_tool_content($content) {
        // اگر کاربر از قالب المنتور (Single Tool Template) استفاده می‌کند، از تزریق خودکار جلوگیری می‌کنیم
        if (is_singular('tools') && in_the_loop() && is_main_query()) {
            $has_elementor_library = false;
            if (class_exists('\Elementor\Plugin')) {
                $documents = \Elementor\Plugin::$instance->documents;
                $doc = $documents->get_doc_for_frontend(get_the_ID());
                if ($doc && $doc->is_built_with_elementor()) {
                    // مدیریت از طریق ویجت المنتور
                }
            }

            $tool_id = get_the_ID();
            $html = get_post_meta($tool_id, '_tool_html', true);
            $is_elementor_single_active = false;
        }
        return $content;
    }

    public function handle_live_search() {
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $raw_settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '';
        $settings = $raw_settings !== '' ? json_decode($raw_settings, true) : [];
        if (!is_array($settings) && isset($_POST['settings'])) {
            $settings = json_decode($_POST['settings'], true);
        }
        if (!is_array($settings)) {
            $settings = [];
        }
        $filters_json = isset($_POST['filters']) ? stripslashes($_POST['filters']) : '{}';
        $filters = json_decode($filters_json, true) ?: [];
        $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
        $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
        
        $s = $settings;
        $layout = $s['layout_mode'] ?? 'grid';
        $base_pt = $s['post_type_selector'] ?? 'tools';
        $args_pt = ($base_pt === 'both') ? ['tools', 'articles'] : $base_pt;
        
        if (!empty($filters['post_type']) && $filters['post_type'] !== 'any') {
            $args_pt = $filters['post_type'] === 'tools' ? 'tools' : 'articles';
        }

        $args = [
            'post_type' => $args_pt,
            'post_status' => 'publish',
            'posts_per_page' => isset($s['posts_count']) && $s['posts_count'] != '' ? intval($s['posts_count']) : 6,
            'paged' => $paged
        ];
        
        if (!empty($keyword)) {
            $args['s'] = $keyword;
        }
        
        if (!empty($filters['sort'])) {
            if ($filters['sort'] === 'date') { $args['orderby'] = 'date'; $args['order'] = 'DESC'; } 
            elseif ($filters['sort'] === 'relevance') { $args['orderby'] = 'relevance'; }
        }
        
        $tax_queries = [];
        if ($term_id > 0) $tax_queries[] = ['taxonomy' => 'madenna_cat', 'field' => 'term_id', 'terms' => $term_id, 'include_children' => true];
        if (!empty($filters['categories']) && is_array($filters['categories'])) $tax_queries[] = ['taxonomy' => 'madenna_cat', 'field' => 'term_id', 'terms' => array_map('intval', $filters['categories']), 'include_children' => true];
        
        if (!empty($filters['tool_tags']) && is_array($filters['tool_tags'])) {
            $tax_queries[] = ['taxonomy' => 'madenna_tool_tag', 'field' => 'term_id', 'terms' => array_map('intval', $filters['tool_tags'])];
        } elseif (!empty($s['selected_tool_tags']) && is_array($s['selected_tool_tags']) && ($s['query_source'] ?? 'latest') !== 'manual') {
            $tax_queries[] = ['taxonomy' => 'madenna_tool_tag', 'field' => 'term_id', 'terms' => array_map('intval', $s['selected_tool_tags'])];
        }
        
        if (count($tax_queries) > 1) { $tax_queries['relation'] = 'AND'; $args['tax_query'] = $tax_queries; } 
        elseif (count($tax_queries) === 1) { $args['tax_query'] = $tax_queries; }

        $meta_queries = [];
        
        if (!empty($filters['featured']) && $filters['featured'] === 'yes') {
            $meta_queries[] = ['key' => '_madenna_is_featured', 'value' => 'yes', 'compare' => '='];
        }

        if (!empty($filters['time']) && is_array($filters['time'])) {
            $time_queries = ['relation' => 'OR'];
            foreach ($filters['time'] as $t) {
                if ($t === 'fast') $time_queries[] = ['key' => '_article_reading_time', 'value' => 5, 'compare' => '<=', 'type' => 'NUMERIC'];
                if ($t === 'medium') $time_queries[] = ['key' => '_article_reading_time', 'value' => [5, 10], 'compare' => 'BETWEEN', 'type' => 'NUMERIC'];
                if ($t === 'deep') $time_queries[] = ['key' => '_article_reading_time', 'value' => 10, 'compare' => '>', 'type' => 'NUMERIC'];
            }
            if (count($time_queries) > 1) $meta_queries[] = $time_queries;
        }
        
        if (count($meta_queries) > 1) {
            $meta_queries['relation'] = 'AND';
            $args['meta_query'] = $meta_queries;
        } elseif (count($meta_queries) === 1) {
            $args['meta_query'] = $meta_queries;
        }
        
        $query = new \WP_Query($args);

        if ( $base_pt === 'both' && ($s['alternate_pattern'] ?? 'no') === 'yes' && (!isset($filters['post_type']) || $filters['post_type'] === 'any') && empty($keyword) ) {
            $p_count = $args['posts_per_page'];
            if($p_count <= 0) $p_count = 6;
            
            $q1 = new \WP_Query(array_merge($args, ['post_type'=>'tools', 'posts_per_page'=>ceil($p_count/2)]));
            $q2 = new \WP_Query(array_merge($args, ['post_type'=>'articles', 'posts_per_page'=>floor($p_count/2)]));
            
            $merged = [];
            $len = max(count($q1->posts), count($q2->posts));
            for($i=0; $i<$len; $i++) {
                if(isset($q2->posts[$i])) $merged[] = $q2->posts[$i];
                if(isset($q1->posts[$i])) $merged[] = $q1->posts[$i];
            }
            $query->posts = $merged;
            $query->post_count = count($merged);
            $query->max_num_pages = max($q1->max_num_pages, $q2->max_num_pages);
        }
        
        if (!$query->have_posts()) {
            echo "<div style='width:100%; text-align:center; padding:40px 20px; color:#64748b; grid-column: 1 / -1; font-family:sans-serif; background:#f8fafc; border-radius:12px; border:1px dashed #cbd5e1;'>متاسفانه هیچ نتیجه‌ای یافت نشد. لطفاً فیلترها را تغییر دهید.</div>";
            wp_die();
        }
        
        while ($query->have_posts()) { 
            $query->the_post(); 
            $id = get_the_ID();
            $actual_pt = get_post_type($id);
            $terms = wp_get_post_terms($id, 'madenna_cat');
            
// استخراج هوشمند زیردسته و رنگ‌های اختصاصی
            $primary_cat = class_exists('Madenna_Helpers') ? Madenna_Helpers::get_primary_category($id) : null;
            
            if ($actual_pt === 'tools') {
                $icn = get_post_meta($id, '_tool_icon_class', true);
                $svg = get_post_meta($id, '_tool_icon_url', true);
                $sub = get_post_meta($id, '_tool_subtitle', true);
                $pri = get_post_meta($id, '_tool_color_primary', true) ?: '#2563eb';
                $acc = get_post_meta($id, '_tool_color_accent', true) ?: '#eff6ff';
                $reading = '';
            } else {
                $icn = get_post_meta($id, '_article_icon_class', true);
                $svg = get_post_meta($id, '_article_svg_url', true);
                $sub = get_post_meta($id, '_article_subtitle', true);
                $reading = get_post_meta($id, '_article_reading_time', true);
                
                $pri = '#2563eb'; $acc = '#eff6ff';
                if ($primary_cat) {
                    $pri = get_term_meta($primary_cat->term_id, 'cat_color_primary', true) ?: $pri;
                    $acc = get_term_meta($primary_cat->term_id, 'cat_color_accent', true) ?: $acc;
                }
            }
            
            $icon_style = "display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 0.3s;";
            $svg_style  = "display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 0.3s;";
            
            if (($s['use_term_icon_color'] ?? 'yes') === 'yes') {
                $icon_style .= " color:{$pri} !important; background-color:{$acc} !important;";
                $svg_style  .= " color:{$pri} !important; background-color:{$acc} !important;";
            }
            
            $item_cls = ($layout === 'carousel') ? 'mad-tool-item mad-carousel-slide' : 'mad-tool-item';
            $snap_style = ($layout === 'carousel') ? 'scroll-snap-align:start;' : '';
            
            echo "<a href='" . esc_url(get_permalink()) . "' class='{$item_cls}' style='{$snap_style} display:flex; overflow:hidden; text-decoration:none; color:inherit; transition:all 0.3s; position:relative;'>";
            
            // رندر لیبل‌های روی کارت (لیبل دسته‌بندی و لیبل برچسب برای ابزارها و مقالات)
// رندر لیبل‌های روی کارت
            if (($s['show_labels'] ?? 'yes') === 'yes') {
                $cat_label_text = '';
                if (($s['show_cat_label'] ?? 'yes') === 'yes' && $primary_cat) {
                    $cat_label_text = $primary_cat->name; // استفاده از زیردسته واقعی
                }

                $tag_label_text = '';
                if (($s['show_tag_label'] ?? 'yes') === 'yes') {
                    $post_tags = wp_get_post_terms($id, 'madenna_tool_tag');
                    if ((empty($post_tags) || is_wp_error($post_tags)) && $actual_pt === 'articles') {
                        $post_tags = get_the_terms($id, 'post_tag');
                    }
                    if (!empty($post_tags) && !is_wp_error($post_tags)) {
                        $tag_label_text = $post_tags[0]->name;
                    }
                }

                if ($cat_label_text !== '' || $tag_label_text !== '') {
                    echo "<div class='mad-card-labels' style='position:absolute; top:12px; left:12px; display:flex; flex-wrap:wrap; gap:6px; z-index:2;'>";
                    if ($cat_label_text !== '') {
                        echo "<span class='mad-tool-badge mad-cat-label' style='background:{$pri}; color:{$acc}; font-size:10px; font-weight:800; padding:2px 8px; line-height:1.4; border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.08);'>" . esc_html($cat_label_text) . "</span>";
                    }
                    if ($tag_label_text !== '') {
                        echo "<span class='mad-tool-badge mad-tag-label' style='background:{$acc}; color:{$pri}; border:1px solid {$pri}; font-size:10px; font-weight:800; padding:2px 8px; line-height:1.4; border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.08);'>" . esc_html($tag_label_text) . "</span>";
                    }
                    echo "</div>";
                }
            }

            if (($s['show_image'] ?? 'yes') === 'yes' && has_post_thumbnail()) {
                echo "<div class='mad-tool-img-wrap' style='flex-shrink:0; overflow:hidden;'>".get_the_post_thumbnail($id, 'medium', ['style'=>'width:100%;height:100%;object-fit:cover;'])."</div>";
            }
            
            echo "<div class='mad-tool-content-wrap' style='display:flex; flex-direction:column; flex:1;'>";
            echo "<div class='mad-tool-content-head' style='display:flex; flex-wrap:wrap; width:100%; box-sizing:border-box;'>";
            
            if ($actual_pt === 'articles' && ($s['show_reading_time'] ?? 'yes') === 'yes' && !empty($reading)) {
                echo "<span class='mad-tool-reading' style='display:block; width:100%; flex:0 0 auto; font-size:11px; color:#64748b; margin-bottom:8px; order:0;'>⏱️ زمان مطالعه: {$reading} دقیقه</span>";
            }

            if (($s['show_svg'] ?? 'yes') === 'yes' && !empty($svg)) {
                echo "<div class='mad-tool-svg' style='{$svg_style}'>";
                echo "<div class='mad-svg-mask' style=\"display:block; width:100%; height:100%; -webkit-mask-image: url('".esc_url($svg)."'); mask-image: url('".esc_url($svg)."'); background-color: currentColor; -webkit-mask-size: contain; mask-size: contain; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center;\"></div>";
                echo "</div>";
            }
            
            if (($s['show_icon'] ?? 'yes') === 'yes' && !empty($icn)) {
                echo "<div class='mad-tool-icon' style='{$icon_style}'>";
                if (class_exists('Madenna_Helpers')) { echo \Madenna_Helpers::render_icon_element($icn); }
                echo "</div>";
            }
            
            echo "<div class='mad-tool-head-text' style='flex:1 1 auto; min-width:0; display:flex; flex-direction:column; justify-content:center;'>";
            
            $title = get_the_title($id);
            if ($title === '' || $title === null) {
                $title = get_post_field('post_title', $id);
            }
            $show_title_val = $s['show_title'] ?? 'yes';
            if (in_array($show_title_val, ['yes', 'true', '1', 1, true], true) && !empty($title)) {
                echo "<h3 class='mad-tool-title' style='margin:0; font-weight:800;'>".esc_html($title)."</h3>";
            }
            
            if (($s['show_subtitle'] ?? 'yes') === 'yes' && !empty($sub)) {
                $char_limit = $s['sub_chars'] ?? '';
                if (!empty($char_limit) && mb_strlen($sub) > $char_limit) {
                    $sub = mb_substr($sub, 0, $char_limit) . '...';
                }
                echo "<div class='mad-tool-subtitle'>".esc_html($sub)."</div>";
            }
            
            echo "</div>"; 
            
            echo "</div>"; 
            
            $exc = get_the_excerpt();
            if (($s['show_excerpt'] ?? 'yes') === 'yes' && !empty($exc)) {
                echo "<div class='mad-tool-excerpt'>".esc_html($exc)."</div>";
            }
            
            $btn_text = $s['button_text'] ?? 'مشاهده ←';
            if ($actual_pt === 'articles' && $base_pt === 'both') {
                $btn_text = $s['button_text_article'] ?? 'مطالعه مقاله ←';
            }

            if (($s['show_button'] ?? 'yes') === 'yes' && !empty($btn_text)) {
                echo "<div class='mad-tool-btn-wrap' style='display:flex; width:100%;'>";
                echo "<div class='mad-tool-btn' style='display:inline-flex; align-items:center; justify-content:center; transition:all 0.3s; text-align:center;'>".esc_html($btn_text)."</div>";
                echo "</div>";
            }
            
            echo "</div></a>";
        }
        
        $max_pages = $query->max_num_pages;
        if ($layout === 'grid' && ($s['show_pagination'] ?? 'no') === 'yes' && $max_pages > 1) {
            $current_page = $paged;
            echo "<div class='madenna-pagination' style='display:flex; justify-content:center; align-items:center; flex-wrap:wrap; gap:8px; margin-top:30px; grid-column:1/-1; width:100%;'>";
            
            $btn_style = "height:38px; border-radius:8px; border:1px solid; display:flex; align-items:center; justify-content:center; cursor:pointer; font-weight:bold; font-family:inherit; transition:0.3s; padding:0 14px;";
            
            if ($current_page > 1) {
                $prev = $current_page - 1;
                echo "<button type='button' class='mad-page-btn' data-page='{$prev}' style='{$btn_style} background:#fff; color:#0c5c4e; border-color:#0c5c4e;'>قبلی &raquo;</button>";
            }
            
            for ($i = 1; $i <= $max_pages; $i++) {
                $active_style = ($i == $current_page) ? "background:#0c5c4e; color:#fff; border-color:#0c5c4e;" : "background:#fff; color:#475569; border-color:#cbd5e1;";
                echo "<button type='button' class='mad-page-btn' data-page='{$i}' style='width:38px; {$btn_style} {$active_style} padding:0;'>{$i}</button>";
            }
            
            if ($current_page < $max_pages) {
                $nxt = $current_page + 1;
                echo "<button type='button' class='mad-page-btn' data-page='{$nxt}' style='{$btn_style} background:#fff; color:#0c5c4e; border-color:#0c5c4e;'>&laquo; بعدی</button>";
            }
            echo "</div>";
        }
        
        wp_reset_postdata();
        wp_die();
    }

    public function track_post_views() {
        if (is_singular(['articles', 'tools'])) {
            $post_id = get_the_ID();
            $cookie_name = 'madenna_viewed_' . $post_id;
            
            if (!isset($_COOKIE[$cookie_name])) {
                $views = (int)get_post_meta($post_id, '_madenna_views_count', true);
                update_post_meta($post_id, '_madenna_views_count', $views + 1);
                setcookie($cookie_name, '1', time() + 3600 * 24, COOKIEPATH, COOKIE_DOMAIN);
            }
        }
    }


public function fix_madenna_breadcrumbs($links) {
        if (!is_singular(['articles', 'tools'])) {
            return $links;
        }

        $post_id = get_the_ID();
        $primary_term = Madenna_Helpers::get_primary_category($post_id);
        if (!$primary_term) {
            return $links;
        }

        // حفظ لینک خانه (عنصر اول) و لینک خود نوشته (عنصر آخر)
        $home_link = $links[0] ?? ['text' => 'خانه', 'url' => home_url('/')];
        $current_post_link = end($links);

        $new_crumbs = [$home_link];

        // اگر زیردسته است، ابتدا دسته مادر را در مسیر قرار می‌دهد
        if ($primary_term->parent != 0) {
            $parent_term = get_term($primary_term->parent, 'madenna_cat');
            if ($parent_term && !is_wp_error($parent_term)) {
                $new_crumbs[] = [
                    'text' => $parent_term->name,
                    'url'  => home_url('/' . $parent_term->slug . '/'),
                ];
            }
        }

        // اضافه کردن زیردسته به مسیر
        $new_crumbs[] = [
            'text' => $primary_term->name,
            'url'  => home_url('/cate/' . $primary_term->slug . '/'),
        ];

        // اضافه کردن نام خود نوشته جاری در انتهای مسیر
        $new_crumbs[] = $current_post_link;

        return $new_crumbs;
    }

    
}