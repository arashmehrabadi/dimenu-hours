(function () {
  async function fetchStatus() {
    try {
      const res = await fetch(window.DIMENU_HOURS?.restUrl || '/wp-json/dimenu/v1/status', {
        credentials: 'same-origin'
      });
      if (!res.ok) return null;
      return await res.json();
    } catch (e) {
      return null;
    }
  }

  function ensureBanner(text) {
    if (!window.DIMENU_HOURS?.showBanner) return;

    const targetSelector = window.DIMENU_HOURS?.bannerSelector || null;
    const container = targetSelector ? document.querySelector(targetSelector) : null;

    const id = 'dimenu-hours-banner';
    let el = document.getElementById(id);
    if (!el) {
      el = document.createElement('div');
      el.id = id;
      el.style.padding = '10px 12px';
      el.style.margin = '10px 0';
      el.style.border = '1px solid rgba(0,0,0,.1)';
      el.style.borderRadius = '10px';
      el.style.fontSize = '14px';
      el.style.lineHeight = '1.6';
      el.style.background = 'rgba(255, 193, 7, .15)';
      el.style.position = 'relative';
      el.style.zIndex = '5';
      if (container) {
        container.prepend(el);
      } else {
        document.body.prepend(el);
      }
    }
    el.textContent = text || '';
  }

  function gateButtons() {
    const customSelectors = window.DIMENU_HOURS?.gateSelectors;
    const selectors = Array.isArray(customSelectors) && customSelectors.length > 0 ? customSelectors : [
      'button[name="add-to-cart"]',
      'button.single_add_to_cart_button',
      '.add_to_cart_button',
      '[data-add-to-cart]',
      '.dimenu-add-to-cart',
      '.dimenu-order-btn',
      'button.checkout',
      'a.checkout'
    ];

    const nodes = document.querySelectorAll(selectors.join(','));
    nodes.forEach((btn) => {
      if (btn.tagName === 'A') {
        btn.setAttribute('aria-disabled', 'true');
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
        }, { capture: true });
        btn.style.pointerEvents = 'none';
        btn.style.opacity = '0.5';
        return;
      }

      btn.disabled = true;
      btn.setAttribute('aria-disabled', 'true');
      btn.style.opacity = '0.5';
      btn.style.cursor = 'not-allowed';
    });
  }

  async function run() {
    const status = await fetchStatus();
    if (!status) return;

    if (status.is_open === false) {
      ensureBanner(status.human || 'Closed now.');
      gateButtons();
      setTimeout(gateButtons, 800);
      setTimeout(gateButtons, 2000);
    }
  }

  document.addEventListener('DOMContentLoaded', run);
})();
