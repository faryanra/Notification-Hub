// NH v1.3.0 — Dashboard JS (Modal preview + AJAX view)

document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('nh-modal');
  const title = document.getElementById('nh-modal-title');
  const msg   = document.getElementById('nh-modal-message');
  const meta  = document.getElementById('nh-modal-meta');

  function openModal() { 
    if (modal) modal.style.display = 'block'; 
  }
  function closeModal() { 
    if (modal) modal.style.display = 'none'; 
  }

  document.body.addEventListener('click', e => {
    const a = e.target.closest('.nh-view');
    if (a) {
      e.preventDefault();
      const id = a.dataset.id;
      const nonce = a.dataset.nonce;
      if (!nhAdmin || !nhAdmin.ajax_url) {
        alert('AJAX URL not found');
        return;
      }

      fetch(`${nhAdmin.ajax_url}?action=nh_view_notification&id=${encodeURIComponent(id)}&_wpnonce=${encodeURIComponent(nonce)}`, {
        credentials: 'same-origin'
      })
        .then(r => r.json())
        .then(data => {
          if (data && data.success) {
            if (title) title.textContent = data.data.source || 'Notification';
            if (msg) msg.textContent = data.data.message || '';
            if (meta) meta.innerHTML = `<small>${data.data.created_at || ''}</small>`;
            openModal();
          } else {
            alert(data?.data?.message || 'Error loading notification');
          }
        })
        .catch(() => alert('Request failed'));
    }

    if (
      e.target.matches('.nh-modal__close') ||
      e.target.matches('.nh-modal__backdrop')
    ) {
      closeModal();
    }
  });
});
