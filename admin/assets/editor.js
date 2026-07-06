/* Block editor: schema-driven forms, SortableJS ordering, JSON save. */
(() => {
    'use strict';
    const C = window.CMS;
    const state = structuredClone(C.doc);
    delete state.slug; // slug is an address, not a field
    if (C.schema.hasBlocks && !Array.isArray(state.blocks)) state.blocks = [];

    let dirty = false;
    let selected = -1;

    const $ = (id) => document.getElementById(id);
    const saveStatus = $('save-status');

    const markDirty = () => {
        dirty = true;
        saveStatus.textContent = 'unsaved changes';
    };

    const randomId = () => 'b_' + Array.from(crypto.getRandomValues(new Uint8Array(3)))
        .map((b) => b.toString(16).padStart(2, '0')).join('');

    const defaultFor = (spec) => {
        switch (spec.type) {
            case 'check': return false;
            case 'number': return 0;
            case 'csv':
            case 'repeater': return [];
            case 'select': return spec.options[0];
            default: return '';
        }
    };

    const el = (tag, attrs = {}, ...children) => {
        const node = document.createElement(tag);
        for (const [k, v] of Object.entries(attrs)) {
            if (k === 'class') node.className = v;
            else if (k.startsWith('on')) node.addEventListener(k.slice(2), v);
            else if (v !== false && v !== null && v !== undefined) node.setAttribute(k, v === true ? '' : v);
        }
        for (const child of children) {
            if (child !== null && child !== undefined) {
                node.append(child.nodeType ? child : document.createTextNode(child));
            }
        }
        return node;
    };

    /* ---------- field builders ---------- */

    function buildField(name, spec, value, onChange) {
        const wrap = el('div', { class: 'field field--' + spec.type });
        const id = 'f_' + name + '_' + Math.random().toString(36).slice(2, 7);
        if (spec.type !== 'check') wrap.append(el('label', { for: id }, spec.label || name));

        if (spec.type === 'textarea' || spec.type === 'markdown') {
            const input = el('textarea', {
                id, rows: spec.type === 'markdown' ? 6 : 4,
                oninput: (e) => onChange(e.target.value),
            });
            input.value = value ?? '';
            if (spec.type === 'markdown') wrap.append(el('span', { class: 'field__hint mono' }, 'markdown: ## heading · **bold** · [link](url) · - list'));
            wrap.append(input);
        } else if (spec.type === 'select') {
            const input = el('select', { id, onchange: (e) => onChange(e.target.value) });
            for (const opt of spec.options) {
                input.append(el('option', { value: opt, selected: opt === value }, opt));
            }
            wrap.append(input);
        } else if (spec.type === 'check') {
            const input = el('input', { id, type: 'checkbox', onchange: (e) => onChange(e.target.checked) });
            input.checked = !!value;
            wrap.append(el('label', { class: 'field__check', for: id }, input, ' ', spec.label || name));
        } else if (spec.type === 'number') {
            const input = el('input', { id, type: 'number', min: 0, oninput: (e) => onChange(parseInt(e.target.value || '0', 10)) });
            input.value = value ?? 0;
            wrap.append(input);
        } else if (spec.type === 'csv') {
            const input = el('input', {
                id, type: 'text', placeholder: 'comma, separated, values',
                oninput: (e) => onChange(e.target.value.split(',').map((s) => s.trim()).filter(Boolean)),
            });
            input.value = Array.isArray(value) ? value.join(', ') : (value || '');
            wrap.append(input);
        } else if (spec.type === 'image') {
            const input = el('input', { id, type: 'text', placeholder: '/media/uploads/…', oninput: (e) => onChange(e.target.value) });
            input.value = value ?? '';
            const pick = el('button', {
                class: 'btn btn--ghost', type: 'button',
                onclick: () => openPicker((path) => { input.value = path; onChange(path); }),
            }, 'Pick');
            wrap.append(el('div', { class: 'field__row' }, input, pick));
        } else if (spec.type === 'repeater') {
            wrap.append(buildRepeater(spec, Array.isArray(value) ? value : [], onChange));
        } else { // text, url, slug
            const input = el('input', { id, type: 'text', oninput: (e) => onChange(e.target.value) });
            input.value = value ?? '';
            wrap.append(input);
        }
        return wrap;
    }

    function buildRepeater(spec, rows, onChange) {
        const box = el('div', { class: 'repeater' });
        const list = el('div', { class: 'repeater__rows' });

        const rebuild = () => {
            list.innerHTML = '';
            rows.forEach((row, i) => {
                const rowEl = el('div', { class: 'repeater__row' });
                rowEl.append(el('span', { class: 'repeater__handle mono', title: 'Drag to reorder' }, '⋮⋮'));
                const fields = el('div', { class: 'repeater__fields' });
                for (const [name, sub] of Object.entries(spec.fields)) {
                    fields.append(buildField(name, sub, row[name], (v) => { row[name] = v; onChange(rows); markDirty(); }));
                }
                rowEl.append(fields);
                rowEl.append(el('button', {
                    class: 'btn btn--danger btn--small', type: 'button',
                    onclick: () => { rows.splice(i, 1); onChange(rows); markDirty(); rebuild(); },
                }, '×'));
                list.append(rowEl);
            });
        };
        rebuild();

        new Sortable(list, {
            handle: '.repeater__handle',
            animation: 120,
            onEnd: (evt) => {
                const [moved] = rows.splice(evt.oldIndex, 1);
                rows.splice(evt.newIndex, 0, moved);
                onChange(rows);
                markDirty();
                rebuild();
            },
        });

        box.append(list, el('button', {
            class: 'btn btn--ghost btn--small', type: 'button',
            onclick: () => {
                const row = {};
                for (const [name, sub] of Object.entries(spec.fields)) row[name] = defaultFor(sub);
                rows.push(row);
                onChange(rows);
                markDirty();
                rebuild();
            },
        }, '+ Add row'));
        return box;
    }

    /* ---------- meta ---------- */

    function renderMeta() {
        const box = $('meta-form');
        box.innerHTML = '';
        for (const [name, spec] of Object.entries(C.schema.meta)) {
            if (state[name] === undefined) state[name] = defaultFor(spec);
            box.append(buildField(name, spec, state[name], (v) => { state[name] = v; markDirty(); }));
        }
    }

    /* ---------- blocks ---------- */

    const blockLabel = (b) => (C.schema.blocks[b.type] || {}).label || b.type;
    const blockExcerpt = (b) => {
        const text = b.heading || b.name || (b.body_md || '').replace(/[#*`>]/g, '').slice(0, 46) || '';
        return text.length > 46 ? text.slice(0, 46) + '…' : text;
    };

    function renderBlockList() {
        const list = $('block-list');
        list.innerHTML = '';
        state.blocks.forEach((b, i) => {
            const card = el('li', {
                class: 'block-card' + (i === selected ? ' is-selected' : ''),
                onclick: () => selectBlock(i),
            },
                el('span', { class: 'block-card__handle mono', title: 'Drag to reorder' }, '⋮⋮'),
                el('span', { class: 'block-card__label mono' }, blockLabel(b)),
                el('span', { class: 'block-card__excerpt' }, blockExcerpt(b)),
                el('span', { class: 'block-card__actions' },
                    el('button', { class: 'btn btn--ghost btn--small', type: 'button', title: 'Duplicate', onclick: (e) => { e.stopPropagation(); duplicateBlock(i); } }, '⧉'),
                    el('button', { class: 'btn btn--danger btn--small', type: 'button', title: 'Delete', onclick: (e) => { e.stopPropagation(); deleteBlock(i); } }, '×'),
                ),
            );
            list.append(card);
        });
        $('block-count').textContent = '(' + state.blocks.length + ')';
    }

    function selectBlock(i) {
        selected = i;
        renderBlockList();
        const box = $('field-form');
        box.innerHTML = '';
        const b = state.blocks[i];
        if (!b) { $('fields-title').textContent = 'Select a block'; return; }
        $('fields-title').textContent = blockLabel(b);
        const spec = C.schema.blocks[b.type];
        if (!spec) return;
        for (const [name, sub] of Object.entries(spec.fields)) {
            if (b[name] === undefined) b[name] = defaultFor(sub);
            box.append(buildField(name, sub, b[name], (v) => {
                b[name] = v;
                markDirty();
                const card = $('block-list').children[i];
                if (card) card.querySelector('.block-card__excerpt').textContent = blockExcerpt(b);
            }));
        }
    }

    function duplicateBlock(i) {
        const copy = structuredClone(state.blocks[i]);
        copy.id = randomId();
        state.blocks.splice(i + 1, 0, copy);
        markDirty();
        selectBlock(i + 1);
    }

    function deleteBlock(i) {
        if (!confirm('Delete this ' + blockLabel(state.blocks[i]) + ' block?')) return;
        state.blocks.splice(i, 1);
        selected = -1;
        markDirty();
        renderBlockList();
        $('field-form').innerHTML = '';
        $('fields-title').textContent = 'Select a block';
    }

    function initBlocksUI() {
        $('editor-panes').hidden = false;
        renderBlockList();

        new Sortable($('block-list'), {
            handle: '.block-card__handle',
            animation: 120,
            onEnd: (evt) => {
                const [moved] = state.blocks.splice(evt.oldIndex, 1);
                state.blocks.splice(evt.newIndex, 0, moved);
                if (selected === evt.oldIndex) selected = evt.newIndex;
                markDirty();
                renderBlockList();
            },
        });

        const palette = $('palette-select');
        for (const [type, def] of Object.entries(C.schema.blocks)) {
            palette.append(el('option', { value: type }, def.label));
        }
        $('palette-add').addEventListener('click', () => {
            const type = palette.value;
            const block = { id: randomId(), type };
            for (const [name, sub] of Object.entries(C.schema.blocks[type].fields)) {
                block[name] = defaultFor(sub);
            }
            state.blocks.push(block);
            markDirty();
            selectBlock(state.blocks.length - 1);
        });
    }

    /* ---------- save ---------- */

    async function save() {
        saveStatus.textContent = 'saving…';
        try {
            const res = await fetch(C.saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF': C.csrf },
                body: JSON.stringify(state),
            });
            const data = await res.json();
            if (data.ok) {
                dirty = false;
                saveStatus.textContent = 'saved ' + data.saved_at;
            } else {
                saveStatus.textContent = 'error: ' + (data.error || res.status);
            }
        } catch (err) {
            saveStatus.textContent = 'network error';
        }
    }

    /* ---------- media picker ---------- */

    let pickCallback = null;

    async function openPicker(cb) {
        pickCallback = cb;
        await refreshPicker();
        $('media-dialog').showModal();
    }

    async function refreshPicker() {
        const res = await fetch(C.mediaListUrl);
        const data = await res.json();
        const grid = $('media-dialog-grid');
        grid.innerHTML = '';
        for (const img of (data.images || [])) {
            grid.append(el('figure', { class: 'media-item media-item--pick', onclick: () => { pickCallback(img.path); $('media-dialog').close(); } },
                el('img', { src: img.thumb, alt: '', loading: 'lazy' }),
                el('figcaption', { class: 'mono' }, img.path.split('/').pop()),
            ));
        }
    }

    $('media-close').addEventListener('click', () => $('media-dialog').close());
    $('media-upload-input').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const body = new FormData();
        body.append('file', file);
        const res = await fetch(C.uploadUrl, { method: 'POST', headers: { 'X-CSRF': C.csrf }, body });
        const data = await res.json();
        if (data.ok) await refreshPicker();
        else alert(data.error || 'Upload failed');
        e.target.value = '';
    });

    /* ---------- init ---------- */

    $('save-btn').addEventListener('click', save);
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); save(); }
    });
    window.addEventListener('beforeunload', (e) => {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    renderMeta();
    if (C.schema.hasBlocks) initBlocksUI();
})();
