<?php

namespace Babel\Controller;

if (class_exists('Cockpit')) {
    class_alias('\App\Controller\App', '\Cockpit\AuthController');
}

class Admin extends \Cockpit\AuthController {

    public $isCockpitV2;

    protected function before() {

        $this->isCockpitV2 = class_exists('Cockpit');

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
        $modules[] = 'unassigned';

        $view = $this->isCockpitV2 ? 'babel:views/index_v2.php' : 'babel:views/index.php';

        return $this->render($view, compact('languages', 'localizedStrings', 'modules'));
    }

    public function save() {

        $data = $this->app->param('data', null);

        if (!$data) return false;

        return $this->app->helper('babel')->save($data);

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
