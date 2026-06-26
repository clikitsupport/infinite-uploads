/**
 * E2E: File exclusions.
 *
 * Walks the full user-visible behaviour of the exclusion feature:
 *
 *   1. Image is uploaded and "synced" → front-end shows the CDN URL.
 *   2. The image's path is added to the exclusion list (via the test API,
 *      which mirrors what the admin UI saves to `iup_excluded_files`) →
 *      front-end shows the LOCAL URL.
 *   3. Exclusion is cleared → front-end shows the CDN URL again.
 *
 * This is the round-trip the user described — exclude → served locally,
 * un-exclude → served from IU.
 *
 * @package ClikIT\InfiniteUploads\Tests\E2E
 */

import { test, expect } from './helpers/test';

const FIXTURE_FILENAME = 'test-image.png';
const FAKE_CDN_HOST    = 'test-cdn.iu-tests.local';

test.describe( 'File exclusions — round-trip behaviour', () => {

	test.beforeEach( async ( { iuApi } ) => {
		await iuApi.reset();
		await iuApi.clearExcludedPaths();
		await iuApi.connect( FAKE_CDN_HOST );
	} );

	test.afterEach( async ( { iuApi } ) => {
		await iuApi.clearExcludedPaths();
		await iuApi.disconnect();
	} );

	test( 'excluding a file flips the front-end URL back to local, then reverting restores CDN', async ( {
		page,
		iuApi,
	} ) => {
		// 1. Seed: upload + mark-synced + post.
		const upload = await iuApi.uploadFixture( FIXTURE_FILENAME );
		await iuApi.markSynced( upload.attachment_id );
		const post = await iuApi.createPost(
			'Exclusion round-trip',
			`<p><img src="${ upload.source_url }" alt="" /></p>`,
		);

		const imgSelector = 'article img, .entry-content img, main img';

		// 2. Baseline — IU is connected, image is synced, no exclusions. The
		//    front-end should serve the image from the CDN host.
		await page.goto( post.permalink );
		let src = await page.locator( imgSelector ).first().getAttribute( 'src' );
		expect( src, 'baseline: CDN URL expected before any exclusion' ).toContain( FAKE_CDN_HOST );

		// 3. Exclude the image's path. We use the *filename* — Helper's
		//    is_path_excluded() does a substring match (stripos), so the
		//    filename is enough to match anywhere it appears in the URL.
		const filenameToExclude = upload.relative.split( '/' ).pop()!;
		await iuApi.setExcludedPaths( [ filenameToExclude ] );

		// 4. Reload. The rewriter should now skip this URL, leaving the
		//    original /wp-content/uploads/... path in the HTML.
		await page.goto( post.permalink );
		src = await page.locator( imgSelector ).first().getAttribute( 'src' );
		expect( src, 'after exclusion: local URL expected' ).not.toContain( FAKE_CDN_HOST );
		expect( src, 'after exclusion: should still be the WP uploads path' ).toContain( '/wp-content/uploads' );

		// 5. Reverse: clear exclusions.
		await iuApi.clearExcludedPaths();

		// 6. Reload. CDN URL should be back.
		await page.goto( post.permalink );
		src = await page.locator( imgSelector ).first().getAttribute( 'src' );
		expect( src, 'after un-exclusion: CDN URL should be restored' ).toContain( FAKE_CDN_HOST );
	} );

	test( 'exclusion is scoped — un-excluded images on the same page still go via CDN', async ( {
		page,
		iuApi,
	} ) => {
		// Upload two images so we can exclude one and prove the other still
		// rewrites. The fixture only has one file, so we upload it twice.
		// wp_unique_filename ensures the destinations are different.
		const imageA = await iuApi.uploadFixture( FIXTURE_FILENAME );
		const imageB = await iuApi.uploadFixture( FIXTURE_FILENAME );
		await iuApi.markSynced( imageA.attachment_id );
		await iuApi.markSynced( imageB.attachment_id );

		const post = await iuApi.createPost(
			'Mixed exclusion',
			`<p>` +
				`<img class="image-a" src="${ imageA.source_url }" alt="" />` +
				`<img class="image-b" src="${ imageB.source_url }" alt="" />` +
			`</p>`,
		);

		// Exclude ONLY image B by its unique filename.
		const filenameB = imageB.relative.split( '/' ).pop()!;
		await iuApi.setExcludedPaths( [ filenameB ] );

		await page.goto( post.permalink );

		const srcA = await page.locator( 'img.image-a' ).first().getAttribute( 'src' );
		const srcB = await page.locator( 'img.image-b' ).first().getAttribute( 'src' );

		expect( srcA, 'image A (not excluded) should still rewrite to CDN' ).toContain( FAKE_CDN_HOST );
		expect( srcB, 'image B (excluded) should stay local' ).not.toContain( FAKE_CDN_HOST );
		expect( srcB! ).toContain( '/wp-content/uploads' );
	} );

	test( 'disabling the exclusion toggle (empty list) makes everything use CDN again', async ( {
		page,
		iuApi,
	} ) => {
		// The toggle is auto-managed by setExcludedPaths — passing an empty
		// list sets iu_file_exclusion_enabled to 'no'. We verify the
		// rewriter short-circuits to its normal CDN path in that mode.
		const upload = await iuApi.uploadFixture( FIXTURE_FILENAME );
		await iuApi.markSynced( upload.attachment_id );

		// Set an exclusion (which also flips the toggle on).
		await iuApi.setExcludedPaths( [ upload.relative ] );

		const post = await iuApi.createPost(
			'Exclusion toggle disable test',
			`<p><img src="${ upload.source_url }" alt="" /></p>`,
		);

		await page.goto( post.permalink );
		let src = await page.locator( 'article img, .entry-content img, main img' ).first().getAttribute( 'src' );
		expect( src, 'while excluded, src should be local' ).not.toContain( FAKE_CDN_HOST );

		// Disable the toggle by clearing the list.
		await iuApi.clearExcludedPaths();

		await page.goto( post.permalink );
		src = await page.locator( 'article img, .entry-content img, main img' ).first().getAttribute( 'src' );
		expect( src, 'after disabling the toggle, src should be CDN again' ).toContain( FAKE_CDN_HOST );
	} );
} );
