<?php
/**
 * ویجت فیلترهای پیشرفته سایدبار (هماهنگ با گرید هوشمند)
 * Location: /inc/elementor/widgets/class-widget-filters.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Madenna_Filters_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'madenna_filters'; }
    public function get_title() { return 'فیلترهای دقیق مادنا'; }
    public function get_icon() { return 'eicon-filter'; }
    public function get_categories() { return ['general']; }

    protected function register_controls() {
        $this->start_controls_section('sec_content', ['label' => 'نمایش فیلترها', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);
        $this->add_control('show_sort', ['label' => 'نمایش مرتب‌سازی', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('show_featured', ['label' => 'نمایش فیلتر برگزیده‌ها', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('show_type', ['label' => 'نمایش نوع محتوا', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('show_cat', ['label' => 'نمایش دسته‌بندی‌ها', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('show_time', ['label' => 'نمایش زمان مطالعه', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->end_controls_section();

        $this->start_controls_section('sec_style', ['label' => 'استایل‌ها', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_control('bg_color', ['label' => 'رنگ پس‌زمینه', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .madenna-filter-widget' => 'background-color: {{VALUE}};']]);
        
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'title_typ', 'label' => 'تایپوگرافی تیترها', 'selector' => '{{WRAPPER}} .madenna-filter-section-title']);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'label_typ', 'label' => 'تایپوگرافی گزینه‌ها', 'selector' => '{{WRAPPER}} .madenna-filter-option span']);
        
        $this->add_control('primary_color', ['label' => 'رنگ المان‌های فعال (تیک‌ها)', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#0c5c4e']);
        $this->add_responsive_control('padding', ['label' => 'پدینگ کادر', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .madenna-filter-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('radius', ['label' => 'انحنای کادر', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .madenna-filter-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $cats = get_terms(['taxonomy' => 'madenna_cat', 'hide_empty' => false, 'parent' => 0]);
        $tool_tags = get_terms(['taxonomy' => 'madenna_tool_tag', 'hide_empty' => false]);
        $primary_col = !empty($s['primary_color']) ? esc_attr($s['primary_color']) : '#0c5c4e';
        $cid = 'mad-fltr-' . esc_attr($this->get_id());
        ?>
        <style>
            .madenna-filter-wrapper { position: relative; width: 100%; align-self: flex-start; }
            .madenna-filter-widget { background: #fff; padding: 25px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); direction: rtl; font-family: inherit; }
            .madenna-filter-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
            .madenna-filter-header h3 { margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
            .madenna-filter-clear { font-size: 11px; color: #3b82f6; cursor: pointer; text-decoration: none; }
            .madenna-filter-section { margin-bottom: 25px; }
            .madenna-filter-section-title { font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 12px; }
            .madenna-filter-option { display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px; color: #475569; }
            .madenna-filter-option input { margin-left: 8px; width: 16px; height: 16px; accent-color: <?php echo $primary_col; ?>; cursor: pointer; flex-shrink:0; }
            .madenna-filter-option:hover { color: <?php echo $primary_col; ?>; }
            
            /* استایل موبایل: استیکی و آبشاری */
            .madenna-filter-mobile-toggle { display: none; align-items: center; justify-content: space-between; background: <?php echo $primary_col; ?>; color: #fff; padding: 12px 18px; border-radius: 8px; cursor: pointer; font-weight:bold; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
            .madenna-filter-mobile-toggle i { transition: transform 0.3s; }
            .madenna-filter-mobile-toggle.open i.fa-chevron-down { transform: rotate(180deg); }

            @media (max-width: 768px) {
                .madenna-filter-wrapper { position: sticky; top: 15px; z-index: 999; margin-bottom: 20px; }
                .madenna-filter-mobile-toggle { display: flex; }
                .madenna-filter-widget { display: none; position: absolute; top: 100%; right: 0; width: 100%; margin-top: 10px; border: 1px solid #e2e8f0; max-height: 70vh; overflow-y: auto; }
                .madenna-filter-widget.active { display: block; }
            }
        </style>

        <div class="madenna-filter-wrapper" id="<?php echo $cid; ?>-wrapper">
            <div class="madenna-filter-mobile-toggle" id="<?php echo $cid; ?>-toggle">
                <span><i class="fa-solid fa-filter"></i> فیلتر و مرتب‌سازی</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            
            <div class="madenna-filter-widget" id="<?php echo $cid; ?>">
                <div class="madenna-filter-header">
                    <h3><i class="fa-solid fa-sliders"></i> فیلترهای دقیق</h3>
                    <span class="madenna-filter-clear" onclick="document.getElementById('madenna-filter-form-<?php echo esc_attr($this->get_id()); ?>').reset(); window.madennaDispatchFilters('clear');">پاکسازی فیلترها</span>
                </div>

                <form id="madenna-filter-form-<?php echo esc_attr($this->get_id()); ?>">
                    <?php if ($s['show_sort'] === 'yes'): ?>
                    <div class="madenna-filter-section">
                        <div class="madenna-filter-section-title">مرتب‌سازی نتایج</div>
                        <label class="madenna-filter-option"><input type="radio" name="sort" value="relevance" checked> <span>مرتبط‌ترین نتیجه</span></label>
                        <label class="madenna-filter-option"><input type="radio" name="sort" value="date"> <span>جدیدترین انتشارات</span></label>
                        <?php if (($s['show_featured'] ?? 'yes') === 'yes'): ?>
                        <label class="madenna-filter-option"><input type="checkbox" name="featured" value="yes"> <span>⭐ فقط برگزیده‌ها</span></label>
                        <?php endif; ?>
                    </div>
                    <?php elseif (($s['show_featured'] ?? 'yes') === 'yes'): ?>
                    <div class="madenna-filter-section">
                        <div class="madenna-filter-section-title">فیلتر ویژه</div>
                        <label class="madenna-filter-option"><input type="checkbox" name="featured" value="yes"> <span>⭐ فقط برگزیده‌ها</span></label>
                    </div>
                    <?php endif; ?>

                    <?php if ($s['show_type'] === 'yes'): ?>
                    <div class="madenna-filter-section" id="madenna-type-filter-section">
                        <div class="madenna-filter-section-title">نوع محتوا</div>
                        <label class="madenna-filter-option"><input type="radio" name="post_type" value="any" checked> <span>همه انواع محتوا</span></label>
                        <label class="madenna-filter-option"><input type="radio" name="post_type" value="tools"> <span>ابزارهای محاسباتی و کاربردی</span></label>
                        <label class="madenna-filter-option"><input type="radio" name="post_type" value="articles"> <span>مقالات و یادداشت‌ها</span></label>
                    </div>
                    <?php endif; ?>

                    <?php if ($s['show_cat'] === 'yes' && !empty($cats) && !is_wp_error($cats)): ?>
                    <div class="madenna-filter-section">
                        <div class="madenna-filter-section-title">دسته‌بندی موضوعی</div>
                        <?php foreach ($cats as $cat): ?>
                        <label class="madenna-filter-option"><input type="checkbox" name="cat[]" value="<?php echo esc_attr($cat->term_id); ?>"> <span><?php echo esc_html($cat->name); ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($s['show_time'] === 'yes'): ?>
                    <div class="madenna-filter-section" id="madenna-time-filter-section-<?php echo esc_attr($this->get_id()); ?>">
                        <div class="madenna-filter-section-title">مدت زمان مطالعه</div>
                        <label class="madenna-filter-option"><input type="checkbox" name="time[]" value="fast"> <span>تا ۵ دقیقه</span></label>
                        <label class="madenna-filter-option"><input type="checkbox" name="time[]" value="medium"> <span>۵ تا ۱۰ دقیقه</span></label>
                        <label class="madenna-filter-option"><input type="checkbox" name="time[]" value="deep"> <span>بیش از ۱۰ دقیقه</span></label>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($tool_tags) && !is_wp_error($tool_tags)): ?>
                    <div class="madenna-filter-section" id="madenna-badge-filter-section-<?php echo esc_attr($this->get_id()); ?>" style="display: none;">
                        <div class="madenna-filter-section-title">🏷️ برچسب‌های ابزار</div>
                        <?php foreach ($tool_tags as $tag): ?>
                        <label class="madenna-filter-option"><input type="checkbox" name="tool_tags[]" value="<?php echo esc_attr($tag->term_id); ?>"> <span><?php echo esc_html($tag->name); ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('madenna-filter-form-<?php echo esc_attr($this->get_id()); ?>');
            var toggle = document.getElementById('<?php echo $cid; ?>-toggle');
            var widget = document.getElementById('<?php echo $cid; ?>');
            if(!form) return;

            if(toggle) {
                toggle.addEventListener('click', function() {
                    widget.classList.toggle('active');
                    toggle.classList.toggle('open');
                });
            }

            var typeRadios = form.querySelectorAll('input[name="post_type"]');
            var timeSection = document.getElementById('madenna-time-filter-section-<?php echo esc_attr($this->get_id()); ?>');
            var badgeSection = document.getElementById('madenna-badge-filter-section-<?php echo esc_attr($this->get_id()); ?>');

            if(timeSection || badgeSection) {
                typeRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if(this.value === 'tools') {
                            if(timeSection) {
                                timeSection.style.display = 'none';
                                timeSection.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                            }
                            if(badgeSection) badgeSection.style.display = 'block';
                        } else if(this.value === 'articles') {
                            if(timeSection) timeSection.style.display = 'block';
                            if(badgeSection) {
                                badgeSection.style.display = 'none';
                                badgeSection.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                            }
                        } else {
                            if(timeSection) timeSection.style.display = 'block';
                            if(badgeSection) {
                                badgeSection.style.display = 'none';
                                badgeSection.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                            }
                        }
                    });
                });
            }

            // تابع مخابره فیلترها (اولویت با فیلتر است و سرچ را در صورت کلیک دستی پاک می‌کند)
            window.madennaDispatchFilters = function(origin) {
                var searchInput = document.querySelector('.mad-srch-input');
                
                // پاک کردن سرچ وقتی روی سایدبار کلیک شده باشد
                if (origin === 'sidebar_click' && searchInput && searchInput.value.trim() !== '') {
                    searchInput.value = '';
                    window.madennaCurrentKeyword = '';
                }
                
                var keyword = searchInput ? searchInput.value : (window.madennaCurrentKeyword || '');

                var filters = {
                    sort: form.querySelector('input[name="sort"]:checked') ? form.querySelector('input[name="sort"]:checked').value : 'relevance',
                    featured: form.querySelector('input[name="featured"]:checked') ? 'yes' : 'no',
                    post_type: form.querySelector('input[name="post_type"]:checked') ? form.querySelector('input[name="post_type"]:checked').value : 'any',
                    categories: Array.from(form.querySelectorAll('input[name="cat[]"]:checked')).map(cb => cb.value),
                    time: Array.from(form.querySelectorAll('input[name="time[]"]:checked')).map(cb => cb.value),
                    tool_tags: badgeSection ? Array.from(form.querySelectorAll('input[name="tool_tags[]"]:checked')).map(cb => cb.value) : []
                };
                
                var event = new CustomEvent('madennaLiveSearch', { detail: { keyword: keyword, filters: filters, paged: 1 } });
                document.dispatchEvent(event);
            };

            form.addEventListener('change', function() {
                window.madennaDispatchFilters('sidebar_click');
            });
        });
        </script>
        <?php
    }
}