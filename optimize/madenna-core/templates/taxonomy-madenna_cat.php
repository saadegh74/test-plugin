<?php
/**
 * تمپلیت اختصاصی نمایش دسته‌بندی‌های مادنا
 * پشتیبانی کامل از رنگ‌های ۳ گانه، آیکون/SVG، ابزارها و مقالات منتخب، و سوالات متداول
 */

get_header();

$current_term      = get_queried_object();
$term_id           = $current_term->term_id;
$cat_subtitle      = get_term_meta($term_id, 'cat_subtitle', true);
$cat_excerpt       = get_term_meta($term_id, 'cat_excerpt', true);
$cat_icon_class    = get_term_meta($term_id, 'cat_icon_class', true);
$cat_icon_url      = get_term_meta($term_id, 'cat_icon_url', true);
$cat_image         = get_term_meta($term_id, 'cat_image', true);
$color_primary     = get_term_meta($term_id, 'cat_color_primary', true) ?: '#2563eb';
$color_secondary   = get_term_meta($term_id, 'cat_color_secondary', true) ?: '#3b82f6';
$color_accent      = get_term_meta($term_id, 'cat_color_accent', true) ?: '#eff6ff';

$featured_articles = get_term_meta($term_id, 'cat_featured_articles', true) ?: [];
$featured_tools    = get_term_meta($term_id, 'cat_featured_tools', true) ?: [];
$faqs              = get_term_meta($term_id, 'cat_faqs', true) ?: [];
?>

