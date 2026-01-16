// Notification Hub — Dashboard Live Refresh (no duplicate rows, server-render swap)

(function () {

  // ---------- Modal (unchanged) ----------

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

  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.nh-open-modal');
    if (!btn) return;
    e.preventDefault();
    const id = btn.dataset.id;
    const nonce = btn.dataset.nonce;
    fetch(`${nhAdmin.ajax_url}?action=nh_view_notification&id=${encodeURIComponent(id)}&_wpnonce=${encodeURIComponent(nonce)}`, {
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          document.getElementById('nh-modal-title').textContent = data.data.source || 'Notification';
          document.getElementById('nh-modal-message').textContent = data.data.message || '';
          document.getElementById('nh-modal-meta').innerHTML = `<small>${data.data.created_at || ''}</small>`;
          openModal();
        } else {
          alert(data?.data?.message || (window.nh_i18n && nh_i18n.load_error) || 'Load failed');
        }
      })
      .catch(() => alert((window.nh_i18n && nh_i18n.request_fail) || 'Request failed'));
  });

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target.matches('.nh-modal__close') || e.target.matches('.nh-modal__backdrop')) closeModal();
    });
  }


  // ---------- Live Refresh ----------

  const tableBody = document.querySelector('#the-list');


  const getRoot  = () => (window.nhREST && nhREST.root)  || '/wp-json/nh/v1/';
  const getNonce = () => (window.nhREST && nhREST.nonce) || '';
  const buildUrl = (p) => (getRoot().endsWith('/') ? getRoot() : getRoot() + '/') + p;


  // Read current IDs from the rendered table (WP_List_Table layout: checkbox column first, ID in 2nd cell)
  const readCurrentIds = () => {
    const ids = new Set();
    document.querySelectorAll('#the-list tr').forEach(tr => {
      const did = tr.getAttribute('data-id');
      if (did) { ids.add(String(did)); return; }
      const idCell = tr.querySelector('td:nth-child(2)');
      if (idCell) {
        const val = idCell.textContent.trim();
        if (val) ids.add(val);
      }
    });
    return ids;
  };


  // Swap tbody with server-rendered one (no duplicate rows, server-render swap)
  function refreshTableFromServer(newCountHint = 0) {
      const beforeIds = readCurrentIds();


      return fetch(window.location.href, { credentials: 'same-origin' })
          .then(r => r.text())
          .then(html => {
              const doc = new DOMParser().parseFromString(html, 'text/html');
              const newBody = doc.querySelector('#the-list');


              // Refresh filter tabs
              const newViews = doc.querySelector('.subsubsub');
              const oldViews = document.querySelector('.subsubsub');
              if (newViews && oldViews) {
                  oldViews.innerHTML = newViews.innerHTML;
              }


              if (!newBody || !tableBody) return;


              const afterIds = new Set();
              newBody.querySelectorAll('tr').forEach(tr => {
                  const did = tr.getAttribute('data-id')
                      || tr.querySelector('td:nth-child(2)')?.textContent.trim()
                      || '';
                  if (did) afterIds.add(did);
              });


              tableBody.innerHTML = newBody.innerHTML;


              document.dispatchEvent(new Event('nh_refresh_done'));


              tableBody.querySelectorAll('tr').forEach(tr => {
                  const did = tr.getAttribute('data-id')
                      || tr.querySelector('td:nth-child(2)')?.textContent.trim()
                      || '';
                  if (did && !beforeIds.has(did)) {
                      tr.classList.add('nh-row-anim');
                      setTimeout(() => tr.classList.remove('nh-row-anim'), 2000);
                  }
              });
          })
          .catch(() => {});
  }


  // Always global
  window.nhRefreshTable = refreshTableFromServer;



  let lastTs = null;
  const initLastTs = () => {
    const firstRowTime = document.querySelector('#the-list tr:first-child td:nth-child(7)')?.textContent.trim();
    lastTs = firstRowTime || (window.nhREST && nhREST.server_now) || null;
  };


  const poll = () => {
    let url = buildUrl('notifications');
    if (lastTs) url += (url.includes('?') ? '&' : '?') + 'since=' + encodeURIComponent(lastTs);


    fetch(url, {
      headers: { 'X-WP-Nonce': getNonce() },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(json => {
        if (!json?.ok || !Array.isArray(json.data)) return;
        if (json.data.length === 0) return;
        refreshTableFromServer(json.data.length);
        lastTs = json.data[0]?.created_at || lastTs;
      })
      .catch(() => {})
      .finally(() => setTimeout(poll, 15000));
  };


  document.addEventListener('DOMContentLoaded', () => {
    initLastTs();
    poll();
  });

})();


