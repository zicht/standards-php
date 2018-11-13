<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff as PearClassCommentSniff;

/**
 * Checks for empty or superfluous class doc comments, sniffs the doc comment tags in class level comments
 */
class ClassCommentSniff extends PearClassCommentSniff
{
    use DisallowTagsTrait,
        EmptyCommentTrait;

    protected $tags = [
        '@category' => [
            'disallow' => true,
        ],
        '@package' => [
            'disallow' => true,
        ],
        '@subpackage' => [
            'disallow' => true,
        ],
        '@author' => [
            'disallow' => true,
        ],
        '@copyright' => [
            'disallow' => true,
        ],
        '@version' => [
            'required' => false,
            'allow_multiple' => false,
            'order_text' => 'follows @license',
        ],
        '@link' => [
            'required' => false,
            'allow_multiple' => true,
        ],
        '@see' => [
            'required' => false,
            'allow_multiple' => true,
            'order_text' => 'follows @link',
        ],
        '@deprecated' => [
            'required' => false,
            'allow_multiple' => false,
            'order_text' => 'follows @see (if used) or @version (if used)',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function processTags($phpcsFile, $stackPtr, $commentStart)
    {
        $this->processIsEmptyDocBlock($phpcsFile, $stackPtr, $commentStart);
        $this->processDisallowedTags($phpcsFile, $stackPtr, $commentStart);
    }
}
