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
    $this->on('app.render.view/cockpit:views/accounts/account.php with cockpit:views/layouts/app.php', function($template, &$slots) {

        $slots['languages'] = [['i18n' => 'en', 'language' => 'English']];

        foreach ($this->helper('babel')->getLanguages() as $lang) {
            $slots['languages'][] = [
                'i18n' => $lang['code'],
                'language' => $lang['name'],
            ];
        }

    });

});

// load i18n
$this->on('before', function() {
    $this->helper('babel')->loadI18n();
});