(function () {

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
    const sec = diff / 1000, min = sec / 60, hour = min / 60, day = hour / 24;


    const sameDay = now.toDateString() === date.toDateString();
    const yesterday = new Date(now); yesterday.setDate(now.getDate() - 1);
    const isYesterday = date.toDateString() === yesterday.toDateString();


    if (sec < 60) return 'Now';
    if (sameDay && min < 60) return `${Math.floor(min)} min ago`;
    if (sameDay && hour < 24) return `${Math.floor(hour)} hour${hour >= 2 ? 's' : ''} ago`;
    if (isYesterday) return `Yesterday ${date.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' })}`;
    if (day < 7)
      return `${date.toLocaleDateString([], { weekday:'long' })} ${date.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' })}`;
    return date.toLocaleDateString([], { month:'short', day:'numeric', year:'numeric' });
  }


  function findCreatedIndex() {
    const headers = document.querySelectorAll('table thead th');
    let index = -1;
    headers.forEach((th, i) => {
      if (th.textContent.trim().toLowerCase() === 'created') index = i + 2;
    });
    return index > 0 ? index : 8;
  }


  function updateCreatedTimes() {
      // اول سعی کن با class پیدا کنی (دقیق‌تر)
      const spans = document.querySelectorAll('.nh-created-time');
      if (spans.length > 0) {
          spans.forEach(span => {
              const raw = span.getAttribute('data-raw') || span.textContent.trim();
              if (!/^\d{4}-\d{2}-\d{2}/.test(raw)) return;
              const nice = humanTime(raw);
              if (nice !== raw) span.textContent = nice;
          });
      } else {
          // Fallback: اگر class نبود، از nth-child استفاده کن
          const colIndex = findCreatedIndex();
          document.querySelectorAll(`#the-list tr td:nth-child(${colIndex})`).forEach(td => {
              const raw = td.textContent.trim();
              if (!/^\d{4}-\d{2}-\d{2}/.test(raw)) return;
              const nice = humanTime(raw);
              if (nice !== raw) td.textContent = nice;
          });
      }
  }


  document.addEventListener('DOMContentLoaded', updateCreatedTimes);
  document.addEventListener('nh_refresh_done', updateCreatedTimes);
  setInterval(updateCreatedTimes, 60000);

})();


jQuery(document).on('click', '.nh-actions-more', function(e){
  e.preventDefault();
  const $row = jQuery(this).closest('td, .column-actions');
  $row.find('.nh-actions-secondary').toggle();
});


/* ============================================================

   Notification Hub — AJAX Actions (Read / Unread / Important)

============================================================ */


