/**
 * Freelance Proposal Optimizer - App JS
 */

(function () {
    'use strict';

    /**
     * Get CSRF token for AJAX calls
     */
    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            return meta.getAttribute('content');
        }
        var input = document.querySelector('input[name="csrf_token"]');
        return input ? input.value : '';
    }

    /**
     * Attach CSRF token to fetch requests
     */
    function fetchWithCsrf(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers['X-CSRF-TOKEN'] = getCsrfToken();
        return fetch(url, options);
    }

    /**
     * Confirm before submitting delete forms
     */
    function confirmDelete(event) {
        var message = event.currentTarget.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
        if (!confirm(message)) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    /**
     * Auto-dismiss flash messages after 5 seconds
     */
    function initFlashDismiss() {
        var alerts = document.querySelectorAll('.flash-message');
        alerts.forEach(function (alert) {
            setTimeout(function () {
                alert.style.opacity = '0';
                setTimeout(function () {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            }, 5000);
        });
    }

    /**
     * Set active sidebar link based on current URL
     */
    function initSidebarActiveState() {
        var path = window.location.pathname.replace(/\/$/, '');
        var links = document.querySelectorAll('.sidebar-link');

        links.forEach(function (link) {
            var href = link.getAttribute('href').replace(/\/$/, '');
            link.classList.remove('active');

            if (path === href || (href !== '/dashboard' && href !== '/' && path.startsWith(href))) {
                link.classList.add('active');
            } else if ((path === '' || path === '/') && (href === '/dashboard' || href === '/')) {
                link.classList.add('active');
            }
        });
    }

    /**
     * Mobile sidebar toggle
     */
    function initSidebarToggle() {
        var sidebar = document.getElementById('sidebar');
        var toggle = document.querySelector('.sidebar-toggle');
        var overlay = document.querySelector('.sidebar-overlay');

        if (toggle && sidebar) {
            toggle.addEventListener('click', function () {
                sidebar.classList.toggle('show');
                if (overlay) {
                    overlay.classList.toggle('show');
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function () {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    }

    /**
     * Initialize on DOM ready
     */
    document.addEventListener('DOMContentLoaded', function () {
        initFlashDismiss();
        initSidebarActiveState();
        initSidebarToggle();

        // Attach confirm to all delete forms
        document.querySelectorAll('[data-confirm]').forEach(function (el) {
            el.addEventListener('submit', confirmDelete);
            el.addEventListener('click', function (e) {
                if (el.tagName === 'A' || el.tagName === 'BUTTON') {
                    confirmDelete(e);
                }
            });
        });
    });

    // Expose utilities globally
    window.FreelanceApp = {
        getCsrfToken: getCsrfToken,
        fetchWithCsrf: fetchWithCsrf,
        confirmDelete: confirmDelete
    };
})();
