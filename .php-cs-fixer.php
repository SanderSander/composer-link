<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2022 Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

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
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@Symfony' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'php_unit_test_case_static_method_calls' => true,
        'single_blank_line_at_eof' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'header_comment' => ['header' => $header],
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'concat_space' => ['spacing' => 'one'],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'single_line_throw' => false,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'ordered_class_elements' => [
            'sort_algorithm' => 'alpha'
        ]
    ])->setFinder($finder);
