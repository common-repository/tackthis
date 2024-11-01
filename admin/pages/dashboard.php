<div style="margin-right:20px;">
    <h2 class="title">TackThis Cart Settings</h2>

    <!--<div style="width:100%;"><script src="//<?php echo CART_DOMAIN; ?>/widget/scripts/generate-iframe-signup.php?logintype=shop&show=register&textcolor=fff" type="text/javascript"></script></div>-->
</div>


<form method="post" action="options.php">

    <h3 class="title">Shop Settings</h3>

    <p>New to TackThis?
        <a href="http://www.tackthis.com/shop/personal/pricing" target="_blank">Signup for a TackThis account</a> to
        start using it with Wordpress.</p>

    <?php $options = get_site_option(WP_TACKTHIS_OPTIONS_NAME); ?>
    <?php settings_fields(WP_TACKTHIS_OPTIONS_SETTINGS_GROUP_NAME); ?>
    <?php do_settings_sections(WP_TACKTHIS_OPTIONS_SETTINGS_GROUP_NAME); ?>

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row"><label for="shopId">Tackthis Shop ID</label></th>
            <td>
                <input name="wptackthis_options[shopId]" type="number" id="shopId"
                       value="<?php echo $options['shopId'] ?>" class="regular-text">

                <p class="description">This is your (numeric) TackThis Widget ID. E.g. "12345"</p>
            </td>
        </tr>
        </tbody>
    </table>

    <h3 class="title">WP URL Settings</h3>

    <p>
        To use TackThis Wordpress Plugin's SEO-friendly URLs, you will need to configure your WordPress
        <a href="<?php echo home_url('wp-admin/options-general.php'); ?>">Permalink Settings</a> to
        <code>Post name</code>, and ensure that your <code>.htaccess</code> file is writable.
    </p>

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row"><label for="pages_shop">Shop Page URL</label></th>
            <td>
                <label for="pages_shop">
                    <code><?php echo home_url() ?>/</code>
                    <input name="wptackthis_options[pages][shop]" type="text" id="pages_shop"
                           value="<?php echo $options['pages']['shop'] ?>" class="regular-text">
                </label>

                <p class="description">This is the URL to your TackThis Shop.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pages_product">Product Page URL</label></th>
            <td>
                <label for="pages_product">
                    <code><?php echo home_url() ?>/</code>
                    <input name="wptackthis_options[pages][product]" type="text" id="pages_product"
                           value="<?php echo $options['pages']['product'] ?>" class="regular-text">
                    <code>/product-name</code>
                </label>

                <p class="description">This is the URL to your TackThis Product Page. You can manage your inventory in
                    the
                    <a href="http://www.tackthis.com/login">TackThis Dashboard</a>, and the changes to your products
                    will be reflected
                    here.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pages_category">Category Page URL</label></th>
            <td>
                <label for="pages_category">
                    <code><?php echo home_url() ?>/</code>
                    <input name="wptackthis_options[pages][category]" type="text" id="pages_category"
                           value="<?php echo $options['pages']['category'] ?>" class="regular-text">
                    <code>/category-name</code>
                </label>

                <p class="description">This is the URL to your TackThis Category Page. You can manage your categories in
                    the
                    <a href="http://www.tackthis.com/login">TackThis Dashboard</a>, and the changes to your category
                    tags will be reflected
                    here.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pages_checkout">Checkout Page</label></th>
            <td>
                <label for="pages_checkout">
                    <code><?php echo home_url() ?>/</code>
                    <input name="wptackthis_options[pages][checkout]" type="text" id="pages_checkout"
                           value="<?php echo $options['pages']['checkout'] ?>" class="regular-text">
                </label>

                <p class="description">This is the URL to TackThis Checkout Page.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pages_customer_orders">Search Page</label></th>
            <td>
                <label for="pages_search">
                    <code><?php echo home_url() ?>/</code>
                    <input name="wptackthis_options[pages][search]" type="text" id="pages_search"
                           value="<?php echo $options['pages']['search'] ?>" class="regular-text">
                </label>

                <p class="description">This is the URL to TackThis Search Result page.</p>
            </td>
        </tr>
        <tr valign="top" style="<?php echo WP_TACKTHIS_PRODUCTION_HIDDEN_STYLES; ?>">
            <th scope="row">
                <label for="api_mode">
                    Tackthis Endpoints <br />
                    <small><i>(Show by default on localhost)</i></small>
                </label>
            </th>
            <td>
                <input name="wptackthis_options[api_mode]" type="text" id="api_mode"
                       value="<?php echo $options['api_mode'] ?>" class="regular-text">

                <p class="description">Enter "beta" to switch to using the Beta version of TackThis.</p>
            </td>
        </tr>
        </tbody>
    </table>

    <h3 class="title">TackThis Widget URL Rewrite</h3>

    <p>
        Some users prefer links in TackThis widget's link to redirect to Wordpress site, so that visitors can
        right-click on the links. <br/>
        Warning: Enable this following setting will increase your server load and page loading time.
    </p>

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row"><label for="shopId">Overwrite Iframe Links</label></th>
            <td>
                <input name="wptackthis_options[overwrite_iframe_links]" type="checkbox" id="overwrite_iframe_links"
                       value="1" <?php checked('1', $options['overwrite_iframe_links']); ?> />

                Check to enable.
            </td>
        </tr>
        </tbody>
    </table>



    <?php submit_button(); ?>
</form>
