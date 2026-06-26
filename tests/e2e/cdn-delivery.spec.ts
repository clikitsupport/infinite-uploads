/**
 * E2E: Uploaded images use the CDN on the live site.
 *
 * Verifies the two things end-users care about most about offload:
 *
 *   1. The `<img src>` of an uploaded-and-offloaded image is rewritten to
 *      point at the IU CDN host (not /wp-content/uploads/...).
 *   2. The page's `<head>` includes browser resource hints
 *      (dns-prefetch or preconnect) for the CDN domain so the browser
 *      can warm up the connection before requesting any image.
 *
 * @package ClikIT\InfiniteUploads\Tests\E2E
 */

import { test, expect } from './helpers/test';

const FIXTURE_FILENAME = 'test-image.png';
const FAKE_CDN_HOST    = 'test-cdn.iu-tests.local';

test.describe( 'CDN delivery on the live site', () => {

	test.beforeEach( async ( { iuApi } ) => {
		await iuApi.reset();
		await iuApi.clearExcludedPaths();
		await iuApi.connect( FAKE_CDN_HOST );
	} );

	test.afterEach( async ( { iuApi } ) => {
		await iuApi.disconnect();
	} );

	test( 'public page rewrites an offloaded image src to the CDN host', async ( {
		page,
		iuApi,
	} ) => {
		const upload = await iuApi.uploadFixture( FIXTURE_FILENAME );
		await iuApi.markSynced( upload.attachment_id );

		const post = await iuApi.createPost(
			'CDN Delivery — basic img tag',
			`<p>This post embeds an image: <img src="${ upload.source_url }" alt="test" /></p>`,
		);

		await page.goto( post.permalink );

		const img = page.locator( 'article img, .entry-content img, main img' ).first();
		await expect( img ).toBeVisible();

		const src = await img.getAttribute( 'src' );
		expect( src ).not.toBeNull();
		expect( src! ).toContain( FAKE_CDN_HOST );
		// Sanity: the local uploads path must NOT survive the rewrite.
		expect( src! ).not.toContain( '/wp-content/uploads' );
	} );

	test( 'WP rendered img tags (the_content) are also rewritten', async ( { page, iuApi } ) => {
		// WordPress turns wrapped <img> tags into figure blocks etc. via
		// the_content filters. The IU rewriter runs over the final HTML
		// regardless of how the image was emitted.
		const upload = await iuApi.uploadFixture( FIXTURE_FILENAME );
		await iuApi.markSynced( upload.attachment_id );

		// Use a Gutenberg-style image block so the rendering path differs
		// from a raw <img> tag.
		const blockContent =
			`<!-- wp:image {"id":${ upload.attachment_id }} -->\n` +
			`<figure class="wp-block-image"><img src="${ upload.source_url }" alt="" class="wp-image-${ upload.attachment_id }"/></figure>\n` +
			`<!-- /wp:image -->`;

		const post = await iuApi.createPost( 'CDN Delivery — Gutenberg image block', blockContent );

		await page.goto( post.permalink );

		const src = await page.locator( 'figure.wp-block-image img' ).first().getAttribute( 'src' );
		expect( src ).not.toBeNull();
		expect( src! ).toContain( FAKE_CDN_HOST );
	} );

	test( 'page <head> includes a resource hint for the CDN domain', async ( { page, iuApi } ) => {
		// Even a post with NO images should emit the hint, because the plugin
		// adds it via wp_resource_hints unconditionally when enabled.
		const post = await iuApi.createPost(
			'CDN Delivery — resource hints',
			'<p>No image here, just text.</p>',
		);

		await page.goto( post.permalink );

		const headHtml = await page.locator( 'head' ).innerHTML();

		// We want SOMETHING — dns-prefetch OR preconnect — pointing at the CDN.
		const hintRegex = new RegExp(
			`<link[^>]+rel=["'](?:dns-prefetch|preconnect)["'][^>]*${ FAKE_CDN_HOST.replace( /\./g, '\\.' ) }`,
			'i'
		);
		expect(
			headHtml,
			'<head> should contain a <link rel="dns-prefetch"> or <link rel="preconnect"> targeting the CDN host'
		).toMatch( hintRegex );
	} );

	test( 'when the plugin is disconnected, urls stay local', async ( { page, iuApi } ) => {
		// Regression-guard: if disconnect didn't clear iup_enabled, all the
		// other tests above would still pass spuriously.
		const upload = await iuApi.uploadFixture( FIXTURE_FILENAME );

		await iuApi.disconnect();

		const post = await iuApi.createPost(
			'CDN Delivery — disconnected baseline',
			`<p><img src="${ upload.source_url }" alt="" /></p>`,
		);

		await page.goto( post.permalink );

		const src = await page.locator( 'article img, .entry-content img, main img' ).first().getAttribute( 'src' );
		expect( src ).not.toBeNull();
		expect( src! ).not.toContain( FAKE_CDN_HOST );
		expect( src! ).toContain( '/wp-content/uploads' );
	} );
} );
