# Infinite Uploads — E2E Tests (Playwright + wp-env)

Browser-driven end-to-end tests against a disposable WordPress environment
provisioned by `@wordpress/env` (Docker). Tests drive a real Chromium against
the IU plugin running inside that environment.

## Prerequisites

- **Docker Desktop** (or any Docker-compatible runtime) running locally
- **Node.js 18+** — Playwright 1.48+ requires it
- **npm 8+**

## First-time setup

```bash
# 1. Install dependencies (incl. Playwright + @wordpress/env)
npm install

# 2. Install Playwright's browser binaries
npx playwright install chromium

# 3. Boot the wp-env Docker environment (downloads WP + BB Lite the first time)
npm run wp-env:start
```

The first `wp-env start` takes a few minutes (WP + BB Lite downloads + DB
init). Subsequent starts are fast (~10s).

When the environment is up:

- **Tests site**: http://localhost:8890 (this is what Playwright targets)
- **Development site**: http://localhost:8889 (the same plugins, fresh DB)
- **Login**: `admin` / `password` on either

## Run the tests

```bash
npm run test:e2e               # headless, all specs
npm run test:e2e:headed        # opens Chromium so you can watch
npm run test:e2e:ui            # Playwright's interactive Test UI — best for development
npm run test:e2e:debug         # pauses on the first action, opens Inspector
npm run test:e2e:report        # open the HTML report from the last run
```

Run a single file or test:

```bash
npx playwright test tests/e2e/media-folders.spec.ts
npx playwright test -g "creates a new folder"
```

## Connection modes

Tests that depend on IU being "connected" (the CDN-delivery, file-exclusion,
and setup-and-sync specs) work in **two modes**, auto-detected per run:

### Fake mode (default — no real IU account needed)

`iuApi.connect()` seeds fake credentials into the four site options the
plugin reads at `setup()`:

```
iup_apitoken    = "fake-test-token-<random>"
iup_site_id     = 999
iup_api_data    = { site: { cdn_url: "test-cdn.iu-tests.local", ... } }
iup_enabled     = 1
```

