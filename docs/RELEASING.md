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

This repo is a single-package Composer SDK. The release source of truth is the Git tag.

## Local Release Commands

1. Update [CHANGELOG.md](../CHANGELOG.md).
2. Run local verification:
   - `composer validate --strict`
   - `composer install --no-interaction --prefer-dist`
   - `composer test`
   - `composer analyse`
   - `composer install --no-interaction --prefer-dist --no-dev`
3. Run the live integration suite against staging when credentials are available:
   - start from [`.env.live.example`](../.env.live.example)
   - `QREDEX_LIVE_ENABLED=1`
   - `QREDEX_LIVE_ENVIRONMENT=staging`
   - `QREDEX_LIVE_CLIENT_ID=...`
   - `QREDEX_LIVE_CLIENT_SECRET=...`
   - `QREDEX_LIVE_STORE_ID=...`
   - `composer test:live`
4. Create the release tag:
   - GitHub Actions: `Create release tag`
   - or push `vX.Y.Z` yourself if you are doing recovery work

## Automated GitHub Flow

The PHP release flow is GitHub-driven and tag-based:

1. [`.github/workflows/create-release-tag.yml`](../.github/workflows/create-release-tag.yml)
   - creates `vX.Y.Z`
2. [`.github/workflows/publish-packagist.yml`](../.github/workflows/publish-packagist.yml)
   - validates the package
   - installs dependencies
   - runs `composer test`
   - runs `composer analyse`
   - performs a `--no-dev` package smoke install
   - creates the GitHub Release
   - optionally notifies Packagist through `PACKAGIST_WEBHOOK_URL`

## Live Tests

- `composer test` excludes the live suite.
- `composer test:live` runs only the live suite.
- [`.github/workflows/live-tests.yml`](../.github/workflows/live-tests.yml) is manual-only and keeps live credentials out of the default CI and release paths.
