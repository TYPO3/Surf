<?php

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
// Define in which folders to search and which folders to exclude
// Exclude some directories that are excluded by Git anyways to speed up the sniffing
$finder = PhpCsFixer\Finder::create()
    ->exclude('.github')
    ->exclude('Documentation')
    ->exclude('Resources')
    ->exclude('var')
    ->exclude('vendor')
    ->in(__DIR__);

// Return a Code Sniffing configuration using
// all sniffers needed for PSR-2
// and additionally:
//  - Remove leading slashes in use clauses.
//  - PHP single-line arrays should not have trailing comma.
//  - Single-line whitespace before closing semicolon are prohibited.
//  - Remove unused use statements in the PHP source code
//  - Ensure Concatenation to have at least one whitespace around
//  - Remove trailing whitespace at the end of blank lines.
return PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PSR2' => true,
            'array_syntax' => [
                'syntax' => 'short'
            ],
            'cast_spaces' => [
                'space' => 'none'
            ],
            'concat_space' => [
                'spacing' => 'one'
            ],
            'declare_equal_normalize' => [
                'space' => 'single'
            ],
            'dir_constant' => true,
            'function_typehint_space' => true,
            'hash_to_slash_comment' => true,
            'lowercase_cast' => true,
            'native_function_casing' => true,
            'no_alias_functions' => true,
            'no_blank_lines_after_phpdoc' => true,
            'no_empty_statement' => true,
            'no_extra_consecutive_blank_lines' => true,
            'no_leading_import_slash' => true,
            'no_leading_namespace_whitespace' => true,
            'no_short_bool_cast' => true,
            'no_singleline_whitespace_before_semicolons' => true,
            'no_superfluous_elseif' => true,
            'no_trailing_comma_in_singleline_array' => true,
            'no_unneeded_control_parentheses' => true,
            'no_unused_imports' => true,
            'no_useless_else' => true,
            'no_whitespace_in_blank_line' => true,
            'ordered_imports' => true,
            'phpdoc_no_empty_return' => true,
            'phpdoc_no_package' => true,
            'phpdoc_scalar' => true,
            'phpdoc_trim' => true,
            'phpdoc_types' => true,
            'phpdoc_types_order' => [
                'null_adjustment' => 'always_last',
                'sort_algorithm' => 'alpha'
            ],
            'return_type_declaration' => [
                'space_before' => 'none'
            ],
            'single_quote' => true,
            'whitespace_after_comma_in_array' => true,
        ]
    )
    ->setFinder($finder);
