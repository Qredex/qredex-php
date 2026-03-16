#!/usr/bin/env bash
#    ▄▄▄▄
#  ▄█▀▀███▄▄              █▄
#  ██    ██ ▄             ██
#  ██    ██ ████▄▄█▀█▄ ▄████ ▄█▀█▄▀██ ██▀
#  ██  ▄ ██ ██   ██▄█▀ ██ ██ ██▄█▀  ███
#   ▀█████▄▄█▀  ▄▀█▄▄▄▄█▀███▄▀█▄▄▄▄██ ██▄
#        ▀█
#
#  Copyright (C) 2026 — 2026, Qredex, LTD. All Rights Reserved.
#
#  DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
#
#  Licensed under the Apache License, Version 2.0. See LICENSE for the full license text.
#  You may not use this file except in compliance with that License.
#  Unless required by applicable law or agreed to in writing, software distributed under the
#  License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
#  either express or implied. See the License for the specific language governing permissions
#  and limitations under the License.
#
#  If you need additional information or have any questions, please email: copyright@qredex.com

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
QREDEX_PHP="${SCRIPT_DIR}/src/Qredex.php"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

usage() {
    cat <<EOF
Usage: $0 <version> [--skip-validation]

Bump SDK_VERSION in src/Qredex.php to the specified version.

Arguments:
  <version>           New semantic version (e.g., 0.2.0 or v0.2.0)
  --skip-validation   Skip running composer check before committing

Examples:
  $0 0.2.0
  $0 v0.2.1
  $0 1.0.0 --skip-validation

EOF
    exit 1
}

if [[ $# -lt 1 ]]; then
    usage
fi

# Parse arguments
NEW_VERSION="$1"
SKIP_VALIDATION=false

if [[ $# -ge 2 ]] && [[ "$2" == "--skip-validation" ]]; then
    SKIP_VALIDATION=true
fi

# Normalize version (remove 'v' prefix if present)
NEW_VERSION="${NEW_VERSION#v}"

# Validate semantic version format
if [[ ! "$NEW_VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo -e "${RED}Error: Version must be in semantic version format (e.g., 0.2.0)${NC}" >&2
    exit 1
fi

# Extract current version
CURRENT_VERSION=$(sed -n "s/.*public const SDK_VERSION = '\([0-9]*\.[0-9]*\.[0-9]*\)'.*/\1/p" "$QREDEX_PHP")

if [[ -z "$CURRENT_VERSION" ]]; then
    echo -e "${RED}Error: Could not extract current SDK_VERSION from $QREDEX_PHP${NC}" >&2
    exit 1
fi

echo -e "${YELLOW}Current version: ${CURRENT_VERSION}${NC}"
echo -e "${YELLOW}New version:     ${NEW_VERSION}${NC}"
echo

# Check if versions are the same
if [[ "$CURRENT_VERSION" == "$NEW_VERSION" ]]; then
    echo -e "${RED}Error: New version is the same as current version${NC}" >&2
    exit 1
fi

# Check for uncommitted changes
if ! git diff --quiet HEAD; then
    echo -e "${RED}Error: You have uncommitted changes. Please commit or stash them first.${NC}" >&2
    git status --short
    exit 1
fi

# Update the version in Qredex.php (portable sed -i for macOS and Linux)
echo "Updating SDK_VERSION in $QREDEX_PHP..."
if sed --version >/dev/null 2>&1; then
  # GNU sed (Linux)
  sed -i "s/public const SDK_VERSION = '${CURRENT_VERSION}'/public const SDK_VERSION = '${NEW_VERSION}'/" "$QREDEX_PHP"
else
  # BSD sed (macOS)
  sed -i '' "s/public const SDK_VERSION = '${CURRENT_VERSION}'/public const SDK_VERSION = '${NEW_VERSION}'/" "$QREDEX_PHP"
fi

# Verify the change
NEW_VERSION_CHECK=$(sed -n "s/.*public const SDK_VERSION = '\([0-9]*\.[0-9]*\.[0-9]*\)'.*/\1/p" "$QREDEX_PHP")
if [[ "$NEW_VERSION_CHECK" != "$NEW_VERSION" ]]; then
    echo -e "${RED}Error: Version update failed. Expected ${NEW_VERSION}, got ${NEW_VERSION_CHECK}${NC}" >&2
    git checkout "$QREDEX_PHP"
    exit 1
fi

echo -e "${GREEN}✓ Updated SDK_VERSION to ${NEW_VERSION}${NC}"

# Run validation unless skipped
if [[ "$SKIP_VALIDATION" == false ]]; then
    echo
    echo "Running composer check..."
    if ! composer check; then
        echo -e "${RED}Error: composer check failed. Rolling back changes.${NC}" >&2
        git checkout "$QREDEX_PHP"
        exit 1
    fi
    echo -e "${GREEN}✓ All checks passed${NC}"
fi

# Stage the change
git add "$QREDEX_PHP"

echo
echo -e "${GREEN}Version bumped to ${NEW_VERSION}${NC}"
echo
echo "Next steps:"
echo "  1. Update CHANGELOG.md with release notes"
echo "  2. Review changes: git diff --staged"
echo "  3. Commit and push:"
echo "       git commit -m 'Bump version to ${NEW_VERSION}'"
echo "       git push origin main"
echo
echo "Automation will then:"
echo "  - Create tag v${NEW_VERSION}"
echo "  - Run tests and validation"
echo "  - Create GitHub release"
echo "  - Notify Packagist"
