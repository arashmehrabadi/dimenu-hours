<?php
if (!defined('ABSPATH')) exit;

require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/helpers.php';

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
    if (!in_array($tz, timezone_identifiers_list(), true)) {
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

    // weekly schedule
    $out['weekly'] = self::sanitize_weekly($post['weekly'] ?? array());

    // exceptions
    $out['exceptions'] = self::sanitize_exceptions($post['exceptions'] ?? array());

    // ui options
    $ui = $current['ui'] ?? array();
    $ui['redirect_url'] = isset($post['ui']['redirect_url']) ? esc_url_raw($post['ui']['redirect_url']) : ($ui['redirect_url'] ?? '');
    $out['ui'] = $ui;

    if (isset($current['version'])) {
      $out['version'] = $current['version'];
    }

    return $out;
  }

  public static function render_admin_form() {
    if (!current_user_can('manage_options')) return;

    $settings = self::get();

    $enabled = isset($settings['enabled']) ? (bool)$settings['enabled'] : true;
    $manual_closed = isset($settings['manual_closed']) ? (bool)$settings['manual_closed'] : false;
    $timezone = $settings['timezone'] ?? (get_option('timezone_string') ?: 'UTC');

    $messages = $settings['messages'] ?? array(
      'closed_now' => 'الان بسته‌ایم.',
      'open_now' => 'الان باز هستیم.',
      'next_open_prefix' => 'بازگشایی بعدی:',
      'until_prefix' => 'تا',
      'manual_closed' => 'سفارش‌گیری موقتاً غیرفعال است.',
    );

    ?>
    <div class="wrap dimenu-hours-admin">
      <h1><?php echo esc_html__('تنظیمات ساعات کاری dimenu', 'dimenu-hours'); ?></h1>

      <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <div class="notice notice-success is-dismissible">
          <p><?php echo esc_html__('تنظیمات ذخیره شد.', 'dimenu-hours'); ?></p>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <?php wp_nonce_field('dimenu_hours_save_settings', 'dimenu_hours_nonce'); ?>

        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><?php echo esc_html__('فعال‌سازی محدودیت سفارش', 'dimenu-hours'); ?></th>
            <td>
              <label>
                <input type="checkbox" name="enabled" value="1" <?php checked($enabled); ?> />
                <?php echo esc_html__('سفارش‌دهی فقط در ساعات کاری فعال است (منو همیشه نمایش داده می‌شود).', 'dimenu-hours'); ?>
              </label>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php echo esc_html__('تعطیلی دستی', 'dimenu-hours'); ?></th>
            <td>
              <label>
                <input type="checkbox" name="manual_closed" value="1" <?php checked($manual_closed); ?> />
                <?php echo esc_html__('سفارش‌گیری را موقتاً غیرفعال کن (صرف‌نظر از برنامه زمانی).', 'dimenu-hours'); ?>
              </label>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php echo esc_html__('منطقه زمانی', 'dimenu-hours'); ?></th>
            <td>
              <input type="text" class="regular-text" name="timezone" value="<?php echo esc_attr($timezone); ?>" />
              <p class="description"><?php echo esc_html__('مثال: Asia/Tehran — اگر خالی بماند از تنظیمات وردپرس استفاده می‌شود.', 'dimenu-hours'); ?></p>
            </td>
          </tr>
        </table>

        <h2><?php echo esc_html__('متن پیام‌ها', 'dimenu-hours'); ?></h2>

        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><?php echo esc_html__('پیام «الان بسته‌ایم»', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[closed_now]" value="<?php echo esc_attr($messages['closed_now'] ?? ''); ?>" /></td>
          </tr>
          <tr>
            <th scope="row"><?php echo esc_html__('پیام «الان باز هستیم»', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[open_now]" value="<?php echo esc_attr($messages['open_now'] ?? ''); ?>" /></td>
          </tr>
          <tr>
            <th scope="row"><?php echo esc_html__('پیشوند «بازگشایی بعدی»', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[next_open_prefix]" value="<?php echo esc_attr($messages['next_open_prefix'] ?? ''); ?>" /></td>
          </tr>
          <tr>
            <th scope="row"><?php echo esc_html__('پیشوند «تا»', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[until_prefix]" value="<?php echo esc_attr($messages['until_prefix'] ?? ''); ?>" /></td>
          </tr>
          <tr>
            <th scope="row"><?php echo esc_html__('پیام تعطیلی دستی', 'dimenu-hours'); ?></th>
            <td><input type="text" class="regular-text" name="messages[manual_closed]" value="<?php echo esc_attr($messages['manual_closed'] ?? ''); ?>" /></td>
          </tr>
        </table>

        <h2><?php echo esc_html__('استثناها', 'dimenu-hours'); ?></h2>
        <p class="description"><?php echo esc_html__('روزهای خاص را اضافه کنید (تعطیل یا ساعات خاص). تاریخ‌ها شامل ابتدا و انتها هستند.', 'dimenu-hours'); ?></p>
        <table class="widefat fixed dimenu-hours-exceptions">
          <thead>
            <tr>
              <th><?php echo esc_html__('نوع', 'dimenu-hours'); ?></th>
              <th><?php echo esc_html__('تاریخ شروع', 'dimenu-hours'); ?></th>
              <th><?php echo esc_html__('تاریخ پایان', 'dimenu-hours'); ?></th>
              <th><?php echo esc_html__('بازه‌ها (برای ساعات خاص)', 'dimenu-hours'); ?></th>
              <th><?php echo esc_html__('عملیات', 'dimenu-hours'); ?></th>
            </tr>
          </thead>
          <tbody id="dimenu-exceptions-body">
            <?php
              $exceptions = is_array($settings['exceptions'] ?? null) ? $settings['exceptions'] : array();
              if (empty($exceptions)) {
                $exceptions[] = array('type' => 'closed', 'start_date' => '', 'end_date' => '', 'intervals' => array());
              }
              foreach ($exceptions as $idx => $ex):
                $type = isset($ex['type']) && $ex['type'] === 'special_hours' ? 'special_hours' : 'closed';
                $sd = $ex['start_date'] ?? ($ex['date'] ?? '');
                $ed = $ex['end_date'] ?? ($ex['date'] ?? '');
                $intervals = is_array($ex['intervals'] ?? null) ? $ex['intervals'] : array();
            ?>
              <tr class="exception-row">
                <td>
                  <select name="exceptions[<?php echo esc_attr($idx); ?>][type]" class="exception-type">
                    <option value="closed" <?php selected($type, 'closed'); ?>><?php echo esc_html__('تعطیل', 'dimenu-hours'); ?></option>
                    <option value="special_hours" <?php selected($type, 'special_hours'); ?>><?php echo esc_html__('ساعات خاص', 'dimenu-hours'); ?></option>
                  </select>
                </td>
                <td>
                  <input type="date" name="exceptions[<?php echo esc_attr($idx); ?>][start_date]" value="<?php echo esc_attr($sd); ?>" />
                </td>
                <td>
                  <input type="date" name="exceptions[<?php echo esc_attr($idx); ?>][end_date]" value="<?php echo esc_attr($ed); ?>" />
                </td>
                <td>
                  <div class="ex-intervals" data-index="<?php echo esc_attr($idx); ?>">
                    <?php if (empty($intervals)): ?>
                      <div class="interval-row">
                        <input type="time" name="exceptions[<?php echo esc_attr($idx); ?>][intervals][0][start]" value="" />
                        <span class="sep">–</span>
                        <input type="time" name="exceptions[<?php echo esc_attr($idx); ?>][intervals][0][end]" value="" />
                        <button type="button" class="button remove-ex-interval" aria-label="<?php echo esc_attr__('حذف بازه', 'dimenu-hours'); ?>">×</button>
                      </div>
                    <?php else: ?>
                      <?php foreach ($intervals as $iidx => $it): ?>
                        <div class="interval-row">
                          <input type="time" name="exceptions[<?php echo esc_attr($idx); ?>][intervals][<?php echo esc_attr($iidx); ?>][start]" value="<?php echo esc_attr($it['start'] ?? ''); ?>" />
                          <span class="sep">–</span>
                          <input type="time" name="exceptions[<?php echo esc_attr($idx); ?>][intervals][<?php echo esc_attr($iidx); ?>][end]" value="<?php echo esc_attr($it['end'] ?? ''); ?>" />
                          <button type="button" class="button remove-ex-interval" aria-label="<?php echo esc_attr__('حذف بازه', 'dimenu-hours'); ?>">×</button>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                  <button type="button" class="button add-ex-interval" data-ex="<?php echo esc_attr($idx); ?>"><?php echo esc_html__('افزودن بازه', 'dimenu-hours'); ?></button>
                </td>
                <td>
                  <button type="button" class="button remove-exception" aria-label="<?php echo esc_attr__('حذف استثنا', 'dimenu-hours'); ?>">×</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5">
                <button type="button" class="button button-secondary" id="add-exception-row">
                  <?php echo esc_html__('افزودن استثنا', 'dimenu-hours'); ?>
                </button>
              </td>
            </tr>
          </tfoot>
        </table>

        <h2><?php echo esc_html__('مسیر ریدایرکت (سبد/تسویه)', 'dimenu-hours'); ?></h2>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><?php echo esc_html__('نشانی ریدایرکت', 'dimenu-hours'); ?></th>
            <td>
              <?php $redirect_url = $settings['ui']['redirect_url'] ?? ''; ?>
              <input type="url" class="regular-text" name="ui[redirect_url]" value="<?php echo esc_attr($redirect_url); ?>" placeholder="<?php echo esc_attr(home_url('/')); ?>" />
              <p class="description"><?php echo esc_html__('وقتی بسته هستید سبد و تسویه به این آدرس هدایت می‌شوند؛ اگر خالی باشد صفحه اصلی استفاده می‌شود.', 'dimenu-hours'); ?></p>
            </td>
          </tr>
        </table>

        <h2><?php echo esc_html__('برنامه هفتگی', 'dimenu-hours'); ?></h2>
        <p class="description"><?php echo esc_html__('ساعات هر روز را وارد کنید. برای افزودن بازه روی + کلیک کنید. روز بدون تیک و بدون بازه یعنی پاک شدن آن روز.', 'dimenu-hours'); ?></p>
        <table class="widefat fixed dimenu-hours-weekly">
          <thead>
            <tr>
              <th><?php echo esc_html__('روز', 'dimenu-hours'); ?></th>
              <th><?php echo esc_html__('تعطیل', 'dimenu-hours'); ?></th>
              <th><?php echo esc_html__('بازه‌ها', 'dimenu-hours'); ?></th>
              <th><?php echo esc_html__('عملیات', 'dimenu-hours'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php for ($d = 0; $d < 7; $d++): ?>
              <?php
                $day = self::find_day($settings['weekly'] ?? array(), $d);
                $is_closed = !empty($day['is_closed']);
                $intervals = is_array($day['intervals'] ?? null) ? $day['intervals'] : array();
              ?>
              <tr data-day="<?php echo esc_attr($d); ?>">
                <td class="day-label">
                  <?php echo esc_html(self::day_label($d)); ?>
                  <input type="hidden" name="weekly[<?php echo esc_attr($d); ?>][day]" value="<?php echo esc_attr($d); ?>" />
                </td>
                <td>
                  <label>
                    <input type="checkbox" name="weekly[<?php echo esc_attr($d); ?>][is_closed]" value="1" <?php checked($is_closed); ?> />
                  </label>
                </td>
                <td>
                  <div class="intervals" data-day="<?php echo esc_attr($d); ?>">
                    <?php if (empty($intervals)): ?>
                      <div class="interval-row">
                        <input type="time" name="weekly[<?php echo esc_attr($d); ?>][intervals][0][start]" value="" />
                        <span class="sep">–</span>
                        <input type="time" name="weekly[<?php echo esc_attr($d); ?>][intervals][0][end]" value="" />
                        <button type="button" class="button remove-interval" aria-label="<?php echo esc_attr__('Remove', 'dimenu-hours'); ?>">×</button>
                      </div>
                    <?php else: ?>
                      <?php foreach ($intervals as $idx => $it): ?>
                        <div class="interval-row">
                          <input type="time" name="weekly[<?php echo esc_attr($d); ?>][intervals][<?php echo esc_attr($idx); ?>][start]" value="<?php echo esc_attr($it['start'] ?? ''); ?>" />
                          <span class="sep">–</span>
                          <input type="time" name="weekly[<?php echo esc_attr($d); ?>][intervals][<?php echo esc_attr($idx); ?>][end]" value="<?php echo esc_attr($it['end'] ?? ''); ?>" />
                          <button type="button" class="button remove-interval" aria-label="<?php echo esc_attr__('Remove', 'dimenu-hours'); ?>">×</button>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <button type="button" class="button add-interval" data-day="<?php echo esc_attr($d); ?>">
                    <?php echo esc_html__('افزودن بازه', 'dimenu-hours'); ?>
                  </button>
                </td>
              </tr>
            <?php endfor; ?>
          </tbody>
        </table>

        <?php submit_button(__('ذخیره تنظیمات', 'dimenu-hours')); ?>
      </form>
    </div>
    <?php
  }

  private static function day_label(int $d): string {
    $labels = array(
      __('یکشنبه', 'dimenu-hours'),
      __('دوشنبه', 'dimenu-hours'),
      __('سه‌شنبه', 'dimenu-hours'),
      __('چهارشنبه', 'dimenu-hours'),
      __('پنجشنبه', 'dimenu-hours'),
      __('جمعه', 'dimenu-hours'),
      __('شنبه', 'dimenu-hours'),
    );
    return $labels[$d] ?? (string)$d;
  }

  private static function find_day(array $weekly, int $dayIndex): array {
    foreach ($weekly as $d) {
      if (is_array($d) && isset($d['day']) && (int)$d['day'] === $dayIndex) return $d;
    }
    return array('day' => $dayIndex, 'is_closed' => false, 'intervals' => array());
  }

  private static function sanitize_weekly($raw): array {
    $out = array();

    for ($d = 0; $d < 7; $d++) {
      $row = isset($raw[$d]) && is_array($raw[$d]) ? $raw[$d] : array();
      $is_closed = !empty($row['is_closed']);

      $item = array(
        'day' => $d,
        'is_closed' => $is_closed,
        'intervals' => array(),
      );

      if ($is_closed) {
        $out[] = $item;
        continue;
      }

      $intervals = isset($row['intervals']) && is_array($row['intervals']) ? $row['intervals'] : array();
      $clean = array();
      foreach ($intervals as $it) {
        if (!isset($it['start'], $it['end'])) continue;
        $s = trim((string)$it['start']);
        $e = trim((string)$it['end']);
        $sm = Dimenu_Hours_Helpers::hhmm_to_min($s);
        $em = Dimenu_Hours_Helpers::hhmm_to_min($e);
        if ($sm === null || $em === null) continue;
        $clean[] = array('start' => $s, 'end' => $e, 'start_min' => $sm, 'end_min' => $em);
      }

      if (!empty($clean)) {
        usort($clean, function($a, $b) {
          return $a['start_min'] <=> $b['start_min'];
        });
        $validated = array();
        $lastEnd = null;
        foreach ($clean as $c) {
          $sameDay = ($c['end_min'] >= $c['start_min']);
          if ($sameDay) {
            if ($lastEnd !== null && $c['start_min'] < $lastEnd) {
              continue;
            }
            $lastEnd = $c['end_min'];
          }
          $validated[] = array('start' => $c['start'], 'end' => $c['end']);
        }
        $item['intervals'] = $validated;
      }

      $out[] = $item;
    }

    return $out;
  }

  private static function sanitize_exceptions($raw): array {
    if (!is_array($raw)) return array();

    $out = array();
    foreach ($raw as $ex) {
      if (!is_array($ex)) continue;
      $type = isset($ex['type']) && $ex['type'] === 'special_hours' ? 'special_hours' : 'closed';

      $sd = self::valid_date($ex['start_date'] ?? ($ex['date'] ?? ''));
      $ed = self::valid_date($ex['end_date'] ?? ($ex['date'] ?? ''));
      if ($sd === '' && $ed !== '') $sd = $ed;
      if ($ed === '' && $sd !== '') $ed = $sd;
      if ($sd === '' || $ed === '') continue;
      if ($sd > $ed) {
        $tmp = $sd;
        $sd = $ed;
        $ed = $tmp;
      }

      $intervals = array();
      if ($type === 'special_hours') {
        $intervals_raw = isset($ex['intervals']) && is_array($ex['intervals']) ? $ex['intervals'] : array();
        $norm = Dimenu_Hours_Helpers::normalize_intervals($intervals_raw);
        foreach ($norm as $n) {
          $intervals[] = array('start' => $n['start'], 'end' => $n['end']);
        }
        if (empty($intervals)) continue;
      }

      $out[] = array(
        'type' => $type,
        'start_date' => $sd,
        'end_date' => $ed,
        'intervals' => $intervals,
      );
    }

    return $out;
  }

  private static function valid_date($val): string {
    $val = is_string($val) ? trim($val) : '';
    if ($val === '') return '';
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) === 1 ? $val : '';
  }
}
