/*
| ----------------------------------------------------------------------
| Bootstrap 3 / AdminLTE 2 compatibility for AdminLTE 4 (Bootstrap 5)
| ----------------------------------------------------------------------
| Load right after bootstrap.bundle.js and before any page script. It lets
| markup and scripts written for Bootstrap 3 keep working:
|
|  - data-toggle / data-target / data-dismiss / ... are mirrored onto the
|    data-bs-* attributes Bootstrap 5 listens for, now and for any markup
|    added later (AJAX modals, cloned child-table rows, ...).
|  - Bootstrap 3 state classes (.in) become Bootstrap 5's (.show), and
|    .dropdown-menu-right / .pull-right menus align to the end.
|  - $(el).modal(), .tooltip(), .popover(), .tab(), ... exist as soon as this
|    file runs, so $(function () { $('#m').modal('show') }) works even though
|    jQuery's ready handlers fire before Bootstrap 5 would register them.
|    $(el).modal() with no argument opens the modal, as in Bootstrap 3.
|  - AdminLTE 2 box widgets: [data-widget="collapse"] and [data-widget="remove"].
|  - The AdminLTE 2 sidebar toggle, data-toggle="offcanvas".
*/
(function (window, document) {
    'use strict';

    var bootstrap = window.bootstrap;
    var $ = window.jQuery;

    var ATTRIBUTES = ['toggle', 'target', 'dismiss', 'parent', 'placement', 'content', 'trigger', 'container',
        'backdrop', 'keyboard', 'html', 'delay', 'offset', 'title', 'original-title', 'animation', 'spy', 'ride',
        'slide', 'slide-to', 'interval'];
    var TOGGLES = ['dropdown', 'modal', 'collapse', 'tab', 'pill', 'tooltip', 'popover', 'button', 'offcanvas'];

    // Widgets that read their own data-toggle attributes (Summernote's editor and dialogs).
    var OWN_TOGGLES = '.note-editor, .note-popover, .note-modal, .note-dropdown-menu';

    function upgradeElement(el) {
        if (el.nodeType !== 1 || el.closest(OWN_TOGGLES)) {
            return;
        }

        var toggle = el.getAttribute('data-toggle');
        if (toggle === 'offcanvas' && !el.hasAttribute('data-lte-toggle')) {
            // AdminLTE 2's sidebar button.
            el.setAttribute('data-lte-toggle', 'sidebar');
        } else if (toggle && TOGGLES.indexOf(toggle) !== -1 && !el.hasAttribute('data-bs-toggle')) {
            el.setAttribute('data-bs-toggle', toggle);
        }

        for (var i = 0; i < ATTRIBUTES.length; i++) {
            var name = ATTRIBUTES[i];
            if (name === 'toggle' || name === 'title' || name === 'original-title') {
                continue;
            }
            if (el.hasAttribute('data-' + name) && !el.hasAttribute('data-bs-' + name)) {
                el.setAttribute('data-bs-' + name, el.getAttribute('data-' + name));
            }
        }

        var cls = el.classList;
        if (cls.contains('in') && (cls.contains('collapse') || cls.contains('fade') || cls.contains('tab-pane'))) {
            // Swap rather than add: Bootstrap 5 only clears .show when it hides the element.
            cls.remove('in');
            cls.add('show');
            if (cls.contains('tab-pane')) {
                cls.add('active');
            }
        }
        if (cls.contains('dropdown-menu') && (cls.contains('dropdown-menu-right') || cls.contains('pull-right'))) {
            cls.add('dropdown-menu-end');
        }
        // Bootstrap 5 tabs mark the link active, Bootstrap 3 marked the <li>.
        if (cls.contains('active') && el.tagName === 'LI' && el.parentNode && el.parentNode.classList &&
            el.parentNode.classList.contains('nav')) {
            var link = el.querySelector(':scope > a');
            if (link) {
                link.classList.add('active');
            }
        }
    }

    var SELECTOR = '[data-toggle],[data-target],[data-dismiss],[data-parent],[data-placement],[data-backdrop],' +
        '.collapse.in,.fade.in,.tab-pane.in,.dropdown-menu-right,.dropdown-menu.pull-right,.nav > li.active';

    function upgrade(root) {
        if (!root || root.nodeType !== 1 && root.nodeType !== 9) {
            return;
        }
        if (root.nodeType === 1 && root.matches(SELECTOR)) {
            upgradeElement(root);
        }
        var found = root.querySelectorAll(SELECTOR);
        for (var i = 0; i < found.length; i++) {
            upgradeElement(found[i]);
        }
    }

    // Keep the <li class="active"> of Bootstrap 3 tabs in step with the link Bootstrap 5 activates.
    document.addEventListener('shown.bs.tab', function (event) {
        var link = event.target;
        var item = link.parentNode;
        if (item && item.tagName === 'LI') {
            var siblings = item.parentNode.children;
            for (var i = 0; i < siblings.length; i++) {
                siblings[i].classList.remove('active');
            }
            item.classList.add('active');
        }
    });

    // Upgrade markup on click too, so nothing depends on the observer having run first.
    document.addEventListener('click', function (event) {
        var el = event.target.closest && event.target.closest('[data-toggle],[data-dismiss]');
        if (el) {
            upgradeElement(el);
        }
    }, true);

    function start() {
        upgrade(document);
        if (window.MutationObserver) {
            new MutationObserver(function (mutations) {
                for (var i = 0; i < mutations.length; i++) {
                    var added = mutations[i].addedNodes;
                    for (var j = 0; j < added.length; j++) {
                        upgrade(added[j]);
                    }
                }
            }).observe(document.documentElement, {childList: true, subtree: true});
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }

    if (!$ || !bootstrap) {
        return;
    }

    // Register Bootstrap's jQuery plugins now instead of on DOMContentLoaded.
    ['Alert', 'Button', 'Carousel', 'Collapse', 'Dropdown', 'Modal', 'Offcanvas', 'Popover', 'ScrollSpy', 'Tab',
        'Toast', 'Tooltip'].forEach(function (name) {
        var Plugin = bootstrap[name];
        if (!Plugin || !Plugin.jQueryInterface) {
            return;
        }
        var key = Plugin.NAME;
        var previous = $.fn[key];
        $.fn[key] = Plugin.jQueryInterface;
        $.fn[key].Constructor = Plugin;
        $.fn[key].noConflict = function () {
            $.fn[key] = previous;
            return Plugin.jQueryInterface;
        };
    });

    // Bootstrap 3: $(el).modal() and $(el).modal({backdrop: 'static'}) also show the modal.
    var modalInterface = bootstrap.Modal.jQueryInterface;
    $.fn.modal = function (config, relatedTarget) {
        if (typeof config === 'string') {
            return modalInterface.call(this, config, relatedTarget);
        }
        var options = $.extend({}, config);
        var show = options.show !== false;
        delete options.show;
        return this.each(function () {
            upgrade(this);
            var modal = bootstrap.Modal.getOrCreateInstance(this, options);
            if (show) {
                modal.show(relatedTarget);
            }
        });
    };
    $.fn.modal.Constructor = bootstrap.Modal;

    // AdminLTE 2 box tools.
    $(document).on('click', '.box [data-widget="collapse"]', function (event) {
        event.preventDefault();
        var $box = $(this).closest('.box');
        var $icon = $(this).find('.fa, .glyphicon, .ion').first();
        if ($box.hasClass('collapsed-box')) {
            $box.children('.box-body, .box-footer').slideDown(250, function () {
                $box.removeClass('collapsed-box');
            });
            $icon.removeClass('fa-plus').addClass('fa-minus');
        } else {
            $box.children('.box-body, .box-footer').slideUp(250, function () {
                $box.addClass('collapsed-box');
                $(this).css('display', '');
            });
            $icon.removeClass('fa-minus').addClass('fa-plus');
        }
    });

    $(document).on('click', '.box [data-widget="remove"]', function (event) {
        event.preventDefault();
        $(this).closest('.box').slideUp(250);
    });
})(window, document);
