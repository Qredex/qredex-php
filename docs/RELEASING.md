<!--
     ▄▄▄▄
   ▄█▀▀███▄▄              █▄
   ██    ██ ▄             ██
   ██    ██ ████▄▄█▀█▄ ▄████ ▄█▀█▄▀██ ██▀
   ██  ▄ ██ ██   ██▄█▀ ██ ██ ██▄█▀  ███
    ▀█████▄▄█▀  ▄▀█▄▄▄▄█▀███▄▀█▄▄▄▄██ ██▄
         ▀█

   Copyright (C) 2026 — 2026, Qredex, LTD. All Rights Reserved.

   DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.

   Licensed under the Apache License, Version 2.0. See LICENSE for the full license text.
   You may not use this file except in compliance with that License.
   Unless required by applicable law or agreed to in writing, software distributed under the
   License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
   either express or implied. See the License for the specific language governing permissions
   and limitations under the License.

   If you need additional information or have any questions, please email: copyright@qredex.com
-->

# Releasing `qredex/php`

This repo is a single-package Composer SDK. The release source of truth is `Qredex::SDK_VERSION` in `src/Qredex.php`.

## How to release

### Quick version bump

Use the version bump script for a guided release:

```bash
# Bump version (runs validation checks automatically)
ota run version:bump --version minor
# or
OTA_INPUT_VERSION=minor ./bump-version.sh

# Skip validation if needed
OTA_INPUT_VERSION=major OTA_INPUT_SKIP_VALIDATION=true ./bump-version.sh
```

The script will:
- Update `SDK_VERSION` in `src/Qredex.php`
- Run `composer check` (unless skipped)
- Stage the change
- Display next steps

### Manual release steps

1. Update `Qredex::SDK_VERSION` in `src/Qredex.php` to the new version.
2. Update [CHANGELOG.md](../CHANGELOG.md) with the release notes.
3. Commit, push to `main`.
4. That's it. Automation handles the rest.

## What happens automatically

1. [`.github/workflows/auto-release.yml`](../.github/workflows/auto-release.yml)
   - Triggered on push to `main` when `src/Qredex.php` changes.
   - Extracts `SDK_VERSION` from source code.
   - Creates and pushes the `vX.Y.Z` tag if it does not already exist.

2. [`.github/workflows/publish-packagist.yml`](../.github/workflows/publish-packagist.yml)
   - Triggered when `Auto-release` completes successfully, or when a `vX.Y.Z` tag is pushed manually.
   - Validates the package (`composer validate --strict`).
   - Installs dependencies and runs `composer test` + `composer analyse`.
   - Performs a `--no-dev` package smoke install.
   - Creates the GitHub Release with auto-generated notes.
   - Notifies Packagist through `PACKAGIST_WEBHOOK_URL` if configured.

## Packagist Setup

### Initial Setup

1. **Submit package to Packagist:**
   - Go to https://packagist.org/packages/submit
   - Enter repository URL: `https://github.com/Qredex/qredex-php`
   - Click "Check" then "Submit"

2. **Configure auto-update webhook:**
   - On Packagist, go to your package page: https://packagist.org/packages/qredex/php
   - Click "Show API Token" in the right sidebar
   - Copy the webhook URL (looks like `https://packagist.org/api/update-package?username=...&apiToken=...`)
   - In GitHub, go to Settings → Secrets and variables → Actions
   - Create new secret: `PACKAGIST_WEBHOOK_URL` with the webhook URL

3. **Verify setup:**
   - Create a test release or trigger the workflow manually
   - Check Actions tab for workflow success
   - Check Packagist for the new version

### Troubleshooting

**Package not updating on Packagist:**
- Verify `PACKAGIST_WEBHOOK_URL` secret is set in GitHub Actions
- Check that webhook URL is valid on Packagist package settings
- Manually trigger update on Packagist if needed (click "Update" button on package page)
- Verify the repository is public or Packagist has access

**Release not created:**
- Check GitHub Actions logs for errors
- Ensure `composer check` passes locally
- Verify the tag format matches `v*.*.*` (e.g., `v0.1.0`)

## Local pre-release verification

Run these before pushing:

```bash
composer validate --strict
composer install --no-interaction --prefer-dist
composer test
composer analyse
composer install --no-interaction --prefer-dist --no-dev
```

## Live Tests

- `composer test` excludes the live suite.
- `composer test:live` runs only the live suite.
- [`.github/workflows/live-tests.yml`](../.github/workflows/live-tests.yml) is manual-only and keeps live credentials out of the default CI and release paths.
