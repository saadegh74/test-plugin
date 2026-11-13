<?php
/**
 * ویجت نمایش گرید دسته‌بندی‌های مادنا
 * Location: /inc/elementor/widgets/class-widget-categories.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Categories_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() { return 'madenna_categories'; }
    public function get_title() { return 'دسته‌‌بندی‌های مادنا'; }
    public function get_icon() { return 'eicon-gallery-grid'; }
    public function get_categories() { return ['general']; }
    public function get_style_depends() { return [ 'madenna-elementor-widgets' ]; }
    
    protected function register_controls() {
        $this->start_controls_section('sec_query', ['label' => 'منبع داده', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);
        $this->add_control('query_type', ['label' => 'نوع نمایش', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'parents', 'options' => ['parents' => 'فقط والدها', 'current_subs' => 'زیردسته‌های جاری', 'manual' => 'انتخاب دستی']]);
        
        $this->add_control('click_action', [
            'label' => 'عملکرد هنگام کلیک',
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'link',
            'options' => [
                'link' => 'رفتن به صفحه دسته',
                'filter' => 'تب فیلتر (AJAX گرید هوشمند)'
            ]
        ]);

        $this->add_control('show_all_tab', [
            'label' => 'نمایش تب «همه» در ابتدا',
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
            'condition' => ['click_action' => 'filter']
        ]);

        $this->add_control('all_tab_title', [
            'label' => 'عنوان تب همه',
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'همه دسته‌ها',
            'condition' => ['click_action' => 'filter', 'show_all_tab' => 'yes']
        ]);

        $this->add_control('all_tab_subtitle', [
            'label' => 'زیرعنوان تب همه',
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'نمایش تمام مطالب',
            'condition' => ['click_action' => 'filter', 'show_all_tab' => 'yes']
        ]);

        $this->add_control('all_tab_icon', [
            'label' => 'کلاس آیکون تب همه',
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'fa-solid fa-border-all',
            'condition' => ['click_action' => 'filter', 'show_all_tab' => 'yes']
        ]);

        $options = []; 
        $terms = get_terms(['taxonomy' => 'madenna_cat', 'hide_empty' => false]);
        if (!is_wp_error($terms) && is_array($terms)) {
            foreach ($terms as $term) $options[$term->term_id] = $term->name;
        }
        $this->add_control('selected_cats', ['label' => 'انتخاب دسته‌ها', 'type' => \Elementor\Controls_Manager::SELECT2, 'multiple' => true, 'options' => $options, 'condition' => ['query_type' => 'manual']]);
        
        $this->add_responsive_control('columns', [
            'label' => 'تعداد ستون‌ها', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 12, 'default' => 3, 
            'selectors' => ['{{WRAPPER}} .mad-cats-grid-wrap' => 'grid-template-columns: repeat({{VALUE}}, 1fr);']
        ]);
        $this->add_responsive_control('grid_gap', ['label' => 'فاصله شبکه‌ها', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-cats-grid-wrap' => 'gap: {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();
        
        $this->start_controls_section('sec_display', ['label' => 'نمایش اجزا', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);
        foreach (['image'=>'تصویر', 'icon'=>'آیکون', 'title'=>'عنوان', 'subtitle'=>'زیرعنوان', 'excerpt'=>'چکیده', 'link_button'=>'دکمه'] as $k => $l) {
            $this->add_control("show_$k", ['label' => "نمایش $l", 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes']);
        }
        $this->add_control('button_text', ['label' => 'متن دکمه', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'مشاهده دسته ←', 'condition' => ['show_link_button' => 'yes']]);
        $this->add_control('use_term_icon_color', ['label' => 'ارث‌بری رنگ آیکون', 'description' => 'اگر خاموش باشد، می‌توانید در تب استایل رنگ دلخواه تنظیم کنید.', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_layout', ['label' => 'چیدمان رسپانسیو کارت', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('card_direction', [
            'label' => 'جهت کارت (تصویر و محتوا)', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['row' => ['title' => 'افقی', 'icon' => 'eicon-h-align-left'], 'column' => ['title' => 'عمودی', 'icon' => 'eicon-v-align-top']],
            'selectors' => ['{{WRAPPER}} .mad-cat-item' => 'flex-direction: {{VALUE}};']
        ]);
        $this->add_responsive_control('content_align', [
            'label' => 'تراز محتوا', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['flex-start' => ['title' => 'راست', 'icon' => 'eicon-text-align-right'], 'center' => ['title' => 'وسط', 'icon' => 'eicon-text-align-center'], 'flex-end' => ['title' => 'چپ', 'icon' => 'eicon-text-align-left']],
            'selectors' => [
                '{{WRAPPER}} .mad-cat-content' => 'align-items: {{VALUE}}; text-align: {{VALUE}};',
                '{{WRAPPER}} .mad-cat-content-head' => 'justify-content: {{VALUE}};'
            ]
        ]);
        $this->add_responsive_control('head_direction', [
            'label' => 'جهت سربرگ (آیکون و عنوان)', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => ['row' => ['title' => 'افقی', 'icon' => 'eicon-h-align-left'], 'column' => ['title' => 'عمودی', 'icon' => 'eicon-v-align-top']],
            'selectors' => ['{{WRAPPER}} .mad-cat-content-head' => 'flex-direction: {{VALUE}};']
        ]);
        $this->add_responsive_control('head_gap', ['label' => 'فاصله آیکون و عنوان', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-cat-content-head' => 'gap: {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();
        
        $this->start_controls_section('sec_style_box', ['label' => 'جعبه و کادر', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_control('bg_color', ['label' => 'پس‌زمینه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-cat-item' => 'background-color: {{VALUE}};']]);
        $this->add_control('border_color', ['label' => 'رنگ حاشیه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-cat-item' => 'border-color: {{VALUE}};']]);
        $this->add_control('hover_bg_color', ['label' => 'پس‌‌زمینه (هاور)', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-cat-item:hover' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), ['name' => 'box_shadow', 'selector' => '{{WRAPPER}} .mad-cat-item']);
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), ['name' => 'box_shadow_hover', 'label' => 'سایه هاور', 'selector' => '{{WRAPPER}} .mad-cat-item:hover']);
        $this->add_responsive_control('border_radius', ['label' => 'انحنا', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-cat-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('card_padding', ['label' => 'پدینگ داخلی', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-cat-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_img', ['label' => 'تصویر کاور', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_image' => 'yes']]);
        $this->add_responsive_control('img_width', ['label' => 'عرض تصویر', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .mad-cat-img-wrap' => 'width: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('img_height', ['label' => 'ارتفاع تصویر', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => ['px', '%', 'vh'], 'selectors' => ['{{WRAPPER}} .mad-cat-img-wrap' => 'height: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('img_radius', ['label' => 'انحنای تصویر', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-cat-img-wrap img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_icon', ['label' => 'آیکون', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_icon' => 'yes']]);
        $this->add_control('custom_icon_color', ['label' => 'رنگ یکپارچه آیکون‌ها', 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => ['use_term_icon_color' => ''], 'selectors' => ['{{WRAPPER}} .mad-cat-icon' => 'color: {{VALUE}};']]);
        $this->add_control('custom_icon_bg', ['label' => 'پس‌زمینه یکپارچه آیکون‌ها', 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => ['use_term_icon_color' => ''], 'selectors' => ['{{WRAPPER}} .mad-cat-icon' => 'background-color: {{VALUE}};']]);
        $this->add_responsive_control('icon_size', ['label' => 'اندازه آیکون', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-cat-icon i, {{WRAPPER}} .mad-cat-icon span, {{WRAPPER}} .mad-cat-icon .mad-svg-icon' => 'font-size: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('icon_box_width', ['label' => 'عرض باکس آیکون', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-cat-icon' => 'width: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('icon_box_height', ['label' => 'ارتفاع باکس آیکون', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .mad-cat-icon' => 'height: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('icon_radius', ['label' => 'انحنای باکس آیکون', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-cat-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_title', ['label' => 'عنوان', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_title' => 'yes']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'title_typography', 'selector' => '{{WRAPPER}} .mad-cat-title']);
        $this->add_control('title_color', ['label' => 'رنگ عنوان', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-cat-title' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('title_margin', ['label' => 'فاصله خارجی', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-cat-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_subtitle', ['label' => 'زیرعنوان', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_subtitle' => 'yes']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'sub_typography', 'selector' => '{{WRAPPER}} .mad-cat-subtitle']);
        $this->add_control('sub_color', ['label' => 'رنگ زیرعنوان', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-cat-subtitle' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('sub_margin', ['label' => 'فاصله', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-cat-subtitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_excerpt', ['label' => 'چکیده', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_excerpt' => 'yes']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'exc_typography', 'selector' => '{{WRAPPER}} .mad-cat-excerpt']);
        $this->add_control('exc_color', ['label' => 'رنگ چکیده', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-cat-excerpt' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('exc_margin', ['label' => 'فاصله', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-cat-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();

        $this->start_controls_section('sec_style_btn', ['label' => 'دکمه', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_link_button' => 'yes']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'btn_typography', 'selector' => '{{WRAPPER}} .mad-cat-btn']);
        $this->add_control('btn_color', ['label' => 'رنگ متن', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .mad-cat-btn' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('btn_margin', ['label' => 'فاصله', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .mad-cat-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();
    }
    
    protected function render() {
        $s = $this->get_settings_for_display(); $q = $s['query_type'] ?? 'parents';
        $args = ['taxonomy' => 'madenna_cat', 'hide_empty' => false];
        $obj = get_queried_object();
        
        if ($q === 'parents') { $args['parent'] = 0; } 
        elseif ($q === 'current_subs' && isset($obj->term_id)) { $args['parent'] = $obj->term_id; } 
        elseif ($q === 'manual' && !empty($s['selected_cats'])) { $args['include'] = $s['selected_cats']; $args['orderby'] = 'include'; } 
        else { $args['include'] = [0]; }
        
        $terms = get_terms($args);
        if (empty($terms) || is_wp_error($terms) || !is_array($terms)) return;

        $click_action = $s['click_action'] ?? 'link';
        
        echo "<div class='mad-cats-grid-wrap' id='mad-cats-" . esc_attr($this->get_id()) . "'>";

        // رندر تب «همه» در ابتدای لیست (در حالت تب فیلتر و فعال به صورت پیش‌فرض)
        if ($click_action === 'filter' && ($s['show_all_tab'] ?? 'yes') === 'yes') {
            $all_title = !empty($s['all_tab_title']) ? $s['all_tab_title'] : 'همه دسته‌ها';
            $all_sub   = $s['all_tab_subtitle'] ?? '';
            $all_icon  = !empty($s['all_tab_icon']) ? $s['all_tab_icon'] : 'fa-solid fa-border-all';
            $all_style = (($s['use_term_icon_color'] ?? 'yes') === 'yes') ? "color:#0c5c4e; background-color:#ecfdf5;" : "";

            echo "<div class='mad-cat-item mad-cat-tab-filter active' data-term-id='all' style='cursor:pointer; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: var(--e-global-color-primary, #0c5c4e);'>";
            echo "<div class='mad-cat-content'>";
            echo "<div class='mad-cat-content-head'>";
            if (($s['show_icon'] ?? 'yes') === 'yes' && $all_icon) {
                echo "<div class='mad-cat-icon' style='{$all_style}'>";
                if (class_exists('Madenna_Helpers')) {
                    echo \Madenna_Helpers::render_icon_element($all_icon);
                } else {
                    echo "<i class='" . esc_attr($all_icon) . "'></i>";
                }
                echo "</div>";
            }
            echo "<div class='mad-cat-head-text'>";
            if (in_array($s['show_title'] ?? 'yes', ['yes', 'true', '1', 1, true], true)) echo "<h3 class='mad-cat-title'>" . esc_html($all_title) . "</h3>";
            if (($s['show_subtitle'] ?? 'yes') === 'yes' && !empty($all_sub)) echo "<span class='mad-cat-subtitle'>" . esc_html($all_sub) . "</span>";
            echo "</div></div>";
            if (($s['show_link_button'] ?? 'yes') === 'yes') echo "<div class='mad-cat-btn'>" . esc_html($s['button_text'] ?? 'مشاهده دسته ←') . "</div>";
            echo "</div></div>";
        }

        foreach ($terms as $t) {
            $img = get_term_meta($t->term_id, 'cat_image', true);
            $icn = get_term_meta($t->term_id, 'cat_icon_class', true);
            $svg = get_term_meta($t->term_id, 'cat_icon_url', true);
            
            $style = "";
            if (($s['use_term_icon_color'] ?? 'yes') === 'yes') {
                $i_c = get_term_meta($t->term_id, 'cat_icon_color', true) ?: '#2563eb';
                $i_bg = get_term_meta($t->term_id, 'cat_icon_bg', true) ?: '#eff6ff';
                $style = "color:{$i_c}; background-color:{$i_bg};";
            }
            
            if ($click_action === 'filter') {
                echo "<div class='mad-cat-item mad-cat-tab-filter' data-term-id='" . esc_attr($t->term_id) . "' style='cursor:pointer;'>";
            } else {
                echo "<a href='" . esc_url(get_term_link($t)) . "' class='mad-cat-item'>";
            }

            if (($s['show_image'] ?? 'yes') === 'yes' && $img) echo "<div class='mad-cat-img-wrap'><img src='".esc_url($img)."' style='width:100%;height:100%;object-fit:cover;'></div>";
            
            echo "<div class='mad-cat-content'>";
            echo "<div class='mad-cat-content-head'>";
            
            if (($s['show_icon'] ?? 'yes') === 'yes' && ($svg || $icn)) {
                echo "<div class='mad-cat-icon' style='{$style}'>";
                if ($svg) {
                    echo "<div class='mad-svg-icon' style=\"-webkit-mask-image: url('".esc_url($svg)."'); mask-image: url('".esc_url($svg)."');\"></div>";
                } else {
                    if (class_exists('Madenna_Helpers')) {
                        echo \Madenna_Helpers::render_icon_element($icn);
                    }
                }
                echo "</div>";
            }
            
            echo "<div class='mad-cat-head-text'>";
            if (in_array($s['show_title'] ?? 'yes', ['yes', 'true', '1', 1, true], true)) echo "<h3 class='mad-cat-title'>".esc_html($t->name)."</h3>";
            if (($s['show_subtitle'] ?? 'yes') === 'yes' && $sub=get_term_meta($t->term_id,'cat_subtitle',true)) echo "<span class='mad-cat-subtitle'>".esc_html($sub)."</span>";
            echo "</div></div>";
            
            if (($s['show_excerpt'] ?? 'yes') === 'yes' && $exc=get_term_meta($t->term_id,'cat_excerpt',true)) echo "<p class='mad-cat-excerpt'>".esc_html($exc)."</p>";
            if (($s['show_link_button'] ?? 'yes') === 'yes') echo "<div class='mad-cat-btn'>".esc_html($s['button_text'] ?? 'مشاهده دسته ←')."</div>";
            
            if ($click_action === 'filter') {
                echo "</div></div>";
            } else {
                echo "</div></a>";
            }
        }
        echo "</div>";

        if ($click_action === 'filter') {
            ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const wrap = document.getElementById('mad-cats-<?php echo esc_attr($this->get_id()); ?>');
                if (!wrap) return;
                
                const tabs = wrap.querySelectorAll('.mad-cat-tab-filter');
                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        // استایل دهی به تب فعال
                        tabs.forEach(t => {
                            t.classList.remove('active');
                            t.style.boxShadow = '';
                            t.style.borderColor = '#e2e8f0';
                        });
                        this.classList.add('active');
                        this.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
                        this.style.borderColor = 'var(--e-global-color-primary, #0c5c4e)';
                        
                        const termId = this.getAttribute('data-term-id');
                        const keyword = window.madennaCurrentKeyword || '';
                        const filters = window.madennaCurrentFilters || {};
                        
                        // اگر تب «همه» کلیک شد، فیلتر دسته‌ها خالی شود تا همه را نشان دهد
                        if (termId === 'all' || !termId) {
                            filters.categories = [];
                        } else {
                            filters.categories = [termId];
                        }
                        
                        const event = new CustomEvent('madennaLiveSearch', { detail: { keyword: keyword, filters: filters, paged: 1 } });
                        document.dispatchEvent(event);
                    });
                });
            });
            </script>
            <?php
        }
    }
}