<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/app/')
    ->in(__DIR__ . '/src/')
    ->in(__DIR__ . '/tests/');

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setLineEnding("\r\n")
    ->setRules([
        '@PSR2'            => true,
        'array_syntax'     => ['syntax' => 'short'],
        'full_opening_tag' => true,
        'cast_spaces'      => true,
        'concat_space' => true,
        'function_typehint_space' => true,
        'function_declaration' => true,
        'line_ending' => true,
        'linebreak_after_opening_tag' => true,
        'lowercase_cast' => true,
        'method_separation' => true,
        'native_function_casing' => true,
        'new_with_braces' => true,
        'no_closing_tag' => true,
        'no_empty_phpdoc' => true,
        'no_empty_comment' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_imports' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'single_quote' => true,
    ])
    ->setFinder($finder);
