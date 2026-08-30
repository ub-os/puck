# Site configuration: Tracking & Security

`Site Management > Sites` has a **Tracking & Security** tab. Everything is stored
in the site's `config.yaml` and deploys with the project.

| Field | Effect |
| --- | --- |
| **Head markup** | Raw HTML injected before `</head>` (consent manager, GTM loader, …). |
| **Body end markup** | Non-script HTML injected before `</body>` (`<noscript>`, pixels). |
| **Content-Security-Policy** | `off` / `report` / `enforce`. |
| **Allow inline scripts** | Adds `'unsafe-inline'`, disables hash mode. Off by default. |
| **Content-Security-Policy rules** | Line-based directive rules. |

Inline `<script>` / `<style>` in either markup field is hashed automatically and
moved to `<head>`. External `<script src>`, `<noscript>` and pixels are output
verbatim — external hosts need a CSP rule.

## CSP rollout

1. Start on **`report`**. Load the site with browser devtools open — every
   violation logs the exact directive and blocked URI to the console.
2. Add the missing hosts to **Content-Security-Policy rules**, deploy.
3. Switch to **`enforce`** once the console is clean.

`off` sends no CSP header.

## CSP rules syntax

One rule per line, `#` starts a comment:

```
<directive> [<mode>] <source> [<source> …]
```

`<mode>` (optional, default `extend`): `extend`, `append`, `set`, `remove`,
`reduce`, `inherit` — maps to TYPO3 core's `MutationMode`.

```
script-src            https://cdn.example.com https://*.matomo.cloud
img-src               data: https://*.matomo.cloud
connect-src           https://*.matomo.cloud
frame-src   remove    https://old-embed.example.com
```

The baseline is the TYPO3 core frontend policy (`'self'`, `data:` images,
YouTube-nocookie / Vimeo embeds); the field starts empty and these rules run
last, so `set` / `remove` can override anything below.

Usercentrics consent manager (`puck.enableCookieSettings`):

```
script-src  https://*.usercentrics.eu https://app.usercentrics.eu
connect-src https://*.usercentrics.eu https://api.usercentrics.eu
```

## Why hash mode

`Initialisation/Site/puck/csp.yaml` sets `useNonce: false, useHash: true`. Inline
scripts are authorised by a SHA-256 of their content — deterministic, stored with
the page cache, so responses stay **fully cacheable** (CDN, reverse proxy,
`staticfilecache`). A nonce would force `Cache-Control: private, no-store`.

**Allow inline scripts** switches to `'unsafe-inline'` + `useHash: false` (the two
are mutually exclusive). Responses stay cacheable but every inline script runs.
Prefer the default.

`csp.yaml` is puck-managed — the tab drives it, don't hand-edit it.

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| No CSP header | Mode is `off`, or the site is not a puck site. |
| Inline script blocked after editing markup | Stale hash — flush the frontend cache. |
| Works on `report`, breaks on `enforce` | Re-check the console on `report`, add the host, then switch. |
| Embed still blocked after `script-src` | Also needs `frame-src` / `connect-src` / `img-src` — check which directive the console names. |
