<?php
/**
 * Manage translations of Cockpit CMS v1 and v2 modules
 *
 * @version   0.3.2
 * @author    Raffael Jesche
 * @license   MIT
 *
 * @see       https://github.com/raffaelj/cockpit_Babel
 * @see       https://github.com/agentejo/cockpit/
 * @see       https://github.com/Cockpit-HQ/Cockpit
 * @see       https://codeberg.org/multiplane/cockpit-cms-Multiplane/
 */

$isCockpitV2 = class_exists('Cockpit');

// Register Helpers
$this->helpers['babel'] = 'Babel\\Helper\\Babel';

// ACL
if (!$isCockpitV2) {
    $this->helper('acl')->addResource('babel', ['manage']);
}

// ADMIN
if (!$isCockpitV2 && COCKPIT_ADMIN_CP) {
    include_once(__DIR__.'/admin.php');
}
if ($isCockpitV2) {
    $this->on('app.admin.init', function() {
        include(__DIR__.'/admin_v2.php');
    });
}
