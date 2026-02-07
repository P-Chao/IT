<?php
/**
 * Single Impossible Trinity Template
 */

get_header();

while (have_posts()) : the_post();
    
    $it_id = get_the_ID();
    $name_en = get_post_meta($it_id, '_name_en', true);
    $field = get_post_meta($it_id, '_field', true);
    $element1 = get_post_meta($it_id, '_element1', true);
    $element2 = get_post_meta($it_id, '_element2', true);
    $element3 = get_post_meta($it_id, '_element3', true);
    $element1_sacrifice = get_post_meta($it_id, '_element1_sacrifice_explanation', true);
    $element2_sacrifice = get_post_meta($it_id, '_element2_sacrifice_explanation', true);
    $element3_sacrifice = get_post_meta($it_id, '_element3_sacrifice_explanation', true);
    $agree_count = (int) get_post_meta($it_id, '_agree_count', true);
    $hyperlink = get_post_meta($it_id, '_hyperlink', true);
    
    $author = get_the_author();
    $date = get_the_date();
    $description = get_the_content();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <article id="post-<?php the_ID(); ?>" <?php post_class('it-detail'); ?> data-id="<?php echo esc_attr($it_id); ?>">
            
            <div class="it-detail-header">
                <h1 class="it-detail-title"><?php the_title(); ?></h1>
                <?php if ($name_en) : ?>
                    <h2 class="it-detail-subtitle"><?php echo esc_html($name_en); ?></h2>
                <?php endif; ?>
                <div class="it-detail-meta">
                    <?php if ($field) : ?>
                        <span class="it-detail-field">
                            <?php echo esc_html($field); ?>
                        </span>
                    <?php endif; ?>
                    <span class="it-detail-author">
                        <?php printf(__('By %s', 'wp-impossible-trinity'), esc_html($author)); ?>
                    </span>
                    <span class="it-detail-date">
                        <?php echo esc_html($date); ?>
                    </span>
                </div>
            </div>
            
            <div class="it-elements-section">
                <h3><?php _e('The Three Elements', 'wp-impossible-trinity'); ?></h3>
                <div class="it-elements-large">
                    <div class="it-element-large">
                        <h4 class="it-element-title"><?php echo esc_html($element1); ?></h4>
                        <?php if ($element1_sacrifice) : ?>
                            <p class="it-element-sacrifice"><?php echo esc_html($element1_sacrifice); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="it-element-large">
                        <h4 class="it-element-title"><?php echo esc_html($element2); ?></h4>
                        <?php if ($element2_sacrifice) : ?>
                            <p class="it-element-sacrifice"><?php echo esc_html($element2_sacrifice); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="it-element-large">
                        <h4 class="it-element-title"><?php echo esc_html($element3); ?></h4>
                        <?php if ($element3_sacrifice) : ?>
                            <p class="it-element-sacrifice"><?php echo esc_html($element3_sacrifice); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="it-description-section">
                <h3><?php _e('Description', 'wp-impossible-trinity'); ?></h3>
                <div class="it-description">
                    <?php echo wp_kses_post($description); ?>
                </div>
            </div>
            
            <?php if ($hyperlink) : ?>
                <div class="it-reference-section">
                    <h3><?php _e('References', 'wp-impossible-trinity'); ?></h3>
                    <a href="<?php echo esc_url($hyperlink); ?>" target="_blank" rel="noopener noreferrer" class="it-reference-link">
                        <?php _e('External Reference', 'wp-impossible-trinity'); ?> ↗
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="it-actions-section">
                <button class="it-agree-button button" data-id="<?php echo esc_attr($it_id); ?>">
                    <span class="it-agree-icon">👍</span>
                    <span class="it-agree-text"><?php _e('Agree', 'wp-impossible-trinity'); ?></span>
                    <span class="it-agree-count">(<?php echo esc_html($agree_count); ?>)</span>
                </button>
            </div>
            
        </article>
        
        <?php
        // If comments are open or we have at least one comment, load up the comment template
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        ?>
        
    </main>
</div>

<?php endwhile; ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>