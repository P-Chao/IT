<?php
/**
 * Metaboxes for Impossible Trinity post type
 */

class WPIT_Metaboxes {
    
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
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add meta boxes to the post edit screen
     */
    public function add_meta_boxes() {
        add_meta_box(
            'it_basic_info',
            __('Basic Information', 'wp-impossible-trinity'),
            array($this, 'render_basic_info_metabox'),
            'impossible_trinity',
            'normal',
            'high'
        );
        
        add_meta_box(
            'it_elements',
            __('The Three Elements', 'wp-impossible-trinity'),
            array($this, 'render_elements_metabox'),
            'impossible_trinity',
            'normal',
            'high'
        );
        
        add_meta_box(
            'it_explanations',
            __('Sacrifice Explanations', 'wp-impossible-trinity'),
            array($this, 'render_explanations_metabox'),
            'impossible_trinity',
            'normal',
            'default'
        );
        
        add_meta_box(
            'it_images',
            __('Images', 'wp-impossible-trinity'),
            array($this, 'render_images_metabox'),
            'impossible_trinity',
            'side',
            'default'
        );
        
        add_meta_box(
            'it_references',
            __('References', 'wp-impossible-trinity'),
            array($this, 'render_references_metabox'),
            'impossible_trinity',
            'side',
            'default'
        );
    }
    
