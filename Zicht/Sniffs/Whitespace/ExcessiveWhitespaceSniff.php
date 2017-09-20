<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\Whitespace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * A sniff that checks for excessive whitespace lines.
 */
class ExcessiveWhitespaceSniff implements Sniff
{
    /**
     * Registers for whitespace tokens
     *
     * @return array
     */
    public function register()
    {
        return [
            T_WHITESPACE,
        ];
    }


    /**
     * Sniffs for multiple newlines
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[ $stackPtr - 1 ]['code'] == T_WHITESPACE) {
            // we already processed consecutive whitespace, no need to do it again.
            return;
        }

        $concat = $tokens[ $stackPtr ]['content'];
        $pos = $stackPtr;
        while (isset($tokens[ ++$pos ]) && $tokens[ $pos ]['code'] == T_WHITESPACE) {
            $concat .= $tokens[ $pos ]['content'];
        }

        if (preg_match('/^[^\n](\s+?)\n/', $concat, $m)) {
            // carriage return should be catched by the line ending style sniffs
            if (strpos($m[0], "\r") === false) {
                $phpcsFile->addWarning(
                    "There should be no whitespace before the end of line",
                    $stackPtr,
                    "WhitespaceBeforeEol"
                );
            }
        }

        $newlines = substr_count($concat, "\n");

        if ($newlines > 3) {
            $phpcsFile->addWarning(
                'Excessive whitespace, no more than two lines of whitespace is allowed',
                $stackPtr,
                'WhiteLines'
            );
        }

        if (!isset($tokens[ $pos ]) && $newlines > 1) {
            $phpcsFile->addWarning('Excess whitespace at end of file', $stackPtr, 'WhiteLinesAtEndOfFile');
        } elseif (
            isset($tokens[ $pos ])
            && (
            in_array(
                $tokens[ $pos ]['type'],
                ['T_CLOSE_CURLY_BRACKET', 'T_CLOSE_PARENTHESIS']
            )
            ) && $newlines > 1
        ) {
            $phpcsFile->addWarning(
                'Excess whitespace before closing bracket',
                $stackPtr,
                'WhiteLinesBeforeClosingBracket'
            );
        } else {
//            var_dump($tokens[$pos]['type']);
        }
    }
}
