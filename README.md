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

The generated OCMOD MySQLi driver can override the source driver. After backing it up and deploying the source, run `php scripts/patch-generated-mysqli.php /home/ellabohr/public_html/system/storage/modification/system/library/db/mysqli.php` if that file exists. The helper preserves OCMOD settings, refuses unknown blocks, and is safe to repeat. A future modification refresh generates the same connection fix from the source driver.
