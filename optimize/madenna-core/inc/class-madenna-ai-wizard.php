<?php
/**
 * ماژول ساخت هوشمند مقالات، ابزارها و دسته‌بندی‌های مادنا با هوش مصنوعی چرخشی
 * نسخه ۲.۱ - اصلاح خطای ۵۰۰، تنظیم انحصاری تصویر و هماهنگی کامل با هسته مادنا
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_AI_Wizard {

    private static $worker_endpoint = 'https://plugin-insert-ai-madenna.raihankhan200423.workers.dev/';

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_admin_menu']);
        add_action('wp_ajax_madenna_ai_generate_action', [__CLASS__, 'handle_ajax_generation']);
        add_action('wp_ajax_madenna_ai_save_site_context', [__CLASS__, 'handle_save_site_context']);
        add_action('wp_ajax_madenna_ai_get_existing_items', [__CLASS__, 'handle_get_existing_items']);
    }

    /**
     * استخراج پایگاه دانش جامع سایت: پالت‌های رنگی تم، درخت کامل دسته‌ها، ابزارها و مقالات
     */
    public static function get_full_site_knowledge() {
        // ۱. پالت رنگ‌های فعال المنتور و پوسته سایت
        $global_colors = [];
        if (class_exists('Madenna_Core') && method_exists('Madenna_Core', 'get_elementor_global_colors')) {
            $global_colors = Madenna_Core::get_elementor_global_colors();
        } else {
            $kit_id = get_option('elementor_active_kit');
            if ($kit_id) {
                $kit_settings = get_post_meta($kit_id, '_elementor_page_settings', true);
                if (is_array($kit_settings)) {
                    $sys = $kit_settings['system_colors'] ?? [];
                    $cus = $kit_settings['custom_colors'] ?? [];
                    foreach (array_merge($sys, $cus) as $c) {
                        if (!empty($c['color'])) {
                            $global_colors[] = ['title' => $c['title'] ?? 'رنگ تم', 'color' => $c['color']];
                        }
                    }
                }
            }
        }

        // ۲. درخت کامل دسته‌بندی‌ها و زیردسته‌های موجود
        $all_terms = get_terms([
            'taxonomy'   => 'madenna_cat',
            'hide_empty' => false,
        ]);

        $taxonomy_tree = [];
        if (!empty($all_terms) && !is_wp_error($all_terms)) {
            $terms_by_id = [];
            foreach ($all_terms as $t) {
                $terms_by_id[$t->term_id] = [
                    'id'              => $t->term_id,
                    'name'            => $t->name,
                    'slug'            => $t->slug,
                    'parent_id'       => $t->parent,
                    'subtitle'        => get_term_meta($t->term_id, 'cat_subtitle', true),
                    'color_primary'   => get_term_meta($t->term_id, 'cat_color_primary', true) ?: '#2563eb',
                    'color_accent'    => get_term_meta($t->term_id, 'cat_color_accent', true) ?: '#eff6ff',
                    'icon_class'      => get_term_meta($t->term_id, 'cat_icon_class', true) ?: 'fa-solid fa-folder',
                    'subcategories'   => []
                ];
            }

            foreach ($terms_by_id as $id => &$term_node) {
                if ($term_node['parent_id'] > 0 && isset($terms_by_id[$term_node['parent_id']])) {
                    $terms_by_id[$term_node['parent_id']]['subcategories'][] = [
                        'id'   => $term_node['id'],
                        'name' => $term_node['name'],
                        'slug' => $term_node['slug']
                    ];
                } else {
                    $taxonomy_tree[] = &$term_node;
                }
            }
            unset($term_node);
        }

        // ۳. فهرست ابزارهای موجود
        $existing_tools = [];
        $raw_tools = get_posts([
            'post_type'      => 'tools',
            'posts_per_page' => 150,
            'post_status'    => ['publish', 'draft']
        ]);
        foreach ($raw_tools as $t) {
            $terms = get_the_terms($t->ID, 'madenna_cat');
            $cat_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : '';
            $existing_tools[] = [
                'id'       => $t->ID,
                'title'    => $t->post_title,
                'category' => $cat_name
            ];
        }

        // ۴. فهرست مقالات موجود
        $existing_articles = [];
        $raw_articles = get_posts([
            'post_type'      => 'articles',
            'posts_per_page' => 150,
            'post_status'    => ['publish', 'draft']
        ]);
        foreach ($raw_articles as $a) {
            $terms = get_the_terms($a->ID, 'madenna_cat');
            $cat_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : '';
            $existing_articles[] = [
                'id'       => $a->ID,
                'title'    => $a->post_title,
                'category' => $cat_name
            ];
        }

        return [
            'global_colors'     => $global_colors,
            'taxonomy_tree'     => $taxonomy_tree,
            'existing_tools'    => $existing_tools,
            'existing_articles' => $existing_articles
        ];
    }

    /**
     * ذخیره فیلدهای سئو (عنوان و متای توضیحات) در متاداده‌های وردپرس، Yoast و Rank Math
     */
    public static function save_seo_meta_data($object_id, $type, $seo_title, $seo_desc) {
        if (empty($seo_title) && empty($seo_desc)) {
            return;
        }

        if ($type === 'term') {
            // ذخیره برای تاکسونومی madenna_cat
            if (!empty($seo_title)) {
                update_term_meta($object_id, '_madenna_seo_title', sanitize_text_field($seo_title));
                update_term_meta($object_id, 'rank_math_title', sanitize_text_field($seo_title));
            }
            if (!empty($seo_desc)) {
                update_term_meta($object_id, '_madenna_seo_desc', sanitize_textarea_field($seo_desc));
                update_term_meta($object_id, 'rank_math_description', sanitize_textarea_field($seo_desc));
            }

            // هماهنگی با تنظیمات Yoast SEO در تکسانومی‌ها
            $wpseo_taxonomy_meta = get_option('wpseo_taxonomy_meta');
            if (is_array($wpseo_taxonomy_meta) || empty($wpseo_taxonomy_meta)) {
                if (!empty($seo_title)) {
                    $wpseo_taxonomy_meta['madenna_cat'][$object_id]['wpseo_title'] = sanitize_text_field($seo_title);
                }
                if (!empty($seo_desc)) {
                    $wpseo_taxonomy_meta['madenna_cat'][$object_id]['wpseo_desc'] = sanitize_textarea_field($seo_desc);
                }
                update_option('wpseo_taxonomy_meta', $wpseo_taxonomy_meta);
            }
        } else {
            // ذخیره برای پست‌ها (مقاله و ابزار)
            if (!empty($seo_title)) {
                update_post_meta($object_id, '_madenna_seo_title', sanitize_text_field($seo_title));
                update_post_meta($object_id, '_yoast_wpseo_title', sanitize_text_field($seo_title));
                update_post_meta($object_id, 'rank_math_title', sanitize_text_field($seo_title));
            }
            if (!empty($seo_desc)) {
                update_post_meta($object_id, '_madenna_seo_desc', sanitize_textarea_field($seo_desc));
                update_post_meta($object_id, '_yoast_wpseo_metadesc', sanitize_textarea_field($seo_desc));
                update_post_meta($object_id, 'rank_math_description', sanitize_textarea_field($seo_desc));
            }
        }
    }

    public static function register_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=tools',
            'دستیار جامع AI مادنا',
            '⚡ هوش مصنوعی مادنا',
            'manage_options',
            'madenna-ai-builder',
            [__CLASS__, 'render_admin_view']
        );
    }

    public static function render_admin_view() {
        // فعال‌سازی مدیا لایبرری برای باز شدن بدون خطای پنجره انتخاب تصویر
        wp_enqueue_media();

        $categories = get_terms([
            'taxonomy'   => 'madenna_cat',
            'hide_empty' => false,
        ]);

        $default_context = 'پلتفرم مادنا مرجع تخصصی ابزارهای آنلاین تعاملی، محاسباتی و مقالات راهنمای کاربردی است. تمام محتواها باید سئومحور، دقیق، ساده و با هویت بصری مدرن ارائه شوند.';
        $site_context = get_option('madenna_ai_site_context', $default_context);
        ?>
        <div class="wrap" style="direction: rtl; font-family: inherit; max-width: 980px; margin: 25px auto 40px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04);">
                
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 24px;">
                    <div>
                        <h1 style="font-size: 23px; font-weight: 800; color: #0f172a; margin: 0 0 6px;">🤖 استودیوی هوش مصنوعی اختصاصی مادنا</h1>
                        <p style="margin: 0; color: #64748b; font-size: 13px;">تولید، ویرایش و توسعه هوشمند ابزارها، مقالات و دسته‌بندی‌ها با تحلیل تصویر و ورکر چرخشی.</p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <span style="background: #eff6ff; color: #2563eb; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 20px; border: 1px solid #bfdbfe;">پشتیبانی از زیردسته</span>
                        <span style="background: #f0fdf4; color: #166534; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 20px; border: 1px solid #bbf7d0;">ویرایش هوشمند آیتم‌ها</span>
                    </div>
                </div>

                <!-- بخش اول: شناسنامه و کانتکست سایت -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; margin-bottom: 28px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label for="madenna_site_context_input" style="font-weight: 800; font-size: 14px; color: #1e293b;">
                            🌐 شناسنامه و زمینه کلی سایت مادنا (Site Persona & Context):
                        </label>
                        <button type="button" id="save_site_context_btn" class="button button-secondary" style="font-weight: 600;">
                            💾 ذخیره شناسنامه
                        </button>
                    </div>
                    <p style="margin: 0 0 10px; font-size: 12px; color: #64748b;">
                        توضیحات این بخش به تمام درخواست‌های هوش مصنوعی تزریق می‌شود تا مدل ماهیت و لحن مادنا را به درستی بشناسد.
                    </p>
                    <textarea id="madenna_site_context_input" rows="3" style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 10px; font-size: 13px; line-height: 1.6;"><?php echo esc_textarea($site_context); ?></textarea>
                    <div id="site_context_feedback" style="display:none; font-size:12px; font-weight:700; margin-top:8px;"></div>
                </div>

                <!-- فرم اصلی تولید و ویرایش آیتم -->
                <form id="madenna-ai-wizard-form">
                    <table class="form-table" style="margin-top: 0;">
                        <!-- حالت عملیات: ساخت جدید یا ویرایش موجود -->
                        <tr>
                            <th scope="row" style="width: 220px;"><label for="ai_operation_mode" style="font-weight: 700; color: #1e293b;">هدف عملیات:</label></th>
                            <td>
                                <select id="ai_operation_mode" name="operation_mode" style="min-width: 320px; height: 42px; border-radius: 8px; border-color: #cbd5e1; font-weight: 700; color: #2563eb;">
                                    <option value="create" selected>✨ ساخت آیتم جدید (تولید از ابتدا)</option>
                                    <option value="edit">✏️ ویرایش و ارتقای آیتم موجود (بروزرسانی هوشمند)</option>
                                </select>
                            </td>
                        </tr>

                        <!-- نوع موجودیت هدف -->
                        <tr>
                            <th scope="row"><label for="ai_target_type" style="font-weight: 700; color: #1e293b;">نوع موجودیت:</label></th>
                            <td>
                                <select id="ai_target_type" name="target_type" style="min-width: 320px; height: 42px; border-radius: 8px; border-color: #cbd5e1; font-weight: 600;">
                                    <option value="tools">🛠️ ابزار آنلاین تعاملی (HTML + CSS + JS)</option>
                                    <option value="articles">📚 مقاله تخصصی سئو شده (با اتصال خودکار به ابزارها)</option>
                                    <option value="categories">🗂️ دسته‌بندی و زیردسته‌ها (با رنگ‌ها، آیکون و FAQ)</option>
                                </select>
                            </td>
                        </tr>

                        <!-- ردیف انتخاب آیتم موجود در صورت حالت ویرایش -->
                        <tr id="existing_item_row" style="display: none;">
                            <th scope="row"><label for="ai_existing_item_id" style="font-weight: 700; color: #dc2626;">انتخاب آیتم جهت ویرایش:</label></th>
                            <td>
                                <select id="ai_existing_item_id" name="existing_item_id" style="min-width: 320px; height: 42px; border-radius: 8px; border-color: #f87171;">
                                    <option value="">-- در حال بارگذاری لیست آیتم‌ها... --</option>
                                </select>
                                <p class="description" style="margin-top: 6px; color: #64748b;">محتوا و کدهای فعلی این آیتم استخراج شده و توسط هوش مصنوعی بر اساس دستور شما ارتقا خواهد یافت.</p>
                            </td>
                        </tr>

                        <!-- فیلد انتخاب دسته‌بندی والد (مخصوص حالت ساخت زیردسته) -->
                        <tr id="parent_category_row" style="display: none;">
                            <th scope="row"><label for="ai_parent_category_id" style="font-weight: 700; color: #1e293b;">دسته والد (اختیاری):</label></th>
                            <td>
                                <select id="ai_parent_category_id" name="parent_category_id" style="min-width: 320px; height: 42px; border-radius: 8px; border-color: #cbd5e1;">
                                    <option value="0">-- بدون والد (ایجاد به عنوان دسته اصلی / ریشه) --</option>
                                    <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo esc_attr($cat->term_id); ?>">
                                                <?php echo esc_html($cat->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <p class="description" style="margin-top: 6px; color: #64748b;">در صورت انتخاب، دسته‌بندی‌های جدید به عنوان زیرمجموعه (زیردسته) این والد ثبت می‌شوند.</p>
                            </td>
                        </tr>

                        <!-- فیلد انتخاب دسته‌بندی برای ابزار و مقاله -->
                        <tr id="category_select_row">
                            <th scope="row"><label for="ai_category_id" style="font-weight: 700; color: #1e293b;">دسته‌بندی مربوطه:</label></th>
                            <td>
                                <select id="ai_category_id" name="category_id" style="min-width: 320px; height: 42px; border-radius: 8px; border-color: #cbd5e1;">
                                    <option value="">-- انتخاب دسته‌بندی مادنا --</option>
                                    <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo esc_attr($cat->term_id); ?>">
                                                <?php echo esc_html($cat->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <p class="description" style="margin-top: 6px; color: #64748b;">رنگ‌های اختصاصی و آیکون دسته به صورت خودکار به هوش مصنوعی تزریق می‌شوند.</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="ai_prompt" style="font-weight: 700; color: #1e293b;">دستور / پرامپت اختصاصی:</label></th>
                            <td>
                                <textarea id="ai_prompt" name="prompt" rows="5" style="width: 100%; border-radius: 10px; border-color: #cbd5e1; padding: 12px; line-height: 1.6;" placeholder="مثال ساخت: یک ابزار ماشین حساب محاسبه اقساط وام بانکی بساز.&#10;مثال ویرایش: دکمه پاک کردن فرم و جدول نمایش جزئیات اقساط ماهانه را به این ابزار اضافه کن.&#10;مثال زیردسته: سه زیردسته تخصصی برای این حوزه همراه با پالت رنگی بساز."></textarea>
                            </td>
                        </tr>

                        <!-- فیلد تصویر مرجع جهت تحلیل چندحالته -->
                        <tr>
                            <th scope="row"><label style="font-weight: 700; color: #1e293b;">پیوست تصویر مرجع (عکس):</label></th>
                            <td>
                                <input type="hidden" id="ai_file_url" name="file_url">
                                <input type="hidden" id="ai_attachment_id" name="attachment_id">
                                
                                <div id="ai_file_preview_box" style="display: none; margin-bottom: 12px; align-items: center; gap: 12px; background: #f8fafc; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; max-width: 420px;">
                                    <img id="ai_file_preview_img" src="" alt="پیش‌نمایش" style="width: 55px; height: 55px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0;">
                                    <div style="flex: 1; overflow: hidden;">
                                        <div id="ai_file_preview_name" style="font-size: 13px; font-weight: 700; color: #1e293b; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"></div>
                                        <span style="font-size: 11px; color: #64748b;">تصویر مرجع جهت تحلیل چندحالته هوش مصنوعی</span>
                                    </div>
                                    <button type="button" class="button-link-delete" id="ai_remove_file_btn" style="color: #ef4444; font-size: 12px; font-weight: 600; text-decoration: none;">حذف</button>
                                </div>

                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <button type="button" class="button" id="ai_select_file_btn" style="height: 38px; padding: 0 16px; border-radius: 8px; font-weight: 600;">
                                        🖼️ انتخاب یا آپلود تصویر مرجع
                                    </button>
                                </div>
                                <p class="description" style="margin-top: 6px; color: #64748b;">هوش مصنوعی این تصویر را بررسی و تحلیل می‌کند (طرح اولیه، جدول، اسکرین‌شات ابزار یا ساختار مدنظر).</p>

                                <!-- چک‌باکس مجزا برای تنظیم تصویر شاخص -->
                                <div style="margin-top: 10px;">
                                    <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #475569; cursor: pointer;">
                                        <input type="checkbox" id="ai_set_as_thumbnail" name="set_as_thumbnail" value="1">
                                        این تصویر به عنوان «تصویر شاخص» پست یا کاور دسته‌بندی نیز ثبت شود. (پیش‌فرض: خاموش، فقط جهت تحلیل)
                                    </label>
                                </div>
                            </td>
                        </tr>

                        <tr id="post_status_row">
                            <th scope="row"><label for="ai_post_status" style="font-weight: 700; color: #1e293b;">وضعیت پست:</label></th>
                            <td>
                                <select id="ai_post_status" name="post_status" style="min-width: 220px; height: 38px; border-radius: 8px; border-color: #cbd5e1;">
                                    <option value="draft" selected>پیش‌نویس (Draft) - جهت بررسی دستی</option>
                                    <option value="publish">انتشار مستقیم و عمومی (Publish)</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 16px;">
                        <button type="submit" id="ai_run_btn" class="button button-primary button-large" style="height: 46px; padding: 0 32px; font-size: 14px; font-weight: 800; border-radius: 8px; background: #2563eb; border-color: #1d4ed8;">
                            ⚡ اجرای هوش مصنوعی و اعمال تغییرات
                        </button>
                        <div id="ai_loading_indicator" style="display: none; align-items: center; gap: 10px; color: #2563eb; font-weight: 700;">
                            <span class="spinner is-active" style="float: none; margin: 0;"></span>
                            <span>در حال پردازش هوشمند و تحلیل دقیق تصویر... لطفاً چند لحظه شکیبا باشید (مهلت تا ۵ دقیقه).</span>
                        </div>
                    </div>
                </form>

                <!-- پیام بازخورد عملیات و گزارش تحلیل -->
                <div id="ai_result_message" style="display: none; margin-top: 24px; padding: 18px 20px; border-radius: 12px; font-size: 14px; line-height: 1.8;"></div>

            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            function updateFormVisibility() {
                var opMode     = $('#ai_operation_mode').val();
                var targetType = $('#ai_target_type').val();

                if (opMode === 'edit') {
                    $('#existing_item_row').show();
                    loadExistingItems(targetType);
                } else {
                    $('#existing_item_row').hide();
                }

                if (targetType === 'categories') {
                    $('#category_select_row').hide();
                    $('#post_status_row').hide();
                    if (opMode === 'create') {
                        $('#parent_category_row').show();
                    } else {
                        $('#parent_category_row').hide();
                    }
                } else {
                    $('#parent_category_row').hide();
                    $('#category_select_row').show();
                    $('#post_status_row').show();
                }
            }

            function loadExistingItems(type) {
                var select = $('#ai_existing_item_id');
                select.html('<option value="">-- در حال بارگذاری آیتم‌ها... --</option>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'madenna_ai_get_existing_items',
                        nonce: '<?php echo wp_create_nonce("madenna_ai_wizard_nonce"); ?>',
                        target_type: type
                    },
                    success: function(res) {
                        if (res.success && res.data.length > 0) {
                            var options = '<option value="">-- انتخاب آیتم جهت ویرایش و ارتقا --</option>';
                            res.data.forEach(function(item) {
                                options += '<option value="' + item.id + '">' + item.title + '</option>';
                            });
                            select.html(options);
                        } else {
                            select.html('<option value="">آیتمی برای نمایش یافت نشد</option>');
                        }
                    },
                    error: function() {
                        select.html('<option value="">خطا در واکشی آیتم‌ها</option>');
                    }
                });
            }

            $('#ai_operation_mode, #ai_target_type').on('change', function() {
                updateFormVisibility();
            });

            // ذخیره شناسنامه سایت با ایجکس
            $('#save_site_context_btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var feedback = $('#site_context_feedback');
                btn.prop('disabled', true).text('در حال ذخیره...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'madenna_ai_save_site_context',
                        nonce: '<?php echo wp_create_nonce("madenna_ai_wizard_nonce"); ?>',
                        site_context: $('#madenna_site_context_input').val()
                    },
                    success: function(res) {
                        btn.prop('disabled', false).text('💾 ذخیره شناسنامه');
                        if (res.success) {
                            feedback.css('color', '#16a34a').text('✓ شناسنامه سایت ذخیره شد.').fadeIn().delay(3000).fadeOut();
                        } else {
                            feedback.css('color', '#dc2626').text('خطا در ذخیره شناسنامه: ' + (res.data || '')).fadeIn();
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).text('💾 ذخیره شناسنامه');
                        feedback.css('color', '#dc2626').text('ارتباط با سرور وردپرس برقرار نشد.').fadeIn();
                    }
                });
            });

            // باز کردن مدیا لایبرری وردپرس منحصراً برای تصویر
            var mediaFrame;
            $('#ai_select_file_btn').on('click', function(e) {
                e.preventDefault();

                if (mediaFrame) {
                    mediaFrame.open();
                    return;
                }

                mediaFrame = wp.media({
                    title: 'انتخاب تصویر مرجع برای هوش مصنوعی',
                    button: { text: 'استفاده از این تصویر' },
                    multiple: false,
                    library: { type: 'image' }
                });

                mediaFrame.on('select', function() {
                    var attachment = mediaFrame.state().get('selection').first().toJSON();
                    $('#ai_file_url').val(attachment.url);
                    $('#ai_attachment_id').val(attachment.id);

                    var displayName = attachment.filename || attachment.title || 'تصویر مرجع';
                    $('#ai_file_preview_name').text(displayName);
                    $('#ai_file_preview_img').attr('src', attachment.url);
                    $('#ai_file_preview_box').css('display', 'flex');
                });

                mediaFrame.open();
            });

            // حذف تصویر پیوست
            $('#ai_remove_file_btn').on('click', function(e) {
                e.preventDefault();
                $('#ai_file_url').val('');
                $('#ai_attachment_id').val('');
                $('#ai_file_preview_box').hide();
                $('#ai_set_as_thumbnail').prop('checked', false);
            });

            // ارسال درخواست به بک‌اند وردپرس
            $('#madenna-ai-wizard-form').on('submit', function(e) {
                e.preventDefault();

                var opMode         = $('#ai_operation_mode').val();
                var targetType     = $('#ai_target_type').val();
                var existingItemId = $('#ai_existing_item_id').val();
                var categoryId     = $('#ai_category_id').val();
                var parentCatId    = $('#ai_parent_category_id').val();
                var promptVal      = $('#ai_prompt').val().trim();
                var resultBox      = $('#ai_result_message');

                if (opMode === 'edit' && !existingItemId) {
                    resultBox.css({
                        'display': 'block',
                        'background': '#fef2f2',
                        'border': '1px solid #fca5a5',
                        'color': '#991b1b'
                    }).html('⚠️ لطفاً آیتمی که قصد ویرایش آن را دارید انتخاب کنید.');
                    return;
                }

                if (opMode === 'create' && targetType !== 'categories' && !categoryId) {
                    resultBox.css({
                        'display': 'block',
                        'background': '#fef2f2',
                        'border': '1px solid #fca5a5',
                        'color': '#991b1b'
                    }).html('⚠️ لطفاً ابتدا یک دسته‌بندی انتخاب کنید.');
                    return;
                }

                if (!promptVal && !$('#ai_file_url').val()) {
                    resultBox.css({
                        'display': 'block',
                        'background': '#fef2f2',
                        'border': '1px solid #fca5a5',
                        'color': '#991b1b'
                    }).html('⚠️ لطفاً حداقل یک دستور متنی بنویسید یا یک تصویر مرجع انتخاب کنید.');
                    return;
                }

                $('#ai_run_btn').prop('disabled', true);
                $('#ai_loading_indicator').css('display', 'flex');
                resultBox.hide();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    timeout: 300000, // ۵ دقیقه تایم‌اوت در ایجکس
                    dataType: 'json',
                    data: {
                        action: 'madenna_ai_generate_action',
                        nonce: '<?php echo wp_create_nonce("madenna_ai_wizard_nonce"); ?>',
                        operation_mode: opMode,
                        target_type: targetType,
                        existing_item_id: existingItemId,
                        category_id: categoryId,
                        parent_category_id: parentCatId,
                        prompt: promptVal,
                        file_url: $('#ai_file_url').val(),
                        attachment_id: $('#ai_attachment_id').val(),
                        set_as_thumbnail: $('#ai_set_as_thumbnail').is(':checked') ? 1 : 0,
                        post_status: $('#ai_post_status').val()
                    },
                    success: function(res) {
                        $('#ai_run_btn').prop('disabled', false);
                        $('#ai_loading_indicator').hide();

                        if (res.success) {
                            var linksHtml = '';
                            if (res.data.links && res.data.links.length > 0) {
                                linksHtml = '<div style="margin-top:14px; display:flex; flex-wrap:wrap; gap:10px;">';
                                res.data.links.forEach(function(l) {
                                    linksHtml += '<a href="' + l.url + '" class="button button-primary" target="_blank">' + l.title + ' ←</a>';
                                });
                                linksHtml += '</div>';
                            }

                            var analysisHtml = '';
                            if (res.data.analysis_report) {
                                var rep = res.data.analysis_report;
                                analysisHtml = '<div style="margin-top:16px; background:#ffffff; border:1px solid #cbd5e1; border-radius:10px; padding:14px 18px;">' +
                                    '<details open><summary style="font-weight:800; cursor:pointer; color:#1e293b; font-size:13px;">🔍 مشاهده گزارش تحلیل و ارزیابی هوش مصنوعی (AI Audit Report)</summary>' +
                                    '<div style="margin-top:10px; font-size:12px; color:#475569; line-height:1.7;">';
                                if (rep.vision_summary) {
                                    analysisHtml += '<p style="margin:4px 0;"><strong>🖼️ استخراج و تحلیل تصویر:</strong> ' + rep.vision_summary + '</p>';
                                }
                                if (rep.structure_logic) {
                                    analysisHtml += '<p style="margin:4px 0;"><strong>🗂️ ساختار و زیردسته‌ها:</strong> ' + rep.structure_logic + '</p>';
                                }
                                if (rep.seo_strategy) {
                                    analysisHtml += '<p style="margin:4px 0;"><strong>📈 استراتژی سئو و اتصالات:</strong> ' + rep.seo_strategy + '</p>';
                                }
                                if (rep.tech_notes) {
                                    analysisHtml += '<p style="margin:4px 0;"><strong>⚙️ نکات فنی و محاسباتی:</strong> ' + rep.tech_notes + '</p>';
                                }
                                analysisHtml += '</div></details></div>';
                            }

                            resultBox
                                .css({
                                    'display': 'block',
                                    'background': '#f0fdf4',
                                    'border': '1px solid #86efac',
                                    'color': '#166534'
                                })
                                .html('🎉 <strong>عملیات با موفقیت انجام شد!</strong><br>' + res.data.message + linksHtml + analysisHtml);
                        } else {
                            resultBox
                                .css({
                                    'display': 'block',
                                    'background': '#fef2f2',
                                    'border': '1px solid #fca5a5',
                                    'color': '#991b1b'
                                })
                                .html('⚠️ <strong>خطا:</strong> ' + (res.data || 'خطای نامشخص در پاسخ هوش مصنوعی.'));
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#ai_run_btn').prop('disabled', false);
                        $('#ai_loading_indicator').hide();
                        var errText = (status === 'timeout') ? 'زمان درخواست طولانی شد (Timeout). لطفاً مجدداً امتحان کنید.' : error;
                        resultBox
                            .css({
                                'display': 'block',
                                'background': '#fef2f2',
                                'border': '1px solid #fca5a5',
                                'color': '#991b1b'
                            })
                            .html('⚠️ ارتباط با سرور برقرار نشد: ' + errText);
                    }
                });
            });
        });
        </script>
        <?php
    }

    public static function handle_get_existing_items() {
        check_ajax_referer('madenna_ai_wizard_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('دسترسی غیرمجاز.');
        }

        $type = sanitize_text_field($_POST['target_type'] ?? 'tools');
        $items = [];

        if ($type === 'categories') {
            $terms = get_terms([
                'taxonomy'   => 'madenna_cat',
                'hide_empty' => false,
            ]);
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $t) {
                    $prefix = $t->parent ? '↳ ' : '';
                    $items[] = [
                        'id'    => $t->term_id,
                        'title' => $prefix . $t->name . ' (' . $t->slug . ')'
                    ];
                }
            }
        } else {
            $posts = get_posts([
                'post_type'      => in_array($type, ['tools', 'articles'], true) ? $type : 'tools',
                'posts_per_page' => 150,
                'post_status'    => ['publish', 'draft'],
                'orderby'        => 'date',
                'order'          => 'DESC'
            ]);
            if (!empty($posts)) {
                foreach ($posts as $p) {
                    $items[] = [
                        'id'    => $p->ID,
                        'title' => $p->post_title . ($p->post_status === 'draft' ? ' (پیش‌نویس)' : '')
                    ];
                }
            }
        }

        wp_send_json_success($items);
    }

    public static function handle_save_site_context() {
        check_ajax_referer('madenna_ai_wizard_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز است.');
        }

        $context = sanitize_textarea_field($_POST['site_context'] ?? '');
        update_option('madenna_ai_site_context', $context);
        wp_send_json_success();
    }

    public static function handle_ajax_generation() {
        check_ajax_referer('madenna_ai_wizard_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('شما اجازه دسترسی به این بخش را ندارید.');
        }

        // افزایش سقف زمان اجرای اسکریپت PHP برای کارهای سنگین چندرسانه‌ای
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $operation_mode   = sanitize_text_field($_POST['operation_mode'] ?? 'create');
        $target_type      = sanitize_text_field($_POST['target_type'] ?? 'tools');
        $existing_item_id = intval($_POST['existing_item_id'] ?? 0);
        $category_id      = intval($_POST['category_id'] ?? 0);
        $parent_cat_id    = intval($_POST['parent_category_id'] ?? 0);
        $user_prompt      = sanitize_textarea_field($_POST['prompt'] ?? '');
        $file_url         = esc_url_raw($_POST['file_url'] ?? '');
        $attachment_id    = intval($_POST['attachment_id'] ?? 0);
        $set_as_thumbnail = !empty($_POST['set_as_thumbnail']) && $_POST['set_as_thumbnail'] == '1';
        $post_status      = in_array($_POST['post_status'] ?? '', ['publish', 'draft'], true) ? $_POST['post_status'] : 'draft';

        $site_context = get_option('madenna_ai_site_context', '');

        // واکشی اطلاعات دسته والد در صورتی که والد انتخاب شده باشد
        $parent_category_info = null;
        if ($parent_cat_id > 0) {
            $p_term = get_term($parent_cat_id, 'madenna_cat');
            if ($p_term && !is_wp_error($p_term)) {
                $parent_category_info = [
                    'id'          => $p_term->term_id,
                    'name'        => $p_term->name,
                    'slug'        => $p_term->slug,
                    'description' => term_description($p_term->term_id, 'madenna_cat'),
                ];
            }
        }

        // واکشی داده‌های فعلی آیتم در صورت حالت ویرایش
        $existing_data = null;
        if ($operation_mode === 'edit' && $existing_item_id > 0) {
            if ($target_type === 'categories') {
                $term = get_term($existing_item_id, 'madenna_cat');
                if ($term && !is_wp_error($term)) {
                    $existing_data = [
                        'id'               => $term->term_id,
                        'name'             => $term->name,
                        'slug'             => $term->slug,
                        'subtitle'         => get_term_meta($term->term_id, 'cat_subtitle', true),
                        'excerpt'          => get_term_meta($term->term_id, 'cat_excerpt', true),
                        'rich_description' => term_description($term->term_id, 'madenna_cat'),
                        'icon_class'       => get_term_meta($term->term_id, 'cat_icon_class', true),
                        'color_primary'    => get_term_meta($term->term_id, 'cat_color_primary', true),
                        'color_secondary'  => get_term_meta($term->term_id, 'cat_color_secondary', true),
                        'color_accent'     => get_term_meta($term->term_id, 'cat_color_accent', true),
                        'faqs'             => get_term_meta($term->term_id, 'cat_faqs', true)
                    ];
                }
            } elseif ($target_type === 'articles') {
                $post = get_post($existing_item_id);
                if ($post && $post->post_type === 'articles') {
                    $existing_data = [
                        'id'           => $post->ID,
                        'title'        => $post->post_title,
                        'content'      => $post->post_content,
                        'excerpt'      => $post->post_excerpt,
                        'subtitle'     => get_post_meta($post->ID, '_article_subtitle', true),
                        'reading_time' => get_post_meta($post->ID, '_article_reading_time', true),
                        'icon_class'   => get_post_meta($post->ID, '_article_icon_class', true),
                        'faqs'         => get_post_meta($post->ID, '_article_faqs', true)
                    ];
                }
            } else {
                $post = get_post($existing_item_id);
                if ($post && $post->post_type === 'tools') {
                    $existing_data = [
                        'id'              => $post->ID,
                        'title'           => $post->post_title,
                        'subtitle'        => get_post_meta($post->ID, '_tool_subtitle', true),
                        'libraries'       => get_post_meta($post->ID, '_tool_libraries', true),
                        'html'            => get_post_meta($post->ID, '_tool_html', true),
                        'css'             => get_post_meta($post->ID, '_tool_css', true),
                        'js'              => get_post_meta($post->ID, '_tool_js', true),
                        'icon_class'      => get_post_meta($post->ID, '_tool_icon_class', true),
                        'color_primary'   => get_post_meta($post->ID, '_tool_color_primary', true),
                        'color_secondary' => get_post_meta($post->ID, '_tool_color_secondary', true),
                        'color_accent'    => get_post_meta($post->ID, '_tool_color_accent', true),
                        'faqs'            => get_post_meta($post->ID, '_tool_faqs', true)
                    ];
                }
            }
        }

        $site_knowledge = self::get_full_site_knowledge();

        // ۱. واکشی اطلاعات دسته‌بندی
        $category_info = null;
        if ($target_type !== 'categories' && $category_id > 0) {
            $term = get_term($category_id, 'madenna_cat');
            if ($term && !is_wp_error($term)) {
                $category_info = [
                    'name'            => $term->name,
                    'slug'            => $term->slug,
                    'subtitle'        => get_term_meta($category_id, 'cat_subtitle', true),
                    'description'     => term_description($category_id, 'madenna_cat'),
                    'color_primary'   => get_term_meta($category_id, 'cat_color_primary', true) ?: '#2563eb',
                    'color_secondary' => get_term_meta($category_id, 'cat_color_secondary', true) ?: '#3b82f6',
                    'color_accent'    => get_term_meta($category_id, 'cat_color_accent', true) ?: '#eff6ff',
                ];
            }
        }

        // ۲. واکشی آیتم‌های موجود در دیتابیس برای اتصال هوشمند دوطرفه
        $db_items = [];
        if ($target_type === 'articles') {
            $tools = get_posts([
                'post_type'      => 'tools',
                'posts_per_page' => 100,
                'post_status'    => 'publish'
            ]);
            if (!empty($tools)) {
                foreach ($tools as $t) {
                    $db_items[] = [
                        'id'       => $t->ID,
                        'title'    => $t->post_title,
                        'subtitle' => get_post_meta($t->ID, '_tool_subtitle', true)
                    ];
                }
            }
        } elseif ($target_type === 'tools') {
            $articles = get_posts([
                'post_type'      => 'articles',
                'posts_per_page' => 100,
                'post_status'    => 'publish'
            ]);
            if (!empty($articles)) {
                foreach ($articles as $a) {
                    $db_items[] = [
                        'id'       => $a->ID,
                        'title'    => $a->post_title,
                        'subtitle' => get_post_meta($a->ID, '_article_subtitle', true)
                    ];
                }
            }
        }

        $image_base64 = null;
        $mime_type    = 'image/jpeg';

        if ($attachment_id > 0) {
            $attached_path = get_attached_file($attachment_id);
            if ($attached_path && file_exists($attached_path)) {
                $image_base64 = base64_encode(file_get_contents($attached_path));
                $mime_type    = get_post_mime_type($attachment_id) ?: 'image/jpeg';
            }
        } elseif (!empty($file_url)) {
            $upload_info = wp_upload_dir();
            $file_path   = str_replace($upload_info['baseurl'], $upload_info['basedir'], $file_url);

            if (file_exists($file_path)) {
                $image_base64 = base64_encode(file_get_contents($file_path));
                $mime_type    = mime_content_type($file_path) ?: 'image/jpeg';
            }
        }

        // ۴. ارسال داده به ورکر کلودفلر با مهلت ۳۰۰ ثانیه‌ای
        $response = wp_remote_post(self::$worker_endpoint, [
            'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
            'body'    => wp_json_encode([
                'action_mode'     => $operation_mode,
                'post_type'       => $target_type,
                'prompt'          => $user_prompt,
                'category'        => $category_info,
                'parent_category' => $parent_category_info,
                'site_context'    => $site_context,
                'site_knowledge'  => $site_knowledge, // انتقال دانش دیتابیس و رنگ‌ها
                'db_items'        => $db_items,
                'existing_data'   => $existing_data,
                'image_base64'    => $image_base64,
                'mime_type'       => $mime_type
            ]),
            'timeout' => 300
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error('خطای اتصال به ورکر کلودفلر: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $raw_body    = wp_remote_retrieve_body($response);
        $ai_data     = json_decode($raw_body, true);

        if ($status_code !== 200 || !$ai_data) {
            $err_msg = isset($ai_data['error']) ? $ai_data['error'] : 'پاسخ هوش مصنوعی معتبر نبود.';
            if (isset($ai_data['details'])) {
                $err_msg .= ' (' . $ai_data['details'] . ')';
            }
            wp_send_json_error($err_msg);
        }

        $analysis_report = $ai_data['analysis_report'] ?? null;

        if ($target_type === 'categories') {
            $created_cats = [];
            $categories_list = isset($ai_data['categories']) && is_array($ai_data['categories']) ? $ai_data['categories'] : [$ai_data];
            $slug_to_id_map = [];

            // اگر والد قبلاً از فرم انتخاب شده بود، شناسه آن را داریم
            $default_parent_id = ($parent_cat_id > 0) ? $parent_cat_id : 0;

            foreach ($categories_list as $cat_data) {
                $cat_name = sanitize_text_field($cat_data['name'] ?? 'دسته‌بندی جدید');
                $cat_slug = sanitize_title($cat_data['slug'] ?? $cat_name);

                // تعیین والد: اگر parent_slug در خروجی آمده بود یا والد انتخابی فرم وجود داشت
                $computed_parent_id = $default_parent_id;
                if (!empty($cat_data['parent_slug']) && isset($slug_to_id_map[$cat_data['parent_slug']])) {
                    $computed_parent_id = $slug_to_id_map[$cat_data['parent_slug']];
                } elseif (!empty($cat_data['parent_slug'])) {
                    $existing_p = get_term_by('slug', sanitize_title($cat_data['parent_slug']), 'madenna_cat');
                    if ($existing_p && !is_wp_error($existing_p)) {
                        $computed_parent_id = $existing_p->term_id;
                    }
                }

                if ($operation_mode === 'edit' && $existing_item_id > 0) {
                    $term_id = $existing_item_id;
                    wp_update_term($term_id, 'madenna_cat', [
                        'name'        => $cat_name,
                        'description' => wp_kses_post($cat_data['rich_description'] ?? '')
                    ]);
                } else {
                    $inserted_term = wp_insert_term($cat_name, 'madenna_cat', [
                        'slug'        => $cat_slug,
                        'parent'      => $computed_parent_id,
                        'description' => wp_kses_post($cat_data['rich_description'] ?? '')
                    ]);

                    if (is_wp_error($inserted_term)) {
                        $term_id = $inserted_term->get_error_data();
                    } else {
                        $term_id = $inserted_term['term_id'] ?? 0;
                    }
                }

                if ($term_id) {
                    $slug_to_id_map[$cat_slug] = $term_id;

                    update_term_meta($term_id, 'cat_subtitle', sanitize_text_field($cat_data['subtitle'] ?? ''));
                    update_term_meta($term_id, 'cat_excerpt', sanitize_textarea_field($cat_data['excerpt'] ?? ''));
                    update_term_meta($term_id, 'cat_icon_class', sanitize_text_field($cat_data['icon_class'] ?? 'fa-solid fa-folder-open'));
                    update_term_meta($term_id, 'cat_color_primary', sanitize_hex_color($cat_data['color_primary'] ?? '#2563eb'));
                    update_term_meta($term_id, 'cat_color_secondary', sanitize_hex_color($cat_data['color_secondary'] ?? '#3b82f6'));
                    update_term_meta($term_id, 'cat_color_accent', sanitize_hex_color($cat_data['color_accent'] ?? '#eff6ff'));
                    update_term_meta($term_id, 'cat_icon_color', sanitize_hex_color($cat_data['icon_color'] ?? '#2563eb'));
                    update_term_meta($term_id, 'cat_icon_bg', sanitize_hex_color($cat_data['icon_bg'] ?? '#eff6ff'));

                    self::save_seo_meta_data($term_id, 'term', $cat_data['seo_title'] ?? '', $cat_data['seo_description'] ?? '');

                    if (!empty($cat_data['faqs']) && is_array($cat_data['faqs'])) {
                        $clean_faqs = [];
                        foreach ($cat_data['faqs'] as $faq) {
                            if (!empty($faq['q']) && !empty($faq['a'])) {
                                $clean_faqs[] = ['q' => sanitize_text_field($faq['q']), 'a' => wp_kses_post($faq['a'])];
                            }
                        }
                        update_term_meta($term_id, 'cat_faqs', $clean_faqs);
                    }

                    if ($set_as_thumbnail && $attachment_id > 0) {
                        update_term_meta($term_id, 'cat_image', $file_url);
                    }

                    $action_title = ($operation_mode === 'edit') ? 'ویرایش دسته: ' : 'مشاهده/ویرایش دسته: ';
                    $created_cats[] = [
                        'title' => $action_title . $cat_name . ($computed_parent_id > 0 ? ' (زیردسته)' : ''),
                        'url'   => admin_url("term.php?taxonomy=madenna_cat&tag_ID={$term_id}&post_type=tools")
                    ];
                }
            }

            $success_msg = ($operation_mode === 'edit') ? 'دسته‌بندی با موفقیت ویرایش و ارتقا یافت.' : count($created_cats) . ' دسته‌بندی/زیردسته با موفقیت ساخته شد.';
            wp_send_json_success([
                'message'         => $success_msg,
                'links'           => $created_cats,
                'analysis_report' => $analysis_report
            ]);

        } elseif ($target_type === 'articles') {
            $post_title   = sanitize_text_field($ai_data['title'] ?? 'مقاله');
            $post_content = wp_kses_post($ai_data['content'] ?? '');
            $post_excerpt = sanitize_textarea_field($ai_data['excerpt'] ?? '');

            if ($operation_mode === 'edit' && $existing_item_id > 0) {
                $post_id = $existing_item_id;
                wp_update_post([
                    'ID'           => $post_id,
                    'post_title'   => $post_title,
                    'post_content' => $post_content,
                    'post_excerpt' => $post_excerpt,
                ]);
            } else {
                $post_id = wp_insert_post([
                    'post_type'    => 'articles',
                    'post_title'   => $post_title,
                    'post_content' => $post_content,
                    'post_excerpt' => $post_excerpt,
                    'post_status'  => $post_status,
                ]);
            }

            if (is_wp_error($post_id)) {
                wp_send_json_error($post_id->get_error_message());
            }

            update_post_meta($post_id, '_article_subtitle', sanitize_text_field($ai_data['subtitle'] ?? ''));
            update_post_meta($post_id, '_article_reading_time', absint($ai_data['reading_time'] ?? 5));
            update_post_meta($post_id, '_article_icon_class', sanitize_text_field($ai_data['icon_class'] ?? 'fa-solid fa-file-lines'));

            self::save_seo_meta_data($post_id, 'post', $ai_data['seo_title'] ?? '', $ai_data['seo_description'] ?? '');

            if (!empty($ai_data['faqs']) && is_array($ai_data['faqs'])) {
                $clean_faqs = [];
                foreach ($ai_data['faqs'] as $faq) {
                    if (!empty($faq['q']) && !empty($faq['a'])) {
                        $clean_faqs[] = ['q' => sanitize_text_field($faq['q']), 'a' => wp_kses_post($faq['a'])];
                    }
                }
                update_post_meta($post_id, '_article_faqs', $clean_faqs);
            }

            // اتصال دوطرفه به ابزارها
            $related_tools = isset($ai_data['related_tool_ids']) && is_array($ai_data['related_tool_ids']) ? array_map('intval', $ai_data['related_tool_ids']) : [];
            if (!empty($related_tools)) {
                update_post_meta($post_id, '_related_tools', $related_tools);
                foreach ($related_tools as $tid) {
                    $cur = get_post_meta($tid, '_related_articles', true);
                    $cur = is_array($cur) ? $cur : [];
                    if (!in_array($post_id, $cur, true)) {
                        $cur[] = $post_id;
                        update_post_meta($tid, '_related_articles', array_unique($cur));
                    }
                }
            }

            if ($category_id > 0) {
                wp_set_object_terms($post_id, [$category_id], 'madenna_cat');
            }

            if ($set_as_thumbnail && $attachment_id > 0) {
                set_post_thumbnail($post_id, $attachment_id);
            }

            $success_text = ($operation_mode === 'edit') ? 'مقاله با موفقیت ارتقا و ویرایش یافت.' : 'مقاله جدید ساخته شد و ارتباط با ابزارهای مرتبط برقرار گردید.';
            wp_send_json_success([
                'message'         => $success_text,
                'links'           => [
                    ['title' => 'ویرایش مقاله در وردپرس', 'url' => admin_url("post.php?post={$post_id}&action=edit")],
                    ['title' => 'مشاهده پیش‌نمایش', 'url' => get_permalink($post_id)]
                ],
                'analysis_report' => $analysis_report
            ]);

        } else {
            // ساخت یا ویرایش ابزار (tools)
            $post_title = sanitize_text_field($ai_data['title'] ?? 'ابزار بدون عنوان');

            if ($operation_mode === 'edit' && $existing_item_id > 0) {
                $post_id = $existing_item_id;
                wp_update_post([
                    'ID'         => $post_id,
                    'post_title' => $post_title,
                ]);
            } else {
                $post_id = wp_insert_post([
                    'post_type'    => 'tools',
                    'post_title'   => $post_title,
                    'post_status'  => $post_status,
                ]);
            }

            if (is_wp_error($post_id)) {
                wp_send_json_error($post_id->get_error_message());
            }

            update_post_meta($post_id, '_tool_subtitle', sanitize_text_field($ai_data['subtitle'] ?? ''));
            update_post_meta($post_id, '_tool_icon_class', sanitize_text_field($ai_data['icon_class'] ?? 'fa-solid fa-wrench'));
            update_post_meta($post_id, '_tool_color_primary', sanitize_hex_color($ai_data['color_primary'] ?? ($category_info['color_primary'] ?? '#2563eb')));
            update_post_meta($post_id, '_tool_color_secondary', sanitize_hex_color($ai_data['color_secondary'] ?? ($category_info['color_secondary'] ?? '#3b82f6')));
            update_post_meta($post_id, '_tool_color_accent', sanitize_hex_color($ai_data['color_accent'] ?? ($category_info['color_accent'] ?? '#eff6ff')));
            update_post_meta($post_id, '_tool_force_site_font', 'yes');

            self::save_seo_meta_data($post_id, 'post', $ai_data['seo_title'] ?? '', $ai_data['seo_description'] ?? '');

            $libs = isset($ai_data['libraries']) && is_array($ai_data['libraries']) ? $ai_data['libraries'] : ['tailwind'];
            update_post_meta($post_id, '_tool_libraries', array_map('sanitize_text_field', $libs));

            $html = isset($ai_data['html']) ? wp_unslash($ai_data['html']) : '';
            $css  = isset($ai_data['css']) ? wp_unslash($ai_data['css']) : '';
            $js   = isset($ai_data['js']) ? wp_unslash($ai_data['js']) : '';

            update_post_meta($post_id, '_tool_html', $html);
            update_post_meta($post_id, '_tool_css', $css);
            update_post_meta($post_id, '_tool_js', $js);

            if (!empty($ai_data['faqs']) && is_array($ai_data['faqs'])) {
                $clean_faqs = [];
                foreach ($ai_data['faqs'] as $faq) {
                    if (!empty($faq['q']) && !empty($faq['a'])) {
                        $clean_faqs[] = ['q' => sanitize_text_field($faq['q']), 'a' => wp_kses_post($faq['a'])];
                    }
                }
                update_post_meta($post_id, '_tool_faqs', $clean_faqs);
            }

            $related_articles = isset($ai_data['related_article_ids']) && is_array($ai_data['related_article_ids']) ? array_map('intval', $ai_data['related_article_ids']) : [];
            if (!empty($related_articles)) {
                update_post_meta($post_id, '_related_articles', $related_articles);
                foreach ($related_articles as $aid) {
                    $cur = get_post_meta($aid, '_related_tools', true);
                    $cur = is_array($cur) ? $cur : [];
                    if (!in_array($post_id, $cur, true)) {
                        $cur[] = $post_id;
                        update_post_meta($aid, '_related_tools', array_unique($cur));
                    }
                }
            }

            // ذخیره و مینیفای مجدد فایل‌های کش‌شده فیزیکی ابزار
            $upload_dir = wp_upload_dir();
            $tools_dir  = $upload_dir['basedir'] . '/madenna-tools';
            if (!file_exists($tools_dir)) {
                wp_mkdir_p($tools_dir);
            }

            $min_css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
            $min_css = str_replace(["\r\n", "\r", "\n", "\t"], '', $min_css);
            $min_js  = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
            $min_js  = "(function(){\n" . trim($min_js) . "\n})();";

            @file_put_contents("{$tools_dir}/tool-{$post_id}.css", $min_css);
            @file_put_contents("{$tools_dir}/tool-{$post_id}.js", $min_js);

            if ($category_id > 0) {
                wp_set_object_terms($post_id, [$category_id], 'madenna_cat');
            }

            if ($set_as_thumbnail && $attachment_id > 0) {
                set_post_thumbnail($post_id, $attachment_id);
            }

            $tool_success_text = ($operation_mode === 'edit') ? 'ابزار آنلاین با موفقیت ویرایش و کدهای فرانت آن بروزرسانی شد.' : 'ابزار آنلاین جدید با کدهای فرانت‌اند و اتصال به دیتابیس ساخته شد.';
            wp_send_json_success([
                'message'         => $tool_success_text,
                'links'           => [
                    ['title' => 'ویرایش ابزار در پیشخوان', 'url' => admin_url("post.php?post={$post_id}&action=edit")],
                    ['title' => 'پیش‌نمایش زنده ابزار', 'url' => get_permalink($post_id)]
                ],
                'analysis_report' => $analysis_report
            ]);
        }
    }
}

// مقداردهی اولیه ماژول
Madenna_AI_Wizard::init();