// Notification Hub — Dashboard (live refresh + actions + filters)
//
// @package Notification_Hub
// @since 1.6.2

(function () {
  'use strict';

  // Only run on NH dashboard pages (avoid affecting other admin pages).
  const isNhDashboard =
    document.body.classList.contains('toplevel_page_nh-dashboard') ||
    (window.location.search || '').includes('page=nh-dashboard');

  if (!isNhDashboard) return;

  const t = (key, fallback) => (window.nh_i18n && nh_i18n[key]) || fallback;

  // =====================================================
  // Filters (moved from inline PHP)
  // =====================================================
  function initFilters() {
    const btn = document.getElementById('nh-apply-filters');
    if (!btn) return;

    btn.addEventListener('click', function (e) {
      e.preventDefault();

      const params = new URLSearchParams(window.location.search);

      const getVal = (id) => {
        const el = document.getElementById(id);
        return el ? el.value : '';
      };

      const filters = {
        filter_created: getVal('nh-filter-created'),
        filter_source: getVal('nh-filter-source'),
        filter_type: getVal('nh-filter-type'),
        filter_priority: getVal('nh-filter-priority'),
        filter_read_status: getVal('nh-filter-read-status'),
      };

      Object.entries(filters).forEach(([key, val]) => {
        val ? params.set(key, val) : params.delete(key);
      });

      window.location.search = params.toString();
    });
  }

  // =====================================================
  // Modal (notification preview)
  // =====================================================
  const modal = document.getElementById('nh-modal');
  const closeBtn = modal?.querySelector('.nh-modal__close');

  const openModal = () => {
    if (!modal) return;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    closeBtn?.focus();
  };

  const closeModal = () => {
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
  };

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });

  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.nh-open-modal');
    if (!btn) return;

    e.preventDefault();

    const id = btn.dataset.id;
    const nonce = btn.dataset.nonce;

    fetch(
      `${nhAdmin.ajax_url}?action=nh_view_notification&id=${encodeURIComponent(
        id
      )}&_wpnonce=${encodeURIComponent(nonce)}`,
      { credentials: 'same-origin' }
    )
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          document.getElementById('nh-modal-title').textContent =
            data.data.source || t('modal_default_title', 'Notification');
          document.getElementById('nh-modal-message').textContent =
            data.data.message || '';
          document.getElementById('nh-modal-meta').innerHTML = `<small>${
            data.data.created_at || ''
          }</small>`;
          openModal();
        } else {
          alert(data?.data?.message || t('load_error', 'Load failed.'));
        }
      })
      .catch(() => alert(t('request_fail', 'Request failed.')));
  });

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (
        e.target.matches('.nh-modal__close') ||
        e.target.matches('.nh-modal__backdrop')
      ) {
        closeModal();
      }
    });
  }

  // =====================================================
  // Live refresh (server-render swap, no duplicates)
  // =====================================================
  const tableBody = document.querySelector('#the-list');

  const getRoot = () => (window.nhREST && nhREST.root) || '/wp-json/nh/v1/';
  const getNonce = () => (window.nhREST && nhREST.nonce) || '';
  const buildUrl = (p) =>
    (getRoot().endsWith('/') ? getRoot() : getRoot() + '/') + p;

  // Read IDs from current rendered table
  const readCurrentIds = () => {
    const ids = new Set();

    document.querySelectorAll('#the-list tr').forEach((tr) => {
      const did = tr.getAttribute('data-id');
      if (did) {
        ids.add(String(did));
        return;
      }

      const idCell = tr.querySelector('td:nth-child(2)');
      if (idCell) {
        const val = idCell.textContent.trim();
        if (val) ids.add(val);
      }
    });

    return ids;
  };

  function refreshTableFromServer() {
    const beforeIds = readCurrentIds();

    return fetch(window.location.href, { credentials: 'same-origin' })
      .then((r) => r.text())
      .then((html) => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const newBody = doc.querySelector('#the-list');

        // Refresh WP list table view tabs (All / Active / Archived...)
        const newViews = doc.querySelector('.subsubsub');
        const oldViews = document.querySelector('.subsubsub');
        if (newViews && oldViews) oldViews.innerHTML = newViews.innerHTML;

        if (!newBody || !tableBody) return;

        tableBody.innerHTML = newBody.innerHTML;

        // Let other modules update themselves (created-time, etc.)
        document.dispatchEvent(new Event('nh_refresh_done'));

        // Highlight new rows only
        tableBody.querySelectorAll('tr').forEach((tr) => {
          const did =
            tr.getAttribute('data-id') ||
            tr.querySelector('td:nth-child(2)')?.textContent.trim() ||
            '';

          if (did && !beforeIds.has(did)) {
            tr.classList.add('nh-row-anim');
            setTimeout(() => tr.classList.remove('nh-row-anim'), 2000);
          }
        });
      })
      .catch(() => {});
  }

  // Expose globally (used by other modules / events)
  window.nhRefreshTable = refreshTableFromServer;

  let lastTs = null;

  // Note: this still depends on a fixed column index. If you want, next step is
  // to make created_at a stable data-attr and read from that instead.
  const initLastTs = () => {
    const firstRowTime = document
      .querySelector('#the-list tr:first-child td:nth-child(7)')
      ?.textContent.trim();

    lastTs = firstRowTime || (window.nhREST && nhREST.server_now) || null;
  };

  const poll = () => {
    let url = buildUrl('notifications');
    if (lastTs) url += (url.includes('?') ? '&' : '?') + 'since=' + encodeURIComponent(lastTs);

    fetch(url, {
      headers: { 'X-WP-Nonce': getNonce() },
      credentials: 'same-origin',
    })
      .then((r) => r.json())
      .then((json) => {
        if (!json?.ok || !Array.isArray(json.data)) return;
        if (json.data.length === 0) return;

        refreshTableFromServer();
        lastTs = json.data[0]?.created_at || lastTs;
      })
      .catch(() => {})
      .finally(() => setTimeout(poll, 15000));
  };

  // =====================================================
  // Created time humanizer
  // =====================================================
  function parseMysqlDateToLocal(dateStr) {
    const normalized = dateStr.trim().replace(' ', 'T');
    const utcDate = new Date(normalized + 'Z');
    if (isNaN(utcDate.getTime())) return null;
    return utcDate;
  }

  function humanTime(dateStr) {
    const date = parseMysqlDateToLocal(dateStr);
    if (!date) return dateStr;

    const now = new Date();
    const diff = now - date;
    const sec = diff / 1000,
      min = sec / 60,
      hour = min / 60,
      day = hour / 24;

    const sameDay = now.toDateString() === date.toDateString();
    const y = new Date(now);
    y.setDate(now.getDate() - 1);
    const isYesterday = date.toDateString() === y.toDateString();

    if (sec < 60) return t('time_now', 'Now');
    if (sameDay && min < 60) return `${Math.floor(min)} ${t('time_min_ago', 'min ago')}`;
    if (sameDay && hour < 24) return `${Math.floor(hour)} ${t('time_hour_ago', 'hour(s) ago')}`;
    if (isYesterday) {
      return `${t('time_yesterday', 'Yesterday')} ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    }
    if (day < 7) {
      return `${date.toLocaleDateString([], { weekday: 'long' })} ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    }
    return date.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function updateCreatedTimes() {
    const spans = document.querySelectorAll('.nh-created-time');
    if (spans.length === 0) return;

    spans.forEach((span) => {
      const raw = span.getAttribute('data-raw') || span.textContent.trim();
      if (!/^\d{4}-\d{2}-\d{2}/.test(raw)) return;

      const nice = humanTime(raw);
      if (nice !== raw) span.textContent = nice;
    });
  }

  // =====================================================
  // Actions + Bulk (jQuery)
  // =====================================================
  function initJQueryActions() {
    if (!window.jQuery) return;
    const $ = window.jQuery;

    function nhAjax(action, id) {
      return $.post(nhAdmin.ajax_url, {
        action,
        id,
        _wpnonce: nhAdmin.nonce,
      });
    }

    $(document).on('click', '.nh-mark-read', function (e) {
      e.preventDefault();
      const id = $(this).data('id');

      nhAjax('nh_mark_read', id).done((res) => {
        if (res.success) document.dispatchEvent(new Event('nh_refresh_force'));
        else alert(res.data?.message || t('failed', 'Failed.'));
      });
    });

    $(document).on('click', '.nh-mark-unread', function (e) {
      e.preventDefault();
      const id = $(this).data('id');

      nhAjax('nh_mark_unread', id).done((res) => {
        if (res.success) document.dispatchEvent(new Event('nh_refresh_force'));
        else alert(res.data?.message || t('failed', 'Failed.'));
      });
    });

    $(document).on('click', '.nh-mark-important', function (e) {
      e.preventDefault();
      const id = $(this).data('id');

      nhAjax('nh_mark_important', id).done((res) => {
        if (res.success) document.dispatchEvent(new Event('nh_refresh_force'));
        else alert(res.data?.message || t('failed', 'Failed.'));
      });
    });

    $(document).on('click', '.nh-unmark-important', function (e) {
      e.preventDefault();
      const id = $(this).data('id');

      nhAjax('nh_unmark_important', id).done((res) => {
        if (res.success) document.dispatchEvent(new Event('nh_refresh_force'));
        else alert(res.data?.message || t('failed', 'Failed.'));
      });
    });

    $(document).on('click', '.nh-delete-notification', function (e) {
      e.preventDefault();
      const id = $(this).data('id');

      if (!confirm(t('confirm_delete_one', 'Delete this notification?'))) return;

      nhAjax('nh_delete_notification', id).done((res) => {
        if (res.success) document.dispatchEvent(new Event('nh_refresh_force'));
        else alert(res.data?.message || t('failed', 'Failed.'));
      });
    });

    // Force refresh + table loader
    document.addEventListener('nh_refresh_force', function () {
      const loader = document.getElementById('nh-table-loader');
      if (loader) loader.classList.add('active');

      if (typeof window.nhRefreshTable === 'function') {
        window.nhRefreshTable().finally(() => {
          if (loader) loader.classList.remove('active');
        });
      }
    });

    // Bulk loader (no inline styles)
    const $bulkActions = $('.tablenav.top .bulkactions');
    if ($bulkActions.length && !$('#nh-bulk-loader').length) {
      $bulkActions.after(
        '<span id="nh-bulk-loader" class="nh-bulk-loading">' +
          '<span class="spinner is-active"></span>' +
          '<span class="nh-bulk-loading__text">' +
          t('processing', 'Processing...') +
          '</span>' +
          '</span>'
      );
    }

    // Intercept bulk action submission (top + bottom)
    $('#doaction, #doaction2').on('click', function (e) {
      const $form = $(this).closest('form');
      const $select =
        $(this).attr('id') === 'doaction'
          ? $form.find('select[name="action"]')
          : $form.find('select[name="action2"]');

      const action = $select.val();

      // Default WP behavior
      if (!action || action === '-1') return;

      // Collect ids
      const ids = [];
      $form.find('input[name="ids[]"]:checked').each(function () {
        ids.push($(this).val());
      });

      if (ids.length === 0) {
        alert(t('select_at_least_one', 'Please select at least one notification.'));
        e.preventDefault();
        return false;
      }

      e.preventDefault();

      if (
        action === 'delete' &&
        !confirm(t('confirm_delete_bulk', 'Are you sure you want to delete selected notifications?'))
      ) {
        return false;
      }

      const $loader = $('#nh-bulk-loader');
      $loader.addClass('active');

      $.post(nhAdmin.ajax_url, {
        action: 'nh_bulk_action',
        bulk_action: action,
        ids,
        _wpnonce: nhAdmin.nonce,
      })
        .done(function (res) {
          if (res.success) {
            if (typeof window.nhRefreshTable === 'function') {
              window.nhRefreshTable().finally(() => $loader.removeClass('active'));
            } else {
              location.reload();
            }
          } else {
            $loader.removeClass('active');
            alert(res.data?.message || t('bulk_failed', 'Bulk action failed.'));
          }
        })
        .fail(function () {
          $loader.removeClass('active');
          alert(t('request_fail', 'Request failed.'));
        });

      return false;
    });
  }

  // =====================================================
  // Init
  // =====================================================
  document.addEventListener('DOMContentLoaded', () => {
    initFilters();
    initLastTs();
    poll();
    updateCreatedTimes();
    initJQueryActions();
  });

  document.addEventListener('nh_refresh_done', updateCreatedTimes);
  setInterval(updateCreatedTimes, 60000);
})();
