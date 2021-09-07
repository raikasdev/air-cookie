<?php
/**
 * Plugin Name: Air Cookie
 * Version: 0.1.0
 *
 * @Author: Timi Wahalahti
 * @Date:   2021-08-10 10:49:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-07 17:01:27
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Get current version of plugin. Version is semver without extra marks, so it can be used as a int.
 *
 * @since 0.1.0
 * @return integer current version of plugin
 */
function get_plugin_version() {
  return 010;
} // end plugin_version

function get_databse_version() {
  return 20210824;
} // end get_databse_version

function get_script_version() {
  return '2.5.0';
} // end get_script_version

/**
* Require helpers for this plugin.
*
* @since 0.1.0
*/
require 'plugin-helpers.php';
require plugin_base_path() . '/settings.php';

require plugin_base_path(). '/strings.php';
add_action( 'init', __NAMESPACE__ . '\register_strings' );

require plugin_base_path() . '/script-injection.php';
add_action( 'wp_head', __NAMESPACE__ . '\inject_js' );

require plugin_base_path() . '/rest-api.php';
add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_endpoint' );

if ( is_admin() ) {
  require plugin_base_path(). '/database.php';
  add_action( 'admin_init', __NAMESPACE__ . '\maybe_init_database' );
}

/**
 * # TODO
 * Github updater
 *
 * @since 0.1.0
 */

function maybe_set_identification_cookie() {
  if ( isset( $_COOKIE['air_cookie_visitor'] ) ) {
    return false;
  }

  $visitor_uuid = wp_generate_uuid4();
  $expiration = YEAR_IN_SECONDS * 10;

  setcookie( 'air_cookie_visitor', $visitor_uuid, time() + $expiration, '/' );

  return $visitor_uuid;
} // end maybe_set_identification_cookie

/**
* Plugin activation hook to save current version for reference in what version activation happened.
* Check if deactivation without version option is apparent, then do not save current version for
* maintaining backwards compatibility.
*
* @since 1.6.0
*/
register_activation_hook( __FILE__, __NAMESPACE__ . '\plugin_activate' );
function plugin_activate() {
  $deactivated_without = get_option( 'air_cookie_deactivated_without_version' );

  if ( 'true' !== $deactivated_without ) {
    update_option( 'air_cookie_activated_at_version', plugin_version() );
  }
} // end plugin_activate

/**
* Maybe add option if activated version is not yet saved.
* Helps to maintain backwards compatibility.
*
* @since 1.6.0
*/
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\plugin_deactivate' );
add_action( 'admin_init', __NAMESPACE__ . '\plugin_deactivate' );
function plugin_deactivate() {
  $activated_version = get_option( 'air_cookie_activated_at_version' );

  if ( ! $activated_version ) {
    update_option( 'air_cookie_deactivated_without_version', 'true', false );
  }
} // end plugin_deactivate

add_action( 'air_cookie_js_necessary', function() {
  ob_start(); ?>
    console.log( 'necessary' );
  <?php echo ob_get_clean();
} );

add_action( 'air_cookie_js_analytics', function() {
  ob_start(); ?>
    console.log( 'analytics' );
  <?php echo ob_get_clean();
} );

add_action( 'wp_head', function() { ?>
  <script type="text/javascript">
    document.addEventListener( 'air_cookie', function( event ) {
      console.log( 'global event  ' + event.category );
    } );

    document.addEventListener( 'air_cookie_necessary', function( event ) {
      console.log( 'category event necessary' );
    } );
  </script>
<?php } );
