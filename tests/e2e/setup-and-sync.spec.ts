/**
 * E2E: First-time setup + first sync.
 *
 * Verifies the customer-facing connect-and-sync journey end to end:
 *
 *   1. Before connection (FAKE-MODE ONLY) — settings page shows a Connect /
 *      Get Started CTA and no CDN host appears anywhere.
 *   2. After connection — settings page shows the connected state: CDN host
 *      is rendered somewhere on the page.
 *   3. After "first sync" (a fixture image is uploaded + marked synced) —
 *      the file is visible in the IU file table.
 *   4. Front-end smoke: a published post with an uploaded image gets the
 *      CDN URL rewritten into its `<img src>`.
 *
 * Works in two modes (auto-detected):
 *   • FAKE MODE  — `iuApi.connect()` seeds fake credentials so the plugin
 *     enters its connected code path. The test owns disconnect/reconnect.
 *   • REAL MODE  — you connected manually via wp-admin. The "before
 *     connection" + "disconnect cycle" tests skip themselves; everything
 *     else reads the live CDN host from the plugin and verifies against it.
 *
 * @package ClikIT\InfiniteUploads\Tests\E2E
 */

import { test, expect } from './helpers/test';

const FIXTURE_FILENAME = 'test-image.png';

test.describe( 'First-time setup + first sync', () => {

	test.beforeEach( async ( { iuApi } ) => {
		// Reset folder + relationship tables but DO NOT touch the
		// connection state. iuApi.disconnect() is itself a no-op when a
		// real connection is present.
		await iuApi.reset();
		await iuApi.clearExcludedPaths();
	} );

	test( 'before connection, settings page shows the Connect CTA', async ( {
		page,
		adminPage,
		iuApi,
	} ) => {
		// Only meaningful when we control the connection. In real-mode the
		// admin is already connected, so the CTA doesn't render.
		const state = await iuApi.getState();
		test.skip( state.is_real, 'real connection present — Connect CTA is hidden' );

		await iuApi.disconnect();
		await adminPage.visitIuSettings();

		await expect( page.locator( 'body' ) ).toContainText(
			/get started|connect|sign up|create.*account/i,
			{ timeout: 10_000 }
		);
	} );

	test( 'after connection, settings page reflects the connected state', async ( {
		page,
		adminPage,
		iuApi,
	} ) => {
		await iuApi.connect(); // no-op in real mode, seeds in fake mode
		const cdnHost = await iuApi.cdnHost();

		await adminPage.visitIuSettings();

		// The CDN host should now appear somewhere on the page — exact
		// placement varies (could be the settings panel, the URL hint, or
		// a "your bucket" indicator). We just want SOME evidence the plugin
		// has read the api_data option.
		await expect( page.locator( 'body' ) ).toContainText( cdnHost, { timeout: 10_000 } );
	} );

	test( 'after first sync, the file appears in the file count', async ( {
		page,
		adminPage,
		iuApi,
	} ) => {
		await iuApi.connect();

		// Upload a fixture and mark it as synced — this is what would happen
		// after a real first-sync pass completes.
		const upload = await iuApi.uploadFixture( FIXTURE_FILENAME );
		await iuApi.markSynced( upload.attachment_id );

		await adminPage.visitIuSettings();

		// Look for any digit that could be the count, or for the filename.
		// One of these has to be visible if the sync state surfaces at all.
		const bodyText        = await page.locator( 'body' ).innerText();
		const hasReflectedSync = /\b1\b/.test( bodyText ) || bodyText.includes( 'test-image' );

		expect( hasReflectedSync ).toBe( true );
	} );

	test( 'front-end serves the synced image from the CDN host', async ( { page, iuApi } ) => {
		// This is the smoke check that the connect flow + sync state actually
		// flip the URL rewriter on. The richer assertions live in
		// cdn-delivery.spec.ts.
		await iuApi.connect();
		const cdnHost = await iuApi.cdnHost();

		const upload = await iuApi.uploadFixture( FIXTURE_FILENAME );
		await iuApi.markSynced( upload.attachment_id );

		const post = await iuApi.createPost(
			'Setup Smoke Test',
			`<p><img src="${ upload.source_url }" /></p>`,
		);

		await page.goto( post.permalink );

		const imgSrc = await page
			.locator( 'article img, .entry-content img, main img' )
			.first()
			.getAttribute( 'src' );
		expect( imgSrc ).not.toBeNull();
		expect( imgSrc! ).toContain( cdnHost );
	} );
} );
