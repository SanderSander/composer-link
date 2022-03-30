<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['vendor'])
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

$header = 'This file is part of the composer-link plugin.

Copyright (c) 2021-' . date('Y') . ' Sander Visser <themastersleader@hotmail.com>.

For the full copyright and license information, please view the LICENSE.md
file that was distributed with this source code.

@link https://github.com/SanderSander/composer-link';

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'single_blank_line_at_eof' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'header_comment' => ['header' => $header]
    ])->setFinder($finder);
