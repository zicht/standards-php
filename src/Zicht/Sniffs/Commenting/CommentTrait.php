<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\File;
use Zicht\StandardsPhp\DocComment;
use Zicht\StandardsPhp\FileUtils;

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
     * @return DocComment
     */
    protected function parseDocComment(File $phpcsFile, $stackPtr, $commentStart)
    {
        return new DocComment($phpcsFile, $stackPtr, $commentStart);
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
                [$type, $declarationName, 'function' === $type ? '()' : '']
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
            $descriptionStrings = preg_replace('/[\{@]+inheritdoc\}?/', '', $descriptionStrings, -1, $inheritDocsCount);
            if ($inheritDocsCount > 0 && '' === trim($descriptionStrings)) {
                return false;
            }
            $filteredDocBlockString = preg_replace(
                '/ (a|all|an|and|as|at|for|in|it|no|not|of|on|or|the|to)(?= )/',
                ' ',
                $descriptionStrings
            );
            $filteredDocBlockString = preg_replace('/[^a-z]+/', '', $filteredDocBlockString);

            $nameReplacements = array_map('strtolower', preg_split('/(?=[A-Z])/', str_replace('_', '', $declarationName), -1, PREG_SPLIT_NO_EMPTY));
            $superfluousReplacements = [$type];
            $parentType = null;
            $parentPos = null;
            $classStructKeywords = ['class', 'interface', 'trait'];

            if ('function' === $type) {
                $superfluousReplacements[] = 'method';
                if (0 === strpos($declarationName, '__')) {
                    $superfluousReplacements[] = 'magic';
                }
                if ('__construct' === $declarationName) {
                    $superfluousReplacements = array_merge($superfluousReplacements, ['initialize', 'new', 'instance']);
                }
                if (T_STATIC === $tokens[$stackPtr - 2]['code']) {
                    $superfluousReplacements[] = 'statically';
                    $superfluousReplacements[] = $tokens[$stackPtr - 2]['content'];
                }
            }
            if (isset($tokens[$stackPtr]['conditions']) && 0 < count($tokens[$stackPtr]['conditions'])) {
                $parentPos = key($tokens[$stackPtr]['conditions']);
                try {
                    $parentDeclarationName = $phpcsFile->getDeclarationName($parentPos);
                } catch (RuntimeException $e) {
                    // Ignore if the declaration name of the parent cannot be found
                    $parentDeclarationName = '';
                }
                $nameReplacements = array_merge($nameReplacements, array_map('strtolower', preg_split('/(?=[A-Z])/', $parentDeclarationName, -1, PREG_SPLIT_NO_EMPTY)));
                $parentType = $tokens[$parentPos]['content'];
                $superfluousReplacements[] = $parentType;
            }

            // Type or parent type is a class/interface/trait, so add some more superfluous words
            if (in_array($type, $classStructKeywords, true) || in_array($parentType, $classStructKeywords, true)) {
                $typeToCheck = in_array($parentType, $classStructKeywords, true) ? $parentType : $type;
                $posToCheck = in_array($parentType, $classStructKeywords, true) ? $parentPos : $stackPtr;
                $superfluousReplacements[] = 'class';
                $superfluousReplacements[] = 'base';
                $isAbstract = 'class' === $typeToCheck && T_ABSTRACT === $tokens[$posToCheck - 2]['code'];
                if ($isAbstract) {
                    $superfluousReplacements[] = $tokens[$posToCheck - 2]['content'];
                }
            }

            $superfluousReplacements = array_unique(array_merge($nameReplacements, $superfluousReplacements));

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
                    'function' === $type ? '()' : '',
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
            FileUtils::fixRemoveWholeLine(
                $phpcsFile,
                $fixRemoveLinePos,
                ['start' => $commentStart, 'end' => $commentEnd]
            );
            $nextLinePos = $fixRemoveLinePos;
            while (null !== ($nextLinePos = FileUtils::getNextLine($phpcsFile, $nextLinePos))
                && '' === trim(FileUtils::getLineContents($phpcsFile, $nextLinePos), " *\r\n")
                || !FileUtils::lineContainsTokens(
                    $phpcsFile,
                    $nextLinePos,
                    [T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_TAG]
                )) {
                FileUtils::fixRemoveWholeLine($phpcsFile, $nextLinePos);
            }
            $phpcsFile->fixer->endChangeset();
        }

        return $docComment->isEmpty();
    }
}
