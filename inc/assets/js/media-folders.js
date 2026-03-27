/**
 * Infinite Uploads - Media Folders
 *
 * Custom folder tree sidebar for the WordPress Media Library.
 * Replaces jstree with plain jQuery + HTML for a lighter footprint.
 * Uses native HTML5 Drag and Drop API for moving media into folders.
 */
(function ($) {
	'use strict';

	var IU_Folders = {
		currentFolder: null,
		isListMode: iuMediaFolders.is_list_mode,
		dragIds: [],
		sortMode: localStorage.getItem('iu_folders_sort') || 'custom',
		folderColors: {},
		mediaSortMode: localStorage.getItem('iu_media_sort') || 'date',
		mediaSortOrder: localStorage.getItem('iu_media_sort_order') || 'desc',
		mediaSearchFields: (function () {
			var stored = localStorage.getItem('iu_search_fields');
			return stored ? JSON.parse(stored) : ['title', 'filename', 'alt', 'description', 'caption', 'folders'];
		}()),
		cutNode: null,
		_pendingCreates: {}, // tempId → jQuery Deferred, resolved with real nodeId on AJAX success
		totalCount: 0,
		uncategorizedCount: 0,
		folderCounts: {},

		// Custom tree state
		folders: [],
		expandedNodes: null, // Set, loaded from localStorage
		selectedNode: null,

		// Multi-select state
		selectedNodes: null,      // Set<string>, initialized in init()
		folderDragNodeIds: [],    // replaces single folderDragNodeId; holds all folders being dragged
		_activeFrame: null,       // last modal frame that received sidebar injection

		// Upload folder target (null = no folder selected)
		uploadTargetFolder: null,

		// SVG icons
		chevronSvg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>',
		folderSvg: '<span class="dashicons dashicons-open-folder iu-node-icon"></span>',

		init: function () {
			var self = this;

			// Load expanded nodes from localStorage
			try {
				var stored = JSON.parse(localStorage.getItem('iu_folders_expanded') || '[]');
				this.expandedNodes = new Set(stored);
			} catch (e) {
				this.expandedNodes = new Set();
			}

			this.selectedNodes = new Set();

			// Restore persisted upload target folder
			this.uploadTargetFolder = localStorage.getItem('iu_upload_folder') || null;

			// Upload New Media page: just show folder selector + hook uploader, no sidebar.
			if (iuMediaFolders.is_media_new_page) {
				this.hookUploader();
				this.loadAndRenderTree(function () {
					self.injectUploadFolderSelector();
				});
				return;
			}

			this.injectSidebar();
			this.updateSortButton();
			this.loadAndRenderTree();
			this.initSearch();
			this.initResize();
			this.bindEvents();

			if ( !this.isListMode) {
				this.hookGridMode();
				this.hookUploader();
			} else {
				this.hookListMode();
			}
		},

		/**
		 * Generate the sidebar HTML string.
		 * collapsed: bool — whether to start in collapsed state.
		 * sidebarStyle: string — inline style attribute value for the sidebar panel.
		 */
		buildSidebarHtml: function ( collapsed, sidebarStyle ) {
			return (
				'<div id="iu-media-folders-wrap" class="' + ( collapsed ? 'iu-collapsed' : '' ) + '">' +
				'<div id="iu-media-folders-sidebar"' + sidebarStyle + '>' +
				// Row 1: Title + New Folder button
				'<div class="iu-folders-header">' +
				'<span class="iu-folders-title">' + iuMediaFolders.folders_title + '</span>' +
				'<button type="button" class="iu-folder-add-btn" title="' + iuMediaFolders.new_folder + '">' +
				'<span class="dashicons dashicons-open-folder"></span>' +
				'<span class="iu-btn-label">' + iuMediaFolders.new_folder + '</span>' +
				'</button>' +
				'</div>' +
				// Row 2: Rename, Delete, Sort, More
				'<div class="iu-folders-actions">' +
				'<button type="button" class="iu-action-rename" disabled title="' + iuMediaFolders.rename + '">' +
				'<span class="dashicons dashicons-edit"></span>' +
				'<span>' + iuMediaFolders.rename + '</span>' +
				'</button>' +
				'<button type="button" class="iu-action-delete" disabled title="' + iuMediaFolders.delete + '">' +
				'<span class="dashicons dashicons-trash"></span>' +
				'<span>' + iuMediaFolders.delete + '</span>' +
				'</button>' +
				'<div class="iu-actions-right">' +
				'<div class="iu-sort-dropdown">' +
				'<button type="button" class="iu-sort-btn" title="' + iuMediaFolders.sort_label + '">' +
				'<span class="dashicons dashicons-sort"></span>' +
				'</button>' +
				'<div class="iu-sort-menu">' +
				'<button type="button" class="iu-sort-item" data-sort="az">' + iuMediaFolders.sort_az + '</button>' +
				'<button type="button" class="iu-sort-item" data-sort="za">' + iuMediaFolders.sort_za + '</button>' +
				'<div class="iu-sort-divider"></div>' +
				'<button type="button" class="iu-sort-item" data-sort="date_desc">' + iuMediaFolders.sort_date_desc + '</button>' +
				'<button type="button" class="iu-sort-item" data-sort="date_asc">' + iuMediaFolders.sort_date_asc + '</button>' +
				'<div class="iu-sort-divider"></div>' +
				'<button type="button" class="iu-sort-item" data-sort="modified_desc">' + iuMediaFolders.sort_modified_desc + '</button>' +
				'<button type="button" class="iu-sort-item" data-sort="modified_asc">' + iuMediaFolders.sort_modified_asc + '</button>' +
				'<div class="iu-sort-divider"></div>' +
				'<button type="button" class="iu-sort-item" data-sort="size_desc">' + iuMediaFolders.sort_size_desc + '</button>' +
				'<button type="button" class="iu-sort-item" data-sort="size_asc">' + iuMediaFolders.sort_size_asc + '</button>' +
				'</div>' +
				'</div>' +

				'<div class="iu-more-dropdown">' +
				'<button type="button" class="iu-action-more" title="' + iuMediaFolders.more + '">' +
				'<span class="dashicons dashicons-ellipsis"></span>' +
				'</button>' +
				'<div class="iu-more-menu">' +
				'<button type="button" class="iu-more-item iu-expand-all-btn">' +
				'<span class="dashicons dashicons-arrow-down-alt2"></span>' +
				iuMediaFolders.expand_all +
				'</button>' +
				'<button type="button" class="iu-more-item iu-collapse-all-btn">' +
				'<span class="dashicons dashicons-arrow-up-alt2"></span>' +
				iuMediaFolders.collapse_all +
				'</button>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'</div>' +
				// Virtual folders: All Files + Uncategorized
				'<div class="iu-virtual-folders">' +
				'<div class="iu-virtual-folder iu-vf-selected" data-folder="all">' +
				'<span class="dashicons dashicons-open-folder iu-vf-icon"></span>' +
				'<span class="iu-vf-name">' + iuMediaFolders.all_label + '</span>' +
				'<span class="iu-count-badge" data-count="all">0</span>' +
				'</div>' +
				'<div class="iu-virtual-folder" data-folder="uncategorized">' +
				'<span class="dashicons dashicons-portfolio iu-vf-icon"></span>' +
				'<span class="iu-vf-name">' + iuMediaFolders.uncat_label + '</span>' +
				'<span class="iu-count-badge" data-count="uncategorized">0</span>' +
				'</div>' +
				'</div>' +
				// Search input
				'<div class="iu-folders-search">' +
				'<span class="dashicons dashicons-search iu-search-icon"></span>' +
				'<input type="text" class="iu-folder-search-input" placeholder="' + iuMediaFolders.search_folders + '" />' +
				'</div>' +
				// Folder tree (user folders only)
				'<div id="iu-folders-tree"></div>' +
				'</div>' +
				'<div class="iu-sidebar-handle"></div>' +
				'<span class="iu-sidebar-toggle" title="Toggle folders">' +
				'<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.5 17a1 1 0 0 1-.71-.29 1 1 0 0 1 0-1.42L13.1 12 9.92 8.69a1 1 0 0 1 0-1.41 1 1 0 0 1 1.42 0l3.86 4a1 1 0 0 1 0 1.4l-4 4a1 1 0 0 1-.7.32z"></path></svg>' +
				'</span>' +
				'</div>'
			);
		},

		/**
		 * Inject the sidebar HTML into the media library page.
		 */
		injectSidebar: function () {
			var collapsed = localStorage.getItem('iu_folders_collapsed') === '1';
			var savedWidth = localStorage.getItem('iu_folders_width');
			var sidebarStyle = savedWidth ? ' style="width:' + savedWidth + 'px;min-width:' + savedWidth + 'px"' : '';
			var sidebarHtml = this.buildSidebarHtml( collapsed, sidebarStyle );

			if (this.isListMode) {
				var $wrap = $('.wrap');
				if ($wrap.length) {
					$wrap.children().wrapAll('<div class="iu-media-content"></div>');
					$wrap.find('.iu-media-content').first().before(sidebarHtml);
					$wrap.find('#iu-media-folders-wrap, .iu-media-content').wrapAll('<div class="iu-media-wrapper"></div>');
				}
			} else {
				// On upload.php inject directly into the existing media frame.
				// All other pages (page builders, post.php, etc.) rely on
				// _extendAttachmentsBrowser() to inject when a modal opens.
				var $mediaFrame = iuMediaFolders.is_upload_page ? $('.media-frame') : $([]);
				if ($mediaFrame.length) {
					$mediaFrame.addClass('iu-has-folders');
					$mediaFrame.prepend(sidebarHtml);
				}
				// Note: no _sidebarHtml storage — _extendAttachmentsBrowser generates
				// fresh HTML for every modal that opens.
			}
		},

		/**
		 * Inject the folder sidebar into a dynamically opened wp.media frame.
		 * Called by the MutationObserver in hookGridMode() when the frame appears.
		 */
		injectIntoFrame: function ($frame, draggableObserver) {
			var self = this;

			if ( !this._sidebarHtml || !$frame.length) {
				return;
			}

			$frame.addClass('iu-has-folders');
			$frame.prepend(this._sidebarHtml);
			this._sidebarHtml = null; // prevent double-injection

			// If folder data is already loaded, render the tree immediately.
			// Otherwise loadAndRenderTree() (already in-flight or completed) will
			// call buildTree() which will now find #iu-folders-tree in the DOM.
			if (this.folders.length) {
				this.buildTree();
				this.renderCountBadges();
			}

			// Default selection: All Files
			this.selectVirtualFolder('all');

			// Start observing the frame for new attachments (for draggable marking).
			if (draggableObserver) {
				draggableObserver.observe($frame[0], {childList: true, subtree: true});
			}

			setTimeout(function () {
				self.markDraggable();
			}, 300);
		},

		// -----------------------------------------------------------------
		// Tree: load data and render
		// -----------------------------------------------------------------

		loadAndRenderTree: function (callback) {
			var self = this;

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_get_folders',
				nonce: iuMediaFolders.nonce,
				sort: self.sortMode,
			}, function (response) {
				if (response.success) {
					self.folders = response.data.folders || [];
					self.totalCount = response.data.total_count || 0;
					self.uncategorizedCount = response.data.uncategorized_count || 0;
					self.folderCounts = response.data.counts || {};
					// Rebuild color map from fresh data.
					self.folderColors = {};
					self.folders.forEach(function (f) {
						if (f.data && f.data.color) {
							self.folderColors[f.data.folder_id] = f.data.color;
						}
					});
					self.updateVirtualCounts();
					self.buildTree();
					self.updateUploadFolderDropdown();

					if (typeof callback === 'function') {
						callback();
					}
				}
			}, 'json');
		},

		/**
		 * Convert flat folder array to nested tree and render HTML.
		 */
		buildTree: function () {
			var self = this;
			var $tree = $('#iu-folders-tree');
			$tree.empty();

			if ( !this.folders.length) {
				return;
			}

			// Build parent→children map from flat array
			var childrenMap = {};
			var roots = [];

			for (var i = 0; i < this.folders.length; i++) {
				var f = this.folders[i];
				var parentKey = f.parent; // '#' or 'folder_X'
				if (parentKey === '#') {
					roots.push(f);
				} else {
					if ( !childrenMap[parentKey]) childrenMap[parentKey] = [];
					childrenMap[parentKey].push(f);
				}
			}

			var $ul = $('<ul></ul>');
			for (var r = 0; r < roots.length; r++) {
				$ul.append(self.renderNode(roots[r], childrenMap));
			}

			$tree.append($ul);

			// Re-apply selection(s) after rebuild
			if (this.selectedNodes && this.selectedNodes.size > 1) {
				var self = this;
				this.selectedNodes.forEach(function (nid) {
					$tree.find('.iu-tree-node[data-id="' + nid + '"] > .iu-node-row').first().addClass('iu-multi-selected');
				});
			} else if (this.selectedNode) {
				$tree.find('.iu-tree-node[data-id="' + this.selectedNode + '"] > .iu-node-row').first().addClass('iu-selected');
			}
		},

		/**
		 * Recursively render a single tree node.
		 */
		renderNode: function (folder, childrenMap) {
			var self = this;
			var nodeId = folder.id; // e.g. 'folder_5'
			var children = childrenMap[nodeId] || [];
			var hasChildren = children.length > 0;
			var isExpanded = this.expandedNodes.has(nodeId);
			var folderId = folder.data ? folder.data.folder_id : parseInt(nodeId.replace('folder_', ''), 10);
			var count = this.folderCounts[folderId] || 0;

			var $li = $('<li class="iu-tree-node" data-id="' + nodeId + '"></li>');

			// Apply folder color directly to the icon.
			var nodeColor = (folder.data && folder.data.color) ? folder.data.color : '';
			var iconHtml = nodeColor
				? '<span class="dashicons dashicons-open-folder iu-node-icon" style="color:' + nodeColor + '"></span>'
				: self.folderSvg;

			// Node row
			var $row = $(
				'<div class="iu-node-row" draggable="true">' +
				'<span class="iu-node-toggle ' + (hasChildren ? (isExpanded ? 'iu-expanded' : '') : 'iu-leaf') + '">' +
				self.chevronSvg +
				'</span>' +
				iconHtml +
				'<span class="iu-node-text">' + self.escHtml(folder.text) + '</span>' +
				'<span class="iu-count-badge">' + count + '</span>' +
				'</div>'
			);
			$li.append($row);

			// Children
			if (hasChildren) {
				var $childUl = $('<ul class="iu-node-children' + (isExpanded ? '' : ' hidden') + '"></ul>');
				for (var c = 0; c < children.length; c++) {
					$childUl.append(self.renderNode(children[c], childrenMap));
				}
				$li.append($childUl);
			}

			return $li;
		},

		escHtml: function (str) {
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		},

		// Use for strings placed inside HTML attribute values (value="...", data-*="...").
		// escHtml() encodes <, >, & but not ", so quotes in folder names would break
		// out of the attribute and create an injection vector.
		escAttr: function (str) {
			return this.escHtml(str).replace(/"/g, '&quot;');
		},

		// -----------------------------------------------------------------
		// Tree: expand / collapse
		// -----------------------------------------------------------------

		toggleNode: function (nodeId) {
			var $node = $('#iu-folders-tree .iu-tree-node[data-id="' + nodeId + '"]');
			var $toggle = $node.children('.iu-node-row').find('.iu-node-toggle');
			var $children = $node.children('.iu-node-children');

			if ( !$children.length) return;

			if ($children.hasClass('hidden')) {
				$children.removeClass('hidden');
				$toggle.addClass('iu-expanded');
				this.expandedNodes.add(nodeId);
			} else {
				$children.addClass('hidden');
				$toggle.removeClass('iu-expanded');
				this.expandedNodes.delete(nodeId);
			}

			this.persistExpanded();
		},

		expandAll: function () {
			var self = this;
			$('#iu-folders-tree .iu-node-children').removeClass('hidden');
			$('#iu-folders-tree .iu-node-toggle').not('.iu-leaf').addClass('iu-expanded');
			$('#iu-folders-tree .iu-tree-node').each(function () {
				self.expandedNodes.add($(this).data('id'));
			});
			this.persistExpanded();
		},

		collapseAll: function () {
			$('#iu-folders-tree .iu-node-children').addClass('hidden');
			$('#iu-folders-tree .iu-node-toggle').removeClass('iu-expanded');
			this.expandedNodes.clear();
			this.persistExpanded();
		},

		persistExpanded: function () {
			localStorage.setItem('iu_folders_expanded', JSON.stringify(Array.from(this.expandedNodes)));
		},

		// -----------------------------------------------------------------
		// Tree: selection
		// -----------------------------------------------------------------

		selectNode: function (nodeId, addToSelection) {
			var self = this;

			if (!addToSelection) {
				// Normal click: clear all selections
				$('#iu-folders-tree .iu-node-row.iu-selected, #iu-folders-tree .iu-node-row.iu-multi-selected')
					.removeClass('iu-selected iu-multi-selected');
				this.selectedNodes.clear();
			}

			var $node = $('#iu-folders-tree .iu-tree-node[data-id="' + nodeId + '"]');

			if (addToSelection && this.selectedNodes.has(nodeId)) {
				// Ctrl+click on already-selected node → deselect it
				$node.children('.iu-node-row').removeClass('iu-selected iu-multi-selected');
				this.selectedNodes.delete(nodeId);
				var remaining = Array.from(this.selectedNodes);
				this.selectedNode = remaining.length ? remaining[remaining.length - 1] : null;
				this.updateToolbarButtons(this.selectedNode);
				return;
			}

			this.selectedNodes.add(nodeId);

			if (!addToSelection) {
				$node.children('.iu-node-row').addClass('iu-selected');
				this.selectedNode = nodeId;
				this.deselectVirtualFolders();
				this.updateToolbarButtons(nodeId);
				this.onFolderSelect(nodeId);
			} else {
				// Multi-select: re-style all selected nodes as multi-selected
				this.selectedNodes.forEach(function (nid) {
					$('#iu-folders-tree .iu-tree-node[data-id="' + nid + '"]')
						.children('.iu-node-row')
						.removeClass('iu-selected')
						.addClass('iu-multi-selected');
				});
				this.selectedNode = nodeId;
				this.deselectVirtualFolders();
				this.updateToolbarButtons(this.selectedNode);
				// Do NOT call onFolderSelect — no navigation on multi-select
			}
		},

		deselectTree: function () {
			$('#iu-folders-tree .iu-node-row.iu-selected, #iu-folders-tree .iu-node-row.iu-multi-selected')
				.removeClass('iu-selected iu-multi-selected');
			this.selectedNode = null;
			if (this.selectedNodes) this.selectedNodes.clear();
		},

		/**
		 * Shift+click: range-select all visible folder nodes between the anchor
		 * (selectedNode) and the clicked node.
		 */
		rangeSelectTo: function (nodeId) {
			var self = this;
			var ids = [];
			$('#iu-folders-tree .iu-tree-node:not(.iu-search-hidden)').each(function () {
				var nid = $(this).data('id');
				if (nid && String(nid).indexOf('folder_') === 0) ids.push(nid);
			});

			var anchorIdx = ids.indexOf(this.selectedNode);
			var targetIdx = ids.indexOf(nodeId);
			if (anchorIdx === -1 || targetIdx === -1) { this.selectNode(nodeId); return; }

			var start = Math.min(anchorIdx, targetIdx);
			var end   = Math.max(anchorIdx, targetIdx);

			$('#iu-folders-tree .iu-node-row.iu-selected, #iu-folders-tree .iu-node-row.iu-multi-selected')
				.removeClass('iu-selected iu-multi-selected');
			this.selectedNodes.clear();

			for (var i = start; i <= end; i++) this.selectedNodes.add(ids[i]);

			this.selectedNodes.forEach(function (nid) {
				$('#iu-folders-tree .iu-tree-node[data-id="' + nid + '"]')
					.children('.iu-node-row').addClass('iu-multi-selected');
			});
			this.updateToolbarButtons(this.selectedNode);
		},

		// -----------------------------------------------------------------
		// Tree: inline rename
		// -----------------------------------------------------------------

		startRename: function (nodeId) {
			var self = this;
			var $node = $('#iu-folders-tree .iu-tree-node[data-id="' + nodeId + '"]');
			var $row = $node.children('.iu-node-row');
			var $text = $row.find('.iu-node-text');
			var currentName = $text.text().trim();

			// Replace text with input
			$text.replaceWith(
				'<input type="text" class="iu-rename-input" value="' + self.escAttr(currentName) + '" data-original="' + self.escAttr(currentName) + '" />'
			);

			var $input = $row.find('.iu-rename-input');
			$input.focus().select();

			$input.on('keydown', function (e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					self.finishRename(nodeId, $(this).val().trim(), currentName);
				} else if (e.key === 'Escape') {
					e.preventDefault();
					self.cancelRename($row, currentName);
				}
			});

			$input.on('blur', function () {
				// Delay to allow Enter to fire first
				var val = $(this).val().trim();
				setTimeout(function () {
					if ($row.find('.iu-rename-input').length) {
						self.finishRename(nodeId, val, currentName);
					}
				}, 100);
			});
		},

		finishRename: function (nodeId, newName, oldName) {
			var self = this;
			var $node = $('#iu-folders-tree .iu-tree-node[data-id="' + nodeId + '"]');
			var $row = $node.children('.iu-node-row');

			if ( !newName || newName === oldName) {
				self.cancelRename($row, oldName);
				return;
			}

			// Replace input with text immediately
			$row.find('.iu-rename-input').replaceWith(
				'<span class="iu-node-text">' + self.escHtml(newName) + '</span>'
			);

			var termId = parseInt(nodeId.replace('folder_', ''), 10);

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_rename_folder',
				nonce: iuMediaFolders.nonce,
				term_id: termId,
				name: newName,
			}, function (response) {
				if ( !response.success) {
					// Revert on failure
					$row.find('.iu-node-text').text(oldName);
					alert(response.data);
				}
			}, 'json');
		},

		cancelRename: function ($row, originalName) {
			$row.find('.iu-rename-input').replaceWith(
				'<span class="iu-node-text">' + this.escHtml(originalName) + '</span>'
			);
		},

		// -----------------------------------------------------------------
		// Tree: create folder
		// -----------------------------------------------------------------

		startCreate: function (parentId) {
			var self = this;
			// parentId: '#' for root, or 'folder_X' for subfolder

			// If a parent folder, expand it first
			if (parentId !== '#') {
				var $parent = $('#iu-folders-tree .iu-tree-node[data-id="' + parentId + '"]');
				var $children = $parent.children('.iu-node-children');

				if ( !$children.length) {
					// Create children container
					$children = $('<ul class="iu-node-children"></ul>');
					$parent.append($children);
					// Update toggle to show chevron
					var $toggle = $parent.children('.iu-node-row').find('.iu-node-toggle');
					$toggle.removeClass('iu-leaf').addClass('iu-expanded');
				} else {
					$children.removeClass('hidden');
					$parent.children('.iu-node-row').find('.iu-node-toggle').addClass('iu-expanded');
				}
				this.expandedNodes.add(parentId);
				this.persistExpanded();
			}

			// Create temp node
			var tempId = 'iu_temp_' + Date.now();
			var $tempLi = $(
				'<li class="iu-tree-node" data-id="' + tempId + '">' +
				'<div class="iu-node-row">' +
				'<span class="iu-node-toggle iu-leaf">' + self.chevronSvg + '</span>' +
				self.folderSvg +
				'<input type="text" class="iu-rename-input" value="' + self.escAttr(iuMediaFolders.new_folder) + '" />' +
				'<span class="iu-count-badge">0</span>' +
				'</div>' +
				'</li>'
			);

			// Append to correct parent
			if (parentId === '#') {
				var $rootUl = $('#iu-folders-tree > ul');
				if ( !$rootUl.length) {
					$rootUl = $('<ul></ul>');
					$('#iu-folders-tree').append($rootUl);
				}
				$rootUl.append($tempLi);
			} else {
				$parent.children('.iu-node-children').append($tempLi);
			}

			var $input = $tempLi.find('.iu-rename-input');
			$input.focus().select();

			$input.on('keydown', function (e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					self.finishCreate(tempId, $(this).val().trim(), parentId);
				} else if (e.key === 'Escape') {
					e.preventDefault();
					$tempLi.remove();
					self.cleanEmptyParent(parentId);
				}
			});

			$input.on('blur', function () {
				var val = $(this).val().trim();
				setTimeout(function () {
					if ($('#iu-folders-tree .iu-tree-node[data-id="' + tempId + '"]').length) {
						if (val) {
							self.finishCreate(tempId, val, parentId);
						} else {
							$tempLi.remove();
							self.cleanEmptyParent(parentId);
						}
					}
				}, 100);
			});
		},

		finishCreate: function (tempId, name, parentId) {
			var self = this;
			var $tempNode = $('#iu-folders-tree .iu-tree-node[data-id="' + tempId + '"]');

			if ( !name) {
				$tempNode.remove();
				self.cleanEmptyParent(parentId);
				return;
			}

			var parentFolderId = 0;
			if (parentId !== '#') {
				parentFolderId = parseInt(parentId.replace('folder_', ''), 10);
			}

			// Replace input with text
			$tempNode.find('.iu-rename-input').replaceWith(
				'<span class="iu-node-text">' + self.escHtml(name) + '</span>'
			);

			// Track the in-flight create so right-clicks on the pending node can wait.
			var deferred = $.Deferred();
			self._pendingCreates[tempId] = deferred;

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_create_folder',
				nonce: iuMediaFolders.nonce,
				name: name,
				parent: parentFolderId,
			}, function (response) {
				delete self._pendingCreates[tempId];

				if (response.success) {
					var newId = response.data.id; // 'folder_X'
					$tempNode.attr('data-id', newId);

					// Add to local data
					self.folders.push({
						id: newId,
						text: response.data.text,
						parent: parentId === '#' ? '#' : parentId,
						data: response.data.data,
					});

					// Make row draggable
					$tempNode.children('.iu-node-row').attr('draggable', 'true');

					deferred.resolve(newId);
				} else {
					$tempNode.remove();
					self.cleanEmptyParent(parentId);
					deferred.reject();
					alert(response.data);
				}
			}, 'json');
		},

		cleanEmptyParent: function (parentId) {
			if (parentId === '#') return;
			var $parent = $('#iu-folders-tree .iu-tree-node[data-id="' + parentId + '"]');
			var $children = $parent.children('.iu-node-children');
			if ($children.length && $children.children().length === 0) {
				$children.remove();
				$parent.children('.iu-node-row').find('.iu-node-toggle').addClass('iu-leaf').removeClass('iu-expanded');
				this.expandedNodes.delete(parentId);
				this.persistExpanded();
			}
		},

		// -----------------------------------------------------------------
		// Tree: delete folder
		// -----------------------------------------------------------------

		deleteFolder: function (nodeId) {
			var self = this;
			var termId = parseInt(nodeId.replace('folder_', ''), 10);

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_delete_folder',
				nonce: iuMediaFolders.nonce,
				term_id: termId,
			}, function (response) {
				if (response.success) {
					var $node = $('#iu-folders-tree .iu-tree-node[data-id="' + nodeId + '"]');
					var $parentLi = $node.parent('.iu-node-children').closest('.iu-tree-node');
					var parentId = $parentLi.data('id') || '#';

					// Move child folders up to parent
					var $children = $node.children('.iu-node-children').children();
					if ($children.length) {
						$node.before($children);
					}

					$node.remove();
					self.cleanEmptyParent(parentId);

					// Remove from local data
					self.folders = self.folders.filter(function (f) {
						return f.id !== nodeId;
					});

					if (self.selectedNode === nodeId) {
						self.selectVirtualFolder('all');
						self.onFolderSelect('all');
					}
				} else {
					alert(response.data);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// Tree: bulk delete multiple folders
		// -----------------------------------------------------------------

		bulkDeleteFolders: function (nodeIds) {
			var self = this;

			var termIds = nodeIds
				.filter(function (nid) { return String(nid).indexOf('folder_') === 0; })
				.map(function (nid) { return parseInt(nid.replace('folder_', ''), 10); });
			if (!termIds.length) return;

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_bulk_delete_folders',
				nonce: iuMediaFolders.nonce,
				term_ids: termIds,
			}, function (response) {
				if (response.success) {
					var deletedIds = response.data.deleted || [];
					var deletedNodeIds = deletedIds.map(function (id) { return 'folder_' + id; });

					// Clean up cut state if any deleted node was marked as cut
					deletedNodeIds.forEach(function (nodeId) {
						if (self.cutNode === nodeId) {
							self.cutNode = null;
						}
					});
					$('#iu-folders-tree .iu-cut').removeClass('iu-cut');

					// Clear selection
					self.selectedNodes.clear();
					self.selectedNode = null;
					self.updateToolbarButtons(null);

					// If the currently viewed folder was deleted, go to All Files
					var currentWasDeleted = deletedNodeIds.indexOf(self.currentFolder) !== -1;
					if (currentWasDeleted) {
						self.selectVirtualFolder('all');
						self.onFolderSelect('all');
					}

					// Full tree refresh handles reparented children correctly
					self.refreshTree();
				} else {
					alert(response.data);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// Tree: refresh (re-fetch and rebuild)
		// -----------------------------------------------------------------

		refreshTree: function (callback) {
			this.loadAndRenderTree(callback);
		},

		highlightMovedNodes: function (nodeIds) {
			var self = this;
			nodeIds.forEach(function (nodeId) {
				var $row = $('#iu-folders-tree .iu-tree-node[data-id="' + nodeId + '"]').children('.iu-node-row');
				$row.addClass('iu-node-moved');
				setTimeout(function () { $row.removeClass('iu-node-moved'); }, 3000);
			});
		},

		// -----------------------------------------------------------------
		// Tree: search/filter
		// -----------------------------------------------------------------

		searchFolders: function (query) {
			if ( !query) {
				$('#iu-folders-tree .iu-tree-node').removeClass('iu-search-hidden iu-search-match');
				return;
			}

			var lowerQuery = query.toLowerCase();

			// First, hide everything
			$('#iu-folders-tree .iu-tree-node').addClass('iu-search-hidden').removeClass('iu-search-match');

			// Show matches and their ancestors
			$('#iu-folders-tree .iu-tree-node').each(function () {
				var $node = $(this);
				var text = $node.children('.iu-node-row').find('.iu-node-text').text();
				if (text.toLowerCase().indexOf(lowerQuery) !== -1) {
					$node.removeClass('iu-search-hidden').addClass('iu-search-match');
					// Show all ancestors
					$node.parents('.iu-tree-node').removeClass('iu-search-hidden');
					// Expand ancestors
					$node.parents('.iu-node-children').removeClass('hidden');
					$node.parents('.iu-tree-node').children('.iu-node-row').find('.iu-node-toggle').not('.iu-leaf').addClass('iu-expanded');
					// Show all children of matching node
					$node.find('.iu-tree-node').removeClass('iu-search-hidden');
				}
			});
		},

		// -----------------------------------------------------------------
		// Context menu
		// -----------------------------------------------------------------

		showContextMenu: function (e, nodeId) {
			var self = this;
			e.preventDefault();
			e.stopPropagation();

			// Remove existing menu
			$('.iu-context-menu').remove();

			var items = '';
			items += '<button class="iu-context-menu-item" data-action="create"><span class="dashicons dashicons-plus-alt2"></span>' + iuMediaFolders.new_subfolder + '</button>';
			items += '<button class="iu-context-menu-item" data-action="rename"><span class="dashicons dashicons-edit"></span>' + iuMediaFolders.rename + '</button>';
			items += '<button class="iu-context-menu-item" data-action="cut"><span class="dashicons dashicons-clipboard"></span>' + iuMediaFolders.cut + '</button>';

			if (self.cutNode && self.cutNode !== nodeId) {
				items += '<button class="iu-context-menu-item" data-action="paste"><span class="dashicons dashicons-clipboard"></span>' + iuMediaFolders.paste + '</button>';
			}

			// Show 'Move to top level' only when the folder is currently nested.
			var folderObj = self.folders.filter(function (f) { return f.id === nodeId; })[0];
			if (folderObj && folderObj.parent !== '#') {
				items += '<button class="iu-context-menu-item" data-action="move-to-root"><span class="dashicons dashicons-arrow-up-alt"></span>' + iuMediaFolders.move_to_root + '</button>';
			}

			var folderId     = parseInt( String(nodeId).replace('folder_', ''), 10 );
			var currentColor = self.folderColors[folderId] || '';
			var palette = [
				'#e74c3c', '#e67e22', '#f1c40f', '#2ecc71',
				'#1abc9c', '#3498db', '#9b59b6', '#e91e63',
				'#795548', '#607d8b', '#95a5a6', '#1a1a2e',
			];
			var swatches = '<button class="iu-color-swatch iu-color-none' + (currentColor === '' ? ' iu-swatch-active' : '') + '" data-color="" title="' + iuMediaFolders.color_none + '">&#x2715;</button>';
			for (var ci = 0; ci < palette.length; ci++) {
				var pc = palette[ci];
				swatches += '<button class="iu-color-swatch' + (currentColor === pc ? ' iu-swatch-active' : '') + '" data-color="' + pc + '" style="background:' + pc + '"></button>';
			}
			items += '<div class="iu-submenu-wrap">' +
				'<button class="iu-context-menu-item iu-has-submenu" data-action="color">' +
				'<span class="dashicons dashicons-art"></span>' + iuMediaFolders.set_color +
				'<span class="iu-submenu-arrow">&#9654;</span>' +
				'</button>' +
				'<div class="iu-color-submenu">' +
				'<div class="iu-color-swatches">' + swatches + '</div>' +
				'<div class="iu-custom-color-wrap">' +
				'<input type="color" class="iu-custom-color-input" value="' + (currentColor || '#3498db') + '">' +
				'<span class="iu-custom-color-label">' + iuMediaFolders.color_custom + '</span>' +
				'</div>' +
				'</div>' +
				'</div>';
			items += '<div class="iu-context-menu-separator"></div>';
			items += '<button class="iu-context-menu-item iu-danger" data-action="delete"><span class="dashicons dashicons-trash"></span>' + iuMediaFolders.delete + '</button>';

			var $menu = $('<div class="iu-context-menu" data-node="' + nodeId + '">' + items + '</div>');
			$('body').append($menu);

			// Position (menu uses position:fixed → must use viewport coords)
			var menuWidth = $menu.outerWidth();
			var menuHeight = $menu.outerHeight();
			var left = e.clientX;
			var top  = e.clientY;

			if (left + menuWidth > window.innerWidth - 5) {
				left = window.innerWidth - menuWidth - 5;
			}
			if (top + menuHeight > window.innerHeight - 5) {
				top = e.clientY - menuHeight;
			}

			$menu.css({left: left, top: top});
		},

		closeContextMenu: function () {
			$('.iu-context-menu').remove();
		},

		// -----------------------------------------------------------------
		// Color picker
		// -----------------------------------------------------------------

		showColorPicker: function (e, nodeId) {
			var self = this;
			$('.iu-color-picker').remove();

			var folderId = parseInt(String(nodeId).replace('folder_', ''), 10);
			var current  = self.folderColors[folderId] || '';

			var palette = [
				'#e74c3c', '#e67e22', '#f1c40f', '#2ecc71',
				'#1abc9c', '#3498db', '#9b59b6', '#e91e63',
				'#795548', '#607d8b', '#95a5a6', '#1a1a2e',
			];

			var swatches = '<button class="iu-color-swatch iu-color-none' + (current === '' ? ' iu-swatch-active' : '') + '" data-color="" title="' + iuMediaFolders.color_none + '">✕</button>';
			for (var i = 0; i < palette.length; i++) {
				var c = palette[i];
				swatches += '<button class="iu-color-swatch' + (current === c ? ' iu-swatch-active' : '') + '" data-color="' + c + '" style="background:' + c + '" title="' + c + '"></button>';
			}

			var $picker = $('<div class="iu-color-picker" data-node="' + nodeId + '">' +
				'<div class="iu-color-picker-title">' + iuMediaFolders.set_color + '</div>' +
				'<div class="iu-color-swatches">' + swatches + '</div>' +
				'</div>');

			$('body').append($picker);

			// Position near cursor, keep inside viewport.
			var pickerWidth  = $picker.outerWidth();
			var pickerHeight = $picker.outerHeight();
			var left = e.pageX;
			var top  = e.pageY;

			if (left + pickerWidth > $(window).width()) {
				left = $(window).width() - pickerWidth - 5;
			}
			if (top + pickerHeight > $(window).scrollTop() + $(window).height()) {
				top = e.pageY - pickerHeight;
			}
			$picker.css({left: left, top: top});
		},

		setFolderColor: function (nodeId, color) {
			var self     = this;
			var folderId = parseInt(String(nodeId).replace('folder_', ''), 10);

			$.post(iuMediaFolders.ajax_url, {
				action:    'iu_set_folder_color',
				nonce:     iuMediaFolders.nonce,
				folder_id: folderId,
				color:     color,
			}, function (response) {
				if (response.success) {
					// Update client-side color map and folder data.
					if (color) {
						self.folderColors[folderId] = color;
					} else {
						delete self.folderColors[folderId];
					}
					for (var i = 0; i < self.folders.length; i++) {
						if (self.folders[i].data && self.folders[i].data.folder_id === folderId) {
							self.folders[i].data.color = color;
							break;
						}
					}

					// Update the DOM directly — no full tree re-render needed.
					var $icon = $('#iu-folders-tree .iu-tree-node[data-id="' + nodeId + '"] > .iu-node-row .iu-node-icon');
					$icon.css('color', color || '');
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// Cut / Paste
		// -----------------------------------------------------------------

		cutFolder: function (nodeId) {
			$('#iu-folders-tree .iu-cut').removeClass('iu-cut');
			this.cutNode = nodeId;
			$('#iu-folders-tree .iu-tree-node[data-id="' + nodeId + '"]').addClass('iu-cut');
		},

		pasteFolder: function (targetNodeId) {
			var self = this;
			if ( !this.cutNode) return;

			var termId = parseInt(this.cutNode.replace('folder_', ''), 10);
			var newParent = 0;

			if (targetNodeId && targetNodeId.indexOf('folder_') === 0) {
				newParent = parseInt(targetNodeId.replace('folder_', ''), 10);
				// Ensure the destination folder is expanded after the tree reloads.
				self.expandedNodes.add(targetNodeId);
				self.persistExpanded();
			}

			self.showTreeLoader();
			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_move_folder',
				nonce: iuMediaFolders.nonce,
				term_id: termId,
				parent: newParent,
			}, function (response) {
				self.hideTreeLoader();
				$('#iu-folders-tree .iu-cut').removeClass('iu-cut');
				var movedNodeId = self.cutNode;
				self.cutNode = null;
				if (response.success) {
					self.refreshTree(function () { self.highlightMovedNodes([movedNodeId]); });
				} else {
					alert(response.data);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// Folder DnD (drag folder to reparent)
		// -----------------------------------------------------------------

		// -----------------------------------------------------------------
		// Virtual folder management
		// -----------------------------------------------------------------

		deselectVirtualFolders: function () {
			$('.iu-virtual-folder').removeClass('iu-vf-selected');
		},

		selectVirtualFolder: function (folder) {
			this.deselectVirtualFolders();
			$('.iu-virtual-folder[data-folder="' + folder + '"]').addClass('iu-vf-selected');
			this.deselectTree();
			this.updateToolbarButtons(null);
			this.currentFolder = folder;
		},

		updateVirtualCounts: function () {
			$('.iu-count-badge[data-count="all"]').text(this.totalCount);
			$('.iu-count-badge[data-count="uncategorized"]').text(this.uncategorizedCount);
		},

		// -----------------------------------------------------------------
		// Toolbar buttons (Rename / Delete)
		// -----------------------------------------------------------------

		updateToolbarButtons: function (nodeId) {
			var count = this.selectedNodes ? this.selectedNodes.size : 0;
			var isBulk = count >= 2;
			var isUserFolder = nodeId && typeof nodeId === 'string' && nodeId.indexOf('folder_') === 0;
			var $rename = $('.iu-action-rename');
			var $delete = $('.iu-action-delete');

			if (isBulk) {
				$rename.prop('disabled', true);
				$delete.prop('disabled', false);
				$delete.find('span:not(.dashicons)').text(iuMediaFolders.delete_bulk.replace('%d', count));
			} else {
				$rename.prop('disabled', !isUserFolder);
				$delete.prop('disabled', !isUserFolder);
				$delete.find('span:not(.dashicons)').text(iuMediaFolders.delete);
			}
		},

		// -----------------------------------------------------------------
		// Count badges
		// -----------------------------------------------------------------

		renderCountBadges: function () {
			var self = this;

			$('#iu-folders-tree .iu-tree-node').each(function () {
				var nodeId = $(this).data('id');
				var folderId = parseInt(String(nodeId).replace('folder_', ''), 10);
				var count = self.folderCounts[folderId] || 0;
				$(this).children('.iu-node-row').find('.iu-count-badge').text(count);
			});

			self.updateVirtualCounts();
		},

		updateCountsFromResponse: function (data) {
			if (data.counts) {
				this.folderCounts = data.counts;
			}
			if (typeof data.total_count !== 'undefined') {
				this.totalCount = data.total_count;
			}
			if (typeof data.uncategorized_count !== 'undefined') {
				this.uncategorizedCount = data.uncategorized_count;
			}
			this.renderCountBadges();
		},

		// -----------------------------------------------------------------
		// Search
		// -----------------------------------------------------------------

		initSearch: function () {
			var self = this;
			var searchTimeout = null;

			$(document).on('input', '.iu-folder-search-input', function () {
				var val = $(this).val();
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(function () {
					self.searchFolders(val);
				}, 250);
			});
		},

		// -----------------------------------------------------------------
		// Sort
		// -----------------------------------------------------------------

		sortFolders: function (mode) {
			this.sortMode = mode;
			localStorage.setItem('iu_folders_sort', this.sortMode);
			this.updateSortButton();
			this.refreshTree();
		},

		updateSortButton: function () {
			var $btn = $('.iu-sort-btn');
			var isCustom = (this.sortMode === 'custom');
			if (isCustom) {
				$btn.removeClass('iu-active');
			} else {
				$btn.addClass('iu-active');
			}
			// Mark the active item in the sort menu.
			$('.iu-sort-item[data-sort]').removeClass('iu-sort-active');
			$('.iu-sort-item[data-sort="' + this.sortMode + '"]').addClass('iu-sort-active');
		},

		// -----------------------------------------------------------------
		// Resize sidebar
		// -----------------------------------------------------------------

		initResize: function () {
			var isResizing = false;
			var startX, startWidth;

			$(document).on('mousedown', '.iu-sidebar-handle', function (e) {
				if ($(e.target).closest('.iu-sidebar-toggle').length) return;
				e.preventDefault();
				isResizing = true;
				startX = e.clientX;
				startWidth = $('#iu-media-folders-sidebar').outerWidth();
				$('body').addClass('iu-resizing');
			});

			$(document).on('mousemove', function (e) {
				if ( !isResizing) return;
				var newWidth = startWidth + (e.clientX - startX);
				newWidth = Math.max(180, Math.min(500, newWidth));
				$('#iu-media-folders-sidebar').css({width: newWidth, minWidth: newWidth});
			});

			$(document).on('mouseup', function () {
				if ( !isResizing) return;
				isResizing = false;
				$('body').removeClass('iu-resizing');
				var finalWidth = $('#iu-media-folders-sidebar').outerWidth();
				localStorage.setItem('iu_folders_width', finalWidth);
			});
		},

		// -----------------------------------------------------------------
		// Folder selection / filtering
		// -----------------------------------------------------------------

		onFolderSelect: function (folderId) {
			this.currentFolder = folderId;

			if (this.isListMode) {
				this.filterListMode(folderId);
			} else {
				this.filterGridMode(folderId);
				this.syncUploadTargetToFolder(folderId);
			}
		},

		/**
		 * Keep uploadTargetFolder in sync with the currently selected folder
		 * so that files dropped into the uploader go directly into that folder.
		 * Persists to localStorage and server-side user meta (for PHP add_attachment hook).
		 */
		syncUploadTargetToFolder: function (folderId) {
			var newTarget = (folderId && folderId !== 'all' && folderId !== 'uncategorized')
				? folderId
				: null;

			if (newTarget === this.uploadTargetFolder) {
				return; // nothing changed
			}

			this.uploadTargetFolder = newTarget;

			if (newTarget) {
				localStorage.setItem('iu_upload_folder', newTarget);
			} else {
				localStorage.removeItem('iu_upload_folder');
			}

			var numericId = newTarget ? parseInt(newTarget.replace('folder_', ''), 10) : 0;
			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_set_upload_folder',
				nonce: iuMediaFolders.nonce,
				folder_id: numericId,
			});
		},

		filterListMode: function (folderId) {
			var url = new URL(window.location.href);
			var currentValue = url.searchParams.get('iu_folder');

			if (folderId === 'all') {
				if ( !currentValue) return;
				url.searchParams.delete('iu_folder');
			} else if (folderId === 'uncategorized') {
				if (currentValue === 'uncategorized') return;
				url.searchParams.set('iu_folder', 'uncategorized');
			} else {
				var termId = folderId.replace('folder_', '');
				if (currentValue === termId) return;
				url.searchParams.set('iu_folder', termId);
			}

			url.searchParams.delete('paged');
			window.location.href = url.toString();
		},

		filterGridMode: function (folderId) {
			if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
				return;
			}

			// Prefer the explicitly tracked frame (page builder modals, etc.);
			// fall back to wp.media.frame for upload.php.
			var activeFrame = this._activeFrame || wp.media.frame;
			var collection = activeFrame && activeFrame.content && activeFrame.content.get();
			var browserCollection = collection && (collection.collection || (collection.options && collection.options.collection));

			// When the user has clicked a media file the frame switches to an
			// attachment-details view; content.get() no longer returns the
			// AttachmentsBrowser so browserCollection is null.
			// Fallback 1: try the frame's state library (always available).
			if (!browserCollection && activeFrame) {
				try {
					var lib = activeFrame.state && activeFrame.state() && activeFrame.state().get('library');
					if (lib && lib.props) { browserCollection = lib; }
				} catch (e) {}
			}
			// Fallback 2: navigate back to the grid/browse view so the user sees results.
			if (activeFrame && activeFrame.content && typeof activeFrame.content.mode === 'function') {
				try { activeFrame.content.mode('browse'); } catch (e) {}
			}

			if ( !browserCollection) return;

			var props = browserCollection.props;

			if (folderId === 'all') {
				props.unset('iu_folder');
			} else if (folderId === 'uncategorized') {
				props.set('iu_folder', 'uncategorized');
			} else {
				props.set('iu_folder', folderId.replace('folder_', ''));
			}

			browserCollection.reset();
			browserCollection.more();
		},

		// -----------------------------------------------------------------
		// Media file sort bar
		// -----------------------------------------------------------------

		/**
		 * Build the sort select + order-toggle button HTML.
		 * Returns two sibling elements (no wrapper) so they sit inline
		 * with WP's native attachment-filter selects.
		 */
		buildMediaSortBarHtml: function () {
			var self = this;
			var s    = iuMediaFolders;

			var options = [
				['date',      s.media_sort_date],
				['modified',  s.media_sort_modified],
				['name',      s.media_sort_name],
				['filename',  s.media_sort_filename],
				['author',    s.media_sort_author],
				['file_type', s.media_sort_file_type],
				['extension', s.media_sort_extension],
				['file_size', s.media_sort_file_size],
			];

			var optHtml = '';
			options.forEach(function (o) {
				var sel = (o[0] === self.mediaSortMode) ? ' selected' : '';
				optHtml += '<option value="' + o[0] + '"' + sel + '>' + o[1] + '</option>';
			});

			var isAsc = (self.mediaSortOrder === 'asc');
			var icon  = isAsc ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2';
			var title = isAsc ? s.media_sort_desc : s.media_sort_asc;

			// Wrap both controls in a single span so they are always injected
			// as one DOM node and stay together regardless of toolbar layout.
			return (
				'<span class="iu-media-sort-wrap">' +
				'<select class="iu-media-sort-select" title="' + s.media_sort_label + '">' +
				optHtml +
				'</select>' +
				'<button type="button" class="iu-media-sort-order" title="' + title + '">' +
				'<span class="dashicons ' + icon + '"></span>' +
				'</button>' +
				'</span>'
			);
		},

		/**
		 * Inject the sort controls next to the "All dates" filter inside $scope.
		 * $scope is the frame element (grid) or document.body (list mode).
		 * Safe to call multiple times — guarded by presence of .iu-media-sort-select.
		 */
		injectMediaSortBar: function ($scope) {
			if (!$scope || !$scope.length) return;

			// Limit searches to .attachments-browser when present so we never
			// accidentally inject into the bottom footer bar of the modal.
			var $browser = $scope.find('.attachments-browser');
			var $root    = $browser.length ? $browser : $scope;

			// Inject sort controls only once; search-fields button is always attempted.
			if (!$root.find('.iu-media-sort-wrap').length) {
				var html = this.buildMediaSortBarHtml();

				// List mode: insert after the "Filter" submit button in the tablenav.
				var $filterBtn = $root.find('#post-query-submit');
				if ($filterBtn.length) {
					$filterBtn.after(html);
				} else {
					// Grid mode: insert after the "All dates" dropdown in the media toolbar.
					var $after = $root.find('#media-attachment-date-filters');
					if ($after.length) {
						$after.after(html);
						$after.closest('.media-toolbar-secondary').addClass('iu-has-sort');
					} else {
						// Fallback: append to secondary toolbar.
						var $secondary = $root.find('.media-toolbar-secondary').first();
						if ($secondary.length) {
							$secondary.append(html);
							$secondary.addClass('iu-has-sort');
						}
					}
				}
			}

			// Always inject the search-fields button (guarded inside).
			this.injectSearchFieldsBtn($root);
		},

		/**
		 * Build the search-fields popover HTML (checkboxes for each searchable field).
		 */
		buildSearchFieldsHtml: function () {
			var self = this;
			var s    = iuMediaFolders;
			var defs = [
				['title',       s.search_field_title],
				['filename',    s.search_field_filename],
				['alt',         s.search_field_alt],
				['description', s.search_field_description],
				['caption',     s.search_field_caption],
				['folders',     s.search_field_folders],
			];
			var rows = defs.map(function (f) {
				var checked = self.mediaSearchFields.indexOf(f[0]) !== -1 ? ' checked' : '';
				return (
					'<label class="iu-search-field-item">' +
					'<input type="checkbox" value="' + f[0] + '"' + checked + '> ' +
					f[1] +
					'</label>'
				);
			}).join('');
			return (
				'<div class="iu-search-fields-popover" hidden>' +
				'<div class="iu-search-fields-title">' + s.search_fields_label + '</div>' +
				rows +
				'</div>'
			);
		},

		/**
		 * Inject the search-fields toggle button next to the media search input.
		 * Grid/modal: prepends to .media-toolbar-primary.
		 * List mode: prepends to .search-box (before submit button).
		 */
		injectSearchFieldsBtn: function ($scope) {
			if (!$scope || !$scope.length) return;
			if ($scope.find('.iu-search-fields-btn').length) return;
			var btnHtml = (
				'<span class="iu-search-fields-wrap">' +
				'<button type="button" class="iu-search-fields-btn" title="' + iuMediaFolders.search_fields_label + '">' +
				'<span class="dashicons dashicons-filter"></span>' +
				'</button>' +
				this.buildSearchFieldsHtml() +
				'</span>'
			);
			var $primary = $scope.find('.media-toolbar-primary').first();
			if ($primary.length) {
				$primary.prepend(btnHtml);
				// Remove only the direct-child label (the visible 'Search media' heading).
				$primary.children('label').remove();
				$primary.find('input[type="search"]').attr('placeholder', iuMediaFolders.search_placeholder || 'Search Media...');
				return;
			}
			// List mode: inject before the search-box paragraph.
			var $searchBox = $scope.find('.search-box');
			if ($searchBox.length) {
				$searchBox.before(btnHtml);
				this._syncListSearchFields();
			}
		},

		/**
		 * Sync hidden iu_search_fields[] inputs in the list-mode search form
		 * with the current mediaSearchFields state.
		 */
		_syncListSearchFields: function () {
			var self = this;
			var $form = $('#posts-filter');
			if (!$form.length) return;
			$form.find('input[name="iu_search_fields[]"]').remove();
			self.mediaSearchFields.forEach(function (field) {
				$form.append('<input type="hidden" name="iu_search_fields[]" value="' + field + '">');
			});
		},

		/**
		 * Force a grid-mode re-query after search field state changes.
		 * Uses a timestamp prop to guarantee _requery fires.
		 */
		_applySearchFieldsRequery: function () {
			var activeFrame = this._activeFrame || (typeof wp !== 'undefined' && wp.media && wp.media.frame);
			if (!activeFrame) return;
			var content = activeFrame.content && activeFrame.content.get();
			if (!content) return;
			var browserCollection = content.collection || (content.options && content.options.collection);
			if (!browserCollection || !browserCollection.props) return;
			// Any prop change triggers WP's _requery; timestamp ensures it fires every time.
			browserCollection.props.set('iu_query_time', Date.now());
		},

		/**
		 * Force a grid re-query without clearing the collection first.
		 * Safe to call even when the folder/sort props haven't changed --
		 * the timestamp guarantees Backbone sees a change and fires _requery.
		 */
		_forceGridRequery: function () {
			var activeFrame = this._activeFrame || (typeof wp !== 'undefined' && wp.media && wp.media.frame);
			if (!activeFrame) return;
			var content = activeFrame.content && activeFrame.content.get();
			if (!content) return;
			var browserCollection = content.collection || (content.options && content.options.collection);
			if (!browserCollection || !browserCollection.props) return;
			browserCollection.props.set('iu_query_time', Date.now());
		},

		/**
		 * Map of our sort keys → WP-native orderby values.
		 * These are handled entirely by WordPress core; no PHP intervention needed.
		 */
		_nativeSortMap: {
			date:     'date',
			modified: 'modified',
			name:     'title',
			filename: 'name',
			author:   'author',
		},

		/**
		 * Apply the current mediaSortMode / mediaSortOrder.
		 *
		 * Grid mode: call props.set() on the Attachments collection so WP's
		 *   internal _requery fires and creates a fresh Query (fresh _hasMore
		 *   state, guaranteed refetch). Native sorts use WP's own orderby/order
		 *   props; custom sorts set iu_media_orderby which the sync patch picks up.
		 *
		 * List mode: navigate to the URL with updated sort params.
		 */
		applyMediaSort: function () {
			if (this.isListMode) {
				var url = new URL(window.location.href);
				url.searchParams.set('iu_media_orderby', this.mediaSortMode);
				url.searchParams.set('iu_media_order',   this.mediaSortOrder);
				url.searchParams.delete('paged');
				window.location.href = url.toString();
				return;
			}

			// Grid mode — same three-path resolution as filterGridMode.
			var activeFrame = this._activeFrame || wp.media.frame;
			if (!activeFrame) return;

			var browserCollection = null;

			// Path 1: named 'library' state (works even in edit-attachment state).
			try {
				var libraryState = activeFrame.states && activeFrame.states.get('library');
				if (libraryState) {
					var lib = libraryState.get('library');
					if (lib && lib.props) { browserCollection = lib; }
				}
			} catch (e) {}

			// Path 2: current state library (page builder modals).
			if (!browserCollection) {
				try {
					var lib2 = activeFrame.state && activeFrame.state() && activeFrame.state().get('library');
					if (lib2 && lib2.props) { browserCollection = lib2; }
				} catch (e) {}
			}

			// Path 3: active content view's collection.
			if (!browserCollection) {
				try {
					var cv = activeFrame.content && activeFrame.content.get();
					var bc = cv && (cv.collection || (cv.options && cv.options.collection));
					if (bc && bc.props) { browserCollection = bc; }
				} catch (e) {}
			}

			if (!browserCollection) return;

			// If in edit-attachment state, switch back to library so changes are visible.
			try {
				if (activeFrame.states && activeFrame.states.get('library') &&
					activeFrame.state && activeFrame.state() &&
					activeFrame.state().id !== 'library') {
					activeFrame.setState('library');
				}
			} catch (e) {}

			var props    = browserCollection.props;
			var nativeWp = this._nativeSortMap[this.mediaSortMode];
			var order    = this.mediaSortOrder.toUpperCase();

			if (nativeWp) {
				// Restore WP's native client-side comparator (it reads props.orderby/order
				// and re-sorts the collection correctly for date/title/etc.).
				// We may have nulled it for a previous custom sort, so delete the own
				// property to let the prototype method take over again.
				delete browserCollection.comparator;
				// Clear any custom sort prop first (silent so _requery fires only once).
				props.unset('iu_media_orderby', {silent: true});
				props.unset('iu_media_order',   {silent: true});
				// Set WP's own orderby/order props — this triggers _requery and a fresh fetch.
				props.set({ orderby: nativeWp, order: order });
			} else {
				// Custom sort (file_size / extension / file_type).
				// WP's collection comparator re-sorts models client-side after each fetch,
				// discarding the server's order. Setting comparator to null tells Backbone
				// to preserve insertion (server-response) order instead.
				browserCollection.comparator = null;
				// Setting iu_media_orderby triggers _requery; our sync patch then injects
				// the value into the POST data so PHP can apply custom SQL ordering.
				props.set({ iu_media_orderby: this.mediaSortMode, iu_media_order: order });
			}
		},

		// -----------------------------------------------------------------
		// Folder move (DnD reparent via tree)
		// -----------------------------------------------------------------

		onFolderMove: function (nodeId, newParentId) {
			var self = this;
			var termId = parseInt(nodeId.replace('folder_', ''), 10);
			var newParent = 0;

			if (newParentId && newParentId !== '#') {
				newParent = parseInt(newParentId.replace('folder_', ''), 10);
				// Ensure the destination folder is expanded after the tree reloads.
				self.expandedNodes.add(newParentId);
				self.persistExpanded();
			}

			self.showTreeLoader();
			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_move_folder',
				nonce: iuMediaFolders.nonce,
				term_id: termId,
				parent: newParent,
			}, function (response) {
				self.hideTreeLoader();
				if (response.success) {
					self.refreshTree(function () { self.highlightMovedNodes([nodeId]); });
				} else {
					self.refreshTree();
					alert(response.data);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// Bulk move: move multiple folders to a new parent
		// -----------------------------------------------------------------

		bulkMoveFolders: function (nodeIds, targetNodeId) {
			var self = this;

			// Filter to valid folder_ IDs, excluding the target itself
			var validIds = nodeIds.filter(function (nid) {
				return nid && String(nid).indexOf('folder_') === 0 && nid !== targetNodeId;
			});
			if (!validIds.length) return;

			var termIds = validIds.map(function (nid) {
				return parseInt(nid.replace('folder_', ''), 10);
			});

			var newParent = (targetNodeId && targetNodeId !== '#' && String(targetNodeId).indexOf('folder_') === 0)
				? parseInt(targetNodeId.replace('folder_', ''), 10) : 0;

			// Ensure the destination folder is expanded after the tree reloads.
			if (targetNodeId && targetNodeId !== '#' && String(targetNodeId).indexOf('folder_') === 0) {
				self.expandedNodes.add(targetNodeId);
				self.persistExpanded();
			}

			self.showTreeLoader();
			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_bulk_move_folders',
				nonce: iuMediaFolders.nonce,
				term_ids: termIds,
				parent: newParent,
			}, function (response) {
				self.hideTreeLoader();
				if (response.success) {
					self.refreshTree(function () { self.highlightMovedNodes(validIds); });
				} else {
					self.refreshTree();
					var msg = (response.data && response.data.message) ? response.data.message : response.data;
					if (msg) alert(msg);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// HTML5 Drag-and-Drop: media items -> folder tree
		// -----------------------------------------------------------------

		hookGridMode: function () {
			var self = this;

			// Patch wp.media.model.Query to pass iu_folder and custom sort params.
			// We read from this.props (per-query) so each Query instance carries its own
			// values, exactly the same way iu_folder is already handled.
			if (typeof wp !== 'undefined' && wp.media && wp.media.model && wp.media.model.Query) {
				var origSync = wp.media.model.Query.prototype.sync;
				wp.media.model.Query.prototype.sync = function (method, model, options) {
					options = options || {};
					options.data = options.data || {};

					// Folder filter (existing pattern).
					var folder = this.props.get('iu_folder');
					if (folder) {
						options.data.iu_folder = folder;
					}

					// Custom sorts (file_size / extension / file_type) — set via
					// applyMediaSort(); native sorts use WP's own orderby/order props.
					var customSort = this.props.get('iu_media_orderby');
					if (customSort) {
						options.data.iu_media_orderby = customSort;
						options.data.iu_media_order   = this.props.get('iu_media_order') || 'DESC';
					}

					// Always send search fields; PHP applies them only when s= is present.
					options.data.iu_search_fields = self.mediaSearchFields;

					return origSync.call(this, method, model, options);
				};
			}

			self.markDraggable();

			var debounce = null;
			var draggableObserver = new MutationObserver(function () {
				clearTimeout(debounce);
				debounce = setTimeout(function () {
					self.markDraggable();
				}, 200);
			});

			// On upload.php the sidebar is already in the existing frame; observe for DnD.
			var existingFrame = iuMediaFolders.is_upload_page
				? document.querySelector('.media-frame')
				: null;
			if (existingFrame) {
				draggableObserver.observe(existingFrame, {childList: true, subtree: true});

				// Inject sort controls next to the "All dates" filter.
				setTimeout(function () {
					self.injectMediaSortBar($(existingFrame));
				}, 500);
			}

			// Extend AttachmentsBrowser so the sidebar is injected into every
			// dynamically-opened wp.media() modal (page builders, WooCommerce gallery,
			// ACF, Gutenberg, etc.).  The method guards against double injection.
			self._extendAttachmentsBrowser(draggableObserver);
		},

		/**
		 * Extend wp.media.view.AttachmentsBrowser so the folder sidebar is injected
		 * into every dynamically-opened wp.media() modal.
		 *
		 * Works with page builders (Elementor, Avada, Oxygen, WPBakery, Divi…),
		 * Gutenberg, ACF, WooCommerce gallery, featured-image pickers, etc.
		 */
		_extendAttachmentsBrowser: function (draggableObserver) {
			var self = this;

			if (
				typeof wp === 'undefined' ||
				!wp.media ||
				!wp.media.view ||
				!wp.media.view.AttachmentsBrowser
			) {
				return;
			}

			// Guard against double-wrapping when hookGridMode() is called multiple times.
			if (wp.media.view.AttachmentsBrowser._iuExtended) {
				return;
			}
			wp.media.view.AttachmentsBrowser._iuExtended = true;

			var OrigBrowser = wp.media.view.AttachmentsBrowser;

			wp.media.view.AttachmentsBrowser = OrigBrowser.extend({
				createToolbar: function () {
					OrigBrowser.prototype.createToolbar.apply(this, arguments);

					var controller = this.controller;
					var $frame = $(controller.el);

					// Skip frames that already have the sidebar (upload.php inline frame).
					if ($frame.find('#iu-media-folders-wrap').length) {
						self._activeFrame = controller;
						return;
					}

					function injectIntoFrame() {
						var $f = $(controller.el);

						// Already injected — just update the active frame reference.
						if ($f.find('#iu-media-folders-wrap').length) {
							self._activeFrame = controller;
							return;
						}

						var $menu = $f.find('.media-frame-menu');
						if (!$menu.length) return;

						// WP hides modals on close rather than removing them from the DOM.
						// A hidden frame's #iu-folders-tree / #iu-media-folders-wrap IDs
						// conflict with the fresh ones we inject here, causing buildTree()
						// to target the wrong (invisible) element. Remove any leftover
						// sidebars from other modal frames before injecting the new one.
						$('.media-frame-menu #iu-media-folders-wrap').remove();
						$('.media-frame-menu .iu-media-sort-wrap').remove();
						$('.media-frame-menu .iu-search-fields-wrap').remove();

						// Generate fresh HTML — each modal gets its own sidebar instance.
						var sidebarHtml = self.buildSidebarHtml(false, '');

						$menu.prepend(sidebarHtml);
						$f.removeClass('hide-menu').addClass('iu-has-menu-sidebar');
						$f.find('#iu-media-folders-wrap').removeClass('iu-collapsed');
						$f.find('.media-frame-menu-heading').hide();

						self._activeFrame = controller;

						// Inject sort controls next to the "All dates" filter.
						setTimeout(function () {
							self.injectMediaSortBar($(controller.el));
						}, 200);

						if (self.folders.length) {
							self.buildTree();
							self.renderCountBadges();
						}
						self.selectVirtualFolder('all');

						if (draggableObserver) {
							draggableObserver.observe(controller.el, {childList: true, subtree: true});
						}
						setTimeout(function () { self.markDraggable(); }, 300);
					}

					// Bind to `open` so injection happens every time the frame is
					// displayed — covers Elementor's lazy-create / reuse pattern
					// where createToolbar fires once but open() is called on each use.
					controller.on('open', function () {
						setTimeout(injectIntoFrame, 50);
					});

					// Immediate attempt for frames already visible when createToolbar fires.
					setTimeout(function () {
						if (!$(controller.el).find('#iu-media-folders-wrap').length &&
							$(controller.el).find('.media-frame-menu').length) {
							injectIntoFrame();
						}
					}, 100);
				},
			});
		},

		hookListMode: function () {
			var self = this;

			// Restore sort state from URL on list mode page load.
			var urlParams = new URLSearchParams(window.location.search);
			var urlSortMode  = urlParams.get('iu_media_orderby');
			var urlSortOrder = urlParams.get('iu_media_order');
			if (urlSortMode)  { self.mediaSortMode  = urlSortMode; }
			if (urlSortOrder) { self.mediaSortOrder = urlSortOrder; }

			// Inject sort controls next to the date filter in the list-mode toolbar.
			setTimeout(function () {
				self.injectMediaSortBar($(document.body));
			}, 200);

			var currentFolderParam = urlParams.get('iu_folder');

			// Wait for tree to load then restore selection
			var checkReady = setInterval(function () {
				if ($('#iu-folders-tree .iu-tree-node').length || !self.folders.length) {
					clearInterval(checkReady);

					if (currentFolderParam === 'uncategorized') {
						self.selectVirtualFolder('uncategorized');
					} else if (currentFolderParam && currentFolderParam !== '') {
						// Select the folder node without triggering navigation
						var nodeId = 'folder_' + currentFolderParam;
						$('#iu-folders-tree .iu-node-row.iu-selected').removeClass('iu-selected');
						var $node = $('#iu-folders-tree .iu-tree-node[data-id="' + nodeId + '"]');
						$node.children('.iu-node-row').addClass('iu-selected');
						self.selectedNode = nodeId;
						self.deselectVirtualFolders();
						self.updateToolbarButtons(nodeId);
						self.currentFolder = nodeId;

						// Expand parent nodes
						$node.parents('.iu-tree-node').each(function () {
							var pid = $(this).data('id');
							$(this).children('.iu-node-children').removeClass('hidden');
							$(this).children('.iu-node-row').find('.iu-node-toggle').not('.iu-leaf').addClass('iu-expanded');
							self.expandedNodes.add(pid);
						});
						self.persistExpanded();
					} else {
						self.selectVirtualFolder('all');
					}
				}
			}, 100);

			setTimeout(function () {
				self.markDraggable();
			}, 300);
		},

		markDraggable: function () {
			if (this.isListMode) {
				$('#the-list tr:not([draggable])').attr('draggable', 'true');
			} else {
				$('.attachments-browser .attachment:not([draggable])').attr('draggable', 'true');
			}
		},

		// -----------------------------------------------------------------
		// Shared drag / drop event handlers (bound via delegation)
		// -----------------------------------------------------------------

		bindEvents: function () {
			var self = this;

			// --- Sidebar toggle ---
			$(document).on('click', '.iu-sidebar-toggle', function (e) {
				e.preventDefault();
				var $wrap = $('#iu-media-folders-wrap');
				$wrap.toggleClass('iu-collapsed');
				localStorage.setItem('iu_folders_collapsed', $wrap.hasClass('iu-collapsed') ? '1' : '0');
			});

			// --- New folder button ---
			$(document).on('click', '.iu-folder-add-btn', function (e) {
				e.preventDefault();
				self.startCreate('#');
			});

			// --- Toolbar Rename ---
			$(document).on('click', '.iu-action-rename', function (e) {
				e.preventDefault();
				if (self.selectedNode) {
					self.startRename(self.selectedNode);
				}
			});

			// --- Toolbar Delete ---
			$(document).on('click', '.iu-action-delete', function (e) {
				e.preventDefault();
				var count = self.selectedNodes ? self.selectedNodes.size : 0;
				if (count >= 2) {
					var msg = iuMediaFolders.confirm_bulk_delete.replace('%d', count);
					if (confirm(msg)) self.bulkDeleteFolders(Array.from(self.selectedNodes));
				} else if (self.selectedNode && confirm(iuMediaFolders.confirm_delete)) {
					self.deleteFolder(self.selectedNode);
				}
			});

			// --- Sort dropdown toggle ---
			$(document).on('click', '.iu-sort-btn', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var $menu = $(this).siblings('.iu-sort-menu');
				$menu.toggleClass('iu-open');
				$('.iu-more-menu').removeClass('iu-open');
			});

			// --- Sort item selection ---
			$(document).on('click', '.iu-sort-item[data-sort]', function (e) {
				e.preventDefault();
				var mode = $(this).data('sort');
				$('.iu-sort-menu').removeClass('iu-open');
				self.sortFolders(mode);
			});

			// --- More dropdown toggle ---
			$(document).on('click', '.iu-action-more', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var $menu = $(this).siblings('.iu-more-menu');
				$menu.toggleClass('iu-open');
				$('.iu-sort-menu').removeClass('iu-open');
			});

			// --- Media file sort select ---
			$(document).on('change', '.iu-media-sort-select', function () {
				self.mediaSortMode = $(this).val();
				localStorage.setItem('iu_media_sort', self.mediaSortMode);
				self.applyMediaSort();
			});

			// --- Media sort order toggle ---
			$(document).on('click', '.iu-media-sort-order', function () {
				self.mediaSortOrder = (self.mediaSortOrder === 'asc') ? 'desc' : 'asc';
				localStorage.setItem('iu_media_sort_order', self.mediaSortOrder);

				// Update icon and title.
				var isAsc = (self.mediaSortOrder === 'asc');
				$(this).find('.dashicons')
					.removeClass('dashicons-arrow-up-alt2 dashicons-arrow-down-alt2')
					.addClass(isAsc ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2');
				$(this).attr('title', isAsc ? iuMediaFolders.media_sort_desc : iuMediaFolders.media_sort_asc);

				self.applyMediaSort();
			});

			// --- Search fields popover toggle ---
			$(document).on('click', '.iu-search-fields-btn', function (e) {
				e.stopPropagation();
				var $btn     = $(this);
				var $popover = $btn.siblings('.iu-search-fields-popover');
				var isHidden = $popover.prop('hidden');

				// Close any open popover first.
				$('.iu-search-fields-popover').prop('hidden', true);
				$('.iu-search-fields-btn').removeClass('iu-active');

				if (isHidden) {
					// Position as fixed so it escapes any overflow:hidden parents
					// (WP's toolbar clips absolutely-positioned children).
					var rect = this.getBoundingClientRect();
					$popover.css({
						position: 'fixed',
						top:  (rect.bottom + 4) + 'px',
						left: rect.left + 'px',
					});
					$popover.prop('hidden', false);
					$btn.addClass('iu-active');
				}
			});

			// --- Search field checkbox ---
			$(document).on('change', '.iu-search-fields-popover input[type="checkbox"]', function () {
				var fields = [];
				$(this).closest('.iu-search-fields-popover').find('input[type="checkbox"]:checked').each(function () {
					fields.push($(this).val());
				});
				self.mediaSearchFields = fields;
				localStorage.setItem('iu_search_fields', JSON.stringify(fields));
				if (self.isListMode) {
					self._syncListSearchFields();
				} else {
					self._applySearchFieldsRequery();
				}
			});

			// Close dropdowns on outside click.
			$(document).on('click', function (e) {
				if ( !$(e.target).closest('.iu-more-dropdown').length) {
					$('.iu-more-menu').removeClass('iu-open');
				}
				if ( !$(e.target).closest('.iu-sort-dropdown').length) {
					$('.iu-sort-menu').removeClass('iu-open');
				}
				if ( !$(e.target).closest('.iu-search-fields-wrap').length) {
					$('.iu-search-fields-popover').prop('hidden', true);
					$('.iu-search-fields-btn').removeClass('iu-active');
				}
			});

			// --- Expand all ---
			$(document).on('click', '.iu-expand-all-btn', function (e) {
				e.preventDefault();
				self.expandAll();
				$('.iu-more-menu').removeClass('iu-open');
			});

			// --- Collapse all ---
			$(document).on('click', '.iu-collapse-all-btn', function (e) {
				e.preventDefault();
				self.collapseAll();
				$('.iu-more-menu').removeClass('iu-open');
			});

			// --- Virtual folder click ---
			$(document).on('click', '.iu-virtual-folder', function (e) {
				e.preventDefault();
				var folder = $(this).data('folder');
				self.selectVirtualFolder(folder);
				self.onFolderSelect(folder);
			});

			// --- Tree node click (select; supports Ctrl/Cmd multi-select and Shift range-select) ---
			$(document).on('click', '#iu-folders-tree .iu-node-row', function (e) {
				if ($(e.target).hasClass('iu-rename-input')) return;

				var $node = $(this).closest('.iu-tree-node');
				var nodeId = $node.data('id');

				if ($(e.target).closest('.iu-node-toggle').length) {
					self.toggleNode(nodeId);
					return;
				}

				var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
				var isCtrl = isMac ? e.metaKey : e.ctrlKey;

				if (isCtrl && nodeId && String(nodeId).indexOf('folder_') === 0) {
					self.selectNode(nodeId, true /* addToSelection */);
				} else if (e.shiftKey && self.selectedNode && nodeId && String(nodeId).indexOf('folder_') === 0) {
					self.rangeSelectTo(nodeId);
				} else {
					self.selectNode(nodeId);
				}
			});

			// --- Right-click on "All Files" virtual folder (paste cut folder to root) ---
			$(document).on('contextmenu', '.iu-virtual-folder[data-folder="all"]', function (e) {
				if (!self.cutNode) return;
				e.preventDefault();
				e.stopPropagation();
				var items = '<button class="iu-context-menu-item" data-action="paste-root"><span class="dashicons dashicons-clipboard"></span>' + iuMediaFolders.paste_to_root + '</button>';
				var $menu = $('<div class="iu-context-menu" data-node="#">' + items + '</div>');
				$('body').append($menu);
				var left = e.clientX, top = e.clientY;
				if (left + $menu.outerWidth()  > window.innerWidth  - 5) left = window.innerWidth  - $menu.outerWidth()  - 5;
				if (top  + $menu.outerHeight() > window.innerHeight - 5) top  = e.clientY - $menu.outerHeight();
				$menu.css({left: left, top: top});
			});

			$(document).on('click', '.iu-context-menu-item[data-action="paste-root"]', function (e) {
				e.preventDefault();
				self.closeContextMenu();
				self.pasteFolder('#');
			});

			// --- Tree node right-click (context menu) ---
			$(document).on('contextmenu', '#iu-folders-tree .iu-node-row', function (e) {
				var nodeId = String($(this).closest('.iu-tree-node').data('id') || '');
				if (!nodeId) return;

				if (nodeId.indexOf('folder_') === 0) {
					self.showContextMenu(e, nodeId);
					return;
				}

				// AJAX still in-flight for a newly created folder: wait for it.
				if (nodeId.indexOf('iu_temp_') === 0 && self._pendingCreates[nodeId]) {
					e.preventDefault();
					e.stopPropagation();
					var pos = { clientX: e.clientX, clientY: e.clientY };
					self._pendingCreates[nodeId].done(function (realNodeId) {
						self.showContextMenu(pos, realNodeId);
					});
				}
			});

			// --- Context menu item click ---
			$(document).on('click', '.iu-context-menu-item', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var action  = $(this).data('action');
				var nodeId  = $(this).closest('.iu-context-menu').data('node');

				if (action === 'color') {
					var $submenu = $(this).siblings('.iu-color-submenu');
					$submenu.toggleClass('iu-open');
					if ($submenu.hasClass('iu-open')) {
						// Flip to left if submenu overflows the right viewport edge.
						var rect = $submenu[0].getBoundingClientRect();
						$submenu.toggleClass('iu-flip-left', rect.right > window.innerWidth - 5);
					}
					return;
				}

				self.closeContextMenu();

				switch (action) {
					case 'create':
						self.startCreate(nodeId);
						break;
					case 'rename':
						self.startRename(nodeId);
						break;
					case 'cut':
						self.cutFolder(nodeId);
						break;
					case 'paste':
						self.pasteFolder(nodeId);
						break;
					case 'move-to-root':
						self.bulkMoveFolders([nodeId], '#');
						break;
					case 'delete':
						if (confirm(iuMediaFolders.confirm_delete)) {
							self.deleteFolder(nodeId);
						}
						break;
				}
			});

			// --- Color swatch click ---
			$(document).on('click', '.iu-color-swatch', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var color  = $(this).data('color') || '';
				var nodeId = $(this).closest('.iu-context-menu').data('node');
				self.closeContextMenu();
				self.setFolderColor(nodeId, color);
			});

			// --- Custom color input ---
			$(document).on('change', '.iu-custom-color-input', function (e) {
				e.stopPropagation();
				var color  = $(this).val();
				var nodeId = $(this).closest('.iu-context-menu').data('node');
				self.closeContextMenu();
				self.setFolderColor(nodeId, color);
			});

			// --- Close context menu on outside click / Escape / scroll ---
			$(document).on('click', function (e) {
				if ( !$(e.target).closest('.iu-context-menu').length) {
					self.closeContextMenu();
				}
			});

			$(document).on('keydown', function (e) {
				if (e.key === 'Escape') {
					self.closeContextMenu();
				}
			});

			// Note: sidebar scroll is handled via capture listener in initSearch(),
			// scroll events don't bubble so cannot be delegated here.

			// --- Virtual folder drag-drop targets ---
			$(document)
				.on('dragover', '.iu-virtual-folder', function (e) {
					e.preventDefault();
					e.originalEvent.dataTransfer.dropEffect = 'move';
					$(this).addClass('iu-drop-hover');
				})
				.on('dragleave', '.iu-virtual-folder', function (e) {
					var related = e.originalEvent.relatedTarget;
					if ( !this.contains(related)) {
						$(this).removeClass('iu-drop-hover');
					}
				})
				.on('drop', '.iu-virtual-folder', function (e) {
					e.preventDefault();
					e.stopPropagation();
					$(this).removeClass('iu-drop-hover');

					// If folders are being dragged, handle reparent to root
					if (self.folderDragNodeIds.length) {
						var folder = $(this).data('folder');
						if (folder === 'all') {
							self.bulkMoveFolders(self.folderDragNodeIds, '#');
						}
						self.folderDragNodeIds = [];
						return;
					}

					var folder = $(this).data('folder');
					var ids = self.dragIds.length ? self.dragIds : [];
					if ( !ids.length) {
						try {
							ids = JSON.parse(e.originalEvent.dataTransfer.getData('text/plain'));
						} catch (err) {
							ids = [];
						}
					}
					if (ids.length && folder !== 'all') {
						self.moveMedia(ids, folder);
					}
				});

			// --- Native dragstart on media items (delegated) ---
			$(document).on('dragstart', '.attachment[draggable], #the-list tr[draggable]', function (e) {
				// Don't handle if this is a folder row drag
				if ($(e.target).closest('.iu-node-row').length) return;

				var ids = self.collectDragIds($(this));
				if ( !ids.length) {
					e.preventDefault();
					return;
				}

				self.dragIds = ids;

				e.originalEvent.dataTransfer.effectAllowed = 'move';
				e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify(ids));

				var ghost = self.buildDragGhost($(this), ids.length);
				document.body.appendChild(ghost);
				e.originalEvent.dataTransfer.setDragImage(ghost, 28, 28);
				setTimeout(function () {
					document.body.removeChild(ghost);
				}, 0);

				// Mark body so CSS can suppress WP's "Drop files to upload" overlay.
				document.body.classList.add('iu-dragging-media');

				$('#iu-media-folders-wrap').removeClass('iu-collapsed');
				$('#iu-media-folders-sidebar').addClass('iu-drop-active');
			});

			$(document).on('dragend', '.attachment[draggable], #the-list tr[draggable]', function () {
				self.dragIds = [];
				document.body.classList.remove('iu-dragging-media');
				$('#iu-media-folders-sidebar').removeClass('iu-drop-active');
				$('#iu-folders-tree .iu-drop-hover').removeClass('iu-drop-hover');
				$('.iu-virtual-folder.iu-drop-hover').removeClass('iu-drop-hover');
			});

			// Suppress WP's plupload upload overlay during attachment-to-folder drags.
			// WP listens to dragenter/dragover in bubble phase; we intercept in capture
			// phase (useCapture=true) so we run first and can stop propagation before
			// WP's handlers ever see the event.
			// Folder highlighting is handled here too because stopPropagation prevents
			// the bubble-phase jQuery dragover handlers from firing.
			document.addEventListener('dragenter', function (e) {
				var isDragging = self.dragIds.length || self.folderDragNodeIds.length;
				if (!isDragging) return;
				e.stopPropagation();
				// Highlight whichever folder the cursor just entered; clear others.
				var $row = $(e.target).closest('.iu-node-row');
				var $vf  = $(e.target).closest('.iu-virtual-folder');
				$('.iu-node-row.iu-drop-hover, .iu-virtual-folder.iu-drop-hover').removeClass('iu-drop-hover');
				if ($row.length) { $row.addClass('iu-drop-hover'); }
				else if ($vf.length) { $vf.addClass('iu-drop-hover'); }
				// Highlight 'All Files' when dragging a folder over empty sidebar space
				// (drop will move folder to root).
				else if (self.folderDragNodeIds.length && $(e.target).closest('#iu-media-folders-sidebar').length) {
					$('.iu-virtual-folder[data-folder="all"]').addClass('iu-drop-hover');
				}
			}, true);

			document.addEventListener('dragover', function (e) {
				var isDragging = self.dragIds.length || self.folderDragNodeIds.length;
				if (!isDragging) return;
				e.stopPropagation();
				// Allow drop on folder node rows and virtual folders.
				var overFolder = $(e.target).closest('.iu-node-row, .iu-virtual-folder').length > 0;
				// Also allow drop on empty sidebar space when dragging a folder
				// (moves to root). Without this, e.preventDefault() is never called
				// during dragover → browser never fires the drop event over empty space.
				var overSidebarEmpty = !overFolder && self.folderDragNodeIds.length > 0 &&
					$(e.target).closest('#iu-media-folders-sidebar').length > 0;
				var canDrop = overFolder || overSidebarEmpty;
				e.dataTransfer.dropEffect = canDrop ? 'move' : 'none';
				if (canDrop) e.preventDefault();
			}, true);

			// --- Drop targets: custom tree node rows (delegated from document so
			//     they work even when #iu-folders-tree is added to the DOM after init) ---
			$(document)
				.on('dragover', '#iu-folders-tree .iu-node-row', function (e) {
					e.preventDefault();
					e.originalEvent.dataTransfer.dropEffect = 'move';
					$(this).addClass('iu-drop-hover');
				})
				.on('dragleave', '#iu-folders-tree .iu-node-row', function (e) {
					var related = e.originalEvent.relatedTarget;
					if ( !this.contains(related)) {
						$(this).removeClass('iu-drop-hover');
					}
				})
				.on('drop', '#iu-folders-tree .iu-node-row', function (e) {
					e.preventDefault();
					e.stopPropagation();

					var $row = $(this);
					$row.removeClass('iu-drop-hover');

					var $node = $row.closest('.iu-tree-node');
					var targetNodeId = $node.data('id');

					// If folders are being dragged (reparent)
					if (self.folderDragNodeIds.length) {
						// Prevent dropping onto one of the dragged nodes itself
						if (self.folderDragNodeIds.indexOf(targetNodeId) === -1) {
							self.bulkMoveFolders(self.folderDragNodeIds, targetNodeId);
						}
						self.folderDragNodeIds = [];
						return;
					}

					// Media drag
					var ids = self.dragIds.length ? self.dragIds : [];
					if ( !ids.length) {
						try {
							ids = JSON.parse(e.originalEvent.dataTransfer.getData('text/plain'));
						} catch (err) {
							ids = [];
						}
					}

					if (ids.length && targetNodeId) {
						self.moveMedia(ids, targetNodeId);
					}
				});

			// --- Drop on tree container empty space (reparent folder to root) ---
			$(document)
				.on('dragover', '#iu-folders-tree', function (e) {
					// Only accept if folders are being dragged
					if (self.folderDragNodeIds.length) {
						e.preventDefault();
						e.originalEvent.dataTransfer.dropEffect = 'move';
					}
				})
				.on('drop', '#iu-folders-tree', function (e) {
					// Only handle if drop landed on the container itself, not on a node row
					if ($(e.target).closest('.iu-node-row').length) return;

					if (self.folderDragNodeIds.length) {
						e.preventDefault();
						e.stopPropagation();
						self.bulkMoveFolders(self.folderDragNodeIds, '#');
						self.folderDragNodeIds = [];
					}
				});

			// --- Folder node drag (reparent via DnD; supports multi-select) ---
			$(document).on('dragstart', '#iu-folders-tree .iu-node-row[draggable]', function (e) {
				var $node = $(this).closest('.iu-tree-node');
				var nodeId = $node.data('id');

				if ( !nodeId || String(nodeId).indexOf('folder_') !== 0) {
					e.preventDefault();
					return;
				}

				// Prevent media drag handler from firing
				e.stopPropagation();

				// If dragging a node that is part of a multi-selection, drag all selected folders.
				// Otherwise drag only this node (even if other nodes are selected).
				var dragSet;
				if (self.selectedNodes && self.selectedNodes.has(nodeId) && self.selectedNodes.size > 1) {
					dragSet = Array.from(self.selectedNodes);
				} else {
					dragSet = [nodeId];
				}

				self.folderDragNodeIds = dragSet;
				self.dragIds = []; // Clear media drag IDs

				e.originalEvent.dataTransfer.effectAllowed = 'move';
				e.originalEvent.dataTransfer.setData('text/plain', 'folder:' + dragSet.join(','));

				// Suppress WP's upload overlay for folder drags (same as media drags).
				document.body.classList.add('iu-dragging-media');

				// Mark all dragged nodes
				dragSet.forEach(function (nid) {
					$('#iu-folders-tree .iu-tree-node[data-id="' + nid + '"]').addClass('iu-dragging');
				});
			});

			$(document).on('dragend', '#iu-folders-tree .iu-node-row[draggable]', function () {
				self.folderDragNodeIds = [];
				document.body.classList.remove('iu-dragging-media');
				$('#iu-folders-tree .iu-dragging').removeClass('iu-dragging');
				$('#iu-folders-tree .iu-drop-hover').removeClass('iu-drop-hover');
			});
		},

		collectDragIds: function ($el) {
			var ids = [];

			if (this.isListMode) {
				$('#the-list input[name="media[]"]:checked').each(function () {
					ids.push(parseInt($(this).val(), 10));
				});
				if ( !ids.length) {
					var rowId = $el.attr('id');
					if (rowId) {
						ids.push(parseInt(rowId.replace('post-', ''), 10));
					}
				}
			} else {
				ids = this.getSelectedAttachments();
				if ( !ids.length) {
					var dataId = $el.data('id');
					if (dataId) {
						ids.push(parseInt(dataId, 10));
					}
				}
			}

			return ids;
		},

		buildDragGhost: function ($el, count) {
			var ghost = document.createElement('div');
			ghost.className = 'iu-drag-helper';
			ghost.style.position = 'absolute';
			ghost.style.top = '-1000px';

			var img = $el.find('.thumbnail img, .media-icon img').first();
			if (img.length) {
				var clone = img[0].cloneNode(true);
				clone.style.width = '50px';
				clone.style.height = '50px';
				clone.style.objectFit = 'cover';
				ghost.appendChild(clone);
			} else {
				ghost.textContent = $el.find('.title a, .filename').first().text() || 'Media';
				ghost.className += ' iu-drag-helper-list';
			}

			if (count > 1) {
				var badge = document.createElement('span');
				badge.className = 'iu-drag-count';
				badge.textContent = count;
				ghost.appendChild(badge);
			}

			return ghost;
		},

		getSelectedAttachments: function () {
			var ids = [];

			if (typeof wp !== 'undefined' && wp.media && wp.media.frame) {
				try {
					var sel = wp.media.frame.state().get('selection');
					if (sel && sel.length) {
						sel.each(function (m) {
							ids.push(m.get('id'));
						});
					}
				} catch (err) { /* no selection */
				}
			}

			if ( !ids.length) {
				$('.attachment.selected, .attachment[aria-checked="true"]').each(function () {
					var id = $(this).data('id');
					if (id) ids.push(id);
				});
			}

			return ids;
		},

		// -----------------------------------------------------------------
		// Upload folder selector
		// -----------------------------------------------------------------

		/**
		 * Build <option> HTML for the upload folder <select>.
		 * Returns a flat, indented list reflecting the folder hierarchy.
		 */
		buildFolderOptions: function () {
			var self = this;
			var options = '<option value="">' + self.escHtml(iuMediaFolders.upload_folder_none) + '</option>';

			var childrenMap = {};
			var roots = [];

			for (var i = 0; i < this.folders.length; i++) {
				var f = this.folders[i];
				if (f.parent === '#') {
					roots.push(f);
				} else {
					if ( !childrenMap[f.parent]) childrenMap[f.parent] = [];
					childrenMap[f.parent].push(f);
				}
			}

			function addOptions(arr, depth) {
				for (var j = 0; j < arr.length; j++) {
					var folder = arr[j];
					var prefix = depth > 0 ? new Array(depth + 1).join('— ') : '';
					options += '<option value="' + folder.id + '">' + prefix + self.escHtml(folder.text) + '</option>';
					if (childrenMap[folder.id]) {
						addOptions(childrenMap[folder.id], depth + 1);
					}
				}
			}

			addOptions(roots, 0);
			return options;
		},

		/**
		 * Re-populate the upload folder <select> after tree data is refreshed.
		 * Preserves the previously selected value where possible.
		 */
		updateUploadFolderDropdown: function () {
			var $select = $('.iu-upload-folder-select');
			if ( !$select.length) return;

			var prevVal = $select.val() || this.uploadTargetFolder || '';
			$select.html(this.buildFolderOptions());

			// Restore previous selection if the folder still exists
			if (prevVal) {
				$select.val(prevVal);
				if ($select.val() !== prevVal) {
					// Folder no longer exists — reset
					this.uploadTargetFolder = null;
					localStorage.removeItem('iu_upload_folder');
				}
			}
		},

		/**
		 * Inject the standalone upload folder selector bar on media-new.php.
		 * Called after folders have been loaded (tree data is ready).
		 */
		injectUploadFolderSelector: function () {
			if ($('.iu-upload-folder-bar').length) return; // already injected

			var self = this;
			var html =
				'<div class="iu-upload-folder-bar">' +
				'<span class="dashicons dashicons-open-folder"></span>' +
				'<label for="iu-upload-folder-select-bar" class="iu-upload-folder-bar-label">' +
				iuMediaFolders.choose_folder +
				'</label>' +
				'<select id="iu-upload-folder-select-bar" class="iu-upload-folder-select">' +
				this.buildFolderOptions() +
				'</select>' +
				'</div>';

			var $target = $('#plupload-upload-ui, .upload-ui').first();
			if ($target.length) {
				$target.before(html);
			} else {
				$('.wrap').find('h1, h2').first().after(html);
			}

			// Restore persisted selection and sync to server so PHP hook is ready.
			if (self.uploadTargetFolder) {
				$('.iu-upload-folder-select').val(self.uploadTargetFolder);
				var folderId = parseInt(self.uploadTargetFolder.replace('folder_', ''), 10);
				$.post(iuMediaFolders.ajax_url, {
					action: 'iu_set_upload_folder',
					nonce: iuMediaFolders.nonce,
					folder_id: folderId || 0,
				});
			}
		},

		/**
		 * Hook into wp.Uploader.queue to auto-assign newly uploaded files
		 * to the selected folder.
		 */
		hookUploader: function () {
			var self = this;

			// Bind dropdown change — persist to localStorage AND server meta (for PHP add_attachment hook).
			$(document).on('change', '.iu-upload-folder-select', function () {
				var val = $(this).val() || '';
				self.uploadTargetFolder = val || null;

				if (val) {
					localStorage.setItem('iu_upload_folder', val);
				} else {
					localStorage.removeItem('iu_upload_folder');
				}

				// Strip 'folder_' prefix to get the integer ID for PHP.
				var folderId = val ? parseInt(val.replace('folder_', ''), 10) : 0;
				$.post(iuMediaFolders.ajax_url, {
					action: 'iu_set_upload_folder',
					nonce: iuMediaFolders.nonce,
					folder_id: folderId,
				});
			});

			var bindQueue = function () {
				if (typeof wp === 'undefined' || !wp.Uploader || !wp.Uploader.queue) return;

				// Guard against double-binding
				if (wp.Uploader.queue._iuBound) return;
				wp.Uploader.queue._iuBound = true;

				wp.Uploader.queue.on('add', function (attachment) {
					var folderId = self.uploadTargetFolder;
					if ( !folderId || folderId === '') return;

					// Immediately add the attachment to the current grid collection
					// so the upload progress tile is visible even in a filtered folder view.
					self.addAttachmentToGrid(attachment);

					var doMove = function () {
						var id = attachment.get('id');
						if (id) {
							self.moveMedia([id], folderId, { skipRefresh: true });
						}
					};

					if (attachment.get('id')) {
						doMove();
					} else {
						attachment.once('change:id', doMove);
					}
				});
			};

			// Try immediately (queue already exists on most pages).
			bindQueue();

			// Also try on ready (queue might not exist yet on media-new.php).
			$(document).ready(function () {
				bindQueue();
			});
		},

		/**
		 * Push an attachment model into the current grid collection so the
		 * upload progress tile is visible even in a folder-filtered view.
		 * WordPress's uploader can only add items to an unfiltered collection;
		 * for folder-filtered Query collections we have to do it manually.
		 */
		addAttachmentToGrid: function (attachment) {
			if (this.isListMode || typeof wp === 'undefined' || !wp.media || !wp.media.frame) {
				return;
			}
			try {
				var content = wp.media.frame.content && wp.media.frame.content.get();
				if (!content) return;
				var bc = content.collection || (content.options && content.options.collection);
				if (!bc) return;
				// Avoid duplicates — check by cid (no real id yet while uploading)
				var lookupKey = attachment.get('id') || attachment.cid;
				if (!bc.get(lookupKey)) {
					bc.unshift(attachment);
				}
			} catch (e) {}
		},

		/**
		 * Re-fetch the current folder's items from offset 0 and MERGE them
		 * into the existing collection (remove:false), so newly uploaded
		 * attachments appear without wiping the grid.
		 */
		softRefreshGrid: function () {
			if (this.isListMode || typeof wp === 'undefined' || !wp.media || !wp.media.frame) {
				return;
			}
			try {
				var content = wp.media.frame.content && wp.media.frame.content.get();
				if (!content) return;
				var bc = content.collection || (content.options && content.options.collection);
				if (!bc) return;
				// Setting _more to null bypasses the "all loaded" guard in more()
				// and makes sync() fetch from offset 0 (since falsy _more = no offset).
				bc._more = null;
				bc.more({ remove: false });
			} catch (e) {}
		},

		// -----------------------------------------------------------------
		// Sidebar loading indicator
		// -----------------------------------------------------------------

		showTreeLoader: function () {
			$('#iu-media-folders-sidebar').addClass('iu-loading');
			if (!$('#iu-media-folders-sidebar .iu-tree-spinner').length) {
				$('#iu-folders-tree').after('<span class="iu-tree-spinner"></span>');
			}
		},

		hideTreeLoader: function () {
			$('#iu-media-folders-sidebar').removeClass('iu-loading');
			$('.iu-tree-spinner').remove();
		},

		moveMedia: function (attachmentIds, folderId, options) {
			var self = this;
			options = options || {};

			if (folderId === 'all') return;

			var targetFolderId = folderId === 'uncategorized'
				? 0
				: parseInt(folderId.replace('folder_', ''), 10);

			self.showTreeLoader();
			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_move_media',
				nonce: iuMediaFolders.nonce,
				attachment_ids: attachmentIds,
				folder_id: targetFolderId,
			}, function (response) {
				self.hideTreeLoader();
				if (response.success) {
					self.updateCountsFromResponse(response.data);

					if (options.skipRefresh) {
						// Upload-triggered move: soft-refresh so the new item appears
						// in the folder view without wiping the grid mid-upload.
						self.softRefreshGrid();
					} else if (self.currentFolder && self.currentFolder !== 'all') {
						if (self.isListMode) {
							window.location.reload();
						} else {
							// Force a fresh re-query by bumping a timestamp prop.
							// filterGridMode() calls reset() + more() which clears the grid;
							// when iu_folder prop is unchanged Backbone fires no change event
							// so _requery never runs, leaving the grid permanently empty.
							self._forceGridRequery();
						}
					}
				}
			}, 'json');
		},
	};

	// Initialize when DOM is ready.
	$(document).ready(function () {
		IU_Folders.init();
		if ($('body').hasClass('upload-php')) {
		}
	});
})(jQuery);
