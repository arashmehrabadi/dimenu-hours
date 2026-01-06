# Runbook (How to continue next session)

## Quick sanity checks
1) WP Admin -> Plugins -> dimenu Hours is active
2) WP Admin -> dimenu Hours -> manual_closed toggle works
3) REST works:
   - GET /wp-json/dimenu/v1/status returns JSON
4) When manual_closed=ON:
   - Add to cart blocked
   - /cart and /checkout redirect to home and cannot order

## Where we are (current behavior)
- Status Engine supports:
  - enabled toggle
  - manual_closed
  - weekly schedule (multiple intervals + overnight)
  - exceptions (closed/special_hours) with priority over weekly + next_open_at
- Admin UI in Persian with:
  - weekly schedule table (add/remove intervals, closed toggle)
  - exceptions table (closed/special_hours with intervals)
  - editable messages
  - redirect URL for cart/checkout when closed
- WooCommerce gating:
  - add-to-cart blocked when closed
  - cart/checkout/order endpoints redirect to redirect_url (or home/?dimenu_closed=1)
- Public JS:
  - configurable banner container selector + gate selectors (via localized vars)

## Next milestone (Priority)
### A) Polish UI/UX
- Add settings to choose bannerSelector/gateSelectors instead of code defaults.
- Add helper text/validation errors for overlaps/empty intervals in weekly/exceptions forms.
- Optional: add RTL tweaks to admin table widths.

### B) Frontend integration
- Inject banner into real dimenu container once selector provided.
- Tune gate selectors to real dimenu add-to-cart/checkout buttons.

### C) WooCommerce redirect UX
- Show a friendly landing page/message at redirect_url (currently just param on home).

### D) Testing
- Manual: REST `/wp-json/dimenu/v1/status` with weekly + exceptions (closed and special_hours).
- Woo: add-to-cart blocked + cart/checkout redirect when closed; normal flow when open.

## Command checklist for debugging
- Check PHP errors: wp-content/debug.log (if enabled)
- Test REST:
  - curl https://domain/wp-json/dimenu/v1/status
- Woo pages:
  - /cart
  - /checkout
  - /checkout/order-pay/...
