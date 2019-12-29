<?php

$excludeFolders = [
    'storage',
    'assets',
    'docs',
    'vendor',
    'node_modules',
    'modules',
    '.phpintel',
    '.idea',
    '.github'
];

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([])
    ->setFinder(
        PhpCsFixer\Finder::create()
        ->in(__DIR__)
        ->exclude($excludeFolders)
        ->notName('README.md')
        ->notName('*.blade.php')
        ->notName('*.cache')
        ->notName('*.sqlite')
        ->notName('*.conf')
        ->notName('*.htaccess')
        ->notName('*.editorconfig')
        ->notName('*.gitignore')
        ->notName('*.ini')
        ->notName('*.log')
        ->notName('*.xml')
        ->notName('*.yml')
        ->notName('*.vc.php')
        ->notName('_ide_helper.php')
    );