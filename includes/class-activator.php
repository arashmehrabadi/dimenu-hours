<?php
if (!defined('ABSPATH')) {
  exit;
}

class Dimenu_Hours_Activator {

  public static function activate() {
    // Create defaults if not present
    $option_key = 'dimenu_hours_settings';
    $existing = get_option($option_key, null);

    if ($existing === null) {
      $defaults = self::default_settings();
      add_option($option_key, $defaults, '', false);
      return;
    }

    // If option exists but missing keys, patch minimal keys
    $patched = self::patch_settings($existing);
    update_option($option_key, $patched, false);
  }

  public static function default_settings() {
    $tz = get_option('timezone_string');
    if (empty($tz)) {
      $tz = 'UTC';
    }

    return array(
      'version' => defined('DIMENU_HOURS_VERSION') ? DIMENU_HOURS_VERSION : '0.1.0',

      // Global toggle to enable/disable the whole feature.
      'enabled' => true,

      // Manual override: if on => always closed (ordering disabled) regardless of schedule.
      'manual_closed' => false,
      'manual_closed_until' => '', // ISO string optional (future use)

      // Timezone used for calculations
      'timezone' => $tz,

      // Weekly schedule: 0=Sunday .. 6=Saturday (WP default)
      'weekly' => array(
        array('day' => 0, 'is_closed' => false, 'intervals' => array(array('start' => '09:00', 'end' => '21:00'))),
        array('day' => 1, 'is_closed' => false, 'intervals' => array(array('start' => '09:00', 'end' => '21:00'))),
        array('day' => 2, 'is_closed' => false, 'intervals' => array(array('start' => '09:00', 'end' => '21:00'))),
        array('day' => 3, 'is_closed' => false, 'intervals' => array(array('start' => '09:00', 'end' => '21:00'))),
        array('day' => 4, 'is_closed' => false, 'intervals' => array(array('start' => '09:00', 'end' => '21:00'))),
        array('day' => 5, 'is_closed' => false, 'intervals' => array(array('start' => '09:00', 'end' => '21:00'))),
        array('day' => 6, 'is_closed' => false, 'intervals' => array(array('start' => '09:00', 'end' => '21:00'))),
      ),

      // Exceptions: see later implementation
      'exceptions' => array(),

      // User-editable messages
      'messages' => array(
        'closed_now' => 'الان بسته‌ایم.',
        'open_now' => 'الان باز هستیم.',
        'next_open_prefix' => 'سفارش‌گیری از',
        'until_prefix' => 'تا',
        'manual_closed' => 'فعلاً پذیرش سفارش غیرفعال است.',
      ),

      // UI behavior (MVP flags)
      'ui' => array(
        'gate_add_to_cart' => true,
        'gate_checkout' => true,
        'show_banner' => true,
      ),
    );
  }

  private static function patch_settings($settings) {
    if (!is_array($settings)) {
      $settings = array();
    }

    $defaults = self::default_settings();

    if (empty($settings['version'])) {
      $settings['version'] = $defaults['version'];
    }
    if (!isset($settings['enabled'])) {
      $settings['enabled'] = $defaults['enabled'];
    }
    if (!isset($settings['manual_closed'])) {
      $settings['manual_closed'] = $defaults['manual_closed'];
    }
    if (!isset($settings['manual_closed_until'])) {
      $settings['manual_closed_until'] = $defaults['manual_closed_until'];
    }
    if (!isset($settings['timezone'])) {
      $settings['timezone'] = $defaults['timezone'];
    }
    if (!isset($settings['weekly']) || !is_array($settings['weekly'])) {
      $settings['weekly'] = $defaults['weekly'];
    }
    if (!isset($settings['exceptions']) || !is_array($settings['exceptions'])) {
      $settings['exceptions'] = $defaults['exceptions'];
    }
    if (!isset($settings['messages']) || !is_array($settings['messages'])) {
      $settings['messages'] = $defaults['messages'];
    }
    if (!isset($settings['ui']) || !is_array($settings['ui'])) {
      $settings['ui'] = $defaults['ui'];
    }

    return $settings;
  }
}
