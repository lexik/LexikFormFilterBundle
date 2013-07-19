#!/usr/bin/env php
<?php

set_time_limit(0);

if (isset($argv[1])) {
    $_SERVER['SYMFONY_VERSION'] = $argv[1];
}

$vendorDir = __DIR__.'/../vendor';
if (!is_dir($vendorDir)) {
    mkdir($vendorDir);
}

$deps = array(
    array('symfony', 'http://github.com/symfony/symfony.git'),
    array('doctrine-common', 'http://github.com/doctrine/common.git'),
    array('doctrine-dbal', 'http://github.com/doctrine/dbal.git'),
    array('doctrine', 'http://github.com/doctrine/doctrine2.git'),
    array('doctrine-fixtures', 'http://github.com/doctrine/data-fixtures.git'),
);

$revs = array(
    'v2.2' => array(
        'symfony'           => 'v2.2.2',
        'doctrine-common'   => '2.3.0',
        'doctrine-dbal'     => '2.3.2',
        'doctrine'          => '2.3.2',
        'doctrine-fixtures' => 'origin/master',
    ),
    'v2.3' => array(
        'symfony'           => 'v2.3.0',
        'doctrine-common'   => '2.3.0',
        'doctrine-dbal'     => '2.3.4',
        'doctrine'          => '2.3.4',
        'doctrine-fixtures' => 'origin/master',
    ),
);

if (!isset($_SERVER['SYMFONY_VERSION'])) {
    $_SERVER['SYMFONY_VERSION'] = 'origin/master';
}

foreach ($deps as $index => $dep) {
    list($name, $url) = $dep;
    $rev = isset($revs[$_SERVER['SYMFONY_VERSION']][$name]) ? $revs[$_SERVER['SYMFONY_VERSION']][$name] : 'origin/master';

    $installDir = $vendorDir.'/'.$name;
    if (!is_dir($installDir)) {
        echo sprintf("\n> Installing %s\n", $name);

        system(sprintf('git clone %s %s', escapeshellarg($url), escapeshellarg($installDir)));
    } else {
        echo sprintf("\n> Updating %s\n", $name);
    }

    system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));
}
