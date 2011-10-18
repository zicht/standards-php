<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

/**
 * Sniffs the doc comment tags in class level comments
 */
class Zicht_Sniffs_PHP_UseStatementSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register() {
        return array(
            T_USE
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
        $this->processUseStatementValue($phpcsFile, $stackPtr);
        $this->processUseStatementPosition($phpcsFile, $stackPtr);
    }


    public function processUseStatementPosition(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();
        $ptr = $stackPtr;
        do {
            -- $ptr;
        } while(
            $ptr > 0
            && in_array(
                $tokens[$ptr]['code'],
                array(
                     T_OPEN_TAG,
                     T_NAMESPACE,
                     T_COMMENT,
                     T_DOC_COMMENT,
                     T_WHITESPACE,
                     T_SEMICOLON,
                     T_STRING,
                     T_NS_SEPARATOR
                )
            )
        );
        if($ptr !== 0) {
            $phpcsFile->addWarning(
                'Use statement on any other position than top of file is discouraged.',
                $stackPtr,
                'TopOfFile'
            );
        } 
    }


    public function processUseStatementValue(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();

        $ptr = $stackPtr;
        do {
            ++ $ptr;
        } while($tokens[$ptr]['code'] == T_WHITESPACE);

        if($tokens[$ptr]['code'] != T_NS_SEPARATOR) {
            $code = '';
            do {
                $code .= $tokens[$ptr]['content'];
                ++ $ptr;
            } while(isset($tokens[$ptr]) && !in_array($tokens[$ptr]['content'], array(';', 'as')));
            $phpcsFile->addError(
                'Use statements should always refer global namespace, found ' . trim($code),
                $stackPtr,
                'GlobalReference'
            );
        }
    }
}