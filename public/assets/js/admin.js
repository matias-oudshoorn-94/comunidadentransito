/* Admin · subida de archivos en las entregas */
(() => {
  'use strict';

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
  const csrf = $('meta[name="csrf-token"]')?.content ?? '';
  const mb = (bytes) => (bytes / 1024 / 1024).toFixed(1).replace('.', ',') + ' MB';

  // Vista previa de la imagen elegida
  $$('[data-preview-input]').forEach((input) => {
    const box = input.closest('.upload');
    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      const preview = $('[data-preview]', box);
      preview.innerHTML = '';
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.alt = '';
      preview.append(img);
      $('[data-file-name]', box).textContent = file.name + ' · ' + mb(file.size);
    });
  });

  // Nombre y peso del archivo de video, audio o PDF
  $$('[data-file-input]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files[0];
      if (file) input.closest('[data-media-panel]').querySelector('[data-file-name]').textContent = file.name + ' · ' + mb(file.size);
    });
  });

  // Link o archivo
  $$('[data-media]').forEach((box) => {
    const radios = $$('input[name="media_source"]', box);
    const sync = () => {
      const value = radios.find((r) => r.checked)?.value ?? 'link';
      $$('[data-media-panel]', box).forEach((p) => { p.hidden = p.dataset.mediaPanel !== value; });
    };
    radios.forEach((r) => r.addEventListener('change', sync));
    sync();
  });

  // "Insertar imagen en el texto": sube y agrega ![](uploads/…) donde está el cursor
  $$('[data-inline-upload]').forEach((input) => {
    const textarea = document.getElementById(input.dataset.inlineUpload);
    input.addEventListener('change', async () => {
      const file = input.files[0];
      if (!file) return;
      const button = input.closest('label');
      const original = button.innerHTML;
      button.classList.add('is-busy');
      try {
        const body = new FormData();
        body.append('image', file);
        body.append('_token', csrf);
        const res = await fetch(input.dataset.url, { method: 'POST', body, headers: { 'X-Requested-With': 'fetch', Accept: 'application/json' } });
        const json = await res.json().catch(() => ({ error: 'El archivo es demasiado grande para el servidor.' }));
        if (!res.ok) throw new Error(json.error || 'No se pudo subir la imagen.');
        const at = textarea.selectionStart ?? textarea.value.length;
        const before = textarea.value.slice(0, at).replace(/\s*$/, '');
        const after = textarea.value.slice(at).replace(/^\s*/, '');
        const block = json.markdown;
        textarea.value = (before ? before + '\n\n' : '') + block + (after ? '\n\n' + after : '');
        const pos = (before ? before.length + 2 : 0) + 2;
        textarea.focus();
        textarea.setSelectionRange(pos, pos); // el cursor queda donde va el epígrafe: ![acá](…)
        window.toast?.('Imagen agregada. Si querés, escribí el epígrafe entre los corchetes.');
      } catch (err) {
        window.toast?.(err.message);
      } finally {
        button.classList.remove('is-busy');
        button.innerHTML = original;
        input.value = '';
      }
    });
  });

  // Mientras sube un archivo grande, el botón avisa
  $$('[data-submit-uploading]').forEach((button) => {
    const form = document.getElementById(button.getAttribute('form'));
    form?.addEventListener('submit', () => {
      const hasFiles = $$('input[type=file]', form).some((i) => i.files.length && !i.dataset.inlineUpload);
      if (hasFiles) {
        button.disabled = true;
        button.textContent = button.dataset.submitUploading;
      }
    });
  });

  // Galería: reordenar con flechas, marcar para quitar y ver las imágenes nuevas antes de guardar
  $$('[data-gallery]').forEach((box) => {
    const list = $('[data-gallery-list]', box);
    const renumber = () => $$('[data-gallery-item]', list).forEach((item, i) => { $('[data-sort]', item).value = i + 1; });
    list.addEventListener('click', (e) => {
      const button = e.target.closest('[data-move]');
      if (!button) return;
      const item = button.closest('[data-gallery-item]');
      if (button.dataset.move === '-1' && item.previousElementSibling) item.previousElementSibling.before(item);
      if (button.dataset.move === '1' && item.nextElementSibling) item.nextElementSibling.after(item);
      renumber();
    });
    list.addEventListener('change', (e) => {
      if (e.target.matches('[data-remove]')) e.target.closest('[data-gallery-item]').classList.toggle('is-removed', e.target.checked);
    });
    const input = $('[data-gallery-input]', box);
    const preview = $('[data-gallery-new]', box);
    if (!input) return;
    input.addEventListener('change', () => {
      preview.innerHTML = '';
      [...input.files].forEach((file) => {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = file.name;
        preview.append(img);
      });
      preview.hidden = input.files.length === 0;
      const total = [...input.files].reduce((n, f) => n + f.size, 0);
      const word = input.files.length === 1 ? ' imagen nueva' : ' imágenes nuevas';
      $('[data-gallery-count]', box).textContent = input.files.length + word + ' · ' + mb(total) + '. Se suben al guardar.';
    });
  });
})();
