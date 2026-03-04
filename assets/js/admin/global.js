// =====================================================
// Notification Hub — Admin JS (global)
//
// @package Notification_Hub
// @since 1.6.2
// =====================================================

(function () {
  'use strict';

  const t = (key, fallback) =>
    (window.nhAdmin && window.nhAdmin.i18n && window.nhAdmin.i18n[key]) ||
    fallback;

  // Global confirm handler.
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a.nh-confirm[data-confirm]');
    if (!a) return;

    const msg = a.getAttribute('data-confirm') || '';
    if (msg && !confirm(msg)) {
      e.preventDefault();
    }
  });

  // Settings tabs: keep Save button state in sync.
  function updateSettingsSaveVisibility(activeTab) {
    const tabs = document.querySelector('.nh-settings-tabs');
    if (!tabs) return;

    const proAddonActive = tabs.getAttribute('data-pro-addon') === '1';

    const form = document.querySelector('form[action*="options.php"]');
    if (!form) return;

    const submit = form.querySelector('p.submit');
    if (!submit) return;

    const shouldHide = activeTab === 'pro' && !proAddonActive;
    submit.style.display = shouldHide ? 'none' : '';
  }

  document.addEventListener('click', (e) => {
    const tab = e.target.closest('.nh-settings-tabs a.nav-tab[data-tab]');
    if (!tab) return;

    const tabKey = tab.getAttribute('data-tab') || '';
    if (!tabKey) return;

    updateSettingsSaveVisibility(tabKey);
  });

  document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelector('.nh-settings-tabs');
    const active = tabs?.getAttribute('data-active-tab') || 'general';
    updateSettingsSaveVisibility(active);
  });

  // Single "Mark as Read" AJAX Handler.
  document.addEventListener('click', (e) => {
    const a = e.target.closest('.nh-mark-read-ajax');
    if (!a) return;

    e.preventDefault();

    const id = a.dataset.id;
    const nonce = a.dataset.nonce;
    if (!id || !nonce || !window.nhAdmin) return;

    const body = new URLSearchParams();
    body.set('action', 'nh_mark_read');
    body.set('id', id);
    body.set('_wpnonce', nonce);

    fetch(window.nhAdmin.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body,
    })
      .then((r) => r.json())
      .then((json) => {
        if (!json || !json.success) return;

        const row = a.closest('tr');
        if (row) row.classList.remove('nh-row-new');

        if (window.nhNewRows) window.nhNewRows.delete(String(id));
        document.dispatchEvent(new Event('nh:table-updated'));
      })
      .catch(() => {});
  });

  // Live Admin Bar Badge Refresh.
  let nhLastBadgeCount = -1;

  function setBadgeText(badgeEl, count) {
    const label = t('badge_new', 'New');
    badgeEl.textContent = ` ${count} ${label}`;
  }

  function refreshNHBadge() {
    if (!window.nhAdmin) return;

    const badgeItem = document.querySelector('#wp-admin-bar-nh_unread');
    const badge = badgeItem?.querySelector('.ab-label');
    if (!badgeItem || !badge) return;

    const url = new URL(window.nhAdmin.ajax_url, window.location.origin);
    url.searchParams.set('action', 'nh_get_unread_count');
    url.searchParams.set('_wpnonce', window.nhAdmin.nonce);

    fetch(url.toString(), { credentials: 'same-origin' })
      .then((r) => r.json())
      .then((json) => {
        const count =
          json?.success && json?.data && typeof json.data.count === 'number'
            ? json.data.count
            : null;

        if (count === null) return;

        if (nhLastBadgeCount >= 0 && count > nhLastBadgeCount) {
          badgeItem.classList.add('nh-has-new');
          setTimeout(() => badgeItem.classList.remove('nh-has-new'), 2500);
        }

        setBadgeText(badge, count);
        nhLastBadgeCount = count;
      })
      .catch(() => {});
  }

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') refreshNHBadge();
  });

  if (window.nhAdmin) {
    setInterval(refreshNHBadge, 15000);
    document.addEventListener('nh:table-updated', refreshNHBadge);
    document.addEventListener('DOMContentLoaded', refreshNHBadge);
  }

  // Persist "NEW" row highlights between updates.
  window.nhNewRows = window.nhNewRows || new Set();

  function rememberNewRows() {
    document.querySelectorAll('tr[data-id]').forEach((tr) => {
      const id = tr.getAttribute('data-id');
      if (!id) return;

      if (tr.classList.contains('nh-row-new')) window.nhNewRows.add(String(id));
      else window.nhNewRows.delete(String(id));
    });
  }

  document.addEventListener('DOMContentLoaded', rememberNewRows);
  document.addEventListener('nh:table-updated', rememberNewRows);
})();
