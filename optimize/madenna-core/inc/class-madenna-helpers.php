<?php
/**
 * توابع کمکی (Helpers) برای استفاده در سراسر افزونه
 * Location: /inc/class-madenna-helpers.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Helpers {

    public static function get_available_tool_libraries() {
        return [
            'tailwind' => ['name' => 'Tailwind CSS (CDN)', 'js' => 'https://cdn.tailwindcss.com', 'css' => ''],
            'chartjs'  => ['name' => 'Chart.js (رسم نمودار)', 'js' => 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', 'css' => ''],
            'mathjs'   => ['name' => 'Math.js (محاسبات ریاضی)', 'js' => 'https://cdnjs.cloudflare.com/ajax/libs/mathjs/12.4.0/math.js', 'css' => ''],
            'confetti' => ['name' => 'Canvas Confetti (افکت جشن)', 'js' => 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js', 'css' => ''],
            'jspdf'    => ['name' => 'jsPDF (خروجی PDF)', 'js' => 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', 'css' => ''],
            'anime'    => ['name' => 'Anime.js (انیمیشن)', 'js' => 'https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.2/anime.min.js', 'css' => ''],
        ];
    }

    public static function get_elementor_global_colors() {
        $colors = [];
        $kit_id = get_option('elementor_active_kit');
        if ($kit_id) {
            $kit_settings = get_post_meta($kit_id, '_elementor_page_settings', true);
            if (is_array($kit_settings)) {
                $sys = (isset($kit_settings['system_colors']) && is_array($kit_settings['system_colors'])) ? $kit_settings['system_colors'] : [];
                $cus = (isset($kit_settings['custom_colors']) && is_array($kit_settings['custom_colors'])) ? $kit_settings['custom_colors'] : [];
                foreach (array_merge($sys, $cus) as $c) {
                    if (!empty($c['color'])) $colors[] = ['title' => $c['title'] ?? 'رنگ', 'color' => $c['color']];
                }
            }
        }
        return $colors;
    }

    public static function render_elementor_palette_swatches($target_input_name) {
        $colors = self::get_elementor_global_colors();
        if (empty($colors)) return;
        echo '<div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; align-items:center;"><span style="font-size:11px; color:#475569; font-weight:600;">پالت المنتور:</span>';
        foreach ($colors as $c) {
            $hex = esc_attr($c['color']);
            echo sprintf('<button type="button" class="madenna-palette-swatch" data-target="%s" data-color="%s" title="%s" style="width:24px; height:24px; border-radius:50%%; background:%s; border:2px solid #fff; box-shadow:0 0 0 1px #cbd5e1; cursor:pointer; outline:none;"></button>', esc_attr($target_input_name), $hex, esc_attr($c['title']), $hex);
        }
        echo '</div>';
    }

    public static function render_icon_element($icon_class, $style = '') {
        if (empty($icon_class)) return '';
        if (strpos($icon_class, 'fa-') !== false) return '<i class="' . esc_attr($icon_class) . '" style="' . esc_attr($style) . '"></i>';
        return '<span class="dashicons ' . esc_attr($icon_class) . '" style="' . esc_attr($style) . '"></span>';
    }

    public static function render_icon_and_svg_picker($field_icon_name, $current_icon_val, $field_svg_name, $current_svg_val, $title = 'آیکون یا SVG اختصاصی') {
        ?>
        <div class="madenna-icon-picker-box" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:15px; margin:12px 0;">
            <div style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:10px;">✨ <?php echo esc_html($title); ?></div>
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12px; color:#475569; font-weight:600; margin-bottom:6px;">روش ۱: انتخاب از کتابخانه آیکون‌ها:</label>
                <input type="hidden" name="<?php echo esc_attr($field_icon_name); ?>" id="<?php echo esc_attr($field_icon_name); ?>" value="<?php echo esc_attr($current_icon_val); ?>">
                <div style="display:flex; gap:12px; align-items:center;">
                    <div style="width:48px; height:48px; border-radius:10px; background:#fff; border:1px solid #cbd5e1; display:flex; align-items:center; justify-content:center; font-size:22px; color:#2563eb;" id="preview_<?php echo esc_attr($field_icon_name); ?>">
                        <?php echo !empty($current_icon_val) ? self::render_icon_element($current_icon_val) : '<i class="fa-solid fa-icons" style="color:#94a3b8; font-size:20px;"></i>'; ?>
                    </div>
                    <button type="button" class="button button-secondary madenna-open-icon-modal" data-target="<?php echo esc_attr($field_icon_name); ?>" data-preview="preview_<?php echo esc_attr($field_icon_name); ?>" style="height:36px; padding:0 14px; font-weight:600;">انتخاب آیکون</button>
                    <?php if (!empty($current_icon_val)): ?><button type="button" class="button-link-delete madenna-clear-icon-btn" data-target="<?php echo esc_attr($field_icon_name); ?>" data-preview="preview_<?php echo esc_attr($field_icon_name); ?>" style="color:#ef4444;">حذف</button><?php endif; ?>
                </div>
            </div>
            <div style="border-top:1px dashed #cbd5e1; padding-top:12px;">
                <label style="display:block; font-size:12px; color:#475569; font-weight:600; margin-bottom:6px;">روش ۲: آپلود فایل SVG:</label>
                <input type="hidden" name="<?php echo esc_attr($field_svg_name); ?>" id="<?php echo esc_attr($field_svg_name); ?>" value="<?php echo esc_attr($current_svg_val); ?>">
                <div id="preview_<?php echo esc_attr($field_svg_name); ?>" style="margin-bottom:8px;">
                    <?php if ($current_svg_val): ?><img src="<?php echo esc_url($current_svg_val); ?>" style="max-width:55px; max-height:55px; border-radius:8px; border:1px solid #cbd5e1; padding:4px; background:#fff; display:block;"><?php endif; ?>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="button madenna-media-btn" data-target="<?php echo esc_attr($field_svg_name); ?>" data-preview="preview_<?php echo esc_attr($field_svg_name); ?>">انتخاب SVG</button>
                    <?php if ($current_svg_val): ?><button type="button" class="button-link-delete madenna-clear-media-btn" data-target="<?php echo esc_attr($field_svg_name); ?>" data-preview="preview_<?php echo esc_attr($field_svg_name); ?>" style="color:#ef4444;">حذف فایل</button><?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public static function minify_code($code, $type = 'css') {
        if (empty($code)) return '';
        $code = (string) $code;
        if ($type === 'css') {
            $code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code) ?? $code;
            $code = str_replace(["\r\n", "\r", "\n", "\t"], '', $code);
            $code = preg_replace('/\s+/', ' ', $code) ?? $code;
            $code = preg_replace('/\s*([:;{}])\s*/', '$1', $code) ?? $code;
            return trim($code);
        }
        $code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code) ?? $code;
        $code = preg_replace('/^\s*[\r\n]/m', '', $code) ?? $code;
        return trim($code);
    }
    
    
    
/**
     * استخراج دقیق دسته‌بندی اصلی (اولویت قطعی با زیردسته)
     */
    public static function get_primary_category($post_id) {
        $terms = wp_get_post_terms($post_id, 'madenna_cat');
        if (empty($terms) || is_wp_error($terms)) {
            return null;
        }

        // ۱. بررسی انتخاب دستی دسته اصلی در یواست سئو
        $yoast_primary = get_post_meta($post_id, '_yoast_wpseo_primary_madenna_cat', true);
        if ($yoast_primary) {
            $term = get_term((int) $yoast_primary, 'madenna_cat');
            if ($term && !is_wp_error($term)) {
                return $term;
            }
        }

        // ۲. اولویت قطعی با زیردسته (ترمی که parent مخالف صفر است)
        foreach ($terms as $term) {
            if (!empty($term->parent) && $term->parent != 0) {
                return $term;
            }
        }

        // ۳. در صورت نبود زیردسته، استفاده از دسته مادر
        return $terms[0];
    }
    
}