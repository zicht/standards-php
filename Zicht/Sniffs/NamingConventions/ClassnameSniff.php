<?php
/**
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
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_CLASS,
        ];
    }

    /**
     * {@inheritdoc}
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
