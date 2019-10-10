(function($) {
    'use strict';

    function getQueryArg(url, name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(url);
        return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

    function addQueryArg(url, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$|#)", "i");
        var separator = url.indexOf('?') !== -1 ? "&" : "?";
        if (url.match(re)) {
            return url.replace(re, '$1' + key + "=" + value + '$2');
        }
        var anchor = '';
        if (url.indexOf('#') !== -1) {
            anchor = url.substr(url.indexOf('#'));
            url = url.substr(0, url.indexOf('#'));
        }
        return url + separator + key + "=" + value + anchor;
    }

    function getLocation(href) {
        var a = document.createElement("a");
        a.href = href;
        return a;
    }

    function processUrl(url) {
        var targetLocation = getLocation(url);
        if (url === '#' ||
            targetLocation.host !== location.host ||
            targetLocation.href.indexOf('/wp-login.php') !== -1 ||
            targetLocation.href.indexOf('/wp-admin/') !== -1 ||
            targetLocation.href.indexOf('/feed/') !== -1 ||
            targetLocation.href.indexOf('/trackback/') !== -1
        ) {
            return url;
        }

        if (url[0] === '#') {
            return location.href.split('#')[0] + url;
        }

        var parametersForCheck = ['preview', 'template', 'stylesheet', 'preview_iframe', 'theme', 'nonce', 'original', 'wp_customize'];

        parametersForCheck.forEach(function (attr) {
            var value = getQueryArg(location.href, attr);
            if (value !== null) {
                url = addQueryArg(url, attr, value);
            }
        });
        return url;
    }

    function updatePreviewLinks() {
        //$(".carousel .left-button, .carousel .right-button").filter('a').attr("href", "#"); // TODO: recheck

        $("a[href*=\'#038;\']").each(function () {
            var href = $(this).attr("href");
            href = href.replace("#038;", "&");
            $(this).attr("href", href);
        });

        if (window.isThemlerIframe()) {
            return;
        }

        $('form').each(function () {
            var form = $(this);
            var action = form.attr('action');
            if (action) {
                form.attr('action', processUrl(action));
            }
        });

        $('[href]').each(function() {
            var link = $(this);
            if (link.prop('tagName').toLowerCase() !== 'link') {
                link.attr('href', processUrl(link.attr('href')));
            }
        });
    }

    $(updatePreviewLinks);
    $(function() {
        if (window.wpJQuery) {
            window.wpJQuery(document.body).bind('updated_wc_div', updatePreviewLinks);
        }
    });

    window.processPreviewUrl = processUrl;

    // prevent shopping cart updating from sessionStorage
    // see cart-fragments.js
    $(function () {
        try {
            if (typeof wc_cart_fragments_params === 'object' && wc_cart_fragments_params.fragment_name) {
                var jsonStr = sessionStorage.getItem(wc_cart_fragments_params.fragment_name);
                if (jsonStr) {
                    var data = $.parseJSON(jsonStr);
                    sessionStorage.setItem(wc_cart_fragments_params.fragment_name,
                        JSON.stringify({'div.widget_shopping_cart_content': data['div.widget_shopping_cart_content']}));
                }
            }
        } catch (err) {
            // unsupported html5 storage
        }
    });
})(jQuery);