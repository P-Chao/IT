<?php
/**
 * CSV Export and Import for Impossible Trinity
 */

class WPIT_Export_Import {
    
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
        add_action('admin_menu', array($this, 'add_export_import_page'));
        add_action('admin_init', array($this, 'handle_export'));
        add_action('admin_init', array($this, 'handle_import'));
    }
    
    /**
     * Add export/import page to admin menu
     */
    public function add_export_import_page() {
        add_submenu_page(
            'edit.php?post_type=impossible_trinity',
            __('Export/Import', 'wp-impossible-trinity'),
            __('Export/Import', 'wp-impossible-trinity'),
            'manage_options',
            'wpit-export-import',
            array($this, 'render_export_import_page')
        );
    }
    
    /**
     * Render export/import page
     */
    public function render_export_import_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-impossible-trinity'));
        }
        
        $export_url = admin_url('admin.php?page=wpit-export-import&action=export&_wpnonce=' . wp_create_nonce('wpit_export'));
        ?>
        <div class="wrap">
            <h1><?php _e('Export / Import Impossible Trinities', 'wp-impossible-trinity'); ?></h1>
            
            <div class="card">
                <h2><?php _e('Export to CSV', 'wp-impossible-trinity'); ?></h2>
                <p><?php _e('Export all impossible trinities to a CSV file.', 'wp-impossible-trinity'); ?></p>
                <a href="<?php echo esc_url($export_url); ?>" class="button button-primary button-large">
                    <?php _e('Download CSV', 'wp-impossible-trinity'); ?>
                </a>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2><?php _e('Import from CSV', 'wp-impossible-trinity'); ?></h2>
                <p><?php _e('Import impossible trinities from a CSV file.', 'wp-impossible-trinity'); ?></p>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('wpit_import', 'wpit_import_nonce'); ?>
                    <input type="file" name="csv_file" accept=".csv" required>
                    <input type="hidden" name="action" value="import">
                    <input type="submit" class="button button-primary button-large" value="<?php _e('Upload and Import', 'wp-impossible-trinity'); ?>">
                </form>
                
                <div style="margin-top: 20px;">
                    <h3><?php _e('CSV Format', 'wp-impossible-trinity'); ?></h3>
                    <p><?php _e('The CSV file should contain the following columns:', 'wp-impossible-trinity'); ?></p>
                    <code>
                        name, name_en, field, element1, element2, element3, description, 
                        element1_sacrifice_explanation, element2_sacrifice_explanation, element3_sacrifice_explanation, 
                        hyperlink, element1_image_url, element2_image_url, element3_image_url
                    </code>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle CSV export
     */
    public function handle_export() {
        if (!isset($_GET['action']) || $_GET['action'] !== 'export') {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'wp-impossible-trinity'));
        }
        
        check_admin_referer('wpit_export', '_wpnonce');
        
        // Query all impossible trinities
        $args = array(
            'post_type' => 'impossible_trinity',
            'post_status' => 'any',
            'posts_per_page' => -1,
        );
        
        $query = new WP_Query($args);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=impossible_trinities_' . date('Y-m-d_H-i-s') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 support in Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Write header
        fputcsv($output, array(
            'name',
            'name_en',
            'field',
            'element1',
            'element2',
            'element3',
            'description',
            'element1_sacrifice_explanation',
            'element2_sacrifice_explanation',
            'element3_sacrifice_explanation',
            'hyperlink',
            'element1_image_url',
            'element2_image_url',
            'element3_image_url',
        ));
        
        // Write data rows
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $row = array(
                get_the_title(),
                get_post_meta($post_id, '_name_en', true),
                get_post_meta($post_id, '_field', true),
                get_post_meta($post_id, '_element1', true),
                get_post_meta($post_id, '_element2', true),
                get_post_meta($post_id, '_element3', true),
                strip_tags(get_the_content()),
                get_post_meta($post_id, '_element1_sacrifice_explanation', true),
                get_post_meta($post_id, '_element2_sacrifice_explanation', true),
                get_post_meta($post_id, '_element3_sacrifice_explanation', true),
                get_post_meta($post_id, '_hyperlink', true),
                get_post_meta($post_id, '_element1_image_url', true),
                get_post_meta($post_id, '_element2_image_url', true),
                get_post_meta($post_id, '_element3_image_url', true),
            );
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Handle CSV import
     */
    public function handle_import() {
        if (!isset($_POST['action']) || $_POST['action'] !== 'import') {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'wp-impossible-trinity'));
        }
        
        check_admin_referer('wpit_import', 'wpit_import_nonce');
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_die(__('Error uploading file.', 'wp-impossible-trinity'));
        }
        
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if (!$handle) {
            wp_die(__('Error opening CSV file.', 'wp-impossible-trinity'));
        }
        
        // Read header
        $header = fgetcsv($handle);
        
        if (!$header) {
            fclose($handle);
            wp_die(__('Invalid CSV file.', 'wp-impossible-trinity'));
        }
        
        $imported = 0;
        $skipped = 0;
        $current_user_id = get_current_user_id();
        
        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            // Map columns to fields
            $data = array_combine($header, $row);
            
            // Validate required fields
            if (empty($data['name']) || empty($data['element1']) || empty($data['element2']) || empty($data['element3'])) {
                $skipped++;
                continue;
            }
            
            // Create or update post
            $post_data = array(
                'post_title' => $data['name'],
                'post_content' => isset($data['description']) ? $data['description'] : '',
                'post_status' => 'publish',
                'post_type' => 'impossible_trinity',
                'post_author' => $current_user_id,
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (!is_wp_error($post_id)) {
                // Update meta fields
                update_post_meta($post_id, '_name_en', isset($data['name_en']) ? $data['name_en'] : 'Impossible Trinity');
                update_post_meta($post_id, '_field', isset($data['field']) ? $data['field'] : '');
                update_post_meta($post_id, '_element1', $data['element1']);
                update_post_meta($post_id, '_element2', $data['element2']);
                update_post_meta($post_id, '_element3', $data['element3']);
                update_post_meta($post_id, '_element1_sacrifice_explanation', isset($data['element1_sacrifice_explanation']) ? $data['element1_sacrifice_explanation'] : '');
                update_post_meta($post_id, '_element2_sacrifice_explanation', isset($data['element2_sacrifice_explanation']) ? $data['element2_sacrifice_explanation'] : '');
                update_post_meta($post_id, '_element3_sacrifice_explanation', isset($data['element3_sacrifice_explanation']) ? $data['element3_sacrifice_explanation'] : '');
                update_post_meta($post_id, '_hyperlink', isset($data['hyperlink']) ? $data['hyperlink'] : '');
                update_post_meta($post_id, '_element1_image_url', isset($data['element1_image_url']) ? $data['element1_image_url'] : '');
                update_post_meta($post_id, '_element2_image_url', isset($data['element2_image_url']) ? $data['element2_image_url'] : '');
                update_post_meta($post_id, '_element3_image_url', isset($data['element3_image_url']) ? $data['element3_image_url'] : '');
                
                $imported++;
            } else {
                $skipped++;
            }
        }
        
        fclose($handle);
        
        // Redirect with success message
        wp_redirect(add_query_arg(array(
            'imported' => $imported,
            'skipped' => $skipped,
        ), admin_url('admin.php?page=wpit-export-import')));
        exit;
    }
}