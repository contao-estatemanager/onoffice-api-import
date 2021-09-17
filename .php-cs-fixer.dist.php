<?php
$header = <<<EOF
This file is part of the Contao EstateManager extension "onOffice API Import".

@link      https://www.contao-estatemanager.com/
@source    https://github.com/contao-estatemanager/onoffice-api-import
@copyright Copyright (c) 2021 Oveleon (https://www.oveleon.de)
@license   https://www.contao-estatemanager.com/lizenzbedingungen.html
@author    Daniele Sciannimanica (https://github.com/doishub)
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src'
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP71Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHPUnit60Migration:risky' => true,
        'align_multiline_comment' => true,
        'array_indentation' => true,
        'trim_array_spaces' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_whitespace_before_comma_in_array' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'comment_to_phpdoc' => true,
        'compact_nullable_typehint' => true,
        'escape_implicit_backslashes' => true,
        'fully_qualified_strict_types' => true,
        'general_phpdoc_annotation_remove' => [
            'author'
        ],
        'header_comment' => ['header' => $header],
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'list_syntax' => ['syntax' => 'short'],
        'multiline_comment_opening_closing' => true,
        'braces' => [
            'position_after_control_structures' => 'next',
            'position_after_functions_and_oop_constructs' => 'next',
        ],
        'no_alternative_syntax' => true,
        'no_binary_string' => true,
        'no_null_property_initialization' => true,
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'return_assignment' => true,
        'strict_comparison' => true,
        'string_line_ending' => true,
        'void_return' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(false);
