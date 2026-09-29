#!/usr/bin/env bash

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
deploy_root="/home/ellabohr/public_html"

if [[ ! -d "$deploy_root" || ! -f "$deploy_root/index.php" ]]; then
    echo "Refusing to deploy: expected Ellabo document root was not found." >&2
    exit 1
fi

rsync_options=(
    --recursive
    --links
    --safe-links
    --perms
    --checksum
    --from0
    --files-from=-
    --exclude-from="$repo_root/.deploy-excludes"
)

if [[ "${1:-}" == "--dry-run" ]]; then
    rsync_options+=(--dry-run --itemize-changes)
fi

cd "$repo_root"
git ls-files -z | while IFS= read -r -d '' tracked_file; do
    case "$tracked_file" in
        .git* | .cpanel.yml | .deploy-excludes | README.md | scripts/* \
        | config.php | admin/config.php | config.example.php | admin/config.example.php \
        | admin/model/extension/payment/pp_express.php \
        | admin/view/javascript/ckeditor_full/plugins/leaflet/* \
        | system/fmanager/config/config*.php \
        | image/* | xml/* | data_sample/* | system/cache_mfp/* \
        | system/storage/cache/* | system/storage/logs/* \
        | system/storage/modification/* | system/storage/session/* \
        | system/storage/upload/* | system/storage/download/* \
        | catalog/view/theme/zeexo/skins/store_default/*/settings.json)
            continue
            ;;
    esac

    printf '%s\0' "$tracked_file"
done | /usr/bin/rsync "${rsync_options[@]}" "$repo_root/" "$deploy_root/"
