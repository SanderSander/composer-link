<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['vendor'])
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@PSR2' => true,
        'strict_param' => false,
        'no_unused_imports' => true,
        'single_blank_line_at_eof' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha']
    ])->setFinder($finder);
