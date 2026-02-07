<?php
/**
 * Template handling for Impossible Trinity
 */

class WPIT_Templates {
    
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
        add_filter('single_template', array($this, 'load_single_template'));
        add_filter('archive_template', array($this, 'load_archive_template'));
    }
    
    /**
     * Load single template for impossible trinity
     */
    public function load_single_template($single_template) {
        global $post;
        
        if ('impossible_trinity' === $post->post_type) {
            $custom_template = WPIT_PLUGIN_DIR . 'templates/single-impossible-trinity.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $single_template;
    }
    
    /**
     * Load archive template for impossible trinity
     */
    public function load_archive_template($archive_template) {
        if (is_post_type_archive('impossible_trinity')) {
            $custom_template = WPIT_PLUGIN_DIR . 'templates/archive-impossible-trinity.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $archive_template;
    }
}