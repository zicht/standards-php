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
            'disallow' => true,
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
}
