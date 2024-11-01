<?php
/*
  Plugin Name: TackThis! Retail Widget
  Plugin URI: http://www.tackthis.com
  Description: This plugin enables you to change your Wordpress into a full-fledge online shop using the highly popular TackThis! Retail Widget. This version comes with Chinese Language support.
  Version: 1.3.8
  Author: PayWhere
  Author URI: http://www.paywhere.com
 */

/*
  TackThis! Retail Widget (Wordpress Plugin)
  Copyright (C) 2011-2012 PayWhere Pte Ltd
  Contact me at developers@paywhere.com

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('DB_NAME')) {
    header('HTTP/1.0 403 Forbidden');
    die;
}

define('WPTACKTHIS_VERSION', '1.3.8');

if (!defined('WPTACKTHIS_PATH')) {
    define('WPTACKTHIS_PATH', plugin_dir_path(__FILE__));
}
if (!defined('WPTACKTHIS_BASENAME')) {
    define('WPTACKTHIS_BASENAME', plugin_basename(__FILE__));
}

if( !defined('WP_CONTENT_DIR') )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

if( !defined('WP_CONTENT_CACHE_DIR') )
    define( 'WP_CONTENT_CACHE_DIR', WP_CONTENT_DIR . '/cache_no_delete' );

if( !defined('WP_CONTENT_CACHE_TACKTHIS_DIR') )
    define( 'WP_CONTENT_CACHE_TACKTHIS_DIR', WP_CONTENT_CACHE_DIR . '/tackthis' );

if (!file_exists(WP_CONTENT_CACHE_TACKTHIS_DIR)) {
    mkdir(WP_CONTENT_CACHE_TACKTHIS_DIR, 0777, true);
}

define('SHOP_CANNOT_USE_SEARCH', 'Your shop is not configured to use this search.');
define('SHOP_CANNOT_USE_CART', 'Your shop is not configured to use this cart.');
define('ITEMS_IN_CART', 'items in Cart');
define('VIEW_CART', 'view cart');

// TackThis WP Options
define('WP_TACKTHIS_OPTIONS_NAME', 'wptackthis_options');
define('WP_TACKTHIS_OPTIONS_SETTINGS_GROUP_NAME', 'wptackthis-options-group');

if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1') {
    define('WP_TACKTHIS_ENVIRONMENT', 'LOCAL');
    define('WP_TACKTHIS_PRODUCTION_HIDDEN_STYLES', '');
} else {
    define('WP_TACKTHIS_ENVIRONMENT', 'LIVE');
    define('WP_TACKTHIS_PRODUCTION_HIDDEN_STYLES', 'display:none;');
}

/**
 * API Endpoints
 */
$options = get_site_option(WP_TACKTHIS_OPTIONS_NAME);
if ($options && $options['api_mode'] === 'beta') {
    define('TACKTHIS_URL', 'http://beta.tackthis.com');
    define('CART_DOMAIN', 'beta.tackthis.com');
    define('CART_BETA_DOMAIN', 'beta.tackthis.com');
} else if ($options && trim($options['api_mode']) !== '') {
    define('TACKTHIS_URL', 'http://' . $options['api_mode']);
    define('CART_DOMAIN', $options['api_mode']);
    define('CART_BETA_DOMAIN', $options['api_mode']);
} else {
    define('TACKTHIS_URL', 'http://www.tackthis.com');
    define('CART_DOMAIN', 'www.tackthis.com');
    define('CART_BETA_DOMAIN', 'beta.tackthis.com');
}

/**
 * Used to load the required files on the plugins_loaded hook, instead of immediately.
 */
function wptackthis_admin_init()
{
    require_once(WPTACKTHIS_PATH . 'admin/class-admin.php');
    global $pagenow;
}

/**
 * Used to load the required files on the plugins_loaded hook, instead of immediately.
 */
