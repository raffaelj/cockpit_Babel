<?php
/**
 * Manage translations of Cockpit CMS v1 modules
 *
 * @version   0.1.0
 * @author    Raffael Jesche
 * @license   MIT
 *
 * @see       https://github.com/raffaelj/cockpit_Babel
 */

$this->helpers['babel'] = 'Babel\\Helper\\Babel';

// ACL
$this->helper('acl')->addResource('babel', ['manage']);

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include_once(__DIR__.'/admin.php');
}
