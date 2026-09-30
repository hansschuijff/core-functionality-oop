/**
 * Advanced Framework Admin Menu Builder Tree Layout Orchestrator.
 * Handles unified event delegation for precise reordering, cloning, and button-driven depth steps.
 *
 * @since 1.0.0
 */
document.addEventListener('DOMContentLoaded', function() {
    const canvas       = document.querySelector('.js-dwp-active-tree-canvas');
    const pool         = document.querySelector('.js-dwp-builder-pool');
    const form         = document.querySelector('.dwp-gutenberg-section-form');
    const jsonInput    = document.getElementById('js-dwp-serialized-tree-input');
    const bulkBox      = document.querySelector('.js-dwp-bulk-management-box');

    if (!canvas || !pool || !form || !jsonInput || !bulkBox) {
        return;
    }

    let draggedItem = null;

    // 1. RESTRUCTURE CONTROLS PANEL: Groups Button and Select All inside the top of the unified grey box
    const bulkControlBar = document.createElement('div');
    bulkControlBar.style.cssText = 'border-bottom: 1px solid #cbd5e1; padding-bottom: 12px; margin-bottom: 8px; display: flex; flex-direction: column; gap: 10px; width: 100%; box-sizing: border-box;';

    const selectAllText = (typeof dwp_cf_builder_i18n_select_all !== 'undefined') ? dwp_cf_builder_i18n_select_all : 'Select All Visible';
    bulkControlBar.innerHTML = `
        <button type="button" id="js-dwp-bulk-insert-btn" class="button button-secondary" style="width: 100%; font-weight: 600; height: 32px; border-color: #cbd5e1; text-align: center; line-height: 30px;">
            👇 Add Selected Shortcuts to Menu
        </button>
        <div style="display: flex; align-items: center; gap: 8px; padding-left: 2px;">
            <input type="checkbox" id="js-dwp-pool-select-all" style="margin: 0;" />
            <label for="js-dwp-pool-select-all" style="font-weight: 600; font-size: 13px; color: #475569; cursor: pointer; user-select: none;">${selectAllText}</label>
        </div>
    `;
    bulkBox.insertBefore(bulkControlBar, pool);

    const insertBtn = document.getElementById('js-dwp-bulk-insert-btn');

    // Select All Checkbox Handler (Only targets currently visible filtered nodes)
    document.getElementById('js-dwp-pool-select-all').addEventListener('change', function() {
        const isChecked = this.checked;
        pool.querySelectorAll('.js-dwp-tree-node').forEach(item => {
            if (item.style.display !== 'none') {
                const cb = item.querySelector('.js-pool-checkbox');
                if (cb) cb.checked = isChecked;
            }
        });
    });

    // 2. PROVIDER FILTER LOGIC
    const sourceFilter = document.getElementById('js-dwp-provider-filter');
    if (sourceFilter) {
        sourceFilter.addEventListener('change', function() {
            const filterValue = this.value;
            const selectAllCb = document.getElementById('js-dwp-pool-select-all');
            if (selectAllCb) selectAllCb.checked = false;

            pool.querySelectorAll('.js-dwp-tree-node').forEach(item => {
                const provider = item.getAttribute('data-provider');
                const cb = item.querySelector('.js-pool-checkbox');
                if (cb) cb.checked = false; // Reset checkboxes on filter shift

                if (filterValue === 'all' || provider === filterValue) {
                    item.style.setProperty('display', 'flex', 'important');
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            });
        });
    }

    // 3. SELECTION BULK INSERTION ENGINE (Now passes live data-href attributes)
    insertBtn.addEventListener('click', function() {
        const checkedBoxes = pool.querySelectorAll('.js-pool-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one shortcut checkbox to insert.');
            return;
        }

        clearCanvasPlaceholder();

        checkedBoxes.forEach(cb => {
            const sourceNode = cb.closest('.js-dwp-tree-node');
            if (sourceNode) {
                cloneAndInsertNode(
                    sourceNode.getAttribute('data-id'),
                    sourceNode.querySelector('.js-node-title').innerText,
                    sourceNode.getAttribute('data-provider'),
                    sourceNode.getAttribute('data-type') || 'shortcut',
                    sourceNode.getAttribute('data-href') || '#', // FIXED: Plucks real provider URLs straight out of DOM markup
                    0,
                    null
                );
                cb.checked = false;
            }
        });

        const selectAllCb = document.getElementById('js-dwp-pool-select-all');
        if (selectAllCb) selectAllCb.checked = false;

        serializeActiveTreeStructure();
    });

    // Helper function to remove placeholder cleanly
    function clearCanvasPlaceholder() {
        const placeholder = canvas.querySelector('.dwp-canvas-placeholder-msg');
        if (placeholder) {
            placeholder.parentNode.removeChild(placeholder);
        }
    }

    // 4. CLONE CONSTRUCTOR HOOK FUNCTION
    function cloneAndInsertNode(id, title, provider, type, href, depth, referenceNode) {
        const instanceId = 'inst-' + Math.random().toString(36).substr(2, 9);
        const borderLeft = (provider === 'custom_link') ? '4px solid #16a34a' : '4px solid #2271b1';
        const friendlySource = provider.toUpperCase().replace(/[-_]/g, ' ');

        let targetId = id;
        if (provider === 'custom_link' && id === 'custom-link-node') {
            targetId = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-\$)/g, '');
            if (!targetId) targetId = 'custom-link';
        }

        const item = document.createElement('div');
        item.className = 'dwp-tree-node-item js-dwp-tree-node';
        item.setAttribute('data-id', targetId);
        item.setAttribute('data-instance', instanceId);
        item.setAttribute('data-type', type);
        item.setAttribute('data-provider', provider);
        item.setAttribute('data-depth', depth);
        item.setAttribute('draggable', 'true');
        item.style.cssText = `background: #fff; border: 1px solid #cbd5e1; border-left: ${borderLeft}; padding: 10px 15px; margin-left: ${depth * 30}px; border-radius: 3px; display: flex; flex-direction: column; cursor: move; box-shadow: 0 1px 2px rgba(0,0,0,0.05); position: relative; transition: margin-left 0.15s ease;`;

        const isReadOnlyAttr = (provider === 'custom_link') ? '' : 'readonly="readonly" style="background: #e2e8f0; color: #475569;"';

        item.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0; width: 100%; box-sizing: border-box;">
                <div style="display: flex; align-items: center; gap: 10px; max-width: 65%;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <strong class="js-node-title" style="font-size: 13px; color: #1e293b;">${title}</strong>
                        <span style="font-size: 10px; background: #e2e8f0; color: #475569; padding: 1px 5px; border-radius: 3px; font-weight: 600; width: fit-content; margin-top: 3px; font-family: sans-serif;">${friendlySource}</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; color: #94a3b8;">
                    <span class="dashicons dashicons-arrow-left-alt2 js-dwp-move-outward" title="Move Outward (Reduce Depth)" style="cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #475569;"></span>
                    <span class="dashicons dashicons-arrow-right-alt2 js-dwp-move-inward" title="Move Inward (Increase Depth)" style="cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #2271b1;"></span>
                    <span class="dashicons dashicons-arrow-down-alt2 js-dwp-toggle-edit" title="Toggle Configuration" style="cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #475569; transition: transform 0.2s; margin-left: 4px;"></span>
                    <span class="dashicons dashicons-trash js-dwp-remove-node" title="Remove item" style="cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #ef4444;"></span>
                    <span class="dashicons dashicons-editor-justify" style="font-size: 18px; width: 18px; height: 18px; margin-left: 2px;"></span>
                </div>
            </div>
            <div class="js-dwp-edit-panel" style="display: none; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 15px; margin-top: 10px; box-sizing: border-box; width: 100%;">
                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Navigation Label Override</label>
                    <input type="text" class="js-dwp-edit-title regular-text" value="${title}" style="width: 100%; height: 28px; font-size: 12px;" />
                    <span style="display: block; font-size: 10px; color: #b91c1c; margin-top: 4px; font-style: italic;">⚠️ Notice: Custom label overrides bypass system i18n core translation files.</span>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Resource URL Reference (Read-Only Copy Source)</label>
                    <div style="display: flex; gap: 6px;">
                        <input type="text" class="js-dwp-edit-url regular-text" id="url-${instanceId}" value="${href}" ${isReadOnlyAttr} style="flex: 1; height: 28px; font-size: 12px; font-family: monospace;" />
                        <button type="button" class="button button-small" onclick="navigator.clipboard.writeText(document.getElementById('url-${instanceId}').value); alert('URL successfully copied to clipboard!');" style="height: 28px; line-height: 26px; font-size: 11px;">📋 Copy</button>
                    </div>
                </div>
            </div>
        `;

        if (referenceNode && referenceNode.parentNode === canvas) {
            canvas.insertBefore(item, referenceNode);
        } else {
            canvas.appendChild(item);
        }
        return item;
    }

    // 5. UNIFIED DRAG & DROP COUPLING SYSTEM (Now injects dynamic data-href lookups on drop)
    document.addEventListener('dragstart', function(e) {
        const node = e.target.closest('.js-dwp-tree-node');
        if (node) {
            draggedItem = node;
            node.style.opacity = '0.4';
            e.dataTransfer.setData('text/plain', node.getAttribute('data-id'));

            const origin = node.closest('.js-dwp-builder-pool') ? 'pool' : 'canvas';
            node.setAttribute('data-drag-origin', origin);
        }
    });

    document.addEventListener('dragend', function(e) {
        if (draggedItem) {
            draggedItem.style.opacity = '1';
            draggedItem.removeAttribute('data-drag-origin');
            draggedItem = null;
            serializeActiveTreeStructure();
        }
    });

    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (!draggedItem) return;

        if (draggedItem.getAttribute('data-drag-origin') === 'canvas') {
            const afterElement = getDragAfterElement(canvas, e.clientY);
            if (afterElement == null) {
                canvas.appendChild(draggedItem);
            } else {
                canvas.insertBefore(draggedItem, afterElement);
            }
        }
    });

    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        if (!draggedItem) return;

        const dragOrigin = draggedItem.getAttribute('data-drag-origin');

        if (dragOrigin === 'pool') {
            clearCanvasPlaceholder();
            const afterElement = getDragAfterElement(canvas, e.clientY);
            const insertedNode = cloneAndInsertNode(
                draggedItem.getAttribute('data-id'),
                draggedItem.querySelector('.js-node-title').innerText,
                draggedItem.getAttribute('data-provider'),
                draggedItem.getAttribute('data-type') || 'shortcut',
                draggedItem.getAttribute('data-href') || '#', // FIXED: Plucks real provider URLs straight out of dynamic drag markups
                0,
                afterElement
            );

            if (insertedNode.getAttribute('data-provider') === 'custom_link') {
                const editPanel = insertedNode.querySelector('.js-dwp-edit-panel');
                const toggleBtn = insertedNode.querySelector('.js-dwp-toggle-edit');
                if (editPanel) editPanel.style.display = 'block';
                if (toggleBtn) toggleBtn.style.transform = 'rotate(180deg)';
            }
        }

        serializeActiveTreeStructure();
    });

    // 6. INLINE INTERACTIVE CONTROLS & STEPPER ENGINE DELEGATION
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('js-dwp-toggle-edit')) {
            const card = e.target.closest('.js-dwp-tree-node');
            const panel = card.querySelector('.js-dwp-edit-panel');
            if (panel) {
                const isHidden = panel.style.display === 'none';
                panel.style.display = isHidden ? 'block' : 'none';
                e.target.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }

        // STEPPER INWARD: Shifts level indentation 1 layer deeper (Max depth 2)
        if (e.target.classList.contains('js-dwp-move-inward')) {
            const card = e.target.closest('.js-dwp-tree-node');
            let depth = parseInt(card.getAttribute('data-depth') || '0', 10);
            if (depth < 2) {
                depth++;
                card.setAttribute('data-depth', depth.toString());
                card.style.marginLeft = (depth * 30) + 'px';
                serializeActiveTreeStructure();
            }
        }

        // STEPPER OUTWARD: Decrements indentation level 1 layer (Min depth 0)
        if (e.target.classList.contains('js-dwp-move-outward')) {
            const card = e.target.closest('.js-dwp-tree-node');
            let depth = parseInt(card.getAttribute('data-depth') || '0', 10);
            if (depth > 0) {
                depth--;
                card.setAttribute('data-depth', depth.toString());
                card.style.marginLeft = (depth * 30) + 'px';
                serializeActiveTreeStructure();
            }
        }

        // CASCADING WIPE WITH DEFENSIVE CONFIRMATION DIALOG GATES
        if (e.target.classList.contains('js-dwp-remove-node')) {
            const currentItem = e.target.closest('.js-dwp-tree-node');
            if (currentItem) {
                const nodes = [...canvas.querySelectorAll('.js-dwp-tree-node')];
                const currentIndex = nodes.indexOf(currentItem);
                const currentDepth = parseInt(currentItem.getAttribute('data-depth') || '0', 10);

                let itemsToRemove = [currentItem];

                // Track and collect all nested child nodes following this parent row parameters
                for (let i = currentIndex + 1; i < nodes.length; i++) {
                    const nextDepth = parseInt(nodes[i].getAttribute('data-depth') || '0', 10);
                    if (nextDepth > currentDepth) {
                        itemsToRemove.push(nodes[i]);
                    } else {
                        break;
                    }
                }

                // CRITICAL FIREWALL GATES: If the item houses nested children, force a descriptive confirmation prompt
                if (itemsToRemove.length > 1) {
                    const confirmMsg = `CRITICAL WARNING:\n\nThis node contains ${itemsToRemove.length - 1} nested sub-menu items.\n\nAre you absolutely sure you want to permanently delete this entire menu branch hierarchy from the canvas? This action cannot be undone.`;
                    if (!confirm(confirmMsg)) {
                        return; // Abort wipe operation immediately if user declines the prompt
                    }
                }

                // Execute the clean cascading scrub from the active workspace canvas DOM
                itemsToRemove.forEach(item => item.parentNode.removeChild(item));
                serializeActiveTreeStructure();

                if (canvas.querySelectorAll('.js-dwp-tree-node').length === 0) {
                    canvas.innerHTML = `<div class='dwp-canvas-placeholder-msg' style='color: #64748b; font-style: italic; text-align: center; padding: 50px 10px;'>Toolbar menu is empty. Add shortcuts from the pool and manage your dropdown matrix submenus nesting groups.</div>`;
                }
            }
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('js-dwp-edit-title')) {
            const card = e.target.closest('.js-dwp-tree-node');
            card.querySelector('.js-node-title').innerText = e.target.value.trim();
            serializeActiveTreeStructure();
        }
        if (e.target.classList.contains('js-dwp-edit-url')) {
            serializeActiveTreeStructure();
        }
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.js-dwp-tree-node')];
        return draggableElements.reduce((closest, child) => {
            if (child === draggedItem) {
                return closest;
            }

            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: -Infinity }).element;
    }

    function serializeActiveTreeStructure() {
        const nodes = [...canvas.querySelectorAll('.js-dwp-tree-node')];
        const treeResult = {};

        let level0Key = null;
        let level1Key = null;

        nodes.forEach(node => {
            const id = node.getAttribute('data-id');
            const instanceId = node.getAttribute('data-instance');
            const type = node.getAttribute('data-type') || 'shortcut';
            const depth = parseInt(node.getAttribute('data-depth') || '0', 10);

            const nodePayload = {
                id: id,
                type: type,
                title: node.querySelector('.js-node-title').innerText,
                href: node.querySelector('.js-dwp-edit-url') ? node.querySelector('.js-dwp-edit-url').value.trim() : '#',
                children: {}
            };

            if (depth === 0) {
                treeResult[instanceId] = nodePayload;
                level0Key = instanceId;
                level1Key = null;
            } else if (depth === 1 && level0Key) {
                treeResult[level0Key].children[instanceId] = nodePayload;
                level1Key = instanceId;
            } else if (depth === 2 && level0Key && level1Key) {
                treeResult[level0Key].children[level1Key].children[instanceId] = nodePayload;
            } else {
                treeResult[instanceId] = nodePayload;
                level0Key = instanceId;
                level1Key = null;
                node.setAttribute('data-depth', '0');
                node.style.marginLeft = '0px';
            }
        });

        jsonInput.value = JSON.stringify(treeResult);
    }

    form.addEventListener('submit', function() {
        serializeActiveTreeStructure();
    });
});
