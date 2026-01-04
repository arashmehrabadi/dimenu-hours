# TODO (Next Steps)

## Priority 1 — Exceptions in Status Engine
- Implement exceptions evaluation in Dimenu_Hours_Status:
  - match date or range
  - override weekly schedule
  - handle special_hours intervals and overnight
  - set reason="exception" (optional new reason)

## Priority 2 — Admin UI for Weekly + Exceptions
- Weekly editor UI (multiple shifts)
- Exceptions UI (single + range, closed/special)
- Validation (overlaps, dates, required fields)

## Priority 3 — Public UI improvements
- Banner inject into dimenu container
- Refine selectors for dimenu add-to-cart buttons
- Add setting: redirect URL (menu page) instead of home

## Priority 4 — Shortcodes
- [dimenu_status]
- [dimenu_hours_week]

## Priority 5 — Performance & Caching
- Cache status for short time (e.g. 30s) if needed
- Avoid excessive REST calls (use local state + refresh interval)

## Priority 6 — Multi-site / multi-location (future)
- per-location schedule using post meta or custom table
