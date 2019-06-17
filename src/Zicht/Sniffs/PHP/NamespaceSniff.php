<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class NamespaceSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [
            T_NS_SEPARATOR,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $this->processGlobalNamespaceUsage($phpcsFile, $stackPtr);
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     */
    public function processGlobalNamespaceUsage(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // find the previous token.
        $ptrLeft = $stackPtr;
        do {
            --$ptrLeft;
        } while (T_WHITESPACE === $tokens[ $ptrLeft ]['code']);

        $ptrRight1 = $stackPtr;
        do {
            ++$ptrRight1;
        } while (T_WHITESPACE === $tokens[ $ptrRight1 ]['code']);

        $ptrRight2 = $ptrRight1;
        do {
            ++$ptrRight2;
        } while (T_WHITESPACE === $tokens[ $ptrRight2 ]['code']);

        if ($tokens[ $ptrLeft ]['code'] != T_USE && $tokens[ $ptrLeft ]['code'] != T_STRING) {
            if (T_NS_SEPARATOR === $tokens[ $ptrRight2 ]['code']) {
                $phpcsFile->addWarning(
                    'Referring a non global namespace globally (without use statement) is discouraged',
                    $stackPtr,
                    'GlobalNamespaceReferral'
                );
            }
        }
    }
}