<div class="madenna-cat-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: inherit; direction: rtl; --cat-primary: <?php echo esc_attr($color_primary); ?>; --cat-secondary: <?php echo esc_attr($color_secondary); ?>; --cat-accent: <?php echo esc_attr($color_accent); ?>;">

    <!-- سربرگ دسته‌بندی -->
    <header class="cat-header" style="display: flex; align-items: center; gap: 24px; margin-bottom: 40px; padding: 30px; background: #ffffff; border: 1px solid #e2e8f0; border-right: 6px solid var(--cat-primary); border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
        <?php if ($cat_image): ?>
            <img src="<?php echo esc_url($cat_image); ?>" alt="<?php echo esc_attr($current_term->name); ?>" style="width: 85px; height: 85px; object-fit: cover; border-radius: 14px; border: 1px solid #cbd5e1;">
        <?php elseif ($cat_icon_url): ?>
            <div style="width: 75px; height: 75px; background: var(--cat-accent); border-radius: 14px; display: flex; align-items: center; justify-content: center; padding: 12px;">
                <img src="<?php echo esc_url($cat_icon_url); ?>" alt="<?php echo esc_attr($current_term->name); ?>" style="max-width: 100%; max-height: 100%;">
            </div>
        <?php elseif ($cat_icon_class): ?>
            <div style="width: 75px; height: 75px; background: var(--cat-accent); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 32px; color: var(--cat-primary);">
                <?php echo Madenna_Core::render_icon_element($cat_icon_class); ?>
            </div>
        <?php endif; ?>

        <div style="flex: 1;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #0f172a;"><?php echo esc_html($current_term->name); ?></h1>
            <?php if ($cat_subtitle): ?>
                <span style="display: block; font-size: 14px; color: var(--cat-secondary); margin-top: 4px; font-weight: 600;"><?php echo esc_html($cat_subtitle); ?></span>
            <?php endif; ?>
            <?php if ($cat_excerpt): ?>
                <p style="color: #475569; margin: 10px 0 0; line-height: 1.7; font-size: 14px;"><?php echo esc_html($cat_excerpt); ?></p>
            <?php endif; ?>
        </div>
    </header>

    <!-- بخش ابزارها (منتخب یا عمومی دسته) -->
    <?php
    $tools_args = [
        'post_type'      => 'tools',
        'posts_per_page' => 8,
        'tax_query'      => [
            [
                'taxonomy' => 'madenna_cat',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ],
        ],
    ];

    if (!empty($featured_tools)) {
        $tools_args['post__in'] = $featured_tools;
        $tools_args['orderby']  = 'post__in';
    }

    $tools_query = new WP_Query($tools_args);
    if ($tools_query->have_posts()):
    ?>
        <section class="cat-tools-section" style="margin-bottom: 50px; background: var(--cat-accent); padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="font-size: 20px; margin: 0; color: #0f172a; font-weight: 700;">⚙️ ابزارهای آنلاین این دسته‌بندی</h2>
                <a href="<?php echo home_url('/tools/'); ?>" style="font-size: 13px; color: var(--cat-primary); text-decoration: none; font-weight: 600;">مشاهده همه ابزارها ←</a>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px;">
                <?php while ($tools_query->have_posts()): $tools_query->the_post(); 
                    $t_icon_class = get_post_meta(get_the_ID(), '_tool_icon_class', true);
                    $t_icon_url   = get_post_meta(get_the_ID(), '_tool_icon_url', true);
                    $t_sub        = get_post_meta(get_the_ID(), '_tool_subtitle', true);
                ?>
                    <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; padding: 18px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; transition: transform 0.2s, box-shadow 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <?php if ($t_icon_url || $t_icon_class): ?>
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--cat-accent); display: flex; align-items: center; justify-content: center; font-size: 18px; color: var(--cat-primary);">
                                    <?php if ($t_icon_url): ?>
                                        <img src="<?php echo esc_url($t_icon_url); ?>" style="max-width: 22px; max-height: 22px;">
                                    <?php else: ?>
                                        <?php echo Madenna_Core::render_icon_element($t_icon_class); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <h3 style="font-size: 16px; margin: 0; font-weight: 700; color: #1e293b;"><?php the_title(); ?></h3>
                        </div>
                        <?php if ($t_sub): ?>
                            <span style="font-size: 12px; color: #64748b; line-height: 1.5; margin-bottom: 12px;"><?php echo esc_html($t_sub); ?></span>
                        <?php endif; ?>
                        <div style="margin-top: auto; padding-top: 8px; display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #f1f5f9; font-size: 12px; color: var(--cat-primary); font-weight: 600;">
                            <span>اجرای ابزار</span>
                            <span>←</span>
                        </div>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- بخش مقالات این دسته -->
    <?php
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $articles_args = [
        'post_type'      => 'articles',
        'posts_per_page' => 12,
        'paged'          => $paged,
        'tax_query'      => [
            [
                'taxonomy' => 'madenna_cat',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ],
        ],
    ];

    if (!empty($featured_articles) && $paged == 1) {
        $articles_args['post__in'] = $featured_articles;
        $articles_args['orderby']  = 'post__in';
    }

    $articles_query = new WP_Query($articles_args);
    ?>

    <section class="cat-articles-section" style="margin-bottom: 50px;">
        <h2 style="font-size: 22px; margin-bottom: 24px; color: #0f172a; font-weight: 800;">📚 مقالات و آموزش‌های تخصصی</h2>
        <?php if ($articles_query->have_posts()): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
                <?php while ($articles_query->have_posts()): $articles_query->the_post(); 
                    $reading = get_post_meta(get_the_ID(), '_article_reading_time', true);
                    $sub     = get_post_meta(get_the_ID(), '_article_subtitle', true);
                ?>
                    <article style="border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #fff; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">
                        <?php if (has_post_thumbnail()): ?>
                            <a href="<?php the_permalink(); ?>" style="display: block; height: 180px; overflow: hidden;">
                                <?php the_post_thumbnail('medium_large', ['style' => 'width: 100%; height: 100%; object-fit: cover;']); ?>
                            </a>
                        <?php endif; ?>
                        <div style="padding: 18px; flex: 1; display: flex; flex-direction: column;">
                            <?php if ($reading): ?>
                                <span style="font-size: 11px; color: #64748b; margin-bottom: 6px;">⏱️ زمان مطالعه: <?php echo esc_html($reading); ?> دقیقه</span>
                            <?php endif; ?>
                            <h3 style="font-size: 17px; margin: 0 0 8px; font-weight: 700;">
                                <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: #1e293b;"><?php the_title(); ?></a>
                            </h3>
                            <?php if ($sub): ?>
                                <span style="font-size: 12px; color: #64748b; margin-bottom: 8px;"><?php echo esc_html($sub); ?></span>
                            <?php endif; ?>
                            <p style="font-size: 13px; color: #475569; margin: 0 0 16px; line-height: 1.6;">
                                <?php echo wp_trim_words(get_the_excerpt(), 18); ?>
                            </p>
                            <div style="margin-top: auto; padding-top: 10px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
                                <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: var(--cat-primary); font-size: 12px; font-weight: 600;">مطالعه کامل مقاله ←</a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <div style="margin-top: 35px; text-align: center;">
                <?php
                echo paginate_links([
                    'total'   => $articles_query->max_num_pages,
                    'current' => $paged,
                ]);
                wp_reset_postdata();
                ?>
            </div>
        <?php else: ?>
            <p style="color: #64748b; background: #f8fafc; padding: 20px; border-radius: 10px;">هنوز مقاله‌ای در این دسته‌بندی ثبت نشده است.</p>
        <?php endif; ?>
    </section>

    <!-- بخش سوالات متداول (FAQ) دسته -->
    <?php if (!empty($faqs) && is_array($faqs)): ?>
        <section class="cat-faqs-section" style="margin-top: 50px; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 25px;">
            <h2 style="font-size: 20px; margin-top: 0; margin-bottom: 20px; color: #0f172a; font-weight: 700;">❓ پرسش‌های پرتکرار درباره <?php echo esc_html($current_term->name); ?></h2>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach ($faqs as $faq): 
                    if (empty($faq['q']) || empty($faq['a'])) continue;
                ?>
                    <details style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 18px;">
                        <summary style="font-weight: 700; font-size: 15px; cursor: pointer; color: #1e293b;">
                            <?php echo esc_html($faq['q']); ?>
                        </summary>
                        <div style="padding-top: 10px; margin-top: 8px; border-top: 1px solid #e2e8f0; color: #475569; font-size: 14px; line-height: 1.8;">
                            <?php echo wp_kses_post($faq['a']); ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php get_footer(); ?>