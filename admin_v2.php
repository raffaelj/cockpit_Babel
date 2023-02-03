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
$this->on('app.system.controller.users.init', function($controller) {

    $view = 'system:views/users/user.php';

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
