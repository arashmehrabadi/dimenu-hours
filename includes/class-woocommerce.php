<?php
if (!defined('ABSPATH')) exit;

require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-status.php';
require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-settings.php';

class Dimenu_Hours_WooCommerce {

  public function init() {
    add_filter('woocommerce_add_to_cart_validation', array($this, 'block_add_to_cart'), 10, 3);
    add_action('wp_loaded', array($this, 'maybe_redirect_cart_checkout'), 1);
    add_action('woocommerce_check_cart_items', array($this, 'maybe_block_checkout'), 20);
  }

  private function should_gate(): bool {
    $settings = Dimenu_Hours_Settings::get();
    if (isset($settings['enabled']) && !$settings['enabled']) return false;

    $status = Dimenu_Hours_Status::get_status();
    if (!isset($status['is_open']) || $status['is_open'] !== false) return false;

    $ui = $settings['ui'] ?? array();
    $gateAdd = isset($ui['gate_add_to_cart']) ? (bool)$ui['gate_add_to_cart'] : true;
    $gateChk = isset($ui['gate_checkout']) ? (bool)$ui['gate_checkout'] : true;

    return ($gateAdd || $gateChk);
  }

  private function get_closed_message(): string {
    $settings = Dimenu_Hours_Settings::get();
    $status = Dimenu_Hours_Status::get_status();

    if (!empty($status['human'])) return (string)$status['human'];

    $msgs = $settings['messages'] ?? array();
    return $msgs['closed_now'] ?? 'Closed now.';
  }

  public function block_add_to_cart($passed, $product_id, $quantity) {
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
    if (is_admin()) return;
    if (!function_exists('wc_get_page_id')) return;

    $settings = Dimenu_Hours_Settings::get();
    $ui = $settings['ui'] ?? array();
    $gateChk = isset($ui['gate_checkout']) ? (bool)$ui['gate_checkout'] : true;
    if (!$gateChk) return;

    $status = Dimenu_Hours_Status::get_status();
    if (!isset($status['is_open']) || $status['is_open'] !== false) return;

    $cart_id = wc_get_page_id('cart');
    $checkout_id = wc_get_page_id('checkout');

    $is_cart_page = (function_exists('is_cart') && is_cart()) || ($cart_id > 0 && is_page($cart_id));
    $is_checkout_page = (function_exists('is_checkout') && is_checkout()) || ($checkout_id > 0 && is_page($checkout_id));

    $uri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';
    $uri_l = strtolower($uri);
    $is_checkout_endpoint = (strpos($uri_l, 'order-pay') !== false) || (strpos($uri_l, 'order-received') !== false);

    if ($is_cart_page || $is_checkout_page || $is_checkout_endpoint) {
      if (function_exists('wc_add_notice')) {
        wc_add_notice($this->get_closed_message(), 'error');
      }

      $redirect_url = $settings['ui']['redirect_url'] ?? '';
      $target = !empty($redirect_url) ? $redirect_url : home_url('/?dimenu_closed=1');
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
