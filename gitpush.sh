#!/usr/bin/env bash
set -euo pipefail

npm run build
git add -A

files=$(git diff --cached --name-only)
scope=$(echo "$files" | sed 's|/.*||' | sort -u | head -3 | tr '\n' ',' | sed 's/,$//')
# ponytail: commit type inferred from file patterns
if echo "$files" | grep -qE '^database/|migration'; then
  prefix="feat"
elif echo "$files" | grep -qE 'fix|bug|hotfix'; then
  prefix="fix"
elif echo "$files" | grep -qE 'test/'; then
  prefix="test"
elif echo "$files" | grep -qE '\.(css|js|vue|blade\.php)$'; then
  prefix="feat"
else
  prefix="chore"
fi

n=$(echo "$files" | wc -l | tr -d ' ')
git commit -m "${prefix}(${scope}): update ${n} file(s)"
git push
