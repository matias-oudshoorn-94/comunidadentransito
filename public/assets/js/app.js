/* Comunidad en Tránsito · interacciones del sitio
   Movimientos suaves y lentos. Con "reducir movimiento" activado, los carruseles
   se detienen y la runa aparece con un fundido en lugar del giro. */

(() => {
  'use strict';

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const csrf = $('meta[name="csrf-token"]')?.content ?? '';

  /* ---------- Avisos ---------- */
  const checkIcon = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7"/></svg>';

  function toast(message, timeout = 5000) {
    const box = $('#toasts');
    if (!box || !message) return;
    const el = document.createElement('div');
    el.className = 'toast';
    el.setAttribute('role', 'status');
    el.innerHTML = checkIcon;
    const text = document.createElement('span');
    text.textContent = message;
    el.append(text);
    box.append(el);
    setTimeout(() => {
      el.classList.add('is-leaving');
      el.addEventListener('animationend', () => el.remove(), { once: true });
    }, timeout);
  }
  window.toast = toast;
  $$('[data-toast]').forEach((el) => { toast(el.dataset.toast); el.remove(); });

  /* ---------- Encabezado fijo ---------- */
  const header = $('.site-header');
  if (header) {
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ---------- Menú desplegable de escritorio: clic para abrir, clic afuera para cerrar ---------- */
  $$('[data-dropdown]').forEach((wrap) => {
    const trigger = $('[data-dropdown-trigger]', wrap);
    const panel = $('[data-dropdown-panel]', wrap);
    const setOpen = (open) => {
      trigger.setAttribute('aria-expanded', String(open));
      panel.classList.toggle('is-open', open);
    };
    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      setOpen(trigger.getAttribute('aria-expanded') !== 'true');
    });
    document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) setOpen(false); });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && trigger.getAttribute('aria-expanded') === 'true') { setOpen(false); trigger.focus(); }
    });
  });

  /* ---------- Menú de celular: pantalla completa ---------- */
  const mobileMenu = $('#mobile-menu');
  if (mobileMenu) {
    const openers = $$('[data-menu-open]');
    const setMenu = (open) => {
      mobileMenu.classList.toggle('is-open', open);
      mobileMenu.setAttribute('aria-hidden', String(!open));
      document.body.classList.toggle('menu-open', open);
      openers.forEach((b) => b.setAttribute('aria-expanded', String(open)));
      if (open) $('[data-menu-close]', mobileMenu)?.focus();
    };
    openers.forEach((b) => b.addEventListener('click', () => setMenu(true)));
    $$('[data-menu-close]', mobileMenu).forEach((b) => b.addEventListener('click', () => setMenu(false)));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && mobileMenu.classList.contains('is-open')) setMenu(false); });
    window.matchMedia('(min-width: 1181px)').addEventListener('change', (e) => { if (e.matches) setMenu(false); });
  }

  /* ---------- Acordeones (menú de celular y preguntas frecuentes) ---------- */
  $$('[data-accordion]').forEach((button) => {
    const panel = document.getElementById(button.getAttribute('aria-controls'));
    button.addEventListener('click', () => {
      const open = button.getAttribute('aria-expanded') !== 'true';
      button.setAttribute('aria-expanded', String(open));
      panel?.classList.toggle('is-open', open);
    });
  });

  /* ---------- Fotos que cambian: fundido suave de 2 s cada 5 s ---------- */
  $$('[data-fader]').forEach((fader) => {
    const slides = [...fader.children];
    if (slides.length < 2) return;
    let index = 0;
    let timer = null;
    const start = () => {
      if (reducedMotion.matches || timer) return;
      timer = setInterval(() => {
        slides[index].classList.remove('is-active');
        index = (index + 1) % slides.length;
        slides[index].classList.add('is-active');
      }, 5000);
    };
    const stop = () => { clearInterval(timer); timer = null; };
    start();
    reducedMotion.addEventListener('change', () => (reducedMotion.matches ? stop() : start()));
    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
  });

  /* ---------- Selector de planes: los precios cambian con una transición de 0,35 s ---------- */
  $$('[data-plans]').forEach((root) => {
    const data = JSON.parse($('script[type="application/json"]', root).textContent);
    const tabs = $$('[data-period]', root);
    const cards = $$('[data-tier]', root);
    const half = reducedMotion.matches ? 0 : 175;

    const render = (period) => {
      cards.forEach((card) => {
        const plan = data[period]?.[card.dataset.tier];
        if (!plan) return;
        card.classList.add('is-switching');
        setTimeout(() => {
          $('[data-amount]', card).textContent = plan.amount;
          $('[data-per]', card).textContent = plan.per;
          $('[data-usd]', card).textContent = plan.usd;
          $('[data-note]', card).textContent = plan.note;
          $('[data-cta]', card).href = plan.url;
          card.classList.remove('is-switching');
        }, half);
      });
    };

    tabs.forEach((tab) => tab.addEventListener('click', () => {
      tabs.forEach((t) => t.setAttribute('aria-selected', String(t === tab)));
      render(tab.dataset.period);
    }));
  });

  /* ---------- Filtros de contenidos ---------- */
  $$('[data-filters]').forEach((bar) => {
    const target = document.getElementById(bar.dataset.filters);
    if (!target) return;
    const buttons = $$('[data-filter]', bar);
    buttons.forEach((button) => button.addEventListener('click', () => {
      const value = button.dataset.filter;
      buttons.forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
      $$('[data-group]', target).forEach((card) => {
        const show = value === 'todo' || card.dataset.group === value;
        card.classList.toggle('is-hidden', !show);
        card.classList.remove('is-entering');
        if (show) { void card.offsetWidth; card.classList.add('is-entering'); }
      });
    }));
  });

  /* ---------- Ficha de libro: se cierra con la ✕ o tocando afuera ---------- */
  $$('[data-open-dialog]').forEach((button) => {
    const dialog = document.getElementById(button.dataset.openDialog);
    if (!dialog) return;
    button.addEventListener('click', () => dialog.showModal());
  });
  $$('dialog.modal').forEach((dialog) => {
    $$('[data-close-dialog]', dialog).forEach((b) => b.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
  });

  /* ---------- Sacar una runa ---------- */
  const deck = $('[data-rune-deck]');
  if (deck) {
    const result = $('[data-rune-result]');
    let busy = false;
    $$('.rune-card', deck).forEach((card) => card.addEventListener('click', async () => {
      if (busy) return;
      busy = true;
      try {
        const res = await fetch(deck.dataset.url, {
          method: 'POST',
          headers: { 'X-CSRF-Token': csrf, 'X-Requested-With': 'fetch', Accept: 'application/json' },
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.error || 'No pudimos sacar la runa.');

        // La carta elegida gira (0,22 s) y las demás se desvanecen
        const others = $$('.rune-card', deck).filter((c) => c !== card);
        others.forEach((c) => c.classList.add('is-fading'));
        card.classList.add('is-flipping');
        await new Promise((r) => setTimeout(r, reducedMotion.matches ? 0 : 240));

        $('[data-rune-symbol]', result).textContent = json.rune.symbol;
        $('[data-rune-name]', result).textContent = `${json.rune.name} · ${json.rune.meaning}`;
        $('[data-rune-prompt]', result).textContent = json.rune.prompt;
        deck.classList.add('is-done');
        result.classList.add('is-visible'); // la runa aparece de frente (0,55 s)
        result.focus();
      } catch (err) {
        toast(err.message);
        busy = false;
      }
    }));
  }

  /* ---------- Formularios que confirman sin recargar (regalo de Tecniquitas) ---------- */
  $$('form[data-ajax]').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const button = $('button[type="submit"]', form);
      button.disabled = true;
      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'fetch', Accept: 'application/json' },
        });
        const json = await res.json();
        if (!res.ok) {
          const first = json.errors ? Object.values(json.errors)[0][0] : json.error;
          throw new Error(first || 'Algo no salió bien.');
        }
        form.reset();
        toast(json.message);
      } catch (err) {
        toast(err.message);
      } finally {
        button.disabled = false;
      }
    });
  });

  /* ---------- Confirmación antes de acciones importantes ---------- */
  document.addEventListener('submit', (e) => {
    const message = e.target.dataset?.confirm;
    if (message && !window.confirm(message)) e.preventDefault();
  });
})();
