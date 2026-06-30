/**
 * E2E: infrastructure smoke tests.
 *
 * Run these FIRST before debugging individual specs — if any of these fail,
 * fixing the failing infrastructure layer will likely fix many other specs
 * at once. The leading underscore in the filename means Playwright sorts
 * this file first when running the full suite.
 *
 * Smoke checks (cheapest → most expensive):
 *
 *   1. baseURL responds at all (front-end home page loads)
 *   2. Admin session is valid (storageState put us in wp-admin, no redirect to login)
 *   3. Infinite Uploads plugin is active and reachable
 *   4. iu-test-helpers REST endpoints are reachable (returns 200 + JSON)
 *   5. The test-helpers connect/disconnect cycle round-trips correctly
 *   6. uploadFixture creates a real attachment WP recognises
 *
 * @package ClikIT\InfiniteUploads\Tests\E2E
 */

import { test, expect } from './helpers/test';

test.describe( 'Infrastructure smoke', () => {

	test( '1. baseURL responds', async ( { page } ) => {
		// If this fails: wp-env isn't running, or baseURL in playwright.config.ts
		// doesn't match the wp-env port (default 8890).
		const response = await page.goto( '/' );
		expect( response, 'expected an HTTP response from baseURL' ).not.toBeNull();
		expect( response!.status(), 'home page should return 2xx/3xx' ).toBeLessThan( 400 );
	} );

	test( '2. admin session is valid (no redirect to wp-login)', async ( { page, adminPage } ) => {
		// If this fails: global-setup.ts couldn't log in OR the storageState
		// file is stale / missing.  Re-running with `WP_BASE_URL=...
		// npx playwright test` produces a fresh login.
		await adminPage.visit( '/' );

		// wp-admin redirects to wp-login.php when the session is missing.
		expect( page.url(), 'should be on wp-admin, not wp-login' ).toMatch( /\/wp-admin\// );
		expect( page.url() ).not.toMatch( /wp-login\.php/ );
	} );

	test( '3. Infinite Uploads plugin is active', async ( { page, adminPage } ) => {
		// If this fails: wp-env didn't install / activate the plugin from
		// the `plugins` array of .wp-env.json.  Try `npm run wp-env:destroy`
		// then `npm run wp-env:start`.
		await adminPage.visit( '/plugins.php' );

		// Either the row exists with a Deactivate link (active) OR the IU
		// settings link is visible. We accept either positive signal.
		const pluginActive = await page.locator(
			'a:has-text("Infinite Uploads"), tr:has-text("Infinite Uploads") a:has-text("Deactivate")'
		).first().isVisible().catch( () => false );

		expect( pluginActive, 'Infinite Uploads plugin must be installed and active' ).toBe( true );
	} );

	test( '4. WP REST is reachable at all (/index.php?rest_route=/)', async ( { request } ) => {
		// If THIS fails: WP REST is broken at a level beneath us. Most
		// common cause is permalinks set to "Plain" — but we use the
		// rest_route query-var form which works even with Plain permalinks,
		// so a failure here is unusual.
		//
		// Diagnostic curl:
		//   curl -i 'http://localhost:8890/index.php?rest_route=/'
		const response = await request.get( '/index.php?rest_route=/' );
		const status   = response.status();
		const body     = await response.text();

		expect(
			status,
			`Expected 200 from WP REST root. Got ${ status }: ${ body.slice( 0, 200 ) }`
		).toBe( 200 );

		// Body should be a JSON description of the REST API root.
		const json = JSON.parse( body );
		expect( json ).toHaveProperty( 'namespaces' );
		expect( Array.isArray( json.namespaces ) ).toBe( true );
	} );

	test( '5a. iu-test-helpers plugin file is loaded (/ping)', async ( { request } ) => {
		// /ping has no permission_callback so it returns 200 if-and-only-if
		// the plugin file ran.
		//
		//   - 200 + JSON  → plugin loaded ✓
		//   - 404          → plugin not loaded at all (file not in
		//                     wp-content/plugins, or not activated)
		const response = await request.get( '/index.php?rest_route=/iu-test/v1/ping' );
		const status   = response.status();
		const body     = await response.text();

		expect(
			status,
			`Expected 200 from /iu-test/v1/ping. Got ${ status }: ${ body.slice( 0, 500 ) }\n\n` +
			'A 404 means the iu-test-helpers plugin file never ran. Likely causes:\n' +
			'  1. `./tests/e2e/iu-test-helpers` is not in the `plugins` array of .wp-env.json\n' +
			'  2. wp-env wasn\'t rebuilt after editing .wp-env.json\n' +
			'     → `npm run wp-env:destroy && npm run wp-env:start`\n' +
			'  3. The plugin isn\'t activated. Check with:\n' +
			'     `npx wp-env run tests-cli wp plugin list`\n'
		).toBe( 200 );

		const json = JSON.parse( body );
		expect( json.pong ).toBe( true );
		expect(
			json.wp_environment_type,
			'WP_ENVIRONMENT_TYPE constant should be "local" inside the tests env. ' +
			'If null or "production", wp-env\'s env.tests.config block isn\'t being applied.'
		).toBe( 'local' );
	} );

	test( '5b. iu-test-helpers REST endpoints accept calls', async ( { request } ) => {
		const response = await request.get( '/index.php?rest_route=/iu-test/v1/folder-count' );

		const status = response.status();
		const body   = await response.text();

		// On 403 dump the WP_Error code so we know which guard rejected us.
		if ( status === 403 ) {
			try {
				const err = JSON.parse( body );
				throw new Error(
					`/folder-count returned 403 with code "${ err.code }": ${ err.message }`
				);
			} catch ( _ ) {
				/* fall through to the normal expect() below */
			}
		}

		expect(
			status,
			`Expected 200 from /iu-test/v1/folder-count. Got ${ status }: ${ body.slice( 0, 500 ) }`
		).toBe( 200 );

		const json = JSON.parse( body );
		expect( json ).toHaveProperty( 'count' );
		expect( typeof json.count ).toBe( 'number' );
	} );

	test( '6. connection state reflects in front-end resource hints', async ( { iuApi, page } ) => {
		// Verifies that the plugin's setup() correctly enters the connected
		// code path: whatever CDN host the plugin currently knows about
		// (real or fake) should appear in the front-end <head> via
		// wp_resource_hints.
		//
		// In REAL mode: cdnHost is your live IU host (e.g. lwpzob.infiniteuploads.cloud).
		// In FAKE mode: cdnHost is 'test-cdn.iu-tests.local'.
		await iuApi.reset();
		await iuApi.connect(); // no-op when a real connection is in place

		const state   = await iuApi.getState();
		const cdnHost = state.cdn_host;

		expect(
			state.enabled,
			'Plugin must be in connected state for this smoke check. ' +
			'If you\'re testing in fake mode and this fails, the /connect endpoint isn\'t seeding options correctly.'
		).toBe( true );
		expect( cdnHost, 'cdn_host should be set in state' ).not.toBeNull();

		const post = await iuApi.createPost(
			'Smoke — connection check',
			'<p>baseline content, no images</p>',
		);

		await page.goto( post.permalink );
		const headHtml = await page.locator( 'head' ).innerHTML();

		expect(
			headHtml,
			`Expected the page <head> to contain a resource hint for the CDN host "${ cdnHost }".\n` +
			`This proves infinite_uploads_enabled() returned true and the plugin's wp_resource_hints filter ran.\n` +
			`Mode: ${ state.is_real ? 'real' : 'fake' } connection.`,
		).toContain( cdnHost! );

		// Don't iuApi.disconnect() here — in real mode it's a no-op anyway,
		// and we don't want to disrupt a manually-connected account.
	} );

	test( '7. uploadFixture creates a real attachment', async ( { iuApi } ) => {
		// If this fails: the test-image.png fixture isn't bundled inside the
		// iu-test-helpers plugin directory.  Re-check that
		// tests/e2e/iu-test-helpers/fixtures/test-image.png exists in the repo.
		const upload = await iuApi.uploadFixture( 'test-image.png' );

		expect( upload.attachment_id ).toBeGreaterThan( 0 );

		// `source_url` comes from wp_get_attachment_url() which is rewritten
		// to the CDN URL when IU is connected. We accept either shape:
		//   - local:  http://localhost:8890/wp-content/uploads/2026/06/...png
		//   - CDN:    https://<cdn>/2026/06/...png  (any host, any subpath)
		expect( upload.source_url ).toMatch( /\/\d{4}\/\d{2}\/test-image(-\d+)?\.png(\?.*)?$/ );

		// `relative` is the local relative path the plugin computes from the
		// filesystem — never rewritten. Always /YYYY/MM/test-image*.png.
		expect( upload.relative ).toMatch( /^\/\d{4}\/\d{2}\/test-image(-\d+)?\.png$/ );
	} );
} );
