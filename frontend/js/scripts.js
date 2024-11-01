
(function(window) {
    "use strict";

    setTimeout(function () {
        window.TACKTHIS = window.TACKTHIS || {};
        TACKTHIS.Events.addListener('iframe.hashchange', function (eventData) {
            var url_hash = eventData.hash.replace('#', '');
            if (window.WP_HOME_URL) {
                url_hash = TACKTHIS_WP.URLMapping(url_hash);
                window.history.pushState({}, "", WP_HOME_URL + url_hash);
            }
        });
    }, 3000);

    setTimeout(function () {
        var iframes = document.getElementsByTagName('iframe');
        [].map.call(iframes, function (iframe) {
            TACKTHIS.postMessage(iframe.contentWindow, {
                'name': 'wp.config',
                'wp_config': TACKTHIS_WP.CONFIG
            });
        });
    }, 5000);

    window.TACKTHIS_WP = window.TACKTHIS_WP || {};

    TACKTHIS_WP.URLMapping = function (url) {
        var TAG = 'UrlMapping::';
        var el = (document.getElementsByClassName('entry-title'))[0];
        if (url.indexOf('/home') === 0) {
            // ---------------------------------------------------------------------
            //                  Shop Homepage
            // ---------------------------------------------------------------------
            url = '/' + TACKTHIS_WP.CONFIG.pages_shop;
        } else if (url.indexOf('/category') === 0) {
            // ---------------------------------------------------------------------
            //                  Category Page
            // ---------------------------------------------------------------------
            var cid = url.replace('/category/', '');
            url = '/' + TACKTHIS_WP.CONFIG.pages_category + '/' + cid;
            if (el) {
                el.innerHTML = cid;
            }
        } else if (url.indexOf('/product') === 0) {
            // ---------------------------------------------------------------------
            //                  Product Page
            // ---------------------------------------------------------------------
            var pid = url.replace('/product/', '');
            url = '/' + TACKTHIS_WP.CONFIG.pages_product + '/' + pid;
            if (el) {
                el.innerHTML = pid;
            }
        } else if (url.indexOf('/checkout') === 0) {
            // ---------------------------------------------------------------------
            //                  Checkout Page
            // ---------------------------------------------------------------------
            url = '/' + TACKTHIS_WP.CONFIG.pages_checkout;
        } else if (url.indexOf('/user/order-history') === 0) {
            // ---------------------------------------------------------------------
            //                  User Order History Page
            // ---------------------------------------------------------------------
            url = '/' + TACKTHIS_WP.CONFIG.pages_customer_orders;
        } else if (url.indexOf('/search') === 0) {
            // ---------------------------------------------------------------------
            //                  Search Page
            // ---------------------------------------------------------------------
            var pid = url.replace('/search/', '');
            url = '/' + TACKTHIS_WP.CONFIG.pages_search + '/' + pid;

            if (el) {
                el.innerHTML = 'Search result for ' + decodeURIComponent(pid);
            }
        }
        return url;
    };

})(window);
