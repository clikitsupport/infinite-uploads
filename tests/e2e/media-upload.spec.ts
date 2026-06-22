/**
 * E2E: Media upload + auto-folder assignment.
 *
 * Verifies the on_add_attachment hook: when the user has selected an upload
 * folder via the bar on /wp-admin/media-new.php, new uploads should be
 * auto-assigned to that folder via the iu_upload_folder user meta.
 *
 * @package ClikIT\InfiniteUploads\Tests\E2E
 */

import * as path from 'path';
import { test, expect } from './helpers/test';

const FIXTURE_IMAGE = path.join( __dirname, 'fixtures/test-image.png' );

test.describe( 'Media upload', () => {

	test.beforeEach( async ( { iuApi } ) => {
		await iuApi.reset();
	} );

	test( 'uploads an image and it appears in the Media Library', async ( { page, adminPage } ) => {
		await adminPage.visit( '/media-new.php' );

		// The classic uploader exposes a hidden file input. Use Playwright's
		// setInputFiles which works regardless of visibility.
		const fileInput = page.locator( 'input[type="file"]' ).first();
		await fileInput.setInputFiles( FIXTURE_IMAGE );

		// Plupload reports completion by showing the filename + a "Done!" text or by
		// adding the row to .media-items. Wait for the upload to finish.
		await expect( page.locator( '.media-items' ).getByText( 'test-image' ) ).toBeVisible( {
			timeout: 30_000,
		} );

		// Confirm the attachment landed in the Media Library list table.
		await adminPage.visitMediaLibrary();
		await page.waitForSelector( '.wp-list-table.media, .attachments-browser', { timeout: 10_000 } );
		await expect( page.locator( 'body' ) ).toContainText( 'test-image' );
	} );

	test( 'auto-assigns the uploaded attachment to the selected upload folder', async ( {
		page,
		adminPage,
		iuApi,
	} ) => {
		const folder = await iuApi.createFolder( 'Auto-Upload Target' );

		// Set the user's upload folder preference via the test API — equivalent
		// to picking the folder from the .iu-upload-folder-bar select.
		await iuApi.setUploadFolder( folder.id );

		await adminPage.visit( '/media-new.php' );
		await page.locator( 'input[type="file"]' ).first().setInputFiles( FIXTURE_IMAGE );

		// Wait for the upload row to settle.
		await expect( page.locator( '.media-items' ).getByText( 'test-image' ) ).toBeVisible( {
			timeout: 30_000,
		} );

		// The upload row carries an edit link with the new attachment ID.
		const editLink = page.locator( '.media-items a[href*="post.php?post="]' ).first();
		await editLink.waitFor( { state: 'visible' } );
		const editHref = await editLink.getAttribute( 'href' );
		expect( editHref ).toMatch( /post=\d+/ );
		const attachmentId = Number( editHref!.match( /post=(\d+)/ )![ 1 ] );

		// The on_add_attachment hook should have inserted a relationship row.
		const assignedFolderId = await iuApi.getAttachmentFolder( attachmentId );
		expect( assignedFolderId ).toBe( folder.id );
	} );

	test( 'does NOT auto-assign when no upload folder is set', async ( { page, adminPage, iuApi } ) => {
		// No setUploadFolder call → user meta is empty.

		await adminPage.visit( '/media-new.php' );
		await page.locator( 'input[type="file"]' ).first().setInputFiles( FIXTURE_IMAGE );

		await expect( page.locator( '.media-items' ).getByText( 'test-image' ) ).toBeVisible( {
			timeout: 30_000,
		} );

		const editLink     = page.locator( '.media-items a[href*="post.php?post="]' ).first();
		await editLink.waitFor();
		const editHref     = await editLink.getAttribute( 'href' );
		const attachmentId = Number( editHref!.match( /post=(\d+)/ )![ 1 ] );

		expect( await iuApi.getAttachmentFolder( attachmentId ) ).toBeNull();
	} );
} );
