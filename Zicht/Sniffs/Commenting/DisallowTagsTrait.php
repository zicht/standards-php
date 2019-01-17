<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff as PearClassCommentSniff;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff as PearFileCommentSniff;
use Zicht\PhpCsFile as ZichtPhpCs_File;

trait DisallowTagsTrait
{
    /**
     * Processes disallowed tags, after that, follow normal process for other tags
     *
     * @param File $phpcsFile    The file being scanned.
     * @param int $stackPtr      The position of the current token in the stack passed in $tokens.
     * @param int $commentStart  Position in the stack where the comment started.
     * @see PearFileCommentSniff::processTags()
     */
    protected function processDisallowedTags($phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();

        $docBlock = $this instanceof PearClassCommentSniff ? 'class' : 'file';

        foreach ($tokens[$commentStart]['comment_tags'] as $tagPos) {
            $name = $tokens[$tagPos]['content'];
            if (!isset($this->tags[$name])) {
                continue;
            }
            if (isset($this->tags[$name]['disallow']) && true === $this->tags[$name]['disallow']) {
                $error = 'Tag %s is not allowed in %s comment';
                $data = [$name, $docBlock];
                $code = sprintf('NotAllowed%sTag', ucfirst(substr($name, 1)));
                $fix = $phpcsFile->addFixableError($error, $tagPos, $code, $data);
                if ($fix) {
                    $phpcsFile->fixer->beginChangeset();
                    $commentEnd = $tokens[$commentStart]['comment_closer'];
                    ZichtPhpCs_File::fixRemoveWholeLine($phpcsFile, $tagPos, ['start' => $commentStart, 'end' => $commentEnd]);
                    while (null !== ($nextLinePos = ZichtPhpCs_File::getNextLine($phpcsFile, (isset($nextLinePos) ? $nextLinePos : $tagPos)))
                        && '' === trim(ZichtPhpCs_File::getLineContents($phpcsFile, $nextLinePos), " *\r\n")
                        || !ZichtPhpCs_File::lineContainsTokens($phpcsFile, $nextLinePos, [T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_TAG])) {
                        ZichtPhpCs_File::fixRemoveWholeLine($phpcsFile, $nextLinePos);
                    }
                    $phpcsFile->fixer->endChangeset();
                }
            }
        }

        // Only process other tags that not contain `disallow`
        $originalTags = $this->tags;
        $this->tags = array_filter(
            $this->tags,
            function ($opts) {
                return !isset($opts['disallow']) || true !== $opts['disallow'];
            }
        );

        parent::processTags($phpcsFile, $stackPtr, $commentStart);

        $this->tags = $originalTags;
    }
}