(function ($) {


    // Generic AJAX handler
    function nhAjax(action, id) {
        return $.post(nhAdmin.ajax_url, {
            action: action,
            id: id,
            _wpnonce: nhAdmin.nonce
        });
    }


    // ---- Mark as Read ----
    $(document).on('click', '.nh-mark-read', function (e) {
        e.preventDefault();
        const id = $(this).data('id');


        nhAjax('nh_mark_read', id).done(function (res) {
            if (res.success) {
                document.dispatchEvent(new Event('nh_refresh_force'));
            } else {
                alert(res.data?.message || 'Failed');
            }
        });
    });


    // ---- Mark as Unread ----
    $(document).on('click', '.nh-mark-unread', function (e) {
        e.preventDefault();
        const id = $(this).data('id');


        nhAjax('nh_mark_unread', id).done(function (res) {
            if (res.success) {
                document.dispatchEvent(new Event('nh_refresh_force'));
            } else {
                alert(res.data?.message || 'Failed');
            }
        });
    });


    // ---- Mark Important ----
    $(document).on('click', '.nh-mark-important', function (e) {
        e.preventDefault();
        const id = $(this).data('id');


        nhAjax('nh_mark_important', id).done(function (res) {
            if (res.success) {
                document.dispatchEvent(new Event('nh_refresh_force'));
            } else {
                alert(res.data?.message || 'Failed');
            }
        });
    });


    // ---- Unmark Important ----
    $(document).on('click', '.nh-unmark-important', function (e) {
        e.preventDefault();
        const id = $(this).data('id');


        nhAjax('nh_unmark_important', id).done(function (res) {
            if (res.success) {
                document.dispatchEvent(new Event('nh_refresh_force'));
            } else {
                alert(res.data?.message || 'Failed');
            }
        });
    });


    // ---- Delete Notification ----
    $(document).on('click', '.nh-delete-notification', function (e) {
        e.preventDefault();
        const id = $(this).data('id');

        if (!confirm('Delete this notification?')) return;

        nhAjax('nh_delete_notification', id).done(function (res) {
            if (res.success) {
                document.dispatchEvent(new Event('nh_refresh_force'));
            } else {
                alert(res.data?.message || 'Failed');
            }
        });
    });


    // ---- Force table refresh ----
    document.addEventListener('nh_refresh_force', function () {


        const loader = document.getElementById('nh-table-loader');
        if (loader) loader.classList.add('active');


        if (typeof window.nhRefreshTable === 'function') {
            window.nhRefreshTable().finally(() => {
                if (loader) loader.classList.remove('active');
            });
        }
    });


})(jQuery);

/* ============================================================
   Bulk Actions via AJAX with Loading Indicator
============================================================ */
(function ($) {
    // Add loading indicator HTML after bulk action buttons
    const $bulkActions = $('.tablenav.top .bulkactions');
    if ($bulkActions.length && !$('#nh-bulk-loader').length) {
        $bulkActions.after(
            '<span id="nh-bulk-loader" class="nh-bulk-loading">' +
            '<span class="spinner is-active"></span>' +
            '<span style="margin-left:5px;">Processing...</span>' +
            '</span>'
        );
    }

    // Intercept bulk action form submission
    $('#doaction, #doaction2').on('click', function (e) {
        const $form = $(this).closest('form');
        const $select = $(this).attr('id') === 'doaction' 
            ? $form.find('select[name="action"]') 
            : $form.find('select[name="action2"]');
        const action = $select.val();
        
        // If action is empty or default, let WordPress handle it
        if (!action || action === '-1') return;
        
        // Get selected IDs
        const ids = [];
        $form.find('input[name="ids[]"]:checked').each(function () {
            ids.push($(this).val());
        });
        
        if (ids.length === 0) {
            alert('Please select at least one notification.');
            e.preventDefault();
            return false;
        }
        
        // Prevent default form submission
        e.preventDefault();
        
        // Confirm for delete action
        if (action === 'delete' && !confirm('Are you sure you want to delete selected notifications?')) {
            return false;
        }
        
        // Show loading indicator
        const $loader = $('#nh-bulk-loader');
        $loader.addClass('active');
        
        // Send AJAX request
        $.post(nhAdmin.ajax_url, {
            action: 'nh_bulk_action',
            bulk_action: action,
            ids: ids,
            _wpnonce: nhAdmin.nonce
        })
        .done(function (res) {
            if (res.success) {
                // Refresh table
                if (typeof window.nhRefreshTable === 'function') {
                    window.nhRefreshTable().finally(() => {
                        $loader.removeClass('active');
                    });
                } else {
                    location.reload();
                }
            } else {
                $loader.removeClass('active');
                alert(res.data?.message || 'Bulk action failed');
            }
        })
        .fail(function () {
            $loader.removeClass('active');
            alert('Request failed');
        });
        
        return false;
    });
})(jQuery);