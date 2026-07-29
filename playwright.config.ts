/**
 * Playwright configuration for Infinite Uploads E2E tests.
 *
 * Tests run against the wp-env "tests" environment (port 8890). Use
 * `npm run wp-env:start` to boot it, then `npm run test:e2e` to run.
 *
 * @see https://playwright.dev/docs/test-configuration
 * @see https://github.com/WordPress/gutenberg/tree/trunk/packages/e2e-test-utils-playwright
 */

import { defineConfig, devices } from '@playwright/test';
import * as path from 'path';

const STORAGE_STATE_PATH = path.join( __dirname, 'tests/e2e/.auth/admin.json' );
const ARTIFACTS_PATH     = path.join( __dirname, 'test-results' );
// Keep the HTML report SEPARATE from outputDir — Playwright clears outputDir
// at the start of each run, which would wipe the html-report if it were
// nested inside (in newer versions this is a hard "Configuration Error").
const HTML_REPORT_PATH   = path.join( __dirname, 'playwright-report' );

export default defineConfig( {
	testDir: './tests/e2e',
	testIgnore: [ '**/fixtures/**', '**/iu-test-helpers/**' ],

	// Each test gets its own context — no cross-test bleed.
	fullyParallel: true,

	// Fail CI builds if a developer left a test.only().
	forbidOnly: !! process.env.CI,

	// Retry failing tests once locally, twice in CI (flaky-network mitigation).
	retries: process.env.CI ? 2 : 1,

	// Single worker by default — wp-env's MySQL is the bottleneck for parallelism.
	// Raise to 2-4 on a beefy machine if you've validated test isolation.
	workers: process.env.CI ? 1 : 1,

	reporter: [
		[ 'html', { outputFolder: HTML_REPORT_PATH, open: 'never' } ],
		[ 'list' ],
	],

	outputDir: ARTIFACTS_PATH,

	use: {
		// wp-env's tests env runs on port 8890 by default.
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8890',

		// Re-use the admin session captured by global-setup.ts so every spec
		// starts already logged in. Saves ~1.5s per test.
		storageState: STORAGE_STATE_PATH,

		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		video: 'retain-on-failure',
		actionTimeout: 10_000,
		navigationTimeout: 30_000,
	},

	// Global setup: log into wp-admin once and save the session state.
	// All specs reuse that state via use.storageState above.
	globalSetup: require.resolve( './tests/e2e/global-setup.ts' ),

	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ] },
		},
		// Add other browsers if you want broader coverage:
		// { name: 'firefox', use: { ...devices[ 'Desktop Firefox' ] } },
		// { name: 'webkit',  use: { ...devices[ 'Desktop Safari' ] } },
	],
} );
