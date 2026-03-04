// =====================================================
// Notification Hub — Settings JS
//
// Handles tab switching (no reload) and ensures test links
// keep the correct `tab` in the query string.
//
// Also handles License UI lock/unlock (Edit) UX.
//
// @package Notification_Hub
// @since 1.6.2
// =====================================================

(function () {
  'use strict';

  const AUTO_HIDE_MS = 10000;

  function getActiveTabFromUrl() {
    const params = new URLSearchParams(window.location.search || '');
    const tab = (params.get('tab') || '').trim();
    return tab ? tab : null;
  }

  function setReadonlyState(inputs, locked) {
    inputs.forEach((el) => {
      if (locked) {
        el.setAttribute('readonly', 'readonly');
        el.classList.add('is-locked');
      } else {
        el.removeAttribute('readonly');
        el.classList.remove('is-locked');
        el.style.pointerEvents = '';
        el.style.userSelect = '';
        el.style.backgroundColor = '';
        el.style.color = '';
        el.style.cursor = '';
      }
    });
  }

  function initLicenseEditToggle() {
    const box = document.getElementById('nh-license-box');
    if (!box) return;

    const btnEdit = box.querySelector('#nh-license-edit');
    if (!btnEdit) return;

    const inputs = box.querySelectorAll('[data-lockable="1"]');
    const saveBtn = box.querySelector('button[type="submit"], input[type="submit"]');

    const isLocked = () => {
      const anyReadonly = Array.from(inputs).some((el) => el.hasAttribute('readonly'));
      return anyReadonly;
    };

    const setLocked = (locked) => {
      setReadonlyState(inputs, locked);

      if (saveBtn) {
        saveBtn.disabled = locked;
        saveBtn.classList.toggle('disabled', locked);
      }

      btnEdit.setAttribute('aria-pressed', locked ? 'false' : 'true');
      btnEdit.textContent = locked
        ? btnEdit.dataset.labelEdit || 'Edit'
        : btnEdit.dataset.labelCancel || 'Cancel';

      if (!locked) {
        const first = inputs[0];
        if (first && typeof first.focus === 'function') {
          first.focus();
          try {
            const v = first.value || '';
            first.setSelectionRange(v.length, v.length);
          } catch (_) {}
        }
      }

      box.querySelectorAll('.nh-license-locked-wrap').forEach((wrap) => {
        wrap.classList.toggle('is-locked', locked);
      });
    };

    btnEdit.addEventListener('click', (e) => {
      e.preventDefault();
      setLocked(!isLocked());
    });

    const hasValue = Array.from(inputs).some((el) => (el.value || '').trim() !== '');
    setLocked(hasValue);
  }

  function cleanLicenseQueryParams() {
    try {
      const url = new URL(window.location.href);
      const keys = [
        'nh_license_saved',
        'nh_license_revoked',
        'nh_license_error',
        'nh_license_server_saved',
      ];
      let changed = false;
      keys.forEach((k) => {
        if (url.searchParams.has(k)) {
          url.searchParams.delete(k);
          changed = true;
        }
      });
      if (changed) {
        history.replaceState(null, '', url.toString());
      }
    } catch (_) {}
  }

  function initAutoHideNotices() {
    const nodes = document.querySelectorAll('.nh-auto-hide[data-auto-hide="1"]');
    if (!nodes || nodes.length === 0) return;

    nodes.forEach((notice) => {
      window.setTimeout(() => {
        notice.classList.add('nh-notice-slide-out');
        window.setTimeout(() => {
          if (notice && notice.parentNode) notice.parentNode.removeChild(notice);
        }, 500);
      }, AUTO_HIDE_MS);
    });
  }

  function initTabLinksReload() {
    const tabs = document.querySelectorAll('.nh-settings-tabs .nav-tab');
    if (!tabs || tabs.length === 0) return;

    tabs.forEach((tabEl) => {
      tabEl.addEventListener('click', () => {
        // allow navigation
      });
    });
  }

  function initTestLinks() {
    document.querySelectorAll('.nh-test-btn').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();

        const tab = btn.dataset.tab || getActiveTabFromUrl() || 'general';

        let href;
        try {
          href = new URL(btn.getAttribute('href') || '', window.location.origin);
        } catch (_) {
          return;
        }

        href.searchParams.set('tab', tab);
        window.location.href = href.toString();
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initTabLinksReload();
    initTestLinks();

    initLicenseEditToggle();
    initAutoHideNotices();
    cleanLicenseQueryParams();
  });
})();
