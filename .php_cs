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
                           ->exclude('Configuration')
                           ->exclude('Documentation')
                           ->exclude('Resources')
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
                        ->setRules(array(
                            '@PSR2' => true,
                            'no_leading_import_slash' => true,
                            'no_trailing_comma_in_singleline_array' => true,
                            'no_singleline_whitespace_before_semicolons' => true,
                            'no_unused_imports' => true,
                            'concat_space' => ['spacing' => 'one'],
                            'no_whitespace_in_blank_line' => true,
                            'ordered_imports' => true,
                            'single_quote' => true,
                            'no_empty_statement' => true,
                            'no_extra_consecutive_blank_lines' => true,
                            'phpdoc_no_package' => true,
                            'phpdoc_scalar' => true,
                            'no_blank_lines_after_phpdoc' => true,
                            'array_syntax' => ['syntax' => 'short'],
                            'whitespace_after_comma_in_array' => true,
                            'function_typehint_space' => true,
                            'hash_to_slash_comment' => true,
                            'no_alias_functions' => true,
                            'lowercase_cast' => true,
                            'no_leading_namespace_whitespace' => true,
                            'native_function_casing' => true,
                            'no_short_bool_cast' => true,
                            'no_unneeded_control_parentheses' => true,
                            'phpdoc_no_empty_return' => true,
                            'phpdoc_trim' => true,
                            'no_superfluous_elseif' => true,
                            'no_useless_else' => true,
                            'phpdoc_types' => true,
                            'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'alpha'],
                            'return_type_declaration' => ['space_before' => 'none'],
                            'cast_spaces' => ['space' => 'none'],
                            'declare_equal_normalize' => ['space' => 'single'],
                            'dir_constant' => true,
                        ))
                        ->setFinder($finder);
