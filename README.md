# Installation

## 1. Build repository assets:

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
        "url": "extensions/*",
        "options": {
            "symlink": true
        }
    },
    {
        "url": "https://github.com/oliveoilexpert/t3-avif.git",
        "type": "git"
    },
    {
        "url": "https://github.com/oliveoilexpert/puck-powermail.git",
        "type": "git"
    },
    {
        "url": "https://github.com/oliveoilexpert/content-presets.git",
        "type": "git"
    },
    {
        "url": "https://github.com/oliveoilexpert/ckeditor_icons.git",
        "type": "git"
    },
    {
        "url": "https://github.com/oliveoilexpert/puckloader.git",
        "type": "git"
    }
],
"prefer-stable": true,
"minimum-stability": "dev",
```

### 2.3 Install puck extension

Create folder "extensions" in TYPO3 project root.
Upload the extension directory "puck" to the "extensions" folder.<br>
In TYPO3 project root run:
<pre>composer req ubos/puck:@dev</pre>

#### Update database schema
Run the `Admin Tools > Maintenance > Analyze Database Structure` task in the TYPO3 backend.

#### Setup extension and create distribution database records
In TYPO3 project root run:
<pre> vendor/bin/typo3 extension:setup</pre>

#### Override .htaccess
Replace the contents of the .htaccess file in the root of the TYPO3 project with the contents of root.htaccess.txt in the repository root directory.
