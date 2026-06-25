#!/usr/bin/env bash
#
# Package the plugin into an installable distribution ZIP.
#
# Produces ./infinite-uploads.zip containing only the files the plugin loads at
# runtime, wrapped in a top-level infinite-uploads/ directory. Uses an explicit
# include allowlist rather than wp-scripts plugin-zip, which ignores .distignore
# and shipped an incomplete archive (missing inc/, vendor/, libs/). Run
# `npm run build` first so build/ assets are current — the `plugin-zip` npm
# script does this automatically.
#
set -euo pipefail

PLUGIN_SLUG="infinite-uploads"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT
mkdir -p "$STAGE/$PLUGIN_SLUG"

# Include only what the plugin needs at runtime; exclude everything else.
# '/dir/***' includes the directory and all of its descendants.
rsync -a --prune-empty-dirs \
	--include='/assets/***' \
	--include='/build/***' \
	--include='/inc/***' \
	--include='/libs/***' \
	--include='/vendor/***' \
	--include='/composer.json' \
	--include='/functions.php' \
	--include='/infinite-uploads.php' \
	--include='/infinite-uploads.pot' \
	--include='/readme.txt' \
	--include='/uninstall.php' \
	--exclude='*' \
	./ "$STAGE/$PLUGIN_SLUG/"

rm -f "$ROOT/$PLUGIN_SLUG.zip"
( cd "$STAGE" && zip -rqX "$ROOT/$PLUGIN_SLUG.zip" "$PLUGIN_SLUG" )

echo "Created $ROOT/$PLUGIN_SLUG.zip"
