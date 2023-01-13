# Vorgehensweise

## 1. Lokal:
### Repository klonen, npm installieren, builden

<pre>
npm install
npm run build
</pre>

npm Version = 18

## 2. Auf Server
### 2.1. Inhalt composer.json auf Project Root-Ebene:

<pre>
{
   "name": "typo3/cms-base-distribution",
   "description" : "TYPO3 CMS Base Distribution",
   "license": "GPL-2.0-or-later",
   "config": {
	  "allow-plugins": {
		 "typo3/class-alias-loader": true,
		 "typo3/cms-composer-installers": true
	  },
	  "platform": {
		 "php": "8.0"
	  },
	  "sort-packages": true
   },
   "repositories": [
	  {
		 "type": "path",
		 "url": "extensions/*",
		 "options": {
			"symlink": true
		 }
	  }
   ],
   "require": {
	  "b13/menus": "^1.0",
	  "georgringer/news": "^10.0",
	  "helhum/typo3-console": "^7.0.2",
	  "lochmueller/autoloader": "^7.3",
	  "typo3/cms-backend": "^11.5.0",
	  "typo3/cms-belog": "^11.5.0",
	  "typo3/cms-beuser": "^11.5.0",
	  "typo3/cms-core": "^11.5.0",
	  "typo3/cms-dashboard": "^11.5.0",
	  "typo3/cms-extbase": "^11.5.0",
	  "typo3/cms-extensionmanager": "^11.5.0",
	  "typo3/cms-felogin": "^11.5.0",
	  "typo3/cms-filelist": "^11.5.0",
	  "typo3/cms-fluid": "^11.5.0",
	  "typo3/cms-fluid-styled-content": "^11.5.0",
	  "typo3/cms-form": "^11.5.0",
	  "typo3/cms-frontend": "^11.5.0",
	  "typo3/cms-impexp": "^11.5.0",
	  "typo3/cms-info": "^11.5.0",
	  "typo3/cms-install": "^11.5.0",
	  "typo3/cms-lowlevel": "^11",
	  "typo3/cms-recordlist": "^11.5.0",
	  "typo3/cms-rte-ckeditor": "^11.5.0",
	  "typo3/cms-seo": "^11.5.0",
	  "typo3/cms-setup": "^11.5.0",
	  "typo3/cms-sys-note": "^11.5.0",
	  "typo3/cms-t3editor": "^11.5.0",
	  "typo3/cms-tstemplate": "^11.5.0",
	  "typo3/cms-viewpage": "^11.5.0",
	  "ubos/puck": "@dev"
   },
   "scripts":{
	  "typo3-cms-scripts": [
		 "typo3cms install:fixfolderstructure"
	  ],
	  "post-autoload-dump": [
		 "@typo3-cms-scripts"
	  ]
   }
}

</pre>

### 2.2. Auf Server Verzeichnis "extensions" anlegen und hierhin deployer

!! Nach Build!!: Upload der Extension-Verzeichnisse in das Verzeichnis "extensions" auf dem Server

### Composer Installation wie immer
<pre>composer install</pre>
bzw. wenn bereits installiert:
<pre>composer update</pre>
### dann in TYPO3 Database Schema aktualisieren

### dann im Projektverzeichnis auf dem Server Distributionsscript ausführen für Default-Seiten:
<pre> vendor/bin/typo3 extension:setup</pre> 


