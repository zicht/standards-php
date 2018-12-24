<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht;

use PHP_CodeSniffer\Files\File;

/**
 * @deprecated 3.4.1 Will be moved and renamed in version 4.0.0
 */
class PhpCsFile
{
    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array<string,int> $limit  Optional start and end positions, exclusive
     * @return array
     */
    public static function getLineTokens(File $phpcsFile, $stackPtr, array $limit = [])
    {
        $tokens = $phpcsFile->getTokens();

        $lineTokens = [];

        static::walkLine(
            function ($pos, $token) use (&$lineTokens) {
                $lineTokens[$pos] = $token;
            },
            $tokens,
            $stackPtr,
            $limit
        );

        return $lineTokens;
    }

    /**
     * Returns the position of the first token of the previous line
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return int|null
     */
    public static function getPreviousLine(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $linePos = $stackPtr;
        while (isset($tokens[$linePos - 1]) && $tokens[$linePos - 1]['line'] >= $tokens[$stackPtr]['line'] - 1) {
            $linePos--;
        }

        if ($tokens[$linePos]['line'] !== $tokens[$stackPtr]['line'] - 1) {
            return null;
        }

        return $linePos;
    }

    /**
     * Returns the position of the first token of the next line
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return int|null
     */
    public static function getNextLine(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $linePos = $stackPtr;
        while (isset($tokens[$linePos + 1]) && $tokens[$linePos]['line'] === $tokens[$stackPtr]['line']) {
            $linePos++;
        }

        if ($tokens[$linePos]['line'] !== $tokens[$stackPtr]['line'] + 1) {
            return null;
        }

        return $linePos;
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array<string,int> $limit  Optional start and end positions, exclusive
     * @return string|null
     */
    public static function getLineContents(File $phpcsFile, $stackPtr, array $limit = [])
    {
        $tokens = $phpcsFile->getTokens();

        $lineContents = null;

        static::walkLine(
            function ($pos, $token) use (&$tokenCodes, &$lineContents) {
                $lineContents .= (string)$token['content'];
            },
            $tokens,
            $stackPtr,
            $limit
        );

        return $lineContents;
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array $tokenCodes
     * @param array<string,int> $limit  Optional start and end positions, exclusive
     * @return int|null
     */
    public static function lineContainsTokens(File $phpcsFile, $stackPtr, array $tokenCodes, array $limit = [])
    {
        $tokens = $phpcsFile->getTokens();

        $lineContainsTokens = null;

        static::walkLine(
            function ($pos, $token) use (&$tokenCodes, &$lineContainsTokens) {
                if (in_array($token['code'], $tokenCodes, true)) {
                    $lineContainsTokens = $pos;
                }
            },
            $tokens,
            $stackPtr,
            $limit
        );

        return $lineContainsTokens;
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array<string,int> $limit  Optional start and end positions, exclusive
     */
    public static function fixRemoveWholeLine(File $phpcsFile, $stackPtr, array $limit = [])
    {
        $tokens = $phpcsFile->getTokens();

        static::walkLine(
            function ($pos, $token) use ($phpcsFile) {
                $phpcsFile->fixer->replaceToken($pos, '');
            },
            $tokens,
            $stackPtr,
            $limit
        );
    }

    /**
     * @param callable $callback
     * @param array $tokens
     * @param int $stackPtr
     * @param array<string,int> $limit  Optional start and end positions, exclusive
     */
    private static function walkLine(callable $callback, array &$tokens, $stackPtr, array $limit = [])
    {
        $limit = array_merge(['start' => null, 'end' => null], $limit);

        $linePos = $stackPtr;
        while (isset($tokens[$linePos - 1]) && $tokens[$linePos - 1]['line'] === $tokens[$stackPtr]['line']
            && (null === $limit['start'] || $linePos - 1 > $limit['start'])) {
            $linePos--;
        }

        do {
            if ((null === $limit['start'] || $linePos > $limit['start'])
                && (null === $limit['end'] || $linePos < $limit['end'])) {
                $callback($linePos, $tokens[$linePos]);
            }
        } while ($tokens[++$linePos]['line'] === $tokens[$stackPtr]['line']
        && (null === $limit['end'] || $linePos < $limit['end']));
    }
}
