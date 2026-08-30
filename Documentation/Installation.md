# Installation

## 1. Asset Build

### Environment

- `npm >= 10`
- `node >= 22`

In the repository root directory run:

```bash
npm install
npm run build
```

The individual build steps, the watch mode and the PHP QA tooling (PHPStan,
php-cs-fixer, Biome) are documented in [Development.md](Development.md).

## 2. Distribution installation

### 2.1 Set up a Composer-based TYPO3 v14 project

Install TYPO3 v14 via Composer in the usual way, then add the path repository
below so the project can pick puck up from `packages/`.

### 2.2 Add the path repository to `composer.json` in the TYPO3 project root

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

### 2.3 Install the puck extension

Create the folder `packages` in the TYPO3 project root.

Exclude the following paths from your upload (for PhpStorm in `/.idea/deployment.xml`):

```xml
<excludedPaths>
    <excludedPath local="true" path="$PROJECT_DIR$/.claude" />
    <excludedPath local="true" path="$PROJECT_DIR$/.idea" />
    <excludedPath local="true" path="$PROJECT_DIR$/Documentation" />
    <excludedPath local="true" path="$PROJECT_DIR$/node_modules" />
    <excludedPath local="true" path="$PROJECT_DIR$/public" />
    <excludedPath local="true" path="$PROJECT_DIR$/scripts" />
    <excludedPath local="true" path="$PROJECT_DIR$/src" />
    <excludedPath local="true" path="$PROJECT_DIR$/var" />
    <excludedPath local="true" path="$PROJECT_DIR$/vendor" />
    <excludedPath local="true" path="$PROJECT_DIR$/.editorconfig" />
    <excludedPath local="true" path="$PROJECT_DIR$/.gitignore" />
    <excludedPath local="true" path="$PROJECT_DIR$/.php-cs-fixer.cache" />
    <excludedPath local="true" path="$PROJECT_DIR$/.php-cs-fixer.dist.php" />
    <excludedPath local="true" path="$PROJECT_DIR$/biome.json" />
    <excludedPath local="true" path="$PROJECT_DIR$/composer.lock" />
    <excludedPath local="true" path="$PROJECT_DIR$/package.json" />
    <excludedPath local="true" path="$PROJECT_DIR$/package-lock.json" />
    <excludedPath local="true" path="$PROJECT_DIR$/phpstan.neon" />
    <excludedPath local="true" path="$PROJECT_DIR$/phpstan-baseline.neon" />
    <excludedPath local="true" path="$PROJECT_DIR$/README.md" />
</excludedPaths>
```

Upload the extension directory `puck` to the `packages` folder.

In the TYPO3 project root run:

```bash
composer req ubos/puck:@dev
```

### 2.4 Update the database schema

In the TYPO3 project root run:

```bash
vendor/bin/typo3 database:update
```

### 2.5 Set up the extension and create distribution database records

In the TYPO3 project root run:

```bash
vendor/bin/typo3 extension:setup
```

Then delete the Initialisation folder `/packages/puck/Initialisation` in the TYPO3
installation and exclude the folder from upload:

```xml
<excludedPath local="true" path="$PROJECT_DIR$/Initialisation" />
```

### 2.6 Override `.htaccess`

Replace the contents of the `.htaccess` file in the root of the TYPO3 project with
the contents of `./puck/Initialisation/root.htaccess.txt`.

---

After installation, configure tracking and Content-Security-Policy per site — see
[SiteConfiguration.md](SiteConfiguration.md).
