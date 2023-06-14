# Installation

## 1. Lokal:
### Repository klonen, npm installieren, builden

<pre>
npm install
npm run build
</pre>

npm Version = 18

## 2. Auf Server
### 2.1. In composer.json auf Project Root-Ebene hinzufügen:

```json
"repositories": [
  {
     "type": "path",
     "url": "extensions/*",
     "options": {
        "symlink": true
     }
  }
],
```
und 
```json
"platform": {
  "php": "8.1"
},
```

### 2.2. Auf Server Verzeichnis "extensions" anlegen und hierhin deployer

!! Nach Build!!: Upload der Extension-Verzeichnisse in das Verzeichnis "extensions" auf dem Server

### Composer Installation
<pre>composer req ubos/puck:@dev</pre>

### dann in TYPO3 Database Schema aktualisieren

### dann im Projektverzeichnis auf dem Server Distributionsscript ausführen für Default-Seiten:
<pre> vendor/bin/typo3 extension:setup</pre> 

# Neuer Content Element Type

## 1. Model

Neues Model anlegen, z.B. "ProductCards" in "Classes/Domain/Model/Content/ProductCards.php"

```php
namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puck\Attribute\ContentElementWizard;

/**
 * @DatabaseTable("tt_content")
 */
 #[ContentElementWizard("01_content")]
class ProductCards extends Text
{
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $showPrice = 0;
}
```

Die neue Model-Klasse extended entweder ein anderes Content-Model oder AbstractEntity.

Annotations:
- DatabaseTable: Name der Tabelle, in der die Daten gespeichert ("persisted") werden.
- DatabaseField: Falls die Spalte (im Beispiel: "show_price")($camelCase in model => snake_case in Tabelle) noch nicht in der Tabelle existiert wird sie automatisch angelegt.

Attributes:
- ContentElementWizard: Tab, in dem das Content Element im New Content Element Wizard angezeigt wird.

Falls durch die Annotations Datenbankänderungen vorgenommen werden müssen (z.B. neue Spalte in Tabelle), muss das Datenbankschema im Typo3 Backend aktualisiert werden: "Admin Tools" -> "Maintenance" -> "Analyze Database Structure".

## 2. TCA


### 2.1. Content columns
Falls wir in unserem Model eine neue Spalte hinzugefügt haben, müssen wir diese auch in dem TCA definieren. 

"Configuration/TCA/Content/columns.php"

Beispiel
```php
$columns['show_price'] = [
    'label' => 'Show price',
    'config' => [
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 0,
    ]
];
```

### 2.1. Content types
Der Name des neuen "CTypes" ist "puck_" + der Name der Model-Klasse in snake_case.
Für diesen Type fügen wir die TCA-Definition hinzu.

z.B. "Configuration/TCA/Content/Types/puck_product_cards.php"

Beispiel
```php
<?php
use UBOS\Puck\Utility\TcaUtility;

$GLOBALS['TCA']['tt_content']['types']['puck_product_cards'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,'
        .TcaUtility::getContentShowitemBase(),
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ]
];
```

## 3. TSconfig

Der neue Content Element Type muss noch auf die Liste der allowed CTypes der Backend Layouts hinzugefügt werden.

"Configuration/TSconfig/Mod.tsconfig"

Beispiel für Backend Layout "default"
```typo3_typoscript
mod.web_layout.BackendLayouts.default.config.backend_layout.allowed.CType := addToList(puck_product_cards)
```


## 4. Backend Resources

### 4.1. Icon
SVG-Icon unter "Resources/Public/Icons/Backend/{modelName}.svg" speichern.

### 4.2. Language file
"Resources/Private/Language/locallang_be.xlf" erweitern.

Beispiel

```xml
<trans-unit id="content.element.product_cards" resname="content.element.product_cards">
    <source>Product cards</source>
</trans-unit>
<trans-unit id="wizard.product_cards" resname="wizard.product_cards">
    <source>Product cards</source>
</trans-unit>
<trans-unit id="wizard.product_cards.description" resname="wizard.product_cards.description">
    <source>List of product cards.</source>
</trans-unit>
```

## 5. Template
Template unter "Resources/Private/Fluid/Content/{modelName}.html" speichern.

Die Template-Datei sollte ein FluidComponent-Module aufrufen.

Beispiel

```html
<module:content.productCards object="{object}" />
```
Unter Umständen wird eine bereits vorhandene FluidComponent aufgerufen, oder es muss eine neue erstellt werden:

z.B. "Resources/Private/FluidComponents/Modules/Content/ProductCards/ProductCards.html"

```html
  <fc:param name="name" type="string" optional="1" default="m-content-product-cards"/>
  <fc:param name="object" type="mixed"/>
  <fc:renderer>

    <layout:section
        class="{name}__wrap"
        object="{object}"
        id="c{object.uid}">
      <element:text.header object="{object}"/>
      <element:text.rich text="{object.bodytext}" class="{name}__bodytext"/>

        <layout:row class="{name}__row">
            <f:for each="{object.inlineMedia}" as="item" iteration="i">
                <layout:row.item class="{name}__item"  width="6" >
                    <h2 class="{name}__headline">
                        Product: {item.title}
                    </h2>
                </layout:row.item>
            </f:for>
        </layout:row>

    </layout:section>

  </fc:renderer>
</fc:component>
```

## 6. Styling
Die SASS-Datei wird analog der FluidComponent benannt und abgelegt,

z.B. "Resources/Private/Stylesheets/06-modules/content/_m-content-product-cards.sass"

```sass
.m-content-product-cards
  &__headline
    +typo-h1
```
