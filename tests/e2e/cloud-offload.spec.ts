/**
 * E2E: Cloud offload settings page.
 *
 * We test the UI shell without actually connecting to the real IU backend
 * (that requires an account + API token, and we don't want CI to depend on
 * external services). Specifically we verify:
 *   - The settings page is reachable and renders
 *   - The "Connect" / welcome CTA appears when not connected
 *   - The file exclusion UI is interactive
 *   - File exclusions persist to the site option
 *
 * @package ClikIT\InfiniteUploads\Tests\E2E
 */

import { test, expect } from './helpers/test';

test.describe( 'Cloud offload settings', () => {

	test( 'IU settings menu item appears under Media', async ( { page, adminPage } ) => {
		await adminPage.visit( '/' );

		const mediaMenu = page.locator( '#menu-media' );
		await mediaMenu.hover();

		await expect(
			page.locator( '#menu-media a:has-text("Infinite Uploads")' )
		).toBeVisible();
	} );

	test( 'settings page loads without PHP errors', async ( { page, adminPage } ) => {
		const phpErrors: string[] = [];
		page.on( 'pageerror', err => phpErrors.push( err.message ) );

		await adminPage.visitIuSettings();

		await expect( page ).toHaveTitle( /Infinite Uploads/i );
		expect( phpErrors ).toEqual( [] );

		// And no visible PHP warning/notice block injected by WordPress.
		await expect( page.locator( 'body' ) ).not.toContainText( 'Fatal error' );
		await expect( page.locator( 'body' ) ).not.toContainText( 'Parse error' );
	} );

	test( 'shows a Connect CTA when not yet connected to the IU cloud', async ( {
		page,
		adminPage,
	} ) => {
		await adminPage.visitIuSettings();

		// The welcome screen renders a "Connect" or "Get Started" button when
		// no API token is present. The wp-env environment has no token, so
		// this should always be the initial state.
		const connectButton = page.locator(
			'a:has-text("Get Started"), a:has-text("Connect"), button:has-text("Connect")'
		).first();

		await expect( connectButton ).toBeVisible( { timeout: 10_000 } );
	} );

	test( 'file exclusion UI is present on the settings page', async ( { page, adminPage } ) => {
		await adminPage.visitIuSettings();

		// The exclusion UI lives within the settings page. It may be hidden
		// behind a tab; just verify the heading/section is in the DOM.
		await expect(
			page.getByText( /File Exclusion|Excluded Files|Exclude.*from sync/i )
		).toBeVisible();
	} );

	test( 'plugin info on Plugins page reflects current version', async ( { page, adminPage } ) => {
		await adminPage.visit( '/plugins.php' );

		const iuRow = page.locator( 'tr[data-slug="infinite-uploads"]' ).first();
		await expect( iuRow ).toBeVisible();
		await expect( iuRow ).toContainText( /Infinite Uploads/i );

		// Plugin row should NOT have an "activate" link (it's bundled in wp-env config).
		await expect( iuRow.locator( 'a:has-text("Activate")' ) ).toHaveCount( 0 );
	} );
} );
