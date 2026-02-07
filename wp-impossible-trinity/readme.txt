=== Impossible Trinity Database ===
Contributors: P-Chao
Tags: database, impossible trinity, content management
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4 or higher
Stable tag: 1.0.0
License: MIT License

A WordPress plugin for managing and displaying "Impossible Trinities" - a database of concepts where only two out of three elements can be achieved simultaneously.

== Description ==

The Impossible Trinity Database plugin allows you to create, manage, and display "Impossible Trinities" in WordPress. An "Impossible Trinity" is a concept from various fields (economics, computer science, project management, etc.) where only two of three desirable elements can be achieved at the same time.

**Key Features:**

* Custom Post Type for Impossible Trinities
* Dual view modes: Card view and Table view
* Custom meta fields for all trinity elements and descriptions
* Built-in comment system (using WordPress comments)
* Agree/Vote functionality
* Field-based filtering
* Search functionality
* AJAX-powered infinite scroll
* CSV Export/Import for data management
* User permission system (subscribers and administrators)
* Responsive, mobile-friendly design
* WordPress-native styling for seamless integration

== Installation ==

1. Upload the `wp-impossible-trinity` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. A new page "Impossible Trinity List" will be automatically created
4. Navigate to the page to view the plugin

== Usage ==

### Adding Impossible Trinities

1. Go to **Impossible Trinity > Add New** in the WordPress admin
2. Fill in the required fields:
   - **Name**: The Chinese name of the impossible trinity
   - **English Name**: The English name (default: "Impossible Trinity")
   - **Field**: The domain/field (e.g., Economics, Computer Science)
   - **Three Elements**: The three elements that form the trinity
   - **Sacrifice Explanations**: What happens when each element is not satisfied
   - **Description**: Detailed explanation of the concept
   - **References**: Optional external links
3. Click "Publish" to save

### Displaying Trinities

Use the shortcode `[impossible_trinity]` to display the list:

```
[impossible_trinity view="card" per_page="12" field="" search=""]
```

**Parameters:**
- `view`: "card" or "table" (default: "card")
- `per_page`: Number of items per page (default: 12 for card, 20 for table)
- `field`: Filter by field name
- `search`: Search term

Example: `[impossible_trinity view="table" field="Economics"]`

### Displaying Single Trinity

Use the shortcode `[impossible_trinity_detail id="123"]` to display a specific trinity, or simply visit the single post page.

### CSV Export/Import

1. Go to **Impossible Trinity > Export/Import**
2. To export: Click "Download CSV" button
3. To import: Upload a CSV file with the correct format

**CSV Format:**
```
name, name_en, field, element1, element2, element3, description, 
element1_sacrifice_explanation, element2_sacrifice_explanation, element3_sacrifice_explanation, 
hyperlink, element1_image_url, element2_image_url, element3_image_url
```

### User Permissions

* **Subscribers**: Can create, edit, and delete their own trinities
* **Administrators**: Can manage all trinities, export/import data

== Data Migration from Flask App ==

If you have data from the original Flask application:

1. Copy your SQLite database file (`database.db`) to the plugin directory
2. Visit: `yoursite.com/wp-content/plugins/wp-impossible-trinity/migrate-data.php`
3. Review the data preview
4. Click "Start Migration" to begin
5. After successful migration, **delete the migrate-data.php file** for security

The migration will:
* Create WordPress users from the Flask database (with random passwords)
* Migrate all impossible trinities with their metadata
* Migrate all comments
* Preserve all timestamps

**Note:** Migrated users will need to reset their passwords through WordPress's "Lost your password?" feature.

== Screenshots ==

1. The list view shows cards with all trinity information
2. The table view provides a compact, searchable list
3. The detail page shows full information with the three elements
4. The admin interface provides full CRUD operations
5. Export/Import functionality for bulk data management

== Changelog ==

= 1.0.0 =
* Initial release
* Custom post type implementation
* Card and table view modes
* AJAX infinite scroll
* CSV export/import
* Data migration script from Flask app
* Responsive design

== Upgrade Notice ==

No special upgrade instructions needed. Simply deactivate the old version and activate the new version, or overwrite the plugin files.

== Frequently Asked Questions ==

= Can I customize the styling? =

Yes! The plugin uses WordPress-compatible CSS with prefixed classes (`.it-*`). You can override styles in your theme's CSS file or use a custom CSS plugin.

= Can I use this with page builders? =

Yes! The shortcodes work with most page builders including Elementor, Beaver Builder, and Gutenberg blocks.

= What happens to my data if I deactivate the plugin? =

Your data is stored in WordPress database and will not be deleted. Simply reactivate the plugin to access it again.

= Can I export my data? =

Yes, use the Export/Import feature to download all trinities as a CSV file.

= Is this compatible with multisite? =

Yes, the plugin works with WordPress multisite installations.

== License ==

This plugin is licensed under the MIT License.

== Credits ==

* Concept and original design by P-Chao
* WordPress plugin implementation
* Based on the Flask Impossible Trinity Database application

== Support ==

For support, please visit: https://github.com/P-Chao/IT/issues

For feature requests, please open an issue on GitHub.