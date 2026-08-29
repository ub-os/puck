# Installation

## 1. Asset Build

### Environment
`npm >= 10`
`node >= 22`

In repository root directory run:
<pre>
npm install
npm run build
</pre>


## 2. Distribution installation
### 2.1 Install TYPO3 v13 via composer
### 2.2 Add to composer.json in TYPO3 project root

```json
"prefer-stable": true,
"minimum-stability": "dev",
"repositories": [
    {
        "type": "path",
        "url": "packages/*",
        "options": {
            "symlink": true
        }
    }
]
```

### 2.3 Install puck extension

Create folder "packages" in TYPO3 project root.
Exclude the following paths from your upload (For PhpStorm in /.idea/deployment.xml)
```xml
<excludedPaths>
    <excludedPath local="true" path="$PROJECT_DIR$/.claude" />
    <excludedPath local="true" path="$PROJECT_DIR$/.idea" />
    <excludedPath local="true" path="$PROJECT_DIR$/node_modules" />
    <excludedPath local="true" path="$PROJECT_DIR$/scripts" />
    <excludedPath local="true" path="$PROJECT_DIR$/src" />
    <excludedPath local="true" path="$PROJECT_DIR$/.editorconfig" />
    <excludedPath local="true" path="$PROJECT_DIR$/.gitignore" />
    <excludedPath local="true" path="$PROJECT_DIR$/biome.json" />
    <excludedPath local="true" path="$PROJECT_DIR$/package.json" />
    <excludedPath local="true" path="$PROJECT_DIR$/package-lock.json" />
    <excludedPath local="true" path="$PROJECT_DIR$/README.md" />
</excludedPaths>
```
Upload the extension directory "puck" to the "packages" folder.<br>
In TYPO3 project root run:
<pre>composer req ubos/puck:@dev</pre>

#### Update database schema
Run the `Admin Tools > Maintenance > Analyze Database Structure` task in the TYPO3 backend.

#### Setup extension and create distribution database records
In TYPO3 project root run:
<pre> vendor/bin/typo3 extension:setup</pre>

#### Override .htaccess
Replace the contents of the .htaccess file in the root of the TYPO3 project with the contents of ./puck/Initialisation/root.htaccess.txt.

## Site configuration: Tracking & Security

The site configuration form (`Site Management > Sites`) has a **Tracking & Security**
tab:

| Field | Effect |
| --- | --- |
| **Head markup** | Raw HTML injected before `</head>` on every page (consent manager, GTM loader, …). |
| **Body end markup** | Non-script HTML injected before `</body>` on every page (`<noscript>`, tracking pixels). |
| **Content-Security-Policy** | `off` / `report` / `enforce` — see *Rollout* below. |
| **Allow inline scripts** | Adds `'unsafe-inline'` and disables hash mode. Off by default. |
| **Content-Security-Policy rules** | Line based rules that add / override / remove CSP directives. |

Everything is stored in the site's `config.yaml` and deploys with the project —
no backend *Content-Security-Policy* module (system-maintainer only) required.

The markup is contributed during cacheable page rendering by
`UBOS\Puck\Frontend\PageAssetDecorator` (a `BeforeJavaScriptsRenderingEvent`
listener, which also adds puck's assets and favicon). Executable inline
`<script>` / `<style>` in either tracking field is registered as a CSP asset so
TYPO3 collects a **content hash** (see *Caching*), and always renders in `<head>`
— a script pasted into *Body end markup* is moved to the head. External
`<script src>`, `<noscript>`, pixels and `<script type="application/ld+json">`
are output verbatim; external hosts need allow-listing via a CSP rule.

### CSP rules syntax

One rule per line, `#` starts a comment:

```
<directive> [<mode>] <source> [<source> …]
```

`<mode>` is one of `extend` (default), `append`, `set`, `remove`, `reduce`, `inherit`.

```
script-src            https://cdn.example.com https://*.matomo.cloud
img-src               data: https://*.matomo.cloud
connect-src           https://*.matomo.cloud
frame-src   remove    https://old-embed.example.com
default-src set       'self'
```

Puck ships **no** static CSP directives of its own, and the field starts empty.
The baseline is the TYPO3 core frontend policy (`'self'`, `data:` images,
`base-uri 'self'`, YouTube-nocookie / Vimeo embeds), so for many sites nothing
needs to be added here. Effective order: core defaults → these rules. Because the
rules run last and support `set` / `remove`, they can override or strip anything
below them.

When the Usercentrics consent manager is used (`puck.enableCookieSettings`), add:

```
script-src  https://*.usercentrics.eu https://app.usercentrics.eu
connect-src https://*.usercentrics.eu https://api.usercentrics.eu
```

### Rollout

1. Leave **Content-Security-Policy** on `report`. Open the site with browser
   devtools — every violation is logged to the console with the exact directive
   and blocked URI (no backend access needed).
2. Add the missing hosts to **Content-Security-Policy rules**, deploy.
3. Switch to `enforce` once the console is clean.

`off` sends no CSP header at all.

### Caching (hash vs. nonce)

`csp.yaml` sets `behavior: { useNonce: false, useHash: true }` — **hash based
CSP**. Inline scripts/styles are authorised by a SHA-256 of their content, which
is deterministic and stored with the page cache, so responses stay **fully
cacheable** by TYPO3, reverse proxies, CDNs and `staticfilecache`.

A nonce (TYPO3's default when no behavior is set) is a per-request random value;
whenever it is used TYPO3 sends `Cache-Control: private, no-store` and re-writes
the nonce across the whole HTML on every page-cache hit — no shared-cache
caching at all.

**Allow inline scripts** switches the site to `'unsafe-inline'` + `useHash: false`
(the two are mutually exclusive — browsers ignore `'unsafe-inline'` once a hash is
present). Responses stay cacheable, but any inline script then runs. Prefer the
default (hash mode); inline snippets are handled automatically.

`Initialisation/Site/puck/csp.yaml` is puck-managed — the tab drives it, don't
hand-edit it.
