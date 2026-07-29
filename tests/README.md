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
    ├── HelperTest.php              # InfiniteUploadsHelper — BB carve-out
    │                               # predicate + setting toggles
    ├── HelperPathsTest.php         # InfiniteUploadsHelper — path/URL
    │                               # resolution (local↔cloud, exclusions,
    │                               # filename extraction, memoization)
    ├── EwwwFiltersTest.php         # EWWW integration (eio_s3_* filters)
    ├── RewriterSyncGateTest.php    # BB cache sync gate in the rewriter
    ├── FilelistTest.php            # scanner exclusion logic + BB carve-out
    ├── BackfillTest.php            # one-time backfill cron handler
    ├── BbCachePhotoCroppedTest.php # fl_builder_photo_cropped action handler
    ├── RewriterTest.php            # URL rewriting core (regex pipeline,
    │                               # Smush fix pairs, REST attachment filter)
    ├── RewriterConstructorTest.php # Rewriter constructor — Smush URL
    │                               # fix-pair construction (vanity + path-style)
    ├── BeaverModuleTest.php        # BB folder dropdown helper (cache + escape)
    ├── MediaFoldersTest.php        # MediaFolders AJAX handlers — CRUD,
    │                               # move, bulk-move, sort, color,
    │                               # upload-folder, move-media + cache
    │                               # invalidation on mutation
    ├── StreamWrapperTest.php       # stream-wrapper helpers (path parsing,
    │                               # chunk-size, smush-path early returns)
    └── ApiHandlerTest.php          # InfiniteUploadsApiHandler — token/site_id
                                    # storage, URL construction, get_site_data
                                    # caching behaviour
```

Coverage today: **216 tests across 13 test classes, all green. ~220 ms wall time.**

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
| BB carve-out predicate, exclusion/media-folder settings | `HelperTest` | 26 tests including full truth-table for `is_offloadable_bb_cache_image` |
| Helper path/URL resolution (local↔cloud, exclusions, filename, memoization) | `HelperPathsTest` | 29 tests; covers nearly every static method touching paths |
| EWWW `eio_s3_*` filters + Easy IO guard | `EwwwFiltersTest` | 16 tests; vanity/path CDN URLs, Easy IO bypass |
| BB cache sync gate in the rewriter | `RewriterSyncGateTest` | 9 tests; DB cursor, query-string handling, anchored LIKE |
| Filelist exclusion logic | `FilelistTest` | 28 tests; default + compat + filter exclusions, BB carve-out |
| Chunked backfill cron handler | `BackfillTest` | 8 tests; real temp dirs, DB cursor, scheduling |
| BB photo-cropped hook | `BbCachePhotoCroppedTest` | 8 tests; happy path + 6 defensive guards |
| Rewriter URL replacement core | `RewriterTest` | 18 tests; protocolize/relative_url, full HTML rewrite, Smush fix pairs, REST filter |
| Rewriter constructor — Smush fix-pair construction | `RewriterConstructorTest` | 6 tests; vanity-host + path-style CDN URL handling |
| BB folder dropdown cache + escape | `BeaverModuleTest` | 5 tests; cache hit/miss, escaping, query shape |
| MediaFolders AJAX (full coverage of mutation + read handlers) | `MediaFoldersTest` | 26 tests; create/rename/delete/bulk-delete + move/bulk-move/color/sort/upload-folder/move-media + on_add_attachment + ajax_get_folders sort modes |
| Stream wrapper path helpers + chunk size | `StreamWrapperTest` | 14 tests; getBucketKey, initProtocol, normalizeSmushPath, calculate_chunk_size |
| ApiHandler — token + site_id + URL construction + get_site_data caching | `ApiHandlerTest` | 14 tests; has_token/get_token/set_token, set_site_id, rest_url, network_*_url, get_site_data (fresh / stale / locked / no-token) |

## What's not yet covered

Everything below is either high-effort with low ROI or better suited to
integration tests against a real S3 / IU API endpoint.

- **`infinite_uploads_bb_cache_push()`** — the cron handler that actually
  pushes BB cache files to S3. Covered indirectly via the backfill kicking
  it; a direct test would need to mock the AWS `Transfer` / S3 client.
- **`InfiniteUploadsApiHandler::call`** — the HTTP layer (`wp_remote_*`).
  Token/URL/caching are covered; the actual remote call path is not.
- **`InfiniteUploadsStreamWrapper` full flow** (stream_open / stream_read /
  stream_write / dir_opendir etc.) — needs heavy AWS SDK mocking. Better
  approached with integration tests against LocalStack or MinIO.
- **`InfiniteUploadsLocalStreamWrapper`** — similar story.
- **`InfiniteUploadsVideo`** — video upload + encoding flow.
- **`InfiniteUploadsAdmin::ajax_*` handlers** — the sync engine. Each AJAX
  handler is large and mostly hits AWS Transfer / S3 client; favour
  testing the small private helpers first.
- **`InfiniteUploadsWPCLICommand`** — WP-CLI subcommands. Same story as
  admin AJAX.
- **`MediaFoldersGallery` + the per-builder integration files** (Elementor,
  Divi, Bricks, Oxygen, Brizy) — mostly thin adapters; integration tests
  would catch the real bugs.
- **`InfiniteUploads.php` main class hooks** (`setup`, `register_stream_wrapper`,
  `filter_upload_dir`, `tear_down`) — almost entirely WordPress hook
  plumbing; testing it tests WordPress more than testing IU's logic.

## Coverage report

```bash
composer test:coverage
```

Coverage XML can be generated via `phpunit --coverage-xml coverage/` when
xdebug or pcov is available. Coverage in CI would require either of those
PHP extensions; not currently wired up.
