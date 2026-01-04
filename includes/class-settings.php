<?php
if (!defined('ABSPATH')) exit;

class Dimenu_Hours_Settings {
  const OPTION_KEY = 'dimenu_hours_settings';

  public static function get(): array {
    $v = get_option(self::OPTION_KEY, array());
    return is_array($v) ? $v : array();
  }

  public static function update(array $new): array {
    $current = self::get();
    $merged  = array_merge($current, $new);
    update_option(self::OPTION_KEY, $merged, false);
    return $merged;
  }

  public static function sanitize_post(array $post, array $current): array {
    $out = array();

    // enabled
    $out['enabled'] = isset($post['enabled']) ? (bool) $post['enabled'] : false;

    // manual override
    $out['manual_closed'] = isset($post['manual_closed']) ? (bool) $post['manual_closed'] : false;

    // timezone
    $tz = isset($post['timezone']) ? sanitize_text_field($post['timezone']) : '';
    $tz = trim($tz);
    if ($tz === '') {
      $tz = get_option('timezone_string');
      if (empty($tz)) $tz = 'UTC';
    }
    // validate timezone
    if (!in_array($tz, timezone_identifiers_list(), true)) {
      // fallback to current or WP
      $fallback = $current['timezone'] ?? get_option('timezone_string');
      $tz = (!empty($fallback) && in_array($fallback, timezone_identifiers_list(), true)) ? $fallback : 'UTC';
    }
    $out['timezone'] = $tz;

    // messages (editable)
    $msg = $current['messages'] ?? array();
    $msg_fields = array('closed_now','open_now','next_open_prefix','until_prefix','manual_closed');
    foreach ($msg_fields as $k) {
      if (isset($post['messages'][$k])) {
        $msg[$k] = sanitize_text_field($post['messages'][$k]);
      }
    }
    $out['messages'] = $msg;

    // keep version if exists
    if (isset($current['version'])) {
      $out['version'] = $current['version'];
    }

    return $out;
  }

  public static function render_admin_form() {
    if (!current_user_can('manage_options')) return;

    $settings = self::get();

    // defaults if missing
    $enabled = isset($settings['enabled']) ? (bool)$settings['enabled'] : true;
    $manual_closed = isset($settings['manual_closed']) ? (bool)$settings['manual_closed'] : false;
    $timezone = $settings['timezone'] ?? (get_option('timezone_string') ?: 'UTC');

    $messages = $settings['messages'] ?? array(
      'closed_now' => 'الان بسته‌ایم.',
      'open_now' => 'الان باز هستیم.',
      'next_open_prefix' => 'سفارش‌گیری از',
      'until_prefix' => 'تا',
      'manual_closed' => 'فعلاً پذیرش سفارش غیرفعال است.',
    );

    ?>
    <div class="wrap">
      <h1><?php echo esc_html__('dimenu Hours — Settings', 'dimenu-hours'); ?></h1>

      <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <div class="notice notice-success is-dismissible">
          <p><?php echo esc_html__('Settings saved.', 'dimenu-hours'); ?></p>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <?php wp_nonce_field('dimenu_hours_save_settings', 'dimenu_hours_nonce'); ?>

        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><?php echo esc_html__('Enable feature', 'dimenu-hours'); ?></th>
            <td>
              <label>
                <input type="checkbox" name="enabled" value="1" <?php checked($enabled); ?> />
                <?php echo esc_html__('Ordering is gated by business hours (menu always visible).', 'dimenu-hours'); ?>
              </label>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php echo esc_html__('Manual override', 'dimenu-hours'); ?></th>
            <td>
              <label>
                <input type="checkbox" name="manual_closed" value="1" <?php checked($manual_closed); ?> />
                <?php echo esc_html__('Temporarily disable ordering (force closed).', 'dimenu-hours'); ?>
              </label>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php echo esc_html__('Timezone', 'dimenu-hours'); ?></th>
            <td>
              <input type="text" class="regular-text" name="timezone" value="<?php echo esc_attr($timezone); ?>" />
              <p class="description"><?php echo esc_html__('Example: Asia/Tehran — leave empty to use WordPress timezone.', 'dimenu-hours'); ?></p>
            </td>
          </tr>
        </table>

        <h2><?php echo esc_html__('Messages (editable)', 'dimenu-hours'); ?></h2>

        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><?php echo esc_html__('Closed now', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[closed_now]" value="<?php echo esc_attr($messages['closed_now'] ?? ''); ?>" /></td>
          </tr>
          <tr>
            <th scope="row"><?php echo esc_html__('Open now', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[open_now]" value="<?php echo esc_attr($messages['open_now'] ?? ''); ?>" /></td>
          </tr>
          <tr>
            <th scope="row"><?php echo esc_html__('Next open prefix', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[next_open_prefix]" value="<?php echo esc_attr($messages['next_open_prefix'] ?? ''); ?>" /></td>
          </tr>
          <tr>
            <th scope="row"><?php echo esc_html__('Until prefix', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[until_prefix]" value="<?php echo esc_attr($messages['until_prefix'] ?? ''); ?>" /></td>
          </tr>
          <tr>
            <th scope="row"><?php echo esc_html__('Manual closed message', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[manual_closed]" value="<?php echo esc_attr($messages['manual_closed'] ?? ''); ?>" /></td>
          </tr>
        </table>

        <?php submit_button(__('Save settings', 'dimenu-hours')); ?>
      </form>
    </div>
    <?php
  }
}
