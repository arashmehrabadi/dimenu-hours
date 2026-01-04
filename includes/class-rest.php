<?php
if (!defined('ABSPATH')) exit;

require_once DIMENU_HOURS_PLUGIN_DIR . 'includes/class-status.php';

class Dimenu_Hours_Rest {

  public function register_routes() {
    register_rest_route('dimenu/v1', '/status', array(
      'methods'  => WP_REST_Server::READABLE,
      'callback' => array($this, 'get_status'),
      'permission_callback' => '__return_true',
    ));
  }

  public function get_status(WP_REST_Request $request) {
    $status = Dimenu_Hours_Status::get_status();
    return rest_ensure_response($status);
  }
}
