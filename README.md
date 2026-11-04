# Ellabo webshop

Private source repository for the Ellabo OpenCart webshop.

The initial snapshot was created from the production server on 2026-09-29. Customer/order exports, uploaded files, logs, caches, generated modifications, product media, backups, and active credentials are intentionally excluded.

## Configuration

Copy `config.example.php` to `config.php` and `admin/config.example.php` to `admin/config.php`, then provide environment-specific paths and credentials. Never commit the active configuration files.

This is a legacy application without complete Composer manifests, so the existing vendor directories are retained in the repository for reproducibility.

## Production safety

Deploy only reviewed commits. Keep XML order exports and all OpenCart runtime storage outside Git.

## Product editor recovery checks

Run `node scripts/tests/product-grid-ajax.test.js`, `php scripts/tests/database-unavailable.php`, and `php scripts/tests/theme-options-refine-search.php` before deploying changes to the product grid or database bootstrap.

## Database capacity protection

Catalog requests acquire a nonblocking request slot before connecting to the database. The default of 12 leaves room below the observed 30-connection account limit, including catalog routes that also open a lazy Eloquent connection. Administration bypasses this catalog limit. Excess catalog traffic receives HTTP 503 and a retry hint, rather than consuming the remaining database connections. Sessions are written and existing database clients closed before a slot is released. `CATALOG_DB_REQUEST_LIMIT` may override the default with a positive integer once hosting capacity is known.

Malformed category chains redirect to the final category's real ancestry before rendering. Theme settings load once per request, avoiding repeated JSON parsing.

Additional checks: `php scripts/tests/catalog-request-gate.php`, `php scripts/tests/category-path-canonical.php`, `php scripts/tests/theme-options-settings.php`, and `php scripts/tests/modification-overrides.php`.

## Git deployment

Review and test changes locally, commit and push, then update the server's managed repository with a fast-forward pull. Run `bash scripts/deploy-production.sh --dry-run` and inspect the result before invoking the same tracked script without `--dry-run`. Do not copy or edit production source files separately.

The deployment script validates reviewed source deltas against active OCMOD overrides before publishing, preserves extension additions, backs up changed runtime files outside the document root, and publishes dependencies before the framework. Unknown override changes stop deployment. The script also maintains the sanitized MySQLi connection fix. The individual override helpers are internal deployment steps, not alternate publication procedures. A future OCMOD refresh rebuilds the same fixes from tracked source.
