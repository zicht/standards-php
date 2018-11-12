<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if class constants are documented
 */
class ClassConstantCommentSniff implements Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_CONST];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $pos = $stackPtr;
        while (T_WHITESPACE === $tokens[ --$pos ]['code']) {
            // Wind back to non-whitespace token
        }
        if (T_DOC_COMMENT !== $tokens[ $pos ]['code'] && T_DOC_COMMENT_CLOSE_TAG !== $tokens[$pos]['code']) {
            $phpcsFile->addError('Doc comment missing for constant', $stackPtr, 'Missing');
        }
    }
}
