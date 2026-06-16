# Infinite Uploads — Test Suite

Brain Monkey + Mockery + PHPUnit 9. Unit-test only — no MySQL, no full
WordPress bootstrap. Runs in well under a second.

## Running

```bash
composer install            # required after every clone — vendor/ is not committed
composer test               # runs all tests
composer test:coverage      # runs all tests + text coverage summary
vendor/bin/phpunit          # equivalent to `composer test`
vendor/bin/phpunit --testdox tests/Unit/HelperTest.php   # one file, readable output
```

> **Note**: `vendor/` is `.gitignore`d except for `vendor/Aws3/` (the
> php-scoper output for our vendor-prefixed AWS SDK). PHPUnit, Brain Monkey,
> and the rest of the dev deps are installed on demand by `composer install`.
> CI (`.github/workflows/build.yml`) does this automatically before zipping
> a release.

## Layout

```
tests/
├── README.md                       # this file
├── TestCase.php                    # base test case (Brain Monkey + Mockery
│                                   # setUp/tearDown, $wpdb mock helper)
├── bootstrap.php                   # PHPUnit bootstrap; loads Patchwork +
│                                   # composer autoload + WP constant stubs
├── fixtures/
│   └── ewww-environment.php        # minimal global stubs (InfiniteUploads
│                                   # class, infinite_uploads_enabled fn)
│                                   # so source files can be require'd
│                                   # without bringing in the AWS SDK
└── Unit/
    ├── HelperTest.php              # InfiniteUploadsHelper — pure helpers
    ├── EwwwFiltersTest.php         # EWWW integration (eio_s3_* filters)
    ├── RewriterSyncGateTest.php    # BB cache sync gate in the rewriter
    ├── FilelistTest.php            # scanner exclusion logic + BB carve-out
    ├── BackfillTest.php            # one-time backfill cron handler
    ├── RewriterTest.php            # URL rewriting core (regex pipeline,
    │                               # Smush fix pairs, REST attachment filter)
    ├── BeaverModuleTest.php        # BB folder dropdown helper (cache + escape)
    ├── MediaFoldersTest.php        # AJAX folder CRUD + dropdown cache
    │                               # invalidation on every mutation
    └── StreamWrapperTest.php       # stream-wrapper helpers (path parsing,
                                    # chunk-size, smush-path early returns)
```

Coverage today: **133 tests across 9 test classes, all green. ~170 ms wall time.**

## Approach

### Why Brain Monkey, not wp-phpunit

Brain Monkey lets us mock WordPress core functions (`get_option`,
`apply_filters`, `add_filter`, `wp_parse_url`, etc.) without spinning up a
real WP install, a MySQL test DB, or running `install-wp-tests.sh`. Tests are
fast (<1s for the whole suite), trivial to run in CI, and force us to think
in terms of *behavior* (what does this function do given these inputs?)
rather than integration (does the full WP stack still work?).

The cost: anything that requires real DB rows, real WP query objects, or
real action/filter dispatch can't be tested at unit level here. For those
cases write an integration test under a `tests/Integration/` directory using
`yoast/wp-test-utils` (not yet wired up).

### Source organisation

Some plugin code is too entangled with WordPress for unit-level testing —
the main `inc/InfiniteUploads.php` file is 2400+ lines and registers dozens
of hooks at file-load time. To make recent additions testable we extracted
focused pieces into their own files:

- `inc/ewww-integration.php` — the EWWW Image Optimizer integration filters
- `inc/bb-cache-integration.php` — the Beaver Builder cache flow (photo
  hook, cron push, backfill)

Each is `require_once`'d from `inc/InfiniteUploads.php` and is otherwise
identical to the inline code it replaced.

### Patterns

**Pure-function tests** (e.g. `InfiniteUploadsHelper::is_offloadable_bb_cache_image`)
— straightforward `@dataProvider` + truth tables. See `HelperTest.php`.

**Function callbacks that use WP options** (e.g. EWWW integration helpers) —
declare named functions (not anonymous closures) in the source file so tests
can invoke them directly with `Functions\when()` stubs for WP. See
`EwwwFiltersTest.php` and the named `filter_eio_s3_active` /
`filter_eio_s3_object_prefix` in `inc/ewww-integration.php`.

**Protected methods on a class with a heavy constructor** (e.g.
`InfiniteUploadsRewriter::is_bb_cache_synced`) — use
`ReflectionClass::newInstanceWithoutConstructor()` to skip the constructor,
then `ReflectionMethod::setAccessible(true)` to invoke. See
`RewriterSyncGateTest.php`.

