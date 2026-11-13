<?php
/**
 * ویجت اجراکننده ابزار مادنا (مخصوص قالب تکی ابزار در المنتور)
 * Location: /inc/elementor/widgets/class-widget-tool-renderer.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Tool_Renderer_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'madenna_tool_renderer'; }
    public function get_title() { return 'اجراکننده ابزار مادنا'; }
    public function get_icon() { return 'eicon-code'; }
    public function get_categories() { return ['general']; }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => 'تنظیمات ابزار',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'note',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div style="font-size:12px; color:#64748b; line-height:1.6;">این ویجت به صورت کاملاً هوشمند کدهای HTML, CSS و JS ابزار جاری را در صفحه تکی ابزار اجرا می‌کند. نیاز به تنظیم خاصی ندارد.</div>',
            ]
        );

        $this->end_controls_section();

        // بخش استایل برای کانتینر ابزار
        $this->start_controls_section(
            'section_style',
            [
                'label' => 'استایل کانتینر ابزار',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'padding',
            [
                'label' => 'پدینگ داخلی',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .madenna-tool-renderer-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border',
                'selector' => '{{WRAPPER}} .madenna-tool-renderer-wrap',
            ]
        );

        $this->add_responsive_control(
            'border_radius',
            [
                'label' => 'انحنای کادر',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .madenna-tool-renderer-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}} .madenna-tool-renderer-wrap',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if (!is_singular('tools')) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div style="padding:20px; background:#f8fafc; border:1px dashed #cbd5e1; text-align:center; color:#64748b; border-radius:8px;">ویجت اجراکننده ابزار: لطفاً این ویجت را در یک قالب تکی ابزار (Single Tool) استفاده کنید.</div>';
            }
            return;
        }

        $tool_id = get_the_ID();
        
        // فراخوانی فایل‌های جاوااسکریپت و CSS اختصاصی این ابزار
        if (class_exists('Madenna_Frontend')) {
            $frontend = new \Madenna_Frontend();
            // دسترسی به متد خصوصی از طریق بازتاب یا اجرای مستقیم کدهای بارگذاری کتابخانه‌ها
            $upload_dir = wp_upload_dir();
            $css_file = $upload_dir['basedir'] . "/madenna-tools/tool-{$tool_id}.css";
            $js_file  = $upload_dir['basedir'] . "/madenna-tools/tool-{$tool_id}.js";
            
            $libs = get_post_meta($tool_id, '_tool_libraries', true);
            $libs = is_array($libs) ? $libs : [];
            $all_libs = \Madenna_Helpers::get_available_tool_libraries();

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

        $force_font = get_post_meta($tool_id, '_tool_force_site_font', true) === 'yes' ? "<style>#madenna-tool-renderer-{$tool_id} * { font-family: inherit !important; }</style>" : '';
        $html_content = get_post_meta($tool_id, '_tool_html', true);

        $u = is_user_logged_in() ? wp_get_current_user() : null;
        $uid = get_current_user_id();
        $user_name = $u ? esc_attr($u->display_name) : '';
        $user_gender = $uid ? esc_attr(get_user_meta($uid, 'madenna_gender', true) ?: '') : '';
        $user_birthdate = $uid ? esc_attr(get_user_meta($uid, 'madenna_birthdate', true) ?: '') : '';
        $is_logged_in = is_user_logged_in() ? 'true' : 'false';

        echo sprintf(
            '<div class="madenna-tool-renderer-wrap madenna-tool-wrapper" id="madenna-tool-renderer-%d" data-is-logged-in="%s" data-user-id="%d" data-user-name="%s" data-user-gender="%s" data-user-birthdate="%s">%s%s</div>',
            $tool_id,
            $is_logged_in,
            $uid,
            $user_name,
            $user_gender,
            $user_birthdate,
            $force_font,
            $html_content
        );
    }
}