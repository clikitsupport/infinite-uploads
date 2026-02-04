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

		init: function () {
			this.injectSidebar();
			this.initTree();
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

			var sidebarHtml =
				'<div id="iu-media-folders-wrap" class="' + (collapsed ? 'iu-collapsed' : '') + '">' +
					'<button type="button" class="iu-sidebar-toggle" title="Toggle folders">' +
						'<span class="dashicons dashicons-arrow-right-alt2"></span>' +
					'</button>' +
					'<div id="iu-media-folders-sidebar">' +
						'<div class="iu-folders-header">' +
							'<span class="iu-folders-title">' + iuMediaFolders.all_label + '</span>' +
							'<button type="button" class="iu-folder-add-btn" title="' + iuMediaFolders.new_folder + '">' +
								'<span class="dashicons dashicons-plus-alt2"></span>' +
							'</button>' +
						'</div>' +
						'<div id="iu-folders-tree"></div>' +
					'</div>' +
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
		 * Initialize the jstree instance.
		 */
		initTree: function () {
			var self = this;

			$('#iu-folders-tree').jstree({
				core: {
					data: function (node, callback) {
						self.loadFolders(callback);
					},
					check_callback: true,
					themes: {
						dots: false,
						icons: true,
						responsive: false,
					},
					multiple: false,
				},
				plugins: ['dnd', 'contextmenu', 'wholerow'],
				dnd: {
					is_draggable: function (nodes) {
						for (var i = 0; i < nodes.length; i++) {
							if (nodes[i].id === 'all' || nodes[i].id === 'uncategorized') {
								return false;
							}
						}
						return true;
					},
					check_while_dragging: true,
				},
				contextmenu: {
					items: function (node) {
						return self.getContextMenu(node);
					},
				},
			});

			this.tree = $('#iu-folders-tree').jstree(true);

			$('#iu-folders-tree').on('select_node.jstree', function (e, data) {
				self.onFolderSelect(data.node);
			});

			$('#iu-folders-tree').on('move_node.jstree', function (e, data) {
				self.onFolderMove(data);
			});

			$('#iu-folders-tree').on('rename_node.jstree', function (e, data) {
				self.onFolderRename(data);
			});
		},

		/**
		 * Load folders from the server.
		 */
		loadFolders: function (callback) {
			var self = this;

			$.post(iuMediaFolders.ajax_url, {
				action: 'iu_get_folders',
				nonce: iuMediaFolders.nonce,
			}, function (response) {
				if (response.success) {
					var data = [
						{
							id: 'all',
							text: iuMediaFolders.all_label,
							parent: '#',
							icon: 'dashicons dashicons-media-default',
							state: { selected: !self.currentFolder, opened: true },
							li_attr: { class: 'iu-folder-virtual' },
						},
						{
							id: 'uncategorized',
							text: iuMediaFolders.uncat_label,
							parent: '#',
							icon: 'dashicons dashicons-category',
							li_attr: { class: 'iu-folder-virtual' },
						},
					];

					for (var i = 0; i < response.data.length; i++) {
						response.data[i].icon = 'dashicons dashicons-open-folder';
						data.push(response.data[i]);
					}

					callback(data);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// Folder selection / filtering
		// -----------------------------------------------------------------

		onFolderSelect: function (node) {
			this.currentFolder = node.id;

			if (this.isListMode) {
				this.filterListMode(node.id);
			} else {
				this.filterGridMode(node.id);
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

			if (data.parent && data.parent !== '#' && data.parent !== 'all') {
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

			if (nodeId === 'all' || nodeId === 'uncategorized') return;

			if (nodeId.indexOf('j') === 0) {
				this.createFolder(data.node, data.text);
				return;
			}

			var termId = parseInt(nodeId.replace('folder_', ''), 10);
			var name = data.text.replace(/<span[^>]*>.*?<\/span>/gi, '').trim();

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

			if (parentId && parentId !== '#' && parentId !== 'all' && parentId !== 'uncategorized') {
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
				} else {
					self.tree.delete_node(node);
					alert(response.data);
				}
			}, 'json');
		},

		getContextMenu: function (node) {
			var self = this;
			var items = {};

			if (node.id === 'all') {
				items.create = {
					label: iuMediaFolders.new_folder,
					icon: 'dashicons dashicons-plus-alt2',
					action: function () { self.addNewFolder('#'); },
				};
				return items;
			}

			if (node.id === 'uncategorized') return {};

			items.create = {
				label: iuMediaFolders.new_folder,
				icon: 'dashicons dashicons-plus-alt2',
				action: function () { self.addNewFolder(node.id); },
			};
			items.rename = {
				label: iuMediaFolders.rename,
				icon: 'dashicons dashicons-edit',
				action: function () {
					self.tree.edit(node, node.text.replace(/<span[^>]*>.*?<\/span>/gi, '').trim());
				},
			};
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
						self.currentFolder = null;
						self.tree.select_node('all');
					}
				} else {
					alert(response.data);
				}
			}, 'json');
		},

		// -----------------------------------------------------------------
		// HTML5 Drag-and-Drop: media items → folder tree
		// -----------------------------------------------------------------

		/**
		 * Hook grid mode – make attachment thumbnails draggable via the
		 * native HTML5 drag API and observe DOM for new items.
		 */
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

			// Mark existing items draggable.
			self.markDraggable();

			// Use MutationObserver to catch dynamically added attachments.
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

		/**
		 * Hook list mode – make table rows draggable via the
		 * native HTML5 drag API.
		 */
		hookListMode: function () {
			var self = this;

			// Restore selected folder from URL.
			var urlParams = new URLSearchParams(window.location.search);
			var currentFolderParam = urlParams.get('iu_folder');

			$('#iu-folders-tree').on('ready.jstree', function () {
				if (currentFolderParam === 'uncategorized') {
					self.tree.select_node('uncategorized');
				} else if (currentFolderParam && currentFolderParam !== '') {
					self.tree.select_node('folder_' + currentFolderParam);
				} else {
					self.tree.select_node('all');
				}
			});

			// Mark rows draggable once DOM is settled.
			setTimeout(function () { self.markDraggable(); }, 300);
		},

		/**
		 * Set draggable="true" on media items that don't have it yet.
		 */
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

		/**
		 * Bind all UI events – called once during init.
		 */
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

			// --- Native dragstart on media items (delegated) ---
			$(document).on('dragstart', '.attachment[draggable], #the-list tr[draggable]', function (e) {
				var ids = self.collectDragIds($(this));
				if (!ids.length) {
					e.preventDefault();
					return;
				}

				self.dragIds = ids;

				// dataTransfer payload (needed for the drag to register).
				e.originalEvent.dataTransfer.effectAllowed = 'move';
				e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify(ids));

				// Build a small drag image.
				var ghost = self.buildDragGhost($(this), ids.length);
				document.body.appendChild(ghost);
				e.originalEvent.dataTransfer.setDragImage(ghost, 28, 28);
				setTimeout(function () { document.body.removeChild(ghost); }, 0);

				// Visual cues.
				$('#iu-media-folders-wrap').removeClass('iu-collapsed');
				$('#iu-media-folders-sidebar').addClass('iu-drop-active');
			});

			$(document).on('dragend', '.attachment[draggable], #the-list tr[draggable]', function () {
				self.dragIds = [];
				$('#iu-media-folders-sidebar').removeClass('iu-drop-active');
				$('#iu-folders-tree .iu-drop-hover').removeClass('iu-drop-hover');
			});

			// --- Drop targets: jstree anchor rows (delegated) ---
			$('#iu-folders-tree')
				.on('dragover', '.jstree-anchor', function (e) {
					e.preventDefault();
					e.originalEvent.dataTransfer.dropEffect = 'move';
					$(this).closest('.jstree-node').addClass('iu-drop-hover');
				})
				.on('dragleave', '.jstree-anchor', function (e) {
					// Only remove if we actually left this node.
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

					// Fallback: try dataTransfer.
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

		/**
		 * Collect attachment IDs for the current drag operation.
		 * Prefers multi-selection; falls back to the single dragged item.
		 */
		collectDragIds: function ($el) {
			var ids = [];

			if (this.isListMode) {
				// List mode: checked checkboxes first.
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
				// Grid mode: wp.media selection first.
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

		/**
		 * Build an off-screen ghost element used as the drag image.
		 */
		buildDragGhost: function ($el, count) {
			var ghost = document.createElement('div');
			ghost.className = 'iu-drag-helper';
			ghost.style.position = 'absolute';
			ghost.style.top = '-1000px';

			// Try to grab a thumbnail.
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

		/**
		 * Get selected attachment IDs in grid mode via wp.media.
		 */
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

		/**
		 * Move media items to a folder via AJAX.
		 */
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
					self.tree.refresh();

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
