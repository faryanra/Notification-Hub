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

    // Tab click: switch visible pane and update URL.
    tabs.forEach((t) =>
      t.addEventListener('click', (e) => {
        e.preventDefault();

        const tab = t.dataset.tab || 'general';

        tabs.forEach((x) => x.classList.remove('nav-tab-active'));
        t.classList.add('nav-tab-active');

        panes.forEach((p) => p.classList.remove('is-active'));
        const pane = document.querySelector(`#nh-tab-${tab}`);
        if (pane) pane.classList.add('is-active');

        history.replaceState(null, '', t.getAttribute('href'));
      })
    );

    // Ensure "Send test" links keep tab in URL.
    document.querySelectorAll('.nh-test-btn').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();

        const tab = btn.dataset.tab || 'general';
        const href = new URL(btn.href);

        href.searchParams.set('tab', tab);
        window.location.href = href.toString();
      });
    });
  });
})();
