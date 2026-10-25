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
		const tools      = root.querySelector('.js-dwp-mb-pool-tools');
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

		// Remembered in the browser: which pool groups are open and which tree branches are collapsed.
		function loadSet(key) {
			try {
				return new Set(JSON.parse(window.localStorage.getItem(key) || '[]'));
			} catch (error) {
				return new Set();
			}
		}

		function saveSet(key, set) {
			try {
				window.localStorage.setItem(key, JSON.stringify([ ...set ]));
			} catch (error) {
				// Storage unavailable: the state is simply not remembered.
			}
		}

		const openGroups = loadSet('dwp-mb-open-groups');
		const groups     = []; // { element: details, count: span }

		// The pool: the custom submenu first, then one collapsible group per provider.
		function buildPool() {
			const providers = new Map(data.providers.map(function (provider) {
				return [ provider.id, provider ];
			}));
			const lists = new Map();

			data.pool.forEach(function (row) {
				const item = createPoolItem(row);
				if (!row.provider) {
					pool.appendChild(item);
					return;
				}

				if (!lists.has(row.provider)) {
					const provider = providers.get(row.provider) || { id: row.provider, name: row.providerName, status: row.status };
					const group    = el('details', 'dwp-mb-group');
					const summary  = el('summary', 'dwp-mb-group-summary');
					const count    = el('span', 'dwp-mb-group-count');

					group.dataset.provider = provider.id;
					group.open = openGroups.has(provider.id);
					summary.append(el('span', 'dwp-mb-group-name', provider.name), count);
					if ('active' !== provider.status) {
						summary.appendChild(badge(provider.status, statusText(provider.status), t.inactiveHint));
					}
					group.appendChild(summary);
					group.addEventListener('toggle', function () {
						if (!searchInput.value.trim()) {
							if (group.open) {
								openGroups.add(provider.id);
							} else {
								openGroups.delete(provider.id);
							}
							saveSet('dwp-mb-open-groups', openGroups);
						}
					});

					groups.push({ element: group, count: count });
					lists.set(row.provider, group);
					pool.appendChild(group);
				}
				lists.get(row.provider).appendChild(item);
			});
		}

		// Search (title, source, description, id) and "only active"; while searching, groups with
		// a match open, and the remembered state returns when the search is cleared.
		function filterPool() {
			const query      = searchInput.value.trim().toLowerCase();
			const activeOnly = activeInput.checked;

			pool.querySelectorAll('.js-dwp-mb-pool-item').forEach(function (item) {
				const row  = rows.get(item);
				const text = [ row.defaultTitle, row.providerName, row.label, row.id ].join(' ').toLowerCase();
				item.hidden = (activeOnly && 'active' !== row.status) || ('' !== query && !text.includes(query));
			});

			groups.forEach(function (group) {
				const visible = group.element.querySelectorAll('.js-dwp-mb-pool-item:not([hidden])').length;
				group.element.hidden = 0 === visible;
				group.count.textContent = String(visible);
				group.element.open = '' !== query ? visible > 0 : openGroups.has(group.element.dataset.provider);
			});
		}

		// A pool item the user can see: not filtered out and not in a closed group.
		function isShown(item) {
			const group = item.closest('details');
			return !item.hidden && (!group || (group.open && !group.hidden));
		}

		const searchInput = el('input', 'dwp-mb-search');
		searchInput.type = 'search';
		searchInput.placeholder = t.search;
		searchInput.setAttribute('aria-label', t.search);
		searchInput.addEventListener('input', filterPool);

		const activeInput = el('input');
		activeInput.type = 'checkbox';
		activeInput.id = 'js-dwp-mb-active-only';
		activeInput.addEventListener('change', filterPool);

		const activeLabel = el('label', 'dwp-mb-active-only', t.onlyActive);
		activeLabel.prepend(activeInput);

		tools.append(searchInput, activeLabel);

		const addButton = el('button', 'button button-secondary', t.addSelected);
		addButton.type = 'button';
		addButton.title = t.addSelectedTip;

		const selectAll = el('input');
		selectAll.type = 'checkbox';
		selectAll.id = 'js-dwp-mb-select-all';

		const selectAllLabel = el('label', '', t.selectAll);
		selectAllLabel.htmlFor = selectAll.id;

		actions.append(addButton, selectAll, selectAllLabel);

		selectAll.addEventListener('change', function () {
			pool.querySelectorAll('.js-dwp-mb-pool-item').forEach(function (item) {
				item.querySelector('.js-dwp-mb-check').checked = selectAll.checked && isShown(item);
			});
		});

		// With exactly one node selected in the menu, new nodes go below it: inside it when it is a
		// submenu without a link of its own, otherwise after its branch. Without one, at the end.
		function insertionPoint() {
			if (1 !== selected.size) {
				return { before: null, depth: insertionDepth(null) };
			}

			const target = [ ...selected ][ 0 ];
			const branch = branchOf(target);
			const state  = rows.get(target);
			const before = branch[ branch.length - 1 ].nextElementSibling;
			const inside = 'submenu' === state.type && '' === state.href && depthOf(target) < maxDepth;

			if (inside) {
				setCollapsed(target, false);
			}
			return { before: before, depth: depthOf(target) + (inside ? 1 : 0) };
		}

		addButton.addEventListener('click', function () {
			const checked = [ ...pool.querySelectorAll('.js-dwp-mb-check:checked') ];
			if (!checked.length) {
				window.alert(t.selectFirst);
				return;
			}

			const point = insertionPoint();
			insertRows(checked.map(function (checkbox) {
				checkbox.checked = false;
				return rows.get(checkbox.closest('.js-dwp-mb-pool-item'));
			}), point.before, point.depth);
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
			node.setAttribute('aria-level', String(depth + 1));
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
			node.tabIndex = -1;
			node.setAttribute('role', 'treeitem');
			node.setAttribute('aria-selected', 'false');
			node.dataset.instance = row.instance || newInstanceId();
			if ('submenu' === row.type) {
				node.classList.add('is-submenu');
			}
			if ('active' !== row.status) {
				node.classList.add('is-inactive');
			}
			setDepth(node, depth);
			rows.set(node, state);

			// Two rows in a grid: toggle, checkbox, title and buttons; below them the source and
			// badges, and the visibility switch under the buttons.
			const header = el('div', 'dwp-mb-node-header dwp-mb-node-header--tree');
			const toggle = iconButton('dashicons-arrow-down-alt2', t.collapse, 'dwp-mb-collapse js-dwp-mb-collapse');
			toggle.tabIndex = -1; // The arrow keys collapse and expand.

			// Out of the tab order: the node itself takes the focus and Space selects it.
			const select = el('input', 'dwp-mb-select js-dwp-mb-select');
			select.type = 'checkbox';
			select.tabIndex = -1;
			select.title = t.select;
			select.setAttribute('aria-label', t.select);

			const controls = el('span', 'dwp-mb-controls');
			controls.append(
				iconButton('dashicons-arrow-left-alt2', t.moveOut, 'js-dwp-mb-out'),
				iconButton('dashicons-arrow-right-alt2', t.moveIn, 'js-dwp-mb-in'),
				iconButton('dashicons-admin-generic', t.edit, 'js-dwp-mb-edit'),
				iconButton('dashicons-trash', t.remove, 'js-dwp-mb-remove'),
				el('span', 'dashicons dashicons-menu dwp-mb-handle')
			);

			header.append(toggle, select, el('strong', 'dwp-mb-title'), controls, metaLine(row), createVisibilityControl());
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

		function insertRows(newRows, before, depth) {
			const at    = undefined === depth ? insertionDepth(before) : depth;
			const added = newRows.map(function (row) {
				const node = createTreeNode(row, at);
				tree.insertBefore(node, before);
				if ('submenu' === row.type) {
					node.querySelector('.dwp-mb-edit').hidden = false;
				}
				return node;
			});
			updateEmptyState();
			serialize();
			if (added.length) {
				added[ 0 ].scrollIntoView({ block: 'nearest' });
			}
		}

		/* ------------------------------------------------------------------ collapsing branches */

		const collapsed = loadSet('dwp-mb-collapsed'); // Instance ids of collapsed nodes.

		function setCollapsed(node, on) {
			if (on) {
				collapsed.add(node.dataset.instance);
			} else {
				collapsed.delete(node.dataset.instance);
			}
			saveSet('dwp-mb-collapsed', collapsed);
			refreshTreeState();
		}

		// Marks the nodes that have children and hides the branches of collapsed nodes.
		function refreshTreeState() {
			const nodes = treeNodes();
			let hiddenBelow = -1; // Nodes deeper than this depth lie in a collapsed branch.

			nodes.forEach(function (node, index) {
				const depth       = depthOf(node);
				const next        = nodes[ index + 1 ];
				const hasChildren = !!next && depthOf(next) > depth;
				const isCollapsed = hasChildren && collapsed.has(node.dataset.instance);
				const toggle      = node.querySelector('.js-dwp-mb-collapse');

				if (hiddenBelow >= 0 && depth <= hiddenBelow) {
					hiddenBelow = -1;
				}
				node.hidden = hiddenBelow >= 0;
				node.classList.toggle('has-children', hasChildren);
				node.classList.toggle('is-collapsed', isCollapsed);
				toggle.title = isCollapsed ? t.expand : t.collapse;
				toggle.setAttribute('aria-label', toggle.title);
				toggle.setAttribute('aria-expanded', String(!isCollapsed));
				if (hasChildren) {
					node.setAttribute('aria-expanded', String(!isCollapsed));
				} else {
					node.removeAttribute('aria-expanded');
				}

				if (isCollapsed && hiddenBelow < 0) {
					hiddenBelow = depth;
				}
			});

			ensureTabStop();
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
			node.setAttribute('aria-selected', String(on));
			node.querySelector('.js-dwp-mb-select').checked = on;
		}

		function clearSelection() {
			[ ...selected ].forEach(function (node) {
				setSelected(node, false);
			});
			updateBulkBar();
		}

		// Selects or deselects a node together with the nodes below it.
		function toggleBranchSelection(node) {
			const on = !selected.has(node);
			branchOf(node).forEach(function (item) {
				setSelected(item, on);
			});
			anchor = node;
			updateBulkBar();
		}

		// Plain click toggles a node, Ctrl-click the node with its branch, and shift-click selects
		// the range from the last clicked node.
		function clickSelect(node, range, branch) {
			const nodes = treeNodes();
			if (branch) {
				toggleBranchSelection(node);
				return;
			}
			if (range && anchor && nodes.includes(anchor)) {
				const from = nodes.indexOf(anchor);
				const to   = nodes.indexOf(node);
				nodes.slice(Math.min(from, to), Math.max(from, to) + 1).forEach(function (item) {
					if (!item.hidden) {
						setSelected(item, true);
					}
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
			bulkButton(t.selectBelow, 'js-dwp-mb-bulk-below', t.selectBelowTip),
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
			} else if (button.classList.contains('js-dwp-mb-bulk-below')) {
				branchesOf(selectedRoots()).forEach(function (node) {
					setSelected(node, true);
				});
				updateBulkBar();
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
				clickSelect(node, event.shiftKey, event.ctrlKey || event.metaKey);
				return;
			}

			const button = event.target.closest('button');
			if (!button || !node) {
				return;
			}

			if (button.dataset.visibility) {
				rows.get(node).visibility = button.dataset.visibility;
				serialize();
			} else if (button.classList.contains('js-dwp-mb-collapse')) {
				setCollapsed(node, !collapsed.has(node.dataset.instance));
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

		/* ------------------------------------------------------------------ keyboard */

		// One tree node is in the tab order at a time; the arrow keys move between nodes.
		tree.setAttribute('role', 'tree');
		tree.setAttribute('aria-multiselectable', 'true');
		tree.setAttribute('aria-label', t.treeLabel);

		function visibleNodes() {
			return treeNodes().filter(function (node) {
				return !node.hidden;
			});
		}

		function ensureTabStop() {
			const nodes   = visibleNodes();
			const current = nodes.find(function (node) {
				return 0 === node.tabIndex;
			});
			treeNodes().forEach(function (node) {
				node.tabIndex = node === current ? 0 : -1;
			});
			if (!current && nodes.length) {
				nodes[ 0 ].tabIndex = 0;
			}
		}

		function focusNode(node) {
			if (!node) {
				return;
			}
			treeNodes().forEach(function (item) {
				item.tabIndex = item === node ? 0 : -1;
			});
			node.focus({ preventScroll: true });
			node.scrollIntoView({ block: 'nearest' });
		}

		tree.addEventListener('focusin', function (event) {
			if (event.target.classList.contains('js-dwp-mb-tree-node')) {
				treeNodes().forEach(function (item) {
					item.tabIndex = item === event.target ? 0 : -1;
				});
			}
		});

		// What a keyboard command acts on: the selection if the focused node is part of it,
		// otherwise the focused node; always with their branches.
		function keyTargets(node) {
			return selected.has(node) ? selectedRoots() : [ node ];
		}

		// Moves a branch past its previous or next sibling (with that sibling's branch).
		function moveBranch(node, direction) {
			const nodes  = treeNodes();
			const branch = branchOf(node);
			const depth  = depthOf(node);
			let before;

			if (direction < 0) {
				let sibling = null;
				for (let i = nodes.indexOf(node) - 1; i >= 0 && depthOf(nodes[ i ]) >= depth; i--) {
					if (depthOf(nodes[ i ]) === depth) {
						sibling = nodes[ i ];
						break;
					}
				}
				if (!sibling) {
					return;
				}
				before = sibling;
			} else {
				const next = branch[ branch.length - 1 ].nextElementSibling;
				if (!next || !next.classList.contains('js-dwp-mb-tree-node') || depthOf(next) !== depth) {
					return;
				}
				const siblingBranch = branchOf(next);
				before = siblingBranch[ siblingBranch.length - 1 ].nextElementSibling;
			}

			branch.forEach(function (item) {
				tree.insertBefore(item, before);
			});
		}

		function selectRange(from, to) {
			const nodes = visibleNodes();
			const a     = nodes.indexOf(from);
			const b     = nodes.indexOf(to);
			if (a < 0 || b < 0) {
				return;
			}
			nodes.slice(Math.min(a, b), Math.max(a, b) + 1).forEach(function (item) {
				setSelected(item, true);
			});
			updateBulkBar();
		}

		tree.addEventListener('keydown', function (event) {
			// The keys work on a focused node, and on its checkbox after it was clicked with the
			// mouse; in the fields and buttons of a node they keep their own meaning.
			const onCheckbox = event.target.classList.contains('js-dwp-mb-select');
			const node       = event.target.closest('.js-dwp-mb-tree-node');
			if (!node || (event.target !== node && !onCheckbox)) {
				return;
			}
			if (onCheckbox && ' ' === event.key && !event.shiftKey && !event.ctrlKey && !event.metaKey) {
				return; // The checkbox toggles itself, which selects the node.
			}

			const nodes = visibleNodes();
			const index = nodes.indexOf(node);
			let handled = true;

			if (event.altKey && ('ArrowUp' === event.key || 'ArrowDown' === event.key)) {
				const roots = keyTargets(node);
				('ArrowUp' === event.key ? roots : roots.slice().reverse()).forEach(function (root) {
					moveBranch(root, 'ArrowUp' === event.key ? -1 : 1);
				});
				serialize();
				focusNode(node);
			} else if (event.altKey && ('ArrowLeft' === event.key || 'ArrowRight' === event.key)) {
				const roots = keyTargets(node);
				('ArrowRight' === event.key ? roots : roots.slice().reverse()).forEach(function (root) {
					shiftBranch(root, 'ArrowRight' === event.key ? 1 : -1);
				});
				focusNode(node);
			} else if ('ArrowUp' === event.key || 'ArrowDown' === event.key || 'Home' === event.key || 'End' === event.key) {
				const target = {
					'ArrowUp': nodes[ index - 1 ],
					'ArrowDown': nodes[ index + 1 ],
					'Home': nodes[ 0 ],
					'End': nodes[ nodes.length - 1 ],
				}[ event.key ];
				if (target && event.shiftKey) {
					anchor = anchor && nodes.includes(anchor) ? anchor : node;
					selectRange(anchor, target);
				}
				focusNode(target);
			} else if ('ArrowLeft' === event.key || 'ArrowRight' === event.key) {
				// Collapse or expand; on a collapsed or childless node, Left goes to the parent.
				const isOpen = node.classList.contains('has-children') && !node.classList.contains('is-collapsed');
				if ('ArrowRight' === event.key && node.classList.contains('is-collapsed')) {
					setCollapsed(node, false);
				} else if ('ArrowLeft' === event.key && isOpen) {
					setCollapsed(node, true);
				} else if ('ArrowLeft' === event.key) {
					focusNode(nodes.slice(0, index).reverse().find(function (item) {
						return depthOf(item) < depthOf(node);
					}));
				}
			} else if (' ' === event.key) {
				if (event.ctrlKey || event.metaKey) {
					toggleBranchSelection(node);
				} else if (event.shiftKey && anchor) {
					selectRange(anchor, node);
				} else {
					setSelected(node, !selected.has(node));
					anchor = node;
					updateBulkBar();
				}
			} else if ('Enter' === event.key) {
				node.querySelector('.js-dwp-mb-edit').click();
			} else if ('Delete' === event.key) {
				const removing = branchesOf(keyTargets(node));
				const next     = nodes.slice(index + 1).find(function (item) {
					return !removing.includes(item);
				}) || nodes.slice(0, index).reverse().find(function (item) {
					return !removing.includes(item);
				});
				if (1 === removing.length || window.confirm(t.removeSelectedConfirm.replace('%d', String(removing.length)))) {
					removeNodes(removing);
					focusNode(next);
				}
			} else if ('Escape' === event.key && selected.size) {
				clearSelection();
			} else if ('?' === event.key) {
				openShortcuts();
			} else {
				handled = false;
			}

			if (handled) {
				event.preventDefault();
			}
		});

		/* ------------------------------------------------------------------ shortcuts overview */

		// A plain element with the dialog role, shown and hidden by the script: the <dialog> element
		// is not available in every browser (where it is not, it shows on load and cannot close).
		// It hangs directly under <body>, so the fixed settings frame cannot clip or cover it.
		const shortcutsDialog = el('div', 'dwp-mb-dialog');
		const dialogInner     = el('div', 'dwp-mb-dialog-inner');
		const dialogFooter    = el('div', 'dwp-mb-dialog-footer');
		const dialogOverlay   = el('div', 'dwp-mb-overlay');
		const dialogHeader    = el('div', 'dwp-mb-dialog-header');
		const dialogTitle     = el('h2', '', t.shortcutsTitle);
		const dialogBody      = el('div', 'dwp-mb-dialog-body');
		const shortcutsTable  = el('table', 'widefat striped');
		const closeIcon       = iconButton('dashicons-no-alt', t.close, 'js-dwp-mb-dialog-close');
		const closeDialog     = el('button', 'button button-primary js-dwp-mb-dialog-close', t.close);

		dialogTitle.id = 'dwp-mb-shortcuts-title';
		shortcutsDialog.hidden = true;
		shortcutsDialog.setAttribute('role', 'dialog');
		shortcutsDialog.setAttribute('aria-modal', 'true');
		shortcutsDialog.setAttribute('aria-labelledby', dialogTitle.id);
		t.shortcuts.forEach(function (shortcut) {
			const line = el('tr');
			line.append(el('th', '', shortcut[ 0 ]), el('td', '', shortcut[ 1 ]));
			shortcutsTable.appendChild(line);
		});
		closeDialog.type = 'button';
		dialogHeader.append(dialogTitle, closeIcon);
		dialogBody.append(shortcutsTable, el('p', 'description', t.shortcutsNote));
		dialogFooter.appendChild(closeDialog);
		dialogInner.append(dialogHeader, dialogBody, dialogFooter);
		shortcutsDialog.appendChild(dialogInner);

		let focusBeforeDialog = null;

		// Opening moves the focus to the close button (so Enter closes it too); closing returns it.
		function openShortcuts() {
			focusBeforeDialog = document.activeElement;
			dialogOverlay.hidden = false;
			shortcutsDialog.hidden = false;
			closeIcon.focus();
		}

		function closeShortcuts() {
			dialogOverlay.hidden = true;
			shortcutsDialog.hidden = true;
			if (focusBeforeDialog && document.contains(focusBeforeDialog)) {
				focusBeforeDialog.focus();
			}
		}

		// Close with either button, a click on the dimmed page around it, or Esc.
		dialogOverlay.hidden = true;
		dialogOverlay.addEventListener('click', closeShortcuts);
		shortcutsDialog.addEventListener('click', function (event) {
			if (event.target.closest('.js-dwp-mb-dialog-close')) {
				closeShortcuts();
			}
		});
		document.addEventListener('keydown', function (event) {
			if ('Escape' === event.key && !shortcutsDialog.hidden) {
				event.preventDefault();
				event.stopPropagation();
				closeShortcuts();
			}
		}, true);
		document.body.append(dialogOverlay, shortcutsDialog);

		const helpButton = el('button', 'button button-small dwp-mb-help', t.shortcutsButton);
		helpButton.type = 'button';
		helpButton.prepend(el('span', 'dashicons dashicons-editor-help'));
		helpButton.addEventListener('click', openShortcuts);
		tree.closest('.dwp-mb-column').querySelector('h3').appendChild(helpButton);

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
			refreshTreeState();
		}

		/* ------------------------------------------------------------------ start */

		buildPool();
		filterPool();

		data.tree.forEach(function (row) {
			tree.appendChild(createTreeNode(row, row.depth));
		});
		updateEmptyState();
		serialize();

		if (form) {
			form.addEventListener('submit', function (event) {
				const action  = event.submitter ? event.submitter.value : '';
				const question = { 'restore-default': t.restoreConfirm, 'merge-default': t.mergeConfirm }[ action ];
				if (question && !window.confirm(question)) {
					event.preventDefault();
					return;
				}
				serialize();
			});
		}
	});
}());
