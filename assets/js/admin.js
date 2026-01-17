// =====================================================
// Notification Hub — Admin JS
//
// @package Notification_Hub
// @since 1.6.2
// =====================================================

(function () {
  'use strict';

  const t = (key, fallback) =>
    (window.nhAdmin && nhAdmin.i18n && nhAdmin.i18n[key]) || fallback;

  // =====================================================
  // Global confirm handler (no inline onclick)
  // Usage: <a class="nh-confirm" data-confirm="...">
  // =====================================================
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a.nh-confirm[data-confirm]');
    if (!a) return;

    const msg = a.getAttribute('data-confirm') || '';
    if (msg && !confirm(msg)) {
      e.preventDefault();
    }
  });

  // =====================================================
  // Single "Mark as Read" AJAX Handler
  // =====================================================
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

    fetch(nhAdmin.ajax_url, {
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

  // =====================================================
  // Live Admin Bar Badge Refresh (no inline styles)
  // Requires CSS: #wp-admin-bar-nh_unread.nh-has-new ...
  // =====================================================
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

    const url = new URL(nhAdmin.ajax_url, window.location.origin);
    url.searchParams.set('action', 'nh_get_unread_count');
    url.searchParams.set('_wpnonce', nhAdmin.nonce);

    fetch(url.toString(), { credentials: 'same-origin' })
      .then((r) => r.json())
      .then((json) => {
        const count =
          json?.success && json?.data && typeof json.data.count === 'number'
            ? json.data.count
            : null;

        if (count === null) return;

        // Add a class when new items arrive (CSS handles the animation).
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

  setInterval(refreshNHBadge, 15000);
  document.addEventListener('nh:table-updated', refreshNHBadge);
  document.addEventListener('DOMContentLoaded', refreshNHBadge);

  // =====================================================
  // Persist "NEW" row highlights between updates
  // =====================================================
  window.nhNewRows = window.nhNewRows || new Set();

  function rememberNewRows() {
    document.querySelectorAll('tr[data-created]').forEach((tr) => {
      const id = tr.querySelector('input[type="checkbox"]')?.value;
      if (!id) return;

      if (tr.classList.contains('nh-row-new')) window.nhNewRows.add(String(id));
      else window.nhNewRows.delete(String(id));
    });
  }

  document.addEventListener('DOMContentLoaded', rememberNewRows);
  document.addEventListener('nh:table-updated', rememberNewRows);
})();
