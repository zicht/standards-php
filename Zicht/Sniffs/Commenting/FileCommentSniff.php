<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff as PEARFileCommentSniff;

/**
 * Sniffs the doc comment tags in a file comment
 */
class FileCommentSniff extends PEARFileCommentSniff
{
    protected $tags = [
        'author' => [
            'required' => false,
            'allow_multiple' => true,
            'order_text' => 'precedes @copyright',
        ],
        'copyright' => [
            'required' => false,
            'allow_multiple' => true,
            'order_text' => 'follows @author',
        ],
        'version' => [
            'required' => false,
            'allow_multiple' => false,
            'order_text' => 'follows @license',
        ],
        'see' => [
            'required' => false,
            'allow_multiple' => true,
            'order_text' => 'follows @link',
        ],
        'deprecated' => [
            'required' => false,
            'allow_multiple' => false,
            'order_text' => 'follows @see (if used) or @version (if used) or @copyright (if used)',
        ],
    ];
}
