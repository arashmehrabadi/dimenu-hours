# Changelog

## 0.1.0 (2026-01-04)
- Plugin skeleton created
- Settings page implemented:
  - enabled, manual_closed, timezone, messages editable
- Status Engine implemented (weekly + manual override)
- REST endpoint: GET /wp-json/dimenu/v1/status
- Public JS gating:
  - fetch REST, show banner, disable ordering buttons (generic selectors)
- WooCommerce server-side gating implemented:
  - block add-to-cart
  - redirect cart/checkout (robust detection; wp_loaded priority 1)
  - block checkout with notice
- Fixed fatal error due to incorrect class name in helpers.php (helpers now only Dimenu_Hours_Helpers)
