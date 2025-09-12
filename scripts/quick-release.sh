#!/bin/bash
set -euo pipefail

echo "🚀 Quick Release (patch)"
if [ -n "$(git status --porcelain)" ]; then
  echo "📝 Committing local changes..."
  git add -A
  git commit -m "chore: pre-release changes"
fi

./scripts/bump-version.sh patch

echo "✅ Done."