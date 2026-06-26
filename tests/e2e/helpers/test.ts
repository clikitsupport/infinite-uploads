/**
 * Custom Playwright test fixture that extends @playwright/test with:
 *   - `iuApi`     — typed wrapper around our /iu-test/v1/* helper REST endpoints
 *   - `adminPage` — convenience for navigating to /wp-admin/* URLs
 *
 * Use this fixture instead of importing `test` directly from `@playwright/test`:
 *
 *     import { test, expect } from '@helpers/test';
 *
 *     test( 'creates a folder', async ( { iuApi, page } ) => {
 *         await iuApi.reset();
 *         const folder = await iuApi.createFolder( 'My Folder' );
 *         // ...
 *     } );
 *
 * @package ClikIT\InfiniteUploads\Tests\E2E
 */

import { test as base, expect, type APIRequestContext, type Page } from '@playwright/test';

/**
 * Wrapper around the iu-test-helpers REST endpoints. All methods are async
 * and throw on non-2xx responses (Playwright catches and fails the test).
 */
export class IUTestApi {
	constructor( private readonly request: APIRequestContext ) {}

	async reset(): Promise< void > {
		const res = await this.request.post( '/index.php?rest_route=/iu-test/v1/reset' );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.reset failed: ${ res.status() } ${ await res.text() }` );
		}
	}

	async createFolder( name: string, parentId = 0 ): Promise< { id: number; name: string } > {
		const res = await this.request.post( '/index.php?rest_route=/iu-test/v1/folders', {
			data: { name, parent_id: parentId },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.createFolder failed: ${ res.status() } ${ await res.text() }` );
		}
		return res.json();
	}

	async attachMediaToFolder( folderId: number, attachmentId: number ): Promise< void > {
		const res = await this.request.post( `/index.php?rest_route=/iu-test/v1/folders/${ folderId }/media`, {
			data: { attachment_id: attachmentId },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.attachMediaToFolder failed: ${ res.status() }` );
		}
	}

	async setUploadFolder( folderId: number, userId = 1 ): Promise< void > {
		const res = await this.request.post( '/index.php?rest_route=/iu-test/v1/upload-folder', {
			data: { folder_id: folderId, user_id: userId },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.setUploadFolder failed: ${ res.status() }` );
		}
	}

	async folderCount(): Promise< number > {
		const res  = await this.request.get( '/index.php?rest_route=/iu-test/v1/folder-count' );
		const data = await res.json();
		return data.count;
	}

	async getAttachmentFolder( attachmentId: number ): Promise< number | null > {
		const res  = await this.request.get( `/index.php?rest_route=/iu-test/v1/attachment/${ attachmentId }/folder` );
		const data = await res.json();
		return data.folder_id;
	}

	// ---------------------------------------------------------------------
	// Connection state — auto-detects real vs fake.
	//
	// If a real IU connection is already in place (because you connected via
	// the wp-admin UI), every test helper below treats it as authoritative
	// and skips operations that would clobber it. Otherwise we seed fake
	// credentials so the plugin enters its "connected" code path.
	// ---------------------------------------------------------------------

	/**
	 * Read the current connection state from the test-helpers plugin.
	 * Returns an empty/disconnected shape if nothing is set yet.
	 */
	async getState(): Promise< {
		enabled: boolean;
		has_token: boolean;
		site_id: number;
		cdn_host: string | null;
		bucket: string | null;
		is_real: boolean;
	} > {
		const res = await this.request.get( '/index.php?rest_route=/iu-test/v1/state' );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.getState failed: ${ res.status() } ${ await res.text() }` );
		}
		return res.json();
	}

	/**
	 * The CDN host the plugin is currently configured to use, regardless of
	 * whether it's a real or fake connection. Throws if not connected.
	 *
	 * Use this in spec assertions instead of a hard-coded host so tests
	 * work against both the fake test connection and a real manual one.
	 */
	async cdnHost(): Promise< string > {
		const state = await this.getState();
		if ( ! state.enabled || ! state.cdn_host ) {
			throw new Error(
				`iuApi.cdnHost: IU is not connected. Either call iuApi.connect() to seed a fake ` +
				`connection, or connect manually via wp-admin (see tests/e2e/README.md). ` +
				`Current state: ${ JSON.stringify( state ) }`
			);
		}
		return state.cdn_host;
	}

	/**
	 * Seed fake credentials so the plugin behaves as if connected.
	 *
	 * If a REAL connection is already in place (state.is_real === true),
	 * this is a no-op — we don't want to overwrite a real iup_apitoken
	 * that someone set up via the admin UI.
	 *
	 * Returns the current effective CDN host (the real one if connected,
	 * otherwise the freshly-seeded fake one).
	 */
	async connect( cdnHost = 'test-cdn.iu-tests.local' ): Promise< { cdn_host: string } > {
		const state = await this.getState();
		if ( state.is_real && state.enabled && state.cdn_host ) {
			return { cdn_host: state.cdn_host };
		}

		const res = await this.request.post( '/index.php?rest_route=/iu-test/v1/connect', {
			data: { cdn_host: cdnHost },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.connect failed: ${ res.status() } ${ await res.text() }` );
		}
		return res.json();
	}

	/**
	 * Clear the fake credentials.
	 *
	 * If a REAL connection is in place, this is a no-op — we don't want
	 * to disconnect a real account out from under the user just because a
	 * test's `afterEach` ran. Tests that need a truly-disconnected state
	 * should skip themselves when `state.is_real === true`.
	 */
	async disconnect(): Promise< void > {
		const state = await this.getState();
		if ( state.is_real ) {
			return;
		}

		const res = await this.request.post( '/index.php?rest_route=/iu-test/v1/disconnect' );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.disconnect failed: ${ res.status() } ${ await res.text() }` );
		}
	}

	// ---------------------------------------------------------------------
	// Pretend an attachment has been offloaded to the cloud.
	// ---------------------------------------------------------------------

	async markSynced( attachmentId: number ): Promise< { file: string; size: number } > {
		const res = await this.request.post( '/index.php?rest_route=/iu-test/v1/mark-synced', {
			data: { attachment_id: attachmentId },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.markSynced failed: ${ res.status() } ${ await res.text() }` );
		}
		return res.json();
	}

	// ---------------------------------------------------------------------
	// File exclusion controls.
	// ---------------------------------------------------------------------

	async setExcludedPaths( paths: string[] ): Promise< void > {
		const res = await this.request.post( '/index.php?rest_route=/iu-test/v1/excluded-paths', {
			data: { paths },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.setExcludedPaths failed: ${ res.status() } ${ await res.text() }` );
		}
	}

	async clearExcludedPaths(): Promise< void > {
		await this.setExcludedPaths( [] );
	}

	// ---------------------------------------------------------------------
	// Server-side fixture upload + post creation. Avoids the X-WP-Nonce
	// dance required by wp/v2/media multipart uploads.
	// ---------------------------------------------------------------------

	async uploadFixture( filename: string ): Promise< {
		attachment_id: number;
		source_url: string;
		local_url: string;
		relative: string;
	} > {
		const res = await this.request.post( '/index.php?rest_route=/iu-test/v1/upload-fixture', {
			data: { filename },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.uploadFixture failed: ${ res.status() } ${ await res.text() }` );
		}
		return res.json();
	}

	async createPost(
		title: string,
		content: string,
		status: 'publish' | 'draft' = 'publish'
	): Promise< { id: number; permalink: string } > {
		const res = await this.request.post( '/index.php?rest_route=/iu-test/v1/posts', {
			data: { title, content, status },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.createPost failed: ${ res.status() } ${ await res.text() }` );
		}
		return res.json();
	}
}

/**
 * Tiny convenience wrapper for navigating to admin pages.
 */
export class AdminPage {
	constructor( private readonly page: Page ) {}

	async visit( adminPath: string ): Promise< void > {
		const target = adminPath.startsWith( '/' ) ? adminPath : `/${ adminPath }`;
		await this.page.goto( `/wp-admin${ target }` );
	}

	async visitMediaLibrary(): Promise< void > {
		await this.visit( '/upload.php' );
	}

	async visitIuSettings(): Promise< void > {
		await this.visit( '/upload.php?page=infinite_uploads' );
	}

	/**
	 * Ensure the IU Media Folders sidebar is expanded AND the initial
	 * `iu_get_folders` AJAX has settled.
	 *
	 * The sidebar's collapsed/expanded state is persisted to localStorage —
	 * if a previous test (or a manual session) left it collapsed,
	 * `#iu-media-folders-sidebar` won't be visible and every folder
	 * interaction times out. Click `.iu-sidebar-toggle` first if so.
	 *
	 * The wait for `iu_get_folders` to finish is critical: the plugin fires
	 * that AJAX from `init()` and only the success handler calls
	 * `buildTree()`, which begins with `$tree.empty()`. If a test starts
	 * interacting with the tree before that response arrives — e.g. clicks
	 * "New Folder" — the empty `iu_get_folders` response can fire MID-test
	 * and wipe out the in-progress temp node. Always wait first.
	 *
	 * Safe to call on every Media Library navigation — it's a no-op when
	 * the sidebar is already open and the AJAX has already settled.
	 */
	async ensureFolderSidebarOpen(): Promise< void > {
		// Wait for the wrap to render so we can check its collapsed class.
		await this.page.waitForSelector( '#iu-media-folders-wrap', { timeout: 15_000 } );

		const isCollapsed = await this.page
			.locator( '#iu-media-folders-wrap' )
			.evaluate( ( el ) => el.classList.contains( 'iu-collapsed' ) );

		if ( isCollapsed ) {
			await this.page.locator( '.iu-sidebar-toggle' ).first().click();
			// Wait for the class to clear AND the sidebar to be visible.
			await this.page.waitForFunction( () => {
				const wrap = document.getElementById( 'iu-media-folders-wrap' );
				return wrap && ! wrap.classList.contains( 'iu-collapsed' );
			}, { timeout: 5_000 } );
		}

		await this.page.waitForSelector( '#iu-media-folders-sidebar', {
			state: 'visible',
			timeout: 5_000,
		} );

		// Wait until network has been idle for 500ms — the simplest portable
		// signal that the init-time `iu_get_folders` request has settled.
		// If it has already settled by the time we get here, this returns
		// immediately.
		await this.page.waitForLoadState( 'networkidle' );
	}
}

export const test = base.extend< {
	iuApi: IUTestApi;
	adminPage: AdminPage;
} >( {
	iuApi: async ( { request }, use ) => {
		await use( new IUTestApi( request ) );
	},
	adminPage: async ( { page }, use ) => {
		await use( new AdminPage( page ) );
	},
} );

export { expect };
