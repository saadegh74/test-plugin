<?php
/**
 * مدیریت پنل وردپرس (متاباکس‌ها، دسته‌بندی‌ها، استایل‌های پنل)
 * Location: /inc/class-madenna-admin.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Admin {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_all_metaboxes']);
        add_action('save_post_tools', [$this, 'save_tool_meta']);
        add_action('save_post_articles', [$this, 'save_article_meta']);
        
        add_action('madenna_cat_add_form_fields', [$this, 'add_category_meta_fields']);
        add_action('madenna_cat_edit_form_fields', [$this, 'edit_category_meta_fields'], 10, 2);
        add_action('created_madenna_cat', [$this, 'save_category_meta']);
        add_action('edited_madenna_cat', [$this, 'save_category_meta']);
        
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_footer', [$this, 'render_global_icon_picker_modal']);
        
        // قلاب‌های مربوط به بازدیدها و برگزیده‌ها
        foreach (['articles', 'tools'] as $pt) {
            add_filter("manage_{$pt}_posts_columns", [$this, 'add_custom_columns']);
            add_action("manage_{$pt}_posts_custom_column", [$this, 'render_custom_columns'], 10, 2);
            add_filter("manage_edit-{$pt}_sortable_columns", [$this, 'sortable_custom_columns']);
        }
        add_action('pre_get_posts', [$this, 'custom_orderby_views']);
        add_action('manage_posts_extra_tablenav', [$this, 'add_auto_featured_button']);
        add_action('wp_ajax_madenna_toggle_featured', [$this, 'ajax_toggle_featured']);
        add_action('wp_ajax_madenna_auto_featured', [$this, 'ajax_auto_featured']);
        
        // ثبت تکسونومی برچسب‌های ابزار با آدرس tools-tag
        add_action('init', [$this, 'register_tool_tags'], 6);
        add_action('rest_api_init', [$this, 'register_sync_api_routes']);
    }

    public function register_tool_tags() {
        register_taxonomy('madenna_tool_tag', ['tools'], [
            'hierarchical'       => false,
            'public'             => true,
            'publicly_queryable' => true,
            'labels'             => [
                'name'          => 'برچسب‌های ابزار',
                'singular_name' => 'برچسب ابزار',
                'menu_name'     => 'برچسب‌های ابزار',
                'search_items'  => 'جستجوی برچسب‌ها',
                'all_items'     => 'همه برچسب‌ها',
                'edit_item'     => 'ویرایش برچسب',
                'update_item'   => 'به‌روزرسانی برچسب',
                'add_new_item'  => 'افزودن برچسب جدید',
                'new_item_name' => 'نام برچسب جدید',
            ],
            'show_ui'            => true,
            'show_admin_column'  => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'tools-tag', 'with_front' => false],
        ]);

        add_rewrite_rule('^tools-tag/([^/]+)/?$', 'index.php?madenna_tool_tag=$matches[1]', 'top');
        add_rewrite_rule('^tools-tag/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?madenna_tool_tag=$matches[1]&paged=$matches[2]', 'top');

        // فلاش خودکار یک‌باره پیوندهای یکتا برای رفع خطای 404
        if (get_option('madenna_tools_tag_flush_v3') !== 'done') {
            flush_rewrite_rules(false);
            update_option('madenna_tools_tag_flush_v3', 'done');
        }
    }

    public function register_all_metaboxes() {
        add_meta_box('madenna_tool_builder', 'محیط توسعه و کدنویسی ابزار (HTML / CSS / JS) + پیش‌نمایش زنده', [$this, 'render_tool_editor_metabox'], 'tools', 'normal', 'high');
        add_meta_box('madenna_tool_details', 'مشخصات، آیکون، رنگ‌ها، پیش‌نیازها و سوالات متداول ابزار', [$this, 'render_tool_details_metabox'], 'tools', 'normal', 'high');
        add_meta_box('madenna_tool_related_articles', '📚 مقالات متصل به این ابزار', [$this, 'render_tool_related_articles_metabox'], 'tools', 'side', 'default');
        
        add_meta_box('madenna_article_meta_details', 'اطلاعات تکمیلی مقاله (صوت، زمان مطالعه هوشمند، آیکون و FAQ)', [$this, 'render_article_details_metabox'], 'articles', 'normal', 'high');
        add_meta_box('madenna_article_related_tools', '🛠 ابزارهای مرتبط با این مقاله', [$this, 'render_article_related_tools_metabox'], 'articles', 'side', 'default');
    }

    public function render_article_details_metabox($post) {
        wp_nonce_field('madenna_save_article_data', 'madenna_article_nonce');
        $subtitle = get_post_meta($post->ID, '_article_subtitle', true);
        $audio = get_post_meta($post->ID, '_article_audio_url', true);
        $reading = get_post_meta($post->ID, '_article_reading_time', true);
        $icon = get_post_meta($post->ID, '_article_icon_class', true);
        $svg = get_post_meta($post->ID, '_article_svg_url', true);
        
        $faqs = get_post_meta($post->ID, '_article_faqs', true);
        $faqs = is_array($faqs) ? $faqs : [];
        
        $content = isset($post->post_content) ? strip_tags($post->post_content) : '';
        $split = preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY);
        $words = is_array($split) ? count($split) : 0;
        $est = max(1, ceil($words / 200));
        ?>
        <div style="padding:10px 0;">
            <p><label><strong>زیرعنوان مقاله:</strong></label><input type="text" name="article_subtitle" value="<?php echo esc_attr($subtitle); ?>" style="width:100%; margin-top:4px;"></p>
            <p><label><strong>⏱️ زمان مطالعه (دقیقه):</strong></label><br>
               <input type="number" name="article_reading_time" value="<?php echo esc_attr($reading); ?>" style="width:100px; margin-top:4px;" placeholder="<?php echo esc_attr($est); ?>"> 
               <span style="font-size:11px; color:#64748b;">(تخمین فعلی سیستم: <?php echo $est; ?> دقیقه)</span>
            </p>
            
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:15px; margin:15px 0;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">🎙 فایل صوتی پادکست:</label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="text" name="article_audio_url" id="article_audio_url" value="<?php echo esc_attr($audio); ?>" style="flex:1; direction:ltr;">
                    <button type="button" class="button madenna-media-btn" data-target="article_audio_url" data-preview="audio_preview">انتخاب از رسانه</button>
                </div>
                <div id="audio_preview"><?php if($audio) echo '<audio controls style="width:100%; height:36px; margin-top:8px;"><source src="'.esc_url($audio).'"></audio>'; ?></div>
            </div>
            
            <?php Madenna_Helpers::render_icon_and_svg_picker('article_icon_class', $icon, 'article_svg_url', $svg, 'آیکون موضوع مقاله'); ?>
            
            <div style="margin-top:20px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">❓ سوالات متداول:</label>
                <div id="madenna-article-faq-list" class="madenna-faq-container">
                    <?php foreach ($faqs as $i => $item): $eid = 'art_faq_' . $i; ?>
                        <div class="faq-item" style="background:#fff; border:1px solid #ccd0d4; padding:15px; margin-bottom:12px; border-radius:8px;">
                            <input type="text" name="article_faqs[<?php echo $i; ?>][q]" value="<?php echo esc_attr($item['q'] ?? ''); ?>" placeholder="پرسش..." style="width:100%; margin-bottom:10px;">
                            <?php wp_editor($item['a'] ?? '', $eid, ['textarea_name' => "article_faqs[{$i}][a]", 'textarea_rows' => 4, 'media_buttons' => false, 'teeny' => true]); ?>
                            <button type="button" class="button-link-delete madenna-remove-faq" data-editor-id="<?php echo esc_attr($eid); ?>" style="color:#b32d2e; margin-top:8px;">حذف سوال</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary madenna-add-faq-btn" data-container="madenna-article-faq-list" data-prefix="article_faqs">+ افزودن پرسش و پاسخ</button>
            </div>
        </div>
        <?php
    }

    public function render_tool_details_metabox($post) {
        $subtitle = get_post_meta($post->ID, '_tool_subtitle', true);
        $icon = get_post_meta($post->ID, '_tool_icon_class', true);
        $svg = get_post_meta($post->ID, '_tool_icon_url', true);
        $primary = get_post_meta($post->ID, '_tool_color_primary', true) ?: '#2563eb';
        $second = get_post_meta($post->ID, '_tool_color_secondary', true) ?: '#3b82f6';
        $accent = get_post_meta($post->ID, '_tool_color_accent', true) ?: '#eff6ff';
        $force = get_post_meta($post->ID, '_tool_force_site_font', true) ?: 'yes';
        
        $libs = get_post_meta($post->ID, '_tool_libraries', true);
        $libs = is_array($libs) ? $libs : [];
        
        $faqs = get_post_meta($post->ID, '_tool_faqs', true);
        $faqs = is_array($faqs) ? $faqs : [];
        
        $all_libs = Madenna_Helpers::get_available_tool_libraries();
        ?>
        <div style="padding:10px 0;">
            <p><label><strong>زیرعنوان ابزار:</strong></label><input type="text" name="tool_subtitle" value="<?php echo esc_attr($subtitle); ?>" style="width:100%; margin-top:4px;"></p>
            <?php Madenna_Helpers::render_icon_and_svg_picker('tool_icon_class', $icon, 'tool_icon_url', $svg, 'آیکون اختصاصی ابزار'); ?>
            
            <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:15px; border-radius:8px; margin:15px 0;">
                <label style="font-weight:600; display:block; margin-bottom:8px;">📦 کتابخانه‌های کمکی (CDN):</label>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <?php foreach ($all_libs as $k => $d): ?>
                        <label><input type="checkbox" name="tool_libraries[]" value="<?php echo esc_attr($k); ?>" <?php checked(in_array($k, $libs)); ?>> <?php echo esc_html($d['name']); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="background:#f0fdf4; border:1px solid #bbf7d0; padding:14px; border-radius:8px; margin:15px 0;">
                <label style="display:flex; align-items:flex-start; gap:10px;"><input type="checkbox" name="tool_force_site_font" value="yes" <?php checked($force, 'yes'); ?>><strong style="color:#166534; font-size:13px;">🔤 ارث‌بری خودکار از فونت اصلی سایت</strong></label>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:15px; border-radius:8px; margin:15px 0; display:flex; gap:20px; flex-wrap:wrap;">
                <div><span style="display:block; font-size:12px; font-weight:600;">رنگ اصلی</span><input type="text" name="tool_color_primary" value="<?php echo esc_attr($primary); ?>" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('tool_color_primary'); ?></div>
                <div><span style="display:block; font-size:12px; font-weight:600;">رنگ ثانویه</span><input type="text" name="tool_color_secondary" value="<?php echo esc_attr($second); ?>" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('tool_color_secondary'); ?></div>
                <div><span style="display:block; font-size:12px; font-weight:600;">رنگ اکسنت</span><input type="text" name="tool_color_accent" value="<?php echo esc_attr($accent); ?>" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('tool_color_accent'); ?></div>
            </div>

            <div style="margin-top:15px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">❓ سوالات متداول ابزار:</label>
                <div id="madenna-tool-faq-list" class="madenna-faq-container">
                    <?php foreach ($faqs as $i => $item): $eid = 'tool_faq_' . $i; ?>
                        <div class="faq-item" style="background:#fff; border:1px solid #ccd0d4; padding:15px; margin-bottom:12px; border-radius:8px;">
                            <input type="text" name="tool_faqs[<?php echo $i; ?>][q]" value="<?php echo esc_attr($item['q'] ?? ''); ?>" placeholder="پرسش..." style="width:100%; margin-bottom:10px;">
                            <?php wp_editor($item['a'] ?? '', $eid, ['textarea_name' => "tool_faqs[{$i}][a]", 'textarea_rows' => 4, 'media_buttons' => false, 'teeny' => true]); ?>
                            <button type="button" class="button-link-delete madenna-remove-faq" data-editor-id="<?php echo esc_attr($eid); ?>" style="color:#b32d2e; margin-top:8px;">حذف سوال</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary madenna-add-faq-btn" data-container="madenna-tool-faq-list" data-prefix="tool_faqs">+ افزودن پرسش و پاسخ</button>
            </div>
        </div>
        <?php
    }

    public function render_tool_editor_metabox($post) {
        wp_nonce_field('madenna_save_tool_code', 'madenna_tool_nonce');
        $html = get_post_meta($post->ID, '_tool_html', true);
        $css = get_post_meta($post->ID, '_tool_css', true);
        $js = get_post_meta($post->ID, '_tool_js', true);
        ?>
        <div style="padding:10px 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="color:#64748b; font-size:13px;">کدهای CSS و JS مینیفای و کش می‌شوند.</span>
                <button type="button" id="madenna-trigger-preview-btn" class="button button-primary">👁️ پیش‌نمایش زنده</button>
            </div>
            <div id="madenna-live-preview-box" style="display:none; background:#fff; border:2px dashed #0284c7; border-radius:10px; padding:15px; margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><strong style="font-size:14px;">پیش‌نمایش:</strong><button type="button" id="madenna-close-preview-btn" class="button button-secondary">بستن پیش‌نمایش</button></div>
                <iframe id="madenna-preview-iframe" style="width:100%; min-height:380px; border:1px solid #cbd5e1; border-radius:8px;"></iframe>
            </div>
            <p><label><strong>HTML:</strong></label><textarea id="tool_html" name="tool_html" rows="8" style="width:100%; direction:ltr;"><?php echo esc_textarea($html); ?></textarea></p>
            <p><label><strong>CSS:</strong></label><textarea id="tool_css" name="tool_css" rows="6" style="width:100%; direction:ltr;"><?php echo esc_textarea($css); ?></textarea></p>
            <p><label><strong>JS:</strong></label><textarea id="tool_js" name="tool_js" rows="6" style="width:100%; direction:ltr;"><?php echo esc_textarea($js); ?></textarea></p>
            <div style="margin-top:15px; padding:12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; color:#166534;"><strong>شورت‌کد ابزار:</strong> <code style="background:#fff; padding:3px 8px; border:1px solid #86efac;">[madenna_tool id="<?php echo $post->ID; ?>"]</code></div>
        </div>
        <?php
    }

    public function render_tool_related_articles_metabox($post) { $this->render_bidirectional_metabox($post, '_related_articles', 'articles', 'select-related-article', 'madenna-related-articles-list', 'related_articles[]'); }
    public function render_article_related_tools_metabox($post) { $this->render_bidirectional_metabox($post, '_related_tools', 'tools', 'select-related-tool', 'madenna-related-tools-list', 'related_tools[]'); }

    private function render_bidirectional_metabox($post, $meta_key, $post_type, $select_id, $list_id, $input_name) {
        $related = get_post_meta($post->ID, $meta_key, true);
        $related = is_array($related) ? $related : [];
        $all = get_posts(['post_type' => $post_type, 'posts_per_page' => 200, 'post_status' => 'publish']);
        ?>
        <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:10px;">
            <select id="<?php echo esc_attr($select_id); ?>" style="width:100%;"><option value="">-- انتخاب --</option>
                <?php foreach ($all as $item): ?><option value="<?php echo $item->ID; ?>"><?php echo esc_html($item->post_title); ?></option><?php endforeach; ?>
            </select>
            <button type="button" class="button button-secondary madenna-add-featured-btn" data-select="<?php echo esc_attr($select_id); ?>" data-list="<?php echo esc_attr($list_id); ?>" data-input="<?php echo esc_attr(str_replace('[]','', $input_name)); ?>">+ افزودن اتصال</button>
        </div>
        <ul id="<?php echo esc_attr($list_id); ?>" class="madenna-sortable-list" style="margin:0; padding:0; list-style:none;">
            <?php foreach ($related as $id): $p = get_post($id); if (!$p) continue; ?>
                <li style="display:flex; justify-content:space-between; background:#f8fafc; padding:6px 10px; margin-bottom:5px; border:1px solid #cbd5e1; border-radius:6px; cursor:move;">
                    <span>⋮⋮ <?php echo esc_html($p->post_title); ?></span><input type="hidden" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($id); ?>"><button type="button" class="button-link-delete madenna-remove-item" style="color:#ef4444;">حذف</button>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    public function add_category_meta_fields() {
        ?>
        <div class="form-field"><label>زیرعنوان دسته</label><input type="text" name="cat_subtitle"></div>
        <div class="form-field"><label>چکیده کوتاه</label><textarea name="cat_excerpt" rows="3"></textarea></div>
        <div class="form-field"><label>توضیحات کامل</label><?php wp_editor('', 'cat_rich_description', ['textarea_name' => 'cat_rich_description', 'textarea_rows' => 5, 'teeny' => false]); ?></div>
        <?php Madenna_Helpers::render_icon_and_svg_picker('cat_icon_class', '', 'cat_icon_url', '', 'آیکون دسته‌بندی'); ?>
        <div class="form-field"><label>تصویر شاخص</label><input type="hidden" name="cat_image" id="cat_image"><div id="cat_image_preview"></div><button type="button" class="button madenna-media-btn" data-target="cat_image" data-preview="cat_image_preview">انتخاب تصویر</button></div>
        
        <div class="form-field" style="background:#f9f9f9; padding:15px; border-radius:8px;">
            <label style="font-weight:600; margin-bottom:10px; display:block;">🎨 رنگ‌های اختصاصی دسته</label>
            <div style="display:flex; gap:20px;">
                <div><label>اصلی</label><input type="text" name="cat_color_primary" value="#2563eb" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_color_primary'); ?></div>
                <div><label>ثانویه</label><input type="text" name="cat_color_secondary" value="#3b82f6" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_color_secondary'); ?></div>
                <div><label>اکسنت</label><input type="text" name="cat_color_accent" value="#eff6ff" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_color_accent'); ?></div>
            </div>
            <div style="display:flex; gap:20px; margin-top:15px; padding-top:15px; border-top:1px solid #ddd;">
                <div><label>رنگ آیکون</label><input type="text" name="cat_icon_color" value="#2563eb" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_icon_color'); ?></div>
                <div><label>پس‌زمینه آیکون</label><input type="text" name="cat_icon_bg" value="#eff6ff" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_icon_bg'); ?></div>
            </div>
        </div>

        <div class="form-field" style="margin-top:20px;"><label style="font-weight:600;">❓ سوالات متداول</label><div id="madenna-faq-list" class="madenna-faq-container"></div><button type="button" class="button button-secondary madenna-add-faq-btn" data-container="madenna-faq-list" data-prefix="cat_faqs">+ افزودن پرسش و پاسخ</button></div>
        <?php
    }

    public function edit_category_meta_fields($term) {
        $tid = $term->term_id;
        $all_articles = get_posts(['post_type' => 'articles', 'posts_per_page' => 200, 'post_status' => 'publish']);
        $all_tools = get_posts(['post_type' => 'tools', 'posts_per_page' => 200, 'post_status' => 'publish']);
        
        $feat_arts = get_term_meta($tid, 'cat_featured_articles', true);
        $feat_arts = is_array($feat_arts) ? $feat_arts : [];
        
        $feat_tools = get_term_meta($tid, 'cat_featured_tools', true);
        $feat_tools = is_array($feat_tools) ? $feat_tools : [];
        
        $faqs = get_term_meta($tid, 'cat_faqs', true);
        $faqs = is_array($faqs) ? $faqs : [];
        ?>
        <tr class="form-field"><th scope="row"><label>زیرعنوان دسته</label></th><td><input type="text" name="cat_subtitle" value="<?php echo esc_attr(get_term_meta($tid, 'cat_subtitle', true)); ?>"></td></tr>
        <tr class="form-field"><th scope="row"><label>چکیده</label></th><td><textarea name="cat_excerpt" rows="3"><?php echo esc_textarea(get_term_meta($tid, 'cat_excerpt', true)); ?></textarea></td></tr>
        <tr class="form-field"><th scope="row"><label>توضیحات جامع</label></th><td><?php wp_editor(wp_specialchars_decode(term_description($tid, 'madenna_cat')), 'cat_rich_description', ['textarea_name' => 'cat_rich_description', 'textarea_rows' => 8]); ?></td></tr>
        <tr class="form-field"><th scope="row"><label>آیکون</label></th><td><?php Madenna_Helpers::render_icon_and_svg_picker('cat_icon_class', get_term_meta($tid, 'cat_icon_class', true), 'cat_icon_url', get_term_meta($tid, 'cat_icon_url', true)); ?></td></tr>
        <tr class="form-field"><th scope="row"><label>تصویر شاخص</label></th><td><input type="hidden" name="cat_image" id="cat_image" value="<?php echo esc_attr(get_term_meta($tid, 'cat_image', true)); ?>"><div id="cat_image_preview"><?php if ($img = get_term_meta($tid, 'cat_image', true)): ?><img src="<?php echo esc_url($img); ?>" style="max-width:120px; border-radius:6px;"><?php endif; ?></div><button type="button" class="button madenna-media-btn" data-target="cat_image" data-preview="cat_image_preview">انتخاب تصویر</button></td></tr>
        <tr class="form-field"><th scope="row"><label>پالت رنگ‌ها و آیکون</label></th><td>
            <div style="display:flex; flex-direction:column; gap:15px; background:#f8fafc; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
                <div style="display:flex; gap:20px;">
                    <div><span>اصلی</span><input type="text" name="cat_color_primary" value="<?php echo esc_attr(get_term_meta($tid, 'cat_color_primary', true) ?: '#2563eb'); ?>" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_color_primary'); ?></div>
                    <div><span>ثانویه</span><input type="text" name="cat_color_secondary" value="<?php echo esc_attr(get_term_meta($tid, 'cat_color_secondary', true) ?: '#3b82f6'); ?>" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_color_secondary'); ?></div>
                    <div><span>اکسنت</span><input type="text" name="cat_color_accent" value="<?php echo esc_attr(get_term_meta($tid, 'cat_color_accent', true) ?: '#eff6ff'); ?>" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_color_accent'); ?></div>
                </div>
                <div style="display:flex; gap:20px; padding-top:15px; border-top:1px dashed #cbd5e1;">
                    <div><span>رنگ آیکون</span><input type="text" name="cat_icon_color" value="<?php echo esc_attr(get_term_meta($tid, 'cat_icon_color', true) ?: '#2563eb'); ?>" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_icon_color'); ?></div>
                    <div><span>پس‌زمینه آیکون</span><input type="text" name="cat_icon_bg" value="<?php echo esc_attr(get_term_meta($tid, 'cat_icon_bg', true) ?: '#eff6ff'); ?>" class="madenna-color-picker"><?php Madenna_Helpers::render_elementor_palette_swatches('cat_icon_bg'); ?></div>
                </div>
            </div>
        </td></tr>
        <tr class="form-field"><th scope="row"><label>⭐ مقالات منتخب</label></th><td>
            <div style="background:#fff; border:1px solid #e2e8f0; padding:15px; border-radius:8px;">
                <div style="display:flex; gap:10px; margin-bottom:12px;"><select id="sel-feat-art" style="flex:1;"><option value="">-- انتخاب --</option><?php foreach ($all_articles as $a): ?><option value="<?php echo $a->ID; ?>"><?php echo esc_html($a->post_title); ?></option><?php endforeach; ?></select><button type="button" class="button button-secondary madenna-add-featured-btn" data-select="sel-feat-art" data-list="madenna-feat-arts" data-input="cat_featured_articles">+ افزودن</button></div>
                <ul id="madenna-feat-arts" class="madenna-sortable-list" style="margin:0; padding:0; list-style:none;">
                    <?php foreach ($feat_arts as $id): $p = get_post($id); if ($p): ?>
                        <li style="display:flex; justify-content:space-between; background:#f8fafc; padding:8px 12px; margin-bottom:6px; border:1px solid #cbd5e1; border-radius:6px; cursor:move;"><span>⋮⋮ <?php echo esc_html($p->post_title); ?></span><input type="hidden" name="cat_featured_articles[]" value="<?php echo esc_attr($id); ?>"><button type="button" class="button-link-delete madenna-remove-item" style="color:#ef4444;">حذف</button></li>
                    <?php endif; endforeach; ?>
                </ul>
            </div>
        </td></tr>
        <tr class="form-field"><th scope="row"><label>🛠️ ابزارهای منتخب</label></th><td>
            <div style="background:#fff; border:1px solid #e2e8f0; padding:15px; border-radius:8px;">
                <div style="display:flex; gap:10px; margin-bottom:12px;"><select id="sel-feat-tool" style="flex:1;"><option value="">-- انتخاب --</option><?php foreach ($all_tools as $t): ?><option value="<?php echo $t->ID; ?>"><?php echo esc_html($t->post_title); ?></option><?php endforeach; ?></select><button type="button" class="button button-secondary madenna-add-featured-btn" data-select="sel-feat-tool" data-list="madenna-feat-tools" data-input="cat_featured_tools">+ افزودن</button></div>
                <ul id="madenna-feat-tools" class="madenna-sortable-list" style="margin:0; padding:0; list-style:none;">
                    <?php foreach ($feat_tools as $id): $p = get_post($id); if ($p): ?>
                        <li style="display:flex; justify-content:space-between; background:#f8fafc; padding:8px 12px; margin-bottom:6px; border:1px solid #cbd5e1; border-radius:6px; cursor:move;"><span>⋮⋮ <?php echo esc_html($p->post_title); ?></span><input type="hidden" name="cat_featured_tools[]" value="<?php echo esc_attr($id); ?>"><button type="button" class="button-link-delete madenna-remove-item" style="color:#ef4444;">حذف</button></li>
                    <?php endif; endforeach; ?>
                </ul>
            </div>
        </td></tr>
        <tr class="form-field"><th scope="row"><label>سوالات متداول (FAQ)</label></th><td>
            <div id="madenna-faq-list" class="madenna-faq-container" style="max-width:700px;">
                <?php foreach ($faqs as $i => $item): $eid = 'cat_faq_' . $i; ?>
                    <div class="faq-item" style="background:#fff; border:1px solid #ccd0d4; padding:12px; margin-bottom:10px; border-radius:6px;">
                        <input type="text" name="cat_faqs[<?php echo $i; ?>][q]" value="<?php echo esc_attr($item['q'] ?? ''); ?>" placeholder="پرسش..." style="width:100%; margin-bottom:8px;">
                        <?php wp_editor($item['a'] ?? '', $eid, ['textarea_name' => "cat_faqs[{$i}][a]", 'textarea_rows' => 3, 'media_buttons' => false, 'teeny' => true]); ?>
                        <button type="button" class="button-link-delete madenna-remove-faq" data-editor-id="<?php echo esc_attr($eid); ?>" style="color:#b32d2e; margin-top:8px;">حذف سوال</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button button-secondary madenna-add-faq-btn" data-container="madenna-faq-list" data-prefix="cat_faqs">+ افزودن پرسش و پاسخ جدید</button>
        </td></tr>
        <?php
    }

    public function save_category_meta($term_id) {
        $strings = ['cat_subtitle', 'cat_icon_class'];
        foreach ($strings as $f) if (isset($_POST[$f])) update_term_meta($term_id, $f, sanitize_text_field($_POST[$f]));
        if (isset($_POST['cat_excerpt'])) update_term_meta($term_id, 'cat_excerpt', sanitize_textarea_field($_POST['cat_excerpt']));
        
        if (isset($_POST['cat_rich_description'])) {
            remove_action('edited_madenna_cat', [$this, 'save_category_meta']);
            wp_update_term($term_id, 'madenna_cat', ['description' => wp_kses_post($_POST['cat_rich_description'])]);
            add_action('edited_madenna_cat', [$this, 'save_category_meta']);
        }
        
        $urls = ['cat_icon_url', 'cat_image'];
        foreach ($urls as $f) if (isset($_POST[$f])) update_term_meta($term_id, $f, esc_url_raw($_POST[$f]));
        
        $colors = ['cat_color_primary', 'cat_color_secondary', 'cat_color_accent', 'cat_icon_color', 'cat_icon_bg'];
        foreach ($colors as $f) if (isset($_POST[$f])) update_term_meta($term_id, $f, sanitize_hex_color($_POST[$f]));
        
        if (isset($_POST['cat_featured_articles']) && is_array($_POST['cat_featured_articles'])) update_term_meta($term_id, 'cat_featured_articles', array_unique(array_map('intval', $_POST['cat_featured_articles']))); else delete_term_meta($term_id, 'cat_featured_articles');
        if (isset($_POST['cat_featured_tools']) && is_array($_POST['cat_featured_tools'])) update_term_meta($term_id, 'cat_featured_tools', array_unique(array_map('intval', $_POST['cat_featured_tools']))); else delete_term_meta($term_id, 'cat_featured_tools');
        
        if (isset($_POST['cat_faqs']) && is_array($_POST['cat_faqs'])) {
            $faqs = []; foreach ($_POST['cat_faqs'] as $faq) if (!empty($faq['q']) && !empty($faq['a'])) $faqs[] = ['q' => sanitize_text_field($faq['q']), 'a' => wp_kses_post($faq['a'])];
            update_term_meta($term_id, 'cat_faqs', $faqs);
        } else delete_term_meta($term_id, 'cat_faqs');
    }

    public function save_tool_meta($post_id) {
        if (!isset($_POST['madenna_tool_nonce']) || !wp_verify_nonce($_POST['madenna_tool_nonce'], 'madenna_save_tool_code') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) return;
        
        $fields = ['tool_subtitle' => '_tool_subtitle', 'tool_icon_class' => '_tool_icon_class'];
        foreach ($fields as $post_k => $meta_k) if (isset($_POST[$post_k])) update_post_meta($post_id, $meta_k, sanitize_text_field($_POST[$post_k]));
        
        if (isset($_POST['tool_icon_url'])) update_post_meta($post_id, '_tool_icon_url', esc_url_raw($_POST['tool_icon_url']));
        foreach (['primary', 'secondary', 'accent'] as $c) if (isset($_POST["tool_color_{$c}"])) update_post_meta($post_id, "_tool_color_{$c}", sanitize_hex_color($_POST["tool_color_{$c}"]));
        
        update_post_meta($post_id, '_tool_force_site_font', isset($_POST['tool_force_site_font']) ? 'yes' : 'no');
        update_post_meta($post_id, '_tool_libraries', isset($_POST['tool_libraries']) && is_array($_POST['tool_libraries']) ? array_map('sanitize_text_field', $_POST['tool_libraries']) : []);

        $new_arts = isset($_POST['related_articles']) && is_array($_POST['related_articles']) ? array_map('intval', $_POST['related_articles']) : [];
        $old_arts = get_post_meta($post_id, '_related_articles', true);
        $old_arts = is_array($old_arts) ? $old_arts : [];
        update_post_meta($post_id, '_related_articles', $new_arts);
        foreach (array_diff($new_arts, $old_arts) as $art_id) { $t = get_post_meta($art_id, '_related_tools', true); $t = is_array($t) ? $t : []; if (!in_array($post_id, $t)) update_post_meta($art_id, '_related_tools', array_unique(array_merge($t, [$post_id]))); }
        foreach (array_diff($old_arts, $new_arts) as $art_id) { $t = get_post_meta($art_id, '_related_tools', true); $t = is_array($t) ? $t : []; update_post_meta($art_id, '_related_tools', array_values(array_diff($t, [$post_id]))); }

        if (isset($_POST['tool_faqs']) && is_array($_POST['tool_faqs'])) {
            $faqs = []; foreach ($_POST['tool_faqs'] as $faq) if (!empty($faq['q']) && !empty($faq['a'])) $faqs[] = ['q' => sanitize_text_field($faq['q']), 'a' => wp_kses_post($faq['a'])];
            update_post_meta($post_id, '_tool_faqs', $faqs);
        } else delete_post_meta($post_id, '_tool_faqs');

        $html = isset($_POST['tool_html']) ? wp_unslash($_POST['tool_html']) : ''; $css = isset($_POST['tool_css']) ? wp_unslash($_POST['tool_css']) : ''; $js = isset($_POST['tool_js']) ? wp_unslash($_POST['tool_js']) : '';
        update_post_meta($post_id, '_tool_html', $html); update_post_meta($post_id, '_tool_css', $css); update_post_meta($post_id, '_tool_js', $js);

        $dir = wp_upload_dir()['basedir'] . '/madenna-tools';
        if (!file_exists($dir)) wp_mkdir_p($dir);
        @file_put_contents("$dir/tool-{$post_id}.css", Madenna_Helpers::minify_code($css, 'css'));
        @file_put_contents("$dir/tool-{$post_id}.js", "(function(){\n" . Madenna_Helpers::minify_code($js, 'js') . "\n})();");
        
// همگام‌سازی ابزار با گوگل شیت مادنا
        $this->sync_to_google_sheet($post_id, 'tools');
    }

    public function save_article_meta($post_id) {
        if (!isset($_POST['madenna_article_nonce']) || !wp_verify_nonce($_POST['madenna_article_nonce'], 'madenna_save_article_data') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) return;
        
        if (isset($_POST['article_subtitle'])) update_post_meta($post_id, '_article_subtitle', sanitize_text_field($_POST['article_subtitle']));
        if (isset($_POST['article_audio_url'])) update_post_meta($post_id, '_article_audio_url', esc_url_raw($_POST['article_audio_url']));
        if (isset($_POST['article_icon_class'])) update_post_meta($post_id, '_article_icon_class', sanitize_text_field($_POST['article_icon_class']));
        if (isset($_POST['article_svg_url'])) update_post_meta($post_id, '_article_svg_url', esc_url_raw($_POST['article_svg_url']));

        $reading = isset($_POST['article_reading_time']) ? absint($_POST['article_reading_time']) : 0;
        if ($reading <= 0) {
            $content = isset(get_post($post_id)->post_content) ? strip_tags(get_post($post_id)->post_content) : '';
            $split = preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY);
            $reading = max(1, ceil((is_array($split) ? count($split) : 1) / 200));
        }
        update_post_meta($post_id, '_article_reading_time', $reading);

        $new_tools = isset($_POST['related_tools']) && is_array($_POST['related_tools']) ? array_map('intval', $_POST['related_tools']) : [];
        $old_tools = get_post_meta($post_id, '_related_tools', true);
        $old_tools = is_array($old_tools) ? $old_tools : [];
        update_post_meta($post_id, '_related_tools', $new_tools);
        foreach (array_diff($new_tools, $old_tools) as $tid) { $a = get_post_meta($tid, '_related_articles', true); $a = is_array($a) ? $a : []; if (!in_array($post_id, $a)) update_post_meta($tid, '_related_articles', array_unique(array_merge($a, [$post_id]))); }
        foreach (array_diff($old_tools, $new_tools) as $tid) { $a = get_post_meta($tid, '_related_articles', true); $a = is_array($a) ? $a : []; update_post_meta($tid, '_related_articles', array_values(array_diff($a, [$post_id]))); }

        if (isset($_POST['article_faqs']) && is_array($_POST['article_faqs'])) {
            $faqs = []; foreach ($_POST['article_faqs'] as $faq) if (!empty($faq['q']) && !empty($faq['a'])) $faqs[] = ['q' => sanitize_text_field($faq['q']), 'a' => wp_kses_post($faq['a'])];
            update_post_meta($post_id, '_article_faqs', $faqs);
        } else delete_post_meta($post_id, '_article_faqs');
        
// همگام‌سازی مقاله با گوگل شیت مادنا
        $this->sync_to_google_sheet($post_id, 'articles');
    }

    public function enqueue_admin_assets($hook) {
        if (isset($_GET['action']) && $_GET['action'] === 'elementor') return;

        global $post_type;
        wp_enqueue_style('font-awesome-6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');

        if (in_array($hook, ['edit.php', 'post.php', 'post-new.php', 'edit-tags.php', 'term.php'])) {
            wp_enqueue_media();
            if (function_exists('wp_enqueue_editor')) wp_enqueue_editor();
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery-ui-sortable');

            if ($post_type === 'tools' && function_exists('wp_enqueue_code_editor')) {
                $cm_settings = [
                    'type' => 'text/html',
                    'codemirror' => ['lineNumbers' => true, 'lint' => false, 'autoCloseTags' => true, 'matchBrackets' => true]
                ];
                wp_enqueue_code_editor($cm_settings);
                wp_add_inline_script('code-editor', "
                    var madenna_cm_html, madenna_cm_css, madenna_cm_js;
                    jQuery(function($){ 
                        var cm_opt = { codemirror: { lineNumbers: true, lint: false } };
                        if($('#tool_html').length && wp.codeEditor) madenna_cm_html = wp.codeEditor.initialize('tool_html', $.extend(true, {}, cm_opt, {codemirror: {mode:'htmlmixed'}})); 
                        if($('#tool_css').length && wp.codeEditor) madenna_cm_css = wp.codeEditor.initialize('tool_css', $.extend(true, {}, cm_opt, {codemirror: {mode:'css'}})); 
                        if($('#tool_js').length && wp.codeEditor) madenna_cm_js = wp.codeEditor.initialize('tool_js', $.extend(true, {}, cm_opt, {codemirror: {mode:'javascript'}})); 
                        
                        $('#post').on('submit', function() {
                            if(madenna_cm_html) madenna_cm_html.codemirror.save();
                            if(madenna_cm_css) madenna_cm_css.codemirror.save();
                            if(madenna_cm_js) madenna_cm_js.codemirror.save();
                        });
                    });
                ");
            }

            wp_add_inline_script('wp-color-picker', "
                jQuery(document).ready(function($) {
                    $('.madenna-color-picker').wpColorPicker();
                    
                    var activeInput=null, activePreview=null;
                    $(document).on('click', '.madenna-open-icon-modal', function(e) {
                        e.preventDefault(); activeInput = $('#'+$(this).data('target')); activePreview = $('#'+$(this).data('preview'));
                        $('#madenna-icon-search-input').val(''); $('.madenna-modal-icon-item').show(); $('#madenna-icon-modal-backdrop').fadeIn(150);
                        $('#madenna-total-icon-badge').text($('.madenna-modal-icon-item').length);
                    });
                    $('#madenna-close-icon-modal, #madenna-cancel-icon-modal').on('click', function(e) { e.preventDefault(); $('#madenna-icon-modal-backdrop').fadeOut(150); });
                    
                    $('#madenna-icon-search-input').on('keyup input', function() {
                        var search = $(this).val().toLowerCase().trim();
                        var count = 0;
                        $('.madenna-modal-icon-item').each(function() {
                            var n = $(this).data('name');
                            if (!search || n.indexOf(search) !== -1) { $(this).show(); count++; } else { $(this).hide(); }
                        });
                        $('#madenna-total-icon-badge').text(count);
                    });
                    
                    $(document).on('click', '.madenna-modal-icon-item', function() {
                        var icon = $(this).data('icon');
                        if (activeInput) activeInput.val(icon);
                        if (activePreview) activePreview.html('<i class=\"' + icon + '\" style=\"font-size:22px;\"></i>');
                        $('#madenna-icon-modal-backdrop').fadeOut(150);
                    });
                    
                    $(document).on('click', '.madenna-clear-icon-btn, .madenna-clear-media-btn', function(e) {
                        e.preventDefault(); $('#'+$(this).data('target')).val(''); $('#'+$(this).data('preview')).empty(); $(this).remove();
                    });
                    
                    $(document).on('click', '.madenna-palette-swatch', function(e) {
                        e.preventDefault(); var input = $('input[name=\"'+$(this).data('target')+'\"]');
                        if(input.length) { input.val($(this).data('color')); if(input.hasClass('wp-color-picker')) input.wpColorPicker('color', $(this).data('color')); }
                    });
                    
                    $('.madenna-sortable-list').sortable({placeholder:'sortable-placeholder', axis:'y', cursor:'move'});
                    
                    $(document).on('click', '.madenna-add-featured-btn', function(e) {
                        e.preventDefault(); var list=$('#'+$(this).data('list')), sel=$('#'+$(this).data('select')), val=sel.val(), txt=sel.find('option:selected').text(), name=$(this).data('input');
                        if(val && list.find('input[value=\"'+val+'\"]').length===0) list.append('<li style=\"display:flex;justify-content:space-between;background:#f8fafc;padding:6px 10px;margin-bottom:5px;border:1px solid #cbd5e1;border-radius:6px;cursor:move;\"><span>⋮⋮ '+txt+'</span><input type=\"hidden\" name=\"'+name+'[]\" value=\"'+val+'\"><button type=\"button\" class=\"button-link-delete madenna-remove-item\" style=\"color:#ef4444;\">حذف</button></li>');
                    });
                    $(document).on('click', '.madenna-remove-item', function(e) { e.preventDefault(); $(this).closest('li').remove(); });
                    
                    $(document).on('click', '.madenna-media-btn', function(e) {
                        e.preventDefault(); var btn=$(this), target=$('#'+btn.data('target')), preview=$('#'+btn.data('preview'));
                        var frame = wp.media({title:'انتخاب فایل', multiple:false});
                        frame.on('select', function() {
                            var att = frame.state().get('selection').first().toJSON(); target.val(att.url);
                            if(att.url.match(/\.(mp3|wav|ogg)$/i) && preview.length) preview.html('<audio controls style=\"width:100%;height:36px;margin-top:8px;\"><source src=\"'+att.url+'\"></audio>');
                            else if(att.url.match(/\.(jpe?g|png|gif|svg|webp)$/i) && preview.length) preview.html('<img src=\"'+att.url+'\" style=\"max-width:65px;margin-top:8px;border-radius:6px;border:1px solid #ccc;\">');
                        });
                        frame.open();
                    });

                    $('#madenna-trigger-preview-btn').on('click', function(e) {
                        e.preventDefault(); 
                        var html = (typeof madenna_cm_html !== 'undefined' && madenna_cm_html) ? madenna_cm_html.codemirror.getValue() : ($('#tool_html').val()||'');
                        var css = (typeof madenna_cm_css !== 'undefined' && madenna_cm_css) ? madenna_cm_css.codemirror.getValue() : ($('#tool_css').val()||'');
                        var js = (typeof madenna_cm_js !== 'undefined' && madenna_cm_js) ? madenna_cm_js.codemirror.getValue() : ($('#tool_js').val()||'');
                        
                        var cdnTags = '';
                        $('input[name=\"tool_libraries[]\"]:checked').each(function() {
                            var lib = $(this).val();
                            if(lib === 'tailwind') cdnTags += '<script src=\"https://cdn.tailwindcss.com\"><\/script>';
                            if(lib === 'chartjs') cdnTags += '<script src=\"https://cdn.jsdelivr.net/npm/chart.js\"><\/script>';
                        });

                        var doc = '<!DOCTYPE html><html><head><meta charset=\"UTF-8\">'+cdnTags+'<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css\"><style>body{font-family:system-ui;margin:15px;direction:rtl;}'+css+'</style></head><body>'+html+'<script>'+js+'<\/script></body></html>';
                        $('#madenna-live-preview-box').slideDown(200); 
                        var iframe = document.getElementById('madenna-preview-iframe').contentWindow.document; 
                        iframe.open(); iframe.write(doc); iframe.close();
                    });
                    $('#madenna-close-preview-btn').on('click', function(e) { e.preventDefault(); $('#madenna-live-preview-box').slideUp(200); });

                    $(document).on('click', '.madenna-add-faq-btn', function(e) {
                        e.preventDefault(); var c = $('#'+$(this).data('container')), p = $(this).data('prefix'), id = Date.now(), eid = p+'_ed_'+id;
                        c.append('<div class=\"faq-item\" style=\"background:#fff;border:1px solid #ccd0d4;padding:15px;margin-bottom:12px;border-radius:8px;\"><input type=\"text\" name=\"'+p+'['+id+'][q]\" placeholder=\"پرسش...\" style=\"width:100%;margin-bottom:10px;\"><textarea id=\"'+eid+'\" name=\"'+p+'['+id+'][a]\" rows=\"4\" style=\"width:100%;\"></textarea><button type=\"button\" class=\"button-link-delete madenna-remove-faq\" data-editor-id=\"'+eid+'\" style=\"color:#b32d2e;margin-top:8px;\">حذف سوال</button></div>');
                        if(typeof wp!=='undefined' && wp.editor) wp.editor.initialize(eid, {tinymce:{wpautop:true,toolbar1:'bold,italic,bullist,numlist,link,unlink'}, quicktags:true});
                    });
                    $(document).on('click', '.madenna-remove-faq', function(e) { e.preventDefault(); var eid = $(this).data('editor-id'); if(eid && typeof wp!=='undefined' && wp.editor) wp.editor.remove(eid); $(this).closest('.faq-item').remove(); });
                    
                    // اکشن‌های مربوط به ستون برگزیده‌ها و دکمه خودکار در پیشخوان
                    $(document).on('click', '.madenna-toggle-featured', function(e) {
                        e.preventDefault();
                        var btn = $(this);
                        btn.css('opacity', '0.5');
                        $.post(ajaxurl, { action: 'madenna_toggle_featured', post_id: btn.data('id') }, function(res) {
                            btn.css('opacity', '1');
                            if(res && res.success) {
                                btn.css('color', res.data.status === 'yes' ? '#eab308' : '#cbd5e1'); // زرد یا خاکستری
                                btn.attr('title', res.data.status === 'yes' ? 'حذف از برگزیده‌ها' : 'افزودن به برگزیده‌ها');
                            }
                        });
                    });

                    $(document).on('click', '.madenna-auto-featured-btn', function(e) {
                        e.preventDefault();
                        var btn = $(this);
                        btn.text('در حال پردازش...').prop('disabled', true);
                        $.post(ajaxurl, { action: 'madenna_auto_featured', post_type: btn.data('type') }, function(res) {
                            if(res && res.success) {
                                location.reload();
                            }
                        });
                    });
                });
            ");
        }
    }

    public function render_global_icon_picker_modal() {
        if (!function_exists('get_current_screen')) return;
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->base, ['post', 'term', 'edit-tags'])) return;
        
        $fa_solid_str = 'house,user,check,download,image,video,music,file,folder,gear,gears,code,laptop-code,terminal,microchip,database,server,network-wired,robot,brain,cube,cubes,sitemap,plug,wifi,bolt,qrcode,barcode,calculator,wrench,hammer,toolbox,filter,magnifying-glass,scissors,paperclip,stopwatch,clock,calendar,coins,wallet,credit-card,vault,piggy-bank,receipt,chart-line,chart-pie,store,briefcase,tags,bullhorn,book,graduation-cap,file-pdf,file-word,file-excel,bookmark,lightbulb,circle-info,circle-check,palette,paintbrush,camera,play,microphone,headphones,desktop,mobile,tablet,users,envelope,paper-plane,comments,share-nodes,link,globe,location-dot,star,heart,thumbs-up,award,fire,rocket,cloud,shield,lock,key,eye,arrow-right,arrow-left,arrows-rotate,trash,copy,upload,bell,compass,sun,moon,chevron-down,chevron-up,chevron-left,chevron-right,plus,minus,xmark,bars,list,table,list-check,heading,paragraph,align-left,align-center,align-right,align-justify,bold,italic,underline,bug,wand-magic-sparkles,gauge,traffic-light,plane,car,ship,train,bicycle,truck,bus,snowflake,wind,droplet,temperature-half,battery-full,signal,podcast,file-audio,file-video,file-image,file-code,file-zipper,file-csv,folder-open,print,phone,headset,gamepad,dice,chess,trophy,medal,crown,certificate,scale-balanced,gavel,building-columns,building,hospital,stethoscope,syringe,pills,dna,virus,skull,ghost,spider,layer-group,sliders,crop-simple,anchor,archway,atom,ban,battery-empty,battery-half,battery-full,bed,bell-slash,bomb,bone,box,box-open,bread-slice,broom,bucket,bug-slash,building-shield,burger,bus-simple,cable-car,campground,candy-cane,cannabis,capsules,car-battery,car-burst,car-side,caravan,carrot,cart-arrow-down,cart-plus,cart-shopping,cash-register,cat,chair,chalkboard,chalkboard-user,champagne-glasses,charging-station,chart-area,chart-bar,chart-column,check-double,cheese,chess-board,chess-king,chess-knight,chess-queen,child,church,city,clapperboard,clipboard,clipboard-check,clipboard-list,clock-rotate-left,clone,cloud-arrow-down,cloud-arrow-up,cloud-bolt,cloud-rain,cloud-sun,clover,code-branch,code-commit,code-compare,code-fork,code-merge,code-pull-request,comment-dollar,comment-dots,comments-dollar,compact-disc,compass-drafting,compress,computer,cookie,copy,copyright,couch,cow,credit-card,crop,crosshairs,crow,crutch,cubes-stacked,database,delete-left,desktop,dice-d20,dice-five,dice-six,display,divide,dna,dog,dollar-sign,door-closed,door-open,dove,download,dragon,droplet,drum,dumbbell,dumpster,ear-deaf,ear-listen,earth-americas,earth-asia,earth-europe,egg,eject,elevator,ellipsis,ellipsis-vertical,envelope-open,equals,eraser,ethernet,euro-sign,exclamation,expand,explosion,eye-dropper,eye-slash,face-smile,fan,faucet,fax,feather,ferry,file-export,file-import,file-invoice,film,filter,fingerprint,fire-extinguisher,fish,flag,flag-checkered,flask,floppy-disk,folder-closed,folder-minus,folder-plus,folder-tree,football,forward,forward-fast,frog,gamepad,gas-pump,gauge-high,gavel,gem,ghost,gift,gifts,glasses,globe,graduation-cap,guitar,gun,hammer,hand,hand-holding-dollar,handshake,hard-drive,hat-cowboy,hat-wizard,headphones,headset,heart,heart-crack,helicopter,helmet-safety,highlighter,hippo,horse,hospital,hotdog,hotel,hourglass,hourglass-end,hourglass-half,hourglass-start,house,ice-cream,id-badge,id-card,igloo,image,images,inbox,indent,industry,infinity,info,italic,jedi,key,keyboard,language,laptop,leaf,lemon,life-ring,lightbulb,link,list,list-check,list-ol,list-ul,location-arrow,location-crosshairs,location-dot,lock,lock-open,magnet,magnifying-glass,map,map-location,map-pin,marker,mars,mask,medal,memory,message,meteor,microchip,microphone,microphone-slash,microscope,minimize,minus,mobile,mobile-screen,money-bill,money-bills,moon,motorcycle,mountain,mug-hot,music,network-wired,newspaper,note-sticky,oil-can,om,otter,outdent,paint-roller,paintbrush,palette,pallet,paper-plane,paperclip,parachute-box,paragraph,passport,paste,pause,paw,peace,pen,pen-clip,pen-nib,pencil,percent,person,person-biking,person-dots-from-line,person-dress,person-hiking,person-praying,person-running,person-skating,person-skiing,person-swimming,person-walking,phone,phone-flip,phone-slash,phone-volume,photo-film,piggy-bank,pills,pizza-slice,plane,plane-departure,plane-slash,play,plug,plus,podcast,poop,power-off,print,puzzle-piece,qrcode,question,quote-left,quote-right,radiation,radio,rainbow,receipt,record-vinyl,rectangle-list,rectangle-xmark,recycle,reply,reply-all,restroom,retweet,ribbon,right-from-bracket,ring,road,robot,rocket,rotate,rotate-left,rotate-right,route,rss,ruble-sign,ruler,rupee-sign,sack-dollar,sailboat,satellite,scale-balanced,school,scissors,screwdriver,screwdriver-wrench,sd-card,server,shapes,share,share-nodes,shield,shield-cat,shield-dog,ship,shirt,shoe-prints,shop,shower,shrimp,shuffle,shuttle-space,signal,signature,sim-card,sitemap,skull,skull-crossbones,slash,sleigh,sliders,smoking,snowflake,snowman,snowplow,soap,socks,solar-panel,sort,sort-down,sort-up,spa,spider,spinner,spoon,square,square-check,square-minus,square-plus,square-xmark,stairs,stamp,star,star-half,star-half-stroke,stethoscope,stop,stopwatch,store,street-view,strikethrough,suitcase,sun,superscript,swatchbook,syringe,table,tablet,tag,tags,tape,taxi,teeth,temperature-empty,temperature-full,temperature-half,tent,terminal,text-height,text-width,thermometer,thumbs-down,thumbs-up,thumbtack,ticket,timeline,toggle-off,toggle-on,toilet,toolbox,tooth,tornado,tractor,trademark,traffic-light,train,tram,transgender,trash,trash-can,tree,trophy,truck,truck-fast,truck-pickup,tty,tv,umbrella,underline,universal-access,unlock,upload,user,user-astronaut,user-doctor,user-graduate,user-group,user-ninja,user-nurse,user-secret,user-shield,user-tie,users,vault,vector-square,venus,vial,video,video-slash,virus,viruses,voicemail,volcano,volleyball,volume-high,volume-low,volume-off,volume-xmark,vr-cardboard,walkie-talkie,wallet,wand-magic,warehouse,water,weight-scale,wheelchair,wifi,wind,window-maximize,window-minimize,window-restore,wine-bottle,wine-glass,wrench,x-ray,xmark,yen-sign,yin-yang';
        $fa_brands_str = 'python,js,php,html5,css3-alt,react,node-js,vuejs,angular,laravel,bootstrap,docker,github,figma,wordpress,elementor,linux,windows,apple,android,google,telegram,instagram,x-twitter,linkedin,youtube,bitcoin,ethereum,paypal,stripe,aws,cloudflare,npm,yarn,git,gitlab,trello,jira,slack,discord,spotify,twitch,tiktok,reddit,stack-overflow,codepen,amazon,ebay,shopify,vimeo,patreon,adobe,behance,dribbble,whatsapp,skype,zoom,microsoft,playstation,xbox,steam,unity,java,rust,golang,swift,ruby,sass,less,markdown,ubuntu,centos,accusoft,airbnb,alipay,amazon-pay,angellist,app-store,app-store-ios,atlassian,audible,autoprefixer,aws,bandcamp,battle-net,bilibili,bitbucket,black-tie,blackberry,blogger,bluetooth,btc,buffer,cc-amazon-pay,cc-amex,cc-apple-pay,cc-discover,cc-mastercard,cc-paypal,cc-stripe,cc-visa,chrome,chromecast,codepen,confluence,cpanel,creative-commons,css3,dailymotion,deezer,dev,deviantart,digital-ocean,discourse,docker,dropbox,drupal,ebay,edge,ello,ember,envira,erlang,etsy,evernote,facebook,facebook-f,facebook-messenger,fedex,fedora,firefox,flickr,fly,foursquare,free-code-camp,git-alt,github-alt,gitter,glide,goodreads,google-drive,google-pay,google-play,google-wallet,grunt,gulp,hacker-news,hackerrank,hubspot,imdb,intercom,internet-explorer,invision,itunes,java,jenkins,joomla,jsfiddle,kaggle,keybase,kickstarter,laravel,lastfm,less,line,linkedin-in,linode,lyft,magento,mailchimp,markdown,mastodon,medium,meetup,microblog,mixcloud,napster,node,odnoklassniki,opera,patreon,periscope,pinterest,pinterest-p,product-hunt,python,qq,quora,raspberry-pi,react,reddit-alien,redhat,researchgate,rust,safari,salesforce,sass,sketch,slideshare,snapchat,soundcloud,sourcetree,speaker-deck,spotify,squarespace,stack-exchange,stack-overflow,steam,strava,stripe,suse,swift,symfony,teamspeak,telegram,tiktok,trello,tumblr,twitch,twitter,typo3,uber,ubuntu,unity,unsplash,untappd,ups,usb,viber,vimeo,vk,vuejs,waze,weebly,weibo,weixin,whatsapp,wikipedia-w,windows,wix,wordpress,wpbeginner,wpexplorer,x-twitter,xbox,xing,yahoo,yandex,yarn,yelp,yoast,youtube,zhihu';
        
        $fa_solid = explode(',', $fa_solid_str);
        $fa_brands = explode(',', $fa_brands_str);
        
        $icons = [];
        foreach ($fa_solid as $ic) $icons[] = ['class' => 'fa-solid fa-'.trim($ic), 'name' => trim($ic)];
        foreach ($fa_brands as $ic) $icons[] = ['class' => 'fa-brands fa-'.trim($ic), 'name' => trim($ic)];
        $total = count($icons);
        ?>
        <div id="madenna-icon-modal-backdrop" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.68); z-index:999999; backdrop-filter:blur(3px);">
            <div id="madenna-icon-modal-window" style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:94%; max-width:920px; max-height:90vh; background:#fff; border-radius:16px; display:flex; flex-direction:column; overflow:hidden; direction:rtl;">
                <div style="padding:16px 24px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                    <div><strong style="font-size:16px;">کتابخانه آیکون‌های وکتور مادنا</strong><br><span style="font-size:11px; color:#64748b;">نمایش: <b id="madenna-total-icon-badge"><?php echo $total; ?></b> آیکون</span></div>
                    <button type="button" id="madenna-close-icon-modal" style="background:none; border:none; font-size:24px; cursor:pointer;">✕</button>
                </div>
                <div style="padding:12px 24px; border-bottom:1px solid #e2e8f0; background:#fff;">
                    <input type="text" id="madenna-icon-search-input" placeholder="🔍 جستجوی هوشمند انگلیسی (calculator, robot...)" style="width:100%; height:44px; border-radius:8px; border:1px solid #cbd5e1; padding:0 14px;">
                </div>
                <div id="madenna-icon-modal-grid" style="padding:20px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill, minmax(70px, 1fr)); gap:10px; flex:1; max-height:58vh; background:#fbfcfd;">
                    <?php foreach ($icons as $icon): ?>
                        <div class="madenna-modal-icon-item" data-icon="<?php echo esc_attr($icon['class']); ?>" data-name="<?php echo esc_attr($icon['name']); ?>" style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:70px; border:1px solid #e2e8f0; border-radius:10px; cursor:pointer; background:#fff;">
                            <i class="<?php echo esc_attr($icon['class']); ?>" style="font-size:22px; color:#334155;"></i>
                            <span style="font-size:10px; color:#64748b; margin-top:5px; max-width:58px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html($icon['name']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="padding:14px 24px; border-top:1px solid #e2e8f0; background:#f8fafc; text-align:left;"><button type="button" class="button button-large" id="madenna-cancel-icon-modal">بستن</button></div>
            </div>
        </div>
        <?php
    }

    // ==============================================================
    // متدهای مربوط به نمایش ستون‌های بازدید و ستاره برگزیده‌ها
    // ==============================================================
    
    public function add_custom_columns($columns) {
        $new_columns = [];
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            if ($key === 'title') {
                $new_columns['madenna_featured'] = '<span title="برگزیده‌ها">⭐</span>';
                $new_columns['madenna_views'] = '👁️ بازدید';
            }
        }
        return $new_columns;
    }

    public function render_custom_columns($column, $post_id) {
        if ($column === 'madenna_views') {
            $views = get_post_meta($post_id, '_madenna_views_count', true);
            echo $views ? number_format($views) : '0';
        }
        if ($column === 'madenna_featured') {
            $is_featured = get_post_meta($post_id, '_madenna_is_featured', true) === 'yes';
            $color = $is_featured ? '#eab308' : '#cbd5e1';
            $title = $is_featured ? 'حذف از برگزیده‌ها' : 'افزودن به برگزیده‌ها';
            echo '<a href="#" class="madenna-toggle-featured" data-id="'.$post_id.'" title="'.$title.'" style="color:'.$color.'; font-size:20px; text-decoration:none;">★</a>';
        }
    }

    public function sortable_custom_columns($columns) {
        $columns['madenna_views'] = 'madenna_views';
        return $columns;
    }

    public function custom_orderby_views($query) {
        if (!is_admin() || !$query->is_main_query()) return;
        $orderby = $query->get('orderby');
        if ('madenna_views' === $orderby) {
            $query->set('meta_key', '_madenna_views_count');
            $query->set('orderby', 'meta_value_num');
        }
    }

    public function add_auto_featured_button($which) {
        global $typenow;
        if ($which === 'top' && in_array($typenow, ['articles', 'tools'])) {
            echo '<div class="alignleft actions">';
            echo '<button type="button" class="button madenna-auto-featured-btn" data-type="'.$typenow.'">✨ انتخاب ۴ برگزیده (پربازدیدترین‌ها)</button>';
            echo '</div>';
        }
    }

    public function ajax_toggle_featured() {
        if (!current_user_can('edit_posts')) wp_die();
        $post_id = intval($_POST['post_id']);
        $current = get_post_meta($post_id, '_madenna_is_featured', true);
        $new = $current === 'yes' ? 'no' : 'yes';
        update_post_meta($post_id, '_madenna_is_featured', $new);
        wp_send_json_success(['status' => $new]);
    }

    public function ajax_auto_featured() {
        if (!current_user_can('edit_posts')) wp_die();
        $type = sanitize_text_field($_POST['post_type']);
        
        // پاک کردن تیک تمام برگزیده‌های این پست تایپ
        $all = get_posts(['post_type' => $type, 'posts_per_page' => -1, 'post_status' => 'publish', 'fields' => 'ids']);
        foreach ($all as $id) {
            delete_post_meta($id, '_madenna_is_featured');
        }
        
        // انتخاب ۴ تای برتر بر اساس بازدید
        $top = get_posts([
            'post_type' => $type,
            'posts_per_page' => 4,
            'post_status' => 'publish',
            'meta_key' => '_madenna_views_count',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'fields' => 'ids'
        ]);
        
        foreach ($top as $id) {
            update_post_meta($id, '_madenna_is_featured', 'yes');
        }
        wp_send_json_success();
    }
    
    
/**
     * همگام‌سازی خودکار مقالات و ابزارها با گوگل شیت مادنا
     */
    private function sync_to_google_sheet($post_id, $post_type) {
        $webhook_url = 'https://script.google.com/macros/s/AKfycbzoLITUNwQoAFSM8pnWP4DiTH6USjBMA4ht9AeSzQq1lKVXc2YYCeQN6TkWaG0qUNSO/exec'; // آدرس وب‌هوک گوگل شیت خود را اینجا بگذارید

        if (empty($webhook_url) || strpos($webhook_url, 'YOUR_GOOGLE') !== false) {
            return;
        }

        $post = get_post($post_id);
        if (!$post || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        $cats = wp_get_post_terms($post_id, 'madenna_cat');
        $cat_slug = (!empty($cats) && !is_wp_error($cats)) ? $cats[0]->slug : '';

        $body = [
            'post_type' => $post_type,
            'id'        => $post_id,
            'title'     => $post->post_title,
            'status'    => $post->post_status,
            'url'       => get_permalink($post_id),
            'cat_slug'  => $cat_slug,
        ];

// افزودن اطلاعات سئو و آیکون به مقاله
        if ($post_type === 'articles') {
            $body['subtitle']      = get_post_meta($post_id, '_article_subtitle', true);
            $body['content']       = $post->post_content;
            $body['excerpt']       = $post->post_excerpt;
            $body['reading_time']  = get_post_meta($post_id, '_article_reading_time', true);
            $body['audio_url']     = get_post_meta($post_id, '_article_audio_url', true);
            $body['related_tools'] = get_post_meta($post_id, '_related_tools', true);
            $body['faqs']          = get_post_meta($post_id, '_article_faqs', true);
            // موارد سئو و آیکون:
            $body['seo_title']     = get_post_meta($post_id, '_yoast_wpseo_title', true);
            $body['seo_desc']      = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            $body['icon_class']    = get_post_meta($post_id, '_article_icon_class', true);
        } elseif ($post_type === 'tools') {
            $tags = wp_get_post_terms($post_id, 'madenna_tool_tag');
            $body['tool_tag']         = (!empty($tags) && !is_wp_error($tags)) ? $tags[0]->slug : '';
            $body['subtitle']         = get_post_meta($post_id, '_tool_subtitle', true);
            $body['html']             = get_post_meta($post_id, '_tool_html', true);
            $body['css']              = get_post_meta($post_id, '_tool_css', true);
            $body['js']               = get_post_meta($post_id, '_tool_js', true);
            $body['libraries']        = get_post_meta($post_id, '_tool_libraries', true);
            $body['related_articles'] = get_post_meta($post_id, '_related_articles', true);
            $body['faqs']             = get_post_meta($post_id, '_tool_faqs', true);
            // موارد سئو، رنگ‌ها و آیکون:
            $body['seo_title']        = get_post_meta($post_id, '_yoast_wpseo_title', true);
            $body['seo_desc']         = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            $body['color_primary']    = get_post_meta($post_id, '_tool_color_primary', true);
            $body['color_secondary']  = get_post_meta($post_id, '_tool_color_secondary', true);
            $body['color_accent']     = get_post_meta($post_id, '_tool_color_accent', true);
            $body['icon_class']       = get_post_meta($post_id, '_tool_icon_class', true);
        }

        wp_remote_post($webhook_url, [
            'blocking' => false,
            'headers'  => ['Content-Type' => 'application/json; charset=utf-8'],
            'body'     => wp_json_encode($body),
        ]);
    }
    
    
    
    /**
     * ثبت روت اختصاصی دریافت داده از گوگل شیت
     */
    public function register_sync_api_routes() {
        register_rest_route('madenna/v1', '/sync-item', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_sheet_sync_endpoint'],
            'permission_callback' => '__return_true', // امنیت بر اساس توکن داخلی
        ]);
    }

    public function handle_sheet_sync_endpoint($request) {
        $params = $request->get_json_params();
        if (empty($params) || empty($params['post_type'])) {
            return new WP_Error('no_data', 'داده‌ای دریافت نشد', ['status' => 400]);
        }

        $post_type = sanitize_text_field($params['post_type']);
        $post_id   = !empty($params['id']) ? intval($params['id']) : 0;
        $title     = sanitize_text_field($params['title'] ?? '');
        $status    = !empty($params['status']) ? sanitize_text_field($params['status']) : 'draft';

        $post_data = [
            'post_type'   => $post_type,
            'post_title'  => $title,
            'post_status' => $status,
        ];

        if ($post_type === 'articles') {
            $post_data['post_content'] = wp_kses_post($params['content'] ?? '');
            $post_data['post_excerpt'] = sanitize_textarea_field($params['excerpt'] ?? '');
        }

        if ($post_id > 0 && get_post($post_id)) {
            $post_data['ID'] = $post_id;
            $saved_id = wp_update_post($post_data);
        } else {
            $saved_id = wp_insert_post($post_data);
        }

        if (is_wp_error($saved_id)) {
            return $saved_id;
        }

        // ۱. تنظیم دسته‌بندی موضوعی بر اساس اسلاگ
        if (!empty($params['cat_slug'])) {
            $cat = get_term_by('slug', sanitize_title($params['cat_slug']), 'madenna_cat');
            if ($cat && !is_wp_error($cat)) {
                wp_set_object_terms($saved_id, [$cat->term_id], 'madenna_cat');
            }
        }

        // ۲. تنظیم فیلدهای اختصاصی ابزارها
        if ($post_type === 'tools') {
            if (!empty($params['tool_tag_slug'])) {
                $tag = get_term_by('slug', sanitize_title($params['tool_tag_slug']), 'madenna_tool_tag');
                if ($tag && !is_wp_error($tag)) {
                    wp_set_object_terms($saved_id, [$tag->term_id], 'madenna_tool_tag');
                }
            }

            $html = isset($params['html']) ? wp_unslash($params['html']) : '';
            $css  = isset($params['css']) ? wp_unslash($params['css']) : '';
            $js   = isset($params['js']) ? wp_unslash($params['js']) : '';

            update_post_meta($saved_id, '_tool_subtitle', sanitize_text_field($params['subtitle'] ?? ''));
            update_post_meta($saved_id, '_tool_html', $html);
            update_post_meta($saved_id, '_tool_css', $css);
            update_post_meta($saved_id, '_tool_js', $js);
            update_post_meta($saved_id, '_tool_color_primary', sanitize_hex_color($params['color_primary'] ?? '#0f5650'));
            update_post_meta($saved_id, '_tool_color_secondary', sanitize_hex_color($params['color_secondary'] ?? '#426561'));
            update_post_meta($saved_id, '_tool_color_accent', sanitize_hex_color($params['color_accent'] ?? '#c2e7e2'));
            update_post_meta($saved_id, '_tool_icon_class', sanitize_text_field($params['icon_class'] ?? ''));
            update_post_meta($saved_id, '_tool_force_site_font', 'yes');

            // تنظیم کتابخانه‌های CDN
            $libs = !empty($params['libraries']) ? (is_array($params['libraries']) ? $params['libraries'] : explode(',', $params['libraries'])) : ['tailwind'];
            update_post_meta($saved_id, '_tool_libraries', array_map('trim', $libs));

            // سئو یواست
            if (!empty($params['seo_title'])) update_post_meta($saved_id, '_yoast_wpseo_title', sanitize_text_field($params['seo_title']));
            if (!empty($params['seo_desc']))  update_post_meta($saved_id, '_yoast_wpseo_metadesc', sanitize_textarea_field($params['seo_desc']));

            // سوالات متداول FAQ
            if (!empty($params['faqs']) && is_array($params['faqs'])) {
                update_post_meta($saved_id, '_tool_faqs', $params['faqs']);
            }

            // مقالات مرتبط
            if (!empty($params['related_articles'])) {
                $rel_arts = is_array($params['related_articles']) ? $params['related_articles'] : explode(',', $params['related_articles']);
                update_post_meta($saved_id, '_related_articles', array_map('intval', $rel_arts));
            }

            // تولید و کش فایل فیزیکی CSS و JS
            $dir = wp_upload_dir()['basedir'] . '/madenna-tools';
            if (!file_exists($dir)) wp_mkdir_p($dir);
            if (!empty($css)) @file_put_contents("$dir/tool-{$saved_id}.css", Madenna_Helpers::minify_code($css, 'css'));
            if (!empty($js))  @file_put_contents("$dir/tool-{$saved_id}.js", "(function(){\n" . Madenna_Helpers::minify_code($js, 'js') . "\n})();");
        }

        // ۳. تنظیم فیلدهای اختصاصی مقالات
        if ($post_type === 'articles') {
            update_post_meta($saved_id, '_article_subtitle', sanitize_text_field($params['subtitle'] ?? ''));
            update_post_meta($saved_id, '_article_reading_time', intval($params['reading_time'] ?? 0));
            update_post_meta($saved_id, '_article_audio_url', esc_url_raw($params['audio_url'] ?? ''));
            update_post_meta($saved_id, '_article_icon_class', sanitize_text_field($params['icon_class'] ?? ''));

            if (!empty($params['seo_title'])) update_post_meta($saved_id, '_yoast_wpseo_title', sanitize_text_field($params['seo_title']));
            if (!empty($params['seo_desc']))  update_post_meta($saved_id, '_yoast_wpseo_metadesc', sanitize_textarea_field($params['seo_desc']));

            if (!empty($params['faqs']) && is_array($params['faqs'])) {
                update_post_meta($saved_id, '_article_faqs', $params['faqs']);
            }

            if (!empty($params['related_tools'])) {
                $rel_tools = is_array($params['related_tools']) ? $params['related_tools'] : explode(',', $params['related_tools']);
                update_post_meta($saved_id, '_related_tools', array_map('intval', $rel_tools));
            }
        }

        return rest_ensure_response([
            'success' => true,
            'id'      => $saved_id,
            'link'    => get_permalink($saved_id),
        ]);
    }
}