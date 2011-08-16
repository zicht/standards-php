<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

/**
 * Checks for naming conventions on class names
 */
class Zicht_Sniffs_NamingConventions_ClassnameSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register() {
        return array(
            T_CLASS
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
        $tokens = $phpcsFile->getTokens();
        $pos = $stackPtr;
        while($tokens[++$pos]['code'] == T_WHITESPACE) {
        }

        $name = $tokens[$pos]['content'];
        $parts = explode('_', $name);
        foreach($parts as $part) {
            if(!preg_match('/^([A-Z][a-z]*)+$/', $part)) {
                $phpcsFile->addError(
                    'Classname "%s" is not formatted UpperCamelCased',
                    $stackPtr,
                    'InvalidName',
                    array($name)
                );
                break; // no sense reporting more than one time
            }
        }
    }
}