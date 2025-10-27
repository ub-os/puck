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
"repositories": [
    {
        "type": "path",
        "url": "packages/*",
        "options": {
            "symlink": true
        }
    },
    {
        "url": "https://github.com/oliveoilexpert/t3-avif.git",
        "type": "git"
    },
    {
        "url": "https://github.com/oliveoilexpert/ckeditor_icons.git",
        "type": "git"
    },
    {
        "url": "https://github.com/oliveoilexpert/typo3-copy-presets.git",
        "type": "git"
    },
    {
        "url": "https://github.com/oliveoilexpert/typo3-label-editor.git",
        "type": "git"
    },
    {
        "url": "https://github.com/oliveoilexpert/typo3-shape.git",
        "type": "git"
    },
    {
        "url": "https://github.com/oliveoilexpert/typo3-menu-controls.git",
        "type": "git"
    },   
],
"prefer-stable": true,
"minimum-stability": "dev",
```

### 2.3 Install puck extension

Create folder "packages" in TYPO3 project root.
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
