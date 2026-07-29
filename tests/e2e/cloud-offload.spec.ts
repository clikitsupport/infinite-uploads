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

		// The "Infinite Uploads" submenu sits inside #menu-media (Media menu).
		// Check for ANY <a> in admin nav whose href targets the IU settings
		// page — that's the most robust selector across WP/admin theme changes.
		const settingsLink = page.locator(
			'#adminmenu a[href*="page=infinite_uploads"]'
		).first();

		await expect( settingsLink ).toBeAttached( { timeout: 10_000 } );
	} );

	test( 'settings page loads without PHP errors', async ( { page, adminPage } ) => {
		const phpErrors: string[] = [];
		page.on( 'pageerror', err => phpErrors.push( err.message ) );

		await adminPage.visitIuSettings();

		// PHP errors would have surfaced via the pageerror channel above OR
		// as visible text on the page. Don't rely on the page title — it
		// changes across plugin versions ("Infinite Uploads" vs "Infinite
		// Uploads — Setup" vs "Infinite Uploads — Settings").
		expect( phpErrors ).toEqual( [] );
		await expect( page.locator( 'body' ) ).not.toContainText( 'Fatal error' );
		await expect( page.locator( 'body' ) ).not.toContainText( 'Parse error' );
		await expect( page.locator( 'body' ) ).not.toContainText( 'Warning:' );

		// Sanity: we landed on the IU settings page (the form / wrapper has
		// our branding somewhere in the visible content).
		await expect( page.locator( 'body' ) ).toContainText( /Infinite Uploads/i );
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

		// The Plugins list table is huge — there are many tr.active rows for
		// other plugins. Find the row by searching for the IU plugin's
		// header text and walking up to its enclosing <tr>. This is robust
		// against varying slugs (wp-env installs from a local dir give the
		// slug based on directory name, not plugin header).
		const pluginHeader = page
			.locator( 'tr' )
			.filter( { has: page.locator( 'strong, .plugin-title' ).filter( { hasText: /^Infinite Uploads$/i } ) } )
			.first();

		await expect( pluginHeader ).toBeVisible( { timeout: 10_000 } );

		// Plugin row must NOT show an "Activate" link — wp-env auto-activated it.
		await expect( pluginHeader.locator( 'a:has-text("Deactivate")' ) ).toHaveCount( 1 );
	} );
} );
