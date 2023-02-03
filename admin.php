<?php

$this->on('admin.init', function() {

    // bind admin routes
    $this->bindClass('Babel\\Controller\\Admin', 'babel');

    if ($this->module('cockpit')->hasaccess('babel', 'manage')) {

        // add settings entry
        $this->on('cockpit.view.settings.item', function () {
            $this->renderView('babel:views/partials/settings.php');
        });

    }

    // fix missing language names in user i18n selection
    $this->on('app.cockpit.controller.accounts.init', function($controller) {

        $view = 'cockpit:views/accounts/account.php';

        /**
        * Get protected $layout and apply it to $view to make sure, that hacking
        * into the accounts setting page doesn't break, if the core layout name
        * changes or was changed intentionally for e. g. theming
        */
        $layout = (fn() => $this->layout)->call($controller);
        if ($layout) $view .= " with {$layout}";

        $this->on('app.render.view/'.$view, function($template, &$slots) {

            $slots['languages'] = [['i18n' => 'en', 'language' => 'English']];

            foreach ($this->helper('babel')->getLanguages() as $lang) {
                $slots['languages'][] = [
                    'i18n'     => $lang['code'],
                    'language' => $lang['name'],
                ];
            }
        });
    });

});

// load i18n
$this->on('before', function() {
    $this->helper('babel')->loadI18n();
});
