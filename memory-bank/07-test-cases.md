# Test Cases

## REST Status
1) enabled=false => is_open=true, reason=disabled
2) manual_closed=true => is_open=false, reason=manual, human=manual_closed message
3) manual_closed=false + weekly interval contains now => is_open=true, reason=schedule, closes_at set
4) manual_closed=false + outside intervals => is_open=false, next_open_at set

## Overnight shift
- Interval 18:00 -> 02:00
  - at 23:00 same day => open
  - at 01:00 next day => open (from yesterday overnight)
  - at 03:00 => closed (next open calculated)

## WooCommerce gating
When CLOSED (manual or schedule):
1) Add to cart on product page => blocked + notice
2) Open /cart => redirected + notice
3) Open /checkout => redirected + notice
4) Try direct order-pay/order-received endpoints => redirected
5) Try placing order with crafted POST => blocked by woocommerce_check_cart_items

When OPEN:
1) Add to cart works
2) Cart/checkout accessible
3) Order placement works

## Client-side gating
When CLOSED:
- banner visible
- buttons disabled
When OPEN:
- no banner (or different open banner later)
- buttons enabled

## Exceptions (future)
- Exception closed overrides weekly open
- Exception special hours overrides weekly closed
- Range exceptions work for all dates in range
