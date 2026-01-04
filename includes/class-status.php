<?php
if (!defined('ABSPATH')) exit;

require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-settings.php';
require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/helpers.php';

class Dimenu_Hours_Status {

  public static function get_status(): array {
    $settings = Dimenu_Hours_Settings::get();

    // feature disabled => do not gate ordering
    if (isset($settings['enabled']) && !$settings['enabled']) {
      return array(
        'is_open' => true,
        'reason' => 'disabled',
        'opens_at' => '',
        'closes_at' => '',
        'next_open_at' => '',
        'timezone' => $settings['timezone'] ?? 'UTC',
        'now' => '',
        'human' => 'Feature disabled',
      );
    }

    $tz = $settings['timezone'] ?? 'UTC';
    $now = Dimenu_Hours_Helpers::now_in_tz($tz);

    // manual override
    if (!empty($settings['manual_closed'])) {
      $msg = $settings['messages']['manual_closed'] ?? 'Temporarily closed.';
      return array(
        'is_open' => false,
        'reason' => 'manual',
        'opens_at' => '',
        'closes_at' => '',
        'next_open_at' => '',
        'timezone' => $tz,
        'now' => $now->format(DateTime::ATOM),
        'human' => $msg,
      );
    }

    $weekly = $settings['weekly'] ?? array();
    $result = self::status_from_weekly($weekly, $now, $tz, $settings['messages'] ?? array());

    return $result;
  }

  private static function status_from_weekly(array $weekly, DateTime $now, string $tz, array $messages): array {
    $w = Dimenu_Hours_Helpers::wp_weekday_index($now);
    $today = self::find_day($weekly, $w);
    $yesterday = self::find_day($weekly, ($w + 6) % 7);

    $nowMin = (int)$now->format('H') * 60 + (int)$now->format('i');

    // Check overnight intervals from yesterday that extend into today
    $yIntervals = Dimenu_Hours_Helpers::normalize_intervals($yesterday['intervals'] ?? array());
    foreach ($yIntervals as $it) {
      if ($it['end_min'] < $it['start_min']) {
        // overnight: yesterday start -> today end
        // open today if nowMin < end
        if ($nowMin < $it['end_min']) {
          $openStart = clone $now;
          $openStart->modify('-1 day');
          $openStart->setTime((int)floor($it['start_min']/60), $it['start_min']%60, 0);

          $closeEnd = clone $now;
          $closeEnd->setTime((int)floor($it['end_min']/60), $it['end_min']%60, 0);

          return self::open_payload($now, $tz, $openStart, $closeEnd, $messages);
        }
      }
    }

    // If today is marked closed => still might have intervals (but we treat as closed)
    if (!empty($today['is_closed'])) {
      return self::closed_payload($now, $tz, self::find_next_open($weekly, $now), $messages);
    }

    // Check today's intervals (including overnight that end tomorrow)
    $tIntervals = Dimenu_Hours_Helpers::normalize_intervals($today['intervals'] ?? array());
    foreach ($tIntervals as $it) {
      if ($it['end_min'] >= $it['start_min']) {
        // same-day interval
        if ($nowMin >= $it['start_min'] && $nowMin < $it['end_min']) {
          $openStart = clone $now;
          $openStart->setTime((int)floor($it['start_min']/60), $it['start_min']%60, 0);

          $closeEnd = clone $now;
          $closeEnd->setTime((int)floor($it['end_min']/60), $it['end_min']%60, 0);

          return self::open_payload($now, $tz, $openStart, $closeEnd, $messages);
        }
      } else {
        // overnight interval: today start -> tomorrow end
        if ($nowMin >= $it['start_min']) {
          $openStart = clone $now;
          $openStart->setTime((int)floor($it['start_min']/60), $it['start_min']%60, 0);

          $closeEnd = clone $now;
          $closeEnd->modify('+1 day');
          $closeEnd->setTime((int)floor($it['end_min']/60), $it['end_min']%60, 0);

          return self::open_payload($now, $tz, $openStart, $closeEnd, $messages);
        }
        // If now is after midnight part, handled by yesterday block above
      }
    }

    // Not open now => find next open
    $next = self::find_next_open($weekly, $now);
    return self::closed_payload($now, $tz, $next, $messages);
  }

  private static function find_day(array $weekly, int $dayIndex): array {
    foreach ($weekly as $d) {
      if (is_array($d) && isset($d['day']) && (int)$d['day'] === $dayIndex) return $d;
    }
    // fallback
    return array('day' => $dayIndex, 'is_closed' => false, 'intervals' => array());
  }

  private static function find_next_open(array $weekly, DateTime $now): string {
    // Search from "now" up to 7 days ahead
    $base = clone $now;
    $baseMin = (int)$base->format('H') * 60 + (int)$base->format('i');

    for ($i = 0; $i < 8; $i++) {
      $dt = clone $base;
      if ($i > 0) $dt->modify("+$i day");
      $w = (int)$dt->format('w');
      $day = self::find_day($weekly, $w);

      if (!empty($day['is_closed'])) continue;

      $intervals = Dimenu_Hours_Helpers::normalize_intervals($day['intervals'] ?? array());
      if (empty($intervals)) continue;

      // For today, find first interval start after nowMin (or if overnight and we're before end? already handled)
      if ($i === 0) {
        // choose the earliest start >= now
        $starts = array();
        foreach ($intervals as $it) {
          $starts[] = $it['start_min'];
        }
        sort($starts);
        foreach ($starts as $sm) {
          if ($sm > $baseMin) {
            $dt->setTime((int)floor($sm/60), $sm%60, 0);
            return $dt->format(DateTime::ATOM);
          }
        }
      } else {
        // next days => first start
        $sm = null;
        foreach ($intervals as $it) {
          if ($sm === null || $it['start_min'] < $sm) $sm = $it['start_min'];
        }
        if ($sm !== null) {
          $dt->setTime((int)floor($sm/60), $sm%60, 0);
          return $dt->format(DateTime::ATOM);
        }
      }
    }

    return '';
  }

  private static function open_payload(DateTime $now, string $tz, DateTime $opensAt, DateTime $closesAt, array $messages): array {
    $openMsg = $messages['open_now'] ?? 'Open now.';
    $until = $messages['until_prefix'] ?? 'until';

    $human = trim($openMsg . ' ' . $until . ' ' . $closesAt->format('H:i'));

    return array(
      'is_open' => true,
      'reason' => 'schedule',
      'opens_at' => $opensAt->format(DateTime::ATOM),
      'closes_at' => $closesAt->format(DateTime::ATOM),
      'next_open_at' => '',
      'timezone' => $tz,
      'now' => $now->format(DateTime::ATOM),
      'human' => $human,
    );
  }

  private static function closed_payload(DateTime $now, string $tz, string $nextOpenIso, array $messages): array {
    $closedMsg = $messages['closed_now'] ?? 'Closed now.';
    $prefix = $messages['next_open_prefix'] ?? 'Next open:';

    $human = $closedMsg;
    if (!empty($nextOpenIso)) {
      try {
        $next = new DateTime($nextOpenIso);
        $human = trim($closedMsg . ' ' . $prefix . ' ' . $next->format('H:i'));
      } catch (Exception $e) {
        // ignore
      }
    }

    return array(
      'is_open' => false,
      'reason' => 'schedule',
      'opens_at' => '',
      'closes_at' => '',
      'next_open_at' => $nextOpenIso,
      'timezone' => $tz,
      'now' => $now->format(DateTime::ATOM),
      'human' => $human,
    );
  }
}
