// Dashboard JS (Modal Preview + UI Refresh)

(function () {
  const modal = document.getElementById('nh-modal');
  const closeBtn = modal?.querySelector('.nh-modal__close');

  const openModal = () => {
    if (!modal) return;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    if (closeBtn) closeBtn.focus();
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
    const btn = e.target.closest('.nh-view');
    if (btn) {
      e.preventDefault();
      const id = btn.dataset.id;
      const nonce = btn.dataset.nonce;

      fetch(`${nhAdmin.ajax_url}?action=nh_view_notification&id=${encodeURIComponent(id)}&_wpnonce=${encodeURIComponent(nonce)}`, {
        credentials: 'same-origin'
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            document.getElementById('nh-modal-title').textContent = data.data.source || 'Notification';
            document.getElementById('nh-modal-message').textContent = data.data.message || '';
            document.getElementById('nh-modal-meta').innerHTML = `<small>${data.data.created_at || ''}</small>`;
            openModal();
          } else {
            alert(data?.data?.message || nh_i18n.load_error);
          }
        })
        .catch(() => alert(nh_i18n.request_fail));
    }

    if (e.target.matches('.nh-modal__close') || e.target.matches('.nh-modal__backdrop')) {
      closeModal();
    }
  });
})();
