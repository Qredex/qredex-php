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

## [0.1.0] — 2026-03-16

### Added

- Typed request objects for the canonical write flow.
- Retry policy support for `Retry-After`, jitter, and read/network retry coverage.
- Correlation id hooks and public-project hygiene files for CI, security, and contribution workflows.

### Changed

- Split local request validation, API validation, and response decoding into distinct error types.
- Promoted `QredexConfig::fromEnvironment()` as the preferred typed initialization path.
- Removed raw auth entrypoints and non-canonical intent lookup helpers so the public API stays focused on the Integrations flow.
