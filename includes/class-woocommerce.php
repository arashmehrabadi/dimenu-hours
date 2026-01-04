<?php
if (!defined('ABSPATH')) exit;

require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-status.php';
require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-settings.php';

class Dimenu_Hours_WooCommerce {

  public function init() {
    // Block add-to-cart server-side
    add_filter('woocommerce_add_to_cart_validation', array($this, 'block_add_to_cart'), 10, 3);

    // Redirect from cart/checkout when closed
    add_action('wp_loaded', array($this, 'maybe_redirect_cart_checkout'), 1);

    // Optional: block checkout process (extra safety)
    add_action('woocommerce_check_cart_items', array($this, 'maybe_block_checkout'), 20);
  }

  private function should_gate(): bool {
    $settings = Dimenu_Hours_Settings::get();

    // feature disabled => do not gate
    if (isset($settings['enabled']) && !$settings['enabled']) return false;

    // if status says closed => gate
    $status = Dimenu_Hours_Status::get_status();
    if (!isset($status['is_open']) || $status['is_open'] !== false) return false;

    // respect UI flags if present
    $ui = $settings['ui'] ?? array();
    // if neither gating enabled, then don't gate
    $gateAdd = isset($ui['gate_add_to_cart']) ? (bool)$ui['gate_add_to_cart'] : true;
    $gateChk = isset($ui['gate_checkout']) ? (bool)$ui['gate_checkout'] : true;

    // If request is add-to-cart path, gateAdd used elsewhere; for generic, return true if any gating enabled
    return ($gateAdd || $gateChk);
  }

  private function get_closed_message(): string {
    $settings = Dimenu_Hours_Settings::get();
    $status = Dimenu_Hours_Status::get_status();

    // Prefer status.human, fallback to messages
    if (!empty($status['human'])) return (string)$status['human'];

    $msgs = $settings['messages'] ?? array();
    return $msgs['closed_now'] ?? 'الان بسته‌ایم.';
  }

  public function block_add_to_cart($passed, $product_id, $quantity) {
    // Only if WooCommerce exists
    if (!function_exists('wc_add_notice')) return $passed;

    $settings = Dimenu_Hours_Settings::get();
    $ui = $settings['ui'] ?? array();
    $gateAdd = isset($ui['gate_add_to_cart']) ? (bool)$ui['gate_add_to_cart'] : true;

    if (!$gateAdd) return $passed;

    $status = Dimenu_Hours_Status::get_status();
    if (isset($status['is_open']) && $status['is_open'] === false) {
      wc_add_notice($this->get_closed_message(), 'error');
      return false;
    }

    return $passed;
  }

  public function maybe_redirect_cart_checkout() {
  // Only front-end
  if (is_admin()) return;

  // Woo helpers must exist
  if (!function_exists('wc_get_page_id')) return;

  $settings = Dimenu_Hours_Settings::get();
  $ui = $settings['ui'] ?? array();
  $gateChk = isset($ui['gate_checkout']) ? (bool)$ui['gate_checkout'] : true;
  if (!$gateChk) return;

  // If open => allow
  $status = Dimenu_Hours_Status::get_status();
  if (!isset($status['is_open']) || $status['is_open'] !== false) return;

  // Detect cart/checkout pages robustly
  $cart_id = wc_get_page_id('cart');
  $checkout_id = wc_get_page_id('checkout');

  $is_cart_page = (function_exists('is_cart') && is_cart()) || ($cart_id > 0 && is_page($cart_id));
  $is_checkout_page = (function_exists('is_checkout') && is_checkout()) || ($checkout_id > 0 && is_page($checkout_id));

  // Detect checkout endpoints too (order-pay / order-received)
  $uri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';
  $uri_l = strtolower($uri);
  $is_checkout_endpoint = (strpos($uri_l, 'order-pay') !== false) || (strpos($uri_l, 'order-received') !== false);

  if ($is_cart_page || $is_checkout_page || $is_checkout_endpoint) {

    // Show notice (if possible)
    if (function_exists('wc_add_notice')) {
      wc_add_notice($this->get_closed_message(), 'error');
    }

    // 🔥 IMPORTANT: also block further processing
    // Redirect target (later we can add a setting for menu URL)
    $target = home_url('/?dimenu_closed=1');
    wp_safe_redirect($target, 302);
    exit;
  }
}


  public function maybe_block_checkout() {
    if (!function_exists('wc_add_notice')) return;

    $settings = Dimenu_Hours_Settings::get();
    $ui = $settings['ui'] ?? array();
    $gateChk = isset($ui['gate_checkout']) ? (bool)$ui['gate_checkout'] : true;

    if (!$gateChk) return;

    $status = Dimenu_Hours_Status::get_status();
    if (isset($status['is_open']) && $status['is_open'] === false) {
      wc_add_notice($this->get_closed_message(), 'error');
    }
  }
}
