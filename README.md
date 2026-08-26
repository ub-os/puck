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
