// =====================================================
// Notification Hub - Settings JS
//
// Handles tab navigation and test links.
//
// @package Notification_Hub
// @since 1.0.0
// =====================================================

(function () {
  'use strict';

  const AUTO_HIDE_MS = 10000;

  function getActiveTabFromUrl() {
    const params = new URLSearchParams(window.location.search || '');
    const tab = (params.get('tab') || '').trim();
    return tab ? tab : null;
  }

  function initAutoHideNotices() {
    const nodes = document.querySelectorAll('.nh-auto-hide[data-auto-hide="1"]');
    if (!nodes || nodes.length === 0) return;

    nodes.forEach((notice) => {
      window.setTimeout(() => {
        notice.classList.add('nh-notice-slide-out');
        window.setTimeout(() => {
          if (notice && notice.parentNode) notice.parentNode.removeChild(notice);
        }, 500);
      }, AUTO_HIDE_MS);
    });
  }

  function initTabLinksReload() {
    const tabs = document.querySelectorAll('.nh-settings-tabs .nav-tab');
    if (!tabs || tabs.length === 0) return;

    tabs.forEach((tabEl) => {
      tabEl.addEventListener('click', () => {
        // allow navigation
      });
    });
  }

  function initTestLinks() {
    document.querySelectorAll('.nh-test-btn').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();

        const tab = btn.dataset.tab || getActiveTabFromUrl() || 'general';

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
  }

  document.addEventListener('DOMContentLoaded', () => {
    initTabLinksReload();
    initTestLinks();
    initAutoHideNotices();
  });
})();

