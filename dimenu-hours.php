<?php
/**
 * Plugin Name:       dimenu Hours
 * Plugin URI:        https://example.com
 * Description:       Business hours + exceptions + order gating for dimenu digital menu (menu always visible; ordering disabled when closed).
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            dimenu
 * Text Domain:       dimenu-hours
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
  exit;
}

define('DIMENU_HOURS_VERSION', '0.1.0');
define('DIMENU_HOURS_PLUGIN_FILE', __FILE__);
define('DIMENU_HOURS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DIMENU_HOURS_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-activator.php';
require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook(__FILE__, array('Dimenu_Hours_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Dimenu_Hours_Deactivator', 'deactivate'));

function dimenu_hours_run() {
  $plugin = new Dimenu_Hours_Plugin();
  $plugin->run();
}
dimenu_hours_run();
