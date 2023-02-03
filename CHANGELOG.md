# Changelog

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
