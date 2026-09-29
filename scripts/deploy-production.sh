#!/usr/bin/env bash

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
deploy_root="/home/ellabohr/public_html"

if [[ ! -d "$deploy_root" || ! -f "$deploy_root/index.php" ]]; then
    echo "Refusing to deploy: expected Ellabo document root was not found." >&2
    exit 1
fi

rsync_options=(
    --archive
    --no-owner
    --no-group
    --checksum
    --from0
    --files-from=-
    --exclude-from="$repo_root/.deploy-excludes"
)

if [[ "${1:-}" == "--dry-run" ]]; then
    rsync_options+=(--dry-run --itemize-changes)
fi

cd "$repo_root"
git ls-files -z | /usr/bin/rsync "${rsync_options[@]}" "$repo_root/" "$deploy_root/"
