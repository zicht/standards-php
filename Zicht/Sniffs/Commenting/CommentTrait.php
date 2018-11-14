<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\File;
use Zicht\PhpCsDocComment;
use Zicht\PhpCsFile as ZichtPhpCs_File;

trait CommentTrait
{
    /**
     * @var int The minimum string length a description should have after filtering out all superfluous words and
     *          non alphabetic characters
     */
    public $minLengthFilteredDescription = 4;

    /**
     * Get doc comment info (description, tags, etc.)
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param int $commentStart
     * @return PhpCsDocComment
     */
    protected function parseDocComment(File $phpcsFile, $stackPtr, $commentStart)
    {
        return new PhpCsDocComment($phpcsFile, $stackPtr, $commentStart);
    }

    /**
     * Check if doc comment is empty or superfluous
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param int|null $commentStart
     * @return bool
     */
    protected function processIsEmptyOrSuperfluousDocComment(File $phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();

        $commentEnd = $tokens[$commentStart]['comment_closer'];
        $docComment = $this->parseDocComment($phpcsFile, $stackPtr, $commentStart);

        $fixRemoveWhole = $fixRemoveLinePos = false;
        try {
            $declarationName = $phpcsFile->getDeclarationName($stackPtr);
            $type = strtolower($tokens[$stackPtr]['content']);
        } catch (RuntimeException $e) {
            $declarationName = basename($phpcsFile->getFilename());
            $type = 'file';
        }

        if ($docComment->isEmpty()) {
            $fixRemoveWhole = $phpcsFile->addFixableError(
                'Doc comment for %s %s%s is empty and must be removed',
                $commentEnd,
                'Empty',
                [$type, $declarationName, ('function' === $type ? '()' : '')]
            );
        } elseif (0 < count($docComment->getDescriptionStrings())) {
            /**
             * - Filter out stopwords
             * - Filtering out everything that is not an `a-z` character leaves an empty comment: so superfluous
             * - Filtered comment is exactly the same as filtered function name:
             *   comment doesn't add anything => superfluous
             * - Comment is the same as type + name: "Function/Method Bla Bla Xyz" ~ "blaBlyXyz()" => superfluous
             */
            $descriptionStrings = strtolower(implode(' ', $docComment->getDescriptionStrings()));
            $filteredDocBlockString = preg_replace(
                '/ (a|an|and|as|at|for|in|it|no|not|of|on|or|the|to)(?= )/',
                ' ',
                $descriptionStrings
            );
            $filteredDocBlockString = preg_replace('/[^a-z]+/', '', $filteredDocBlockString);
            $superfluousReplacements = [
                preg_replace('/[^a-z]/', '', strtolower($declarationName)),
                $type,
            ];

            if ('function' === $type) {
                $superfluousReplacements[] = 'method';
                if ('__' === substr($declarationName, 0, 2)) {
                    $superfluousReplacements[] = 'magic';
                }
                if ('__construct' === $declarationName) {
                    $superfluousReplacements = array_merge($superfluousReplacements, ['initialize', 'new', 'instance']);
                }
            }
            if (isset($tokens[$stackPtr]['conditions']) && 0 < count($tokens[$stackPtr]['conditions'])) {
                $parentPos = key($tokens[$stackPtr]['conditions']);
                try {
                    $declarationName = strtolower($phpcsFile->getDeclarationName($parentPos));
                    $superfluousReplacements[] = preg_replace('/[^a-z]/', '', $declarationName);
                    $superfluousReplacements[] = $tokens[$parentPos]['content'];
                } catch (RuntimeException $e) {
                    // Ignore if the declaration name of the parent cannot be found
                }
            }

            if (strlen($filteredDocBlockString) < $this->minLengthFilteredDescription
                || strlen(
                    str_replace($superfluousReplacements, '', $filteredDocBlockString)
                ) < $this->minLengthFilteredDescription
            ) {
                $code = 'Superfluous';
                $errorPos = key($docComment->getDescriptionStrings());
                $errorMssgData = [
                    implode(' ', $docComment->getDescriptionStrings()),
                    $type,
                    $declarationName,
                    ('function' === $type ? '()' : ''),
                ];
                if (!isset($tokens[$commentStart]['comment_tags'])
                    || 0 === count($tokens[$commentStart]['comment_tags'])) {
                    $errorMssg = 'Doc comment "%s" for %s %s%s is superfluous and must be improved or removed';
                    $fixRemoveWhole = $phpcsFile->addFixableError($errorMssg, $errorPos, $code, $errorMssgData);
                } else {
                    $errorMssg = 'Doc comment description "%s" for %s %s%s is superfluous '
                        . 'and must be improved or removed';
                    $fixRemoveLinePos = ($phpcsFile->addFixableError($errorMssg, $errorPos, $code, $errorMssgData)
                        ? $errorPos : false);
                }
            }
        }

        if ($fixRemoveWhole) {
            // Clear the whole doc block including preceding and succeeding white space
            $linePos = $commentStart - 1;
            while ($tokens[$linePos]['line'] === $tokens[$commentStart]['line']
                && T_WHITESPACE === $tokens[$linePos]['code']) {
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
            ZichtPhpCs_File::fixRemoveWholeLine(
                $phpcsFile,
                $fixRemoveLinePos,
                ['start' => $commentStart, 'end' => $commentEnd]
            );
            $nextLinePos = $fixRemoveLinePos;
            while (null !== ($nextLinePos = ZichtPhpCs_File::getNextLine($phpcsFile, $nextLinePos))
                && '' === trim(ZichtPhpCs_File::getLineContents($phpcsFile, $nextLinePos), " *\r\n")
                || !ZichtPhpCs_File::lineContainsTokens(
                    $phpcsFile,
                    $nextLinePos,
                    [T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_TAG]
                )) {
                ZichtPhpCs_File::fixRemoveWholeLine($phpcsFile, $nextLinePos);
            }
            $phpcsFile->fixer->endChangeset();
        }

        return $docComment->isEmpty();
    }
}
