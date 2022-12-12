<?php

// bind admin routes
$this->bindClass('Babel\\Controller\\Admin', '/babel');

// add settings icon
$this->on('app.settings.collect', function($settings) {

    $settings['System'][] = [
        'icon' => 'system:assets/icons/settings.svg',
        'route' => '/babel',
        'label' => 'Babel',
        'permission' => 'babel/manage'
    ];

});

// ACL
$this->on('app.permissions.collect', function($permissions) {

    $permissions['Babel'] = [
        'babel/manage' => 'Manage translations',
    ];

});

// load i18n (@meta and unassigned strings)
$this->on('app.admin.request', function() {

    $i18n   = $this->helper('i18n');
    $locale = $this->helper('auth')->getUser('i18n', $i18n->locale);

    if ($translationspath = $this->path("#config:i18n/{$locale}.php")) {
        $i18n->load($translationspath, $locale);
    }

});
