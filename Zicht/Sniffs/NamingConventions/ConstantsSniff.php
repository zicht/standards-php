<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
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

        /**
         * PHP language natively allows ^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$
         * For example \x80 is € and \x85 is …, so we don't want \x80-\xff
         * and starting with a _ is also not really conventional
         * @see https://www.php.net/manual/en/language.constants.php
         */
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $name)) {
            $phpcsFile->addError(
                'Constant "%s" should be UPPERCASED_AND_UNDERSCORED, should start wit a letter '
                . 'and cannot contain characters other than A-Z, 0-9 and underscore',
                $stackPtr,
                'InvalidName',
                [$name]
            );
        }
    }
}
