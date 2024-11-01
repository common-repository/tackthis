<?php

/**
 * @package Admin
 */
if (!defined('WPTACKTHIS_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    die;
}

/**
 * Class that holds most of the admin functionality for WP SEO.
 */
class WPTACKTHIS_Admin {

    /**
     * Class constructor
     */
    function __construct() {
        $this->grant_access();
        add_action('admin_menu', array($this, 'register_settings_page'), 5);
        add_action('admin_init', array($this, 'init_options_settings'));
    }

    /**
     * Register the menu item and its sub menu's.
     *
     * @global array $submenu used to change the label on the first item.
     */
    function init_options_settings() {
        $options = get_site_option(WP_TACKTHIS_OPTIONS_NAME);
        if (!isset($options)) {
            $options = array();
        }
        
        if (!isset($options['overwrite_iframe_links'])) {
            $options['overwrite_iframe_links'] = array();
        }
     
        if (!isset($options['pages'])) {
            $options['pages'] = array(
                'shop' => '', 
                'product' => '', 
                'category' => '', 
                'checkout' => '', 
                'customer_orders' => '', 
                'search' => '');
        }
        
        if (!isset($options['pages']['shop'])) {
            $options['pages']['shop'] = 'shop';
        }
        if (!isset($options['pages']['product'])) {
            $options['pages']['product'] = 'product';
        }
        if (!isset($options['pages']['category'])) {
            $options['pages']['category'] = 'category';
        }
        if (!isset($options['pages']['checkout'])) {
            $options['pages']['checkout'] = 'checkout';
        }
        if (!isset($options['pages']['customer_orders'])) {
            $options['pages']['customer_orders'] = 'customer/orders';
        }
        if (!isset($options['pages']['search'])) {
            $options['pages']['search'] = 'search';
        }
        $options['overwrite_iframe_links'] = absint($options['overwrite_iframe_links']);

        update_option(WP_TACKTHIS_OPTIONS_NAME, $options);
    }

    /**
     * Register the menu item and its sub menu's.
     *
     * @global array $submenu used to change the label on the first item.
     */
    function register_settings_page() {

        // ---------------------------------------------------------------------
        // Menu
        // ---------------------------------------------------------------------
        $page_title = __('TackThis:', 'wordpress-tackthis');

        //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        add_menu_page($page_title . ' ' . __('General Settings', 'wordpress-tackthis'), __('TACKTHIS', 'wordpress-tackthis'), 'manage_options', 'wptackthis_dashboard', array($this, 'config_page'), plugins_url('assets/images/tackthis-icon.png', dirname(__FILE__)), '99.31336');

        // http://codex.wordpress.org/Function_Reference/add_submenu_page
        // add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
        // $admin_page = add_submenu_page('wptackthis_dashboard', $page_title . ' ' . __('TackThis Pages', 'wordpress-tackthis'), __('Pages', 'wordpress-tackthis'), 'manage_options', 'wptackthis_pages', array($this, 'pages_page'));
        //add_action('load-' . $admin_page, array($this, 'title_metas_help_tab'));
        //         add_submenu_page('wptackthis_dashboard', $page_title . ' ' . __('Shop Settings', 'wordpress-tackthis'), __('Shop Settings', 'wordpress-tackthis'), 'manage_options', 'wptackthis_shop_settings', array($this, 'shopsettings_page'));
        //         add_submenu_page('wptackthis_dashboard', $page_title . ' ' . __('Products', 'wordpress-tackthis'), __('Products', 'wordpress-tackthis'), 'manage_options', 'wptackthis_products', array($this, 'products_page'));
        //         add_submenu_page('wptackthis_dashboard', $page_title . ' ' . __('Orders', 'wordpress-tackthis'), __('Orders', 'wordpress-tackthis'), 'manage_options', 'wptackthis_orders', array($this, 'orders_page'));
        add_submenu_page('wptackthis_dashboard', $page_title . ' ' . __('Shortcodes', 'wordpress-tackthis'), __('Shortcodes', 'wordpress-tackthis'), 'manage_options', 'wptackthis_shortcodes', array($this, 'shortcodes_page'));
        //add_submenu_page('wptackthis_dashboard', $page_title . ' ' . __('RSS', 'wordpress-tackthis'), __('RSS', 'wordpress-tackthis'), 'manage_options', 'wptackthis_rss', array($this, 'rss_page'));
        //add_submenu_page('wptackthis_dashboard', $page_title . ' ' . __('Import & Export', 'wordpress-tackthis'), __('Import & Export', 'wordpress-tackthis'), 'manage_options', 'wptackthis_import', array($this, 'import_page'));

        global $submenu;
        if (isset($submenu['wptackthis_dashboard']))
            $submenu['wptackthis_dashboard'][0][0] = __('Dashboard', 'wordpress-tackthis');


        // ---------------------------------------------------------------------
        // Settings Fields
        // register_setting(
        //         WP_TACKTHIS_OPTIONS_SETTINGS_GROUP_NAME, // Option group
        //         'widgetId', // Option name
        //         array($this, 'sanitize') // Sanitize
        // );
        // ---------------------------------------------------------------------
        register_setting(WP_TACKTHIS_OPTIONS_SETTINGS_GROUP_NAME, 'wptackthis_options', array($this, 'sanitize'));
    }

    /**
     * Check whether the current user is allowed to access the configuration.
     *
     * @return boolean
     */
    function grant_access() {
        if (!function_exists('is_multisite') || !is_multisite())
            return true;

        $options = get_site_option('wptackthis_ms');
        if (!is_array($options) || !isset($options['access']))
            return true;

        if ($options['access'] == 'superadmin' && !is_super_admin())
            return false;

        return true;
    }

    /**
     * Loads the form for the Dashboard page.
     */
    function config_page() {
        if (isset($_GET['page']) && 'wptackthis_dashboard' == $_GET['page'])
            require_once( WPTACKTHIS_PATH . 'admin/pages/dashboard.php' );
    }

    /**
     * Loads the page for the Shortcodes page.
     */
    function shortcodes_page() {
        if (isset($_GET['page']) && 'wptackthis_shortcodes' == $_GET['page'])
            require_once( WPTACKTHIS_PATH . 'admin/pages/shortcodes.php' );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        if (isset($input['pages'])) {
            foreach ($input['pages'] as $key => $value) {
                $input['pages'][$key] = sanitize_text_field($value);
            }
        }
        if (isset($input['shopId'])) {
            $input['shopId'] = absint($input['shopId']);
        }
        return $input;
    }

}

// Globalize the var first as it's needed globally.
global $wptackthis_admin;
$wptackthis_admin = new WPTACKTHIS_Admin();
