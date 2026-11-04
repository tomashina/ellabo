# Cloudflare scheduled import policy

`scheduled-imports.json` is the reviewed desired configuration, prepared on 4 November 2026. It contains no job keys, credentials or complete authenticated job URLs. The existing deployment exclusions keep this directory outside the public website. It does not apply Cloudflare changes automatically.

The single Configuration Rule sets only `security_level: off` for `GET` requests to `www.ellabo.hr` with exactly one `route` and one nonempty `key`. EasyCron's published addresses are bound to the stock and unified specials endpoints; cron-job.org's addresses are bound to the product import endpoint. Every endpoint continues to validate its existing backend key. No order export or general admin exception is included.

The addresses were fetched directly from the providers' [EasyCron feed](https://www.easycron.com/ips.json) and [cron-job.org feed](https://api.cron-job.org/executor-nodes.json) on 4 November 2026. Recheck both feeds before future changes; update the addresses and expression together through review, validation, commit and push.

Apply only the committed rule through Cloudflare's Configuration Rules interface, using the exact expression and action in the manifest. Inspect any later matching rule for conflicting settings. Verify the existing jobs retain their keys and receive successful application responses; a login page or HTTP 200 challenge page does not demonstrate a successful import. Record confirmed application status separately from this prepared manifest.

Global `security_level: under_attack` and Bot Fight Mode are enabled. All three scheduled jobs passed with Under Attack mode on; ordinary GET requests without cookies to both `www.ellabo.hr` and `ellabo.hr` received HTTP 403 with `cf-mitigated: challenge`. The verification record contains the timestamps, durations, application results and code revision for each observation.

Production was checked on 4 November 2026: the admin configuration uses `xml_ellabo_artikli2.xml` for products, `xml_ellabo_artikli1.xml` for stock, and `xml_ellabo_artikli11.xml` plus `xml_ellabo_artikli12.xml` for specials, all on `www.ellabo.hr`. The catalog specials job instead reads `xml_ellabo_artikli1.xml` on `www.ellabo.hr` and `xml_ellabo_artikli2.xml` on `ellabo.hr`. All files exist locally under `/xml/`.

The shared `ImportFeed` reader maps both own hostnames to the same four strictly supported local paths. Missing, invalid, oversized and unsupported singleton feeds fail before catalog changes; a stock feed with no matching products also fails without disabling products. Feed failures return HTTP 503 for scheduler detection. The first catalog specials test exposed an apex-host HTTP fallback and returned HTTP 503. Commit `5394dba` corrected that mapping, was deployed and validated against both catalog feeds, and the EasyCron retry passed. The verification record preserves both attempts. The Cloudflare exception remains limited to the three scheduler endpoints on `www.ellabo.hr`; it does not exempt XML feed paths or the apex hostname. Validate active admin **and catalog** mappings before future feed or protection changes.

Cloudflare documents the [`security_level` configuration setting](https://developers.cloudflare.com/rules/configuration-rules/settings/), [array-aware `len()`](https://developers.cloudflare.com/ruleset-engine/rules-language/functions/#len), and [query arguments](https://developers.cloudflare.com/ruleset-engine/rules-language/fields/reference/http.request.uri.args/). The query comparisons are exact and case sensitive; encoded or duplicate route/key arguments do not gain a wider exception.

Execution evidence and remaining checks are recorded in [the 4 November 2026 verification record](verification-2026-11-04.json).