    /**
     * Render basic info metabox
     */
    public function render_basic_info_metabox($post) {
        wp_nonce_field('it_save_meta_boxes', 'it_meta_box_nonce');
        
        $name_en = get_post_meta($post->ID, '_name_en', true);
        $field = get_post_meta($post->ID, '_field', true);
        ?>
        <div class="it-field-group">
            <label for="name_en">
                <strong><?php _e('English Name', 'wp-impossible-trinity'); ?></strong>
            </label>
            <input type="text" 
                   id="name_en" 
                   name="name_en" 
                   value="<?php echo esc_attr($name_en); ?>"
                   class="large-text"
                   placeholder="<?php echo esc_attr__('Impossible Trinity', 'wp-impossible-trinity'); ?>">
        </div>
        
        <div class="it-field-group">
            <label for="field">
                <strong><?php _e('Field', 'wp-impossible-trinity'); ?></strong>
            </label>
            <input type="text" 
                   id="field" 
                   name="field" 
                   value="<?php echo esc_attr($field); ?>"
                   class="large-text"
                   placeholder="<?php echo esc_attr__('e.g., Economics, Project Management', 'wp-impossible-trinity'); ?>">
            <p class="description">
                <?php _e('The field or domain this impossible trinity belongs to.', 'wp-impossible-trinity'); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render elements metabox
     */
    public function render_elements_metabox($post) {
        $element1 = get_post_meta($post->ID, '_element1', true);
        $element2 = get_post_meta($post->ID, '_element2', true);
        $element3 = get_post_meta($post->ID, '_element3', true);
        ?>
        <div class="it-field-group">
            <label for="element1">
                <strong><?php _e('Element 1', 'wp-impossible-trinity'); ?></strong>
            </label>
            <input type="text" 
                   id="element1" 
                   name="element1" 
                   value="<?php echo esc_attr($element1); ?>"
                   class="large-text"
                   required>
        </div>
        
        <div class="it-field-group">
            <label for="element2">
                <strong><?php _e('Element 2', 'wp-impossible-trinity'); ?></strong>
            </label>
            <input type="text" 
                   id="element2" 
                   name="element2" 
                   value="<?php echo esc_attr($element2); ?>"
                   class="large-text"
                   required>
        </div>
        
        <div class="it-field-group">
            <label for="element3">
                <strong><?php _e('Element 3', 'wp-impossible-trinity'); ?></strong>
            </label>
            <input type="text" 
                   id="element3" 
                   name="element3" 
                   value="<?php echo esc_attr($element3); ?>"
                   class="large-text"
                   required>
        </div>
        
        <p class="description">
            <?php _e('These are the three elements of the impossible trinity. Only two can be achieved simultaneously.', 'wp-impossible-trinity'); ?>
        </p>
        <?php
    }
    
    /**
     * Render explanations metabox
     */
    public function render_explanations_metabox($post) {
        $element1_sacrifice = get_post_meta($post->ID, '_element1_sacrifice_explanation', true);
        $element2_sacrifice = get_post_meta($post->ID, '_element2_sacrifice_explanation', true);
        $element3_sacrifice = get_post_meta($post->ID, '_element3_sacrifice_explanation', true);
        ?>
        <div class="it-field-group">
            <label for="element1_sacrifice_explanation">
                <strong><?php _e('Element 1 Sacrifice Explanation', 'wp-impossible-trinity'); ?></strong>
            </label>
            <textarea id="element1_sacrifice_explanation" 
                      name="element1_sacrifice_explanation" 
                      class="large-text" 
                      rows="3"><?php echo esc_textarea($element1_sacrifice); ?></textarea>
            <p class="description">
                <?php _e('Explanation when Element 1 is not satisfied (Elements 2 and 3 are satisfied).', 'wp-impossible-trinity'); ?>
            </p>
        </div>
        
        <div class="it-field-group">
            <label for="element2_sacrifice_explanation">
                <strong><?php _e('Element 2 Sacrifice Explanation', 'wp-impossible-trinity'); ?></strong>
            </label>
            <textarea id="element2_sacrifice_explanation" 
                      name="element2_sacrifice_explanation" 
                      class="large-text" 
                      rows="3"><?php echo esc_textarea($element2_sacrifice); ?></textarea>
            <p class="description">
                <?php _e('Explanation when Element 2 is not satisfied (Elements 1 and 3 are satisfied).', 'wp-impossible-trinity'); ?>
            </p>
        </div>
        
        <div class="it-field-group">
            <label for="element3_sacrifice_explanation">
                <strong><?php _e('Element 3 Sacrifice Explanation', 'wp-impossible-trinity'); ?></strong>
            </label>
            <textarea id="element3_sacrifice_explanation" 
                      name="element3_sacrifice_explanation" 
                      class="large-text" 
                      rows="3"><?php echo esc_textarea($element3_sacrifice); ?></textarea>
            <p class="description">
                <?php _e('Explanation when Element 3 is not satisfied (Elements 1 and 2 are satisfied).', 'wp-impossible-trinity'); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render images metabox
     */
    public function render_images_metabox($post) {
        $element1_image = get_post_meta($post->ID, '_element1_image_url', true);
        $element2_image = get_post_meta($post->ID, '_element2_image_url', true);
        $element3_image = get_post_meta($post->ID, '_element3_image_url', true);
        ?>
        <div class="it-field-group">
            <label for="element1_image_url">
                <?php _e('Element 1 Image URL', 'wp-impossible-trinity'); ?>
            </label>
            <input type="text" 
                   id="element1_image_url" 
                   name="element1_image_url" 
                   value="<?php echo esc_attr($element1_image); ?>"
                   class="large-text">
        </div>
        
        <div class="it-field-group">
            <label for="element2_image_url">
                <?php _e('Element 2 Image URL', 'wp-impossible-trinity'); ?>
            </label>
            <input type="text" 
                   id="element2_image_url" 
                   name="element2_image_url" 
                   value="<?php echo esc_attr($element2_image); ?>"
                   class="large-text">
        </div>
        
        <div class="it-field-group">
            <label for="element3_image_url">
                <?php _e('Element 3 Image URL', 'wp-impossible-trinity'); ?>
            </label>
            <input type="text" 
                   id="element3_image_url" 
                   name="element3_image_url" 
                   value="<?php echo esc_attr($element3_image); ?>"
                   class="large-text">
        </div>
        <?php
    }
    
    /**
     * Render references metabox
     */
    public function render_references_metabox($post) {
        $hyperlink = get_post_meta($post->ID, '_hyperlink', true);
        ?>
        <div class="it-field-group">
            <label for="hyperlink">
                <strong><?php _e('Reference URL', 'wp-impossible-trinity'); ?></strong>
            </label>
            <input type="url" 
                   id="hyperlink" 
                   name="hyperlink" 
                   value="<?php echo esc_attr($hyperlink); ?>"
                   class="large-text"
                   placeholder="https://">
        </div>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id, $post) {
        // Check if our nonce is set
        if (!isset($_POST['it_meta_box_nonce']) || !wp_verify_nonce($_POST['it_meta_box_nonce'], 'it_save_meta_boxes')) {
            return;
        }
        
        // Check for autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check post type
        if ('impossible_trinity' !== $post->post_type) {
            return;
        }
        
        // Save fields
        $fields = array(
            'name_en',
            'field',
            'element1',
            'element2',
            'element3',
            'element1_sacrifice_explanation',
            'element2_sacrifice_explanation',
            'element3_sacrifice_explanation',
            'element1_image_url',
            'element2_image_url',
            'element3_image_url',
            'hyperlink',
        );
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if ('impossible_trinity' === $post_type) {
            wp_enqueue_style(
                'it-admin-style',
                WPIT_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WPIT_VERSION
            );
        }
    }
}