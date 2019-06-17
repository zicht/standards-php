<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\Methods;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use Zicht\StandardsPhp\FileUtils;

/**
 * Detects if there are no empty lines between a function's opening brace and the first line of code
 *
 * Inspired by:
 * @see \PHP_CodeSniffer\Standards\PSR2\Sniffs\Methods\FunctionClosingBraceSniff
 */
class FunctionOpeningBraceSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [
            T_FUNCTION,
            T_CLOSURE,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (false === isset($tokens[$stackPtr]['scope_opener'])) {
            // Interface or abstract method, no body, so no check
            return;
        }

        $openBrace = $tokens[$stackPtr]['scope_opener'];
        $bodyContent = $phpcsFile->findNext(T_WHITESPACE, ($openBrace + 1), null, true);
        $found = ($tokens[$bodyContent]['line'] - $tokens[$openBrace]['line'] - 1);

        if ($found < 0) {
            return; // Brace isn't on a new line, so not handled by us.
        }
        if ($found === 0) {
            return; // All is good.
        }

        $error = 'Function body must follow directly on the next line after the opening brace; '
            . 'found %s blank lines between brace and body';
        $fix = $phpcsFile->addFixableError($error, $openBrace, 'SpacingBetweenOpenAndBody', [$found]);

        if (true === $fix) {
            $phpcsFile->fixer->beginChangeset();
            $linePos = $openBrace;
            do {
                $linePos = FileUtils::getNextLine($phpcsFile, $linePos);
                FileUtils::fixRemoveWholeLine($phpcsFile, $linePos);
            } while ($tokens[$linePos]['line'] < $tokens[$bodyContent]['line'] - 1);
            $phpcsFile->fixer->endChangeset();
        }
    }
}
