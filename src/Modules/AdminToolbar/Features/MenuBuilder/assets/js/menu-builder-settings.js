/**
 * Admin Toolbar Menu Builder settings tab.
 *
 * Builds the pool of shortcuts and the menu tree from the JSON the tab renders, and writes the
 * tree back to a hidden field as nested JSON on every change. The tree is kept as a flat list of
 * nodes with a depth; a node's children are the nodes after it that are deeper.
 *
 * @since 1.0.0
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		const root = document.querySelector('.js-dwp-menu-builder');
		if (!root) {
			return;
		}

		const data       = JSON.parse(root.querySelector('.js-dwp-mb-data').textContent);
		const t          = data.i18n;
		const maxDepth   = data.maxDepth;
		const pool       = root.querySelector('.js-dwp-mb-pool');
		const tree       = root.querySelector('.js-dwp-mb-tree');
		const filter     = root.querySelector('.js-dwp-mb-filter');
		const actions    = root.querySelector('.js-dwp-mb-pool-actions');
		const serialized = root.querySelector('.js-dwp-mb-serialized');
		const form       = root.closest('form');

		// The data behind each pool item and tree node.
		const rows = new WeakMap();

		let dragged = null; // { origin: 'pool' | 'tree', nodes: Element[] }

		/* ------------------------------------------------------------------ helpers */

		function el(tag, className, text) {
			const element = document.createElement(tag);
			if (className) {
				element.className = className;
			}
			if (undefined !== text) {
				element.textContent = text;
			}
			return element;
		}

		function iconButton(icon, label, className) {
			const button = el('button', 'dwp-mb-icon-button ' + className);
			button.type = 'button';
			button.title = label;
			button.setAttribute('aria-label', label);
			button.appendChild(el('span', 'dashicons ' + icon));
			return button;
		}

		function badge(modifier, text, tip) {
			const element = el('span', 'dwp-mb-badge dwp-mb-badge--' + modifier, text);
			if (tip) {
				element.title = tip;
			}
			return element;
		}

		function statusText(status) {
			return {
				'active': t.statusActive,
				'inactive': t.statusInactive,
				'not-installed': t.statusMissing,
			}[status] || t.statusUnknown;
		}

		function newInstanceId() {
			return 'inst-' + Math.random().toString(36).slice(2, 11);
		}

		/* ------------------------------------------------------------------ badges */

		// Source, link kind (admin or front end) and, if not active, the provider status.
		function metaLine(row) {
			const line = el('span', 'dwp-mb-meta');
			line.appendChild(el('span', 'dwp-mb-source', row.providerName));

			if ('admin' === row.kind) {
				const kind = badge('admin', t.kindAdmin, t.kindAdminTip);
				kind.prepend(el('span', 'dashicons dashicons-dashboard'));
				line.appendChild(kind);
			} else if ('frontend' === row.kind) {
				const kind = badge('frontend', t.kindFrontend, t.kindFrontendTip);
				kind.prepend(el('span', 'dashicons dashicons-admin-home'));
				line.appendChild(kind);
			}

			if ('active' !== row.status) {
				line.appendChild(badge(row.status, statusText(row.status), t.inactiveHint));
			}

			return line;
		}

		function visibilityLabel(visibility) {
			return { 'admin': t.kindAdmin, 'frontend': t.kindFrontend }[visibility] || t.visibleBothShort;
		}

		// One-click choice where a node is shown. A node is shown only where both its parents and
		// the node itself allow it, so under a restricted parent the node keeps its own choice (it
		// usually follows from where its link goes); the parent's restriction is shown next to it,
		// and a choice that conflicts with it (never shown) is marked, not changed.
		function createVisibilityControl() {
			const wrapper = el('span', 'dwp-mb-visibility');
			const group   = el('span', 'dwp-mb-segmented');
			group.setAttribute('role', 'group');
			group.setAttribute('aria-label', t.visibility);

			[ [ 'admin', t.visibleAdmin ], [ 'both', t.visibleBoth ], [ 'frontend', t.visibleFrontend ] ].forEach(function (option) {
				const button = el('button', 'dwp-mb-segment', visibilityLabel(option[ 0 ]));
				button.type = 'button';
				button.title = option[ 1 ];
				button.dataset.visibility = option[ 0 ];
				group.appendChild(button);
			});

			wrapper.append(group, el('span', 'dwp-mb-inherited'));
			return wrapper;
		}

		function refreshVisibility(node, restriction) {
			const state     = rows.get(node);
			const inherited = node.querySelector('.dwp-mb-inherited');
			const conflict  = '' !== restriction && 'both' !== state.visibility && restriction !== state.visibility;

			node.querySelectorAll('.dwp-mb-segment').forEach(function (button) {
				button.setAttribute('aria-pressed', String(button.dataset.visibility === state.visibility));
			});

			node.classList.toggle('is-restricted', '' !== restriction);
			node.classList.toggle('has-visibility-conflict', conflict);
			inherited.textContent = '' !== restriction ? t.inheritedVisibility.replace('%s', visibilityLabel(restriction)) : '';
			inherited.title = conflict ? t.visibilityConflict.replace('%s', visibilityLabel(state.visibility)) : '';
		}

		/* ------------------------------------------------------------------ pool */

		function createPoolItem(row) {
			const item = el('div', 'dwp-mb-node dwp-mb-node--pool js-dwp-mb-pool-item');
			item.draggable = true;
			item.dataset.provider = row.provider;
			item.dataset.type = row.type;
			if ('active' !== row.status) {
				item.classList.add('is-inactive');
			}
			if (row.label) {
				item.title = row.label;
			}

			const checkbox = el('input', 'js-dwp-mb-check');
			checkbox.type = 'checkbox';
			checkbox.setAttribute('aria-label', row.defaultTitle);

			const body = el('span', 'dwp-mb-node-body');
			body.appendChild(el('strong', 'dwp-mb-title', row.defaultTitle));
			body.appendChild(metaLine(row));

			item.append(checkbox, body);
			rows.set(item, row);
			return item;
		}

		function buildFilter() {
			filter.appendChild(new Option(t.allSources, 'all'));

			[ 'active', 'inactive', 'not-installed' ].forEach(function (status) {
				const providers = data.providers.filter(function (provider) {
					return provider.status === status;
				});
				if (!providers.length) {
					return;
				}

				const group = el('optgroup');
				group.label = statusText(status);
				providers.forEach(function (provider) {
					group.appendChild(new Option(provider.name, provider.id));
				});
				filter.appendChild(group);
			});

			filter.addEventListener('change', function () {
				selectAll.checked = false;
				pool.querySelectorAll('.js-dwp-mb-pool-item').forEach(function (item) {
					const visible = 'all' === filter.value || 'submenu' === item.dataset.type || item.dataset.provider === filter.value;
					item.hidden = !visible;
					item.querySelector('.js-dwp-mb-check').checked = false;
				});
			});
		}

		const addButton = el('button', 'button button-secondary', t.addSelected);
		addButton.type = 'button';

		const selectAll = el('input');
		selectAll.type = 'checkbox';
		selectAll.id = 'js-dwp-mb-select-all';

		const selectAllLabel = el('label', '', t.selectAll);
		selectAllLabel.htmlFor = selectAll.id;

		actions.append(addButton, selectAll, selectAllLabel);

		selectAll.addEventListener('change', function () {
			pool.querySelectorAll('.js-dwp-mb-pool-item:not([hidden]) .js-dwp-mb-check').forEach(function (checkbox) {
				checkbox.checked = selectAll.checked;
			});
		});

		addButton.addEventListener('click', function () {
			const checked = [ ...pool.querySelectorAll('.js-dwp-mb-check:checked') ];
			if (!checked.length) {
				window.alert(t.selectFirst);
				return;
			}

			insertRows(checked.map(function (checkbox) {
				checkbox.checked = false;
				return rows.get(checkbox.closest('.js-dwp-mb-pool-item'));
			}), null);
			selectAll.checked = false;
		});

		/* ------------------------------------------------------------------ tree */

		function treeNodes() {
			return [ ...tree.querySelectorAll('.js-dwp-mb-tree-node') ];
		}

		function depthOf(node) {
			return parseInt(node.dataset.depth, 10) || 0;
		}

		function setDepth(node, depth) {
			node.dataset.depth = String(depth);
			node.style.setProperty('--depth', String(depth));
		}

		// The node and the nodes below it.
		function branchOf(node) {
			const nodes  = treeNodes();
			const start  = nodes.indexOf(node);
			const branch = [ node ];
			for (let i = start + 1; i < nodes.length && depthOf(nodes[ i ]) > depthOf(node); i++) {
				branch.push(nodes[ i ]);
			}
			return branch;
		}

		function displayTitle(state) {
			if (state.title) {
				return state.title;
			}
			return 'submenu' === state.type ? t.submenuTitle : state.defaultTitle;
		}

		function refreshHeader(node) {
			const state = rows.get(node);
			node.querySelector('.dwp-mb-title').textContent = displayTitle(state);
		}

		function field(labelText, input, hint) {
			const wrapper = el('div', 'dwp-mb-field');
			const label   = el('label', '', labelText);
			input.id      = 'dwp-mb-' + Math.random().toString(36).slice(2, 11);
			label.htmlFor = input.id;
			wrapper.append(label, input);
			if (hint) {
				wrapper.appendChild(el('p', 'description', hint));
			}
			return wrapper;
		}

		function createEditPanel(node, state) {
			const panel = el('div', 'dwp-mb-edit');
			panel.hidden = true;

			const title = el('input', 'regular-text');
			title.type = 'text';
			title.value = state.title;
			title.placeholder = 'submenu' === state.type ? t.submenuTitle : state.defaultTitle;
			title.addEventListener('input', function () {
				state.title = title.value.trim();
				refreshHeader(node);
				serialize();
			});
			panel.appendChild(field(t.title, title, 'submenu' === state.type ? '' : t.titleHint));

			const url = el('input', 'regular-text code');
			url.type = 'text';
			url.value = state.href;
			if ('submenu' === state.type) {
				url.addEventListener('input', function () {
					state.href = url.value.trim();
					serialize();
				});
				panel.appendChild(field(t.url, url, t.urlHint));
			} else {
				url.readOnly = true;
				const copy = el('button', 'button button-small', t.copy);
				copy.type = 'button';
				copy.addEventListener('click', function () {
					navigator.clipboard.writeText(url.value).then(function () {
						copy.textContent = t.copied;
						window.setTimeout(function () {
							copy.textContent = t.copy;
						}, 1500);
					});
				});
				const urlField = field(t.url, url);
				urlField.appendChild(copy);
				urlField.classList.add('dwp-mb-field--url');
				panel.appendChild(urlField);
			}

			return panel;
		}

		function createTreeNode(row, depth) {
			const state = {
				id: row.id,
				type: row.type,
				defaultTitle: row.defaultTitle,
				title: row.title || '',
				href: row.href || '',
				visibility: row.visibility || 'both',
			};

			const node = el('div', 'dwp-mb-node dwp-mb-node--tree js-dwp-mb-tree-node');
			node.draggable = true;
			node.dataset.instance = row.instance || newInstanceId();
			if ('submenu' === row.type) {
				node.classList.add('is-submenu');
			}
			if ('active' !== row.status) {
				node.classList.add('is-inactive');
			}
			setDepth(node, depth);
			rows.set(node, state);

			const header = el('div', 'dwp-mb-node-header');
			const select = el('input', 'dwp-mb-select js-dwp-mb-select');
			select.type = 'checkbox';
			select.setAttribute('aria-label', t.select);
			header.appendChild(select);

			const body   = el('span', 'dwp-mb-node-body');
			const meta   = metaLine(row);
			meta.appendChild(createVisibilityControl());
			body.appendChild(el('strong', 'dwp-mb-title'));
			body.appendChild(meta);

			const controls = el('span', 'dwp-mb-controls');
			controls.append(
				iconButton('dashicons-arrow-left-alt2', t.moveOut, 'js-dwp-mb-out'),
				iconButton('dashicons-arrow-right-alt2', t.moveIn, 'js-dwp-mb-in'),
				iconButton('dashicons-admin-generic', t.edit, 'js-dwp-mb-edit'),
				iconButton('dashicons-trash', t.remove, 'js-dwp-mb-remove'),
				el('span', 'dashicons dashicons-menu dwp-mb-handle')
			);

			header.append(body, controls);
			node.append(header, createEditPanel(node, state));
			refreshHeader(node);
			return node;
		}

		function updateEmptyState() {
			let message = tree.querySelector('.dwp-mb-empty');
			if (treeNodes().length) {
				if (message) {
					message.remove();
				}
			} else if (!message) {
				message = el('p', 'dwp-mb-empty', t.emptyTree);
				tree.appendChild(message);
			}
		}

		// New nodes take the depth of the node they are inserted before, so they never take over
		// that node's children; at the end they take the depth of the last node.
		function insertionDepth(before) {
			if (before) {
				return depthOf(before);
			}
			const nodes = treeNodes();
			return nodes.length ? depthOf(nodes[ nodes.length - 1 ]) : 0;
		}

		function insertRows(newRows, before) {
			const depth = insertionDepth(before);
			newRows.forEach(function (row) {
				const node = createTreeNode(row, depth);
				tree.insertBefore(node, before);
				if ('submenu' === row.type) {
					node.querySelector('.dwp-mb-edit').hidden = false;
				}
			});
			updateEmptyState();
			serialize();
		}

		// Moves a branch in or out by one level, if the result is still a valid tree. Moving out
		// places the branch after its old parent's branch, so it does not take over its siblings.
		function shiftBranch(node, delta) {
			const branch   = branchOf(node);
			const nodes    = treeNodes();
			const index    = nodes.indexOf(node);
			const previous = nodes[ index - 1 ];
			const newDepth = depthOf(node) + delta;
			const deepest  = Math.max(...branch.map(depthOf)) + delta;

			if (newDepth < 0 || deepest > maxDepth) {
				return;
			}
			if (delta > 0 && (!previous || newDepth > depthOf(previous) + 1)) {
				return;
			}

			if (delta < 0) {
				const parent = nodes.slice(0, index).reverse().find(function (item) {
					return depthOf(item) < depthOf(node);
				});
				const parentBranch = branchOf(parent);
				const anchor       = parentBranch[ parentBranch.length - 1 ].nextElementSibling;
				branch.forEach(function (item) {
					tree.insertBefore(item, anchor);
				});
			}

			branch.forEach(function (item) {
				setDepth(item, depthOf(item) + delta);
			});
			serialize();
		}

		function removeNodes(nodes) {
			nodes.forEach(function (item) {
				selected.delete(item);
				item.remove();
			});
			updateEmptyState();
			updateBulkBar();
			serialize();
		}

		function removeBranch(node) {
			const branch = branchOf(node);
			if (branch.length > 1 && !window.confirm(t.removeConfirm.replace('%d', String(branch.length - 1)))) {
				return;
			}
			removeNodes(branch);
		}

		/* ------------------------------------------------------------------ selection and bulk actions */

		const selected = new Set();
		let anchor     = null; // The node a shift-click range starts from.

		function setSelected(node, on) {
			if (on) {
				selected.add(node);
			} else {
				selected.delete(node);
			}
			node.classList.toggle('is-selected', on);
			node.querySelector('.js-dwp-mb-select').checked = on;
		}

		function clearSelection() {
			[ ...selected ].forEach(function (node) {
				setSelected(node, false);
			});
			updateBulkBar();
		}

		// Plain click toggles a node; shift-click selects the range from the last clicked node.
		function clickSelect(node, range) {
			const nodes = treeNodes();
			if (range && anchor && nodes.includes(anchor)) {
				const from = nodes.indexOf(anchor);
				const to   = nodes.indexOf(node);
				nodes.slice(Math.min(from, to), Math.max(from, to) + 1).forEach(function (item) {
					setSelected(item, true);
				});
			} else {
				setSelected(node, !selected.has(node));
			}
			anchor = node;
			updateBulkBar();
		}

		// The selected nodes in tree order, leaving out those that lie in the branch of another
		// selected node: moving or removing a node always takes its branch along.
		function selectedRoots() {
			const roots = [];
			let coveredBelow = -1; // Nodes deeper than this depth lie in the branch of the last root.

			treeNodes().forEach(function (node) {
				if (coveredBelow >= 0 && depthOf(node) > coveredBelow) {
					return;
				}
				coveredBelow = -1;
				if (selected.has(node)) {
					roots.push(node);
					coveredBelow = depthOf(node);
				}
			});
			return roots;
		}

		function branchesOf(roots) {
			return roots.flatMap(branchOf);
		}

		const bulkBar   = el('div', 'dwp-mb-bulk');
		const bulkCount = el('span', 'dwp-mb-bulk-count');
		const bulkShow  = createVisibilityControl();
		bulkShow.querySelector('.dwp-mb-inherited').remove();
		bulkShow.prepend(el('span', 'dwp-mb-bulk-label', t.visibility));

		function bulkButton(text, className, tip) {
			const button = el('button', 'button button-small ' + className, text);
			button.type = 'button';
			if (tip) {
				button.title = tip;
			}
			return button;
		}

		bulkBar.append(
			bulkCount,
			bulkShow,
			bulkButton(t.resetTitles, 'js-dwp-mb-bulk-reset', t.resetTitlesTip),
			bulkButton(t.remove, 'js-dwp-mb-bulk-remove'),
			bulkButton(t.clearSelection, 'js-dwp-mb-bulk-clear')
		);
		bulkBar.hidden = true;
		tree.before(bulkBar);

		function updateBulkBar() {
			bulkBar.hidden = 0 === selected.size;
			bulkCount.textContent = t.selectedCount.replace('%d', String(selected.size));
		}

		bulkBar.addEventListener('click', function (event) {
			const button = event.target.closest('button');
			if (!button) {
				return;
			}

			if (button.dataset.visibility) {
				selected.forEach(function (node) {
					rows.get(node).visibility = button.dataset.visibility;
				});
				serialize();
			} else if (button.classList.contains('js-dwp-mb-bulk-reset')) {
				selected.forEach(function (node) {
					const state = rows.get(node);
					if ('shortcut' === state.type) {
						state.title = '';
						node.querySelector('.dwp-mb-edit input[type="text"]').value = '';
						refreshHeader(node);
					}
				});
				serialize();
			} else if (button.classList.contains('js-dwp-mb-bulk-remove')) {
				const nodes = branchesOf(selectedRoots());
				if (window.confirm(t.removeSelectedConfirm.replace('%d', String(nodes.length)))) {
					removeNodes(nodes);
				}
			} else if (button.classList.contains('js-dwp-mb-bulk-clear')) {
				clearSelection();
			}
		});

		tree.addEventListener('click', function (event) {
			const node = event.target.closest('.js-dwp-mb-tree-node');
			if (node && event.target.classList.contains('js-dwp-mb-select')) {
				clickSelect(node, event.shiftKey);
				return;
			}

			const button = event.target.closest('button');
			if (!button || !node) {
				return;
			}

			if (button.dataset.visibility) {
				rows.get(node).visibility = button.dataset.visibility;
				serialize();
			} else if (button.classList.contains('js-dwp-mb-out')) {
				shiftBranch(node, -1);
			} else if (button.classList.contains('js-dwp-mb-in')) {
				shiftBranch(node, 1);
			} else if (button.classList.contains('js-dwp-mb-edit')) {
				const panel = node.querySelector('.dwp-mb-edit');
				panel.hidden = !panel.hidden;
				button.classList.toggle('is-open', !panel.hidden);
			} else if (button.classList.contains('js-dwp-mb-remove')) {
				removeBranch(node);
			}
		});

		/* ------------------------------------------------------------------ drag and drop */

		function clearDropMarker() {
			root.querySelectorAll('.is-drop-before').forEach(function (node) {
				node.classList.remove('is-drop-before');
			});
			tree.classList.remove('is-drop-end');
		}

		// The first node (outside the dragged branch) whose middle is below the pointer.
		function nodeAfterPointer(y) {
			return treeNodes().find(function (node) {
				if (dragged && dragged.nodes.includes(node)) {
					return false;
				}
				const box = node.getBoundingClientRect();
				return y < box.top + box.height / 2;
			}) || null;
		}

		root.addEventListener('dragstart', function (event) {
			if (event.target.closest('input, select, button')) {
				return;
			}

			const treeNode = event.target.closest('.js-dwp-mb-tree-node');
			const poolItem = event.target.closest('.js-dwp-mb-pool-item');

			if (treeNode) {
				// Dragging a selected node drags the whole selection, each with its branch.
				const roots = selected.has(treeNode) ? selectedRoots() : [ treeNode ];
				dragged = { origin: 'tree', roots: roots, nodes: branchesOf(roots) };
			} else if (poolItem) {
				dragged = { origin: 'pool', nodes: [ poolItem ] };
			} else {
				return;
			}

			event.dataTransfer.effectAllowed = 'tree' === dragged.origin ? 'move' : 'copy';
			event.dataTransfer.setData('text/plain', '');
			dragged.nodes.forEach(function (node) {
				node.classList.add('is-dragging');
			});
		});

		root.addEventListener('dragend', function () {
			if (dragged) {
				dragged.nodes.forEach(function (node) {
					node.classList.remove('is-dragging');
				});
			}
			dragged = null;
			clearDropMarker();
		});

		tree.addEventListener('dragover', function (event) {
			if (!dragged) {
				return;
			}
			event.preventDefault();

			// Scroll the tree while dragging near its top or bottom edge.
			const box = tree.getBoundingClientRect();
			if (event.clientY < box.top + 40) {
				tree.scrollTop -= 12;
			} else if (event.clientY > box.bottom - 40) {
				tree.scrollTop += 12;
			}

			clearDropMarker();
			const after = nodeAfterPointer(event.clientY);
			if (after) {
				after.classList.add('is-drop-before');
			} else {
				tree.classList.add('is-drop-end');
			}
		});

		tree.addEventListener('dragleave', function (event) {
			if (!tree.contains(event.relatedTarget)) {
				clearDropMarker();
			}
		});

		tree.addEventListener('drop', function (event) {
			if (!dragged) {
				return;
			}
			event.preventDefault();
			const after = nodeAfterPointer(event.clientY);
			clearDropMarker();

			if ('pool' === dragged.origin) {
				insertRows([ rows.get(dragged.nodes[ 0 ]) ], after);
				return;
			}

			// Move each branch; its top node takes the depth of the node it lands before, like a new node.
			const depth = insertionDepthAfterMove(after);
			dragged.roots.forEach(function (root) {
				const delta = depth - depthOf(root);
				branchOf(root).forEach(function (node) {
					tree.insertBefore(node, after);
					setDepth(node, Math.min(maxDepth, Math.max(0, depthOf(node) + delta)));
				});
			});
			serialize();
		});

		// Like insertionDepth(), but ignoring the branch that is being moved.
		function insertionDepthAfterMove(before) {
			if (before) {
				return depthOf(before);
			}
			const nodes = treeNodes().filter(function (node) {
				return !dragged.nodes.includes(node);
			});
			return nodes.length ? depthOf(nodes[ nodes.length - 1 ]) : 0;
		}

		/* ------------------------------------------------------------------ serialize */

		// Writes the tree as nested JSON. A node can be at most one level deeper than the one before it.
		// Also refreshes what each node inherits from its parents.
		function serialize() {
			const result       = {};
			const stack        = []; // stack[ depth ] = children object of the last node at that depth.
			const restrictions = []; // restrictions[ depth ] = visibility restriction ('' for none) of that node.
			let previous       = -1;

			treeNodes().forEach(function (node) {
				const state = rows.get(node);
				const depth = Math.min(depthOf(node), previous + 1, maxDepth);
				if (depth !== depthOf(node)) {
					setDepth(node, depth);
				}

				const inherited = 0 === depth ? '' : restrictions[ depth - 1 ];
				refreshVisibility(node, inherited);
				restrictions[ depth ] = inherited || ('both' === state.visibility ? '' : state.visibility);

				const payload = {
					id: state.id,
					type: state.type,
					title: state.title,
					href: 'submenu' === state.type ? state.href : '',
					visibility: state.visibility,
					children: {},
				};

				const parent = 0 === depth ? result : stack[ depth - 1 ];
				parent[ node.dataset.instance ] = payload;
				stack[ depth ] = payload.children;
				stack.length = depth + 1;
				previous = depth;
			});

			serialized.value = JSON.stringify(result);
		}

		/* ------------------------------------------------------------------ start */

		data.pool.forEach(function (row) {
			pool.appendChild(createPoolItem(row));
		});
		buildFilter();

		data.tree.forEach(function (row) {
			tree.appendChild(createTreeNode(row, row.depth));
		});
		updateEmptyState();
		serialize();

		if (form) {
			form.addEventListener('submit', serialize);
		}
	});
}());
