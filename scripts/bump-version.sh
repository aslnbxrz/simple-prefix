#!/bin/bash
# Version bump script for Simple Prefix (git-tag based)
# Usage: ./scripts/bump-version.sh [init|patch|minor|major]
# - init  -> creates v1.0.0 if no semver tag exists yet
# - patch/minor/major -> bumps from latest vX.Y.Z tag

set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
say()  { echo -e "${BLUE}[INFO]${NC} $*"; }
ok()   { echo -e "${GREEN}[SUCCESS]${NC} $*"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $*"; }
fail() { echo -e "${RED}[ERROR]${NC} $*"; exit 1; }

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  fail "Not a git repository."
fi

BUMP=${1:-patch}
[[ "$BUMP" =~ ^(init|patch|minor|major)$ ]] || fail "Usage: $0 [init|patch|minor|major]"

# latest semver tag (vX.Y.Z)
LATEST=$(git tag -l 'v[0-9]*.[0-9]*.[0-9]*' --sort=-v:refname | head -n1 || true)

if [[ -z "$LATEST" ]]; then
  if [[ "$BUMP" == "init" ]]; then
    NEW_TAG="v1.0.0"
    say "No tags found. Initializing $NEW_TAG"
  else
    # baseline as 1.0.0 then bump accordingly
    MA=1; MI=0; PA=0
    case "$BUMP" in
      patch) PA=1;;
      minor) MI=1;;
      major) MA=2; MI=0; PA=0;;
    esac
    NEW_TAG="v$MA.$MI.$PA"
    say "No tags found. Creating $NEW_TAG"
  fi
else
  CUR="${LATEST#v}"
  IFS='.' read -r MA MI PA <<<"$CUR"
  case "$BUMP" in
    patch) PA=$((PA+1));;
    minor) MI=$((MI+1)); PA=0;;
    major) MA=$((MA+1)); MI=0; PA=0;;
    init)  fail "init is only for repos without tags";;
  esac
  NEW_TAG="v$MA.$MI.$PA"
  say "Current tag: $LATEST"
  say "New tag:     $NEW_TAG"
fi

# Clean or auto-commit
if [ -n "$(git status --porcelain)" ]; then
  warn "Uncommitted changes detected. Committing them..."
  git add -A
  git commit -m "chore: prepare $NEW_TAG"
fi

say "Running tests..."
composer test
ok "Tests passed."

say "Creating tag $NEW_TAG..."
git tag -a "$NEW_TAG" -m "Release $NEW_TAG"

say "Pushing commits and tags..."
git push origin HEAD
git push origin "$NEW_TAG"

ok "Released $NEW_TAG 🎉  Packagist will auto-update."