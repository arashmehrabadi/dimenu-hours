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
- Exceptions exist in schema but NOT applied yet
- Admin UI only has basic toggles/messages, no weekly/exceptions editor

## Next milestone (Priority)
### A) Implement Exceptions in Status Engine
Files:
- includes/class-status.php (update)
- optionally: includes/class-exceptions.php (helper class)

Plan:
1) Parse exceptions from settings
2) Determine if "today" matches:
   - date == today OR start_date<=today<=end_date
3) Apply exception priority:
   - closed => CLOSED (reason: exception)
   - special_hours => evaluate intervals for today (support overnight)
4) Set next_open_at based on exception intervals or fall back to weekly

### B) Admin UI for Weekly Schedule
Files:
- includes/class-settings.php (render form sections + handle POST)
- assets/admin/admin.js (dynamic add/remove intervals)
- assets/admin/admin.css

Plan:
- Render 7-day table
- For each day: checkbox closed, list intervals
- Add "Add interval" button
- Validate overlaps server-side in sanitize_post

### C) Admin UI for Exceptions
Files:
- includes/class-settings.php + admin.js
- store as exceptions[] in option
- allow delete/edit rows

### D) Improve public selectors + banner placement
- inspect dimenu HTML, set precise selectors
- inject banner into dimenu container instead of document.body
- add setting: redirect_url for cart/checkout redirect target

## Command checklist for debugging
- Check PHP errors: wp-content/debug.log (if enabled)
- Test REST:
  - curl https://domain/wp-json/dimenu/v1/status
- Woo pages:
  - /cart
  - /checkout
  - /checkout/order-pay/...
