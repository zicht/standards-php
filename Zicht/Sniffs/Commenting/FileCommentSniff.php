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
        CommentTrait;

    /**
     * @var array<string, array<string, bool>>
     */
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
        ],
        '@link' => [
            'required' => false,
            'allow_multiple' => true,
        ],
        '@see' => [
            'required' => false,
            'allow_multiple' => true,
        ],
        '@deprecated' => [
            'required' => false,
            'allow_multiple' => false,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function processTags($phpcsFile, $stackPtr, $commentStart)
    {
        $this->processIsEmptyOrSuperfluousDocComment($phpcsFile, $stackPtr, $commentStart);
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
