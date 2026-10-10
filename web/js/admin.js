/**
 * Скрипты админ-панели.
 *
 * Отвечает за переключение бокового меню (sidenav) на мобильных,
 * т.к. полноценный Material Dashboard JS в проекте не подключён.
 */
(function () {
    'use strict';

    var DESKTOP_QUERY = window.matchMedia('(min-width: 1200px)');

    function setSidebarOpen(open) {
        document.body.classList.toggle('sidenav-open', open);
        var backdrop = document.getElementById('sidenavBackdrop');
        if (backdrop) {
            backdrop.hidden = !open;
        }
    }

    function initSidebarToggle() {
        var toggler = document.getElementById('iconNavbarSidenav');
        var closer = document.getElementById('iconSidenav');
        var backdrop = document.getElementById('sidenavBackdrop');
        var sidenav = document.getElementById('sidenav-main');

        if (toggler) {
            toggler.addEventListener('click', function (e) {
                e.preventDefault();
                setSidebarOpen(!document.body.classList.contains('sidenav-open'));
            });
        }

        if (closer) {
            closer.addEventListener('click', function () {
                setSidebarOpen(false);
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', function () {
                setSidebarOpen(false);
            });
        }

        if (sidenav) {
            sidenav.addEventListener('click', function (e) {
                if (DESKTOP_QUERY.matches) {
                    return;
                }
                var link = e.target.closest('a');
                if (link) {
                    setSidebarOpen(false);
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                setSidebarOpen(false);
            }
        });

        DESKTOP_QUERY.addEventListener('change', function (e) {
            if (e.matches) {
                setSidebarOpen(false);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarToggle);
    } else {
        initSidebarToggle();
    }
})();
