/*
 * web2Project Bootstrap theme: sidebar toggle and header tooltips.
 *
 * On wide screens the toggle collapses the sidebar to its icons and the
 * choice is remembered in localStorage; on narrow screens the sidebar is
 * hidden and the toggle slides it in over the page.
 */
(function () {
    'use strict';

    var body = document.body;
    var wide = window.matchMedia('(min-width: 1200px)');

    function remember(collapsed) {
        try {
            localStorage.setItem('w2p.sidebar', collapsed ? 'collapsed' : 'open');
        } catch (e) {
            // storage can be unavailable (private windows, blocked site data)
        }
    }

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-w2p-sidebar-toggle]');
        if (!toggle) {
            return;
        }
        event.preventDefault();
        if (wide.matches && !toggle.classList.contains('w2p-sidebar-backdrop')) {
            remember(body.classList.toggle('w2p-sidebar-collapsed'));
        } else {
            body.classList.toggle('w2p-sidebar-open');
        }
    });

    document.addEventListener('keydown', function (event) {
        if ('Escape' === event.key) {
            body.classList.remove('w2p-sidebar-open');
        }
    });

    wide.addEventListener('change', function () {
        body.classList.remove('w2p-sidebar-open');
    });

    if (window.bootstrap && window.bootstrap.Tooltip) {
        document.querySelectorAll('.w2p-header [data-bs-toggle="tooltip"]').forEach(function (element) {
            new window.bootstrap.Tooltip(element);
        });
    }
})();
