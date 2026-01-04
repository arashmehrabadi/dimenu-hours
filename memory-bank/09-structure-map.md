# Structure Map (Plugin Folder Tree + Status)

Root:
wp-content/plugins/dimenu-hours/

## Top-level
- dimenu-hours.php ✅
  - plugin bootstrap, constants, register activation/deactivation, run plugin
- readme.txt ⏳ (optional)
- uninstall.php ⏳ (optional cleanup)

## assets/
### assets/admin/
- admin.css ⏳ (not used yet)
- admin.js ⏳ (not used yet)

### assets/public/
- public.css ✅ (banner rtl basic)
- public.js ✅ (REST fetch + banner + disable buttons)

## includes/
- class-plugin.php ✅
  - registers admin menu
  - enqueues assets
  - handles settings POST + nonce
  - registers REST routes
  - initializes WooCommerce gating
- class-activator.php ✅
  - sets default option schema
- class-deactivator.php ✅ (no destructive action)
- class-settings.php ✅
  - get/update option
  - sanitize POST
  - renders admin form
- helpers.php ✅
  - Dimenu_Hours_Helpers (time parsing, tz, normalize intervals)
- class-status.php ✅ (MVP v1)
  - manual override + weekly schedule
  - supports multiple intervals + overnight shifts
  - TODO: add exceptions priority
- class-rest.php ✅
  - GET /dimenu/v1/status
- class-woocommerce.php ✅
  - server-side gating:
    - block add to cart
    - redirect cart/checkout (wp_loaded priority 1)
    - block checkout items
- class-schedule.php ⏳ (placeholder)
- class-exceptions.php ⏳ (placeholder)
- class-shortcodes.php ⏳ (planned)

## templates/
- widget-status.php ⏳ (planned)
- widget-hours-week.php ⏳ (planned)

## memory-bank/
- 00-context.md ✅
- 01-requirements.md ✅
- 02-decisions.md ✅
- 03-data-model.md ✅
- 04-routes-shortcodes.md ✅
- 05-admin-ui.md ✅
- 06-public-behavior.md ✅
- 07-test-cases.md ✅
- 08-todo.md ✅
- 09-structure-map.md ✅ (this file)
- 10-runbook.md ✅ (next file)
- 99-changelog.md ✅
