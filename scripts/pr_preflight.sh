#!/usr/bin/env bash
set -euo pipefail

echo "== PR Preflight =="

if ! git rev-parse --git-dir >/dev/null 2>&1; then
  echo "ERROR: Not inside a git repository."
  exit 1
fi

branch="$(git rev-parse --abbrev-ref HEAD)"
echo "Branch: ${branch}"
if [[ "${branch}" == "HEAD" ]]; then
  echo "ERROR: Detached HEAD. Checkout a branch before creating a PR."
  exit 1
fi

echo
echo "-- Working tree status --"
git status --short

echo
echo "-- Upstream check --"
if git rev-parse --abbrev-ref --symbolic-full-name '@{u}' >/dev/null 2>&1; then
  upstream="$(git rev-parse --abbrev-ref --symbolic-full-name '@{u}')"
  echo "Upstream: ${upstream}"
else
  echo "WARNING: No upstream configured for ${branch}."
  echo "Hint: git push -u origin ${branch}"
fi

echo
echo "-- Commit health --"
latest_commit="$(git rev-parse --short HEAD)"
echo "Latest commit: ${latest_commit}"
if [[ -z "$(git log -1 --pretty=%B | tr -d '[:space:]')" ]]; then
  echo "WARNING: Latest commit message appears empty."
fi

echo
echo "-- Binary diff check (can break some PR UIs) --"
if git diff --numstat HEAD~1..HEAD 2>/dev/null | awk '$1=="-" || $2=="-" {found=1} END {exit !found}'; then
  echo "WARNING: Binary files detected in latest commit."
  echo "Hint: prefer SVG/text assets where possible."
else
  echo "OK: No binary files in latest commit."
fi

echo
echo "-- Suggested recovery steps if PR create still fails --"
cat <<'EOF'
1) Ensure branch is pushed:
   git push -u origin $(git rev-parse --abbrev-ref HEAD)

2) Retry PR with short title/body first.

3) If UI still fails, refresh auth/session and retry.

4) Re-run this script and verify:
   - non-detached branch
   - upstream configured
   - clean push state
   - no problematic binary diff
EOF

echo
echo "PR preflight complete."