The plugin enters its connected code path on the next request. Images get
rewritten to `https://test-cdn.iu-tests.local/...` (which won't actually
load — that's fine, we only assert on the rewritten `src` attribute).

### Real mode (test against your actual IU account)

If you connect IU manually via the wp-env admin UI, every helper auto-detects
it and **leaves the real connection alone** — `iuApi.connect()` becomes a
no-op, `iuApi.disconnect()` becomes a no-op, and assertions use your live
CDN host instead of the fake one.

#### To set up real mode

```bash
# 1. Start the test environment.
npm run wp-env:start
# Wait until you see "WordPress test site started".

# 2. Open the tests env admin UI (port 8890, NOT 8889).
open http://localhost:8890/wp-admin/upload.php?page=infinite_uploads

# 3. Click "Get Started" / "Connect" and complete the IU OAuth flow with
#    your real account. wp-env now has your real iup_apitoken etc.

# 4. Verify the connection is detected.
curl -s 'http://localhost:8890/index.php?rest_route=/iu-test/v1/state'
#    Should print: {"enabled":true,"has_token":true,...,"is_real":true,...}

# 5. Run tests. The helpers will auto-skip operations that would clobber
#    your real connection.
npm run test:e2e
```

#### Caveats of real mode

- `cdn-delivery.spec.ts → "when the plugin is disconnected, urls stay
  local"` skips itself in real mode — we won't disconnect your account
  during a test run.
- `setup-and-sync.spec.ts → "before connection..."` skips itself in real
  mode — your account is already connected.
- The "mark-synced" helper inserts a DB row claiming a file is in cloud,
  but the file isn't actually uploaded. URLs get rewritten to your real
  CDN, which then 404s — Playwright won't notice because it only checks
  the rewritten `src` attribute, not whether the image renders.
- If you want to flip back to fake mode without re-running the whole
  setup, manually wipe the options in the tests env:
  ```bash
  npx wp-env run tests-cli wp option delete iup_apitoken iup_site_id iup_api_data iup_enabled
  ```

## Daily workflow

```bash
# Start the env once when you sit down to work.
npm run wp-env:start

# Iterate on tests. Re-running is fast.
npm run test:e2e:ui

# Tear down the env when done (or just leave it running between sessions).
npm run wp-env:stop
```

If the env gets into a weird state (DB corruption, hung container):

```bash
npm run wp-env:destroy        # wipes the Docker volumes
npm run wp-env:start          # rebuild fresh
```

## Layout

```
tests/e2e/
├── README.md                      # this file
├── tsconfig.json                  # TypeScript config for the test code
├── global-setup.ts                # logs in as admin once, saves session state
├── helpers/
│   └── test.ts                    # custom Playwright fixture (iuApi, adminPage)
├── iu-test-helpers/               # custom WP plugin — REST endpoints for state setup
│   └── iu-test-helpers.php        # /iu-test/v1/reset, /folders, etc. (test-env only)
├── fixtures/
│   ├── test-image.png             # tiny PNG used by upload specs
│   └── bb-cache-seed/             # files seeded into /wp-content/uploads/bb-plugin/cache
├── media-folders.spec.ts          # CRUD: create, rename, delete, nest, filter, bulk-delete
├── media-upload.spec.ts           # upload + auto-folder assignment
├── cloud-offload.spec.ts          # settings page UI shell
├── beaver-builder.spec.ts         # BB editor + IU Gallery module
├── setup-and-sync.spec.ts         # connect + first sync user journey
├── cdn-delivery.spec.ts           # img src + browser hints on the live site
└── file-exclusions.spec.ts        # exclude → local URL, un-exclude → CDN URL
```

`.auth/` (gitignored) holds the saved admin session captured by `global-setup.ts`.
`test-results/` and `playwright-report/` (gitignored) hold failed-test artifacts.

## How tests are isolated

- **Per-test setup**: `test.beforeEach` calls `iuApi.reset()` which wipes the
  folder + relationship tables. Each spec starts from zero IU state.
- **Shared admin session**: `global-setup.ts` logs in once and persists the
  cookies. All specs reuse that session via `playwright.config.ts → use.storageState`.
  Saves ~1.5s per test.
- **No real cloud calls**: the IU API is not authenticated in this environment,
  so `infinite_uploads_enabled()` returns false. Tests exercise plugin code
  paths that don't require a live IU account.

## The `iu-test-helpers` plugin

A small must-use-style helper plugin lives under `tests/e2e/iu-test-helpers/`.
It's mounted into wp-env via the `mappings` block of `.wp-env.json`. It exposes
REST endpoints under `/wp-json/iu-test/v1/*` that let specs cheaply seed and
inspect database state instead of clicking through the UI for every setup step:

| Endpoint | Purpose |
|---|---|
| `POST /reset` | Truncate folder + relationship tables. Default `beforeEach`. |
| `POST /folders` | Create a folder by name (+ optional parent). |
| `POST /folders/<id>/media` | Attach an attachment to a folder. |
| `POST /upload-folder` | Set the user's `iu_upload_folder` meta (target for new uploads). |
| `GET /folder-count` | Quick assertion: how many folders exist? |
| `GET /attachment/<id>/folder` | What folder is an attachment in? Returns `null` if unassigned. |
| `POST /connect` | Seed `iup_apitoken`, `iup_site_id`, `iup_api_data`, `iup_enabled` so the plugin's setup() takes its "connected" code path. Optional `cdn_host` argument controls the fake CDN domain. |
| `POST /disconnect` | Delete the four options above. |
| `POST /mark-synced` | Insert a row into `wp_infinite_uploads_files` with `synced=1` for a given attachment. Simulates the post-first-sync state. |
| `POST /excluded-paths` | Set `iup_excluded_files` to the supplied list (substring matches). Empty list also flips `iu_file_exclusion_enabled` to `'no'`. |
| `POST /upload-fixture` | Copy a file from the test plugin's `fixtures/` directory into the WP uploads dir and register it as an attachment. Sidesteps the X-WP-Nonce dance required by `/wp-json/wp/v2/media`. |
| `POST /posts` | Create a post with arbitrary title/content/status. Sidesteps Gutenberg block validation. |

The plugin **refuses to load outside `WP_ENVIRONMENT_TYPE=local|development`**,
so it can't accidentally be activated in production.

## The custom test fixture

`helpers/test.ts` exports an extended `test` function that adds:

- `iuApi` — typed wrapper around the helper REST endpoints
- `adminPage` — convenience for navigating to `/wp-admin/*` paths

Example:

```typescript
import { test, expect } from './helpers/test';

test( 'does the thing', async ( { page, adminPage, iuApi } ) => {
    await iuApi.reset();
    const folder = await iuApi.createFolder( 'My Folder' );

    await adminPage.visitMediaLibrary();

    await expect( page.locator( `#folder_${ folder.id }` ) ).toBeVisible();
} );
```

## Debugging a failing test

Failures save three artifacts under `test-results/`:

- `trace.zip` — full Playwright trace (open with `npx playwright show-trace trace.zip`)
- `video.webm` — recording of the test session
- `screenshot.png` — the page at the moment of failure

The HTML report (`npm run test:e2e:report`) surfaces all of these.

For interactive debugging:

```bash
npm run test:e2e:debug                                   # pause on first step
npm run test:e2e:debug -- tests/e2e/media-folders.spec.ts # specific file
```

`test.only()` runs just one test (forbidden in CI via `forbidOnly`).

## CI considerations (deferred)

This setup is local-only by design. Wiring it into GitHub Actions would
require:

1. A workflow that boots wp-env (Docker-in-Docker on GitHub-hosted runners).
2. A way to cache the WP + BB Lite downloads between runs.
3. Browser binaries (`npx playwright install --with-deps chromium`).

When ready, add `.github/workflows/e2e.yml` following the official Playwright
GitHub Actions template plus `@wordpress/env` boot steps.

## Adding a new spec

1. Decide what scenario you're testing — pick the existing spec file it
   belongs to, or create a new `*.spec.ts`.
2. Import the custom test fixture: `import { test, expect } from './helpers/test';`
3. Reset state in `beforeEach`: `await iuApi.reset();`
4. Use `iuApi` to seed any preconditions (folders, upload-folder meta, etc.)
   rather than clicking through the UI for setup.
5. Drive the UI for the actual scenario under test.
6. Assert with `expect( page.locator(...) ).toBeVisible()` / `iuApi.foo()`.

Keep specs **independent** — never rely on ordering between tests.

## Known caveats

- **Beaver Builder editor is heavyweight**: the BB spec waits up to 60s for
  `window.FLBuilder` to initialize. If the BB editor is slow on your machine,
  consider bumping the timeout or skipping `beaver-builder.spec.ts` locally.
- **Plupload uses an iframe**: the upload spec relies on the file `input` being
  reachable from the page context. If WP changes that mechanism, the spec
  needs updating.
- **No cloud-connected paths**: anything gated on `infinite_uploads_enabled()`
  isn't exercised here. Use the unit tests (`composer test`) for that logic.
