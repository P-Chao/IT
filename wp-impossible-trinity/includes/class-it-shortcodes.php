<?php
/**
 * Shortcodes for displaying Impossible Trinities
 */

class WPIT_Shortcodes {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_shortcode('impossible_trinity', array($this, 'render_list_shortcode'));
        add_shortcode('impossible_trinity_detail', array($this, 'render_detail_shortcode'));
    }
    
    /**
     * Render the list shortcode
     * Usage: [impossible_trinity view="card|table" field="economics" search="query"]
     */
    public function render_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'view' => 'card',
            'field' => '',
            'search' => '',
            'per_page' => 12,
        ), $atts, 'impossible_trinity');
        
        // Enqueue frontend assets
        $this->enqueue_frontend_assets();
        
        // Get all unique fields
        $fields = $this->get_all_fields();
        
        // Build query arguments
        $args = array(
            'post_type' => 'impossible_trinity',
            'post_status' => 'publish',
            'posts_per_page' => $atts['per_page'],
            'paged' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        // Apply field filter
        if (!empty($atts['field'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'it_field',
                    'field' => 'slug',
                    'terms' => sanitize_title($atts['field']),
                ),
            );
        }
        
        // Apply search
        if (!empty($atts['search'])) {
            $args['s'] = sanitize_text_field($atts['search']);
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        
        if ($query->have_posts()) {
            if ($atts['view'] === 'table') {
                $this->render_table_view($query);
            } else {
                $this->render_card_view($query);
            }
        } else {
            $this->render_empty_state();
        }
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Render card view
     */
    private function render_card_view($query) {
        ?>
        <div class="it-card-grid" id="it-card-grid" 
             data-page="1" 
             data-per-page="12"
             data-view="card"
             data-field=""
             data-search="">
            <?php while ($query->have_posts()) : $query->the_post(); 
                $it_id = get_the_ID();
                $field = get_post_meta($it_id, '_field', true);
                $element1 = get_post_meta($it_id, '_element1', true);
                $element2 = get_post_meta($it_id, '_element2', true);
                $element3 = get_post_meta($it_id, '_element3', true);
                $agree_count = (int) get_post_meta($it_id, '_agree_count', true);
                $comments_count = get_comments_number($it_id);
                $description = wp_trim_words(get_the_content(), 30, '...');
            ?>
                <div class="it-card" data-id="<?php echo esc_attr($it_id); ?>">
                    <div class="it-card-content">
                        <div class="it-card-header">
                            <h3 class="it-card-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <?php if ($field) : ?>
                                <span class="it-card-field"><?php echo esc_html($field); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="it-elements-horizontal">
                            <div class="it-element-badge">
                                <span><?php echo esc_html($element1); ?></span>
                            </div>
                            <div class="it-element-badge">
                                <span><?php echo esc_html($element2); ?></span>
                            </div>
                            <div class="it-element-badge">
                                <span><?php echo esc_html($element3); ?></span>
                            </div>
                        </div>
                        
                        <p class="it-card-preview"><?php echo esc_html($description); ?></p>
                        
                        <div class="it-card-meta">
                            <span class="it-agree-count">
                                <span class="it-meta-icon">👍</span>
                                <span class="it-agree-value"><?php echo esc_html($agree_count); ?></span>
                            </span>
                            <span class="it-comment-count">
                                <span class="it-meta-icon">💬</span>
                                <?php echo esc_html($comments_count); ?>
                            </span>
                            <a href="<?php the_permalink(); ?>" class="it-card-cta">
                                <?php _e('View Details', 'wp-impossible-trinity'); ?> →
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="it-loader" id="it-loader" style="display: none;">
            <?php _e('Loading...', 'wp-impossible-trinity'); ?>
        </div>
        <div class="it-sentinel" id="it-sentinel"></div>
        <?php
    }
    
    /**
     * Render table view
     */
    private function render_table_view($query) {
        ?>
        <div class="it-table-container" id="it-table-container"
             data-page="1" 
             data-per-page="20"
             data-view="table"
             data-field=""
             data-search="">
            <table class="it-data-table">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'wp-impossible-trinity'); ?></th>
                        <th><?php _e('Field', 'wp-impossible-trinity'); ?></th>
                        <th><?php _e('Element 1', 'wp-impossible-trinity'); ?></th>
                        <th><?php _e('Element 2', 'wp-impossible-trinity'); ?></th>
                        <th><?php _e('Element 3', 'wp-impossible-trinity'); ?></th>
                        <th><?php _e('Agreed', 'wp-impossible-trinity'); ?></th>
                        <th><?php _e('Comments', 'wp-impossible-trinity'); ?></th>
                        <th><?php _e('Date', 'wp-impossible-trinity'); ?></th>
                    </tr>
                </thead>
                <tbody id="it-table-body">
                    <?php while ($query->have_posts()) : $query->the_post(); 
                        $it_id = get_the_ID();
                        $field = get_post_meta($it_id, '_field', true);
                        $element1 = get_post_meta($it_id, '_element1', true);
                        $element2 = get_post_meta($it_id, '_element2', true);
                        $element3 = get_post_meta($it_id, '_element3', true);
                        $agree_count = (int) get_post_meta($it_id, '_agree_count', true);
                        $comments_count = get_comments_number($it_id);
                    ?>
                        <tr>
                            <td class="it-table-name">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </td>
                            <td><?php echo esc_html($field); ?></td>
                            <td><?php echo esc_html($element1); ?></td>
                            <td><?php echo esc_html($element2); ?></td>
                            <td><?php echo esc_html($element3); ?></td>
                            <td><?php echo esc_html($agree_count); ?></td>
                            <td><?php echo esc_html($comments_count); ?></td>
                            <td><?php echo get_the_date('Y-m-d'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="it-loader" id="it-loader" style="display: none;">
            <?php _e('Loading...', 'wp-impossible-trinity'); ?>
        </div>
        <div class="it-sentinel" id="it-sentinel"></div>
        <?php
    }
    
    /**
     * Render empty state
     */
    private function render_empty_state() {
        if (is_user_logged_in()) {
            $add_url = admin_url('post-new.php?post_type=impossible_trinity');
            ?>
            <div class="it-empty-state">
                <p><?php _e('No impossible trinities found.', 'wp-impossible-trinity'); ?></p>
                <a href="<?php echo esc_url($add_url); ?>" class="button button-primary">
                    <?php _e('Add New Impossible Trinity', 'wp-impossible-trinity'); ?>
                </a>
            </div>
            <?php
        } else {
            ?>
            <div class="it-empty-state">
                <p><?php _e('No impossible trinities found.', 'wp-impossible-trinity'); ?></p>
                <a href="<?php echo wp_login_url(); ?>" class="button button-primary">
                    <?php _e('Login to Add Content', 'wp-impossible-trinity'); ?>
                </a>
            </div>
            <?php
        }
    }
    
    /**
     * Render detail shortcode
     * Usage: [impossible_trinity_detail id="123"]
     */
    public function render_detail_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'impossible_trinity_detail');
        
        if (empty($atts['id'])) {
            $id = get_the_ID();
        } else {
            $id = intval($atts['id']);
        }
        
        $post = get_post($id);
        
        if (!$post || 'impossible_trinity' !== $post->post_type) {
            return '';
        }
        
        $this->enqueue_frontend_assets();
        
        ob_start();
        $this->render_detail($post);
        return ob_get_clean();
    }
    
    /**
     * Render single detail
     */
    private function render_detail($post) {
        $it_id = $post->ID;
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
        <div class="it-detail" id="it-detail-<?php echo esc_attr($it_id); ?>" data-id="<?php echo esc_attr($it_id); ?>">
            <div class="it-detail-header">
                <h1 class="it-detail-title"><?php the_title(); ?></h1>
                <?php if ($name_en) : ?>
                    <h2 class="it-detail-subtitle"><?php echo esc_html($name_en); ?></h2>
                <?php endif; ?>
                <div class="it-detail-meta">
                    <span class="it-detail-field">
                        <?php if ($field) : ?>
                            <?php echo esc_html($field); ?>
                        <?php endif; ?>
                    </span>
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
        </div>
        
        <?php comments_template('', true); ?>
        <?php
    }
    
    /**
     * Get all unique fields
     */
    private function get_all_fields() {
        global $wpdb;
        
        $fields = $wpdb->get_col("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_field'
            AND meta_value != ''
            ORDER BY meta_value ASC
        ");
        
        return $fields;
    }
    
    /**
     * Enqueue frontend assets
     */
    private function enqueue_frontend_assets() {
        wp_enqueue_style(
            'it-style',
            WPIT_PLUGIN_URL . 'assets/css/style.css',
            array(),
            WPIT_VERSION
        );
        
        wp_enqueue_script(
            'it-main',
            WPIT_PLUGIN_URL . 'assets/js/main.js',
            array('jquery'),
            WPIT_VERSION,
            true
        );
        
        wp_localize_script('it-main', 'wpit_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpit_nonce'),
        ));
    }
}