function wptackthis_frontend_init()
{
    require_once(WPTACKTHIS_PATH . 'frontend/class-frontend.php');
}

/**
 * Proper way to enqueue scripts and styles
 */
function wptackthis_frontend_scripts()
{
    wp_enqueue_script('wptackthis-name', plugin_dir_url(__FILE__) . '/frontend/js/scripts.js', array(), WPTACKTHIS_VERSION, true);
}

if (is_admin()) {
    if (defined('DOING_AJAX') && DOING_AJAX) {
        //require_once( WPTACKTHIS_PATH . 'admin/ajax.php' );
    } else {
        add_action('plugins_loaded', 'wptackthis_admin_init', 15);
    }
} else {
    add_action('plugins_loaded', 'wptackthis_frontend_init', 15);
    add_action('wp_enqueue_scripts', 'wptackthis_frontend_scripts');
}

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/*
 * -----------------------------------------------------------------------------
 * 
 *          OLDER SCRIPTS 
 * 
 * -----------------------------------------------------------------------------
 */

//display the admin options page
function plugin_options_page()
{
    if (isset($_GET['page'])) {
        require_once(WPTACKTHIS_PATH . 'admin/pages/dashboard.php');
    }
}

//tell wordpress to register the tackthis shortcode in beta mode
add_shortcode("tackbeta", "tackbeta_handler");

function tackbeta_handler()
{
    //run function that actually does the work of the plugin
    $tackbeta_output = tack_function('beta');
    //send back text to replace shortcode in post
    return $tackbeta_output;
}

//tell wordpress to register the tackthis shortcode
add_shortcode("tack", "tack_handler");

function tack_handler()
{
    //run function that actually does the work of the plugin
    $tack_output = tack_function();
    //send back text to replace shortcode in post
    return $tack_output;
}

//front generator of the tackthis shortcode
function tack_function($mode = '')
{
    $options = get_option(WP_TACKTHIS_OPTIONS_NAME);

    $params = array();
    if (isset($_GET['cart']) && $_GET['cart'] > 0) {
        $params['cart'] = $_GET['cart'];
    } else if (isset($_GET['pid']) && $_GET['pid'] > 0) {
        $params['pid'] = $_GET['pid'];
    } else if (isset($_GET['cid']) && $_GET['cid'] > 0) {
        $params['cid'] = $_GET['cid'];
    }

    if (isset($_GET['keywords']) && $_GET['keywords'] != '') {
        $params['keywords'] = $_GET['keywords'];
    }
    if (isset($_GET['friend']) && $_GET['friend'] == 1) {
        $params['friend'] = 1;
    }

    $params['viewType'] = 'wordpress';

    if ($options['overwrite_iframe_links']) {
        $params['overwrite_iframe_links'] = 1;
        $options['url'] = home_url();
        $params['overwrite_options'] = base64_encode(json_encode($options));
    }
    if (isset($_REQUEST['cart'])) {
        $params['cart'] = $_REQUEST['cart'];
    }

    //process plugin
    $tack_output = '<script src="//' . CART_DOMAIN . '/widget-validate.php?' . http_build_query($params) . '&';

    //send back text to calling function
    return $tack_output;
}

//tell wordpress to register the tackthis shortcode
add_shortcode("this", "this_handler");

function this_handler()
{
    //run function that actually does the work of the plugin
    $this_output = this_function();
    //send back text to replace shortcode in post
    return $this_output;
}

//back generator of the tackthis shortcode
function this_function()
{
    //process plugin
    $this_output = '" type="text/javascript"></script>';
    //send back text to calling function
    return $this_output;
}

include('search.php');
add_action('widgets_init', create_function('', 'return register_widget("tackThisSearchWidget");'));

include('cart.php');
add_action('widgets_init', create_function('', 'return register_widget("tackThisCartWidget");'));

include('cart-alone.php');
add_action('widgets_init', create_function('', 'return register_widget("tackThisCartAloneWidget");'));
