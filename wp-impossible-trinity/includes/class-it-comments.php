<?php
/**
 * Comments handling for Impossible Trinity
 */

class WPIT_Comments {
    
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
        // Comments are handled natively by WordPress
        // We can add custom comment templates if needed
        add_filter('comments_template', array($this, 'load_comments_template'));
    }
    
    /**
     * Load custom comments template
     */
    public function load_comments_template($template) {
        if ('impossible_trinity' === get_post_type()) {
            $custom_template = WPIT_PLUGIN_DIR . 'templates/comments.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
}