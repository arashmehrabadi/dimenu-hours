<?php
if (!defined('ABSPATH')) exit;

require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-settings.php';
require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-rest.php';
require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-woocommerce.php';



class Dimenu_Hours_Plugin {

  public function run() {
    add_action('init', array($this, 'load_textdomain'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
    add_action('admin_menu', array($this, 'register_admin_menu'));
    add_action('admin_init', array($this, 'handle_settings_post'));
    add_action('rest_api_init', array($this, 'register_rest_routes'));
    add_action('plugins_loaded', array($this, 'maybe_init_woocommerce_gating'), 20);


  }

  public function load_textdomain() {
    load_plugin_textdomain(
      'dimenu-hours',
      false,
      dirname(plugin_basename(DIMENU_HOURS_PLUGIN_FILE)) . '/languages'
    );
  }

  public function enqueue_admin_assets($hook) {
    // Only load on our page
    if ($hook !== 'toplevel_page_dimenu-hours') return;

    wp_enqueue_style(
      'dimenu-hours-admin',
      DIMENU_HOURS_PLUGIN_URL . 'assets/admin/admin.css',
      array(),
      DIMENU_HOURS_VERSION
    );
    wp_enqueue_script(
      'dimenu-hours-admin',
      DIMENU_HOURS_PLUGIN_URL . 'assets/admin/admin.js',
      array('jquery'),
      DIMENU_HOURS_VERSION,
      true
    );
  }

  public function enqueue_public_assets() {
  wp_enqueue_style(
    'dimenu-hours-public',
    DIMENU_HOURS_PLUGIN_URL . 'assets/public/public.css',
    array(),
    DIMENU_HOURS_VERSION
  );

  wp_enqueue_script(
    'dimenu-hours-public',
    DIMENU_HOURS_PLUGIN_URL . 'assets/public/public.js',
    array(),
    DIMENU_HOURS_VERSION,
    true
  );

  wp_localize_script('dimenu-hours-public', 'DIMENU_HOURS', array(
    'restUrl' => esc_url_raw(rest_url('dimenu/v1/status')),
    'showBanner' => true,
    'bannerSelector' => '',
    'gateSelectors' => array(),
  ));
}


  public function register_admin_menu() {
    add_menu_page(
      __('dimenu Hours', 'dimenu-hours'),
      __('dimenu Hours', 'dimenu-hours'),
      'manage_options',
      'dimenu-hours',
      array($this, 'render_admin_page'),
      'dashicons-clock',
      58
    );
  }

  public function render_admin_page() {
    Dimenu_Hours_Settings::render_admin_form();
  }

  public function handle_settings_post() {
    if (!is_admin()) return;
    if (!current_user_can('manage_options')) return;

    // only handle on our page
    if (!isset($_GET['page']) || $_GET['page'] !== 'dimenu-hours') return;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    if (!isset($_POST['dimenu_hours_nonce']) || !wp_verify_nonce($_POST['dimenu_hours_nonce'], 'dimenu_hours_save_settings')) {
      wp_die(__('Security check failed.', 'dimenu-hours'));
    }

    $current = Dimenu_Hours_Settings::get();
    $sanitized = Dimenu_Hours_Settings::sanitize_post($_POST, $current);
    Dimenu_Hours_Settings::update($sanitized);

    wp_safe_redirect(admin_url('admin.php?page=dimenu-hours&updated=1'));
    exit;
  }
  public function register_rest_routes() {
  $rest = new Dimenu_Hours_Rest();
  $rest->register_routes();
}
public function maybe_init_woocommerce_gating() {
  // Only if WooCommerce is active
  if (!class_exists('WooCommerce')) return;

  $wc = new Dimenu_Hours_WooCommerce();
  $wc->init();
}

}
