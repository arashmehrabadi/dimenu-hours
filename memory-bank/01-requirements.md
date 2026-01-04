# Requirements

## Core behavior
1) Menu is always visible (never blocked).
2) Ordering is disabled when CLOSED:
   - Disable "Add to cart" (UI + server-side)
   - Block access to cart/checkout pages via redirect
   - Prevent order placement even with direct POST or bypass attempts.

## Scheduling
A) Weekly schedule
- 7 days
- Each day can be closed
- Each day can have multiple intervals (shifts)
- Overnight shifts supported (e.g. 18:00 -> 02:00)

B) Exceptions (Special dates)
- Single-date: closed OR special hours
- Date range: closed OR special hours
- Exception has higher priority than weekly schedule
- Optional note field per exception

C) Manual override
- manual_closed (force closed) overrides everything
- message for manual mode is user-editable

## UI / Admin
- Simple settings first (done):
  - enabled
  - manual_closed
  - timezone
  - messages editable
- Next:
  - Weekly schedule editor
  - Exceptions editor (single date + date range)
  - Preview: "Now: open/closed, next open/close"

## Public UI
- Banner showing status.human
- Disable ordering buttons when closed
- Respect dimenu design (later refine selectors)

## Tech assumptions
- WordPress plugin independent
- WooCommerce is used for cart/checkout flow (server-side gating built for Woo)
- If dimenu uses a custom order flow later, add hooks for that too.
