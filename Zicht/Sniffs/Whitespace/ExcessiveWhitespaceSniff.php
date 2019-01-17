<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\Whitespace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ExcessiveWhitespaceSniff implements Sniff
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (T_WHITESPACE === $tokens[ $stackPtr - 1 ]['code']) {
            // we already processed consecutive whitespace, no need to do it again.
            return;
        }

        $concat = $tokens[ $stackPtr ]['content'];
        $pos = $stackPtr;
        while (isset($tokens[ ++$pos ]) && T_WHITESPACE === $tokens[ $pos ]['code']) {
            $concat .= $tokens[ $pos ]['content'];
        }

        if (0 < preg_match('/^[^\n](\s+?)\n/', $concat, $m)) {
            // Carriage return should be caught by the line ending style sniffs
            if (strpos($m[0], "\r") === false) {
                $phpcsFile->addWarning(
                    'There should be no whitespace before the end of line',
                    $stackPtr,
                    'WhitespaceBeforeEol'
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
        } elseif (isset($tokens[ $pos ]) && $newlines > 1
            && in_array($tokens[ $pos ]['type'], ['T_CLOSE_CURLY_BRACKET', 'T_CLOSE_PARENTHESIS'])
        ) {
            $phpcsFile->addWarning(
                'Excess whitespace before closing bracket',
                $stackPtr,
                'WhiteLinesBeforeClosingBracket'
            );
        }
    }
}