**Code that touches `$wpdb`** — use the `mock_wpdb()` helper on the base
test class; it returns a Mockery mock with `prepare()` pre-stubbed to
substitute `%s`/`%d`. Set expectations on `query()`, `get_col()`,
`get_results()` as needed. See `RewriterSyncGateTest.php` and
`BackfillTest.php`.

**Code that walks the filesystem** — write a real temp directory under
`WP_CONTENT_DIR` (which `tests/bootstrap.php` aims at `sys_get_temp_dir()`),
populate it in `setUp()`, tear it down in `tearDown()`. See
`BackfillTest.php`.

**Assertions vs Mockery** — PHPUnit in strict mode flags tests that have no
PHPUnit assertions as "risky", even when they have Mockery expectations.
For each Mockery expectation, capture the call's argument into a variable
via `andReturnUsing()` or `Functions\when()->alias()`, then assert against
the variable with `$this->assert…()`. Examples throughout `BackfillTest.php`.

## Adding a new test class

1. Decide what's being tested — a class? a function? a hook callback?
2. If the unit under test is hard to load in isolation (file-level side
   effects, heavy constructor), extract it into its own file under `inc/`
   following the pattern of `inc/ewww-integration.php`.
3. Create `tests/Unit/<Name>Test.php` extending `ClikIT\InfiniteUploads\Tests\TestCase`.
4. In `setUp()`, stub the WP functions the unit calls via
   `Brain\Monkey\Functions\when()->justReturn()` or `->alias()`.
5. Write per-method tests. Prefer `@dataProvider` for truth-tables.
6. Run `vendor/bin/phpunit tests/Unit/<Name>Test.php --testdox` until green.

## What's covered

| Area | Test class | Notes |
|---|---|---|
| BB carve-out predicate, exclusion settings, media-folders setting | `HelperTest` | 26 tests including a full truth-table for `is_offloadable_bb_cache_image` |
| EWWW `eio_s3_*` filters + Easy IO guard | `EwwwFiltersTest` | 16 tests; vanity/path CDN URLs, Easy IO bypass |
| BB cache sync gate in the rewriter | `RewriterSyncGateTest` | 9 tests; DB cursor, query-string handling, anchored LIKE |
| Filelist exclusion logic | `FilelistTest` | 28 tests; default + compat + filter exclusions, BB carve-out short-circuit |
| Chunked backfill cron handler | `BackfillTest` | 8 tests; real temp dirs, DB cursor, scheduling |
| Rewriter URL replacement core | `RewriterTest` | 18 tests; protocolize/relative_url helpers, full HTML rewrite pipeline, Smush fix pairs, REST attachment filter |
| BB folder dropdown cache + escape | `BeaverModuleTest` | 5 tests; cache hit/miss, escaping, query shape |
| MediaFolders AJAX CRUD + cache invalidation | `MediaFoldersTest` | 9 tests; create/rename/delete/bulk-delete all invalidate `iu_bb_folder_options` |
| Stream wrapper path helpers + chunk size | `StreamWrapperTest` | 14 tests; getBucketKey, initProtocol, normalizeSmushPath, calculate_chunk_size |

## What's not yet covered

- **`infinite_uploads_bb_cache_push()`** — covered indirectly via the
  backfill kicking it; a direct test would mock the AWS Transfer/S3 client.
- **`InfiniteUploadsStreamWrapper` full flow** (stream_open / stream_read /
  stream_write / dir_opendir etc.) — needs heavy AWS SDK mocking.
- **`InfiniteUploadsApiHandler`** — HTTP responses from the IU API.
  Needs `wp_remote_get` / `wp_remote_post` mocks.
- **`InfiniteUploadsVideo`** — video upload + encoding flow.
- **`InfiniteUploadsAdmin::ajax_*` handlers** — the sync engine. Each AJAX
  handler is large; favour testing the small private helpers first.
- **`InfiniteUploadsWPCLICommand`** — WP-CLI subcommands. Same story as
  admin AJAX.
- **`MediaFoldersGallery` + the per-builder integration files** (Elementor,
  Divi, Bricks, Oxygen, Brizy) — mostly thin adapters; integration tests
  would catch the real bugs.

## Coverage report

```bash
composer test:coverage
```

Coverage XML can be generated via `phpunit --coverage-xml coverage/` when
xdebug or pcov is available. Coverage in CI would require either of those
PHP extensions; not currently wired up.
