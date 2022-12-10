<?php

$this->on('admin.init', function() {

    if ($this->module('cockpit')->hasaccess('babel', 'manage')) {

        // bind admin routes
        $this->bindClass('Babel\\Controller\\Admin', 'babel');

        // add settings entry
        $this->on('cockpit.view.settings.item', function () {
            $this->renderView('babel:views/partials/settings.php');
        });

    }

    // load i18n
    $this->on('before', function() {
        $this->helper('babel')->loadI18n();
    });

});
