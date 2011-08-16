<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

/**
 * Sniff to check naming convention of class constants
 */
class Zicht_Sniffs_NamingConventions_ConstantsSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register() {
        return array(
            T_CONST,
            T_STRING
        );
    }


    /**
     * Checks if constants are UPPERCASED_AND_UNDERSCORED
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

        if($tokens[$stackPtr]['code'] == T_STRING && $tokens[$stackPtr]['content'] != 'define') {
            return;
        }

        $pos = $stackPtr;
        while(in_array($tokens[++$pos]['code'], array(T_WHITESPACE, T_OPEN_PARENTHESIS))) {
        }

        $name = $tokens[$pos]['content'];
        if($tokens[$pos]['code'] == T_CONSTANT_ENCAPSED_STRING) {
            $name = substr($name, 1, -1);
        }

        if(!preg_match('/^[A-Z][A-Z_]*$/', $name)) {
            $phpcsFile->addError(
                "Constant \"%s\" should be UPPERCASED_AND_UNDERSCORED",
                $stackPtr,
                'InvalidName',
                array($name)
            );
        }
    }
}
