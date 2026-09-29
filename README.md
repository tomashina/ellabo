# Ellabo webshop

Private source repository for the Ellabo OpenCart webshop.

The initial snapshot was created from the production server on 2026-09-29. Customer/order exports, uploaded files, logs, caches, generated modifications, product media, backups, and active credentials are intentionally excluded.

## Configuration

Copy `config.example.php` to `config.php` and `admin/config.example.php` to `admin/config.php`, then provide environment-specific paths and credentials. Never commit the active configuration files.

This is a legacy application without complete Composer manifests, so the existing vendor directories are retained in the repository for reproducibility.

## Production safety

Deploy only reviewed commits. Keep XML order exports and all OpenCart runtime storage outside Git.
