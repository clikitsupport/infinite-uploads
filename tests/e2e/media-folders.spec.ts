/**
 * E2E: Media Folders — CRUD operations through the admin UI.
 *
 * Drives the real folder sidebar in /wp-admin/upload.php and verifies the
 * resulting DB state via the test-helpers REST endpoints.
 *
 * Selectors are derived from inc/assets/js/media-folders.js. Notes:
 *   - The sidebar wrapper is `#iu-media-folders-sidebar`, the tree is
 *     `#iu-folders-tree`.
 *   - The UI does NOT use jstree at runtime (the source comment says
 *     "Replaces jstree with plain jQuery + HTML for a lighter footprint").
 *   - Folder nodes are `li.iu-tree-node[data-id="folder_<id>"]`.
 *   - "New Folder" uses an inline editable input (not a window.prompt) on a
 *     temporary node whose data-id starts with `iu_temp_`.
 *   - The context menu is custom (`.iu-context-menu` / `.iu-context-menu-item`),
 *     opened by right-clicking on a node row.
 *
 * @package ClikIT\InfiniteUploads\Tests\E2E
 */

import { test, expect } from './helpers/test';

test.describe( 'Media Folders — admin UI CRUD', () => {

	test.beforeEach( async ( { iuApi } ) => {
		await iuApi.reset();
		// MediaFolders only initializes when iup_apitoken is present —
		// see InfiniteUploadsHelper::is_media_folders_enabled(). On a real
		// connection (typical local dev) this is a no-op; on CI with no
		// real token it seeds fake credentials so the folder sidebar
		// actually renders.
		await iuApi.connect();
	} );

	test( 'creates a new folder via the toolbar button', async ( { page, adminPage, iuApi } ) => {
		await adminPage.visitMediaLibrary();
		await adminPage.ensureFolderSidebarOpen();

		// Surface any alert() the plugin fires on AJAX failure — without
		// this, alerts are auto-dismissed by Playwright and we lose the
		// diagnostic.
		const alertMessages: string[] = [];
		page.on( 'dialog', async ( dialog ) => {
			alertMessages.push( `[${ dialog.type() }] ${ dialog.message() }` );
			await dialog.dismiss();
		} );

		// Click the toolbar "New Folder" button → inserts an inline editable
		// temp node (data-id `iu_temp_*`) with an `.iu-rename-input`
		// pre-filled with the localized default ("New Folder"), selected.
		await page.locator( '.iu-folder-add-btn' ).first().click();

		const tempInput = page.locator(
			'#iu-folders-tree li.iu-tree-node[data-id^="iu_temp_"] input.iu-rename-input'
		).first();
		await tempInput.waitFor( { state: 'visible', timeout: 5_000 } );

		// IMPORTANT: any focus-stealing interaction (a fill that clicks
		// elsewhere first, a click outside the input) would fire the input's
		// blur handler with the default name. Use the focused-input keyboard
		// path: select-all → type, no intermediate clicks.
		await tempInput.focus();
		await page.keyboard.press( 'ControlOrMeta+A' );
		await page.keyboard.type( 'Playwright Test Folder' );
		await expect( tempInput ).toHaveValue( 'Playwright Test Folder' );

		// Wait for the iu_create_folder AJAX explicitly — more reliable than
		// polling the DOM for the data-id swap. Commit via blur (not Enter)
		// — the blur handler does the same finishCreate with no risk of
		// bubbling to wp.media's frame router.
		const responsePromise = page.waitForResponse(
			( resp ) =>
				resp.url().includes( 'admin-ajax.php' ) &&
				( resp.request().postData() || '' ).includes( 'action=iu_create_folder' ),
			{ timeout: 10_000 },
		);
		await tempInput.evaluate( ( el: HTMLInputElement ) => el.blur() );
		const ajaxResponse = await responsePromise;
		expect( ajaxResponse.status() ).toBe( 200 );
		expect( ( await ajaxResponse.json() ).success ).toBe( true );

		// AJAX done → the temp node now carries a real folder_<id>.
		await expect( page.locator(
			'#iu-folders-tree li.iu-tree-node[data-id^="folder_"] .iu-node-text'
		).filter( { hasText: 'Playwright Test Folder' } ) ).toBeVisible( { timeout: 5_000 } );

		expect( await iuApi.folderCount() ).toBe( 1 );
		expect( alertMessages, 'no alerts should have been raised' ).toEqual( [] );
	} );

	test( 'renames a folder via the toolbar Rename action', async ( { page, adminPage, iuApi } ) => {
		const folder = await iuApi.createFolder( 'Original Name' );

		await adminPage.visitMediaLibrary();
		await adminPage.ensureFolderSidebarOpen();
		await page.waitForSelector( '#iu-folders-tree', { state: 'attached' } );

		const folderNode = page.locator(
			`#iu-folders-tree li.iu-tree-node[data-id="folder_${ folder.id }"]`
		);
		await expect( folderNode.locator( '.iu-node-text' ) ).toContainText( 'Original Name' );

		// Click the row to select it (Rename/Delete toolbar buttons are
		// disabled until a node is selected).
		await folderNode.locator( '.iu-node-row' ).first().click();

		// Toolbar Rename → swaps the node-text span for an inline input.
		await page.locator( '.iu-action-rename' ).click();
		const renameInput = folderNode.locator( 'input' ).first();
		await renameInput.waitFor( { state: 'visible' } );
		await renameInput.fill( 'Renamed Folder' );
		await renameInput.press( 'Enter' );

		// Wait for the inline input to disappear (signals the AJAX save
		// finished and the .iu-node-text was restored with the new name).
		await expect( folderNode.locator( 'input' ) ).toHaveCount( 0, { timeout: 10_000 } );
		await expect( folderNode.locator( '.iu-node-text' ) ).toContainText( 'Renamed Folder' );
		await expect( folderNode.locator( '.iu-node-text' ) ).not.toContainText( 'Original Name' );

		expect( await iuApi.folderCount() ).toBe( 1 );
	} );

	test( 'deletes a folder via the context menu', async ( { page, adminPage, iuApi } ) => {
		const folder = await iuApi.createFolder( 'Folder To Delete' );

		await adminPage.visitMediaLibrary();
		await adminPage.ensureFolderSidebarOpen();
		await page.waitForSelector( '#iu-folders-tree', { state: 'attached' } );

		const folderRow = page.locator(
			`#iu-folders-tree li.iu-tree-node[data-id="folder_${ folder.id }"] .iu-node-row`
		).first();

		// Auto-accept the JS confirm() dialog that fires before deletion.
		page.once( 'dialog', dialog => dialog.accept() );

		// Right-click → custom context menu → Delete item.
		await folderRow.click( { button: 'right' } );
		await page.locator( '.iu-context-menu .iu-context-menu-item[data-action="delete"]' ).click();

		// Node should disappear from the DOM.
		await expect( page.locator(
			`#iu-folders-tree li.iu-tree-node[data-id="folder_${ folder.id }"]`
		) ).toHaveCount( 0 );

		expect( await iuApi.folderCount() ).toBe( 0 );
	} );

	test( 'creates a nested child folder via the context menu', async ( { page, adminPage, iuApi } ) => {
		const parent = await iuApi.createFolder( 'Parent' );

		await adminPage.visitMediaLibrary();
		await adminPage.ensureFolderSidebarOpen();
		await page.waitForSelector( '#iu-folders-tree', { state: 'attached' } );

		const parentRow = page.locator(
			`#iu-folders-tree li.iu-tree-node[data-id="folder_${ parent.id }"] .iu-node-row`
		).first();

		// Right-click parent → New Subfolder.
		await parentRow.click( { button: 'right' } );
		await page.locator( '.iu-context-menu .iu-context-menu-item[data-action="create"]' ).click();

		// A temp node appears under the parent with an inline input.
		const tempInput = page.locator(
			`#iu-folders-tree li.iu-tree-node[data-id="folder_${ parent.id }"] ` +
			`li.iu-tree-node[data-id^="iu_temp_"] input`
		).first();
		await tempInput.waitFor( { state: 'visible', timeout: 5_000 } );
		await tempInput.fill( 'Child' );
		await tempInput.press( 'Enter' );

		// Wait for the AJAX create to complete: temp node replaced by a real
		// folder_<id> node with the child name.
		await expect( page.locator(
			'#iu-folders-tree li.iu-tree-node[data-id^="folder_"] .iu-node-text'
		).filter( { hasText: 'Child' } ) ).toBeVisible( { timeout: 10_000 } );
		await expect( page.locator(
			'#iu-folders-tree li.iu-tree-node[data-id^="iu_temp_"]'
		) ).toHaveCount( 0 );

		expect( await iuApi.folderCount() ).toBe( 2 );
	} );

	test( 'selecting a folder marks it as selected in the tree', async ( { page, adminPage, iuApi } ) => {
		const folder = await iuApi.createFolder( 'My Folder' );

		await adminPage.visitMediaLibrary();
		await adminPage.ensureFolderSidebarOpen();
		await page.waitForSelector( '#iu-folders-tree', { state: 'attached' } );

		await page.locator(
			`#iu-folders-tree li.iu-tree-node[data-id="folder_${ folder.id }"] .iu-node-row`
		).first().click();

		// The plugin applies `.iu-selected` to the selected row.
		await expect( page.locator(
			`#iu-folders-tree li.iu-tree-node[data-id="folder_${ folder.id }"] .iu-node-row.iu-selected`
		) ).toBeVisible();
	} );

	test( 'bulk-deletes multiple folders via ctrl-click + toolbar Delete', async ( {
		page,
		adminPage,
		iuApi,
	} ) => {
		await iuApi.createFolder( 'Folder A' );
		await iuApi.createFolder( 'Folder B' );
		await iuApi.createFolder( 'Folder C' );
		expect( await iuApi.folderCount() ).toBe( 3 );

		await adminPage.visitMediaLibrary();
		await adminPage.ensureFolderSidebarOpen();
		await page.waitForSelector( '#iu-folders-tree', { state: 'attached' } );

		// Ctrl-click each folder row to multi-select. The plugin uses
		// `.iu-multi-selected` class for additional selections after the
		// first `.iu-selected`.
		const rows = page.locator( '#iu-folders-tree li.iu-tree-node .iu-node-row' );
		await rows.nth( 0 ).click();
		await rows.nth( 1 ).click( { modifiers: [ 'Meta' ] } );
		await rows.nth( 2 ).click( { modifiers: [ 'Meta' ] } );

		// Auto-accept the bulk-delete confirmation dialog.
		page.once( 'dialog', dialog => dialog.accept() );

		// Use the toolbar Delete action.
		await page.locator( '.iu-action-delete' ).click();

		// All three folders should be removed from the tree + the DB.
		await expect( page.locator( '#iu-folders-tree .iu-node-text' ).filter( {
			hasText: 'Folder A',
		} ) ).toHaveCount( 0 );
		await expect( page.locator( '#iu-folders-tree .iu-node-text' ).filter( {
			hasText: 'Folder B',
		} ) ).toHaveCount( 0 );
		await expect( page.locator( '#iu-folders-tree .iu-node-text' ).filter( {
			hasText: 'Folder C',
		} ) ).toHaveCount( 0 );

		expect( await iuApi.folderCount() ).toBe( 0 );
	} );
} );
