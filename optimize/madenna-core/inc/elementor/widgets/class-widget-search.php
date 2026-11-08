<?php
/**
 * ویجت جستجوگر هوشمند و سربرگ نتایج مادنا
 * Location: /inc/elementor/widgets/class-widget-search.php
 */

class Madenna_Search_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'madna_search';
    }

    public function get_title() {
        return 'جستجوگر مادنا (ساده/پیشرفته)';
    }

    public function get_icon() {
        return 'eicon-search';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {

        // ==========================================
        // Tab: Content -> Layout & Search Settings
        // ==========================================
        $this->start_controls_section(
            'section_search_settings',
            [
                'label' => 'تنظیمات ساختار',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'layout_style',
            [
                'label' => 'طرح نمایش',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'simple',
                'options' => [
                    'simple' => 'طرح ساده (مناسب لندینگ و هدر)',
                    'advanced' => 'طرح نتایج پیشرفته (باکس کامل با شمارنده و تب‌ها)',
                ],
            ]
        );

        $this->add_control(
            'search_mode',
            [
                'label' => 'عملکرد جستجو',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'redirect',
                'options' => [
                    'redirect' => 'انتقال به صفحه نتایج (استاندارد)',
                    'live' => 'جستجوی زنده (Live AJAX - بدون رفرش)',
                ],
                'condition' => [
                    'layout_style' => 'simple',
                ],
                'description' => 'حالت زنده فقط در طرح ساده پشتیبانی می‌شود.',
            ]
        );

        $this->add_control(
            'target_post_type',
            [
                'label' => 'نوع محتوای هدف',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'any',
                'options' => [
                    'any' => 'همه محتواها (ترکیبی)',
                    'articles' => 'فقط مقالات',
                    'tools' => 'فقط ابزارها',
                ],
                'condition' => [
                    'layout_style' => 'simple',
                    'search_mode' => 'redirect',
                ],
            ]
        );

        $this->add_control(
            'placeholder',
            [
                'label' => 'متن نگهدارنده (Placeholder)',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'دنبال چه ابزاری هستید؟',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => 'متن دکمه',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'جست‌وجو',
            ]
        );

        $this->end_controls_section();

        // ==========================================
        // Tab: Content -> Advanced Elements
        // ==========================================
        $this->start_controls_section(
            'section_advanced_elements',
            [
                'label' => 'تنظیمات سربرگ پیشرفته',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'layout_style' => 'advanced',
                ],
            ]
        );

        $this->add_control(
            'show_summary',
            [
                'label' => 'نمایش نوار آمار (نتایج جستجو برای...)',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // ==========================================
        // Tab: Content -> Popular Tags
        // ==========================================
        $this->start_controls_section(
            'section_popular_tags',
            [
                'label' => 'برچسب‌های پرجستجو',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_tags',
            [
                'label' => 'نمایش برچسب‌ها',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'tags_heading',
            [
                'label' => 'عنوان بخش برچسب‌ها',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'بیشترین جستجوها:',
                'condition' => [
                    'show_tags' => 'yes',
                ],
            ]
        );

        $repeater = new \Elementor\Repeater();
        $repeater->add_control('tag_text', ['label' => 'کلمه جستجو', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'پومودورو']);

        $this->add_control(
            'popular_tags',
            [
                'label' => 'لیست برچسب‌ها',
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [ 'tag_text' => 'رهن و اجاره' ],
                    [ 'tag_text' => 'BMI' ],
                    [ 'tag_text' => 'تست MBTI' ],
                ],
                'title_field' => '{{{ tag_text }}}',
                'condition' => [
                    'show_tags' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // ==========================================
        // Tab: Style -> Search Box
        // ==========================================
        $this->start_controls_section(
            'style_search_box',
            [
                'label' => 'استایل فرم جستجو',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'box_bg_color',
            [
                'label' => 'رنگ پس‌زمینه فرم',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mad-srch-form' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'box_border_color',
            [
                'label' => 'رنگ حاشیه (عادی)',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mad-srch-form' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'box_border_color_focus',
            [
                'label' => 'رنگ حاشیه (هنگام تایپ)',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mad-srch-form:focus-within' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'box_padding',
            [
                'label' => 'پدینگ داخلی فرم',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .mad-srch-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'box_border_radius',
            [
                'label' => 'انحنای فرم (Border Radius)',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .mad-srch-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}} .mad-srch-form',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_focus',
                'label' => 'سایه هنگام تایپ',
                'selector' => '{{WRAPPER}} .mad-srch-form:focus-within',
            ]
        );

        $this->end_controls_section();

        // ==========================================
        // Tab: Style -> Input & Icon & Button
        // ==========================================
        $this->start_controls_section(
            'style_input_button',
            [
                'label' => 'فیلد، آیکون و دکمه',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control('heading_icon_style', ['label' => 'آیکون جستجو', 'type' => \Elementor\Controls_Manager::HEADING]);
        
        $this->add_responsive_control(
            'icon_size',
            [
                'label' => 'اندازه آیکون',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => ['{{WRAPPER}} .mad-srch-icon' => 'font-size: {{SIZE}}{{UNIT}};'],
            ]
        );
        $this->add_control('icon_color', ['label' => 'رنگ آیکون', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-srch-icon' => 'color: {{VALUE}};']]);
        
        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => 'فاصله تا متن',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => ['{{WRAPPER}} .mad-srch-icon' => 'margin-left: {{SIZE}}{{UNIT}};'],
            ]
        );

        $this->add_control('heading_input_style', ['label' => 'فیلد ورودی متن', 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before']);
        
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'input_typography', 'selector' => '{{WRAPPER}} .mad-srch-input']);
        $this->add_control('input_color', ['label' => 'رنگ متن ورودی', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-srch-input' => 'color: {{VALUE}};']]);
        $this->add_control('placeholder_color', ['label' => 'رنگ متن نگهدارنده', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-srch-input::placeholder' => 'color: {{VALUE}};']]);

        $this->add_control('heading_btn_style', ['label' => 'دکمه جستجو', 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before']);
        
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'btn_typography', 'selector' => '{{WRAPPER}} .mad-srch-btn']);
        
        $this->start_controls_tabs('btn_style_tabs');
        $this->start_controls_tab('btn_normal', ['label' => 'عادی']);
        $this->add_control('btn_text_color', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-srch-btn' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'btn_background',
                'label' => 'پس‌زمینه دکمه',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-srch-btn',
            ]
        );
        $this->end_controls_tab();
        
        $this->start_controls_tab('btn_hover', ['label' => 'هاور']);
        $this->add_control('btn_text_color_hover', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-srch-btn:hover' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'btn_background_hover',
                'label' => 'پس‌زمینه دکمه (هاور)',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-srch-btn:hover',
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control('btn_margin', ['label' => 'فاصله خارجی (Margin)', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-srch-btn-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'separator' => 'before']);
        $this->add_responsive_control('btn_padding', ['label' => 'پدینگ دکمه', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-srch-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('btn_radius', ['label' => 'انحنای دکمه', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-srch-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);

        $this->end_controls_section();
        
        // ==========================================
        // Tab: Style -> Tags
        // ==========================================
        $this->start_controls_section(
            'style_tags_section',
            [
                'label' => 'استایل برچسب‌ها',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_tags' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'tags_align',
            [
                'label' => 'ترازبندی کل برچسب‌ها',
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => ['title' => 'راست', 'icon' => 'eicon-h-align-right'],
                    'center' => ['title' => 'وسط', 'icon' => 'eicon-h-align-center'],
                    'flex-end' => ['title' => 'چپ', 'icon' => 'eicon-h-align-left'],
                ],
                'selectors' => ['{{WRAPPER}} .mad-tags-wrap' => 'justify-content: {{VALUE}};'],
            ]
        );

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'tags_heading_typ', 'label' => 'فونت عنوان', 'selector' => '{{WRAPPER}} .mad-tags-lbl']);
        $this->add_control('tags_heading_color', ['label' => 'رنگ عنوان', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tags-lbl' => 'color: {{VALUE}};']]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'tags_typ', 'label' => 'فونت برچسب', 'selector' => '{{WRAPPER}} .mad-tag', 'separator' => 'before']);
        
        $this->start_controls_tabs('tags_style_tabs');
        $this->start_controls_tab('tags_normal', ['label' => 'عادی']);
        $this->add_control('tags_text_color', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tag' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tags_background',
                'label' => 'پس‌زمینه برچسب',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-tag',
            ]
        );
        $this->end_controls_tab();
        
        $this->start_controls_tab('tags_hover', ['label' => 'هاور']);
        $this->add_control('tags_text_color_hover', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tag:hover' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tags_background_hover',
                'label' => 'پس‌زمینه برچسب (هاور)',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-tag:hover',
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control('tags_padding', ['label' => 'پدینگ برچسب', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tag' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'separator' => 'before']);
        $this->add_responsive_control('tags_radius', ['label' => 'انحنای برچسب', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tag' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);

        $this->end_controls_section();

        // ==========================================
        // Tab: Style -> Advanced Tabs & Summary
        // ==========================================
        $this->start_controls_section(
            'style_advanced_tabs',
            [
                'label' => 'استایل نتایج و تب‌ها',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => ['layout_style' => 'advanced'],
            ]
        );

        $this->add_responsive_control(
            'adv_align',
            [
                'label' => 'ترازبندی نوار نتایج و تب‌ها',
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => ['title' => 'راست', 'icon' => 'eicon-h-align-right'],
                    'center' => ['title' => 'وسط', 'icon' => 'eicon-h-align-center'],
                    'flex-end' => ['title' => 'چپ', 'icon' => 'eicon-h-align-left'],
                ],
                'selectors' => [
                    '{{WRAPPER}} .mad-adv-wrap' => 'align-items: {{VALUE}};',
                    '{{WRAPPER}} .mad-adv-summary' => 'justify-content: {{VALUE}};',
                    '{{WRAPPER}} .mad-adv-tabs' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_control('heading_summary_style', ['label' => 'استایل نوار نتایج', 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'summary_typ', 'selector' => '{{WRAPPER}} .mad-adv-summary']);
        $this->add_control('summary_color', ['label' => 'رنگ متن نتایج', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-adv-summary' => 'color: {{VALUE}};']]);
        $this->add_control('summary_bold_color', ['label' => 'رنگ کلمه جستجو شده', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-adv-summary strong' => 'color: {{VALUE}};']]);
        
        $this->add_control('summary_badge_color', ['label' => 'رنگ نشان (Badge)', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-adv-badge' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'summary_badge_background',
                'label' => 'پس‌زمینه نشان (Badge)',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-adv-badge',
            ]
        );

        $this->add_control('heading_tabs_style', ['label' => 'استایل تب‌ها', 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'tabs_typography', 'selector' => '{{WRAPPER}} .mad-tab']);
        
        // تب‌بندی رنگ‌های استایل تب‌ها
        $this->start_controls_tabs('tabs_style_states');

        // حالت عادی
        $this->start_controls_tab('tab_normal', ['label' => 'عادی']);
        $this->add_control('tab_color', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tab' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tab_background',
                'label' => 'پس‌زمینه تب',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-tab',
            ]
        );
        $this->add_control('tab_border', ['label' => 'رنگ حاشیه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tab' => 'border-color: {{VALUE}};']]);
        $this->add_control('tab_num_color', ['label' => 'رنگ عدد', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tab .tab-num' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tab_num_background',
                'label' => 'پس‌زمینه عدد',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-tab .tab-num',
            ]
        );
        $this->end_controls_tab();

        // حالت هاور
        $this->start_controls_tab('tab_hover', ['label' => 'هاور']);
        $this->add_control('tab_hover_color', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tab:hover' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tab_hover_background',
                'label' => 'پس‌زمینه تب',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-tab:hover',
            ]
        );
        $this->add_control('tab_hover_border', ['label' => 'رنگ حاشیه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tab:hover' => 'border-color: {{VALUE}};']]);
        $this->end_controls_tab();

        // حالت فعال
        $this->start_controls_tab('tab_active', ['label' => 'فعال']);
        $this->add_control('tab_active_color', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tab.active' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tab_active_background',
                'label' => 'پس‌زمینه تب',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-tab.active',
            ]
        );
        $this->add_control('tab_active_border', ['label' => 'رنگ حاشیه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tab.active' => 'border-color: {{VALUE}};']]);
        $this->add_control('tab_active_num_color', ['label' => 'رنگ عدد', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tab.active .tab-num' => 'color: {{VALUE}};']]);
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tab_active_num_background',
                'label' => 'پس‌زمینه عدد',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mad-tab.active .tab-num',
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control('tab_padding', ['label' => 'پدینگ تب', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'separator' => 'before']);
        $this->add_responsive_control('tab_border_radius', ['label' => 'انحنای تب', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tab' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        
        $this->add_responsive_control('tab_num_padding', ['label' => 'پدینگ عدد (Badge)', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tab .tab-num' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'separator' => 'before']);
        $this->add_responsive_control('tab_num_margin', ['label' => 'فاصله خارجی عدد', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tab .tab-num' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);

        $this->end_controls_section();
    }

    private function get_post_type_count($keyword, $post_type) {
        global $wpdb;
        if(empty($keyword)) return 0;
        
        $like = '%' . $wpdb->esc_like( $keyword ) . '%';
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND (post_title LIKE %s OR post_content LIKE %s OR post_excerpt LIKE %s)",
            $post_type, $like, $like, $like
        ));
        
        return intval($count);
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $layout = $settings['layout_style'];
        $search_query = get_search_query();
        $is_live = ($layout === 'simple' && $settings['search_mode'] === 'live');
        
        // اطمینان از مقداردهی پیش‌فرض اگر کاربر رنگی انتخاب نکرده بود
        $bg_color = !empty($settings['box_bg_color']) ? $settings['box_bg_color'] : 'transparent';
        
        ?>
        <style>
            .mad-srch-container { width: 100%; direction: rtl; }
            .mad-srch-container * { box-sizing: border-box; }
            
            /* ساختار فرم - رنگ‌ها از المنتور خوانده می‌شوند */
            .mad-srch-form { 
                display: flex; align-items: center; justify-content: space-between;
                border: 1px solid transparent; transition: all 0.3s ease; width: 100%;
                background-color: <?php echo esc_attr($bg_color); ?>;
            }
            .mad-srch-input-grp { display: flex; align-items: center; flex: 1; min-width: 0; }
            .mad-srch-icon { display: flex; align-items: center; justify-content: center; }
            .mad-srch-input { 
                flex: 1; border: none; outline: none; background: transparent; 
                min-width: 0; font-family: inherit; width: 100%;
            }
            
            .mad-srch-clear { 
                font-size: 1.1rem; cursor: pointer; padding: 5px; 
                display: none; transition: 0.2s; opacity: 0.5;
            }
            .mad-srch-clear:hover { opacity: 1; color: #ef4444 !important; }
            .mad-srch-input:not(:placeholder-shown) + .mad-srch-clear { display: block; }
            
            .mad-srch-btn-wrap { display: flex; align-items: center; }
            .mad-srch-btn { 
                border: none; cursor: pointer; transition: all 0.3s ease; 
                white-space: nowrap; font-family: inherit; display: flex; align-items: center; justify-content: center;
            }
            .mad-srch-btn:hover { opacity: 0.9; transform: translateY(-1px); }

            /* ساختار برچسب‌ها */
            .mad-tags-wrap { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
            .mad-tag { text-decoration: none; transition: 0.2s; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
            .mad-tag:hover { opacity: 0.8; }

            /* ساختار تب‌ها و آمار */
            .mad-adv-wrap { margin-top: 25px; display: flex; flex-direction: column; gap: 15px; }
            .mad-adv-summary { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; width: 100%; }
            .mad-adv-badge { font-weight: bold; padding: 5px 15px; border-radius: 20px; display:inline-block; }
            .mad-adv-summary strong { font-weight: 900; }
            
            .mad-adv-tabs { display: flex; flex-wrap: wrap; gap: 10px; width: 100%; margin-top: 10px; }
            .mad-tab { 
                display: flex; align-items: center; gap: 8px; 
                border: 1px solid transparent; text-decoration: none; 
                transition: all 0.3s;
            }
            .mad-tab .tab-num { display: inline-flex; align-items: center; justify-content: center; }

            /* رسپانسیو موبایل - اسکرول افقی تب‌ها و برچسب‌ها */
            @media (max-width: 768px) {
                .mad-adv-summary { justify-content: center !important; flex-direction: column-reverse; gap: 10px; text-align: center; }
                .mad-adv-wrap { align-items: center !important; }
                .mad-adv-tabs { justify-content: flex-start !important; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 10px; -webkit-overflow-scrolling: touch; width: 100%; }
                .mad-tab { white-space: nowrap; flex-shrink: 0; }
            }
        </style>
        <?php

        echo '<div class="mad-srch-container" id="madna-srch-' . esc_attr($this->get_id()) . '">';
        
        $form_action = esc_url(home_url('/'));
        $data_live = $is_live ? 'data-live="true"' : '';
        
        ?>
        <form class="mad-srch-form" action="<?php echo $form_action; ?>" method="get" <?php echo $data_live; ?>>
            <div class="mad-srch-input-grp">
                <i class="fa-solid fa-magnifying-glass mad-srch-icon"></i>
                <input type="text" name="s" class="mad-srch-input" placeholder="<?php echo esc_attr($settings['placeholder']); ?>" value="<?php echo esc_attr($search_query); ?>" autocomplete="off">
                <i class="fa-solid fa-xmark mad-srch-clear" title="پاک کردن"></i>
            </div>
            
            <?php 
            $current_pt = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'any';
            if ( $layout === 'simple' && ! $is_live && $settings['target_post_type'] !== 'any' ) {
                echo '<input type="hidden" name="post_type" value="' . esc_attr($settings['target_post_type']) . '">';
            } elseif ($layout === 'advanced') {
                echo '<input type="hidden" name="post_type" value="' . esc_attr($current_pt) . '">';
            }
            ?>
            
            <div class="mad-srch-btn-wrap">
                <button type="submit" class="mad-srch-btn">
                    <?php echo esc_html($settings['button_text']); ?>
                </button>
            </div>
        </form>
        <?php

        // نمایش برچسب‌ها (در هر دو طرح)
        if ( $settings['show_tags'] === 'yes' && ! empty( $settings['popular_tags'] ) ) {
            echo '<div class="mad-tags-wrap">';
            if (!empty($settings['tags_heading'])) echo '<span class="mad-tags-lbl">' . esc_html($settings['tags_heading']) . '</span>';
            foreach ( $settings['popular_tags'] as $tag ) {
                $t_url = $is_live ? '#' : home_url('/?s=' . urlencode($tag['tag_text']));
                if ( ! $is_live && $settings['target_post_type'] !== 'any' ) $t_url .= '&post_type=' . $settings['target_post_type'];
                echo '<a href="'.esc_url($t_url).'" class="mad-tag" data-keyword="'.esc_attr($tag['tag_text']).'">'.esc_html($tag['tag_text']).'</a>';
            }
            echo '</div>';
        }

        if ($layout === 'advanced') {
            $tools_count = $this->get_post_type_count($search_query, 'tools');
            $articles_count = $this->get_post_type_count($search_query, 'articles');
            $total_count = $tools_count + $articles_count;
            
            $base_url = home_url('/?s=' . urlencode($search_query));
            
            echo '<div class="mad-adv-wrap">';
            
            if ($settings['show_summary'] === 'yes' && !empty($search_query)) {
                echo '<div class="mad-adv-summary">';
                echo '<span class="mad-adv-badge">' . $total_count . ' نتیجه در اکوسیستم مادنا</span>';
                echo '<span>نتایج جست‌وجو برای <strong>«' . esc_html($search_query) . '»</strong></span>';
                echo '</div>';
            }
            
            echo '<div class="mad-adv-tabs">';
            
            // ترتیب جدید: ۱. همه ۲. مقالات ۳. ابزارها
            
            $cls_all = ($current_pt === 'any' || $current_pt === '') ? 'active' : '';
            echo '<a href="'.esc_url($base_url.'&post_type=any').'" class="mad-tab '.$cls_all.'">';
            echo '<span class="tab-num">'.$total_count.'</span>';
            echo '<span class="tab-text">همه نتایج</span>';
            echo '<i class="fa-solid fa-border-all"></i>';
            echo '</a>';

            $cls_arts = ($current_pt === 'articles') ? 'active' : '';
            echo '<a href="'.esc_url($base_url.'&post_type=articles').'" class="mad-tab '.$cls_arts.'">';
            echo '<span class="tab-num">'.$articles_count.'</span>';
            echo '<span class="tab-text"> مقالات </span>';
            echo '<i class="fa-solid fa-file-lines"></i>';
            echo '</a>';
            
            $cls_tools = ($current_pt === 'tools') ? 'active' : '';
            echo '<a href="'.esc_url($base_url.'&post_type=tools').'" class="mad-tab '.$cls_tools.'">';
            echo '<span class="tab-num">'.$tools_count.'</span>';
            echo '<span class="tab-text">ابزارها</span>';
            echo '<i class="fa-solid fa-wrench"></i>';
            echo '</a>';

            echo '</div>'; 
            echo '</div>'; 
        }

        echo '</div>'; 

        // اضافه کردن جاوااسکریپت به صورت امن برای محیط ویرایشگر و فرانت‌اند
        ?>
        <script>
        (function() {
            function initWidget() {
                var widgetContainer = document.getElementById('madna-srch-<?php echo esc_attr($this->get_id()); ?>');
                if(!widgetContainer) return;
                if(widgetContainer.getAttribute('data-events-bound') === 'true') return;
                widgetContainer.setAttribute('data-events-bound', 'true');

                var form = widgetContainer.querySelector('.mad-srch-form');
                var input = widgetContainer.querySelector('.mad-srch-input');
                var clearBtn = widgetContainer.querySelector('.mad-srch-clear');
                var tags = widgetContainer.querySelectorAll('.mad-tag');
                if(!form || !input) return;
                var isLive = form.getAttribute('data-live') === 'true';

                if(clearBtn) {
                    clearBtn.addEventListener('click', function() {
                        input.value = '';
                        input.focus();
                        if(isLive) triggerLiveSearch('');
                    });
                }

                tags.forEach(function(tag) {
                    tag.addEventListener('click', function(e) {
                        var keyword = this.getAttribute('data-keyword');
                        input.value = keyword;
                        if(isLive) { e.preventDefault(); triggerLiveSearch(keyword); }
                    });
                });

                if(isLive) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        triggerLiveSearch(input.value);
                    });
                    
                    var typingTimer;
                    input.addEventListener('input', function() {
                        clearTimeout(typingTimer);
                        var keyword = input.value;
                        typingTimer = setTimeout(function() { triggerLiveSearch(keyword); }, 500);
                    });
                }

                function triggerLiveSearch(keyword) {
                    var event = new CustomEvent('madennaLiveSearch', { detail: { keyword: keyword } });
                    document.dispatchEvent(event);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initWidget);
            } else {
                initWidget();
            }
        })();
        </script>
        <?php
    }
}