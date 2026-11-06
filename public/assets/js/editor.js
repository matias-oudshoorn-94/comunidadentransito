/* Admin · editor por bloques del texto de las entregas.
 *
 * El texto se sigue guardando en el mismo formato simple que interpreta el sitio
 * (rich_text en app/helpers.php); este editor lo convierte en bloques visuales y
 * vuelve a escribirlo en el <textarea name="body"> en cada cambio.
 *
 *   Párrafo        texto con **negrita**, *cursiva* y [link](https://…)
 *   Imagen         ![epígrafe](uploads/…)
 *   Dos imágenes   dos líneas de imagen seguidas
 *   Cita           > texto  /  — Autora
 *   Recuadro       [título]  /  texto
 */
(() => {
  'use strict';

  const root = document.querySelector('[data-editor]');
  if (!root) return;
  const source = document.getElementById(root.dataset.editor);
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const list = root.querySelector('[data-editor-blocks]');
  const toast = (msg) => window.toast?.(msg);

  const TYPES = { p: 'Párrafo', img: 'Imagen', img2: 'Dos imágenes', quote: 'Cita', box: 'Recuadro', raw: 'Bloque especial' };
  const ADD = [['p', 'Párrafo'], ['img', 'Imagen'], ['img2', 'Dos imágenes'], ['quote', 'Cita'], ['box', 'Recuadro “para probar en casa”']];

  /* ---------- helpers de DOM ---------- */
  const el = (tag, cls, text) => {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  };
  const button = (label, title, onClick, disabled = false) => {
    const b = el('button', 'ed-btn', label);
    b.type = 'button';
    b.title = title;
    b.setAttribute('aria-label', title);
    b.disabled = disabled;
    b.addEventListener('click', onClick);
    return b;
  };
  const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  const imgUrl = (path) => (path.startsWith('uploads/') ? '/' + path : '/assets/img/' + path);
  const autosize = (ta) => { ta.style.height = 'auto'; ta.style.height = ta.scrollHeight + 2 + 'px'; };

  /* ---------- formato de línea ⇄ HTML editable ---------- */
  function inlineToHtml(text) {
    let h = esc(text);
    h = h.replace(/\[([^\]\n]+)\]\(((?:https?:\/\/|mailto:|\/)[^\s)]+)\)/g, '<a href="$2">$1</a>');
    h = h.replace(/\*\*(?=\S)(.+?)\*\*/g, '<b>$1</b>');
    h = h.replace(/(^|[^*\w])\*(?=\S)([^*\n]+?)\*(?![*\w])/g, '$1<i>$2</i>');
    return h.replace(/\n/g, '<br>');
  }

  function htmlToInline(node) {
    let out = '';
    node.childNodes.forEach((n) => {
      if (n.nodeType === Node.TEXT_NODE) { out += n.textContent.replace(/ /g, ' '); return; }
      if (n.nodeType !== Node.ELEMENT_NODE) return;
      const inner = htmlToInline(n);
      switch (n.tagName) {
        case 'BR': out += '\n'; break;
        case 'B': case 'STRONG': out += inner.trim() ? '**' + inner + '**' : inner; break;
        case 'I': case 'EM': out += inner.trim() ? '*' + inner + '*' : inner; break;
        case 'A': out += inner.trim() ? '[' + inner + '](' + (n.getAttribute('href') || '') + ')' : ''; break;
        case 'DIV': case 'P': out += (out && !out.endsWith('\n') ? '\n' : '') + inner; break;
        default: out += inner;
      }
    });
    return out;
  }

  /* ---------- texto ⇄ bloques ---------- */
  function parse(text) {
    const chunks = text.replace(/\r/g, '').trim().split(/\n\s*\n/).filter((c) => c.trim());
    return chunks.map((raw) => {
      const c = raw.trim();
      if (c.startsWith('\\')) return { type: 'p', text: c.slice(1) };
      if (c.startsWith('@galeria')) return { type: 'raw', text: c };
      const lines = c.split('\n');
      const imgs = lines.map((l) => l.trim().match(/^!\[([^\]]*)\]\(([^)\s]+)\)$/));
      if (imgs.every(Boolean)) {
        const items = imgs.map((m) => ({ caption: m[1], path: m[2] }));
        if (items.length === 1) return { type: 'img', items };
        if (items.length === 2) return { type: 'img2', items };
        return { type: 'raw', text: c };
      }
      if (c.startsWith('>')) {
        const quote = lines.shift().replace(/^>\s?/, '').replace(/^["“]|["”]$/g, '');
        return { type: 'quote', text: quote, author: lines.join(' ').replace(/^[—–-]\s*/, '') };
      }
      const box = c.match(/^\[([^\]]+)\]\n([\s\S]+)$/);
      if (box) return { type: 'box', title: box[1], text: box[2].trim() };
      return { type: 'p', text: c };
    });
  }

  const clean = (s) => s.replace(/[\[\]\n]/g, ' ').trim();
  function serialize(b) {
    switch (b.type) {
      case 'p': {
        let t = b.text.replace(/\n{2,}/g, '\n').trim();
        if (!t) return '';
        // si empieza con algo que el sitio leería como otro bloque, se marca como párrafo común
        if (/^(>|!\[|\[|@galeria|\\)/.test(t)) t = '\\' + t;
        return t;
      }
      case 'img':
      case 'img2':
        return b.items.filter((i) => i.path).map((i) => '![' + clean(i.caption) + '](' + i.path + ')').join('\n');
      case 'quote': {
        const t = b.text.replace(/\s*\n\s*/g, ' ').trim();
        return t ? '> ' + t + (b.author.trim() ? '\n— ' + b.author.trim() : '') : '';
      }
      case 'box': {
        const t = b.text.replace(/\n{2,}/g, '\n').trim();
        return t ? '[' + (clean(b.title) || 'para probar en casa') + ']\n' + t : '';
      }
      default:
        return b.text.trim();
    }
  }

  let blocks = [];
  const sync = () => { source.value = blocks.map(serialize).filter(Boolean).join('\n\n'); };

  /* ---------- dibujar ---------- */
  function render(focusIndex = null, where = 'end') {
    list.innerHTML = '';
    if (blocks.length === 0) blocks.push({ type: 'p', text: '' });
    list.append(adder(0));
    blocks.forEach((b, i) => {
      list.append(block(b, i));
      list.append(adder(i + 1, i === blocks.length - 1));
    });
    sync();
    if (focusIndex !== null) focus(focusIndex, where);
  }

  function focus(index, where) {
    const target = list.querySelectorAll('.ed-block')[index]?.querySelector('[contenteditable], textarea, input:not([type=file])');
    if (!target) return;
    target.focus();
    if (target.isContentEditable) {
      const range = document.createRange();
      range.selectNodeContents(target);
      range.collapse(where === 'start');
      const sel = window.getSelection();
      sel.removeAllRanges();
      sel.addRange(range);
    }
  }

  function move(i, dir) {
    const j = i + dir;
    if (j < 0 || j >= blocks.length) return;
    [blocks[i], blocks[j]] = [blocks[j], blocks[i]];
    render(j);
  }

  function removeBlock(i) {
    const b = blocks[i];
    const empty = !serialize(b);
    if (!empty && !window.confirm('¿Quitar este bloque (' + TYPES[b.type].toLowerCase() + ')?')) return;
    blocks.splice(i, 1);
    render(Math.max(0, i - 1));
  }

  function insert(index, type) {
    const fresh = {
      p: { type: 'p', text: '' },
      img: { type: 'img', items: [{ path: '', caption: '' }] },
      img2: { type: 'img2', items: [{ path: '', caption: '' }, { path: '', caption: '' }] },
      quote: { type: 'quote', text: '', author: '' },
      box: { type: 'box', title: 'para probar en casa', text: '' },
    }[type];
    blocks.splice(index, 0, fresh);
    render(index, 'start');
  }

  function adder(index, last = false) {
    const wrap = el('div', 'ed-add' + (last ? ' ed-add--last' : ''));
    const toggle = el('button', 'ed-add__toggle', last ? '+ Agregar bloque' : '+');
    toggle.type = 'button';
    toggle.title = 'Agregar un bloque acá';
    toggle.setAttribute('aria-label', 'Agregar un bloque acá');
    toggle.setAttribute('aria-expanded', 'false');
    const menu = el('div', 'ed-add__menu');
    ADD.forEach(([type, label]) => {
      const b = el('button', 'btn btn--soft btn--xs', label);
      b.type = 'button';
      b.addEventListener('click', () => insert(index, type));
      menu.append(b);
    });
    toggle.addEventListener('click', () => {
      const open = !wrap.classList.contains('is-open');
      list.querySelectorAll('.ed-add.is-open').forEach((w) => w.classList.remove('is-open'));
      wrap.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
    });
    wrap.append(toggle, menu);
    return wrap;
  }

  function block(b, i) {
    const wrap = el('div', 'ed-block ed-block--' + b.type);
    const bar = el('div', 'ed-block__bar');
    const tools = el('div', 'ed-block__tools');
    tools.append(
      button('↑', 'Subir el bloque', () => move(i, -1), i === 0),
      button('↓', 'Bajar el bloque', () => move(i, 1), i === blocks.length - 1),
      button('✕', 'Quitar el bloque', () => removeBlock(i)),
    );
    bar.append(el('span', 'ed-block__type', TYPES[b.type]), tools);
    wrap.append(bar, body(b, i));
    return wrap;
  }

  function textarea(cls, value, placeholder, onInput) {
    const ta = el('textarea', cls);
    ta.value = value;
    ta.placeholder = placeholder;
    ta.rows = 1;
    ta.addEventListener('input', () => { autosize(ta); onInput(ta.value); sync(); });
    requestAnimationFrame(() => autosize(ta));
    return ta;
  }

  function input(cls, value, placeholder, onInput) {
    const inp = el('input', cls);
    inp.value = value;
    inp.placeholder = placeholder;
    inp.addEventListener('input', () => { onInput(inp.value); sync(); });
    return inp;
  }

  function body(b, i) {
    switch (b.type) {
      case 'p': return paragraph(b, i);
      case 'img':
      case 'img2': {
        const grid = el('div', 'ed-imgs ed-imgs--' + b.items.length);
        b.items.forEach((item) => grid.append(imageSlot(item)));
        return grid;
      }
      case 'quote': {
        const box = el('div', 'ed-quote');
        box.append(
          textarea('ed-quote__text', b.text, 'Escribí la cita…', (v) => { b.text = v; }),
          input('ed-quote__author', b.author, '— Quién lo dijo (opcional)', (v) => { b.author = v; }),
        );
        return box;
      }
      case 'box': {
        const box = el('div', 'ed-box');
        box.append(
          input('ed-box__title', b.title, 'para probar en casa', (v) => { b.title = v; }),
          textarea('ed-box__text', b.text, 'La propuesta para hacer en casa…', (v) => { b.text = v; }),
        );
        return box;
      }
      default: {
        const box = el('div', 'ed-raw');
        box.append(
          el('p', 'hint', 'Este bloque tiene un formato especial y se muestra tal cual en el sitio.'),
          textarea('ed-raw__text', b.text, '', (v) => { b.text = v; }),
        );
        return box;
      }
    }
  }

  /* Párrafo: editable con negrita, cursiva y link */
  function paragraph(b, i) {
    const box = el('div', 'ed-p');
    const toolbar = el('div', 'ed-toolbar');
    const ed = el('div', 'ed-p__text');
    ed.contentEditable = 'true';
    ed.setAttribute('role', 'textbox');
    ed.setAttribute('aria-multiline', 'true');
    ed.setAttribute('aria-label', 'Párrafo');
    ed.dataset.placeholder = 'Escribí acá…  (Enter: párrafo nuevo · Shift+Enter: salto de línea)';
    ed.innerHTML = inlineToHtml(b.text);
    const update = () => { b.text = htmlToInline(ed); sync(); };

    const fmt = (label, title, run) => {
      const btn = el('button', 'ed-toolbar__btn', label);
      btn.type = 'button';
      btn.title = title;
      btn.setAttribute('aria-label', title);
      btn.addEventListener('mousedown', (e) => e.preventDefault()); // no perder la selección
      btn.addEventListener('click', () => { ed.focus(); run(); update(); });
      return btn;
    };
    const bold = fmt('B', 'Negrita (Ctrl+B)', () => document.execCommand('bold'));
    bold.style.fontWeight = '800';
    const italic = fmt('I', 'Cursiva (Ctrl+I)', () => document.execCommand('italic'));
    italic.style.fontStyle = 'italic';
    const link = fmt('Link', 'Agregar un link', () => {
      const url = (window.prompt('Pegá el link (empieza con https://)') || '').trim();
      if (!url) return;
      if (!/^(https?:\/\/|mailto:|\/)/.test(url)) { toast('El link tiene que empezar con https://'); return; }
      if (window.getSelection().isCollapsed) document.execCommand('insertHTML', false, '<a href="' + esc(url) + '">' + esc(url) + '</a>');
      else document.execCommand('createLink', false, url);
    });
    const unlink = fmt('Quitar formato', 'Quitar negrita, cursiva y link del texto elegido', () => {
      document.execCommand('removeFormat');
      document.execCommand('unlink');
    });
    toolbar.append(bold, italic, link, unlink);

    ed.addEventListener('input', update);
    ed.addEventListener('keydown', (e) => {
      // Enter: corta el párrafo en dos (Shift+Enter: salto de línea dentro del mismo)
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        const sel = window.getSelection();
        if (!sel.rangeCount) return;
        const range = sel.getRangeAt(0);
        range.deleteContents();
        const tail = document.createRange();
        tail.setStart(range.endContainer, range.endOffset);
        tail.setEnd(ed, ed.childNodes.length);
        const rest = el('div');
        rest.append(tail.extractContents());
        update();
        blocks.splice(i + 1, 0, { type: 'p', text: htmlToInline(rest).replace(/^\n+/, '') });
        render(i + 1, 'start');
      }
      // Borrar en un párrafo vacío: lo quita y vuelve al anterior
      if (e.key === 'Backspace' && ed.textContent === '' && blocks.length > 1) {
        e.preventDefault();
        blocks.splice(i, 1);
        render(Math.max(0, i - 1), 'end');
      }
    });
    // Pegar: solo texto (sin estilos de Word o del mail); las líneas en blanco arman párrafos nuevos
    ed.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.clipboardData || window.clipboardData).getData('text/plain').replace(/\r/g, '');
      const parts = text.split(/\n\s*\n/).map((p) => p.trim()).filter(Boolean);
      if (parts.length === 0) return;
      document.execCommand('insertText', false, parts[0]);
      update();
      if (parts.length > 1) {
        blocks.splice(i + 1, 0, ...parts.slice(1).map((t) => ({ type: 'p', text: t })));
        render(i + parts.length - 1, 'end');
      }
    });

    box.append(toolbar, ed);
    return box;
  }

  /* Imagen: subir, ver y escribir el epígrafe */
  function imageSlot(item) {
    const slot = el('div', 'ed-img');
    const pic = el('div', 'ed-img__pic');
    const show = () => {
      pic.innerHTML = item.path ? '<img src="' + esc(imgUrl(item.path)) + '" alt="">' : '<span>Todavía no hay imagen</span>';
    };
    show();
    const label = el('label', 'btn btn--soft btn--xs upload__button');
    const text = el('span', null, item.path ? 'Cambiar imagen' : 'Subir imagen');
    const file = el('input');
    file.type = 'file';
    file.accept = 'image/jpeg,image/png,image/webp,image/gif';
    label.append(text, file);
    file.addEventListener('change', async () => {
      const chosen = file.files[0];
      if (!chosen) return;
      label.classList.add('is-busy');
      text.textContent = 'Subiendo…';
      try {
        const form = new FormData();
        form.append('image', chosen);
        form.append('_token', csrf);
        const res = await fetch(root.dataset.uploadUrl, { method: 'POST', body: form, headers: { 'X-Requested-With': 'fetch', Accept: 'application/json' } });
        const json = await res.json().catch(() => ({ error: 'La imagen es demasiado grande para el servidor.' }));
        if (!res.ok) throw new Error(json.error || 'No se pudo subir la imagen.');
        item.path = json.path;
        show();
        sync();
      } catch (err) {
        toast(err.message);
      } finally {
        label.classList.remove('is-busy');
        text.textContent = item.path ? 'Cambiar imagen' : 'Subir imagen';
        file.value = '';
      }
    });
    const caption = input('ed-img__caption', item.caption, 'Epígrafe (opcional)', (v) => { item.caption = v; });
    slot.append(pic, label, caption);
    return slot;
  }

  /* ---------- modo texto (avanzado) ---------- */
  const toggle = root.querySelector('[data-editor-mode]');
  toggle?.addEventListener('click', () => {
    const toText = !root.classList.contains('is-text');
    if (toText) {
      sync();
      root.classList.add('is-text');
      toggle.textContent = 'Volver al editor visual';
      autosize(source);
      source.focus();
    } else {
      blocks = parse(source.value);
      render();
      root.classList.remove('is-text');
      toggle.textContent = 'Editar como texto';
    }
  });
  source.addEventListener('input', () => autosize(source));

  // Por las dudas, se vuelve a escribir el texto justo antes de guardar
  source.form?.addEventListener('submit', () => { if (!root.classList.contains('is-text')) sync(); });

  blocks = parse(source.value);
  root.classList.add('is-ready');
  render();
})();
