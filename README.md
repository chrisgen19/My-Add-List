# My Add List WordPress Plugin

A simple and lightweight WordPress plugin that allows you to create and manage lists through the admin panel and display them anywhere on your site using shortcodes.

## Features

- Easy-to-use admin interface for managing list items
- Drag-and-drop functionality for reordering items
- Shortcode support for displaying lists
- Responsive admin design
- Clean and minimal frontend output
- No dependencies except jQuery (included with WordPress)

## Installation

1. Download the plugin files
2. Create a new directory named `my-add-list` in your WordPress plugins directory (`wp-content/plugins/`)
3. Upload the plugin files to the `/wp-content/plugins/my-add-list` directory
4. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

### Admin Panel

1. After activation, find "My Add List" in your WordPress admin menu
2. Click "My Add List" to access the settings page
3. Add new items using the "Add New Item" button
4. Edit existing items by modifying the text in the input fields
5. Remove items using the "Remove" button next to each item
6. Reorder items by dragging and dropping them into position
7. Click "Save Changes" to update your list

### Displaying Lists

To display your list on any page or post, use the shortcode:
```
[myaddlist]
```