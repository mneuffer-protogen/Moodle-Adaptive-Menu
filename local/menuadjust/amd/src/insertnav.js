// AMD module to insert navigation items into Lambda theme nav.
// Robust: tries several selectors and will retry for a short period if the nav is rendered late.
define(['jquery'], function($) {
    function findNav() {
        var selectors = [
            'nav.lambda-nav[aria-label="Site navigation"]',
            'nav.lambda-nav',
            'nav[aria-label="Site navigation"]',
            '.lambda-nav',
            '.site-navigation',
            '.navbar'
        ];
        for (var i = 0; i < selectors.length; i++) {
            var n = $(selectors[i]);
            if (n && n.length) { return n.first(); }
        }
        return $();
    }

    function ensureContainer(nav) {
        var container = nav.find('ul.navbar-nav').first();
        if (!container.length) {
            container = nav.find('ul.nav').first();
        }
        if (!container.length) {
            // Create a UL with navbar-nav class and push to the end of nav.
            container = $('<ul/>').addClass('navbar-nav ms-auto');
            // Prefer appending into a .navbar-collapse if present
            var collapse = nav.find('.navbar-collapse').first();
            if (collapse && collapse.length) {
                collapse.append(container);
            } else {
                nav.append(container);
            }
        }
        return container;
    }

    return {
        init: function(items, options) {
            options = options || {};
            var maxAttempts = options.attempts || 12; // up to ~3.6s with 300ms interval
            var interval = options.interval || 300;
            var attempt = 0;

            function tryAttach() {
                attempt++;
                var nav = findNav();
                if (nav && nav.length) {
                    try {
                        var container = ensureContainer(nav);
                        items.forEach(function(it) {
                            if (!it || !it.title || !it.url) { return; }
                            var li = $('<li/>').addClass('nav-item');
                            var a = $('<a/>').addClass('nav-link').attr('href', it.url).text(it.title);
                            li.append(a);
                            container.append(li);
                        });
                        if (window.console && console.debug) {
                            console.debug('local_menuadjust: inserted', items.length, 'items into Lambda nav');
                        }
                        return;
                    } catch (e) {
                        if (window.console && console.error) { console.error('local_menuadjust insertnav error', e); }
                        return;
                    }
                }

                if (attempt < maxAttempts) {
                    setTimeout(tryAttach, interval);
                } else {
                    if (window.console && console.debug) {
                        console.debug('local_menuadjust: failed to find Lambda nav after', attempt, 'attempts');
                    }
                }
            }

            tryAttach();
        }
    };
});
