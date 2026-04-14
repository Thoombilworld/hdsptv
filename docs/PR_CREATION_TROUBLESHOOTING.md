# Pull Request Creation Troubleshooting

If you see:

> **Failed to create pull request. Please try again.**

Use this checklist.

## Quick checks

1. Confirm you are on a normal branch (not detached HEAD).
2. Confirm your branch has an upstream remote configured.
3. Confirm latest commits are pushed.
4. Confirm your latest commit does not include problematic binary diffs for the current PR UI/workflow.

## One-command preflight

Run:

```bash
bash scripts/pr_preflight.sh
```

This script checks branch state, upstream tracking, basic commit health, and binary-diff risk.

## Common fixes

- Push branch with upstream:
  ```bash
  git push -u origin <branch>
  ```

- Retry with a shorter PR title/body first.
- Re-authenticate your Git provider session and retry.
- If binary files are the issue, replace with text-based assets when possible (e.g., SVG instead of PNG for simple marks/icons).

## Notes for HDSPTV repo

- This repository includes large UI updates; if PR UI fails intermittently, verify branch sync and reduce PR metadata length before retrying.
