<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

/**
 * Sniffs for naming conventions of global function names, method names and nested functions
 */
class Zicht_Sniffs_NamingConventions_FunctionsSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register() {
        return array(
            T_FUNCTION
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
            // scanning for function name
        }
        $functionName = $tokens[$pos]['content'];

        switch($tokens[$stackPtr]['level']) {
            case 0:
                if(!preg_match('/^[a-z_]+$/', $functionName)) {
                    $phpcsFile->addError(
                        "Global function name \"%s\" should be formatted with lowercase and underscores only",
                        $stackPtr,
                        'GlobalNaming',
                        array($functionName)
                    );
                }
                break;
            case 1:
                // skip magic methods 
                if(
                    substr($functionName, 0, 2) == '__'
                    && in_array(
                        substr($functionName, 2),
                        array('construct', 'get', 'set', 'call', 'callStatic', 'invoke', 'destruct', 'toString', 'clone', 'invoke', 'invokeStatic')
                    )
                ) {
                    return;
                }
                if(preg_match('/^_?[a-z][a-zA-Z0-9]*$/', $functionName) && preg_match('/[0-9]/', $functionName)) {
                    $phpcsFile->addWarning(
                        "Usage of numbers in methodname \"%s\" is discouraged",
                        $stackPtr,
                        'MethodNaming',
                        array($functionName)
                    );
                } elseif(!preg_match('/^_?[a-z][a-zA-Z]*$/', $functionName)) {
                    $phpcsFile->addError(
                        "Method name \"%s\" should be formatted lowerCamelCased "
                        . "and contain no underscores after the first",
                        $stackPtr,
                        'MethodNaming',
                        array($functionName)
                    );
                }
                break;
            default:
                $phpcsFile->addError(
                    "Local function \"%s\" definition is not allowed. Either use closures, or defined globally",
                    $stackPtr,
                    'NestedDefinition',
                    array($functionName)
                );
        }
    }
}
