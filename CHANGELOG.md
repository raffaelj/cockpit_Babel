# Changelog

## Upcoming

* added more OPCache invalidations
* improved directory iterator for i18n string parsing (ignore `node_modules/*`, `vendor/*`, `lib/vendor/*`, `.git/*`)
* ignore .tag files if cockpit v2

## 0.3.2

* fixed UI (cockpit v1) and cache (cockpit v1+v2) issues when deleting or adding items
* moved filter buttons from top into togglable, fixed sidebar (cockpit v1)
* replaced "Add string" button with plus icon button (cockpit v1)
* show spinner icon instead of "loading..." (cockpit v1)

## 0.3.1

* fixed missing Multiplane themes translations in UI

## 0.3.0

* decoupled content languages from app languages - app languages must be declared in `config.php`
* refactored Multiplane v1 path/i18n detection
* dropped using empty dummy files (cockpit v1)
* requires PHP >= 7.4 (arrow functions)
* minor code refactoring and cleanup

## 0.2.2

* fixed missing language names in user i18n selection (user/account page)
* fixed storing empty strings in i18n files
* fixed handling of empty strings in UI
* removed "unassigned" tab (cockpit v1)
* fixed targeted elements hiding behind `<app-header>` (cockpit v2)
* added grid/list layout toggle (cockpit v1)
* restructured cluttered layout (cockpit v1)
* list affected modules also in "modules" tab (cockpit v1)
* allow deletions also in "strings" tab (cockpit v1)
* fixed updating duplicates
* performance: use change instead of input events
* check, if new string is duplicate (cockpit v1)
* cleanup and code refactoring

## 0.2.1

* fixed JS error, if no i18n strings exist (cockpit v1)
* disabled "Add string" button (not implemented, yet - cockpit v2)

## 0.2.0

* added partial support for Cockpit CMS v2
