<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

/**
 * 
 */
class Zicht_Sniffs_PHP_NamespaceSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register() {
        return array(
            T_NS_SEPARATOR
        );
    }

    /**
     * Called when one of the token types that this sniff is listening for
     * is found.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The PHP_CodeSniffer file where the
     *                                        token was found.
     * @param int                  $stackPtr  The position in the PHP_CodeSniffer
     *                                        file's token stack where the token
     *                                        was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $this->processGlobalNamespaceUsage($phpcsFile, $stackPtr);
    }


    public function processGlobalNamespaceUsage(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();

        // find the previous token. 
        $ptr = $stackPtr;
        do {
            --$ptr;
        } while ($tokens[$ptr]['code'] == T_WHITESPACE);


        if($tokens[$ptr]['code'] != T_USE && $tokens[$ptr]['code'] != T_STRING) {
            $phpcsFile->addWarning(
                'Referring global namespaces (without use statement) is discouraged',
                $stackPtr,
                'GlobalNamespaceReferral'
            );
        }
    }
}