<?php
if (!defined('ABSPATH')) exit;

require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/helpers.php';

/**
 * Helpers for normalizing and evaluating exception rules.
 *
 * Supported entry shape (array):
 * - type: 'closed' | 'special_hours'
 * - date: 'YYYY-MM-DD' (single day) OR start_date/end_date as range (inclusive)
 * - intervals: for special_hours, array of {start: 'HH:MM', end: 'HH:MM'}
 */
class Dimenu_Hours_Exceptions {

  public static function normalize($raw): array {
    if (!is_array($raw)) return array();

    $out = array();
    foreach ($raw as $item) {
      if (!is_array($item)) continue;

      $type = isset($item['type']) && $item['type'] === 'special_hours' ? 'special_hours' : 'closed';

      $date = self::valid_date($item['date'] ?? '');
      $start = self::valid_date($item['start_date'] ?? '');
      $end = self::valid_date($item['end_date'] ?? '');

      if ($date !== '') {
        $start = $date;
        $end = $date;
      }

      if ($start === '' && $end !== '') $start = $end;
      if ($end === '' && $start !== '') $end = $start;

      if ($start === '' || $end === '') continue;

      $norm = array(
        'type' => $type,
        'start_date' => $start,
        'end_date' => $end,
      );

      if ($type === 'special_hours' && isset($item['intervals'])) {
        $norm['intervals'] = Dimenu_Hours_Helpers::normalize_intervals($item['intervals']);
      } else {
        $norm['intervals'] = array();
      }

      $out[] = $norm;
    }

    return $out;
  }

  public static function get_for_date(array $normalized, DateTime $dt): ?array {
    $target = $dt->format('Y-m-d');

    $matched = array();
    foreach ($normalized as $ex) {
      if (!isset($ex['start_date'], $ex['end_date'])) continue;
      if ($target < $ex['start_date'] || $target > $ex['end_date']) continue;
      $matched[] = $ex;
    }

    if (empty($matched)) return null;

    // Priority: closed first, then special_hours (first match wins inside same type)
    foreach ($matched as $ex) {
      if ($ex['type'] === 'closed') return $ex;
    }
    foreach ($matched as $ex) {
      if ($ex['type'] === 'special_hours') return $ex;
    }

    return $matched[0];
  }

  private static function valid_date($val): string {
    $val = is_string($val) ? trim($val) : '';
    if ($val === '') return '';

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) !== 1) return '';
    return $val;
  }
}
