<?php

namespace Babel\Helper;

class Babel extends \Lime\Helper {

    public function getModulesDirs() {

        $modules = [];

        $dirs = [COCKPIT_DIR.'/modules', COCKPIT_ENV_ROOT.'/addons'];

        if ($customModulesPath = $this->app->retrieve('loadmodules')) {
            $dirs[] = $customModulesPath;
        }

        if (isset($this->app['modules']['cpmultiplanegui'])) {
            if ($MP_DIR = $this->app->module('cpmultiplanegui')->findMultiplaneDir()) {
                $dirs[] = $MP_DIR.'/modules';
            }
        }

        foreach ($dirs as $modulesDir) {
            $iter = new \DirectoryIterator($modulesDir);
            foreach ($iter as $file) {
                if ($file->isDot() || $file->isFile()) continue;
                $modules[] = $file->getRealPath();
            }
        }

        return $modules;

    }

    public function getModulesNames() {

        return array_map('basename', $this->getModulesDirs());

    }

    public function getTranslatableStrings($module = null, $force = false, $withContext = false) {

        $extensions = ['php', 'js', 'tag'];

        $modules = $this->getModulesDirs();

        $out = [];

        foreach ($modules as $dir) {

            $name = basename($dir);

            if ($module && $name != $module) continue;

            $out[$name] = [];
            $strings = [];
            $context = [];

            if (!$force && $stringsFilePath = $this->app->path("#config:i18n/_strings/{$name}.php")) {
                $strings = include($stringsFilePath);
            }
            else {

                // TODO: skip "node_modules", "lib/vendor"

                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST);

                foreach ($iterator as $file) {

                    if (!$file->isFile() || !in_array($file->getExtension(), $extensions)) continue;

                    $contents = file_get_contents($file->getRealPath());

                    // cockpit v2
                    // preg_match_all('/(?:{{ t|\<\?=t|App\.i18n\.get|App\.ui\.notify)\((["\'])((?:[^\1]|\\.)*?)\1(,\s*(["\'])((?:[^\4]|\\.)*?)\4)?\)/', $contents, $matches);

                    // cockpit v1
                    // preg_match_all('/(?:\@lang|App\.i18n\.get|App\.ui\.notify)\((["\'])((?:[^\1]|\\.)*?)\1(,\s*(["\'])((?:[^\4]|\\.)*?)\4)?\)/', $contents, $matches);

                    // improved cockpit v1 regex - matches variations of `$app('i18n')->get('str')`
                    // see: https://regex101.com/r/Gtf5L6/1
                    preg_match_all('/(?:\@lang|App\.i18n\.get|App\.ui\.notify|\$i18n->get|(?:(?:cockpit\(\)|\$app|\$this|\$this->app)(?:->helper|))\(\'i18n\'\)->get)\((["\'])((?:[^\1]|\\.)*?)\1(,\s*(["\'])((?:[^\4]|\\.)*?)\4)?\)/', $contents, $matches);

                    if (!isset($matches[2])) continue;

                    foreach ($matches[2] as $string) {
                        $strings[] = $string;

                        if ($withContext) {
                            // $context[] = $file->getRealPath();
                            $context[] = \str_replace($this->app['docs_root'], '', $file->getRealPath());
                        }
                    }
                }
            }

            if (!$withContext) {
                $strings = array_unique($strings);
                sort($strings);

                $this->app->helper('fs')->write("#config:i18n/_strings/{$name}.php", '<?php return '.$this->app->helper('utils')->var_export($strings, true).';');

                $out[$name]['strings'] = $strings;
            }
            else {

                $out[$name]['strings'] = [];
                $out[$name]['context'] = [];

                $i = 0;
                foreach ($strings as $k => $string) {

                    // TODO: sort

                    $key = array_search($string, $out[$name]['strings']);

                    if ($key === false) {

                        $out[$name]['strings'][$i] = $string;

                        $out[$name]['context'][$i] = $out[$name]['context'][$i] ?? [];

                        $out[$name]['context'][$i][] = $context[$k];

                        $i++;
                    }
                    else {
                        $out[$name]['context'][$key][] = $context[$k];
                    }

                }

            }

        }

