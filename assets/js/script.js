/**
 * MTs Al-Ihsan — script.js (v2.0)
 * Sidebar toggle, dropdowns, submenu accordion
 */

(function () {
    'use strict';

    /* ── Sidebar Toggle (Desktop: collapse / Mobile: slide) ── */
    const sidebar      = document.getElementById('sidebar');
    const mainWrapper  = document.getElementById('mainWrapper');
    const sidebarToggle= document.getElementById('sidebarToggle');
    const overlay      = document.getElementById('sidebarOverlay');

    const isMobile = () => window.innerWidth < 769;

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            if (isMobile()) {
                document.body.classList.toggle('mobile-sidebar-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed',
                    document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
            }
        });
    }

    // Restore desktop state
    if (!isMobile() && localStorage.getItem('sidebarCollapsed') === '1') {
        document.body.classList.add('sidebar-collapsed');
    }

    // Close mobile sidebar via overlay
    if (overlay) {
        overlay.addEventListener('click', () => {
            document.body.classList.remove('mobile-sidebar-open');
        });
    }

    /* ── Submenu Accordion ─────────────────────────────────── */
    document.querySelectorAll('.nav-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const navItem  = btn.closest('.nav-item.has-sub');
            const subMenu  = navItem.querySelector('.sub-menu');
            const isOpen   = navItem.classList.contains('open');

            // Close all
            document.querySelectorAll('.nav-item.has-sub.open').forEach(item => {
                item.classList.remove('open');
                item.querySelector('.sub-menu')?.classList.remove('open');
                item.querySelector('.nav-toggle')?.setAttribute('aria-expanded', 'false');
            });

            // Open clicked (if it was closed)
            if (!isOpen) {
                navItem.classList.add('open');
                subMenu?.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });

    /* ── User Dropdown ─────────────────────────────────────── */
    const userMenu    = document.getElementById('userMenu');
    const userTrigger = document.getElementById('userTrigger');
    const userDropdown= document.getElementById('userDropdown');

    if (userTrigger && userDropdown) {
        userTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = userDropdown.classList.toggle('show');
            userMenu.classList.toggle('open', open);
            userTrigger.setAttribute('aria-expanded', String(open));
            // Close notif
            document.getElementById('notifDropdown')?.classList.remove('show');
        });
    }

    /* ── Notification Dropdown ─────────────────────────────── */
    const notifToggle  = document.getElementById('notifToggle');
    const notifDropdown= document.getElementById('notifDropdown');

    if (notifToggle && notifDropdown) {
        notifToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
            // Close user dropdown
            userDropdown?.classList.remove('show');
            userMenu?.classList.remove('open');
        });
    }

    const notifClear = document.getElementById('notifClear');
    if (notifClear) {
        notifClear.addEventListener('click', () => {
            document.getElementById('notifBadge')?.classList.remove('show');
            const list = document.getElementById('notifList');
            if (list) list.innerHTML = '<p class="notif-empty">Tidak ada notifikasi baru.</p>';
        });
    }

    /* ── Close dropdowns on outside click ─────────────────── */
    document.addEventListener('click', () => {
        userDropdown?.classList.remove('show');
        userMenu?.classList.remove('open');
        notifDropdown?.classList.remove('show');
    });

    /* ── Confirm Delete ────────────────────────────────────── */
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const msg = el.dataset.confirm || 'Yakin ingin menghapus data ini?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    /* ── Flash alerts auto-dismiss ─────────────────────────── */
    document.querySelectorAll('.alert[data-autohide]').forEach(alert => {
        const autohideTime = parseInt(alert.dataset.autohide) || 5000;
        
        // Handle close button
        const closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                dismissAlert(alert);
            });
        }
        
        // Auto-dismiss after delay
        setTimeout(() => {
            dismissAlert(alert);
        }, autohideTime);
    });
    
    function dismissAlert(alertEl) {
        alertEl.classList.add('fade-out');
        setTimeout(() => {
            alertEl.remove();
        }, 300);
    }

})();