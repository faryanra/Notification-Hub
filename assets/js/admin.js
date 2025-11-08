// ===============================================
// Notification Hub — Admin JS (v1.5.3 Live Sync)
// ===============================================

// --- Tabs ---
document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('.nav-tab');
  const panes = document.querySelectorAll('.nh-tab');

  tabs.forEach(t => t.addEventListener('click', e => {
    e.preventDefault();
    tabs.forEach(x => x.classList.remove('nav-tab-active'));
    t.classList.add('nav-tab-active');
    panes.forEach(p => p.style.display = 'none');
    const href = t.getAttribute('href');
    const id = href.replace('?page=nh_settings&tab=', 'nh-tab-');
    const pane = document.getElementById(id);
    if (pane) pane.style.display = 'block';
    history.replaceState(null, '', href);
  }));

  // Safe redirect for test buttons
  document.querySelectorAll('.nh-test-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const tab = btn.dataset.tab || 'general';
      const href = new URL(btn.href);
      href.searchParams.set('tab', tab);
      window.location.href = href.toString();
    });
  });
});

// ===============================================
// 🔁 Live Badge Refresh Logic
// ===============================================

let nhLastBadgeCount = -1;

function refreshNHBadge() {
  const badge = document.querySelector('#wp-admin-bar-nh_unread .ab-label');
  if (!badge || typeof nhAdmin === 'undefined') return;

  fetch(nhAdmin.ajax_url + '?action=nh_get_unread_count', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(json => {
      if (!json.success || !json.data || typeof json.data.count !== 'number') return;
      const count = json.data.count;
      if (count !== nhLastBadgeCount) {
        badge.textContent = count > 0 ? `${count} New` : '0 New';
        nhLastBadgeCount = count;
      }
    })
    .catch(() => { /* silent fail */ });
}

// Run once at load
document.addEventListener('DOMContentLoaded', refreshNHBadge);

// Poll every 10 seconds (optimized for admin)
setInterval(refreshNHBadge, 10000);

// ===============================================
// 🖱 Manual "Mark all as seen" Button Handler
// ===============================================
document.addEventListener('click', function (e) {
  const btn = e.target.closest('a[href*="action=nh_mark_all_seen"], a[href*="action=nh_mark_all_read"]');
  if (!btn) return;
  e.preventDefault();

  // Call AJAX endpoint safely
  fetch(nhAdmin.ajax_url + '?action=nh_mark_all_read', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(json => {
      if (json.success) {
        nhLastBadgeCount = 0;
        const badge = document.querySelector('#wp-admin-bar-nh_unread .ab-label');
        if (badge) badge.textContent = '0 New';

        // optional: reload dashboard table for updated view
        if (window.location.href.includes('page=nh-dashboard')) {
          setTimeout(() => window.location.reload(), 800);
        }
      }
    })
    .catch(() => { /* ignore errors */ });
});
