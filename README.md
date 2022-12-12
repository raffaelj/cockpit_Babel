# Babel addon for Cockpit CMS v1

Manage translations of [Cockpit CMS v1][1], [Cockpit CMS v2][4] and [CpMultiplane][2] modules with a graphical user interface.

## Usage

* Login as admin (or with manage rights for babel)
* Go to "Settings" --> "Babel"
* If you use Cockpit CMS v1
  * Click on "Other" tab
  * Click on "Restructure i18n files" button. Now the Babel addon is initialized and the file/folder structure is adapted to new locations.
* Translate strings and click the "Save" button

## Concepts

### Cockpit CMS v1

__`config.php`:__

```php
<?php return [
    'i18n' => 'de',
    'languages' => [
        'default' => 'Deutsch',
        'fr'      => 'Francais',
    ],
];
```

__File/folder structure:__

* `config/cockpit/i18n/{locale}.php`
  * must exist for user language selection
* wysiwyg field (tinymce): `storage/assets/cockpit/i18n/tinymce/{locale}.js`

```text
.
├── config
|   ├── cockpit
|   |   └── i18n
|   |       ├── de.php (must exist)
|   |       └── fr.php (must exist)
|   └── config.php
├── storage
|   └── assets
|       └── cockpit
|           └── i18n
|               └── tinymce
|                   ├── de.js
|                   └── fr.js
```

### Cockpit CMS v2

__`config.php`:__

```php
<?php return [
    'i18n' => 'de',
];
```

* Go to Settings --> Locales --> Add Locale; than create locale "fr"/"Francais

__File/folder structure:__

* `config/i18n/App/{locale}.php`
  * must exist for user language selection
  * must exist to load other module i18n files
* wysiwyg field (tinymce): should be translatable via `App.on('field-wysiwyg-init', function(opts) {opts.language_url = 'path/to/tinymce/locale.js';});` (not tested)

```text
.
├── config
|   ├── i18n
|   |   ├── App
|   |   |   ├── de.php (must exist)
|   |   |   └── fr.php (must exist)
|   |   └── {module}
|   |       ├── de.php
|   |       └── fr.php
|   └── config.php
├── path/to/tinymce/{locale}.js (not tested)
```

### Babel addon

I like the new folder structure of Cockpit v2 and adapted it in this addon.

__`config.php`:__

```php
<?php return [
    'i18n' => 'de',
    'languages' => [
        'default' => 'Deutsch',
        'fr'      => 'Francais',
    ],
];
```

__File/folder structure:__

```text
.
├── config
|   ├── cockpit
|   |   └── i18n
|   |       ├── de.php (must exist - empty dummy file)
|   |       └── fr.php (must exist - empty dummy file)
|   ├── i18n
|   |   ├── {module}
|   |   |   ├── de.php
|   |   |   └── fr.php
|   |   ├── de.php (must exist - @meta and unassigned strings)
|   |   └── fr.php (must exist - @meta and unassigned strings)
|   └── config.php
├── storage/assets/cockpit/i18n/tinymce/{locale.js} (not automated, yet)
```

## Installation

Copy this repository into `/addons` and name it `Babel` or use the cli.

### via git

```bash
cd path/to/cockpit
git clone https://github.com/raffaelj/cockpit_Babel.git addons/Babel
```

### via cp cli (Cockpit CMS v1)

```bash
cd path/to/cockpit
./cp install/addon --name Babel --url https://github.com/raffaelj/cockpit_Babel/archive/main.zip
```

### via composer (Cockpit CMS v1)

Make sure, that the path to cockpit addons is defined in your projects' `composer.json` file.

```json
{
    "name": "my/cockpit-project",
    "extra": {
        "installer-paths": {
            "addons/{$name}": ["type:cockpit-module"]
        }
    }
}
```

```bash
cd path/to/cockpit-root
composer create-project --ignore-platform-reqs aheinze/cockpit .
composer config extra.installer-paths.addons/{\$name} "type:cockpit-module"

composer require --ignore-platform-reqs raffaelj/cockpit-babel
```

## Copyright and License

Copyright 2022 Raffael Jesche under the MIT license.

See [LICENSE][3] for more information.


[1]: https://github.com/agentejo/cockpit/
[2]: https://github.com/raffaelj/CpMultiplane/
[3]: https://github.com/raffaelj/cockpit_Babel/blob/master/LICENSE
[4]: https://github.com/Cockpit-HQ/Cockpit
