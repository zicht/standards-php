<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniff to check naming convention of class constants
 */
class ConstantsSniff implements Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_CONST,
            T_STRING,
        ];
    }

    /**
     * Checks if constants are UPPERCASED_AND_UNDERSCORED
     *
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[ $stackPtr ]['code'] == T_STRING && $tokens[ $stackPtr ]['content'] != 'define') {
            return;
        }

        $pos = $stackPtr;
        while (in_array($tokens[ ++$pos ]['code'], [T_WHITESPACE, T_OPEN_PARENTHESIS])) {
        }

        $name = $tokens[ $pos ]['content'];
        if ($tokens[ $pos ]['code'] == T_CONSTANT_ENCAPSED_STRING) {
            $name = substr($name, 1, -1);
        }

        if (!preg_match('/^[A-Z][A-Z_]*[0-9]*$/', $name)) {
            $phpcsFile->addWarning(
                'Constant "%s" should be UPPERCASED_AND_UNDERSCORED',
                $stackPtr,
                'InvalidName',
                [$name]
            );
        }
    }
}
