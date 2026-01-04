# Admin UI

Current (implemented):
Admin menu: "dimenu Hours"
Page includes:
- Enabled toggle
- Manual override toggle (manual_closed)
- Timezone input
- Editable messages (closed_now/open_now/next_open_prefix/until_prefix/manual_closed)

Storage:
- POST handled in class-plugin.php (admin_init)
- Nonce: dimenu_hours_save_settings / dimenu_hours_nonce
- Sanitization: Dimenu_Hours_Settings::sanitize_post

Next (to build):
1) Weekly schedule editor
   - Each day: is_closed checkbox
   - Intervals list: add/remove intervals
   - Validation: prevent overlaps; allow overnight
   - "Copy to all days" helper

2) Exceptions editor
   - Add single date exception:
     - closed OR special hours
   - Add date range exception:
     - closed OR special hours
   - Note optional
   - Sort by date
   - Validation:
     - correct date format
     - start<=end for ranges
     - interval validation same as weekly
   - Preview status for a chosen date/time

3) Preview panel
   - show current status output (same as REST)
   - show next open/close
