# Decisions (final)

- Plugin is fully independent (not inside theme, not inside dimenu core).
- Default state:
  - enabled = true
  - schedule default is open (weekly intervals exist)
  - If schedule missing: treat as OPEN (avoid blocking accidentally)

- Messages are user-editable (admin can customize texts).
- Order gating approach:
  - Gate starts from Add to Cart (recommended and implemented)
  - Menu content remains visible at all times

- Manual override exists:
  - manual_closed=true => always closed and ordering disabled

- REST endpoint:
  - /wp-json/dimenu/v1/status (public)

- WooCommerce server-side safety:
  - Add-to-cart validation blocks when closed
  - Cart/Checkout page access redirects when closed
  - Checkout process also blocked with notice

- Timezone source:
  - use settings.timezone, fallback to WP timezone_string, fallback UTC
