<?php
$baseDir = dirname(dirname(__FILE__));

return [
    'plugins' => [
        'AkkaFacebook' => $baseDir . '/plugins/AkkaFacebook/',
        'AkkaFacebook (copy)' => $baseDir . '/plugins/AkkaFacebook (copy)/',
        'Bake' => $baseDir . '/vendors/cakephp/bake/',
        'Cake/TwigView' => $baseDir . '/vendors/cakephp/twig-view/',
        'DebugKit' => $baseDir . '/vendors/cakephp/debug_kit/',
        'Migrations' => $baseDir . '/vendors/cakephp/migrations/',
    ],
];
