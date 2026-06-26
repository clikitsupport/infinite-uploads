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
		const res = await this.request.post( '/wp-json/iu-test/v1/reset' );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.reset failed: ${ res.status() } ${ await res.text() }` );
		}
	}

	async createFolder( name: string, parentId = 0 ): Promise< { id: number; name: string } > {
		const res = await this.request.post( '/wp-json/iu-test/v1/folders', {
			data: { name, parent_id: parentId },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.createFolder failed: ${ res.status() } ${ await res.text() }` );
		}
		return res.json();
	}

	async attachMediaToFolder( folderId: number, attachmentId: number ): Promise< void > {
		const res = await this.request.post( `/wp-json/iu-test/v1/folders/${ folderId }/media`, {
			data: { attachment_id: attachmentId },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.attachMediaToFolder failed: ${ res.status() }` );
		}
	}

	async setUploadFolder( folderId: number, userId = 1 ): Promise< void > {
		const res = await this.request.post( '/wp-json/iu-test/v1/upload-folder', {
			data: { folder_id: folderId, user_id: userId },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.setUploadFolder failed: ${ res.status() }` );
		}
	}

	async folderCount(): Promise< number > {
		const res  = await this.request.get( '/wp-json/iu-test/v1/folder-count' );
		const data = await res.json();
		return data.count;
	}

	async getAttachmentFolder( attachmentId: number ): Promise< number | null > {
		const res  = await this.request.get( `/wp-json/iu-test/v1/attachment/${ attachmentId }/folder` );
		const data = await res.json();
		return data.folder_id;
	}

	// ---------------------------------------------------------------------
	// Connection state — fake the "connected to IU cloud" state.
	// ---------------------------------------------------------------------

	async connect( cdnHost = 'test-cdn.iu-tests.local' ): Promise< { cdn_host: string } > {
		const res = await this.request.post( '/wp-json/iu-test/v1/connect', {
			data: { cdn_host: cdnHost },
		} );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.connect failed: ${ res.status() } ${ await res.text() }` );
		}
		return res.json();
	}

	async disconnect(): Promise< void > {
		const res = await this.request.post( '/wp-json/iu-test/v1/disconnect' );
		if ( ! res.ok() ) {
			throw new Error( `iuApi.disconnect failed: ${ res.status() } ${ await res.text() }` );
		}
	}

	// ---------------------------------------------------------------------
	// Pretend an attachment has been offloaded to the cloud.
	// ---------------------------------------------------------------------

	async markSynced( attachmentId: number ): Promise< { file: string; size: number } > {
		const res = await this.request.post( '/wp-json/iu-test/v1/mark-synced', {
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
		const res = await this.request.post( '/wp-json/iu-test/v1/excluded-paths', {
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
		relative: string;
	} > {
		const res = await this.request.post( '/wp-json/iu-test/v1/upload-fixture', {
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
		const res = await this.request.post( '/wp-json/iu-test/v1/posts', {
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
