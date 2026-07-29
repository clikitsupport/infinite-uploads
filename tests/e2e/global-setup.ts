/**
 * Global setup — runs once before the whole test suite.
 *
 * Logs in as admin once and saves the session state to .auth/admin.json.
 * Every spec then re-uses that state via playwright.config.ts → use.storageState,
 * so individual tests start already logged in (saves ~1.5s per test).
 *
 * Robustness:
 *   - Waits for wp-env to actually be reachable before attempting login
 *     (after `wp-env start`, the HTTP port is up before WordPress is fully
 *     ready to serve dynamic pages).
 *   - Uses element-presence assertions instead of fragile waitForURL — login
 *     success is detected by the presence of `#wpadminbar` on the resulting
 *     page, which is more reliable than predicting the exact redirect URL.
 *   - On failure, dumps the page URL, title, and a screenshot under
 *     test-results/global-setup-failure.png so you can see exactly where
 *     the login attempt went wrong.
 *
 * wp-env's default admin credentials are admin / password.
 */

import { chromium, type FullConfig } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

const ADMIN_USER     = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASSWORD = process.env.WP_ADMIN_PASSWORD || 'password';
const READY_TIMEOUT  = 60_000;

async function globalSetup( config: FullConfig ): Promise< void > {
	const baseURL  = config.projects[ 0 ].use.baseURL || 'http://localhost:8890';
	const authDir  = path.join( __dirname, '.auth' );
	const authFile = path.join( authDir, 'admin.json' );
	const failDir  = path.join( __dirname, '..', '..', 'test-results' );

	if ( ! fs.existsSync( authDir ) ) {
		fs.mkdirSync( authDir, { recursive: true } );
	}
	if ( ! fs.existsSync( failDir ) ) {
		fs.mkdirSync( failDir, { recursive: true } );
	}

	const browser = await chromium.launch();
	const page    = await browser.newPage( { baseURL } );

	try {
		// Step 1: poll the home page until we get a 2xx — wp-env's HTTP port
		// is up several seconds before WP can actually render pages.
		await waitForWpReady( page, baseURL );

		// Step 2: navigate to login and submit credentials.
		await page.goto( '/wp-login.php', { waitUntil: 'domcontentloaded' } );

		await page.waitForSelector( '#user_login', { timeout: 15_000 } );
		await page.fill( '#user_login', ADMIN_USER );
		await page.fill( '#user_pass', ADMIN_PASSWORD );

		// Submit + wait for the resulting page to settle. We assert success
		// by the presence of #wpadminbar on the destination — that's only
		// rendered for logged-in users on admin pages.
		await Promise.all( [
			page.waitForLoadState( 'domcontentloaded' ),
			page.click( '#wp-submit' ),
		] );

		// Wait up to 15s for wp-admin to load. If we landed on the login
		// page again (bad credentials), there's no #wpadminbar.
		await page.waitForSelector( '#wpadminbar', { timeout: 15_000 } ).catch( async () => {
			// Dump diagnostics before throwing.
			const url     = page.url();
			const title   = await page.title().catch( () => '<unreadable>' );
			const error   = await page
				.locator( '#login_error, .login-error, #message' )
				.first()
				.textContent()
				.catch( () => null );
			const shotAt = path.join( failDir, 'global-setup-failure.png' );
			await page.screenshot( { path: shotAt, fullPage: true } ).catch( () => {} );

			throw new Error(
				`Login as ${ ADMIN_USER } didn't reach wp-admin.\n` +
				`  URL:       ${ url }\n` +
				`  Title:     ${ title }\n` +
				`  Error msg: ${ error ?? '<none>' }\n` +
				`  Screenshot: ${ shotAt }\n\n` +
				'Likely causes:\n' +
				'  • wp-env tests env not running on baseURL.\n' +
				'      → `npm run wp-env:start` (wait for "WordPress development site started" lines).\n' +
				'  • Admin password isn\'t "password" (override via WP_ADMIN_PASSWORD env var).\n' +
				'  • A "setup" / "install" screen is intercepting because the install hadn\'t completed.'
			);
		} );

		await page.context().storageState( { path: authFile } );
	} finally {
		await browser.close();
	}
}

/**
 * Poll the home page until we get a 2xx OR run out of patience. After
 * `wp-env start`, the HTTP port answers immediately but WordPress takes a
 * few extra seconds to be ready (DB ready check, plugin activation, etc.).
 */
async function waitForWpReady( page: Parameters< typeof globalSetup >[ 0 ] extends any ? any : any, baseURL: string ): Promise< void > {
	const started = Date.now();
	let lastErr   = '';

	while ( Date.now() - started < READY_TIMEOUT ) {
		try {
			const response = await page.goto( '/', { waitUntil: 'domcontentloaded', timeout: 5_000 } );
			if ( response && response.status() < 400 ) {
				return;
			}
			lastErr = `HTTP ${ response?.status() ?? '?' }`;
		} catch ( err: any ) {
			lastErr = err.message ?? String( err );
		}
		await page.waitForTimeout( 1_000 );
	}

	throw new Error(
		`wp-env never became ready at ${ baseURL } within ${ READY_TIMEOUT / 1000 }s.\n` +
		`  Last error: ${ lastErr }\n\n` +
		'Run `npm run wp-env:start` and wait for it to print "WordPress test site started",\n' +
		'then re-run Playwright.'
	);
}

export default globalSetup;
