<?php
/**
 * Custom Post Type: Impossible Trinity
 */

class WPIT_Post_Type {
    
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
        add_action('init', array($this, 'register_post_type'));
        add_filter('post_updated_messages', array($this, 'updated_messages'));
        add_action('init', array($this, 'register_taxonomies'));
    }
    
    /**
     * Register the custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name' => __('Impossible Trinities', 'wp-impossible-trinity'),
            'singular_name' => __('Impossible Trinity', 'wp-impossible-trinity'),
            'add_new' => __('Add New', 'wp-impossible-trinity'),
            'add_new_item' => __('Add New Impossible Trinity', 'wp-impossible-trinity'),
            'edit_item' => __('Edit Impossible Trinity', 'wp-impossible-trinity'),
            'new_item' => __('New Impossible Trinity', 'wp-impossible-trinity'),
            'view_item' => __('View Impossible Trinity', 'wp-impossible-trinity'),
            'view_items' => __('View Impossible Trinities', 'wp-impossible-trinity'),
            'search_items' => __('Search Impossible Trinities', 'wp-impossible-trinity'),
            'not_found' => __('No impossible trinities found', 'wp-impossible-trinity'),
            'not_found_in_trash' => __('No impossible trinities found in trash', 'wp-impossible-trinity'),
            'all_items' => __('All Impossible Trinities', 'wp-impossible-trinity'),
            'menu_name' => __('Impossible Trinity', 'wp-impossible-trinity'),
        );
        
        $args = array(
            'labels' => $labels,
            'description' => __('Impossible Trinity entries', 'wp-impossible-trinity'),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'impossible-trinity'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-star-filled',
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'comments'),
        );
        
        register_post_type('impossible_trinity', $args);
    }
    
    /**
     * Custom post update messages
     */
    public function updated_messages($messages) {
        global $post;
        
        $permalink = get_permalink($post);
        $preview_url = get_preview_post_link($post);
        
        $messages['impossible_trinity'] = array(
            0 => '',
            1 => sprintf(__('Impossible Trinity updated. <a href="%s">View Impossible Trinity</a>', 'wp-impossible-trinity'), esc_url($permalink)),
            2 => __('Custom field updated.', 'wp-impossible-trinity'),
            3 => __('Custom field deleted.', 'wp-impossible-trinity'),
            4 => __('Impossible Trinity updated.', 'wp-impossible-trinity'),
            5 => isset($_GET['revision']) ? sprintf(__('Impossible Trinity restored to revision from %s', 'wp-impossible-trinity'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6 => sprintf(__('Impossible Trinity published. <a href="%s">View Impossible Trinity</a>', 'wp-impossible-trinity'), esc_url($permalink)),
            7 => __('Impossible Trinity saved.', 'wp-impossible-trinity'),
            8 => sprintf(__('Impossible Trinity submitted. <a target="_blank" href="%s">Preview Impossible Trinity</a>', 'wp-impossible-trinity'), esc_url($preview_url)),
            9 => sprintf(__('Impossible Trinity scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Impossible Trinity</a>', 'wp-impossible-trinity'), date_i18n(__('M j, Y @ G:i', 'wp-impossible-trinity'), strtotime($post->post_date)), esc_url($preview_url)),
            10 => sprintf(__('Impossible Trinity draft updated. <a target="_blank" href="%s">Preview Impossible Trinity</a>', 'wp-impossible-trinity'), esc_url($preview_url)),
        );
        
        return $messages;
    }
    
    /**
     * Register taxonomies for filtering
     */
    public function register_taxonomies() {
        // Field taxonomy for categorization
        $labels = array(
            'name' => __('Fields', 'wp-impossible-trinity'),
            'singular_name' => __('Field', 'wp-impossible-trinity'),
            'search_items' => __('Search Fields', 'wp-impossible-trinity'),
            'all_items' => __('All Fields', 'wp-impossible-trinity'),
            'parent_item' => __('Parent Field', 'wp-impossible-trinity'),
            'parent_item_colon' => __('Parent Field:', 'wp-impossible-trinity'),
            'edit_item' => __('Edit Field', 'wp-impossible-trinity'),
            'update_item' => __('Update Field', 'wp-impossible-trinity'),
            'add_new_item' => __('Add New Field', 'wp-impossible-trinity'),
            'new_item_name' => __('New Field Name', 'wp-impossible-trinity'),
            'menu_name' => __('Fields', 'wp-impossible-trinity'),
        );
        
        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'field'),
        );
        
        register_taxonomy('it_field', array('impossible_trinity'), $args);
    }
}