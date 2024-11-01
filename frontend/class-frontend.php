<?php

/**
 * @package XML_Sitemaps
 */
if (!defined('WPTACKTHIS_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    die;
}

/**
 * Class WPTACKTHIS_Products
 */
class WPTACKTHIS_Products
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        if (!defined('ENT_XML1'))
            define("ENT_XML1", 16);

        add_action('init', array($this, 'init'), 1);
    }

    /**
     * Initialize sitemaps. Add sitemap rewrite rules and query var
     */
    public function init()
    {
        $this->options = $options = get_option(WP_TACKTHIS_OPTIONS_NAME);

        if (!is_object($GLOBALS['wp'])) {
            return;
        }

        if (!is_numeric($options['shopId'])) {
            return false;
        }

        $GLOBALS['wp']->add_query_var('sitemap');
        $GLOBALS['wp']->add_query_var('sitemap_n');
        $GLOBALS['wp']->add_query_var('xslt');
        add_rewrite_rule('product/$', 'index.php?product=$matches[1]', 'top');

        $current_url_scheme = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') ? 'https://' : 'http://';
        $current_url = $current_url_scheme . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

        /*
         * Replace home URL with so that we can parse the URL to get path relative to home URL
         * i.e.
         *  http://my-domain.com/wordpress/shop
         *  to
         *  http://DUMMY_HOST/shop
         */
        $current_url_replaced = str_replace(home_url(), $current_url_scheme . 'DUMMY_HOST', $current_url);

        $parsed_current_url = parse_url($current_url_replaced);
        $parsed_request_uri = explode('/', $parsed_current_url['path']);
        $parsed_request_uri = array_filter($parsed_request_uri); // Remove empty values.

        $url_segment_first = array_shift($parsed_request_uri); // This is the page type, e.g. shop, product, category
        $url_segment_remaining = join('/', $parsed_request_uri); // This is the page slug, e.g. /product/{apple-iphone-5s}
        $args = array(
            'slug' => $url_segment_remaining,
        );
        $this->args = $args;
        $this->slug = $args['slug'];
        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);
        if (!$options || !is_numeric($options['shopId'])) {
            return FALSE;
        }

        if (strcasecmp($url_segment_first, $options['pages']['shop']) === 0) {
            add_filter('the_posts', array($this, 'get_shop_page'));
        } elseif (strcasecmp($url_segment_first, $options['pages']['checkout']) === 0) {
            add_filter('the_posts', array($this, 'get_checkout_page'));
        } elseif (strcasecmp($url_segment_first, $options['pages']['product']) === 0) {
            add_filter('the_posts', array($this, 'get_product_page'));
        } elseif (strcasecmp($url_segment_first, $options['pages']['category']) === 0) {
            add_filter('the_posts', array($this, 'get_category_page'));
        } elseif (strcasecmp($url_segment_first, $options['pages']['search']) === 0) {
            add_filter('the_posts', array($this, 'get_search_page'));
        }
        add_action('wp_footer', array($this, 'wptackthis_footer_scripts'), 100);
    }

    /**
     * Generate scripts in Wordpres footer.
     */
    public function wptackthis_footer_scripts()
    {

        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);

        echo '<script>window.WP_HOME_URL = "' . home_url() . '";</script>';
        echo '<script>
            window.TACKTHIS_WP = window.TACKTHIS_WP || {};
            TACKTHIS_WP.CONFIG = {
                shop_id: "' . $options['shopId'] . '",
                pages_shop: "' . $options['pages']['shop'] . '",
                pages_checkout: "' . $options['pages']['checkout'] . '",
                pages_product: "' . $options['pages']['product'] . '",
                pages_category: "' . $options['pages']['category'] . '",
                pages_customer_orders: "' . $options['pages']['customer_orders'] . '",
                pages_search: "' . $options['pages']['search'] . '",
                overwrite_iframe_links: "' . $options['overwrite_iframe_links'] . '",
                url: "' . site_url() . '"
            };
            </script>';
    }

    public function wptackthis_action_metadata_head()
    {
        global $tt_product;
        if ($tt_product && is_numeric($tt_product->productId)) {
            echo '<meta property="product:plural_title"      content="' . $tt_product->productName . '" />';
            echo '<meta property="product:price:amount"      content="' . $tt_product->productPrice . '"/>';
            echo '<meta property="product:price:currency"    content="' . $tt_product->currency->currencyCode . '"/>';
            echo '<meta property="og:description"            content="' . strip_tags($tt_product->productDescriptionShort) . '"/>';
            if ($tt_product->productImages && $tt_product->productImages[0]) {
                echo '<meta property="og:image"               content="' . $tt_product->productImages[0]->url . '"/>';
            }
        }
    }

    public function is_plugin_settings_completed()
    {
        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);
        if (!$options['shopId']) {
            return false;
        }
        return true;
    }

    public function filter_product_wpseo_title($title)
    {
        global $post;
        $title = str_replace('%%title%%', $post->post_name, $title);
        return $title;
    }

    public function complete_dynamic_page_setup()
    {
        global $post, $posts, $wp, $wp_query;
        $posts = null;
        $posts = array($post);

        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_home = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        unset($wp_query->query["error"]);
        $wp_query->query_vars["error"] = "";
        $wp_query->is_404 = false;

        add_filter('wpseo_title', array($this, 'filter_product_wpseo_title'));
        add_action('wp_head', array($this, 'wptackthis_action_metadata_head'), 0);
    }

    // -----------------------------------------------------------------------------------------------------------------
    //      Calls for retrieving TackThis Data
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * get_shop_page
     * the Money function that catches the request and returns the page as if it was retrieved from the database
     * <a href="/param">@param</a>  array $posts
     * @return array
     */
    public function get_shop_page()
    {
        global $wp, $wp_query, $post, $posts, $tt_product;

        // ---------------------------------------------------------------------
        // Check and make sure required settings are completed
        // ---------------------------------------------------------------------
        if (!$this->is_plugin_settings_completed()) {
            return $this->get_incomplete_settings_page();
        }

        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);

        $cache_dir = WP_CONTENT_CACHE_TACKTHIS_DIR . '/shop';
        @mkdir($cache_dir);
        $cache_file = $cache_dir . '/' . $options['shopId'] . '.json';
        if (file_exists($cache_file) && time() - filemtime($cache_file) < 259200) {
            // ---------------------------------------------------------------------
            // Retrieve category from Cache
            // ---------------------------------------------------------------------
            $result_json = json_decode(file_get_contents($cache_file));
        } else {

            // ---------------------------------------------------------------------
            // Retrieve Shop from TackThis
            // ---------------------------------------------------------------------
            $TACKTHIS_URL = TACKTHIS_URL . '/json/shop.php?widgetId=' . $options['shopId'];
            if (function_exists('curl_init')) {
                $tt_resource = curl_init();
                curl_setopt($tt_resource, CURLOPT_URL, $TACKTHIS_URL);
                curl_setopt($tt_resource, CURLOPT_RETURNTRANSFER, 1);
                $result_json = json_decode(curl_exec($tt_resource));
                curl_close($tt_resource);
            } else {
                $result_json = json_decode(file_get_contents($TACKTHIS_URL));
            }
            if (!$result_json->results || !$result_json->results->data) {
                return FALSE;
            }
            if ($result_json) {
                file_put_contents($cache_file, json_encode($result_json));
            }
        }

        $tt_shop = $result_json->results->data;

        // ---------------------------------------------------------------------
        // Create Fake Post
        // ---------------------------------------------------------------------
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = $tt_shop->shopName;
        $post->guid = get_bloginfo('wpurl' . '/' . $tt_shop->shopName);
        $post->post_title = $tt_shop->shopName;
        //put your custom content here
        $post->post_content = "[tack]api=" . $options['shopId'] . "[this]";
        $post->post_status = 'static';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        //dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
        $post->post_date = $tt_shop->createdOn;
        $post->post_date_gmt = $tt_shop->createdOn;
        $post = (object)array_merge((array)$post, (array)$this->args);

        $this->complete_dynamic_page_setup();
        return $posts;
    }

    /**
     * get_product_page
     * the Money function that catches the request and returns the page as if it was retrieved from the database
     * <a href="/param">@param</a>  array $posts
     * @return array
     */
    public function get_product_page($posts)
    {
        global $wp, $wp_query, $post, $posts, $tt_product;

        // ---------------------------------------------------------------------
        // Check and make sure required settings are completed
        // ---------------------------------------------------------------------
        if (!$this->is_plugin_settings_completed()) {
            return $this->get_incomplete_settings_page();
        }

        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);
        // "productId" can be either "numeric product ID" or "product SEF".
        $productId = $this->slug;
        if (!$productId) {
            return false;
        }

        $cache_dir = WP_CONTENT_CACHE_TACKTHIS_DIR . '/product';
        @mkdir($cache_dir);
        $cache_file = $cache_dir . '/' . $productId . '.json';
        if (file_exists($cache_file) && time() - filemtime($cache_file) < 259200) {
            // ---------------------------------------------------------------------
            // Retrieve category from Cache
            // ---------------------------------------------------------------------
            $result_json = json_decode(file_get_contents($cache_file));
        } else {
            // ---------------------------------------------------------------------
            // Retrieve product from TackThis
            // ---------------------------------------------------------------------
            $TACKTHIS_URL = TACKTHIS_URL . '/json/product.php?widgetId=' . $options['shopId'] . '&productId=' . $productId;
            if (function_exists('curl_init')) {
                $tt_resource = curl_init();
                curl_setopt($tt_resource, CURLOPT_URL, $TACKTHIS_URL);
                curl_setopt($tt_resource, CURLOPT_RETURNTRANSFER, 1);
                $result_json = json_decode(curl_exec($tt_resource));
                curl_close($tt_resource);
            } else {
                $result_json = json_decode(file_get_contents($TACKTHIS_URL));
            }
            if ($result_json) {
                file_put_contents($cache_file, json_encode($result_json));
            }
        }

        if (!$result_json->results || !$result_json->results->data) {
            return FALSE;
        } else {
            $tackthis_product = $result_json->results->data;
        }

        // ---------------------------------------------------------------------
        // Create Fake Post
        // ---------------------------------------------------------------------
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = $tackthis_product->productName;
        $post->guid = get_bloginfo('wpurl' . '/' . $tackthis_product->productSEF);
        $post->post_title = $tackthis_product->productName;
        // Put your custom content here
        $post->post_content = "[tack]api=" . $options['shopId'] . "&pid=" . $tackthis_product->productId . "[this]";
        $post->post_status = 'static';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        // Dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
        $post->post_date = $tackthis_product->createdOn;
        $post->post_date_gmt = $tackthis_product->createdOn;
        $post = (object)array_merge((array)$post, (array)$this->args);

        $this->complete_dynamic_page_setup();
        return $posts;
    }

    /**
     * get_category_page
     * the Money function that catches the request and returns the page as if it was retrieved from the database
     * <a href="/param">@param</a>  array $posts
     * @return array
     */
    public function get_category_page($posts)
    {
        global $wp, $wp_query, $post, $posts, $tt_product;

        // ---------------------------------------------------------------------
        // Check and make sure required settings are completed
        // ---------------------------------------------------------------------
        if (!$this->is_plugin_settings_completed()) {
            return $this->get_incomplete_settings_page();
        }

        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);
        // "categoryId" can be either "numeric category ID" or "category SEF".
        $categoryId = $this->slug;
        if (!$categoryId) {
            return false;
        }

        $cache_dir = WP_CONTENT_CACHE_TACKTHIS_DIR . '/category';
        @mkdir($cache_dir);
        $cache_file = $cache_dir . '/' . $categoryId . '.json';
        if (file_exists($cache_file) && time() - filemtime($cache_file) < 259200) {
            // ---------------------------------------------------------------------
            // Retrieve category from Cache
            // ---------------------------------------------------------------------
            $result_json = json_decode(file_get_contents($cache_file));
        } else {
            // ---------------------------------------------------------------------
            // Retrieve category from TackThis
            // ---------------------------------------------------------------------
            $TACKTHIS_URL = TACKTHIS_URL . '/json/category.php?fn=GetCategory';
            $TACKTHIS_URL .= '&widgetId=' . $options['shopId'] . '&catId=' . $categoryId . '&';
            if (function_exists('curl_init')) {
                $tt_resource = curl_init();
                curl_setopt($tt_resource, CURLOPT_URL, $TACKTHIS_URL);
                curl_setopt($tt_resource, CURLOPT_RETURNTRANSFER, 1);
                $result_json = json_decode(curl_exec($tt_resource));
                curl_close($tt_resource);
            } else {
                $result_json = json_decode(file_get_contents($TACKTHIS_URL));
            }
            if ($result_json) {
                file_put_contents($cache_file, json_encode($result_json));
            }
        }

        if (!$result_json->results || !$result_json->results || !is_numeric($result_json->results->catId)) {
            return FALSE;
        } else {
            $tackthis_category = $result_json->results;
        }

        // ---------------------------------------------------------------------
        // Create Fake Post
        // ---------------------------------------------------------------------
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = $tackthis_category->catName;
        $post->guid = get_bloginfo('wpurl' . '/' . $tackthis_category->catSEF);
        $post->post_title = $tackthis_category->catName;
        // Put your custom content here
        $post->post_content = "[tack]api=" . $options['shopId'] . "&cid=" . $tackthis_category->catId . "[this]";
        // Just needs to be a number - negatives are fine
        $post->post_status = 'static';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        // Dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
        $post->post_date = $tackthis_category->createdOn;
        $post->post_date_gmt = $tackthis_category->createdOn;
        $post = (object)array_merge((array)$post, (array)$this->args);

        $this->complete_dynamic_page_setup();
        return $posts;
    }

    /**
     * get_checkout_page
     * the Money function that catches the request and returns the page as if it was retrieved from the database
     * <a href="/param">@param</a>  array $posts
     * @return array
     */
    public function get_checkout_page()
    {
        global $wp, $wp_query, $post, $posts, $tt_product;

        // ---------------------------------------------------------------------
        // Check and make sure required settings are completed
        // ---------------------------------------------------------------------
        if (!$this->is_plugin_settings_completed()) {
            return $this->get_incomplete_settings_page();
        }

        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);

        // ---------------------------------------------------------------------
        // Create Fake Post
        // ---------------------------------------------------------------------
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = 'Checkout';
        $post->guid = get_bloginfo('wpurl' . '/');
        $post->post_title = $tt_product->productName;
        //put your custom content here
        $cartTheme = urlencode(get_stylesheet_uri());
        $post->post_content = "[tack]api=" . $options['shopId'] . "&cart=view" . "[this]";
        $post->post_status = 'static';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        //dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
        $post->post_date = $tt_product->createdOn;
        $post->post_date_gmt = $tt_product->createdOn;
        $post = (object)array_merge((array)$post, (array)$this->args);

        $this->complete_dynamic_page_setup();
        return $posts;
    }

    /**
     * get_checkout_page
     * the Money function that catches the request and returns the page as if it was retrieved from the database
     * <a href="/param">@param</a>  array $posts
     * @return array
     */
    public function get_search_page()
    {
        global $wp, $wp_query, $post, $posts, $tt_product;

        // ---------------------------------------------------------------------
        // Check and make sure required settings are completed
        // ---------------------------------------------------------------------
        if (!$this->is_plugin_settings_completed()) {
            return $this->get_incomplete_settings_page();
        }

        $SEARCH_SLUG = $this->slug;
        if (!$SEARCH_SLUG) {
            return $this->get_shop_page();
        }

        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);

        // ---------------------------------------------------------------------
        // Create Fake Post
        // ---------------------------------------------------------------------
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = 'Search';
        $post->guid = get_bloginfo('wpurl' . '/');
        $post->post_title = $tt_product->productName;
        //put your custom content here
        $cartTheme = urlencode(get_stylesheet_uri());
        $post->post_content = '<script src="' . TACKTHIS_URL . '/widget-validate.php?api=' . $options['shopId'] . '&cart=search&keywords=' . $SEARCH_SLUG . '" type="text/javascript"></script>';
        $post->post_status = 'static';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        //dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
        $post->post_date = $tt_product->createdOn;
        $post->post_date_gmt = $tt_product->createdOn;
        $post = (object)array_merge((array)$post, (array)$this->args);

        $this->complete_dynamic_page_setup();
        return $posts;
    }

    public function get_incomplete_settings_page()
    {
        global $wp, $wp_query, $post, $posts, $tt_product;

        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);

        if (!$options['shopId']) {
            $post = new stdClass;
            $post->post_author = 1;
            $post->post_name = 'Incomplete Settings';
            $post->guid = get_bloginfo('wpurl' . '/');
            $post->post_title = 'Incomplete Settings';
            $post->post_content = "Incomplete Setting: \"<strong>Shop ID</strong>\". Please login to Wordpress Dashboard > TackThis plugin to configure.";
            $post->post_status = 'static';
            $post->comment_status = 'closed';
            $post->ping_status = 'closed';
            $post->comment_count = 0;
            //dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
            $post->post_date = date('Y-m-d H:i:s', time());
            $post->post_date_gmt = date('Y-m-d H:i:s', time());
            $post = (object)array_merge((array)$post, (array)$this->args);

            $this->complete_dynamic_page_setup();
            return $posts;
        }

    }

}

global $wptackthis_products;
$wptackthis_products = new WPTACKTHIS_Products();
