<?php
/*
  Author: Vincent Lau
  Author URI: http://www.paywhere.com

  Products Search Widget Class for use in Wordpress
 */

class tackThisSearchWidget extends WP_Widget {

    function tackThisSearchWidget() {
        $widget_ops = array('classname' => 'tackThisSearchWidget', 'description' => 'Enables products search by keywords in product name and description.');
        $this->WP_Widget('tackThisSearchWidget', 'TackThis Search', $widget_ops);
    }

    function form($instance) {
        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);
        if (!$options['shopId'] || !is_numeric($options['shopId'])) {
            echo '<p>';
            echo 'You have not setup your TackThis Shop Settings. Please complete the setup <a href="admin.php?page=wptackthis_dashboard">here</a>.';
            echo '</p>';
        } else {
            echo '<p><label>Shop ID (<a href="admin.php?page=wptackthis_dashboard">Edit</a>): </label><input class="widefat" type="text" disabled value="' . $options['shopId'] . '" /></p>';
        }
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['shopId'] = $new_instance['shopId'];
        $instance['pageId'] = $new_instance['pageId'];
        return $instance;
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);
        $widgetId = $options['shopId'];
        $pageId = explode(',', $instance['pageId']);

        $cartDomain = CART_DOMAIN;
        if (!is_numeric($widgetId)) {
            $widgetIdArray = explode('_', $widgetId);
            if (!is_numeric($widgetIdArray[0]))
                $widgetId = 0;
            else {
                $widgetId = $widgetIdArray[0];
                if ($widgetIdArray[1] == 'beta')
                    $cartDomain = CART_BETA_DOMAIN;
            }
        }

        global $post;
        $thePostID = $post->ID;
        for ($i = 0; $i < sizeof($pageId); $i++) {
            $currentPageId = $pageId[$i];
            if ($pageId[$i] == $thePostID) {
                $tackedPage = 0;
                break;
            } else {
                $tackedPage = $pageId[$i];
            }
        }

        echo $before_widget;

        if (empty($widgetId) || $widgetId <= 0)
            echo SHOP_CANNOT_USE_SEARCH;
        else {
            $searchTheme = urlencode(get_stylesheet_uri());
            ?>
            <div id="search-shop" style="max-width:480px; min-width:200px; background:none;">
                <?php if ($options && $options['pages'] && $options['pages']['shop']) { ?>
                    <form name="tackthis-search" id="tackthis-search" action="<?php echo home_url($options['pages']['shop']); ?>">
                        <?php if ($tackedPage > 0) { ?>
                            <input type="hidden" name="page_id" id="page_id" value="<?php echo $tackedPage; ?>" />
                        <?php } ?>
                        <input type="hidden" name="cart" id="cart" value="search" />
                        <input type="text" size="20" name="keywords" id="keywords" placeholder="find more products" value="" /> <input type="submit" value="Search" />
                    </form>
                <?php } else { ?>
                    <form name="tackthis-search" id="tackthis-search">
                        <?php if ($tackedPage > 0) { ?>
                            <input type="hidden" name="page_id" id="page_id" value="<?php echo $tackedPage; ?>" />
                        <?php } ?>
                        <input type="hidden" name="cart" id="cart" value="search" />
                        <input type="text" size="20" name="keywords" id="keywords" placeholder="find more products" value="" /> <input type="submit" value="Search" />
                    </form>
                <?php } ?>
            </div><!--/search-shop-->
            <?php
        }
        echo $after_widget;
    }

}
?>