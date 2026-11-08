<?php
/**
 * ویجت گرید هوشمند مادنا (ابزارها + مقالات) با پشتیبانی از AJAX
 * Location: /inc/elementor/widgets/class-widget-tools.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Tools_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() { return 'madenna_tools_grid'; }
    public function get_title() { return 'گرید هوشمند مادنا'; }
    public function get_icon() { return 'eicon-posts-grid'; }
    public function get_categories() { return ['general']; }
    public function get_style_depends() { return [ 'madenna-elementor-widgets' ]; }
    
    protected function register_controls() {
        $this->start_controls_section('sec_tools', ['label' => 'منبع داده', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);
        
        $this->add_control('post_type_selector', [
            'label' => 'نوع محتوا', 
            'type' => \Elementor\Controls_Manager::SELECT, 
            'default' => 'tools', 
            'options' => [
                'tools' => '🛠️ ابزارها', 
                'articles' => '📚 مقالات',
                'both' => '🌟 ترکیبی (ابزار + مقاله)'
            ]
        ]);

        $this->add_control('alternate_pattern', [
            'label' => 'چیدمان یکی‌درمیان (مقاله/ابزار)',
            'description' => 'با روشن کردن این گزینه، نتایج به صورت دقیق یک مقاله و یک ابزار به صورت متناوب چیده می‌شوند.',
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'no',
            'condition' => ['post_type_selector' => 'both'],
        ]);

        $this->add_control('query_source', [
            'label' => 'منبع واکشی', 
            'type' => \Elementor\Controls_Manager::SELECT, 
            'default' => 'latest', 
            'options' => [
                'latest' => 'جدیدترین‌ها', 
                'featured' => '⭐ برگزیده‌ها',
                'current_term' => 'دسته‌بندی / برچسب فعلی (آرشیو)', 
                'by_category' => 'از یک دسته‌بندی خاص',
                'by_tag' => '🏷️ از برچسب‌های خاص',
                'related' => 'موارد مرتبط (اتصال ۲ طرفه)', 
                'related_same_type' => '🔗 مطالب مرتبط (هم‌موضوع)',
                'manual' => 'انتخاب دستی'
            ]
        ]);

        $tag_options = [];
        $tool_tags = get_terms(['taxonomy' => 'madenna_tool_tag', 'hide_empty' => false]);
        if (!is_wp_error($tool_tags) && is_array($tool_tags)) {
            foreach ($tool_tags as $t_tag) {
                $tag_options[$t_tag->term_id] = $t_tag->name;
            }
        }
        $this->add_control('selected_tool_tags', [
            'label' => '🏷️ انتخاب برچسب‌های ابزار',
            'description' => 'برچسب‌های مورد نظر را انتخاب کنید. اگر خالی بگذارید، همه برچسب‌ها نمایش داده می‌شوند (در صفحه آرشیو برچسب یا صفحه تکی ابزار، در صورت خالی بودن به صورت هوشمند برچسب فعلی را می‌خواند).',
            'type' => \Elementor\Controls_Manager::SELECT2,
            'multiple' => true,
            'options' => $tag_options,
            'condition' => [
                'post_type_selector' => ['tools', 'both'],
                'query_source!' => 'manual'
            ],
        ]);

        $this->add_control('loop_subcategories', [
            'label' => '🔁 تکرار برای زیردسته‌ها (Loop)',
            'description' => 'اگر روشن باشد، برای هر زیردسته یک تیتر اختصاصی و اسلایدر/گرید مجزا به صورت اتوماتیک می‌سازد.',
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'no',
            'condition' => ['query_source' => ['current_term', 'by_category']],
        ]);
        
        $this->add_control('subcat_btn_text', [
            'label' => 'متن دکمه سربرگ زیردسته',
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'مشاهده همه ←',
            'condition' => ['loop_subcategories' => 'yes'],
        ]);
        
        $this->add_control('posts_count', [
            'label' => 'تعداد کل در هر بلاک (خالی = همه)', 
            'type' => \Elementor\Controls_Manager::NUMBER, 
            'default' => 6
        ]);
        
        $options = []; 
        $tools = get_posts(['post_type' => 'tools', 'posts_per_page' => 100]);
        if (is_array($tools)) { foreach ($tools as $tp) $options[$tp->ID] = $tp->post_title; }
        $this->add_control('selected_tools', [
            'label' => 'انتخاب ابزار', 'type' => \Elementor\Controls_Manager::SELECT2, 'multiple' => true, 'options' => $options, 
            'condition' => ['query_source' => 'manual', 'post_type_selector' => ['tools', 'both']]
        ]);

        $art_options = []; 
        $articles = get_posts(['post_type' => 'articles', 'posts_per_page' => 100]);
        if (is_array($articles)) { foreach ($articles as $ap) $art_options[$ap->ID] = $ap->post_title; }
        $this->add_control('selected_articles', [
            'label' => 'انتخاب مقاله', 'type' => \Elementor\Controls_Manager::SELECT2, 'multiple' => true, 'options' => $art_options, 
            'condition' => ['query_source' => 'manual', 'post_type_selector' => ['articles', 'both']]
        ]);
        
        $cat_options = []; 
        $cats = get_terms(['taxonomy' => 'madenna_cat', 'hide_empty' => false]);
        if (!is_wp_error($cats) && is_array($cats)) { foreach ($cats as $c) $cat_options[$c->term_id] = $c->name; }
        $this->add_control('selected_tool_cats', [
            'label' => 'انتخاب دسته‌بندی', 'type' => \Elementor\Controls_Manager::SELECT2, 'multiple' => true, 'options' => $cat_options, 
            'condition' => ['query_source' => 'by_category']
        ]);

        $this->add_control('layout_mode', [
            'label' => 'طرح نمایش', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'grid',
            'options' => ['grid' => 'شبکه‌ای (Grid)', 'carousel' => 'اسلایدر (Carousel)'],
            'separator' => 'before'
        ]);

        $this->add_responsive_control('columns', [
            'label' => 'تعداد در ردیف', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 12, 'default' => 3, 
            'condition' => ['layout_mode' => 'grid'],
            'selectors' => ['{{WRAPPER}} .mad-tls-grid-wrap' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));']
        ]);

        $this->add_control('carousel_width_mode', [
            'label' => 'تنظیم عرض کارت‌ها', 'type' => \Elementor\Controls_Manager::CHOOSE, 'default' => 'columns',
            'options' => [
                'columns' => ['title' => 'بر اساس ستون', 'icon' => 'eicon-columns'],
                'custom' => ['title' => 'عرض دلخواه', 'icon' => 'eicon-arrows-h'],
            ],
            'condition' => ['layout_mode' => 'carousel']
        ]);

        $this->add_responsive_control('carousel_columns', [
            'label' => 'تعداد کارت در دید', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 10, 'default' => 3,
            'condition' => ['layout_mode' => 'carousel', 'carousel_width_mode' => 'columns'],
            'selectors' => [
                '{{WRAPPER}} .mad-tls-carousel-track' => '--mad-car-cols: {{VALUE}};',
                '{{WRAPPER}} .mad-tls-carousel-track > .mad-carousel-slide' => 'width: calc((100% - (var(--mad-gap, 20px) * ({{VALUE}} - var(--mad-peek, 0) - 1))) / ({{VALUE}} - var(--mad-peek, 0))); flex: 0 0 calc((100% - (var(--mad-gap, 20px) * ({{VALUE}} - var(--mad-peek, 0) - 1))) / ({{VALUE}} - var(--mad-peek, 0))); max-width: calc((100% - (var(--mad-gap, 20px) * ({{VALUE}} - var(--mad-peek, 0) - 1))) / ({{VALUE}} - var(--mad-peek, 0)));'
            ]
        ]);

        $this->add_responsive_control('carousel_peek', [
            'label' => 'نمایش لبه‌ی کارت بعدی (Peek)', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes',
            'condition' => ['layout_mode' => 'carousel', 'carousel_width_mode' => 'columns'],
            'selectors_dictionary' => [
                'yes' => '0.3',
                '' => '0',
            ],
            'selectors' => [
                '{{WRAPPER}} .mad-tls-carousel-track' => '--mad-peek: {{VALUE}};'
            ]
        ]);

        $this->add_responsive_control('custom_card_width', [
            'label' => 'عرض اختصاصی کارت', 'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', '%', 'vw'],
            'range' => ['px' => ['min' => 150, 'max' => 600], '%' => ['min' => 10, 'max' => 100], 'vw' => ['min' => 10, 'max' => 100]],
            'condition' => ['layout_mode' => 'carousel', 'carousel_width_mode' => 'custom'],
            'selectors' => [
                '{{WRAPPER}} .mad-tls-carousel-track > .mad-carousel-slide' => 'width: {{SIZE}}{{UNIT}} !important; flex: 0 0 {{SIZE}}{{UNIT}} !important; max-width: {{SIZE}}{{UNIT}} !important;'
            ]
        ]);

        $this->add_control('scroll_amount', [
            'label' => 'تعداد پرش در هر کلیک', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 5, 'default' => 1,
            'condition' => ['layout_mode' => 'carousel']
        ]);

        $this->add_control('autoplay', [
            'label' => 'پخش خودکار', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'no',
            'condition' => ['layout_mode' => 'carousel']
        ]);
        
        $this->add_control('autoplay_speed', [
            'label' => 'سرعت پخش (ثانیه)', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 10, 'default' => 3,
            'condition' => ['layout_mode' => 'carousel', 'autoplay' => 'yes']
        ]);

        $this->add_control('carousel_nav', [
            'label' => 'نمایش فلش‌های کنترلی', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes',
            'condition' => ['layout_mode' => 'carousel']
        ]);
        
        $this->add_control('hide_scrollbar', [
            'label' => 'مخفی کردن نوار اسکرول', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes',
            'condition' => ['layout_mode' => 'carousel'],
            'selectors' => [
                '{{WRAPPER}} .mad-tls-carousel-track' => '-ms-overflow-style: none; scrollbar-width: none;',
                '{{WRAPPER}} .mad-tls-carousel-track::-webkit-scrollbar' => 'display: none;'
            ]
        ]);

        $this->add_responsive_control('grid_gap', [
            'label' => 'فاصله بین کارت‌ها', 'type' => \Elementor\Controls_Manager::SLIDER, 
            'default' => ['size' => 20, 'unit' => 'px'],
            'selectors' => ['{{WRAPPER}} .mad-tls-grid-wrap' => 'gap: {{SIZE}}{{UNIT}};', '{{WRAPPER}} .mad-tls-carousel-track' => 'gap: {{SIZE}}{{UNIT}}; --mad-gap: {{SIZE}}{{UNIT}};']
        ]);
        $this->end_controls_section();
        
        $this->start_controls_section('sec_display', ['label' => 'نمایش اجزا', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);
        foreach (['image'=>'تصویر', 'icon'=>'آیکون', 'svg'=>'فایل SVG', 'title'=>'عنوان', 'subtitle'=>'زیرعنوان', 'excerpt'=>'چکیده', 'button'=>'دکمه'] as $k => $l) {
            $this->add_control("show_$k", ['label' => "نمایش $l", 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes']);
        }

        $this->add_control('show_labels', [
            'label' => 'نمایش لیبل‌ها (روی کارت)',
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
            'separator' => 'before'
        ]);

        $this->add_control('show_cat_label', [
            'label' => 'نمایش لیبل دسته‌بندی',
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
            'condition' => ['show_labels' => 'yes']
        ]);

        $this->add_control('show_tag_label', [
            'label' => 'نمایش لیبل برچسب',
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
            'condition' => ['show_labels' => 'yes']
        ]);

        $this->add_control('show_reading_time', [
            'label' => 'نمایش زمان مطالعه', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes',
            'condition' => ['post_type_selector' => ['articles', 'both']],
            'separator' => 'before'
        ]);
        $this->add_control('show_pagination', [
            'label' => 'نمایش صفحه‌بندی (شماره صفحات)', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'no',
            'condition' => ['layout_mode' => 'grid']
        ]);
        
        $this->add_control('button_text', ['label' => 'متن دکمه (پیش‌فرض/ابزار)', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'مشاهده ←', 'condition' => ['show_button' => 'yes']]);
        
        $this->add_control('button_text_article', [
            'label' => 'متن دکمه (مقالات)', 
            'type' => \Elementor\Controls_Manager::TEXT, 
            'default' => 'مطالعه مقاله ←', 
            'condition' => ['show_button' => 'yes', 'post_type_selector' => 'both']
        ]);

        $this->add_control('use_term_icon_color', ['label' => 'ارث‌بری رنگ آیکون', 'description' => 'اگر خاموش باشد، می‌توانید در تب استایل رنگ دلخواه تنظیم کنید.', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_loop_header', ['label' => 'سربرگ زیردسته‌ها (تکرار)', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['loop_subcategories' => 'yes']]);
        
        $this->add_responsive_control('loop_header_margin', ['label' => 'فاصله پایین تا کروسل', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-loop-header' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('loop_block_margin', ['label' => 'فاصله بین بلاک‌های تکرار', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-loop-block-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
        
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'loop_title_typ', 'label' => 'تایپوگرافی تیتر', 'selector' => '{{WRAPPER}} .mad-loop-title']);
        $this->add_control('loop_title_color', ['label' => 'رنگ تیتر', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-loop-title' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('loop_icon_size', ['label' => 'سایز آیکون', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-loop-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: calc({{SIZE}}{{UNIT}} / 2);']]);
        
        $this->add_control('loop_heading_btn', ['label' => 'استایل دکمه', 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'loop_btn_typ', 'label' => 'تایپوگرافی دکمه', 'selector' => '{{WRAPPER}} .mad-loop-btn']);
        
        $this->start_controls_tabs('loop_btn_tabs');
        $this->start_controls_tab('loop_btn_normal', ['label' => 'عادی']);
        $this->add_control('loop_btn_color', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-loop-btn' => 'color: {{VALUE}};']]);
        $this->add_control('loop_btn_bg', ['label' => 'پس‌زمینه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-loop-btn' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), ['name' => 'loop_btn_border', 'selector' => '{{WRAPPER}} .mad-loop-btn']);
        $this->end_controls_tab();
        
        $this->start_controls_tab('loop_btn_hover', ['label' => 'هاور']);
        $this->add_control('loop_btn_color_hover', ['label' => 'رنگ متن (هاور)', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-loop-btn:hover' => 'color: {{VALUE}};']]);
        $this->add_control('loop_btn_bg_hover', ['label' => 'پس‌زمینه (هاور)', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-loop-btn:hover' => 'background-color: {{VALUE}};']]);
        $this->add_control('loop_btn_border_hover', ['label' => 'رنگ حاشیه (هاور)', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-loop-btn:hover' => 'border-color: {{VALUE}};']]);
        $this->end_controls_tab();
        $this->end_controls_tabs();
        
        $this->add_responsive_control('loop_btn_radius', ['label' => 'انحنای دکمه', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-loop-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'separator' => 'before']);
        $this->add_responsive_control('loop_btn_padding', ['label' => 'پدینگ دکمه', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-loop-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_layout', ['label' => 'چیدمان آزاد و رسپانسیو کارت', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('card_direction', [
            'label' => 'جهت کارت (تصویر و بدنه)', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['row' => ['title' => 'افقی', 'icon' => 'eicon-h-align-left'], 'column' => ['title' => 'عمودی', 'icon' => 'eicon-v-align-top'], 'row-reverse' => ['title' => 'افقی معکوس', 'icon' => 'eicon-h-align-right'], 'column-reverse' => ['title' => 'عمودی معکوس', 'icon' => 'eicon-v-align-bottom']],
            'default' => 'column',
            'selectors' => ['{{WRAPPER}} .mad-tool-item' => 'flex-direction: {{VALUE}};']
        ]);
        $this->add_responsive_control('content_align', [
            'label' => 'تراز متن', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'right' => ['title' => 'راست', 'icon' => 'eicon-text-align-right'],
                'center' => ['title' => 'وسط', 'icon' => 'eicon-text-align-center'],
                'left' => ['title' => 'چپ', 'icon' => 'eicon-text-align-left'],
                'justify' => ['title' => 'تراز (Justify)', 'icon' => 'eicon-text-align-justify']
            ],
            'selectors' => [
                '{{WRAPPER}} .mad-tool-content-wrap' => 'text-align: {{VALUE}};',
                '{{WRAPPER}} .mad-tool-excerpt' => 'text-align: {{VALUE}};',
                '{{WRAPPER}} .mad-tool-subtitle' => 'text-align: {{VALUE}};'
            ]
        ]);
        $this->add_responsive_control('content_h_align', [
            'label' => 'تراز افقی بدنه (Flex)', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['flex-start' => ['title' => 'راست', 'icon' => 'eicon-h-align-right'], 'center' => ['title' => 'وسط', 'icon' => 'eicon-h-align-center'], 'flex-end' => ['title' => 'چپ', 'icon' => 'eicon-h-align-left'], 'stretch' => ['title' => 'کشیده', 'icon' => 'eicon-h-align-stretch']],
            'selectors' => ['{{WRAPPER}} .mad-tool-content-wrap' => 'align-items: {{VALUE}} !important;']
        ]);
        $this->add_responsive_control('content_v_align', [
            'label' => 'توزیع عمودی محتوا', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['flex-start' => ['title' => 'بالا', 'icon' => 'eicon-v-align-top'], 'center' => ['title' => 'وسط', 'icon' => 'eicon-v-align-middle'], 'flex-end' => ['title' => 'پایین', 'icon' => 'eicon-v-align-bottom'], 'space-between' => ['title' => 'پخش', 'icon' => 'eicon-v-align-stretch']],
            'default' => 'flex-start',
            'selectors' => ['{{WRAPPER}} .mad-tool-content-wrap' => 'justify-content: {{VALUE}} !important;']
        ]);
        $this->add_responsive_control('head_direction', [
            'label' => 'جهت سربرگ (آیکون و عنوان)', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['row' => ['title' => 'افقی', 'icon' => 'eicon-h-align-left'], 'column' => ['title' => 'عمودی', 'icon' => 'eicon-v-align-top']],
            'selectors' => ['{{WRAPPER}} .mad-tool-content-head' => 'flex-direction: {{VALUE}};']
        ]);
        
        $this->add_responsive_control('head_align_items', [
            'label' => 'تراز عرضی سربرگ (متقاطع)', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['flex-start' => ['title' => 'شروع', 'icon' => 'eicon-align-start-h'], 'center' => ['title' => 'وسط', 'icon' => 'eicon-align-center-h'], 'flex-end' => ['title' => 'پایان', 'icon' => 'eicon-align-end-h']],
            'selectors' => ['{{WRAPPER}} .mad-tool-content-head' => 'align-items: {{VALUE}};']
        ]);
        
        $this->add_responsive_control('head_justify_content', [
            'label' => 'تراز طولی سربرگ (اصلی)', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['flex-start' => ['title' => 'شروع', 'icon' => 'eicon-align-start-h'], 'center' => ['title' => 'وسط', 'icon' => 'eicon-align-center-h'], 'flex-end' => ['title' => 'پایان', 'icon' => 'eicon-align-end-h'], 'space-between' => ['title' => 'پخش', 'icon' => 'eicon-justify-space-between-h']],
            'selectors' => ['{{WRAPPER}} .mad-tool-content-head' => 'justify-content: {{VALUE}};']
        ]);

        $this->add_responsive_control('head_gap', ['label' => 'فاصله اجزای سربرگ', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-tool-content-head' => 'gap: {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();
        
        $this->start_controls_section('sec_style_box', ['label' => 'جعبه و کادر', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('equal_height', [
            'label' => 'ارتفاع کارت‌ها', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['stretch' => ['title' => 'هم‌ارتفاع', 'icon' => 'eicon-v-align-stretch'], 'flex-start' => ['title' => 'فیت محتوا', 'icon' => 'eicon-v-align-top']],
            'default' => 'flex-start',
            'selectors' => ['{{WRAPPER}} .mad-tls-grid-wrap' => 'align-items: {{VALUE}};', '{{WRAPPER}} .mad-tls-carousel-track' => 'align-items: {{VALUE}};']
        ]);
        $this->add_responsive_control('card_min_height', [
            'label' => 'حداقل ارتفاع کارت', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => ['px', 'vh'], 'range' => ['px' => ['min' => 50, 'max' => 800]],
            'selectors' => ['{{WRAPPER}} .mad-tool-item' => 'min-height: {{SIZE}}{{UNIT}};']
        ]);
        $this->add_control('bg_color', ['label' => 'پس‌زمینه کارت', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-item' => 'background-color: {{VALUE}};']]);
        
        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            'name' => 'card_border',
            'label' => 'حاشیه کارت (چهار طرف)',
            'selector' => '{{WRAPPER}} .mad-tool-item',
        ]);
        $this->add_control('border_color', ['label' => 'رنگ حاشیه (ساده)', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-item' => 'border-color: {{VALUE}};']]);
        
        $this->add_control('hover_bg_color', ['label' => 'پس‌زمینه (هاور)', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-item:hover' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), ['name' => 'box_shadow', 'selector' => '{{WRAPPER}} .mad-tool-item']);
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), ['name' => 'box_shadow_hover', 'label' => 'سایه هاور', 'selector' => '{{WRAPPER}} .mad-tool-item:hover']);
        $this->add_responsive_control('border_radius', ['label' => 'انحنا', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('card_padding', ['label' => 'پدینگ داخلی', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-content-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_labels', ['label' => 'لیبل‌های روی کارت', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_labels' => 'yes']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name' => 'labels_typography',
            'label' => 'تایپوگرافی لیبل‌ها',
            'selector' => '{{WRAPPER}} .mad-tool-badge'
        ]);
        $this->add_responsive_control('labels_gap', [
            'label' => 'فاصله بین دو لیبل',
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => ['{{WRAPPER}} .mad-card-labels' => 'gap: {{SIZE}}{{UNIT}} !important;']
        ]);
        $this->add_responsive_control('labels_padding', [
            'label' => 'پدینگ داخلی لیبل‌ها',
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => ['{{WRAPPER}} .mad-tool-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']
        ]);
        $this->add_responsive_control('labels_radius', [
            'label' => 'انحنای لیبل‌ها',
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => ['{{WRAPPER}} .mad-tool-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']
        ]);
        $this->add_control('cat_label_heading', [
            'label' => 'رنگ اختصاصی لیبل دسته‌بندی (در صورت نیاز)',
            'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before'
        ]);
        $this->add_control('cat_label_color', [
            'label' => 'رنگ متن لیبل دسته',
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .mad-cat-label' => 'color: {{VALUE}} !important;']
        ]);
        $this->add_control('cat_label_bg', [
            'label' => 'رنگ پس‌زمینه لیبل دسته',
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .mad-cat-label' => 'background-color: {{VALUE}} !important;']
        ]);
        $this->add_control('tag_label_heading', [
            'label' => 'رنگ اختصاصی لیبل برچسب (در صورت نیاز)',
            'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before'
        ]);
        $this->add_control('tag_label_color', [
            'label' => 'رنگ متن لیبل برچسب',
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .mad-tag-label' => 'color: {{VALUE}} !important;']
        ]);
        $this->add_control('tag_label_bg', [
            'label' => 'رنگ پس‌زمینه لیبل برچسب',
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .mad-tag-label' => 'background-color: {{VALUE}} !important;']
        ]);
        $this->add_control('tag_label_border', [
            'label' => 'رنگ حاشیه لیبل برچسب',
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .mad-tag-label' => 'border-color: {{VALUE}} !important;']
        ]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_img', ['label' => 'تصویر کاور', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_image' => 'yes']]);
        $this->add_responsive_control('img_width', ['label' => 'عرض تصویر', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .mad-tool-img-wrap' => 'width: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('img_height', ['label' => 'ارتفاع تصویر', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => ['px', '%', 'vh'], 'selectors' => ['{{WRAPPER}} .mad-tool-img-wrap' => 'height: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('img_radius', ['label' => 'انحنای تصویر', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-img-wrap img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_svg', ['label' => 'فایل SVG', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_svg' => 'yes']]);
        $this->add_responsive_control('svg_order', ['label' => 'اولویت قرارگیری (Order)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 1, 'selectors' => ['{{WRAPPER}} .mad-tool-svg' => 'order: {{VALUE}};']]);
        $this->add_control('custom_svg_color', ['label' => 'رنگ SVG', 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => ['use_term_icon_color' => ''], 'selectors' => ['{{WRAPPER}} .mad-tool-svg' => 'color: {{VALUE}} !important;']]);
        $this->add_control('custom_svg_bg', ['label' => 'پس‌زمینه', 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => ['use_term_icon_color' => ''], 'selectors' => ['{{WRAPPER}} .mad-tool-svg' => 'background-color: {{VALUE}} !important;']]);
        $this->add_responsive_control('svg_size', ['label' => 'اندازه SVG', 'type' => \Elementor\Controls_Manager::SLIDER, 'default' => ['size' => 24], 'selectors' => ['{{WRAPPER}} .mad-svg-mask' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;']]);
        $this->add_responsive_control('svg_box_width', ['label' => 'عرض باکس', 'type' => \Elementor\Controls_Manager::SLIDER, 'default' => ['size' => 44], 'selectors' => ['{{WRAPPER}} .mad-tool-svg' => 'width: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('svg_box_height', ['label' => 'ارتفاع باکس', 'type' => \Elementor\Controls_Manager::SLIDER, 'default' => ['size' => 44], 'selectors' => ['{{WRAPPER}} .mad-tool-svg' => 'height: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('svg_radius', ['label' => 'انحنای باکس', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-svg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_icon', ['label' => 'آیکون (فونت)', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_icon' => 'yes']]);
        $this->add_responsive_control('icon_order', ['label' => 'اولویت قرارگیری (Order)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 2, 'selectors' => ['{{WRAPPER}} .mad-tool-icon' => 'order: {{VALUE}};']]);
        $this->add_control('custom_icon_color', ['label' => 'رنگ آیکون', 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => ['use_term_icon_color' => ''], 'selectors' => ['{{WRAPPER}} .mad-tool-icon' => 'color: {{VALUE}} !important;']]);
        $this->add_control('custom_icon_bg', ['label' => 'پس‌زمینه', 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => ['use_term_icon_color' => ''], 'selectors' => ['{{WRAPPER}} .mad-tool-icon' => 'background-color: {{VALUE}} !important;']]);
        $this->add_responsive_control('icon_size', ['label' => 'اندازه آیکون', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-tool-icon i, {{WRAPPER}} .mad-tool-icon span' => 'font-size: {{SIZE}}{{UNIT}} !important;']]);
        $this->add_responsive_control('icon_box_width', ['label' => 'عرض باکس', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-tool-icon' => 'width: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('icon_box_height', ['label' => 'ارتفاع باکس', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-tool-icon' => 'height: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('icon_radius', ['label' => 'انحنا', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_title', ['label' => 'عنوان', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_title' => 'yes']]);
        $this->add_responsive_control('title_order', ['label' => 'اولویت (Order)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'selectors' => ['{{WRAPPER}} .mad-tool-title' => 'order: {{VALUE}};']]);
        $this->add_control('title_full', ['label' => 'شکستن خط (100% عرض)', 'type' => \Elementor\Controls_Manager::SWITCHER, 'selectors' => ['{{WRAPPER}} .mad-tool-title' => 'flex-basis: 100%;']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'title_typography', 'selector' => '{{WRAPPER}} .mad-tool-title']);
        $this->add_control('title_color', ['label' => 'رنگ عنوان', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-title' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('title_lines', ['label' => 'محدودیت خطوط', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 10, 'selectors' => ['{{WRAPPER}} .mad-tool-title' => 'display: -webkit-box; -webkit-line-clamp: {{VALUE}}; -webkit-box-orient: vertical; overflow: hidden;']]);
        $this->add_responsive_control('title_margin', ['label' => 'فاصله', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_subtitle', ['label' => 'زیرعنوان', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_subtitle' => 'yes']]);
        $this->add_responsive_control('sub_order', ['label' => 'اولویت (Order)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 4, 'selectors' => ['{{WRAPPER}} .mad-tool-subtitle' => 'order: {{VALUE}};']]);
        $this->add_control('sub_full', ['label' => 'شکستن خط (100% عرض)', 'type' => \Elementor\Controls_Manager::SWITCHER, 'selectors' => ['{{WRAPPER}} .mad-tool-subtitle' => 'flex-basis: 100%;']]);
        
        $this->add_control('sub_chars', [
            'label' => 'محدودیت تعداد کاراکتر',
            'description' => 'دقیقاً بر اساس تعداد حروف کات شده و سه نقطه گذاشته می‌شود.',
            'type' => \Elementor\Controls_Manager::NUMBER,
            'min' => 5,
            'max' => 500,
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'sub_typography', 'selector' => '{{WRAPPER}} .mad-tool-subtitle']);
        $this->add_control('sub_color', ['label' => 'رنگ', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-subtitle' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('sub_lines', ['label' => 'محدودیت خطوط', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 10, 'selectors' => ['{{WRAPPER}} .mad-tool-subtitle' => 'display: -webkit-box; -webkit-line-clamp: {{VALUE}}; -webkit-box-orient: vertical; overflow: hidden;']]);
        $this->add_responsive_control('sub_margin', ['label' => 'فاصله', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-subtitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_excerpt', ['label' => 'چکیده', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_excerpt' => 'yes']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'exc_typography', 'selector' => '{{WRAPPER}} .mad-tool-excerpt']);
        $this->add_control('exc_color', ['label' => 'رنگ چکیده', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-excerpt' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('exc_lines', ['label' => 'محدودیت خطوط', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 10, 'selectors' => ['{{WRAPPER}} .mad-tool-excerpt' => 'display: -webkit-box; -webkit-line-clamp: {{VALUE}}; -webkit-box-orient: vertical; overflow: hidden;']]);
        $this->add_responsive_control('exc_margin', ['label' => 'فاصله', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_btn', ['label' => 'دکمه', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_button' => 'yes']]);
        $this->add_responsive_control('btn_width', [
            'label' => 'عرض دکمه', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['inline-flex' => ['title' => 'متناسب', 'icon' => 'eicon-h-align-center'], '100%' => ['title' => 'تمام‌عرض', 'icon' => 'eicon-h-align-stretch']],
            'selectors' => ['{{WRAPPER}} .mad-tool-btn' => 'width: {{VALUE}};']
        ]);
        $this->add_responsive_control('btn_align', [
            'label' => 'تراز (وقتی متناسب است)', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['flex-start' => ['title' => 'راست', 'icon' => 'eicon-h-align-right'], 'center' => ['title' => 'وسط', 'icon' => 'eicon-h-align-center'], 'flex-end' => ['title' => 'چپ', 'icon' => 'eicon-h-align-left']],
            'selectors' => ['{{WRAPPER}} .mad-tool-btn-wrap' => 'justify-content: {{VALUE}};']
        ]);
        $this->add_control('btn_push_bottom', [
            'label' => 'چسبیدن به کف کارت', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes',
            'selectors' => ['{{WRAPPER}} .mad-tool-btn-wrap' => 'margin-top: auto;']
        ]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name' => 'btn_typography', 
            'selector' => '{{WRAPPER}} .mad-tool-item .mad-tool-btn, {{WRAPPER}} .mad-tool-btn'
        ]);
        
        $this->start_controls_tabs('btn_tabs');
        $this->start_controls_tab('btn_normal', ['label' => 'عادی']);
        $this->add_control('btn_color', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-btn' => 'color: {{VALUE}};']]);
        $this->add_control('btn_bg', ['label' => 'پس‌زمینه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-btn' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), ['name' => 'btn_border', 'selector' => '{{WRAPPER}} .mad-tool-btn']);
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), ['name' => 'btn_shadow', 'selector' => '{{WRAPPER}} .mad-tool-btn']);
        $this->end_controls_tab();
        
        $this->start_controls_tab('btn_hover', ['label' => 'هاور']);
        $this->add_control('btn_color_hover', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-btn:hover' => 'color: {{VALUE}};']]);
        $this->add_control('btn_bg_hover', ['label' => 'پس‌زمینه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-btn:hover' => 'background-color: {{VALUE}};']]);
        $this->add_control('btn_border_color_hover', ['label' => 'رنگ حاشیه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-tool-btn:hover' => 'border-color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), ['name' => 'btn_shadow_hover', 'selector' => '{{WRAPPER}} .mad-tool-btn:hover']);
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control('btn_radius', ['label' => 'انحنا', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'separator' => 'before']);
        $this->add_responsive_control('btn_padding', ['label' => 'پدینگ داخلی', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('btn_margin', ['label' => 'فاصله خارجی', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-tool-btn-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();
    }

    private function get_current_term_id_robustly() {
        if (is_tax('madenna_cat') || is_tax('madenna_tool_tag') || is_category() || is_tag()) return get_queried_object_id();
        $slug = get_query_var('madenna_cat');
        if (!empty($slug)) { $term = get_term_by('slug', $slug, 'madenna_cat'); if ($term && !is_wp_error($term)) return $term->term_id; }
        $tag_slug = get_query_var('madenna_tool_tag');
        if (!empty($tag_slug)) { $term = get_term_by('slug', $tag_slug, 'madenna_tool_tag'); if ($term && !is_wp_error($term)) return $term->term_id; }
        $obj = get_queried_object(); if ($obj instanceof \WP_Term && isset($obj->term_id)) return $obj->term_id;
        return 0; 
    }

    private function get_base_query_args($s) {
        $p_count = (isset($s['posts_count']) && trim($s['posts_count']) !== '') ? intval($s['posts_count']) : -1;
        $pt = $s['post_type_selector'] ?? 'tools';
        $post_type = ($pt === 'both') ? ['tools', 'articles'] : $pt;
        $paged = max(1, get_query_var('paged'), get_query_var('page'), (isset($_POST['paged']) ? intval($_POST['paged']) : 1));
        
        $args = ['post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => $p_count, 'paged' => $paged];
        
        // اعمال هوشمند جستجو در لود اولیه (بدون نیاز به AJAX)
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $args['s'] = sanitize_text_field($_GET['s']);
        }
        
        // همگام‌سازی فیلتر نوع محتوا با تب‌ها در لود اولیه
        if (isset($_GET['post_type']) && in_array($_GET['post_type'], ['tools', 'articles', 'any'])) {
            if ($_GET['post_type'] !== 'any') {
                $args['post_type'] = sanitize_text_field($_GET['post_type']);
            }
        }
        
        return $args;
    }

    private function build_query_args_normally($s, $source, $is_edit, $current_term_id) {
        $args = $this->get_base_query_args($s);
        $pt = $s['post_type_selector'] ?? 'tools';

        // --- منطق هوشمند برچسب‌ها (تکسونومی madenna_tool_tag) ---
        $selected_tags = !empty($s['selected_tool_tags']) && is_array($s['selected_tool_tags']) ? array_map('intval', $s['selected_tool_tags']) : [];
        if ($source !== 'manual') {
            if (!empty($selected_tags)) {
                if (!isset($args['tax_query'])) $args['tax_query'] = [];
                $args['tax_query'][] = ['taxonomy' => 'madenna_tool_tag', 'field' => 'term_id', 'terms' => $selected_tags];
            } elseif (is_tax('madenna_tool_tag') && $current_term_id) {
                if (!isset($args['tax_query'])) $args['tax_query'] = [];
                $args['tax_query'][] = ['taxonomy' => 'madenna_tool_tag', 'field' => 'term_id', 'terms' => $current_term_id];
            } elseif (is_singular('tools') && in_array($pt, ['tools', 'both']) && $source === 'by_tag') {
                $current_tool_tags = wp_get_post_terms(get_the_ID(), 'madenna_tool_tag', ['fields' => 'ids']);
                if (!empty($current_tool_tags) && !is_wp_error($current_tool_tags)) {
                    if (!isset($args['tax_query'])) $args['tax_query'] = [];
                    $args['tax_query'][] = ['taxonomy' => 'madenna_tool_tag', 'field' => 'term_id', 'terms' => $current_tool_tags];
                    $args['post__not_in'] = empty($args['post__not_in']) ? [get_the_ID()] : array_merge($args['post__not_in'], [get_the_ID()]);
                }
            }
        }
        // ------------------------------

        if ($source === 'current_term') {
            if ($current_term_id) {
                $queried_obj = get_queried_object();
                $tax_name = ($queried_obj instanceof \WP_Term && !empty($queried_obj->taxonomy)) ? $queried_obj->taxonomy : (is_tax('madenna_tool_tag') ? 'madenna_tool_tag' : 'madenna_cat');
                $args['tax_query'] = [['taxonomy' => $tax_name, 'field' => 'term_id', 'terms' => $current_term_id, 'include_children' => true]];
            } else {
                if (!$is_edit) $args['post__in'] = [0];
            }
        } elseif ($source === 'featured') {
            $args['meta_key'] = '_madenna_is_featured';
            $args['meta_value'] = 'yes';
        } elseif ($source === 'by_category' && !empty($s['selected_tool_cats'])) {
            if (!isset($args['tax_query'])) $args['tax_query'] = [];
            $args['tax_query'][] = [['taxonomy' => 'madenna_cat', 'field' => 'term_id', 'terms' => $s['selected_tool_cats'], 'include_children' => true]];
        } elseif ($source === 'related_same_type') {
            if (is_singular(['articles', 'tools'])) {
                $current_id = get_the_ID();
                $args['post_type'] = ($pt === 'both') ? get_post_type($current_id) : $pt;
                $args['post__not_in'] = [$current_id];
                $terms = wp_get_post_terms($current_id, 'madenna_cat', ['fields' => 'ids']);
                if (!empty($terms) && !is_wp_error($terms)) {
                    if (!isset($args['tax_query'])) $args['tax_query'] = [];
                    $args['tax_query'][] = ['taxonomy' => 'madenna_cat', 'field' => 'term_id', 'terms' => $terms];
                } else {
                    if (!$is_edit) $args['post__in'] = [0];
                }
            } else {
                if (!$is_edit) $args['post__in'] = [0];
            }
        } elseif ($source === 'related') {
            if ($pt === 'tools' && is_singular('articles')) {
                $rel = get_post_meta(get_the_ID(), '_related_tools', true);
                if (empty($rel) || !is_array($rel)) $args['post__in'] = [0]; else { $args['post__in'] = $rel; $args['orderby'] = 'post__in'; }
            } elseif ($pt === 'articles' && is_singular('tools')) {
                $rel = get_post_meta(get_the_ID(), '_related_articles', true);
                if (empty($rel) || !is_array($rel)) $args['post__in'] = [0]; else { $args['post__in'] = $rel; $args['orderby'] = 'post__in'; }
            } else { if (!$is_edit) $args['post__in'] = [0]; }
        } elseif ($source === 'manual') {
            if ($pt === 'tools' && !empty($s['selected_tools'])) { $args['post__in'] = $s['selected_tools']; $args['orderby'] = 'post__in'; } elseif ($pt === 'articles' && !empty($s['selected_articles'])) { $args['post__in'] = $s['selected_articles']; $args['orderby'] = 'post__in'; } elseif ($pt === 'both') {
                $merged_manual = array_merge(is_array($s['selected_tools'] ?? null) ? $s['selected_tools'] : [], is_array($s['selected_articles'] ?? null) ? $s['selected_articles'] : []);
                if (!empty($merged_manual)) { $args['post__in'] = $merged_manual; $args['orderby'] = 'post__in'; } else { if (!$is_edit) $args['post__in'] = [0]; }
            } else { if (!$is_edit) $args['post__in'] = [0]; }
        }
        return $args;
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $source = $s['query_source'] ?? 'latest';
        $pt = $s['post_type_selector'] ?? 'tools';
        $is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $current_term_id = $this->get_current_term_id_robustly();
        $is_loop = (($source === 'current_term' || $source === 'by_category') && ($s['loop_subcategories'] ?? 'no') === 'yes');

        if ($is_edit && $source === 'current_term' && !$current_term_id) {
            echo '<div style="padding:10px; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:5px; margin-bottom:15px; font-size:12px;">⚠️ توجه: شما منبع را روی "دسته‌بندی فعلی" تنظیم کرده‌اید، اما این صفحه یک آرشیو دسته‌بندی نیست! برای تست، ما چند آیتم فرضی نشان می‌دهیم، اما در سایت زنده این ویجت مخفی خواهد شد.</div>';
        }

        if ($is_loop) {
            $loop_terms = [];
            if ($source === 'current_term' && $current_term_id) {
                $loop_terms = get_terms(['taxonomy' => 'madenna_cat', 'parent' => $current_term_id, 'hide_empty' => false]);
            } elseif ($source === 'by_category' && !empty($s['selected_tool_cats'])) {
                $loop_terms = get_terms(['taxonomy' => 'madenna_cat', 'include' => $s['selected_tool_cats'], 'orderby' => 'include', 'hide_empty' => false]);
            }
            
            if (empty($loop_terms) && $is_edit) {
                $loop_terms = get_terms(['taxonomy' => 'madenna_cat', 'number' => 2, 'hide_empty' => false]);
                if (empty($loop_terms)) $loop_terms = [ (object)[ 'term_id' => 0, 'name' => 'زیردسته‌بندی فرضی', 'taxonomy' => 'madenna_cat' ] ];
            }
            
            if (!empty($loop_terms) && !is_wp_error($loop_terms)) {
                $rendered_any = false;
                foreach ($loop_terms as $index => $term) {
                    $args = $this->get_base_query_args($s);
                    if (isset($term->term_id) && $term->term_id > 0) $args['tax_query'] = [['taxonomy' => 'madenna_cat', 'field' => 'term_id', 'terms' => $term->term_id, 'include_children' => true]];
                    $query = new \WP_Query($args);
                    
                    if (!$query->have_posts() && $is_edit) $query = new \WP_Query(['post_type' => ($pt === 'both' ? ['tools', 'articles'] : $pt), 'posts_per_page' => 3, 'post_status' => 'publish']);
                    
                    if ($query->have_posts()) {
                        $rendered_any = true;
                        echo '<div class="mad-loop-block-wrap">';
                        $this->render_subcat_header($term, $s);
                        $this->render_grid_carousel_with_query($query, $s, $index + 1, (isset($term->term_id) ? $term->term_id : 0));
                        echo '</div>';
                    }
                }
                if ($rendered_any) return; 
            }
        }
        
        $args = $this->build_query_args_normally($s, $source, $is_edit, $current_term_id);
        $query = new \WP_Query($args);
        
        if (!$query->have_posts() && $is_edit) $query = new \WP_Query(['post_type' => ($pt === 'both' ? ['tools', 'articles'] : $pt), 'posts_per_page' => 3, 'post_status' => 'publish']);

        if (!$query->have_posts() && !$is_edit) {
            if (current_user_can('edit_posts')) {
                echo '<div style="padding:15px; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:8px; margin:20px 0; font-family:sans-serif; font-size:13px; text-align:right; direction:rtl;">';
                echo '<strong>⚠️ پیام مدیریت (فقط شما این را می‌بینید):</strong><br>ویجت "گرید هوشمند" در این بخش هیچ محتوایی (ابزار/مقاله) برای نمایش پیدا نکرد.</div>';
            }
            return;
        }

        $this->render_grid_carousel_with_query($query, $s, 0, ($source === 'current_term' ? $current_term_id : 0));
    }

    private function render_subcat_header($term, $s) {
        $icon_class = isset($term->term_id) && $term->term_id > 0 ? get_term_meta($term->term_id, 'cat_icon_class', true) : 'fa-solid fa-folder-open';
        $icon_url = isset($term->term_id) && $term->term_id > 0 ? get_term_meta($term->term_id, 'cat_icon_url', true) : '';
        $btn_text = $s['subcat_btn_text'] ?? 'مشاهده همه ←';
        $link = isset($term->term_id) && $term->term_id > 0 ? get_term_link($term) : '#';
        
        $pri = isset($term->term_id) && $term->term_id > 0 ? get_term_meta($term->term_id, 'cat_color_primary', true) : '#2563eb';
        $acc = isset($term->term_id) && $term->term_id > 0 ? get_term_meta($term->term_id, 'cat_color_accent', true) : '#eff6ff';
        if(!$pri) $pri = '#2563eb'; if(!$acc) $acc = '#eff6ff';
        
        echo '<div class="mad-loop-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">';
        echo '<div class="mad-loop-head-right" style="display:flex; align-items:center; gap:12px;">';
        
        if ($icon_url || $icon_class) {
            echo '<div class="mad-loop-icon" style="display:flex; align-items:center; justify-content:center; border-radius:10px; color:'.esc_attr($pri).'; background-color:'.esc_attr($acc).';">';
            if ($icon_url) {
                echo '<div class="mad-svg-icon" style="-webkit-mask-image: url('.esc_url($icon_url).'); mask-image: url('.esc_url($icon_url).'); background-color: currentColor; width: 1em; height: 1em; -webkit-mask-size: contain; mask-size: contain; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center;"></div>';
            } elseif (class_exists('Madenna_Helpers')) {
                echo \Madenna_Helpers::render_icon_element($icon_class);
            }
            echo '</div>';
        }
        
        echo '<h3 class="mad-loop-title" style="margin:0; font-weight:800;">'.esc_html($term->name).'</h3>';
        echo '</div>';
        
        if ($btn_text) {
            echo '<a href="'.esc_url($link).'" class="mad-loop-btn" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size:13px; transition:0.3s;">'.esc_html($btn_text).'</a>';
        }
        echo '</div>';
    }

    private function render_grid_carousel_with_query($query, $s, $loop_index = 0, $term_id = 0) {
        $pt = $s['post_type_selector'] ?? 'tools';
        $layout = $s['layout_mode'] ?? 'grid';
        $cid = 'mad-car-' . $this->get_id() . '-' . $loop_index;
        
        // سیستم چیدمان یکی‌درمیان
        if ($pt === 'both' && ($s['alternate_pattern'] ?? 'no') === 'yes' && empty($query->query_vars['post__in']) && empty($_GET['s'])) {
            $p_count = $query->query_vars['posts_per_page'] ?? 6;
            if ($p_count <= 0) $p_count = 6;
            
            $args1 = $query->query_vars; $args1['post_type'] = 'tools'; $args1['posts_per_page'] = ceil($p_count / 2);
            $args2 = $query->query_vars; $args2['post_type'] = 'articles'; $args2['posts_per_page'] = floor($p_count / 2);
            
            $q1 = new \WP_Query($args1);
            $q2 = new \WP_Query($args2);
            
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
        
        // رمزنگاری تنظیمات برای ارسال به AJAX
        $json_settings = htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8');
        echo "<div class='madenna-smart-grid-container' data-settings='{$json_settings}' data-term-id='{$term_id}' style='width:100%; max-width:100%; min-width:0;'>";
        
        if ($layout === 'carousel') {
            $scroll_amount = isset($s['scroll_amount']) ? intval($s['scroll_amount']) : 1;
            $autoplay = (isset($s['autoplay']) && $s['autoplay'] === 'yes') ? 'true' : 'false';
            $speed = isset($s['autoplay_speed']) ? intval($s['autoplay_speed']) : 3;
            
            echo "<div class='mad-tls-carousel-wrap' id='{$cid}' style='position:relative; width:100%; max-width:100%; min-width:0; display:block; margin:auto; direction:ltr;'>";
            
            if (($s['carousel_nav'] ?? 'yes') === 'yes') {
                $arrow_style = "position:absolute; top:50%; z-index:10; width:38px; height:38px; border-radius:50%; background:#fff; border:1px solid #e2e8f0; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); cursor:pointer; display:flex; align-items:center; justify-content:center; color:#0f172a; transition:all 0.2s; margin-top:-19px;";
                echo "<button type='button' title='بعدی' id='{$cid}-prev' style='{$arrow_style} left:-19px;' onmouseover=\"this.style.background='#f8fafc'\" onmouseout=\"this.style.background='#fff'\">&#10094;</button>";
                echo "<button type='button' title='قبلی' id='{$cid}-next' style='{$arrow_style} right:-19px;' onmouseover=\"this.style.background='#f8fafc'\" onmouseout=\"this.style.background='#fff'\">&#10095;</button>";
            }
            
            echo "<div style='position:relative; width:100%; max-width:100%; min-width:0; overflow:hidden; padding: 12px 4px; margin: -12px -4px;'>";
            echo "<div id='{$cid}-track' class='mad-tls-carousel-track' style='display:flex; flex-wrap:nowrap; justify-content:flex-start; width:100%; max-width:100%; min-width:0; overflow-x:auto; scroll-snap-type:x mandatory; scroll-behavior:smooth; direction:rtl;'>";
        } else {
            echo "<div class='mad-tls-grid-wrap' style='display:grid; width:100%; max-width:100%; min-width:0; justify-content:start;'>";
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
            $snap_style = ($layout === 'carousel') ? 'scroll-snap-align:start; flex-shrink:0;' : 'width:100%;';
            
            echo "<a href='" . esc_url(get_permalink()) . "' class='{$item_cls}' style='{$snap_style} display:flex; box-sizing:border-box; overflow:hidden; text-decoration:none; color:inherit; transition:all 0.3s; position:relative;'>";
            
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
            echo "<div class='mad-tool-content-head' style='display:flex; flex-wrap:wrap;'>";
            
            if ($actual_pt === 'articles' && ($s['show_reading_time'] ?? 'yes') === 'yes' && !empty($reading)) {
                echo "<span class='mad-tool-reading' style='display:block; flex-basis:100%; font-size:11px; color:#64748b; margin-bottom:8px; order:0;'>⏱️ زمان مطالعه: {$reading} دقیقه</span>";
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
            
            // رفع مشکل سقوط عنوان زیر آیکون
            echo "<div class='mad-tool-head-text' style='flex:1; min-width:0; display:flex; flex-direction:column; justify-content:center;'>";
            
            $title = get_the_title();
            if (($s['show_title'] ?? 'yes') === 'yes' && !empty($title)) {
                echo "<h3 class='mad-tool-title' style='margin:0; font-weight:800;'>".esc_html($title)."</h3>";
            }
            
            if (($s['show_subtitle'] ?? 'yes') === 'yes' && !empty($sub)) {
                $char_limit = $s['sub_chars'] ?? '';
                if (!empty($char_limit) && mb_strlen($sub) > $char_limit) {
                    $sub = mb_substr($sub, 0, $char_limit) . '...';
                }
                echo "<div class='mad-tool-subtitle'>".esc_html($sub)."</div>";
            }
            
            echo "</div>"; // پایان کانتینر عنوان
            
            echo "</div>"; // end head
            
            $exc = get_the_excerpt();
            if (($s['show_excerpt'] ?? 'yes') === 'yes' && !empty($exc)) {
                echo "<div class='mad-tool-excerpt'>".esc_html($exc)."</div>";
            }
            
            $btn_text = $s['button_text'] ?? 'مشاهده ←';
            if ($actual_pt === 'articles' && $pt === 'both') {
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
            $current_page = max(1, get_query_var('paged'), get_query_var('page'), (isset($_POST['paged']) ? intval($_POST['paged']) : 1));
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
        
        if ($layout === 'carousel') {
            echo "</div></div></div>"; 
            ?>
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                const wrap = document.getElementById('<?php echo $cid; ?>');
                const track = document.getElementById('<?php echo $cid; ?>-track');
                const btnNext = document.getElementById('<?php echo $cid; ?>-next');
                const btnPrev = document.getElementById('<?php echo $cid; ?>-prev');
                const scrollAmount = <?php echo $scroll_amount; ?>;

                if (!track) return;

                function scrollTrack(dir) {
                    const slide = track.querySelector('.mad-carousel-slide');
                    if(!slide) return;
                    const gap = parseFloat(window.getComputedStyle(track).gap) || 0;
                    const step = (slide.offsetWidth + gap) * scrollAmount;
                    
                    let scrollVal = (dir === 'next') ? -step : step; 
                    track.scrollBy({ left: scrollVal, behavior: 'smooth' });
                }

                // اصلاح جهت اسکرول دکمه‌ها برای ساختار راست‌چین (RTL)
                if(btnNext) btnNext.onclick = (e) => { e.preventDefault(); scrollTrack('prev'); };
                if(btnPrev) btnPrev.onclick = (e) => { e.preventDefault(); scrollTrack('next'); };

                if (<?php echo $autoplay; ?>) {
                    let speed = <?php echo $speed * 1000; ?>;
                    let timer;
                    function startAutoplay() { timer = setInterval(() => { scrollTrack('next'); }, speed); }
                    startAutoplay();
                    if(wrap) {
                        wrap.addEventListener('mouseenter', () => clearInterval(timer));
                        wrap.addEventListener('mouseleave', startAutoplay);
                    }
                }
            });
            </script>
            <?php
        } else {
            echo "</div>";
        }
        
        echo "</div>"; // پایان کانتینر جستجوی زنده

        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.madennaLiveSearchAttachedGlobal) return;
            window.madennaLiveSearchAttachedGlobal = true;
            
            document.addEventListener('madennaLiveSearch', function(e) {
                const keyword = e.detail.keyword !== undefined ? e.detail.keyword : (window.madennaCurrentKeyword || '');
                const filters = e.detail.filters !== undefined ? e.detail.filters : (window.madennaCurrentFilters || {});
                const paged   = e.detail.paged !== undefined ? e.detail.paged : 1;
                
                window.madennaCurrentKeyword = keyword;
                window.madennaCurrentFilters = filters;
                window.madennaCurrentPage = paged;
                
                const grids = document.querySelectorAll('.madenna-smart-grid-container');
                
                grids.forEach(wrap => {
                    const track = wrap.querySelector('.mad-tls-grid-wrap') || wrap.querySelector('.mad-tls-carousel-track');
                    if (!track) return;
                    
                    track.style.transition = 'opacity 0.3s';
                    track.style.opacity = '0.4';
                    
                    const formData = new FormData();
                    formData.append('action', 'madenna_live_search');
                    formData.append('keyword', keyword);
                    formData.append('filters', JSON.stringify(filters));
                    formData.append('paged', paged);
                    formData.append('settings', wrap.getAttribute('data-settings'));
                    formData.append('term_id', wrap.getAttribute('data-term-id'));
                    
                    fetch(madenna_ajax.ajax_url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(html => {
                        track.innerHTML = html;
                        track.style.opacity = '1';
                        if (wrap.querySelector('.mad-tls-carousel-track')) {
                            wrap.querySelector('.mad-tls-carousel-track').scrollLeft = 0;
                        }
                    })
                    .catch(err => {
                        console.error('AJAX Error:', err);
                        track.style.opacity = '1';
                    });
                });
            });

            document.addEventListener('click', function(e) {
                if(e.target.classList.contains('mad-page-btn')) {
                    e.preventDefault();
                    const page = e.target.getAttribute('data-page');
                    const event = new CustomEvent('madennaLiveSearch', { detail: { paged: page } });
                    document.dispatchEvent(event);
                    
                    const gridWrap = e.target.closest('.madenna-smart-grid-container');
                    if(gridWrap) {
                        const y = gridWrap.getBoundingClientRect().top + window.scrollY - 100;
                        window.scrollTo({top: y, behavior: 'smooth'});
                    }
                }
            });
        });
        </script>
        <?php
    }
}