        return $out;

    }

    public function getLanguages() {

        $languages = [];

        $defaultLang = $this->app->retrieve('i18n', 'en');
        $currentLang = $this->app->helper('i18n')->locale;

        foreach ($this->app['languages'] as $l => $label) {

            $code = $l == 'default' ? $defaultLang : $l;
            $languages[] = [
                'code'    => $code,
                'name'    => $label,
            ];
        }

        return $languages;

    }

    public function getLocalizedStrings() {

        $strings = [];

        $languages = $this->getLanguages();
        $modules   = $this->getModulesDirs();

        foreach ($languages as $lang) {
            $locale = $lang['code'];

            if ($locale == 'en') continue;

            // read i18n data (cockpit v1 folder pattern)
            if ($path = $this->app->path("#config:cockpit/i18n/{$locale}.php")) {

                $tmp = include($path);
                if (!empty($tmp) && is_array($tmp)) {

                    foreach ($tmp as $str => $translation) {
                        $strings[$str] = $strings[$str] ?? [];
                        $strings[$str][$locale] = $translation;
                    }

                }

            }

            // load replacement for cockpit v1 pattern
            if ($path = $this->app->path("#config:i18n/{$locale}.php")) {
                $tmp = include($path);
                if (!empty($tmp) && is_array($tmp)) {

                    foreach ($tmp as $str => $translation) {
                        $strings[$str] = $strings[$str] ?? [];
                        $strings[$str][$locale] = $translation;
                    }
                }
            }

            // read i18n data (cockpit v2 and Babel addon folder pattern)
            foreach ($modules as $dir) {
                $name = basename($dir);
                if ($path = $this->app->path("#config:i18n/{$name}/{$locale}.php")) {
                    $tmp = include($path);
                    if (!empty($tmp) && is_array($tmp)) {

                        foreach ($tmp as $str => $translation) {
                            $strings[$str] = $strings[$str] ?? [];
                            $strings[$str][$locale] = $translation;
                        }
                    }
                }
            }

            // read CpMultiplane i18n files
            if ($path = $this->getMultiplaneI18nPath($locale)) {
                $tmp = include($path);
                if (!empty($tmp) && is_array($tmp)) {

                    foreach ($tmp as $str => $translation) {
                        $strings[$str] = $strings[$str] ?? [];
                        $strings[$str][$locale] = $translation;
                    }
                }
            }

        }

        ksort($strings, SORT_STRING | SORT_FLAG_CASE);

        return $strings;

    }

    public function save($data) {

        // TODO: filter event

        $modules = $this->getModulesNames();

        foreach ($data as $moduleName => $value) {

            if (!in_array($moduleName, $modules)) continue;

            foreach ($value as $locale => $strings) {

                ksort($strings, SORT_STRING | SORT_FLAG_CASE);

                $this->app->helper('fs')->write("#config:i18n/{$moduleName}/{$locale}.php", '<?php return '.$this->app->helper('utils')->var_export($strings, true).';');
            }
        }

        if (isset($data['unassigned'])) {
            foreach ($data['unassigned'] as $locale => $strings) {

                ksort($strings, SORT_STRING | SORT_FLAG_CASE);

                $this->app->helper('fs')->write("#config:i18n/{$locale}.php", '<?php return '.$this->app->helper('utils')->var_export($strings, true).';');
            }
        }

        return $data;

    }

    public function loadI18n($locale = null, $modules = null) {

        $i18n = $this->app->helper('i18n');

        if (!$locale) {
            $locale = $this->app->module('cockpit')->getUser('i18n', $i18n->locale);
        }

        if ($modules) {
            if (is_string($modules)) $modules = [$modules];
            $modules = array_map('strtolower', $modules);
        }

        if ($translationspath = $this->app->path("#config:i18n/{$locale}.php")) {

            $i18n->load($translationspath, $locale);

            foreach ($this->app->retrieve('modules')->getArrayCopy() as $m) {

                $name = basename($m->_dir);

                if ($modules && !in_array(strtolower($name), $modules)) continue;

                if ($translationspath = $this->app->path("#config:i18n/{$name}/{$locale}.php")) {
                    $i18n->load($translationspath, $locale);
                }
            }
        }

    }

    public function restructureI18nFiles() {

        $fs = $this->app->helper('fs');

        $message = [];

        // copy current i18n files to new location
        foreach ($fs->ls('*.php', '#config:cockpit/i18n') as $file) {

            $path = $file->getRealPath();
            $i18n = $file->getBasename('.php');

            $written = false;

            // merge i18n files from old and new location
            if ($translationspath = $this->app->path("#config:i18n/{$i18n}.php")) {

                $tmp1 = include($translationspath);
                $tmp2 = include($path);
                $strings = array_merge($tmp2, $tmp1);
                ksort($strings, SORT_STRING | SORT_FLAG_CASE);

                $written = $fs->write("#config:i18n/{$i18n}.php", '<?php return '.$this->app->helper('utils')->var_export($strings, true).';');

            }

            // copy old i18n files to new location
            else {
                $content = $fs->read($path);
                $written = $fs->write("#config:i18n/{$i18n}.php", $content);
            }

            // create empty dummy file in old location
            if ($written) {
                $message[] = "copied {$i18n} from #config:cockpit/i18n to #config:i18n";

                $written = $fs->write("#config:cockpit/i18n/{$i18n}.php", '<?php return []; // empty dummy file for user i18n selection');
                if ($written) {
                    $message[] = "created empty dummy {$i18n} in #config:cockpit/i18n";
                }

            }

        }

        foreach ($this->getLanguages() as $lang) {

            $i18n = $lang['code'];

            // create empty dummy files inside `#config:cockpit/i18n/{locale}.php`
            // to be able to select a language in user settings
            if (!$this->app->path("#config:cockpit/i18n/{$i18n}.php")) {
                $written = $fs->write("#config:cockpit/i18n/{$i18n}.php", '<?php return []; // empty dummy file for user i18n selection');
                if ($written) {
                    $message[] = "created empty dummy {$i18n} in #config:cockpit/i18n";
                }
            }

            // move Multiplane i18n files
            if ($path = $this->getMultiplaneI18nPath($i18n)) {

                $written = false;

                // merge i18n files from old and new location
                if ($translationspath = $this->app->path("#config:i18n/Multiplane/{$i18n}.php")) {

                    $tmp1 = include($translationspath);
                    $tmp2 = include($path);
                    $strings = array_merge($tmp2, $tmp1);
                    ksort($strings, SORT_STRING | SORT_FLAG_CASE);

                    $written = $fs->write("#config:i18n/Multiplane/{$i18n}.php", '<?php return '.$this->app->helper('utils')->var_export($strings, true).';');
                }

                // copy old i18n files to new location
                else {
                    $content = $fs->read($path);
                    $written = $fs->write("#config:i18n/Multiplane/{$i18n}.php", $content);
                }

                if ($written) {
                    $message[] = "copied {$i18n} from Multiplane:config/i18n to #config:i18n/Multiplane";

                    // delete old Multiplane i18n file
                    try {
                        $fs->delete($path);
                        $message[] = "deleted {$i18n} from Multiplane:config/i18n";
                    } catch(Exception $e) {$message[] = $e->getMessage();}

                }
            }

        }

        return $message;

    }

    public function getMultiplaneI18nPath($locale) {

        $path = null;

        if (defined('MP_CONFIG_DIR') || defined('MP_ENV_ROOT')) {
            if (defined('MP_CONFIG_DIR')) {
                $path = $this->app->path(MP_CONFIG_DIR."/i18n/{$locale}.php");
            }
            elseif (defined('MP_ENV_ROOT')) {
                $path = $this->app->path(MP_ENV_ROOT."/config/i18n/{$locale}.php");
            }
        }

        return $path;

    }

}
