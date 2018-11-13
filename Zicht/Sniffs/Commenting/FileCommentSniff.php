<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff as PearFileCommentSniff;

/**
 * Checks for empty or superfluous file doc comments, sniffs the doc comment tags in a file comment
 */
class FileCommentSniff extends PearFileCommentSniff
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
            'required' => false,
            'allow_multiple' => true
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
            'order_text' => 'follows @see (if used) or @version (if used) or @copyright (if used)',
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

    /**
     * {@inheritdoc}
     */
    protected function processCopyright($phpcsFile, array $tags)
    {
        /**
         * No further processing, skipping rules:
         * - Zicht.Commenting.FileComment.IncompleteCopyright
         * - Zicht.Commenting.FileComment.InvalidCopyright
         * - Zicht.Commenting.FileComment.CopyrightHyphen
         * @see PearFileCommentSniff::processCopyright()
         */
        return;
    }
}
