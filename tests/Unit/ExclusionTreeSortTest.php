<?php
/**
 * Tests for the file-exclusion tree's ordering.
 *
 * prepare_directory_tree() blends real filesystem entries (returned by
 * FilesystemIterator in filesystem order, which is effectively arbitrary) with
 * "virtual" paths injected from the synced filelist. Both are sorted into a
 * single directories-first, alphabetical list so the tree is scannable.
 *
 * The class constructor registers admin hooks and pulls in three singletons, so
 * these tests instantiate it without the constructor — prepare_directory_tree()
 * itself touches no WordPress functions.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use ClikIT\InfiniteUploads\InfiniteUploadsAdmin;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;

class ExclusionTreeSortTest extends TestCase {

	/** @var InfiniteUploadsAdmin */
	private $admin;

	/** @var string */
	private $root;

	protected function setUp(): void {
		parent::setUp();
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsAdmin.php';

		$ref         = new ReflectionClass( InfiniteUploadsAdmin::class );
		$this->admin = $ref->newInstanceWithoutConstructor();

		$this->root = sys_get_temp_dir() . '/iu-tree-sort-' . uniqid();
		mkdir( $this->root, 0777, true );
	}

	protected function tearDown(): void {
		$this->rmdir_recursive( $this->root );
		parent::tearDown();
	}

	private function rmdir_recursive( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		foreach ( scandir( $dir ) as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $entry;
			is_dir( $path ) ? $this->rmdir_recursive( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}

	/**
	 * @return array Node labels in returned order, directories suffixed with '/'.
	 */
	private function labels( array $nodes ): array {
		return array_map(
			function ( $node ) {
				return 'jstree-folder' === $node['icon'] ? $node['text'] . '/' : $node['text'];
			},
			$nodes
		);
	}

	public function test_it_lists_directories_before_files_each_alphabetically() {
		// Created deliberately out of order so a passing result cannot be
		// creation-order coincidence.
		touch( $this->root . '/zebra.png' );
		mkdir( $this->root . '/2024' );
		touch( $this->root . '/apple.png' );
		mkdir( $this->root . '/backups' );
		touch( $this->root . '/Banana.png' );
		mkdir( $this->root . '/2023' );

		$nodes = $this->admin->prepare_directory_tree( $this->root );

		$this->assertSame(
			[ '2023/', '2024/', 'backups/', 'apple.png', 'Banana.png', 'zebra.png' ],
			$this->labels( $nodes )
		);
	}

	public function test_it_sorts_case_insensitively() {
		touch( $this->root . '/beta.png' );
		touch( $this->root . '/Alpha.png' );
		touch( $this->root . '/Gamma.png' );

		$nodes = $this->admin->prepare_directory_tree( $this->root );

		$this->assertSame(
			[ 'Alpha.png', 'beta.png', 'Gamma.png' ],
			$this->labels( $nodes ),
			'Uppercase names should not be grouped ahead of lowercase ones.'
		);
	}

	public function test_it_sorts_numeric_names_naturally() {
		foreach ( [ '10', '9', '1', '2' ] as $month ) {
			mkdir( $this->root . '/' . $month );
		}

		$nodes = $this->admin->prepare_directory_tree( $this->root );

		$this->assertSame(
			[ '1/', '2/', '9/', '10/' ],
			$this->labels( $nodes ),
			'"10" must sort after "9", not between "1" and "2".'
		);
	}

	public function test_cloud_only_virtual_paths_are_sorted_in_with_real_entries() {
		// Only 'middle' exists locally; the other two are cloud-only entries
		// injected from the filelist (e.g. after Free Up Local Storage).
		mkdir( $this->root . '/middle' );
		touch( $this->root . '/zzz.png' );

		$nodes = $this->admin->prepare_directory_tree(
			$this->root,
			[],
			[ '/alpha/one.png', '/omega/two.png' ],
			$this->root
		);

		$this->assertSame(
			[ 'alpha/', 'middle/', 'omega/', 'zzz.png' ],
			$this->labels( $nodes ),
			'Virtual directories must interleave alphabetically, not trail the real ones.'
		);
	}

	public function test_sorting_preserves_node_payloads() {
		mkdir( $this->root . '/dir-b' );
		touch( $this->root . '/file-a.png' );

		$excluded = [ $this->root . '/file-a.png' ];
		$nodes    = $this->admin->prepare_directory_tree( $this->root, $excluded );

		$this->assertSame( 'dir-b', $nodes[0]['text'] );
		$this->assertTrue( $nodes[0]['children'], 'Directories keep their lazy-load marker.' );

		$this->assertSame( 'file-a.png', $nodes[1]['text'] );
		$this->assertSame( $this->root . '/file-a.png', $nodes[1]['data']['path'] );
		$this->assertTrue(
			$nodes[1]['state']['selected'],
			'Preselected state must survive the reorder.'
		);
	}

	public function test_empty_directory_returns_empty_array() {
		$this->assertSame( [], $this->admin->prepare_directory_tree( $this->root ) );
	}
}
