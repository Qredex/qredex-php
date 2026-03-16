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

# Contributing

This repository publishes the public Qredex PHP Integrations SDK. Contributions should preserve the SDK as a focused server-side package for the canonical Qredex backend flow.

## Ground Rules

- Keep the SDK Integrations-only. Do not add Merchant, Internal, browser, or embedded-app flows.
- Prefer fewer public primitives with stronger typing over convenience sprawl.
- Treat public method names, argument shapes, and DTOs as product surface, not local implementation details.
- Avoid logging or exposing secrets, bearer tokens, IITs, or PITs.

## Local Validation

Run the repository checks before opening a pull request:

```bash
composer install
composer test
composer analyse
```

## Pull Requests

- Include tests for every behavior change.
- Update `README.md`, docs, and examples when public behavior changes.
- Document deprecations and breaking changes explicitly in `CHANGELOG.md`.
