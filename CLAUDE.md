# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Carramba WooCommerce Order Tracking is a WordPress/WooCommerce plugin for managing shipping company profiles and attaching tracking information to customer orders. It displays tracking links in order emails and the customer account area.

**Requirements:** WordPress 5.0+, WooCommerce 4.0+, PHP 7.4+

## Development Commands

No build tools, test suites, or linting configuration exist. This is a traditional WordPress plugin developed without npm/composer.

To develop locally:
1. Symlink or copy the plugin to a WordPress installation's `/wp-content/plugins/` directory
2. Activate via WordPress admin or WP-CLI: `wp plugin activate carramba-woo-order-tracking`

## Architecture

### Class Structure (Singleton Pattern)

All core classes use `get_instance()` singleton pattern and are initialized in the main plugin file.

| Class | File | Responsibility |
|-------|------|----------------|
| `CWOT_Database` | `includes/class-cwot-database.php` | Shipper CRUD, table creation, default data |
| `CWOT_Admin` | `includes/class-cwot-admin.php` | Admin menu, settings page, form handling |
| `CWOT_Order_Tracking` | `includes/class-cwot-order-tracking.php` | Order meta box, tracking data save/retrieve, HPOS compatibility |
| `CWOT_Email` | `includes/class-cwot-email.php` | Email integration, frontend order details display |

### Key Files

- **Entry point:** `carramba-woo-order-tracking.php` - Plugin bootstrap, activation/deactivation hooks
- **Templates:** `templates/admin/` - Settings page, shipper form, shippers list
- **Assets:** `assets/js/admin.js` (validation, delete confirm), `assets/js/order.js` (dynamic tracking fields)

### Database

- **Custom table:** `{prefix}_cwot_shippers` - Stores shipping company profiles
- **Order meta keys:**
  - `_cwot_tracking_shipper_id` - Selected shipper ID
  - `_cwot_tracking_shipper_name` - Shipper name snapshot (for deleted shipper fallback)
  - `_cwot_tracking_url` - Tracking URL template snapshot (for deleted shipper fallback)
  - `_cwot_tracking_numbers` - Array of tracking numbers
  - `_cwot_tracking_number` - Legacy single tracking (backward compat)
  - `_cwot_tracking_email_sent_at` - Timestamp when tracking email was sent

### HPOS Compatibility

The plugin supports WooCommerce High-Performance Order Storage. `CWOT_Order_Tracking` handles both HPOS and legacy post meta storage transparently.

## Coding Conventions

- All admin forms use WordPress nonce verification
- Output escaping via `esc_html()`, `esc_attr()`, `esc_url()`
- Capability checks use WooCommerce's `manage_woocommerce` capability
- Tracking URLs use `{tracking_number}` placeholder pattern
- Text domain: `carramba-woo-order-tracking`
