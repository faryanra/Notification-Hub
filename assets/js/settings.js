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

    const paneById = document.getElementById(`nh-tab-${tab}`);
    if (paneById) {
      paneById.classList.add('is-active');
      return;
    }

    const paneByData = document.querySelector(`.nh-tab[data-tab="${tab}"]`);
    if (paneByData) paneByData.classList.add('is-active');
  }

  function syncSaveButton(tab) {
    const form = document.querySelector('form[action*="options.php"]');
    if (!form) return;

    const submit = form.querySelector('p.submit');
    if (!submit) return;

    const tabsWrap = document.querySelector('.nh-settings-tabs');
    const premiumAddonActive = tabsWrap?.getAttribute('data-pro-addon') === '1';

    const hide = tab === 'premium' && !premiumAddonActive;
    submit.style.display = hide ? 'none' : '';
  }

  function setActiveTabAndSync(tab) {
    setActiveTab(tab);
    syncSaveButton(tab);
  }

  function setReadonlyState(inputs, locked) {
    inputs.forEach((el) => {
      if (locked) {
        el.setAttribute('readonly', 'readonly');
        el.classList.add('is-locked');
      } else {
        el.removeAttribute('readonly');
        el.classList.remove('is-locked');
        // Important: some browsers keep focus/selection weird when readonly toggles.
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
    const saveBtn = box.querySelector(
      'button[type="submit"], input[type="submit"]'
    );

    const isLocked = () => {
      const anyReadonly = Array.from(inputs).some((el) =>
        el.hasAttribute('readonly')
      );
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
          // Ensure caret is visible.
          try {
            const v = first.value || '';
            first.setSelectionRange(v.length, v.length);
          } catch (_) {}
        }
      }

      // Toggle wrapper class so CSS can style locked/unlocked properly.
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

  function initAutoHideNotices() {
    const nodes = document.querySelectorAll('.nh-auto-hide[data-auto-hide="1"]');
    if (!nodes || nodes.length === 0) return;

    nodes.forEach((notice) => {
      window.setTimeout(() => {
        notice.classList.add('nh-fade-out');
        window.setTimeout(() => {
          if (notice && notice.parentNode) notice.parentNode.removeChild(notice);
        }, 450);
      }, 4500);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.nh-settings-tabs .nav-tab');
    const panes = document.querySelectorAll('.nh-tab');

    if (tabs.length > 0 && panes.length > 0) {
      const initialTab = getActiveTabFromUrl() || tabs[0]?.dataset?.tab || 'general';
      setActiveTabAndSync(initialTab);

      tabs.forEach((tabEl) =>
        tabEl.addEventListener('click', (e) => {
          e.preventDefault();

          const tab = tabEl.dataset.tab || 'general';
          setActiveTabAndSync(tab);

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
            } catch (_) {}
          }
        })
      );

      window.addEventListener('popstate', () => {
        const tab = getActiveTabFromUrl() || 'general';
        setActiveTabAndSync(tab);
      });

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
    initAutoHideNotices();
  });
})();
