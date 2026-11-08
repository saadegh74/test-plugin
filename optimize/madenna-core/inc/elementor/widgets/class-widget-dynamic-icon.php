<?php
/**
 * ویجت نمایش آیکون هوشمند مادنا (با قابلیت استایل‌دهی پیشرفته)
 * Location: /inc/elementor/widgets/class-widget-dynamic-icon.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Dynamic_Icon_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() { return 'madenna_dynamic_icon'; }
    public function get_title() { return 'آیکون هوشمند مادنا'; }
    public function get_icon() { return 'eicon-info-circle'; }
    public function get_categories() { return ['general']; }
    public function get_style_depends() { return [ 'madenna-elementor-widgets' ]; }
    
    protected function register_controls() {
        
        $this->start_controls_section('sec_content', ['label' => 'تنظیمات پایه', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);
        
        $this->add_responsive_control('align', [
            'label' => 'ترازبندی', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'flex-start' => ['title' => 'راست', 'icon' => 'eicon-text-align-right'], 
                'center' => ['title' => 'وسط', 'icon' => 'eicon-text-align-center'], 
                'flex-end' => ['title' => 'چپ', 'icon' => 'eicon-text-align-left']
            ],
            'selectors' => ['{{WRAPPER}} .mad-dyn-icon-wrap' => 'justify-content: {{VALUE}};']
        ]);

        $this->add_control('use_dynamic_colors', [
            'label' => 'رنگ‌بندی هوشمند (اتوماتیک)',
            'description' => 'اگر روشن باشد، رنگ آیکون و پس‌زمینه به طور خودکار از متاهای پست/دسته خوانده می‌شود.',
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
            'separator' => 'before',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('sec_style', ['label' => 'استایل و رنگ‌ها', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        
        $this->add_control('custom_icon_color', [
            'label' => 'رنگ سفارشی آیکون',
            'type' => \Elementor\Controls_Manager::COLOR,
            'condition' => ['use_dynamic_colors' => ''],
            'selectors' => ['{{WRAPPER}} .mad-dyn-icon' => 'color: {{VALUE}};']
        ]);

        $this->add_control('custom_bg_color', [
            'label' => 'رنگ سفارشی پس‌زمینه',
            'type' => \Elementor\Controls_Manager::COLOR,
            'condition' => ['use_dynamic_colors' => ''],
            'selectors' => ['{{WRAPPER}} .mad-dyn-icon' => 'background-color: {{VALUE}};']
        ]);

        $this->add_responsive_control('icon_size', [
            'label' => 'اندازه آیکون', 'type' => \Elementor\Controls_Manager::SLIDER,
            'default' => ['size' => 24, 'unit' => 'px'],
            'selectors' => ['{{WRAPPER}} .mad-dyn-icon i, {{WRAPPER}} .mad-dyn-icon span, {{WRAPPER}} .mad-dyn-icon .mad-svg-icon' => 'font-size: {{SIZE}}{{UNIT}};']
        ]);
        
        $this->add_responsive_control('box_size', [
            'label' => 'اندازه باکس (پس‌زمینه)', 'type' => \Elementor\Controls_Manager::SLIDER, 
            'range' => ['px' => ['min' => 20, 'max' => 200]],
            'default' => ['size' => 48, 'unit' => 'px'],
            'selectors' => ['{{WRAPPER}} .mad-dyn-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};']
        ]);
        
        $this->add_responsive_control('border_radius', [
            'label' => 'انحنای باکس', 'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'default' => ['top' => 12, 'right' => 12, 'bottom' => 12, 'left' => 12, 'unit' => 'px', 'isLinked' => true],
            'selectors' => ['{{WRAPPER}} .mad-dyn-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'box_border',
                'label' => 'حاشیه دور باکس',
                'selector' => '{{WRAPPER}} .mad-dyn-icon',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'label' => 'سایه باکس',
                'selector' => '{{WRAPPER}} .mad-dyn-icon',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $icon_class = ''; $svg_url = ''; $dynamic_color = ''; $dynamic_bg = '';
        
        // واکشی خودکار آیکون و رنگ‌های هوشمند از دیتابیس
        if (is_singular('tools')) {
            $id = get_the_ID();
            $icon_class = get_post_meta($id, '_tool_icon_class', true);
            $svg_url = get_post_meta($id, '_tool_icon_url', true);
            $dynamic_color = get_post_meta($id, '_tool_color_primary', true) ?: '#2563eb';
            $dynamic_bg = get_post_meta($id, '_tool_color_accent', true) ?: '#eff6ff';
        } elseif (is_singular('articles')) {
            $id = get_the_ID();
            $icon_class = get_post_meta($id, '_article_icon_class', true);
            $svg_url = get_post_meta($id, '_article_svg_url', true);
            $dynamic_color = '#2563eb'; // fallback default
            $dynamic_bg = '#eff6ff';    // fallback default
        } elseif (is_tax('madenna_cat') || is_category() || is_tag()) {
            $obj = get_queried_object();
            if (isset($obj->term_id)) {
                $icon_class = get_term_meta($obj->term_id, 'cat_icon_class', true);
                $svg_url = get_term_meta($obj->term_id, 'cat_icon_url', true);
                $dynamic_color = get_term_meta($obj->term_id, 'cat_icon_color', true) ?: '#2563eb';
                $dynamic_bg = get_term_meta($obj->term_id, 'cat_icon_bg', true) ?: '#eff6ff';
            }
        }

        // حالت پیش‌نمایش خالی برای ویرایشگر المنتور
        if (empty($icon_class) && empty($svg_url)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="mad-dyn-icon-wrap" style="display:flex; width:100%;"><div class="mad-dyn-icon" style="background:#f1f5f9; color:#94a3b8; display:inline-flex; align-items:center; justify-content:center;">?</div></div>';
            }
            return;
        }

        // بررسی اینکه آیا رنگ هوشمند فعال است یا نه
        $inline_style = 'display:inline-flex; align-items:center; justify-content:center; transition:all 0.3s;';
        if ($settings['use_dynamic_colors'] === 'yes') {
            if ($dynamic_color) $inline_style .= ' color:' . esc_attr($dynamic_color) . ';';
            if ($dynamic_bg) $inline_style .= ' background-color:' . esc_attr($dynamic_bg) . ';';
        }

        // رندر نهایی
        echo '<div class="mad-dyn-icon-wrap" style="display:flex; width:100%;">';
        echo '<div class="mad-dyn-icon" style="' . $inline_style . '">';
        
        if ($svg_url) {
            echo "<div class='mad-svg-icon' style=\"-webkit-mask-image: url('".esc_url($svg_url)."'); mask-image: url('".esc_url($svg_url)."');\"></div>";
        } else {
            echo \Madenna_Helpers::render_icon_element($icon_class);
        }
        
        echo '</div></div>';
    }
}