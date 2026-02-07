<?php
/**
 * Archive Template for Impossible Trinity
 */

get_header();

$per_page_card = 12;
$per_page_table = 20;
$current_view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'card';
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <div class="it-archive-header">
            <h1 class="it-archive-title">
                <?php post_type_archive_title(); ?>
            </h1>
            
            <div class="it-view-toggle">
                <button class="it-toggle-btn <?php echo $current_view === 'card' ? 'active' : ''; ?>" 
                        onclick="switchView('card')" aria-label="<?php _e('Card view', 'wp-impossible-trinity'); ?>">
                    <span class="it-toggle-text"><?php _e('Cards', 'wp-impossible-trinity'); ?></span>
                </button>
                <button class="it-toggle-btn <?php echo $current_view === 'table' ? 'active' : ''; ?>" 
                        onclick="switchView('table')" aria-label="<?php _e('Table view', 'wp-impossible-trinity'); ?>">
                    <span class="it-toggle-text"><?php _e('Table', 'wp-impossible-trinity'); ?></span>
                </button>
            </div>
        </div>
        
        <?php
        // Display the list shortcode
        echo do_shortcode('[impossible_trinity view="' . esc_attr($current_view) . '" per_page="' . ($current_view === 'table' ? $per_page_table : $per_page_card) . '"]');
        ?>
        
    </main>
</div>

<script>
function switchView(view) {
    const url = new URL(window.location);
    url.searchParams.set('view', view);
    url.searchParams.delete('paged');
    window.location.href = url.toString();
}
</script>

<?php get_sidebar(); ?>
<?php get_footer(); ?>