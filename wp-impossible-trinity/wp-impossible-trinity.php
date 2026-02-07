<?php
/**
 * Plugin Name: Impossible Trinity Database
 * Plugin URI: https://github.com/P-Chao/IT
 * Description: A WordPress plugin for managing and displaying "Impossible Trinities" - a database of concepts where only two out of three elements can be achieved simultaneously.
 * Version: 1.0.0
 * Author: P-Chao
 * License: MIT
 * Text Domain: wp-impossible-trinity
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPIT_VERSION', '1.0.0');
define('WPIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPIT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPIT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class WP_Impossible_Trinity {
    
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
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once WPIT_PLUGIN_DIR . 'includes/class-it-post-type.php';
        require_once WPIT_PLUGIN_DIR . 'includes/class-it-metaboxes.php';
        require_once WPIT_PLUGIN_DIR . 'includes/class-it-shortcodes.php';
        require_once WPIT_PLUGIN_DIR . 'includes/class-it-ajax.php';
        require_once WPIT_PLUGIN_DIR . 'includes/class-it-comments.php';
        require_once WPIT_PLUGIN_DIR . 'includes/class-it-export-import.php';
        require_once WPIT_PLUGIN_DIR . 'includes/class-it-templates.php';
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Initialize classes
        WPIT_Post_Type::get_instance();
        WPIT_Metaboxes::get_instance();
        WPIT_Shortcodes::get_instance();
        WPIT_Ajax::get_instance();
        WPIT_Comments::get_instance();
        WPIT_Export_Import::get_instance();
        WPIT_Templates::get_instance();
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create necessary pages
        $this->create_pages();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        add_option('wpit_installed_date', current_time('mysql'));
        add_option('wpit_version', WPIT_VERSION);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('wp-impossible-trinity', false, dirname(WPIT_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Create necessary pages on activation
     */
    private function create_pages() {
        $pages = array(
            array(
                'title' => __('Impossible Trinity List', 'wp-impossible-trinity'),
                'content' => '[impossible_trinity]',
                'slug' => 'impossible-trinities',
            ),
        );
        
        foreach ($pages as $page) {
            $existing_page = get_page_by_path($page['slug']);
            
            if (!$existing_page) {
                $page_id = wp_insert_post(array(
                    'post_title' => $page['title'],
                    'post_content' => $page['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $page['slug'],
                ));
            }
        }
    }
}

/**
 * Initialize the plugin
 */
function wpit_init() {
    return WP_Impossible_Trinity::get_instance();
}

// Start the plugin
wpit_init();