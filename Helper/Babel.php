<?php

namespace Babel\Helper;

class Babel extends \Lime\Helper {

    public $isCockpitV2;
    public $isMultiplane;

    public $ksortOpts = SORT_STRING | SORT_FLAG_CASE;

    public function initialize() {
        $this->isCockpitV2  = class_exists('Cockpit');
        $this->isMultiplane = isset($this->app['modules']['multiplane']);
    }

    public function getModulesDirs() {

        static $modules;
        if (!is_null($modules)) return $modules;

        $modules = [];

        if (!$this->isCockpitV2) {
            $dirs = [COCKPIT_DIR.'/modules', COCKPIT_ENV_ROOT.'/addons'];
        }
        else {
            // TODO: custom addons dir
            $dirs = [APP_DIR.'/modules', APP_DIR.'/addons'];
        }

        if ($customModulesPath = $this->app->retrieve('loadmodules')) {
            $dirs[] = $customModulesPath;
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

        $modules = [];
        foreach ($this->getModulesDirs() as $dir) {
            $modules[$dir] = 'module';
        }
        if ($this->isMultiplane) {
            foreach ($this->getMultiplaneThemesPaths() as $dir) {
                $modules[$dir] = 'mp-theme';
            }
        }

        $out = [];

        foreach ($modules as $dir => $type) {

            $name = basename($dir);
            if ($type == 'mp-theme') $name = "mp-theme-{$name}";

            if ($module && $name != $module) continue;

            $out[$name] = [];
            $strings = [];
            $context = [];

            if (!$force && $stringsFilePath = $this->app->path("#config:i18n/_strings/{$name}.php")) {
                $strings = include($stringsFilePath);
            }
            else {

                if ($this->isCockpitV2) {
                    // cockpit v2
                    // TODO: improve regex
                    $regex = '/(?:{{ t|\<\?=t|App\.i18n\.get|App\.ui\.notify)\((["\'])((?:[^\1]|\\.)*?)\1(,\s*(["\'])((?:[^\4]|\\.)*?)\4)?\)/';
                }
                else {

                    // cockpit v1
                    // $regex = '/(?:\@lang|App\.i18n\.get|App\.ui\.notify)\((["\'])((?:[^\1]|\\.)*?)\1(,\s*(["\'])((?:[^\4]|\\.)*?)\4)?\)/';

                    // improved cockpit v1 regex - matches variations of `$app('i18n')->get('str')`
                    // see: https://regex101.com/r/Gtf5L6/1
                    $regex = '/(?:\@lang|App\.i18n\.get|App\.ui\.notify|\$i18n->get|(?:(?:cockpit\(\)|\$app|\$this|\$this->app)(?:->helper|))\(\'i18n\'\)->get)\((["\'])((?:[^\1]|\\.)*?)\1(,\s*(["\'])((?:[^\4]|\\.)*?)\4)?\)/';

                }

                // TODO: skip "node_modules", "lib/vendor"

                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST);

                foreach ($iterator as $file) {

                    if (!$file->isFile() || !in_array($file->getExtension(), $extensions)) continue;

                    $contents = file_get_contents($file->getRealPath());

                    preg_match_all($regex, $contents, $matches);

                    if (!isset($matches[2])) continue;

                    foreach ($matches[2] as $string) {
                        $strings[] = $string;

                        if ($withContext) {
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

        $config = $this->app->retrieve('babel/languages', []);

        if (!is_array($config)) return [];

        $languages = [];
        foreach ($config as $code => $label) {
            $languages[] = [
                'code' => $code,
                'name' => $label,
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

        }

        ksort($strings, $this->ksortOpts);

        return $strings;

    }

    public function save($data) {

        $this->app->trigger('babel.save.before', [&$data]);

        // modules and addons
        $modules = $this->getModulesNames();
        foreach ($data as $moduleName => &$value) {

            if (!in_array($moduleName, $modules)) continue;

            foreach ($value as $locale => &$strings) {

                $strings = $this->removeEmptyStrings($strings);

                ksort($strings, $this->ksortOpts);

                $this->app->helper('fs')->write("#config:i18n/{$moduleName}/{$locale}.php", '<?php return '.$this->app->helper('utils')->var_export($strings, true).';');
            }
        }

        // Multiplane themes
        $themes = $this->getMultiplaneThemesNames();
        foreach ($data as $themeName => &$value) {

            if (!in_array($themeName, $themes)) continue;

            foreach ($value as $locale => &$strings) {

                $strings = $this->removeEmptyStrings($strings);

                ksort($strings, $this->ksortOpts);

                $this->app->helper('fs')->write("#config:i18n/Multiplane/{$themeName}/{$locale}.php", '<?php return '.$this->app->helper('utils')->var_export($strings, true).';');
            }
        }

        // unassigned strings
        if (isset($data['unassigned'])) {
            foreach ($data['unassigned'] as $locale => &$strings) {

                $strings = $this->removeEmptyStrings($strings);

                ksort($strings, $this->ksortOpts);

                $this->app->helper('fs')->write("#config:i18n/{$locale}.php", '<?php return '.$this->app->helper('utils')->var_export($strings, true).';');
            }
        }

        return $data;

    }

    public function loadI18n($locale = null, $modules = null) {

        $i18n = $this->app->helper('i18n');

        if (!$locale) {
            if (!$this->isCockpitV2) {
                $locale = $this->app->module('cockpit')->getUser('i18n', $i18n->locale);
            }
            else {
                $locale = $this->helper('auth')->getUser('i18n', $i18n->locale);
            }
        }

        if ($modules) {
            if (is_string($modules)) $modules = [$modules];
            $modules = array_map('strtolower', $modules);
        }

        if ($translationspath = $this->app->path("#config:i18n/{$locale}.php")) {

            // load @meta and unassigned strings
            $i18n->load($translationspath, $locale);

            // load i18n strings for active modules
            foreach ($this->app->retrieve('modules')->getArrayCopy() as $module) {

                $name = basename($module->_dir);

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
                ksort($strings, $this->ksortOpts);

                $written = $fs->write("#config:i18n/{$i18n}.php", '<?php return '.$this->app->helper('utils')->var_export($strings, true).';');

            }

            // copy old i18n files to new location
            else {
                $content = $fs->read($path);
                $written = $fs->write("#config:i18n/{$i18n}.php", $content);
            }

            if ($written) {
                $message[] = "copied {$i18n} from #config:cockpit/i18n to #config:i18n";

                // delete old i18n file
                try {
                    $fs->delete($path);
                    $message[] = "deleted {$i18n} from #config:cockpit/i18n";
                } catch(Exception $e) {$message[] = $e->getMessage();}

            }

        }

        return $message;

    }

    /**
     * List Multiplane themes paths
     *
     * @return array
     */
    public function getMultiplaneThemesPaths() {

        static $themes;
        if (!is_null($themes)) return $themes;

        $themes = [];

        $paths = $this->app->paths('#themes');

        foreach ($paths as $dir) {
            $iter = new \DirectoryIterator($dir);
            foreach ($iter as $file) {
                if ($file->isDot() || $file->isFile()) continue;
                $name = $file->getRealPath();
                if (in_array($name, $themes)) continue;
                $themes[] = $name;
            }
        }

        return $themes;

    }

    /**
     * List Multiplane themes with prefix
     *
     * @param string $prefix
     * @return array
     */
    public function getMultiplaneThemesNames($prefix = 'mp-theme-') {

        $names = [];
        foreach ($this->getMultiplaneThemesPaths() as $dir) {
            $names[] = $prefix.basename($dir);
        }

        return $names;

    }

    public function removeEmptyStrings($strings) {

        return array_filter($strings, function($v) {

            // @meta key
            if (is_array($v)) return true;

            return '' !== trim($v);
        });
    }

}
