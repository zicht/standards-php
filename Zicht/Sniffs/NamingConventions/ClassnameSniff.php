<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks for naming conventions on class names
 */
class ClassnameSniff implements Sniff
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
            T_CLASS,
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
        }

        $name = $tokens[ $pos ]['content'];
        $parts = explode('_', $name);
        foreach ($parts as $part) {
            if (!preg_match('/^([A-Z][a-z]*)+$/', $part)) {
                $phpcsFile->addError(
                    'Classname "%s" is not formatted UpperCamelCased',
                    $stackPtr,
                    'InvalidName',
                    [$name]
                );
                break; // no sense reporting more than one time
            }
        }
    }
}
