<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
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
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register()
    {
        return [T_STRING];
    }

    /**
     * Called when one of the token types that this sniff is listening for
     * is found.
     *
     * @param File $phpcsFile The PHP_CodeSniffer file where the
     *                                        token was found.
     * @param int $stackPtr The position in the PHP_CodeSniffer
     *                                        file's token stack where the token
     *                                        was found.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[ $stackPtr ]['content'] == 'define') {
            $pos = $stackPtr;
            while ($tokens[ --$pos ]['code'] == T_WHITESPACE) {
            }
            if (!($tokens[ $pos ]['code'] === T_DOC_COMMENT_CLOSE_TAG || $tokens[ $pos ]['code'] === T_DOC_COMMENT)) {
                $phpcsFile->addError('Doc comment missing for constant', $stackPtr, 'Missing');
            }
        }
    }
}
