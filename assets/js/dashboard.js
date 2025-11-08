
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

  // Swap tbody with server-rendered one (no markup drift)
  const refreshTableFromServer = (newCountHint = 0) => {
    const beforeIds = readCurrentIds();
    return fetch(window.location.href, { credentials: 'same-origin' })
      .then(r => r.text())
      .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const newBody = doc.querySelector('#the-list');
        if (!newBody || !tableBody) return;

        const afterIds = new Set();
        newBody.querySelectorAll('tr').forEach(tr => {
          const did = tr.getAttribute('data-id') || (tr.querySelector('td:nth-child(2)')?.textContent.trim() || '');
          if (did) afterIds.add(did);
        });

        tableBody.innerHTML = newBody.innerHTML;

        document.dispatchEvent(new Event('nh_refresh_done'));

        tableBody.querySelectorAll('tr').forEach(tr => {
          const did = tr.getAttribute('data-id') || (tr.querySelector('td:nth-child(2)')?.textContent.trim() || '');
          if (did && !beforeIds.has(did)) {
            tr.classList.add('nh-row-anim');
            setTimeout(() => tr.classList.remove('nh-row-anim'), 2000);
          }
        });
      })
      .catch(() => {});
  };

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
    const colIndex = findCreatedIndex();
    document.querySelectorAll(`#the-list tr td:nth-child(${colIndex})`).forEach(td => {
      const raw = td.textContent.trim();
      if (!/^\d{4}-\d{2}-\d{2}/.test(raw)) return;
      const nice = humanTime(raw);
      if (nice !== raw) td.textContent = nice;
    });
  }

  document.addEventListener('DOMContentLoaded', updateCreatedTimes);
  document.addEventListener('nh_refresh_done', updateCreatedTimes);
  setInterval(updateCreatedTimes, 60000);
})();
