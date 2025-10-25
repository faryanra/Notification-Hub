// Dashboard JS (Modal Preview + UI Refresh)

(function () {
  /** Modal Elements */
  const modal = document.getElementById('nh-modal');
  const title = document.getElementById('nh-modal-title');
  const message = document.getElementById('nh-modal-message');
  const meta = document.getElementById('nh-modal-meta');

  /** Open and close modal functions */
  const openModal = () => modal && (modal.style.display = 'block');
  const closeModal = () => modal && (modal.style.display = 'none');

  /** Handle View button click */
  document.body.addEventListener('click', function (e) {
    const btn = e.target.closest('.nh-view');
    if (btn) {
      e.preventDefault();
      const id = btn.dataset.id;
      const nonce = btn.dataset.nonce;

      if (!window.nhAdmin || !nhAdmin.ajax_url) {
        alert(nh_i18n?.no_ajax || 'AJAX URL not available.');
        return;
      }

      fetch(`${nhAdmin.ajax_url}?action=nh_view_notification&id=${encodeURIComponent(id)}&_wpnonce=${encodeURIComponent(nonce)}`, {
        credentials: 'same-origin'
      })
        .then((res) => res.json())
        .then((data) => {
          if (data && data.success) {
            if (title) title.textContent = data.data.source || 'Notification';
            if (message) message.textContent = data.data.message || '';
            if (meta) meta.innerHTML = `<small>${data.data.created_at || ''}</small>`;
            openModal();
          } else {
            alert(data?.data?.message || nh_i18n?.load_error || 'Failed to load notification.');
          }
        })
        .catch(() => alert(nh_i18n?.request_fail || 'Request failed'));
    }

    if (
      e.target.matches('.nh-modal__close') ||
      e.target.matches('.nh-modal__backdrop')
    ) {
      closeModal();
    }
  });

  /** Refresh indicator */
  const refreshIndicator = document.getElementById('nh-refresh-indicator');
  if (refreshIndicator) {
    const refreshLinks = document.querySelectorAll('.tablenav .tablenav-pages a');
    refreshLinks.forEach(link => {
      link.addEventListener('click', () => {
        refreshIndicator.style.display = 'inline';
        setTimeout(() => refreshIndicator.style.display = 'none', 2000);
      });
    });
  }
})();
