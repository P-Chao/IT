<?php
/**
 * AJAX Handlers for Impossible Trinity
 */

class WPIT_Ajax {
    
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
        // AJAX actions for logged-in users
        add_action('wp_ajax_wpit_load_more', array($this, 'load_more'));
        add_action('wp_ajax_wpit_agree', array($this, 'agree'));
        add_action('wp_ajax_wpit_filter', array($this, 'filter'));
        
        // AJAX actions for non-logged-in users (if needed)
        add_action('wp_ajax_nopriv_wpit_load_more', array($this, 'load_more'));
        add_action('wp_ajax_nopriv_wpit_filter', array($this, 'filter'));
    }
    
    /**
     * Load more items via AJAX
     */
    public function load_more() {
        check_ajax_referer('wpit_nonce', 'nonce');
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $view = isset($_POST['view']) ? sanitize_text_field($_POST['view']) : 'card';
        $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        $per_page = ($view === 'table') ? 20 : 12;
        
        $args = array(
            'post_type' => 'impossible_trinity',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        // Apply field filter
        if (!empty($field)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'it_field',
                    'field' => 'slug',
                    'terms' => $field,
                ),
            );
        }
        
        // Apply search
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $query = new WP_Query($args);
        
        $response = array(
            'success' => true,
            'html' => '',
            'has_more' => $query->max_num_pages > $page,
        );
        
        if ($query->have_posts()) {
            ob_start();
            
            if ($view === 'table') {
                $this->render_table_rows($query);
            } else {
                $this->render_cards($query);
            }
            
            $response['html'] = ob_get_clean();
        }
        
        wp_send_json($response);
    }
    
    /**
     * Render cards for AJAX
     */
    private function render_cards($query) {
        while ($query->have_posts()) : $query->the_post(); 
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
        <?php endwhile;
        wp_reset_postdata();
    }
    
    /**
     * Render table rows for AJAX
     */
    private function render_table_rows($query) {
        while ($query->have_posts()) : $query->the_post(); 
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
        <?php endwhile;
        wp_reset_postdata();
    }
    
    /**
     * Handle agree action
     */
    public function agree() {
        check_ajax_referer('wpit_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to agree.', 'wp-impossible-trinity')));
        }
        
        $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => __('Invalid post ID.', 'wp-impossible-trinity')));
        }
        
        $post = get_post($post_id);
        
        if (!$post || 'impossible_trinity' !== $post->post_type) {
            wp_send_json_error(array('message' => __('Post not found.', 'wp-impossible-trinity')));
        }
        
        // Check if user has already agreed (prevent multiple votes from same user)
        $user_id = get_current_user_id();
        $agreed_posts = get_user_meta($user_id, '_wpit_agreed_posts', true);
        if (!is_array($agreed_posts)) {
            $agreed_posts = array();
        }
        
        if (in_array($post_id, $agreed_posts)) {
            wp_send_json_error(array('message' => __('You have already agreed to this.', 'wp-impossible-trinity')));
        }
        
        // Increment agree count
        $agree_count = (int) get_post_meta($post_id, '_agree_count', true);
        update_post_meta($post_id, '_agree_count', $agree_count + 1);
        
        // Record that user agreed
        $agreed_posts[] = $post_id;
        update_user_meta($user_id, '_wpit_agreed_posts', $agreed_posts);
        
        wp_send_json_success(array(
            'count' => $agree_count + 1,
            'message' => __('Thanks for your agreement!', 'wp-impossible-trinity'),
        ));
    }
    
    /**
     * Filter items
     */
    public function filter() {
        check_ajax_referer('wpit_nonce', 'nonce');
        
        $view = isset($_POST['view']) ? sanitize_text_field($_POST['view']) : 'card';
        $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        $per_page = ($view === 'table') ? 20 : 12;
        
        $args = array(
            'post_type' => 'impossible_trinity',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        // Apply field filter
        if (!empty($field)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'it_field',
                    'field' => 'slug',
                    'terms' => $field,
                ),
            );
        }
        
        // Apply search
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $query = new WP_Query($args);
        
        $response = array(
            'success' => true,
            'html' => '',
            'has_more' => $query->max_num_pages > 1,
        );
        
        if ($query->have_posts()) {
            ob_start();
            
            if ($view === 'table') {
                $this->render_table_rows($query);
            } else {
                $this->render_cards($query);
            }
            
            $response['html'] = ob_get_clean();
        } else {
            $response['html'] = '<div class="it-empty-state"><p>' . __('No impossible trinities found.', 'wp-impossible-trinity') . '</p></div>';
            $response['has_more'] = false;
        }
        
        wp_send_json($response);
    }
}