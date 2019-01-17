<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
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
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_FUNCTION,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $pos = $stackPtr;
        while (T_WHITESPACE === $tokens[ ++$pos ]['code']) {
            // scanning for function name
        }
        $functionName = $tokens[ $pos ]['content'];

        switch ($tokens[ $stackPtr ]['level']) {
            case 0:
                if (!preg_match('/^[a-z_]+$/', $functionName)) {
                    // make an exception for drupal update functions
                    if (!preg_match('/_update_[0-9]+/', $functionName)) {
                        $phpcsFile->addError(
                            'Global function name "%s" should be formatted with lowercase and underscores only',
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
                        'Method name "%s" should be studlyCased and contain no underscores after the first',
                        $stackPtr,
                        'MethodNaming',
                        [$functionName]
                    );
                }
                break;
            default:
                $phpcsFile->addError(
                    'Local function "%s" definition is not allowed. Either use closures, or defined globally',
                    $stackPtr,
                    'NestedDefinition',
                    [$functionName]
                );
        }
    }
}
