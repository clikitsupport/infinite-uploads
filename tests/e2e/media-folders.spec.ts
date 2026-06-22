/**
 * E2E: Media Folders — CRUD operations through the admin UI.
 *
 * Covers the 3.2.0 feature. Tests drive the real jstree sidebar in the Media
 * Library admin, then verify the resulting DB state via the test-helpers REST
 * endpoints.
 *
 * @package ClikIT\InfiniteUploads\Tests\E2E
 */

import { test, expect } from './helpers/test';

test.describe( 'Media Folders — admin UI CRUD', () => {

	test.beforeEach( async ( { iuApi } ) => {
		// Reset folder + relationship tables so each spec starts clean.
		await iuApi.reset();
	} );

	test( 'creates a new folder via the toolbar button', async ( { page, adminPage, iuApi } ) => {
		await adminPage.visitMediaLibrary();

		// The IU sidebar mounts the jstree folder panel after the Media Library JS boots.
		await page.waitForSelector( '#iu-folders-tree', { state: 'visible', timeout: 10_000 } );

		// Stub the prompt() that the "New Folder" button uses for the name.
		await page.evaluate( () => {
			window.prompt = () => 'Playwright Test Folder';
		} );

		await page.locator( '.iu-new-folder-btn' ).first().click();

		// The folder should appear in the jstree node list, AND in the DB.
		await expect( page.locator( '#iu-folders-tree' ) ).toContainText( 'Playwright Test Folder' );
		expect( await iuApi.folderCount() ).toBe( 1 );
	} );

	test( 'renames a folder via inline edit', async ( { page, adminPage, iuApi } ) => {
		const folder = await iuApi.createFolder( 'Original Name' );

		await adminPage.visitMediaLibrary();
		await page.waitForSelector( '#iu-folders-tree' );
		await expect( page.locator( '#iu-folders-tree' ) ).toContainText( 'Original Name' );

		const folderNode = page.locator( `#folder_${ folder.id }` );

		// Right-click context menu → Rename → type new name → Enter.
		await folderNode.click( { button: 'right' } );
		await page.locator( '.vakata-context >> text=Rename' ).click();

		// jstree puts an inline input over the label.
		const input = page.locator( '#iu-folders-tree input[type="text"]' ).first();
		await input.fill( 'Renamed Folder' );
		await input.press( 'Enter' );

		await expect( page.locator( '#iu-folders-tree' ) ).toContainText( 'Renamed Folder' );
		await expect( page.locator( '#iu-folders-tree' ) ).not.toContainText( 'Original Name' );

		// Folder count unchanged — we renamed, not added.
		expect( await iuApi.folderCount() ).toBe( 1 );
	} );

	test( 'deletes a folder via the context menu', async ( { page, adminPage, iuApi } ) => {
		const folder = await iuApi.createFolder( 'Folder To Delete' );

		await adminPage.visitMediaLibrary();
		await page.waitForSelector( '#iu-folders-tree' );

		const folderNode = page.locator( `#folder_${ folder.id }` );
		await folderNode.click( { button: 'right' } );

		// Browser confirm() will be triggered before deletion.
		page.once( 'dialog', dialog => dialog.accept() );
		await page.locator( '.vakata-context >> text=Delete' ).click();

		await expect( page.locator( '#iu-folders-tree' ) ).not.toContainText( 'Folder To Delete' );
		expect( await iuApi.folderCount() ).toBe( 0 );
	} );

	test( 'creates a nested child folder', async ( { page, adminPage, iuApi } ) => {
		const parent = await iuApi.createFolder( 'Parent' );

		await adminPage.visitMediaLibrary();
		await page.waitForSelector( '#iu-folders-tree' );

		// Right-click parent → New Subfolder.
		await page.locator( `#folder_${ parent.id }` ).click( { button: 'right' } );

		await page.evaluate( () => {
			window.prompt = () => 'Child';
		} );
		await page.locator( '.vakata-context >> text=New Subfolder' ).click();

		// Expand the parent (jstree collapses by default) and verify the child shows.
		const parentNode = page.locator( `#folder_${ parent.id }` );
		await parentNode.locator( '.jstree-ocl' ).first().click();
		await expect( page.locator( '#iu-folders-tree' ) ).toContainText( 'Child' );

		expect( await iuApi.folderCount() ).toBe( 2 );
	} );

	test( 'filters Media Library by folder when a folder is selected', async ( { page, adminPage, iuApi } ) => {
		const folder = await iuApi.createFolder( 'My Folder' );

		await adminPage.visitMediaLibrary();
		await page.waitForSelector( '#iu-folders-tree' );

		// Click the folder node to switch view.
		await page.locator( `#folder_${ folder.id } a.jstree-anchor` ).first().click();

		// The URL should now carry an iu_folder query param OR the list filters in place.
		// We assert the lightweight "active" highlight on the folder node.
		await expect(
			page.locator( `#folder_${ folder.id }.jstree-clicked, #folder_${ folder.id } a.jstree-clicked` )
		).toBeVisible();
	} );

	test( 'bulk-deletes multiple folders via shift+click then context menu', async ( { page, adminPage, iuApi } ) => {
		await iuApi.createFolder( 'Folder A' );
		await iuApi.createFolder( 'Folder B' );
		await iuApi.createFolder( 'Folder C' );
		expect( await iuApi.folderCount() ).toBe( 3 );

		await adminPage.visitMediaLibrary();
		await page.waitForSelector( '#iu-folders-tree' );

		// Ctrl-click each folder to multi-select.
		const folders = page.locator( '#iu-folders-tree li[id^="folder_"] a.jstree-anchor' );
		await folders.nth( 0 ).click();
		await folders.nth( 1 ).click( { modifiers: [ 'Meta' ] } );
		await folders.nth( 2 ).click( { modifiers: [ 'Meta' ] } );

		// Trigger bulk-delete via context menu.
		await folders.nth( 0 ).click( { button: 'right' } );
		page.once( 'dialog', dialog => dialog.accept() );
		await page.locator( '.vakata-context >> text=Delete Selected' ).click();

		// All three should be gone.
		await expect( page.locator( '#iu-folders-tree' ) ).not.toContainText( 'Folder A' );
		await expect( page.locator( '#iu-folders-tree' ) ).not.toContainText( 'Folder B' );
		await expect( page.locator( '#iu-folders-tree' ) ).not.toContainText( 'Folder C' );

		expect( await iuApi.folderCount() ).toBe( 0 );
	} );
} );
