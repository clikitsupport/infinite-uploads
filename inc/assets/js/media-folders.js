/**
 * Infinite Uploads - Media Folders
 *
 * Adds a jstree sidebar to the WordPress Media Library for organizing
 * media into folders via a custom taxonomy. Uses native HTML5 Drag and
 * Drop API for moving media into folders.
 */
(function ($) {
	'use strict';

	var IU_Folders = {
		currentFolder: null,
		tree: null,
		isListMode: iuMediaFolders.is_list_mode,
		dragIds: [],
		sortMode: localStorage.getItem('iu_folders_sort') || 'custom',
		cutNode: null,
		totalCount: 0,
		uncategorizedCount: 0,
		folderCounts: {},

		init: function () {
			this.injectSidebar();
			this.initTree();
			this.initSearch();
			this.initResize();
			this.bindEvents();

			if (!this.isListMode) {
				this.hookGridMode();
			} else {
				this.hookListMode();
			}
		},

		/**
		 * Inject the sidebar HTML into the media library page.
		 */
		injectSidebar: function () {
			var collapsed = localStorage.getItem('iu_folders_collapsed') === '1';
			var savedWidth = localStorage.getItem('iu_folders_width');

			var sidebarStyle = savedWidth ? ' style="width:' + savedWidth + 'px;min-width:' + savedWidth + 'px"' : '';

			var sidebarHtml =
				'<div id="iu-media-folders-wrap" class="' + (collapsed ? 'iu-collapsed' : '') + '">' +
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
								'<button type="button" class="iu-sort-btn" title="' + iuMediaFolders.sort_az + '">' +
									'<span class="dashicons dashicons-sort"></span>' +
								'</button>' +
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
							'<span class="dashicons dashicons-open-folder iu-search-icon"></span>' +
							'<input type="text" class="iu-folder-search-input" placeholder="' + iuMediaFolders.search_folders + '" />' +
						'</div>' +
						// Folder tree (user folders only)
						'<div id="iu-folders-tree"></div>' +
					'</div>' +
					'<div class="iu-sidebar-resize-handle"></div>' +
					'<button type="button" class="iu-sidebar-toggle" title="Toggle folders">' +
						'<span class="dashicons dashicons-arrow-left-alt2"></span>' +
					'</button>' +
				'</div>';

			if (this.isListMode) {
				var $wrap = $('.wrap');
				if ($wrap.length) {
					var $pageTitle = $wrap.find('.page-title-action').last();
					var $content = $pageTitle.length ? $pageTitle.nextAll() : $wrap.children().not('h1');
					$content.wrapAll('<div class="iu-media-content"></div>');
					$wrap.find('.iu-media-content').first().before(sidebarHtml);
					$wrap.find('#iu-media-folders-wrap, .iu-media-content').wrapAll('<div class="iu-media-wrapper"></div>');
				}
			} else {
				var $mediaFrame = $('.media-frame');
				if ($mediaFrame.length) {
					$mediaFrame.addClass('iu-has-folders');
					$mediaFrame.prepend(sidebarHtml);
				}
			}
		},

		/**
		 * Initialize the jstree instance (user folders only).
		 */
		initTree: function () {
			var self = this;

			$('#iu-folders-tree').jstree({
				core: {
					data: function (node, callback) {
						self.loadFolders(callback);
					},
					check_callback: function (operation) {
						if (operation === 'move_node') {
							return true;
						}
						return true;
					},
					themes: {
						dots: true,
						icons: true,
						responsive: false,
					},
					multiple: false,
				},
				plugins: ['dnd', 'contextmenu', 'wholerow', 'search'],
				dnd: {
					is_draggable: function () {
						return true;
					},
					check_while_dragging: true,
				},
				contextmenu: {
					items: function (node) {
						return self.getContextMenu(node);
					},
				},
				search: {
					show_only_matches: true,
					show_only_matches_children: true,
					search_callback: function (str, node) {
						var folderName = (node.data && node.data.folder_name) || node.text || '';
						return folderName.toLowerCase().indexOf(str.toLowerCase()) !== -1;
					},
				},
			});

			this.tree = $('#iu-folders-tree').jstree(true);

			// When a jstree node is selected, deselect virtual folders.
			$('#iu-folders-tree').on('select_node.jstree', function (e, data) {
				self.deselectVirtualFolders();
				self.updateToolbarButtons(data.node);
				self.onFolderSelect(data.node.id);
			});

			$('#iu-folders-tree').on('move_node.jstree', function (e, data) {
				self.onFolderMove(data);
			});

			$('#iu-folders-tree').on('rename_node.jstree', function (e, data) {
				self.onFolderRename(data);
			});

			// Re-render count badges when tree redraws or nodes open.
			$('#iu-folders-tree').on('redraw.jstree open_node.jstree', function () {
				self.renderCountBadges();
			});

			// When tree is ready, update sort button and restore selection.
			$('#iu-folders-tree').on('ready.jstree', function () {
				self.updateSortButton();
			});
		},

		/**
		 * Load folders from the server (user folders only, no virtual nodes).
		 */
		loadFolders: function (callback) {
			var self = this;

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_get_folders',
				nonce: iuMediaFolders.nonce,
				sort: self.sortMode,
			}, function (response) {
				if (response.success) {
					var folders = response.data.folders || [];
					self.totalCount = response.data.total_count || 0;
					self.uncategorizedCount = response.data.uncategorized_count || 0;
					self.folderCounts = response.data.counts || {};

					// Update virtual folder badges.
					self.updateVirtualCounts();

					// Only user folders go into jstree.
					for (var i = 0; i < folders.length; i++) {
						folders[i].icon = 'dashicons dashicons-open-folder';
					}

					callback(folders);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// Virtual folder management
		// -----------------------------------------------------------------

		deselectVirtualFolders: function () {
			$('.iu-virtual-folder').removeClass('iu-vf-selected');
		},

		selectVirtualFolder: function (folder) {
			this.deselectVirtualFolders();
			$('.iu-virtual-folder[data-folder="' + folder + '"]').addClass('iu-vf-selected');
			// Deselect jstree nodes.
			if (this.tree) {
				this.tree.deselect_all(true);
			}
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

		getSelectedUserFolder: function () {
			if (!this.tree) return null;
			var sel = this.tree.get_selected(true);
			if (sel.length && sel[0].id.indexOf('folder_') === 0) {
				return sel[0];
			}
			return null;
		},

		updateToolbarButtons: function (node) {
			var isUserFolder = node && node.id && node.id.indexOf('folder_') === 0;
			$('.iu-action-rename').prop('disabled', !isUserFolder);
			$('.iu-action-delete').prop('disabled', !isUserFolder);
		},

		/**
		 * Render count badges on jstree nodes via DOM injection.
		 */
		renderCountBadges: function () {
			var self = this;

			$('#iu-folders-tree .jstree-anchor').each(function () {
				var $anchor = $(this);
				var nodeId = $anchor.closest('.jstree-node').attr('id');

				// Remove existing badge.
				$anchor.find('.iu-count-badge').remove();

				var folderId = parseInt(nodeId.replace('folder_', ''), 10);
				var count = self.folderCounts[folderId] || 0;

				$anchor.append('<span class="iu-count-badge">' + count + '</span>');
			});

			// Also update virtual folder counts.
			self.updateVirtualCounts();
		},

		/**
		 * Update counts from an AJAX response without full tree refresh.
		 */
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
					if (val.length > 0) {
						self.tree.search(val);
					} else {
						self.tree.clear_search();
					}
				}, 250);
			});
		},

		// -----------------------------------------------------------------
		// Sort
		// -----------------------------------------------------------------

		sortFolders: function () {
			if (this.sortMode === 'custom' || this.sortMode === 'za') {
				this.sortMode = 'az';
			} else if (this.sortMode === 'az') {
				this.sortMode = 'za';
			}
			localStorage.setItem('iu_folders_sort', this.sortMode);
			this.updateSortButton();
			this.tree.refresh();
		},

		updateSortButton: function () {
			var $btn = $('.iu-sort-btn');
			if (this.sortMode === 'az') {
				$btn.attr('title', iuMediaFolders.sort_za).addClass('iu-active');
			} else if (this.sortMode === 'za') {
				$btn.attr('title', iuMediaFolders.sort_az).addClass('iu-active');
			} else {
				$btn.attr('title', iuMediaFolders.sort_az).removeClass('iu-active');
			}
		},

		// -----------------------------------------------------------------
		// Expand / Collapse All
		// -----------------------------------------------------------------

		expandAll: function () {
			this.tree.open_all();
		},

		collapseAll: function () {
			this.tree.close_all();
		},

		// -----------------------------------------------------------------
		// Cut / Paste
		// -----------------------------------------------------------------

		cutFolder: function (node) {
			$('#iu-folders-tree .iu-cut').removeClass('iu-cut');
			this.cutNode = node;
			$('#' + node.id).addClass('iu-cut');
		},

		pasteFolder: function (targetNode) {
			var self = this;
			if (!this.cutNode) return;

			var termId = parseInt(this.cutNode.id.replace('folder_', ''), 10);
			var newParent = 0;

			if (targetNode && targetNode.id && targetNode.id.indexOf('folder_') === 0) {
				newParent = parseInt(targetNode.id.replace('folder_', ''), 10);
			}

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_move_folder',
				nonce: iuMediaFolders.nonce,
				term_id: termId,
				parent: newParent,
			}, function (response) {
				$('#iu-folders-tree .iu-cut').removeClass('iu-cut');
				self.cutNode = null;
				if (response.success) {
					self.tree.refresh();
				} else {
					alert(response.data);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// Resize sidebar
		// -----------------------------------------------------------------

		initResize: function () {
			var isResizing = false;
			var startX, startWidth;

			$(document).on('mousedown', '.iu-sidebar-resize-handle', function (e) {
				e.preventDefault();
				isResizing = true;
				startX = e.clientX;
				startWidth = $('#iu-media-folders-sidebar').outerWidth();
				$('body').addClass('iu-resizing');
			});

			$(document).on('mousemove', function (e) {
				if (!isResizing) return;
				var newWidth = startWidth + (e.clientX - startX);
				newWidth = Math.max(180, Math.min(500, newWidth));
				$('#iu-media-folders-sidebar').css({ width: newWidth, minWidth: newWidth });
			});

			$(document).on('mouseup', function () {
				if (!isResizing) return;
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
			}
		},

		filterListMode: function (folderId) {
			var url = new URL(window.location.href);
			var currentValue = url.searchParams.get('iu_folder');

			if (folderId === 'all') {
				if (!currentValue) return;
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

			var collection = wp.media.frame && wp.media.frame.content && wp.media.frame.content.get();
			if (!collection) return;

			var browserCollection = collection.collection || (collection.options && collection.options.collection);
			if (!browserCollection) return;

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
		// Folder tree operations (move / rename / create / delete)
		// -----------------------------------------------------------------

		onFolderMove: function (data) {
			var termId = parseInt(data.node.id.replace('folder_', ''), 10);
			var newParent = 0;

			if (data.parent && data.parent !== '#') {
				newParent = parseInt(data.parent.replace('folder_', ''), 10);
			}

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_move_folder',
				nonce: iuMediaFolders.nonce,
				term_id: termId,
				parent: newParent,
			}, function (response) {
				if (!response.success) {
					$('#iu-folders-tree').jstree(true).refresh();
				}
			}, 'json');
		},

		onFolderRename: function (data) {
			var nodeId = data.node.id;

			// New temp node (jstree assigns IDs starting with 'j').
			if (nodeId.indexOf('j') === 0) {
				this.createFolder(data.node, data.text);
				return;
			}

			var termId = parseInt(nodeId.replace('folder_', ''), 10);
			var name = data.text.trim();

			if (!name) {
				this.tree.refresh();
				return;
			}

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_rename_folder',
				nonce: iuMediaFolders.nonce,
				term_id: termId,
				name: name,
			}, function (response) {
				if (!response.success) {
					$('#iu-folders-tree').jstree(true).refresh();
				}
			}, 'json');
		},

		createFolder: function (node, name) {
			var self = this;
			var parentId = node.parent;
			var parent = 0;

			if (parentId && parentId !== '#') {
				parent = parseInt(parentId.replace('folder_', ''), 10);
			}

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_create_folder',
				nonce: iuMediaFolders.nonce,
				name: name,
				parent: parent,
			}, function (response) {
				if (response.success) {
					self.tree.set_id(node, response.data.id);
					self.tree.set_text(node, response.data.text);
					self.tree.set_icon(node, 'dashicons dashicons-open-folder');

					var nodeObj = self.tree.get_node(response.data.id);
					if (nodeObj && response.data.data) {
						nodeObj.data = response.data.data;
					}

					self.renderCountBadges();
				} else {
					self.tree.delete_node(node);
					alert(response.data);
				}
			}, 'json');
		},

		getContextMenu: function (node) {
			var self = this;
			var items = {};

			items.create = {
				label: iuMediaFolders.new_subfolder,
				icon: 'dashicons dashicons-plus-alt2',
				action: function () { self.addNewFolder(node.id); },
			};
			items.rename = {
				label: iuMediaFolders.rename,
				icon: 'dashicons dashicons-edit',
				action: function () {
					self.tree.edit(node, node.text.trim());
				},
			};
			items.cut = {
				label: iuMediaFolders.cut,
				icon: 'dashicons dashicons-clipboard',
				action: function () { self.cutFolder(node); },
			};
			if (self.cutNode && self.cutNode.id !== node.id) {
				items.paste = {
					label: iuMediaFolders.paste,
					icon: 'dashicons dashicons-clipboard',
					action: function () { self.pasteFolder(node); },
				};
			}
			items.separator = { separator_before: false, separator_after: true, label: '' };
			items.remove = {
				label: iuMediaFolders.delete,
				icon: 'dashicons dashicons-trash',
				action: function () {
					if (confirm(iuMediaFolders.confirm_delete)) {
						self.deleteFolder(node);
					}
				},
			};
			return items;
		},

		addNewFolder: function (parentId) {
			var self = this;
			var parent = parentId === '#' ? '#' : parentId;

			if (parent !== '#') {
				this.tree.open_node(parent);
			}

			var newNode = this.tree.create_node(parent, {
				text: iuMediaFolders.new_folder,
				icon: 'dashicons dashicons-open-folder',
			});

			if (newNode) {
				setTimeout(function () { self.tree.edit(newNode); }, 100);
			}
		},

		deleteFolder: function (node) {
			var self = this;
			var termId = parseInt(node.id.replace('folder_', ''), 10);

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_delete_folder',
				nonce: iuMediaFolders.nonce,
				term_id: termId,
			}, function (response) {
				if (response.success) {
					self.tree.delete_node(node);
					if (self.currentFolder === node.id) {
						self.selectVirtualFolder('all');
						self.onFolderSelect('all');
					}
				} else {
					alert(response.data);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// HTML5 Drag-and-Drop: media items -> folder tree
		// -----------------------------------------------------------------

		hookGridMode: function () {
			var self = this;

			// Patch wp.media.model.Query to pass iu_folder param.
			if (typeof wp !== 'undefined' && wp.media && wp.media.model) {
				var origSync = wp.media.model.Query.prototype.sync;
				wp.media.model.Query.prototype.sync = function (method, model, options) {
					var folder = this.props.get('iu_folder');
					if (folder) {
						options = options || {};
						options.data = options.data || {};
						options.data.iu_folder = folder;
					}
					return origSync.call(this, method, model, options);
				};
			}

			self.markDraggable();

			var debounce = null;
			var observer = new MutationObserver(function () {
				clearTimeout(debounce);
				debounce = setTimeout(function () { self.markDraggable(); }, 200);
			});

			var frame = document.querySelector('.media-frame');
			if (frame) {
				observer.observe(frame, { childList: true, subtree: true });
			}
		},

		hookListMode: function () {
			var self = this;

			var urlParams = new URLSearchParams(window.location.search);
			var currentFolderParam = urlParams.get('iu_folder');

			$('#iu-folders-tree').on('ready.jstree', function () {
				if (currentFolderParam === 'uncategorized') {
					self.selectVirtualFolder('uncategorized');
				} else if (currentFolderParam && currentFolderParam !== '') {
					self.tree.select_node('folder_' + currentFolderParam);
				} else {
					self.selectVirtualFolder('all');
				}
			});

			setTimeout(function () { self.markDraggable(); }, 300);
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
				self.addNewFolder('#');
			});

			// --- Toolbar Rename ---
			$(document).on('click', '.iu-action-rename', function (e) {
				e.preventDefault();
				var node = self.getSelectedUserFolder();
				if (node) {
					self.tree.edit(node, node.text.trim());
				}
			});

			// --- Toolbar Delete ---
			$(document).on('click', '.iu-action-delete', function (e) {
				e.preventDefault();
				var node = self.getSelectedUserFolder();
				if (node && confirm(iuMediaFolders.confirm_delete)) {
					self.deleteFolder(node);
				}
			});

			// --- Sort button ---
			$(document).on('click', '.iu-sort-btn', function (e) {
				e.preventDefault();
				self.sortFolders();
			});

			// --- More dropdown toggle ---
			$(document).on('click', '.iu-action-more', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var $menu = $(this).siblings('.iu-more-menu');
				$menu.toggleClass('iu-open');
			});

			// Close more dropdown on outside click.
			$(document).on('click', function (e) {
				if (!$(e.target).closest('.iu-more-dropdown').length) {
					$('.iu-more-menu').removeClass('iu-open');
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

			// --- Virtual folder drag-drop targets ---
			$(document)
				.on('dragover', '.iu-virtual-folder', function (e) {
					e.preventDefault();
					e.originalEvent.dataTransfer.dropEffect = 'move';
					$(this).addClass('iu-drop-hover');
				})
				.on('dragleave', '.iu-virtual-folder', function (e) {
					var related = e.originalEvent.relatedTarget;
					if (!this.contains(related)) {
						$(this).removeClass('iu-drop-hover');
					}
				})
				.on('drop', '.iu-virtual-folder', function (e) {
					e.preventDefault();
					e.stopPropagation();
					$(this).removeClass('iu-drop-hover');
					var folder = $(this).data('folder');
					var ids = self.dragIds.length ? self.dragIds : [];
					if (!ids.length) {
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
				var ids = self.collectDragIds($(this));
				if (!ids.length) {
					e.preventDefault();
					return;
				}

				self.dragIds = ids;

				e.originalEvent.dataTransfer.effectAllowed = 'move';
				e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify(ids));

				var ghost = self.buildDragGhost($(this), ids.length);
				document.body.appendChild(ghost);
				e.originalEvent.dataTransfer.setDragImage(ghost, 28, 28);
				setTimeout(function () { document.body.removeChild(ghost); }, 0);

				$('#iu-media-folders-wrap').removeClass('iu-collapsed');
				$('#iu-media-folders-sidebar').addClass('iu-drop-active');
			});

			$(document).on('dragend', '.attachment[draggable], #the-list tr[draggable]', function () {
				self.dragIds = [];
				$('#iu-media-folders-sidebar').removeClass('iu-drop-active');
				$('#iu-folders-tree .iu-drop-hover').removeClass('iu-drop-hover');
				$('.iu-virtual-folder.iu-drop-hover').removeClass('iu-drop-hover');
			});

			// --- Drop targets: jstree anchor rows (delegated) ---
			$('#iu-folders-tree')
				.on('dragover', '.jstree-anchor', function (e) {
					e.preventDefault();
					e.originalEvent.dataTransfer.dropEffect = 'move';
					$(this).closest('.jstree-node').addClass('iu-drop-hover');
				})
				.on('dragleave', '.jstree-anchor', function (e) {
					var related = e.originalEvent.relatedTarget;
					var node = $(this).closest('.jstree-node')[0];
					if (!node || !node.contains(related)) {
						$(this).closest('.jstree-node').removeClass('iu-drop-hover');
					}
				})
				.on('drop', '.jstree-anchor', function (e) {
					e.preventDefault();
					e.stopPropagation();

					var $node = $(this).closest('.jstree-node');
					$node.removeClass('iu-drop-hover');

					var folderId = $node.attr('id');
					var ids = self.dragIds.length ? self.dragIds : [];

					if (!ids.length) {
						try {
							ids = JSON.parse(e.originalEvent.dataTransfer.getData('text/plain'));
						} catch (err) {
							ids = [];
						}
					}

					if (ids.length && folderId) {
						self.moveMedia(ids, folderId);
					}
				});
		},

		collectDragIds: function ($el) {
			var ids = [];

			if (this.isListMode) {
				$('#the-list input[name="media[]"]:checked').each(function () {
					ids.push(parseInt($(this).val(), 10));
				});
				if (!ids.length) {
					var rowId = $el.attr('id');
					if (rowId) {
						ids.push(parseInt(rowId.replace('post-', ''), 10));
					}
				}
			} else {
				ids = this.getSelectedAttachments();
				if (!ids.length) {
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
						sel.each(function (m) { ids.push(m.get('id')); });
					}
				} catch (err) { /* no selection */ }
			}

			if (!ids.length) {
				$('.attachment.selected, .attachment[aria-checked="true"]').each(function () {
					var id = $(this).data('id');
					if (id) ids.push(id);
				});
			}

			return ids;
		},

		moveMedia: function (attachmentIds, folderId) {
			var self = this;

			if (folderId === 'all') return;

			var targetFolderId = folderId === 'uncategorized'
				? 0
				: parseInt(folderId.replace('folder_', ''), 10);

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_move_media',
				nonce: iuMediaFolders.nonce,
				attachment_ids: attachmentIds,
				folder_id: targetFolderId,
			}, function (response) {
				if (response.success) {
					self.updateCountsFromResponse(response.data);

					if (self.currentFolder && self.currentFolder !== 'all') {
						if (self.isListMode) {
							window.location.reload();
						} else {
							self.filterGridMode(self.currentFolder);
						}
					}
				}
			}, 'json');
		},
	};

	// Initialize when DOM is ready.
	$(document).ready(function () {
		if ($('body').hasClass('upload-php')) {
			IU_Folders.init();
		}
	});
})(jQuery);
