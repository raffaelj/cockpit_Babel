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

// fix missing language names in user i18n selection
$this->on('app.render.view/system:views/users/user.php with app:layouts/app.php', function($template, &$slots) {

    $slots['languages'] = array_map(function($l) {
        if ($path = $this->path("#config:i18n/{$l['i18n']}.php")) {
            $lang = include($path);
            $l['language'] = $lang['@meta']['language'] ?? $l['i18n'];
        }
        return $l;
    }, $slots['languages']);

});
