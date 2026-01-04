# Public Behavior

Goal:
- Always show menu
- Disable ordering when closed

Client-side (implemented):
- public.js fetches REST status
- If closed:
  - banner inserted at top of body (temporary)
  - disables buttons with generic selectors:
    - add-to-cart / checkout related
  - re-applies gating after short delays (for dynamic content)

Server-side WooCommerce (implemented):
- Block add to cart:
  - woocommerce_add_to_cart_validation => false with wc_add_notice
- Prevent cart/checkout access:
  - wp_loaded (priority 1) robust detection:
    - is_cart()/is_checkout()
    - is_page(wc_get_page_id('cart'/'checkout'))
    - URL endpoints order-pay / order-received
  - redirect to home_url('/?dimenu_closed=1')
- Also blocks checkout process:
  - woocommerce_check_cart_items => wc_add_notice

Known UI improvement:
- Banner placement should target the dimenu menu container (not body)
- Button selectors should be refined based on dimenu HTML (inspect outerHTML)

Future:
- Add setting "menu_page_url" for redirect destination
- Add multi-language output if needed
