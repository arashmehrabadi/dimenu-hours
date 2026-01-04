# Routes & Shortcodes

## REST API
Namespace: dimenu/v1
Route:
- GET /wp-json/dimenu/v1/status

Response example:
{
  is_open: false,
  reason: "manual" | "schedule" | "disabled",
  opens_at: "ISO" | "",
  closes_at: "ISO" | "",
  next_open_at: "ISO" | "",
  timezone: "UTC",
  now: "ISO",
  human: "..."
}

Used by:
- assets/public/public.js

## Shortcodes (planned)
- [dimenu_status]        => show current status banner
- [dimenu_hours_week]    => show weekly table

Not implemented yet.
