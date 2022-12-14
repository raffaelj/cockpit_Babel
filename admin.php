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

        $slots['languages'] = array_map(function($l) {
            if ($path = $this->path("#config:i18n/{$l['i18n']}.php")) {
                $lang = include($path);
                $l['language'] = $lang['@meta']['language'] ?? $l['i18n'];
            }
            return $l;
        }, $slots['languages']);

    });

});

// load i18n
$this->on('before', function() {
    $this->helper('babel')->loadI18n();
});
