<?php

namespace Babel\Controller;

class Admin extends \Cockpit\AuthController {

    public function index() {

        $languages = $this->app->helper('babel')->getLanguages();

        $localizedStrings = $this->app->helper('babel')->getLocalizedStrings();

        return $this->render('babel:views/index.php', compact('languages', 'localizedStrings'));
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
