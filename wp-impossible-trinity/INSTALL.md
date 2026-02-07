# Impossible Trinity WordPress Plugin - Quick Start Guide

## Overview

This WordPress plugin is a complete migration of the Flask Impossible Trinity Database application to WordPress. It provides all the original functionality plus WordPress-native features.

## Installation

### Method 1: WordPress Admin (Recommended)

1. Go to WordPress Admin > Plugins > Add New
2. Click "Upload Plugin"
3. Select the `wp-impossible-trinity` folder (zip it first)
4. Click "Install Now"
5. Activate the plugin

### Method 2: FTP/File Manager

1. Upload the `wp-impossible-trinity` folder to `/wp-content/plugins/`
2. Go to WordPress Admin > Plugins
3. Find "Impossible Trinity Database" and click "Activate"

## Initial Setup

After activation, the plugin will automatically:

1. Create a custom post type "Impossible Trinity"
2. Create a page "Impossible Trinity List" with the list shortcode
3. Register all necessary taxonomies and meta fields

## Data Migration (Optional)

If you have data from the Flask app:

1. Copy your SQLite database file (`impossible-trinity/instance/database.db`) to the plugin directory
2. Visit: `https://yoursite.com/wp-content/plugins/wp-impossible-trinity/migrate-data.php`
3. Review the data preview
4. Click "Start Migration"
5. Wait for migration to complete
6. **IMPORTANT:** Delete the `migrate-data.php` file for security

The migration will transfer:
- All users (with random passwords - users will need to reset)
- All impossible trinities with metadata
- All comments
- Original timestamps

## Basic Usage

### Adding a New Trinity

1. Go to **Impossible Trinity > Add New** in admin menu
2. Fill in the fields:
   - **Name** (Chinese)
   - **English Name** (defaults to "Impossible Trinity")
   - **Field** (e.g., Economics, Computer Science)
   - **Element 1, 2, 3**
   - **Sacrifice Explanations** for each element
   - **Description**
   - **Reference Link** (optional)
3. Click "Publish"

### Viewing Trinities

Visit the "Impossible Trinity List" page or use the shortcode:

```
[impossible_trinity]
```

With options:
```
[impossible_trinity view="card" per_page="12" field="Economics"]
```

### Shortcode Parameters

- `view`: "card" (default) or "table"
- `per_page`: Number of items (12 for card, 20 for table default)
- `field`: Filter by field name
- `search`: Search term

## Features

### Dual View Modes

- **Card View**: Visual cards with all key information
- **Table View**: Compact, sortable table format

Click the toggle buttons to switch between views.

### Filtering

Click on field chips to filter trinities by domain/field.

### Search

Use the search bar to find trinities by name, description, or elements.

### Voting/Agree

- Logged-in users can "agree" to trinities
- Each user can only vote once per trinity
- Vote counts are displayed on cards and detail pages

### Comments

- WordPress native comment system
- Users can discuss each trinity
- Comments appear on detail pages

### CSV Export/Import

1. Go to **Impossible Trinity > Export/Import**
2. Export: Click "Download CSV"
3. Import: Upload a CSV file with the required format

### Admin Features

**Subscribers:**
- Create new trinities
- Edit/delete their own trinities

**Administrators:**
- Manage all trinities
- Export/import data
- Access all user content

## File Structure

```
wp-impossible-trinity/
├── wp-impossible-trinity.php          # Main plugin file
├── readme.txt                        # WordPress plugin readme
├── INSTALL.md                        # This file
├── migrate-data.php                  # Data migration script (delete after use)
├── includes/
│   ├── class-it-post-type.php         # Custom post type registration
│   ├── class-it-metaboxes.php       # Admin meta boxes
│   ├── class-it-shortcodes.php        # Frontend shortcodes
│   ├── class-it-ajax.php            # AJAX handlers
│   ├── class-it-comments.php         # Comment functionality
│   ├── class-it-export-import.php     # CSV export/import
│   └── class-it-templates.php       # Template loading
├── templates/
│   ├── single-impossible-trinity.php  # Single post template
│   └── archive-impossible-trinity.php # Archive template
└── assets/
    ├── css/
    │   └── style.css               # Plugin styles
    └── js/
        └── main.js                  # Plugin JavaScript
```

## Customization

### Styling

Override CSS in your theme:

```css
/* Example: Change card background */
.it-card {
    background: #f0f0f0;
}

/* Example: Change header color */
.it-archive-title {
    color: #ff6600;
}
```

### Templates

Copy template files to your theme to override:

```
your-theme/
├── single-impossible-trinity.php
└── archive-impossible-trinity.php
```

## Troubleshooting

### Trinities not displaying

- Check that the plugin is activated
- Verify you have published trinities
- Clear browser cache
- Check for JavaScript errors in console

### Migration script not working

- Verify the database file path in `migrate-data.php`
- Ensure the database file is readable
- Check PHP error logs
- Make sure you're logged in as administrator

### Styles not loading

- Clear WordPress cache (if using caching plugin)
- Clear browser cache
- Check browser console for 404 errors on CSS/JS files
- Verify permissions on assets folder

## Security Notes

1. **Delete `migrate-data.php`** after migration is complete
2. Regular backups are recommended before major updates
3. All user input is sanitized through WordPress functions
4. AJAX requests use nonces for security

## Support

For issues, feature requests, or questions:

- GitHub Issues: https://github.com/P-Chao/IT/issues
- WordPress Plugin Repository

## License

MIT License - See LICENSE file for details

## Credits

Original Flask application: P-Chao
WordPress plugin implementation: Based on original design

---

**Version:** 1.0.0  
**Last Updated:** 2025-02-07