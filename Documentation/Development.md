# Development

Internal notes for working on the puck sitepackage itself. For installing it into
a project see [Installation.md](Installation.md).

Puck is a **pure sitepackage template** — it is always consumed as a local
package in a TYPO3 project's `packages/`, never deployed as a root package. The
`vendor/`, `public/` and `var/` directories here are throwaway local dev
scaffolding that only exist so the linters can resolve TYPO3 classes. When puck
is consumed as a dependency, its `require-dev` / `scripts` / `phpstan.neon` /
`.php-cs-fixer.dist.php` are all inert.

## Environment

| Tool | Version |
| --- | --- |
| Node | `>= 22` |
| npm | `>= 10` |
| PHP | `>= 8.3` (only needed for the PHP QA tools below) |
| Composer | 2.x |

There is **no CI** — all linting is local. `composer.lock` is git-ignored.

## Frontend asset build

The build is driven by npm scripts in `package.json`. Output goes to
`Resources/Public/`.

```bash
npm install
npm run build      # one-off full build
npm run watch      # rebuild on change (JS, CSS, icons, favicons in parallel)
```

`npm run build` runs these steps in order (`run-s`):

| Script | Does | Source → output |
| --- | --- | --- |
| `build:frontend-icons` | Icon font (fantasticon) + SVG sprite (svgstore) | `src/icons/*.svg` → `Resources/Public/Fonts/Icons/`, sprite |
| `build:favicons` | Favicon set (favicons) | `src/favicons/default.svg` → `Resources/Public/` |
| `build:js` | Bundle (esbuild) | `src/js/*.js` → `Resources/Public/JavaScript/` |
| `build:css` | `sass` → `autoprefixer` (postcss) → `css-minify` | `src/sass/*.sass` → `Resources/Public/Css/dist/` |

Individual steps can be run on their own, e.g. `npm run build:css`. The wrapper
scripts live in `scripts/`.

### JS / CSS linting (Biome)

```bash
npm run biome:check    # report
npm run biome:fix      # apply safe fixes + organise imports
```

Config in `biome.json`: tab indent, single quotes, no semicolons, 120 col width,
`useIgnoreFile` honours `.gitignore`.

## PHP QA

These need the dev dependencies installed (`composer install`). Because
`composer.lock` is not committed, run `composer update` first in a fresh checkout
(a DDEV web container is the easiest place — the host PHP may be too old).

### PHPStan

```bash
composer phpstan            # analyse Classes/ (memory-limit baked in)
composer phpstan:baseline   # regenerate phpstan-baseline.neon
```

`phpstan.neon`: level 5, `phpVersion: 80300`, analyses `Classes/` only. It
manually `includes` `saschaegerer/phpstan-typo3` (no extension-installer) and
`phpstan-baseline.neon`. The baseline is currently **empty** — keep it green;
regenerate only as a deliberate, reviewed step.

### Coding standards (php-cs-fixer)

```bash
composer cs:check    # dry-run + diff
composer cs:fix      # apply
```

`.php-cs-fixer.dist.php` uses `TYPO3\CodingStandards` but overrides the indent to
**tabs** (the repo `.editorconfig` mandates tabs; TYPO3 CGL default is 4 spaces).
Excludes `var`, `vendor`, `node_modules`, `Resources`, `Initialisation`.
`declare(strict_types=1)` is **not** force-added — decided against for a
stringy-data CMS where PHPStan already covers the static cases.

