#!/usr/bin/env bash

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
deploy_root="/home/ellabohr/public_html"

if [[ ! -d "$deploy_root" || ! -f "$deploy_root/index.php" ]]; then
    echo "Refusing to deploy: expected Ellabo document root was not found." >&2
    exit 1
fi

cd "$repo_root"
if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "Refusing to deploy uncommitted source changes." >&2
    exit 1
fi

# OCMOD files override tracked source. Validate reviewed deltas before publication.
php "$repo_root/scripts/sync-modification-overrides.php" "$deploy_root" --check
mysqli_override="$deploy_root/system/storage/modification/system/library/db/mysqli.php"
if [[ -f "$mysqli_override" ]]; then
    mysqli_check_dir="$(mktemp -d)"
    trap 'rm -rf "$mysqli_check_dir"' EXIT
    cp -p "$mysqli_override" "$mysqli_check_dir/mysqli.php"
    php "$repo_root/scripts/patch-generated-mysqli.php" "$mysqli_check_dir/mysqli.php"
    rm -rf "$mysqli_check_dir"
    trap - EXIT
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
else
    backup_root="/home/ellabohr/deploy-backups"
    mkdir -p "$backup_root"
    chmod 700 "$backup_root"
    backup_dir="$(mktemp -d "$backup_root/$(git rev-parse --short HEAD)-XXXXXX")"
    git rev-parse HEAD > "$backup_dir/deploy-commit.txt"
    tar -cf "$backup_dir/source-before.tar" -C "$deploy_root" \
        system/framework.php system/library/db.php system/library/themeoptions.php \
        catalog/controller/startup/seo_url.php \
        admin/controller/extension/module/product_quick_edit.php \
        catalog/controller/extension/module/ag_specials_cron.php
    if [[ -f "$mysqli_override" ]]; then
        tar -rf "$backup_dir/source-before.tar" -C "$deploy_root" \
            system/storage/modification/system/library/db/mysqli.php
    fi
    # Publish shared dependencies before controllers or the framework use them.
    /usr/bin/rsync --perms --checksum \
        "$repo_root/system/library/catalogrequestgate.php" \
        "$repo_root/system/library/importfeed.php" "$deploy_root/system/library/"
    /usr/bin/rsync --perms --checksum \
        "$repo_root/system/library/db.php" "$deploy_root/system/library/"
    php "$repo_root/scripts/sync-modification-overrides.php" "$deploy_root" --apply "$backup_dir"
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
        | system/storage/cache/* | system/storage/logs/* | system/storage/locks/* \
        | system/storage/modification/* | system/storage/session/* \
        | system/storage/upload/* | system/storage/download/* \
        | catalog/view/theme/zeexo/skins/store_default/*/settings.json)
            continue
            ;;
    esac

    printf '%s\0' "$tracked_file"
done | /usr/bin/rsync "${rsync_options[@]}" "$repo_root/" "$deploy_root/"

if [[ "${1:-}" != "--dry-run" ]]; then
    if [[ -f "$deploy_root/system/storage/modification/system/library/db/mysqli.php" ]]; then
        php "$repo_root/scripts/patch-generated-mysqli.php" \
            "$deploy_root/system/storage/modification/system/library/db/mysqli.php"
    fi
    echo "DEPLOYED $(git rev-parse --short HEAD); backup: $backup_dir"
fi
