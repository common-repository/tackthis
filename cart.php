<?php

/*
  Author: Vincent Lau
  Author URI: http://www.paywhere.com

  Shopping Cart Widget Class for use in Wordpress
 */

class tackThisCartWidget extends WP_Widget
{

    function tackThisCartWidget()
    {
        $widget_ops = array('classname' => 'tackThisCartWidget', 'description' => 'Displays the navigation menu and your shopping cart.');
        $this->WP_Widget('tackThisCartWidget', 'TackThis Menu + Cart', $widget_ops);
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
        $instance = wp_parse_args((array)$instance, array(
            'shopId' => '', 
            'pageId' => '', 
            'langCn' => '', 
            'cartClass' => '', 
            'naviContactUs' => '', 
            'naviTack' => '', 
            'naviCart' => '')
        );
        $shopId = $instance['shopId'];
        $pageId = $instance['pageId'];
        $langCn = $instance['langCn'];
        $naviContactUs = $instance['naviContactUs'];
        $naviTack = $instance['naviTack'];
        $naviCart = $instance['naviCart'];
        ?>
        <p><label for="<?php echo $this->get_field_id('langCn'); ?>">Chinese Support: <input
                    id="<?php echo $this->get_field_id('langCn'); ?>"
                    name="<?php echo $this->get_field_name('langCn'); ?>" type="checkbox"
                    value="1"<?php if (esc_attr($langCn) == 1) echo ' checked="checked"'; ?> /></label></p>
        <p><label for="<?php echo $this->get_field_id('naviContactUs'); ?>">Hide Contact Us: <input
                    id="<?php echo $this->get_field_id('naviContactUs'); ?>"
                    name="<?php echo $this->get_field_name('naviContactUs'); ?>" type="checkbox"
                    value="1"<?php if (esc_attr($naviContactUs) == 1) echo ' checked="checked"'; ?> /></label>
        </p>
        <p><label for="<?php echo $this->get_field_id('naviTack'); ?>">Hide Tack: <input
                    id="<?php echo $this->get_field_id('naviTack'); ?>"
                    name="<?php echo $this->get_field_name('naviTack'); ?>" type="checkbox"
                    value="1"<?php if (esc_attr($naviTack) == 1) echo ' checked="checked"'; ?> /></label></p>
        <p><label for="<?php echo $this->get_field_id('naviCart'); ?>">Hide Cart: <input
                    id="<?php echo $this->get_field_id('naviCart'); ?>"
                    name="<?php echo $this->get_field_name('naviCart'); ?>" type="checkbox"
                    value="1"<?php if (esc_attr($naviCart) == 1) echo ' checked="checked"'; ?> /></label></p>
    <?php
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['shopId'] = $new_instance['shopId'];
        $instance['pageId'] = $new_instance['pageId'];
        $instance['langCn'] = $new_instance['langCn'];
        $instance['naviContactUs'] = $new_instance['naviContactUs'];
        $instance['naviTack'] = $new_instance['naviTack'];
        $instance['naviCart'] = $new_instance['naviCart'];
        return $instance;
    }

    function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);
        $options = get_option(WP_TACKTHIS_OPTIONS_NAME);
        $widgetId = $options['shopId'];
        $pageId = explode(',', $instance['pageId']);
        $langCn = $instance['langCn'];
        $naviContactUs = $instance['naviContactUs'];
        $naviTack = $instance['naviTack'];
        $naviCart = $instance['naviCart'];

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

        if ($langCn == 1)
            $showLangCn = '&langCn=1';
        else
            $showLangCn = '';
        if ($naviContactUs == 1)
            $hideNaviCU = '&naviContactUs=1';
        else
            $hideNaviCU = '';
        if ($naviTack == 1)
            $hideNaviT = '&naviTack=1';
        else
            $hideNaviT = '';
        if ($naviCart == 1)
            $hideCartT = '&naviCart=1';
        else
            $hideCartT = '';

        echo $before_widget;

        if (empty($widgetId) || $widgetId <= 0)
            echo SHOP_CANNOT_USE_CART;
        else {
            $cartTheme = urlencode(get_stylesheet_uri());

            $cartIframSrc =
                '//' . $cartDomain . '/widget/shop/generate-cart-info.php?cart' .
                'WidgetId=' . $widgetId .
                '&shopPage=' . home_url($options['pages']['shop']) .
                '&cartTheme=' . $cartTheme . $showLangCn . $hideNaviCU . $hideNaviT . $hideCartT;

            $cartHTMLDiv = '
            <div id="cart-shop" style="max-width:580px; min-width:200px; background:none;">
                <iframe
                    src="' . $cartIframSrc . '"
                    frameborder="0" scrolling="no" style="background:none; border:none;" width="100%" height="50"
                    name="tackthis-cart" id="tackthis-cart"></iframe>
            </div><!--/cart-shop-->
            ';

            echo $cartHTMLDiv;
        }
        echo $after_widget;
    }

}
