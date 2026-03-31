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

# Changelog

All notable changes to this SDK will be documented in this file.

## [Unreleased]

## [0.1.2] — 2026-03-31

### Added

- Opt-in live integration tests with `composer test:live` and a manual GitHub Actions workflow.
- OTA release notes and version bump input handling for env-driven release automation.

### Changed

- Simplified the public README release guidance and added a compact support matrix.
- Fixed the release workflow chain so version bumps create a tag and the tag run creates the GitHub Release.
- Simplified the release docs to center the canonical OTA-driven version bump path.

## [0.1.0] — 2026-03-16

### Added

- Typed request objects for the canonical write flow (`CreateCreatorRequest`, `CreateLinkRequest`, `IssueInfluenceIntentTokenRequest`, `LockPurchaseIntentRequest`, `RecordPaidOrderRequest`, `RecordRefundRequest`).
- Typed list filter objects (`ListCreatorsFilter`, `ListLinksFilter`, `ListOrdersFilter`).
- `NotFoundError` for API `404` responses.
- `BodyType` backed enum replacing string constants on `TransportRequest`.
- `Page` implements `Countable` and `IteratorAggregate` for natural PHP iteration.
- `Qredex::SDK_VERSION` constant for reliable version identification.
- `@throws` docblocks on all public resource methods.
- Retry policy support for `Retry-After`, jitter, and read/network retry coverage.
- Correlation id hooks via `requestIdFactory` and `requestIdHeader`.
- CI, security policy, contribution guidelines, and release workflows.
- 92 unit tests covering validators, config, error factory, models, and request objects.
- PHPStan level 8 with zero errors.
- Apache-2.0 copyright headers on all source files.

### Changed

- `Qredex::bootstrap()` simplified to environment-only convenience (`?array $env` parameter only). Use `Qredex::init(QredexConfig::fromEnvironment(...))` for typed customization.
- Split local request validation, API validation, and response decoding into distinct error types.
- Promoted `QredexConfig::fromEnvironment()` as the preferred typed initialization path.
- Removed raw auth entrypoints and non-canonical intent lookup helpers so the public API stays focused on the Integrations flow.
- Eliminated double validation when using typed request objects.
- Marked `OAuthToken` as `@internal` — not part of the public SDK surface.

### Removed

- `$overrides` bag parameter from `Qredex::bootstrap()`.
- `TokenProvider::normalizeScope()` dead code.
