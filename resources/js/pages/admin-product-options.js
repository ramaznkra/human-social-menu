/**
 * Admin ürün formu — varyasyon grupları / seçenekleri (repeater).
 */
function initProductOptionsEditor() {
    const root = document.querySelector('[data-product-options]');
    if (!root) return;

    const groupsList = root.querySelector('[data-option-groups-list]');
    const emptyHint = root.querySelector('[data-option-groups-empty]');
    const groupTemplate = root.querySelector('[data-option-group-template]');
    const optionTemplate = root.querySelector('[data-option-row-template]');

    function toggleEmptyHint() {
        if (!emptyHint || !groupsList) return;
        emptyHint.hidden = groupsList.children.length > 0;
    }

    function replacePlaceholders(html, gi, oi = null) {
        let out = html.replaceAll('__GI__', String(gi));
        if (oi !== null) {
            out = out.replaceAll('__OI__', String(oi));
        }
        return out;
    }

    function reindexGroups() {
        if (!groupsList) return;

        groupsList.querySelectorAll('[data-option-group]').forEach((groupEl, gi) => {
            groupEl.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name
                    .replace(/option_groups\[\d+\]/, `option_groups[${gi}]`)
                    .replace(/option_groups\[__GI__\]/, `option_groups[${gi}]`);
            });

            const sortInput = groupEl.querySelector('[data-group-sort-order]');
            if (sortInput) sortInput.value = String(gi);

            groupEl.querySelectorAll('[data-option-row]').forEach((rowEl, oi) => {
                rowEl.querySelectorAll('[name]').forEach((input) => {
                    input.name = input.name
                        .replace(/option_groups\[\d+\]\[options\]\[\d+\]/, `option_groups[${gi}][options][${oi}]`)
                        .replace(/option_groups\[__GI__\]\[options\]\[__OI__\]/, `option_groups[${gi}][options][${oi}]`);
                });

                const optSort = rowEl.querySelector('[data-option-sort-order]');
                if (optSort) optSort.value = String(oi);
            });
        });
    }

    function updateDefaultHints(groupEl) {
        const type = groupEl.querySelector('[data-group-type-select]')?.value || 'single';
        groupEl.dataset.groupType = type;

        groupEl.querySelectorAll('[data-default-hint]').forEach((hint) => {
            hint.textContent = type === 'single' ? 'Seçili gelsin' : 'Önceden işaretli';
        });
    }

    function enforceSingleDefault(groupEl, activeInput) {
        const type = groupEl.querySelector('[data-group-type-select]')?.value || 'single';
        if (type !== 'single' || !activeInput?.checked) return;

        groupEl.querySelectorAll('[data-option-default]').forEach((input) => {
            if (input !== activeInput) input.checked = false;
        });
    }

    function bindGroupEvents(groupEl) {
        groupEl.querySelector('[data-remove-option-group]')?.addEventListener('click', () => {
            groupEl.remove();
            reindexGroups();
            toggleEmptyHint();
        });

        groupEl.querySelector('[data-group-type-select]')?.addEventListener('change', () => {
            updateDefaultHints(groupEl);
        });

        groupEl.querySelector('[data-add-option]')?.addEventListener('click', () => {
            addOptionRow(groupEl);
        });

        groupEl.querySelectorAll('[data-option-default]').forEach((input) => {
            input.addEventListener('change', () => enforceSingleDefault(groupEl, input));
        });

        groupEl.querySelectorAll('[data-remove-option]').forEach((btn) => {
            btn.addEventListener('click', () => {
                btn.closest('[data-option-row]')?.remove();
                reindexGroups();
            });
        });

        updateDefaultHints(groupEl);
    }

    function addOptionRow(groupEl, optionData = null) {
        if (!optionTemplate || !groupEl) return;

        const optionsList = groupEl.querySelector('[data-options-list]');
        if (!optionsList) return;

        const gi = Array.from(groupsList.children).indexOf(groupEl);
        const oi = optionsList.querySelectorAll('[data-option-row]').length;
        const html = replacePlaceholders(optionTemplate.innerHTML, gi, oi);
        const wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        const row = wrap.firstElementChild;
        if (!row) return;

        optionsList.appendChild(row);

        row.querySelector('[data-remove-option]')?.addEventListener('click', () => {
            row.remove();
            reindexGroups();
        });

        row.querySelector('[data-option-default]')?.addEventListener('change', (e) => {
            enforceSingleDefault(groupEl, e.target);
        });

        if (optionData) {
            // reserved for future programmatic fill
        }

        reindexGroups();
    }

    function addGroup() {
        if (!groupTemplate || !groupsList) return;

        const gi = groupsList.children.length;
        const html = replacePlaceholders(groupTemplate.innerHTML, gi);
        const wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        const groupEl = wrap.firstElementChild;
        if (!groupEl) return;

        groupsList.appendChild(groupEl);
        bindGroupEvents(groupEl);
        addOptionRow(groupEl);
        reindexGroups();
        toggleEmptyHint();
    }

    root.querySelector('[data-add-option-group]')?.addEventListener('click', addGroup);

    groupsList?.querySelectorAll('[data-option-group]').forEach((groupEl) => {
        bindGroupEvents(groupEl);
    });

    toggleEmptyHint();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductOptionsEditor);
} else {
    initProductOptionsEditor();
}
