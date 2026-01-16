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
// Live Admin Bar Badge Refresh with Red Alert
// =====================================================
  let nhLastBadgeCount = -1;

  function refreshNHBadge() {
    const badgeItem = document.querySelector('#wp-admin-bar-nh_unread');
    const badge = badgeItem?.querySelector('.ab-label');
    const icon = badgeItem?.querySelector('.ab-icon.dashicons-bell');
    
    if (!badge || !icon || typeof nhAdmin === 'undefined') return;

    fetch(nhAdmin.ajax_url + '?action=nh_get_unread_count', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(json => {
        if (!json || !json.success || !json.data || typeof json.data.count !== 'number') return;
        const count = json.data.count;
        
        // Detect new notifications and animate
        if (nhLastBadgeCount >= 0 && count > nhLastBadgeCount) {
          icon.style.color = '#d63638';
          icon.style.transform = 'scale(1.2)';
          icon.style.transition = 'all 0.3s ease';
          
          setTimeout(() => {
            icon.style.transform = 'scale(1)';
            setTimeout(() => {
              icon.style.color = '';
            }, 2000);
          }, 300);
        }
        
        badge.textContent = count > 0 ? ` ${count} New` : ' 0 New';
        nhLastBadgeCount = count;
      })
      .catch(() => {});
  }

  // Refresh when user comes back to tab
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') refreshNHBadge();
  });

  // Refresh every 15 seconds
  setInterval(refreshNHBadge, 15000);

  // Refresh after table updates
  document.addEventListener('nh:table-updated', refreshNHBadge);

  // Initial load
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