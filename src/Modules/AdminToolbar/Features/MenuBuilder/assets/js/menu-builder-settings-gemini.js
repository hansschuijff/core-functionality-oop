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

    // 3. SELECTION BULK INSERTION ENGINE
    insertBtn.addEventListener('click', function() {
        const checkedBoxes = pool.querySelectorAll('.js-pool-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one shortcut checkbox to insert.');
            return;
        }

        const placeholder = canvas.querySelector('.dwp-canvas-placeholder-msg');
        if (placeholder) placeholder.style.display = 'none';

        checkedBoxes.forEach(cb => {
            const sourceNode = cb.closest('.js-dwp-tree-node');
            if (sourceNode) {
                cloneAndInsertNode(
                    sourceNode.getAttribute('data-id'),
                    sourceNode.querySelector('.js-node-title').innerText,
                    sourceNode.getAttribute('data-provider'),
                    sourceNode.getAttribute('data-type') || 'shortcut',
                    '#',
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

    // 4. CLONE CONSTRUCTOR HOOK FUNCTION (With precise target element positioning support)
    function cloneAndInsertNode(id, title, provider, type, href, depth, referenceNode) {
        const instanceId = 'inst-' + Math.random().toString(36).substr(2, 9);
        const borderLeft = (provider === 'custom_link') ? '4px solid #16a34a' : '4px solid #2271b1';
        const friendlySource = provider.toUpperCase().replace(/[-_]/g, ' ');

        // If the node injected is the blank stencil, convert title natively into a clean safe slug string handle
        let targetId = id;
        if (provider === 'custom_link' && id === 'custom-link-node') {
            targetId = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
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

        item.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0; width: 100%; box-sizing: border-box;">
                <div style="display: flex; flex-direction: column; gap: 2px; max-width: 80%;">
                    <strong class="js-node-title" style="font-size: 13px; color: #1e293b;">${title}</strong>
                    <span style="font-family: monospace; font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${targetId}</span>
                    <span style="font-size: 10px; background: #e2e8f0; color: #475569; padding: 1px 5px; border-radius: 3px; font-weight: 600; width: fit-content; margin-top: 3px; font-family: sans-serif;">${friendlySource}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; color: #94a3b8;">
                    <span class="dashicons dashicons-arrow-down-alt2 js-dwp-toggle-edit" title="Toggle Configuration" style="cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #475569; transition: transform 0.2s;"></span>
                    <span class="dashicons dashicons-trash js-dwp-remove-node" title="Remove item" style="cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #ef4444;"></span>
                    <span class="dashicons dashicons-editor-justify" style="font-size: 18px; width: 18px; height: 18px;"></span>
                </div>
            </div>
            <div class="js-dwp-edit-panel" style="display: none; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 15px; margin-top: 10px; box-sizing: border-box; width: 100%;">
                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Navigation Label</label>
                    <input type="text" class="js-dwp-edit-title regular-text" value="${title}" style="width: 100%; height: 28px; font-size: 12px;" />
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Target URL Destination</label>
                    <input type="text" class="js-dwp-edit-url regular-text" value="${href}" style="width: 100%; height: 28px; font-size: 12px; font-family: monospace;" />
                </div>
                <div style="border-top: 1px dashed #cbd5e1; padding-top: 10px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 6px;">Configure Level Position:</label>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="button button-small js-dwp-move-outward" style="font-size: 11px;">« Move Outward</button>
                        <button type="button" class="button button-small js-dwp-move-inward" style="font-size: 11px; color: #2271b1; border-color: #cbd5e1;">Move Inward »</button>
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

    // 5. UNIFIED DRAG & DROP COUPLING SYSTEM: Accepts drag signals from both containers flawlessly
    document.addEventListener('dragstart', function(e) {
        const node = e.target.closest('.js-dwp-tree-node');
        if (node) {
            draggedItem = node;
            node.style.opacity = '0.4';
            e.dataTransfer.setData('text/plain', node.getAttribute('data-id'));
        }
    });

    document.addEventListener('dragend', function(e) {
        if (draggedItem) {
            draggedItem.style.opacity = '1';
            draggedItem = null;
            serializeActiveTreeStructure();
        }
    });

    canvas.addEventListener('dragover', function(e) {
        e.preventDefault(); // CRUCIAL GATE: Formally destroys browser protection to allow rotsvast locking drops!
        if (!draggedItem) return;

        // Process Vertical sorter tracking preview alignment across the grid canvas workspace
        const afterElement = getDragAfterElement(canvas, e.clientY);
        if (afterElement == null) {
            canvas.appendChild(draggedItem);
        } else {
            canvas.insertBefore(draggedItem, afterElement);
        }
    });

    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        if (!draggedItem) return;

        // FIXED LANDING: If the item originates from the pool, execute a clean clone mutation directly at the dropped position
        if (draggedItem.parentNode === pool) {
            const afterElement = getDragAfterElement(canvas, e.clientY);
            const insertedNode = cloneAndInsertNode(
                draggedItem.getAttribute('data-id'),
                draggedItem.querySelector('.js-node-title').innerText,
                draggedItem.getAttribute('data-provider'),
                draggedItem.getAttribute('data-type') || 'shortcut',
                '#',
                0,
                afterElement
            );

            // Automatically unfold the card config form if it represents a blank submenu node to entice immediate labeling
            if (insertedNode.getAttribute('data-provider') === 'custom_link') {
                const editPanel = insertedNode.querySelector('.js-dwp-edit-panel');
                const toggleBtn = insertedNode.querySelector('.js-dwp-toggle-edit');
                if (editPanel) editPanel.style.display = 'block';
                if (toggleBtn) toggleBtn.style.transform = 'rotate(180deg)';
            }
        }

        serializeActiveTreeStructure();
    });

    // 6. INLINE INTERACTIVE CONTROLS & STEPPER ENGINE DELEGATION (The Safe Native WP Way)
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
                card.setAttribute('data-depth', depth);
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
                card.setAttribute('data-depth', depth);
                card.style.marginLeft = (depth * 30) + 'px';
                serializeActiveTreeStructure();
            }
        }

        // CASCADING WIPE: Erases a parent item along with all its deep child paths out of the layout
        if (e.target.classList.contains('js-dwp-remove-node')) {
            const currentItem = e.target.closest('.js-dwp-tree-node');
            if (currentItem) {
                const nodes = [...canvas.querySelectorAll('.js-dwp-tree-node')];
                const currentIndex = nodes.indexOf(currentItem);
                const currentDepth = parseInt(currentItem.getAttribute('data-depth'] || '0', 10);

                let itemsToRemove = [currentItem];
                for (let i = currentIndex + 1; i < nodes.length; i++) {
                    const nextDepth = parseInt(nodes[i].getAttribute('data-depth') || '0', 10);
                    if (nextDepth > currentDepth) {
                        itemsToRemove.push(nodes[i]);
                    } else {
                        break;
                    }
                }

                itemsToRemove.forEach(item => item.parentNode.removeChild(item));
                serializeActiveTreeStructure();

                if (canvas.querySelectorAll('.js-dwp-tree-node').length === 0) {
                    const msg = canvas.querySelector('.dwp-canvas-placeholder-msg');
                    if (msg) msg.style.display = 'block';
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
        const draggableElements = [...container.querySelectorAll('.js-dwp-tree-node:not(style*="opacity: 0.4")')];
        return draggableElements.reduce((closest, child) => {
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
