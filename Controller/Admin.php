<?php

namespace Babel\Controller;

if (class_exists('Cockpit')) {
    class_alias('\App\Controller\App', '\Cockpit\AuthController');
}

class Admin extends \Cockpit\AuthController {

    public $isCockpitV2;
    public $isMultiplane;

    protected function before() {

        $this->isCockpitV2  = class_exists('Cockpit');
        $this->isMultiplane = isset($this->app['modules']['multiplane']);

        if ($this->isCockpitV2) {
            if (!$this->isAllowed('babel/manage')) {
                return $this->stop(401);
            }
            $this->helper('theme')->title('Babel');
        }
    }

    public function index() {

        $languages = $this->app->helper('babel')->getLanguages();

        $localizedStrings = $this->app->helper('babel')->getLocalizedStrings();

        $modules = $this->app->helper('babel')->getModulesNames();
        sort($modules);

        if ($this->isMultiplane) {
            $themes = $this->app->helper('babel')->getMultiplaneThemesNames();
            $modules = array_merge($modules, $themes);
        }

        $modules[] = 'unassigned';

        $view = $this->isCockpitV2 ? 'babel:views/index_v2.php' : 'babel:views/index.php';

        return $this->render($view, compact('languages', 'localizedStrings', 'modules'));
    }

    public function save() {

        $data = $this->app->param('data', null);

        if (!$data) return false;

        $translations = $this->app->helper('babel')->save($data);
        $dictionaries = $this->app->helper('babel')->getLocalizedStrings();

        return compact('translations', 'dictionaries');

    }

    public function getTranslatableStrings($module = null) {

        $force   = (bool) $this->app->param('force', false);
        $context = (bool) $this->app->param('context', false);

        return $this->app->helper('babel')->getTranslatableStrings($module, $force, $context);

    }

    public function getLocalizedStrings() {

        return $this->app->helper('babel')->getLocalizedStrings();

    }

    public function restructureI18nFiles() {

        return $this->app->helper('babel')->restructureI18nFiles();

    }

}
