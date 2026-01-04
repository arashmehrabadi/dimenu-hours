<?php
if (!defined('ABSPATH')) exit;

class Dimenu_Hours_Helpers {

  // "HH:MM" -> minutes from 00:00
  public static function hhmm_to_min(string $hhmm): ?int {
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $hhmm, $m)) {
      return null;
    }
    return ((int)$m[1]) * 60 + (int)$m[2];
  }

  public static function min_to_hhmm(int $min): string {
    $min = $min % 1440;
    if ($min < 0) $min += 1440;
    return sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
  }

  public static function get_timezone_id(string $tz): string {
    if ($tz && in_array($tz, timezone_identifiers_list(), true)) {
      return $tz;
    }
    $wp = get_option('timezone_string');
    if ($wp && in_array($wp, timezone_identifiers_list(), true)) {
      return $wp;
    }
    return 'UTC';
  }

  public static function now_in_tz(string $tz): DateTime {
    return new DateTime('now', new DateTimeZone(self::get_timezone_id($tz)));
  }

  public static function wp_weekday_index(DateTime $dt): int {
    return (int) $dt->format('w'); // 0=Sunday .. 6=Saturday
  }

  public static function normalize_intervals($intervals): array {
    if (!is_array($intervals)) return array();

    $out = array();
    foreach ($intervals as $it) {
      if (!isset($it['start'], $it['end'])) continue;

      $sm = self::hhmm_to_min($it['start']);
      $em = self::hhmm_to_min($it['end']);
      if ($sm === null || $em === null) continue;

      $out[] = array(
        'start' => $it['start'],
        'end' => $it['end'],
        'start_min' => $sm,
        'end_min' => $em,
      );
    }
    return $out;
  }
}
