<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class DisallowMultipleAssignmentsInIfStatementsSniff
 *
 * This sniff detects multiple assignments in a if or elseif construct.
 *
 * Allowed
 * if ($foo = 'bar')
 *
 * Disallowed
 * if ($foo = 'bar' && $test = 'test')
 *
 */
class DisallowMultipleAssignmentsInIfStatementsSniff implements Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_IF,
            T_ELSEIF,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        // currently disabled
        return;

        $tokens = $phpcsFile->getTokens();

        /**
         * Try to find the next && or || en inbetween count the assignments till a ) is reached.
         * If the count > 1 generate a error.
         */
        $counter = 0;
        for ($i = $stackPtr; $i < $phpcsFile->numTokens; $i++) {
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $counter = 0;
            }

            if ($tokens[$i]['code'] === T_EQUAL) {
                $counter++;
            }

            if ($tokens[$i]['code'] === T_CLOSE_PARENTHESIS) {
                if ($counter > 1) {
                    $error = 'Too many assignments in if/elseif condition.';
                    $phpcsFile->addError($error, $stackPtr, 'TooManyAssignments');

                    /** Finished here */
                    return;
                }
            }
        }
    }
}
