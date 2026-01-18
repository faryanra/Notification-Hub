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

  function getActiveTabFromUrl() {
    const params = new URLSearchParams(window.location.search || '');
    const tab = (params.get('tab') || '').trim();
    return tab ? tab : null;
  }

  function setActiveTab(tab) {
    const tabs = document.querySelectorAll('.nh-settings-tabs .nav-tab');
    const panes = document.querySelectorAll('.nh-tab');

    if (tabs.length === 0 || panes.length === 0) return;

    tabs.forEach((x) => x.classList.remove('nav-tab-active'));
    panes.forEach((p) => p.classList.remove('is-active'));

    const tabLink = document.querySelector(
      `.nh-settings-tabs .nav-tab[data-tab="${tab}"]`
    );
    if (tabLink) tabLink.classList.add('nav-tab-active');

    // Support both legacy panes (#nh-tab-*) and any pane by data-tab.
    const paneById = document.getElementById(`nh-tab-${tab}`);
    if (paneById) {
      paneById.classList.add('is-active');
      return;
    }

    const paneByData = document.querySelector(`.nh-tab[data-tab="${tab}"]`);
    if (paneByData) paneByData.classList.add('is-active');
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
      inputs.forEach((el) => {
        if (locked) {
          el.setAttribute('readonly', 'readonly');
          el.classList.add('is-locked');
        } else {
          el.removeAttribute('readonly');
          el.classList.remove('is-locked');
        }
      });

      if (saveBtn) {
        // Disable save when locked; user must click Edit first.
        saveBtn.disabled = locked;
        saveBtn.classList.toggle('disabled', locked);
      }

      btnEdit.setAttribute('aria-pressed', locked ? 'false' : 'true');
      btnEdit.textContent = locked ? (btnEdit.dataset.labelEdit || 'Edit') : (btnEdit.dataset.labelCancel || 'Cancel');

      if (!locked) {
        // Focus the first input for faster UX.
        const first = inputs[0];
        if (first && typeof first.focus === 'function') first.focus();
      }
    };

    btnEdit.addEventListener('click', (e) => {
      e.preventDefault();
      setLocked(!isLocked());
    });

    // Initial state: if server URL exists (or key exists), start locked.
    const hasValue = Array.from(inputs).some((el) => (el.value || '').trim() !== '');
    setLocked(hasValue);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.nh-settings-tabs .nav-tab');
    const panes = document.querySelectorAll('.nh-tab');

    if (tabs.length > 0 && panes.length > 0) {
      // Ensure correct pane is active on load.
      const initialTab = getActiveTabFromUrl() || tabs[0]?.dataset?.tab || 'general';
      setActiveTab(initialTab);

      // Tab click: switch visible pane and update URL.
      tabs.forEach((tabEl) =>
        tabEl.addEventListener('click', (e) => {
          e.preventDefault();

          const tab = tabEl.dataset.tab || 'general';
          setActiveTab(tab);

          // Keep ALL query params, just change the tab.
          const href = tabEl.getAttribute('href');
          if (href) {
            try {
              const next = new URL(href, window.location.origin);
              const cur = new URL(window.location.href);

              cur.searchParams.forEach((value, key) => {
                next.searchParams.set(key, value);
              });
              next.searchParams.set('tab', tab);

              history.replaceState(null, '', next.toString());
            } catch (_) {
              // ignore
            }
          }
        })
      );

      // Ensure "Send test" links keep tab in URL.
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

    initLicenseEditToggle();
  });
})();