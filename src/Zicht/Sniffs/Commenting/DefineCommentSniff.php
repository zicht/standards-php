<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check if constants are documented
 */
class DefineCommentSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_STRING];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ('define' === $tokens[ $stackPtr ]['content']) {
            $pos = $stackPtr;
            while (T_WHITESPACE === $tokens[ --$pos ]['code']) {
                // Wind back to non-whitespace token
            }
            if (T_DOC_COMMENT_CLOSE_TAG !== $tokens[ $pos ]['code'] && T_DOC_COMMENT !== $tokens[ $pos ]['code']) {
                $phpcsFile->addError('Doc comment missing for constant', $stackPtr, 'Missing');
            }
        }
    }
}
