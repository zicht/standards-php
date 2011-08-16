<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

/**
 * Sniff to check if class constants are documented
 */
class Zicht_Sniffs_Commenting_ClassConstantCommentSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register() {
        return array(T_CONST);
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
        $tokens = $phpcsFile->getTokens();
        $pos = $stackPtr;
        while($tokens[--$pos]['code'] == T_WHITESPACE) {
        }
        if($tokens[$pos]['code'] !== T_DOC_COMMENT) {
            $phpcsFile->addError('Doc comment missing for constant', $stackPtr, 'Missing');
        }
    }
}