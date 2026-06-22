/**
 * Global setup — runs once before the whole test suite.
 *
 * Logs in as admin once and saves the session state to .auth/admin.json.
 * Every spec then re-uses that state via playwright.config.ts → use.storageState,
 * so individual tests start already logged in (saves ~1.5s per test).
 *
 * wp-env's default admin credentials: admin / password.
 */

import { chromium, type FullConfig } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

async function globalSetup( config: FullConfig ): Promise< void > {
	const baseURL  = config.projects[ 0 ].use.baseURL || 'http://localhost:8890';
	const authDir  = path.join( __dirname, '.auth' );
	const authFile = path.join( authDir, 'admin.json' );

	if ( ! fs.existsSync( authDir ) ) {
		fs.mkdirSync( authDir, { recursive: true } );
	}

	const browser = await chromium.launch();
	const page    = await browser.newPage( { baseURL } );

	await page.goto( '/wp-login.php' );
	await page.fill( '#user_login', 'admin' );
	await page.fill( '#user_pass', 'password' );
	await page.click( '#wp-submit' );

	// Wait for redirect to wp-admin to confirm the login worked.
	await page.waitForURL( /\/wp-admin\/?(\?|$)/, { timeout: 15_000 } );

	// Persist the session cookies + localStorage to disk.
	await page.context().storageState( { path: authFile } );

	await browser.close();
}

export default globalSetup;
