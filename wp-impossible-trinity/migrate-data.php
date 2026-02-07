<?php
/**
 * Data Migration Script
 * Migrates data from SQLite Flask app to WordPress
 * 
 * Usage: Place this file in wp-content/plugins/wp-impossible-trinity/
 *        and navigate to: yoursite.com/wp-content/plugins/wp-impossible-trinity/migrate-data.php
 *        Then delete the file after migration is complete.
 */

// Load WordPress
require_once(dirname(__FILE__) . '/../../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You must be an administrator to run this migration script.');
}

// Database file path (adjust if needed)
$db_file = dirname(__FILE__) . '/../../impossible-trinity/instance/database.db';

if (!file_exists($db_file)) {
    die('Database file not found: ' . $db_file);
}

// Connect to SQLite
try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Start migration
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Migration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h1 { color: #2271b1; }
        .success { color: green; }
        .error { color: red; }
        .info { color: #666; margin: 10px 0; }
        .progress { margin: 20px 0; padding: 10px; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Impossible Trinity Data Migration</h1>';

// Check if migration was requested
if (isset($_GET['migrate']) && $_GET['migrate'] === 'yes') {
    echo '<div class="progress">';
    
    // Step 1: Migrate Users
    echo '<h2>Step 1: Migrating Users</h2>';
    migrate_users($pdo);
    
    // Step 2: Migrate Impossible Trinities
    echo '<h2>Step 2: Migrating Impossible Trinities</h2>';
    migrate_trinities($pdo);
    
    // Step 3: Migrate Comments
    echo '<h2>Step 3: Migrating Comments</h2>';
    migrate_comments($pdo);
    
    echo '</div>';
    echo '<p class="success"><strong>Migration completed successfully!</strong></p>';
    echo '<p><a href="' . admin_url() . '">Go to WordPress Admin</a></p>';
    echo '<p><strong>Important:</strong> Delete this file from your server for security.</p>';
} else {
    echo '<div class="info">';
    echo '<p>This script will migrate data from the Flask SQLite database to WordPress.</p>';
    echo '<p><strong>Database file:</strong> ' . $db_file . '</p>';
    echo '</div>';
    echo '<p><a href="?migrate=yes" class="button button-primary button-large">Start Migration</a></p>';
    
    // Show preview
    echo '<h2>Data Preview</h2>';
    show_preview($pdo);
}

echo '</body>
</html>';

/**
 * Migrate users
 */
function migrate_users($pdo) {
    try {
        $stmt = $pdo->query('SELECT * FROM user');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($users as $user) {
            // Check if user already exists by username
            $existing = get_user_by('login', $user['username']);
            
            if ($existing) {
                echo '<p class="info">✓ User already exists: ' . esc_html($user['username']) . '</p>';
                $skipped++;
                continue;
            }
            
            // Create WordPress user
            $user_id = wp_create_user(
                $user['username'],
                'migrated_' . wp_generate_password(12, false), // Generate random password
                $user['username'] . '@example.com' // Use dummy email
            );
            
            if (is_wp_error($user_id)) {
                echo '<p class="error">✗ Failed to create user: ' . esc_html($user['username']) . ' - ' . $user_id->get_error_message() . '</p>';
            } else {
                // Update user meta
                update_user_meta($user_id, 'is_admin', $user['is_admin'] ? 1 : 0);
                update_user_meta($user_id, 'wpit_migrated', 1);
                
                // Set role
                $user_obj = new WP_User($user_id);
                $user_obj->set_role($user['is_admin'] ? 'administrator' : 'subscriber');
                
                echo '<p class="success">✓ Migrated user: ' . esc_html($user['username']) . '</p>';
                $migrated++;
            }
        }
        
        echo '<p><strong>Users:</strong> ' . $migrated . ' migrated, ' . $skipped . ' skipped</p>';
        
    } catch (PDOException $e) {
        echo '<p class="error">Error migrating users: ' . $e->getMessage() . '</p>';
    }
}

/**
 * Migrate impossible trinities
 */
function migrate_trinities($pdo) {
    try {
        $stmt = $pdo->query('SELECT * FROM impossible_trinity');
        $trinities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($trinities as $trinity) {
            // Find WordPress user
            $wp_user = get_user_by('login', $trinity['username']);
            
            if (!$wp_user) {
                echo '<p class="error">✗ User not found: ' . esc_html($trinity['username']) . ' for trinity: ' . esc_html($trinity['name']) . '</p>';
                $skipped++;
                continue;
            }
            
            // Create post
            $post_data = array(
                'post_title' => $trinity['name'],
                'post_content' => $trinity['description'],
                'post_status' => 'publish',
                'post_type' => 'impossible_trinity',
                'post_author' => $wp_user->ID,
                'post_date' => $trinity['created_at'],
                'post_date_gmt' => get_gmt_from_date($trinity['created_at']),
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {
                echo '<p class="error">✗ Failed to create trinity: ' . esc_html($trinity['name']) . '</p>';
                $skipped++;
            } else {
                // Update meta fields
                update_post_meta($post_id, '_name_en', $trinity['name_en'] ?: 'Impossible Trinity');
                update_post_meta($post_id, '_field', $trinity['field']);
                update_post_meta($post_id, '_element1', $trinity['element1']);
                update_post_meta($post_id, '_element2', $trinity['element2']);
                update_post_meta($post_id, '_element3', $trinity['element3']);
                update_post_meta($post_id, '_element1_sacrifice_explanation', $trinity['element1_sacrifice_explanation']);
                update_post_meta($post_id, '_element2_sacrifice_explanation', $trinity['element2_sacrifice_explanation']);
                update_post_meta($post_id, '_element3_sacrifice_explanation', $trinity['element3_sacrifice_explanation']);
                update_post_meta($post_id, '_agree_count', $trinity['agree_count']);
                update_post_meta($post_id, '_hyperlink', $trinity['hyperlink']);
                update_post_meta($post_id, '_element1_image_url', $trinity['element1_image_url']);
                update_post_meta($post_id, '_element2_image_url', $trinity['element2_image_url']);
                update_post_meta($post_id, '_element3_image_url', $trinity['element3_image_url']);
                update_post_meta($post_id, 'wpit_migrated', 1);
                
                echo '<p class="success">✓ Migrated: ' . esc_html($trinity['name']) . '</p>';
                $migrated++;
            }
        }
        
        echo '<p><strong>Trinities:</strong> ' . $migrated . ' migrated, ' . $skipped . ' skipped</p>';
        
    } catch (PDOException $e) {
        echo '<p class="error">Error migrating trinities: ' . $e->getMessage() . '</p>';
    }
}

/**
 * Migrate comments
 */
function migrate_comments($pdo) {
    try {
        $stmt = $pdo->query('SELECT c.*, u.username FROM comment c JOIN user u ON c.user_id = u.id');
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($comments as $comment) {
            // Find WordPress user
            $wp_user = get_user_by('login', $comment['username']);
            
            if (!$wp_user) {
                echo '<p class="error">✗ User not found for comment ID ' . $comment['id'] . '</p>';
                $skipped++;
                continue;
            }
            
            // Create comment
            $comment_data = array(
                'comment_post_ID' => $comment['it_id'],
                'comment_content' => $comment['content'],
                'user_id' => $wp_user->ID,
                'comment_date' => $comment['created_at'],
                'comment_approved' => 1,
            );
            
            $comment_id = wp_insert_comment($comment_data);
            
            if ($comment_id) {
                echo '<p class="success">✓ Migrated comment ID: ' . $comment['id'] . '</p>';
                $migrated++;
            } else {
                echo '<p class="error">✗ Failed to migrate comment ID: ' . $comment['id'] . '</p>';
                $skipped++;
            }
        }
        
        echo '<p><strong>Comments:</strong> ' . $migrated . ' migrated, ' . $skipped . ' skipped</p>';
        
    } catch (PDOException $e) {
        echo '<p class="error">Error migrating comments: ' . $e->getMessage() . '</p>';
    }
}

/**
 * Show data preview
 */
function show_preview($pdo) {
    try {
        echo '<h3>Users: ' . $pdo->query('SELECT COUNT(*) FROM user')->fetchColumn() . '</h3>';
        echo '<h3>Impossible Trinities: ' . $pdo->query('SELECT COUNT(*) FROM impossible_trinity')->fetchColumn() . '</h3>';
        echo '<h3>Comments: ' . $pdo->query('SELECT COUNT(*) FROM comment')->fetchColumn() . '</h3>';
        
        echo '<h3>Sample Data:</h3>';
        
        $stmt = $pdo->query('SELECT name, field FROM impossible_trinity LIMIT 5');
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($samples) {
            echo '<ul>';
            foreach ($samples as $sample) {
                echo '<li>' . esc_html($sample['name']) . ' (' . esc_html($sample['field']) . ')</li>';
            }
            echo '</ul>';
        }
        
    } catch (PDOException $e) {
        echo '<p class="error">Error fetching preview: ' . $e->getMessage() . '</p>';
    }
}