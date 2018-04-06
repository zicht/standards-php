<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniffs for naming conventions of global function names, method names and nested functions
 */
class FunctionsSniff implements Sniff
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
            T_FUNCTION,
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
        $tokens = $phpcsFile->getTokens();

        $pos = $stackPtr;
        while ($tokens[ ++$pos ]['code'] == T_WHITESPACE) {
            // scanning for function name
        }
        $functionName = $tokens[ $pos ]['content'];

        switch ($tokens[ $stackPtr ]['level']) {
            case 0:
                if (!preg_match('/^[a-z_]+$/', $functionName)) {
                    // make an exception for drupal update functions
                    if (!preg_match('/_update_[0-9]+/', $functionName)) {
                        $phpcsFile->addError(
                            "Global function name \"%s\" should be formatted with lowercase and underscores only",
                            $stackPtr,
                            'GlobalNaming',
                            [$functionName]
                        );
                    }
                }
                break;
            case 1:
                // skip magic methods
                if (substr($functionName, 0, 2) == '__'
                    && in_array(
                        substr($functionName, 2),
                        ['construct', 'get', 'set', 'call', 'callStatic', 'invoke', 'destruct', 'toString', 'clone', 'invoke', 'invokeStatic']
                    )
                ) {
                    return;
                }

                if (!preg_match('/^_?[a-z][a-zA-Z0-9]*$/', $functionName)) {
                    $phpcsFile->addError(
                        "Method name \"%s\" should be studlyCased and contain no underscores after the first",
                        $stackPtr,
                        'MethodNaming',
                        [$functionName]
                    );
                }
                break;
            default:
                $phpcsFile->addError(
                    "Local function \"%s\" definition is not allowed. Either use closures, or defined globally",
                    $stackPtr,
                    'NestedDefinition',
                    [$functionName]
                );
        }
    }
}
