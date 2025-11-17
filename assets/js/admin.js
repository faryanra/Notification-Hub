// =====================================================
// Notification Hub — Admin JS (Final Clean Version)
// =====================================================

(function () {
  'use strict';

  // =====================================================
  // Single "Mark as Read" AJAX Handler
  // =====================================================
  document.addEventListener('click', e => {
    const a = e.target.closest('.nh-mark-read-ajax');
    if (!a) return;
    e.preventDefault();

    const id = a.dataset.id;
    const nonce = a.dataset.nonce;
    if (!id || !nonce) return;

    const body = new URLSearchParams();
    body.set('action', 'nh_mark_read');
    body.set('id', id);
    body.set('_wpnonce', nonce);

    fetch(nhAdmin.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body
    })
      .then(r => r.json())
      .then(json => {
        if (!json || !json.success) return;
        const row = a.closest('tr');
        if (row) {
          row.classList.remove('nh-row-new');
        }
        if (window.nhNewRows) window.nhNewRows.delete(String(id));
        document.dispatchEvent(new Event('nh:table-updated'));
      })
      .catch(() => {});
  });

  // =====================================================
  // Live Admin Bar Badge Refresh (15s Poll)
  // =====================================================
  let nhLastBadgeCount = -1;

  function refreshNHBadge() {
    const badge = document.querySelector('#wp-admin-bar-nh_unread .ab-label');
    if (!badge || typeof nhAdmin === 'undefined') return;

    fetch(nhAdmin.ajax_url + '?action=nh_get_unread_count', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(json => {
        if (!json || !json.success || !json.data || typeof json.data.count !== 'number') return;
        const count = json.data.count;
        if (count !== nhLastBadgeCount) {
          badge.textContent = count > 0 ? `${count} New` : '0 New';
          nhLastBadgeCount = count;
        }
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
  // Persist "NEW" Row Highlights Between Updates
  // =====================================================
  window.nhNewRows = window.nhNewRows || new Set();

  function rememberNewRows() {
    document.querySelectorAll('tr[data-created]').forEach(tr => {
      const id = tr.querySelector('input[type="checkbox"]')?.value;
      if (!id) return;

      // Only keep rows that PHP already marked as new
      if (tr.classList.contains('nh-row-new')) {
        window.nhNewRows.add(String(id));
      } else {
        window.nhNewRows.delete(String(id));
      }
    });
  }

  document.addEventListener('DOMContentLoaded', rememberNewRows);
  document.addEventListener('nh:table-updated', rememberNewRows);

  // =====================================================
  // Disabled any "Mark All as Seen/Read" auto-handlers
  // =====================================================

})();
