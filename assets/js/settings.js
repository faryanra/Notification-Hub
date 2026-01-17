// =====================================================
// Notification Hub — Settings JS
//
// Handles tab switching (no inline scripts) and ensures test links
// keep the correct `tab` in the query string.
//
// @package Notification_Hub
// @since 1.6.2
// =====================================================

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.nh-settings-tabs .nav-tab');
    const panes = document.querySelectorAll('.nh-tab');

    if (tabs.length === 0 || panes.length === 0) return;

    // Tab click: switch visible pane and update URL.
    tabs.forEach((tabEl) =>
      tabEl.addEventListener('click', (e) => {
        e.preventDefault();

        const tab = tabEl.dataset.tab || 'general';

        tabs.forEach((x) => x.classList.remove('nav-tab-active'));
        tabEl.classList.add('nav-tab-active');

        panes.forEach((p) => p.classList.remove('is-active'));
        const pane = document.querySelector(`#nh-tab-${tab}`);
        if (pane) pane.classList.add('is-active');

        const href = tabEl.getAttribute('href');
        if (href) {
          history.replaceState(null, '', href);
        }
      })
    );

    // Ensure "Send test" links keep tab in URL.
    document.querySelectorAll('.nh-test-btn').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();

        const tab = btn.dataset.tab || 'general';

        let href;
        try {
          href = new URL(btn.getAttribute('href') || '', window.location.origin);
        } catch (_) {
          return;
        }

        href.searchParams.set('tab', tab);
        window.location.href = href.toString();
      });
    });
  });
})();