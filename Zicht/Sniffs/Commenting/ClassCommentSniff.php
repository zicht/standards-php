<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff as PEARClassCommentSniff;

/**
 * Sniffs the doc comment tags in class level comments
 */
class ClassCommentSniff extends PEARClassCommentSniff
{
    protected $tags = array(
        'author' => array(
            'required' => false,
            'allow_multiple' => true,
            'order_text' => 'precedes @copyright',
        ),
        'copyright' => array(
            'required' => false,
            'allow_multiple' => true,
            'order_text' => 'follows @author',
        ),
        'version' => array(
            'required' => false,
            'allow_multiple' => false,
            'order_text' => 'follows @license',
        ),
        'see' => array(
            'required' => false,
            'allow_multiple' => true,
            'order_text' => 'follows @link',
        ),
        'deprecated' => array(
            'required' => false,
            'allow_multiple' => false,
            'order_text' => 'follows @see (if used) or @version (if used) or @copyright (if used)',
        ),
    );


    /**
     * Override to skip the file comments
     *
     * @param int $commentStart
     * @return void
     */
    protected function processCopyrights($commentStart) {
        // skip copyrights semantics.
    }
}