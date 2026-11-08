<?php
/**
 * ویجت سوالات متداول مادنا
 * Location: /inc/elementor/widgets/class-widget-auto-faq.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Auto_FAQ_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() { return 'madenna_auto_faq'; }
    public function get_title() { return 'سوالات متداول مادنا'; }
    public function get_icon() { return 'eicon-help-o'; }
    public function get_categories() { return ['general']; }
    public function get_style_depends() { return [ 'madenna-elementor-widgets' ]; }
    
    protected function register_controls() {
        $this->start_controls_section('sec_faq', ['label' => 'محتوا', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);
        $this->add_control('heading_title', ['label' => 'عنوان سربرگ', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'پرسش‌های متداول']);
        $this->add_responsive_control('title_align', [
            'label' => 'تراز عنوان', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['left' => ['title' => 'چپ', 'icon' => 'eicon-text-align-left'], 'center' => ['title' => 'وسط', 'icon' => 'eicon-text-align-center'], 'right' => ['title' => 'راست', 'icon' => 'eicon-text-align-right']],
            'selectors' => ['{{WRAPPER}} .madenna-faq-title' => 'text-align: {{VALUE}};']
        ]);
        $this->end_controls_section();
        
        $this->start_controls_section('sec_style_title', ['label' => 'سربرگ', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'title_typography', 'selector' => '{{WRAPPER}} .madenna-faq-title']);
        $this->add_control('title_color', ['label' => 'رنگ سربرگ', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .madenna-faq-title' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('title_margin', ['label' => 'فاصله', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .madenna-faq-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_box', ['label' => 'باکس سوالات', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_control('bg_color', ['label' => 'پس‌زمینه آیتم‌ها', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} details.mad-faq-item' => 'background-color: {{VALUE}};']]);
        $this->add_control('border_color', ['label' => 'رنگ حاشیه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} details.mad-faq-item' => 'border-color: {{VALUE}};']]);
        $this->add_responsive_control('border_radius', ['label' => 'انحنای حاشیه', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} details.mad-faq-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('padding', ['label' => 'پدینگ داخلی', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} details.mad-faq-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('item_gap', ['label' => 'فاصله بین سوالات', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} details.mad-faq-item' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_q', ['label' => 'متن سوال', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'q_typography', 'selector' => '{{WRAPPER}} summary.mad-faq-summary']);
        $this->add_control('q_color', ['label' => 'رنگ سوال', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} summary.mad-faq-summary span.mad-q' => 'color: {{VALUE}};']]);
        $this->add_control('q_hover_color', ['label' => 'رنگ سوال (هاور)', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} summary.mad-faq-summary:hover span.mad-q' => 'color: {{VALUE}};']]);
        $this->add_control('icon_color', ['label' => 'رنگ آیکون', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} summary.mad-faq-summary span.mad-icn' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('icon_size', ['label' => 'اندازه آیکون', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} summary.mad-faq-summary span.mad-icn' => 'font-size: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('q_padding', ['label' => 'فاصله داخلی سوال', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} summary.mad-faq-summary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_ans', ['label' => 'متن پاسخ', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'ans_typography', 'selector' => '{{WRAPPER}} .mad-ans']);
        $this->add_control('ans_color', ['label' => 'رنگ پاسخ', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-ans' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('ans_padding', ['label' => 'پدینگ داخلی', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-ans' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('ans_margin', ['label' => 'فاصله خارجی', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-ans' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $faqs = [];
        if (is_singular('articles')) $faqs = get_post_meta(get_the_ID(), '_article_faqs', true);
        elseif (is_singular('tools')) $faqs = get_post_meta(get_the_ID(), '_tool_faqs', true);
        elseif (is_tax('madenna_cat')) { $t = get_queried_object(); if ($t && isset($t->term_id)) $faqs = get_term_meta($t->term_id, 'cat_faqs', true); }
        
        $faqs = is_array($faqs) ? $faqs : [];
        if (empty($faqs)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div style="padding:20px; background:#f8fafc; border:1px dashed #cbd5e1; text-align:center; color:#64748b; border-radius:8px;">این صفحه فاقد سوالات متداول ثبت شده است. در سایت زنده مخفی می‌ماند.</div>';
            }
            return;
        }
        ?>
        <div class="madenna-faq-wrap">
            <?php if (!empty($settings['heading_title'])): ?><h3 class="madenna-faq-title"><?php echo esc_html($settings['heading_title']); ?></h3><?php endif; ?>
            <div class="madenna-faq-list">
                <?php foreach ($faqs as $item): if (empty($item['q']) || empty($item['a'])) continue; ?>
                    <details class="mad-faq-item">
                        <summary class="mad-faq-summary">
                            <span class="mad-q"><?php echo esc_html($item['q']); ?></span><span class="mad-icn">▾</span>
                        </summary>
                        <div class="mad-ans"><?php echo wp_kses_post($item['a']); ?></div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}