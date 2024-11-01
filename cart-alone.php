<?php

/*
  Author: Vincent Lau
  Author URI: http://www.paywhere.com

  Shopping Cart Widget Class for use in Wordpress
 */

class tackThisCartAloneWidget extends WP_Widget
{

    function tackThisCartAloneWidget()
    {
        $widget_ops = array('classname' => 'tackThisCartAloneWidget', 'description' => 'Displays the items and price in your shopping cart.');
        $this->WP_Widget('tackThisCartAloneWidget', 'TackThis Cart', $widget_ops);
    }

    function form($instance)
    {
        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);
        if (!$options['shopId'] || !is_numeric($options['shopId'])) {
            echo '<p>';
            echo 'You have not setup your TackThis Shop Settings. Please complete the setup <a href="admin.php?page=wptackthis_dashboard">here</a>.';
            echo '</p>';
        } else {
            echo '<p><label>Shop ID (<a href="admin.php?page=wptackthis_dashboard">Edit</a>): </label><input class="widefat" type="text" disabled value="' . $options['shopId'] . '" /></p>';
        }
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['shopId'] = $new_instance['shopId'];
        $instance['pageId'] = $new_instance['pageId'];
        $instance['langCn'] = $new_instance['langCn'];
        return $instance;
    }

    function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);
        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);
        $widgetId = $options['shopId'];
        $pageId = explode(',', $instance['pageId']);
        $langCn = $instance['langCn'];

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
                $tackedPage = 1;
                break;
            } else
                $tackedPage = 0;
        }

        echo $before_widget;

        if (empty($widgetId) || $widgetId <= 0)
            echo SHOP_CANNOT_USE_CART;
        else {
            $cartTheme = urlencode(get_stylesheet_uri());

            $parameters = array(
                'cartWidgetId' => $widgetId,
                'standaloneCart' => 1,
                'shopPage' => home_url($options['pages']['shop']),
                'tackedPage' => $tackedPage,
                'pageId' => $currentPageId,
                'cartTheme' => $cartTheme
            );

            if ($langCn == 1) {
                $parameters['langCn'] = '1';
            }

            $iframe_src = '//' . $cartDomain . '/widget/shop/generate-cart-info.php?' . http_build_query($parameters);

            ?>
            <div id="cart-shop" style="max-width:200px; min-width:100px; background:none;">
                <iframe src="<?php echo $iframe_src; ?>" frameborder="0" scrolling="no"
                        style="background:none; border:none;" width="100%" height="50"
                        name="tackthis-cart" id="tackthis-cart"></iframe>
            </div>
            <!--/cart-shop-->
        <?php
        }
        echo $after_widget;
    }

}
