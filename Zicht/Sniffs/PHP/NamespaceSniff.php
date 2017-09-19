<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class NamespaceSniff
 */
class NamespaceSniff implements Sniff
{
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register()
    {
        return [
            T_NS_SEPARATOR,
        ];
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
        $this->processGlobalNamespaceUsage($phpcsFile, $stackPtr);
    }


    public function processGlobalNamespaceUsage(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // find the previous token.
        $ptrLeft = $stackPtr;
        do {
            --$ptrLeft;
        } while ($tokens[ $ptrLeft ]['code'] == T_WHITESPACE);

        $ptrRight1 = $stackPtr;
        do {
            ++$ptrRight1;
        } while ($tokens[ $ptrRight1 ]['code'] == T_WHITESPACE);

        $ptrRight2 = $ptrRight1;
        do {
            ++$ptrRight2;
        } while ($tokens[ $ptrRight2 ]['code'] == T_WHITESPACE);

        if ($tokens[ $ptrLeft ]['code'] != T_USE && $tokens[ $ptrLeft ]['code'] != T_STRING) {
            if ($tokens[ $ptrRight2 ]['code'] == T_NS_SEPARATOR) {
                $phpcsFile->addWarning(
                    'Referring a non global namespace globally (without use statement) is discouraged',
                    $stackPtr,
                    'GlobalNamespaceReferral'
                );
            }
        }
    }
}