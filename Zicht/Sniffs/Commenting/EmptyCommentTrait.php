<?php
/**
 * @copyright Zicht Online <https://www.zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\File;
use Zicht\PhpCsFile as ZichtPhpCs_File;

trait EmptyCommentTrait
{
    /**
     * Check if doc block is empty or superfluous
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param int|null $commentStart
     * @return bool
     */
    protected function processIsEmptyDocBlock(File $phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();

        $commentEnd = $tokens[$commentStart]['comment_closer'];
        $docBlockEmpty = true;
        $docBlockStrings = [];

        if ($commentStart + 1 !== $commentEnd) {
            for ($pos = $commentStart + 1; $pos < $commentEnd; $pos++) {
                if (!in_array($tokens[$pos]['code'], [T_DOC_COMMENT_WHITESPACE, T_DOC_COMMENT_STAR])) {
                    $docBlockEmpty = false;
                    if (T_DOC_COMMENT_STRING === $tokens[$pos]['code']) {
                        $docBlockStrings[$pos] = $tokens[$pos]['content'];
                    } elseif (T_DOC_COMMENT_TAG === $tokens[$pos]['code']) {
                        break;
                    }
                }
            }
        }

        $fixRemoveWhole = $fixRemoveLinePos = false;
        try {
            $declarationName = $phpcsFile->getDeclarationName($stackPtr);
            $type = strtolower($tokens[$stackPtr]['content']);
        } catch (RuntimeException $e) {
            $declarationName = basename($phpcsFile->getFilename());
            $type = 'file';
        }

        if ($docBlockEmpty) {
            $fixRemoveWhole = $phpcsFile->addFixableError(
                'Doc comment for %s %s%s is empty and must be removed',
                $commentEnd,
                'Empty',
                [$type, $declarationName, ('function' === $type ? '()' : '')]
            );
        } elseif (0 < count($docBlockStrings)) {
            /**
             * - Filtering out everything that is not an `a-z` character leaves an empty comment: so superfluous
             * - Filtered comment is exactly the same as filtered function name: comment doesn't add anything => superfluous
             * - Comment is the same as type + name: "Function/Method Bla Bla Xyz" ~ "blaBlyXyz()" => superfluous
             */
            $filteredDocBlockString = preg_replace('/[^a-z]/', '', strtolower(implode('', $docBlockStrings)));
            $superfluousReplacements = [
                preg_replace('/[^a-z]/', '', strtolower($declarationName)),
                $type,
            ];
            if ('function' === $type) {
                $superfluousReplacements[] = 'method';
                if ('__' === substr($declarationName, 0, 2)) {
                    $superfluousReplacements[] = 'magic';
                }
            }
            if (isset($tokens[$stackPtr]['conditions']) && 0 < count($tokens[$stackPtr]['conditions'])) {
                $parentPos = key($tokens[$stackPtr]['conditions']);
                try {
                    $superfluousReplacements[] = preg_replace('/[^a-z]/', '', strtolower($phpcsFile->getDeclarationName($parentPos)));
                    $superfluousReplacements[] = $tokens[$parentPos]['content'];
                } catch (RuntimeException $e) {
                    // Ignore if the declaration name of the parent cannot be found
                }
            }

            if (3 >= strlen($filteredDocBlockString) || 3 >= strlen(str_replace($superfluousReplacements, '', $filteredDocBlockString))) {
                $code = 'Superfluous';
                $errorPos = key($docBlockStrings);
                $errorMssgData = [implode(' ', $docBlockStrings), $type, $declarationName, ('function' === $type ? '()' : '')];
                if (!isset($tokens[$commentStart]['comment_tags']) || 0 === count($tokens[$commentStart]['comment_tags'])) {
                    $errorMssg = 'Doc comment "%s" for %s %s%s is superfluous and must be improved or removed';
                    $fixRemoveWhole = $phpcsFile->addFixableError($errorMssg, $errorPos, $code, $errorMssgData);
                } else {
                    $errorMssg = 'Doc comment description "%s" for %s %s%s is superfluous and must be improved or removed';
                    $fixRemoveLinePos = ($phpcsFile->addFixableError($errorMssg, $errorPos, $code, $errorMssgData) ? $errorPos : false);
                }
            }
        }

        if ($fixRemoveWhole) {
            // Clear the whole doc block including preceding and succeeding white space
            $linePos = $commentStart - 1;
            while ($tokens[$linePos]['line'] === $tokens[$commentStart]['line'] && T_WHITESPACE === $tokens[$linePos]['code']) {
                $linePos--;
            }

            $phpcsFile->fixer->beginChangeset();
            $linePos++;
            do {
                $phpcsFile->fixer->replaceToken($linePos, '');
            } while ($tokens[++$linePos]['line'] <= $tokens[$commentEnd]['line']
                && ($linePos <= $commentEnd || T_WHITESPACE === $tokens[$linePos]['code']));
            $phpcsFile->fixer->endChangeset();
        } elseif (false !== $fixRemoveLinePos) {
            $phpcsFile->fixer->beginChangeset();
            $commentEnd = $tokens[$commentStart]['comment_closer'];
            ZichtPhpCs_File::fixRemoveWholeLine($phpcsFile, $fixRemoveLinePos, ['start' => $commentStart, 'end' => $commentEnd]);
            while (null !== ($nextLinePos = ZichtPhpCs_File::getNextLine($phpcsFile, (isset($nextLinePos) ? $nextLinePos : $fixRemoveLinePos)))
                && '' === trim(ZichtPhpCs_File::getLineContents($phpcsFile, $nextLinePos), " *\r\n")
                || !ZichtPhpCs_File::lineContainsTokens($phpcsFile, $nextLinePos, [T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_TAG])) {
                ZichtPhpCs_File::fixRemoveWholeLine($phpcsFile, $nextLinePos);
            }
            $phpcsFile->fixer->endChangeset();
        }

        return $docBlockEmpty;
    }
}
