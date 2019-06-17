<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff as PearFunctionCommentSniff;
use PHP_CodeSniffer\Util\Tokens;
use Zicht\StandardsPhp\DocComment;
use Zicht\StandardsPhp\FileUtils;

/**
 * Override of PearFunctionCommentSniff to implement @inheritDoc and check for self explanatory definitions
 */
class FunctionCommentSniff extends PearFunctionCommentSniff implements Sniff
{
    use CommentTrait;

    /** @var bool */
    protected $paramsNeedProcessing;

    /** @var bool */
    protected $returnNeedsProcessing;

    /**
     * Checking if there's a PHP Doc block for this function and see what needs to be processed
     *
     * {@inheritDoc}
     *
     * @throws \PHP_CodeSniffer\Exceptions\TokenizerException
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $find   = Tokens::$methodPrefixes + [T_WHITESPACE => T_WHITESPACE];

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if (T_COMMENT === $tokens[$commentEnd]['code']) {
            $prev = $phpcsFile->findPrevious($find, ($commentEnd - 1), null, true);
            if (false !== $prev && $tokens[$prev]['line'] === $tokens[$commentEnd]['line']) {
                $commentEnd = $prev;
            }
        }

        if (T_COMMENT === $tokens[$commentEnd]['code']) {
            // Make parent error on wrong comment type
            parent::process($phpcsFile, $stackPtr);
            return;
        }

        $hasDocComment = T_DOC_COMMENT_CLOSE_TAG === $tokens[$commentEnd]['code'];
        $commentStart = ($hasDocComment ? $tokens[$commentEnd]['comment_opener'] : null);
        $docComment = ($hasDocComment ? $this->parseDocComment($phpcsFile, $stackPtr, $commentStart) : null);

        $inheritDoc = $hasDocComment && $this->processInheritDoc($phpcsFile, $docComment);
        $hasParamDefined = $hasDocComment && $docComment->hasTag('@param');
        $hasReturnDefined = $hasDocComment && $docComment->hasTag('@return');

        $this->paramsNeedProcessing = $hasParamDefined
            || !$inheritDoc && $this->hasParams($phpcsFile, $stackPtr)
            && !$this->hasAllParamTypesDeclared($phpcsFile, $stackPtr);

        $this->returnNeedsProcessing = $hasReturnDefined
            || !$inheritDoc && $this->doesReturnSomething($phpcsFile, $stackPtr)
            && !$this->hasReturnTypeDeclared($phpcsFile, $stackPtr);

        if ($hasDocComment) {
            $this->processIsEmptyOrSuperfluousDocComment($phpcsFile, $stackPtr, $commentStart);
        }
        if ($hasDocComment || $this->paramsNeedProcessing || $this->returnNeedsProcessing) {
            parent::process($phpcsFile, $stackPtr);
        }

        // Unset for the next call to process()
        unset($this->paramsNeedProcessing, $this->returnNeedsProcessing);
    }

    /**
     * {@inheritDoc}
     */
    protected function processParams(File $phpcsFile, $stackPtr, $commentStart)
    {
        if ($this->paramsNeedProcessing) {
            parent::processParams($phpcsFile, $stackPtr, $commentStart);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function processReturn(File $phpcsFile, $stackPtr, $commentStart)
    {
        if ($this->returnNeedsProcessing) {
            parent::processReturn($phpcsFile, $stackPtr, $commentStart);
        }
    }

    /**
     * Process comment to look for and validate `@inheritdoc` tags
     *
     * @param File $phpcsFile
     * @param DocComment $docComment
     * @return bool
     */
    protected function processInheritDoc(File $phpcsFile, $docComment)
    {
        $tokens = $phpcsFile->getTokens();

        $correctInheritDocTag = '{@inheritDoc}';

        $inheritDoc = $hasInlineTag = $docComment->hasInlineTag('@inheritdoc');
        if ($docComment->hasTag('@{inheritdoc}')) {
            $inheritDoc = true;
            foreach ($docComment->getTag('@{inheritdoc}') as $inheritDocPos => $tagDesc) {
                $error = 'Wrong syntax for inherit doc tag: found %s, expecting %s';
                $data = [$tokens[$inheritDocPos]['content'], $correctInheritDocTag];
                $fix = $phpcsFile->addFixableError($error, $inheritDocPos, 'WrongInheritDocTagSyntax', $data);
                if ($fix) {
                    $phpcsFile->fixer->replaceToken($inheritDocPos, $correctInheritDocTag);
                }
            }
        }

        if ($docComment->hasTag('@inheritdoc')) {
            $inheritDoc = true;
            foreach ($docComment->getTag('@inheritdoc') as $inheritDocPos => $tagDesc) {
                $error = (!$hasInlineTag ? 'Inherit doc tag should be written inline: found %s, expecting %s'
                    : 'Inherit doc tag should be written inline only. Separate tag should be removed');
                $data = [$tokens[$inheritDocPos]['content'], $correctInheritDocTag];
                $fix = $phpcsFile->addFixableError($error, $inheritDocPos, 'WrongInheritDocTagSyntax', $data);
                if ($fix) {
                    if ($hasInlineTag) {
                        $phpcsFile->fixer->beginChangeset();
                        FileUtils::fixRemoveWholeLine(
                            $phpcsFile,
                            $inheritDocPos,
                            ['end' => $docComment->getCommentEnd()]
                        );
                        $phpcsFile->fixer->endChangeset();
                    } else {
                        $phpcsFile->fixer->replaceToken($inheritDocPos, $correctInheritDocTag);
                    }
                }
            }
        }

        if ($hasInlineTag && false === strpos($docComment->getDescription(), $correctInheritDocTag)) {
            $error = 'Inherit doc tag should be written camelcase: found %s, expecting %s';
            foreach ($docComment->getDescriptionStrings() as $descPos => $description) {
                if (false === stripos($description, $correctInheritDocTag)) {
                    continue;
                }
                $found = substr(
                    $tokens[$descPos]['content'],
                    stripos($tokens[$descPos]['content'], $correctInheritDocTag),
                    strlen($correctInheritDocTag)
                );
                $data = [$found, $correctInheritDocTag];
                $fix = $phpcsFile->addFixableError($error, $descPos, 'WrongInheritDocTagSyntax', $data);
                if ($fix) {
                    $phpcsFile->fixer->replaceToken($descPos, $correctInheritDocTag);
                }
            }
        }

        return $inheritDoc;
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return bool
     * @throws \PHP_CodeSniffer\Exceptions\TokenizerException
     */
    protected function hasParams(File $phpcsFile, $stackPtr)
    {
        return 0 < count($phpcsFile->getMethodParameters($stackPtr));
    }

    /**
     * Analyse if the function's params types are all declared
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return bool
     * @throws \PHP_CodeSniffer\Exceptions\TokenizerException
     */
    protected function hasAllParamTypesDeclared(File $phpcsFile, $stackPtr)
    {
        $funcParams = $phpcsFile->getMethodParameters($stackPtr);
        foreach ($funcParams as $funcParam) {
            if (!isset($funcParam['type_hint']) || empty($funcParam['type_hint'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find if there is a return statement in de function that actually returns something (`return X;`, not `return;`)
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return bool
     */
    protected function doesReturnSomething(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (!array_key_exists('scope_opener', $tokens[$stackPtr])) {
            // No scope, might be inside an interface or an abstract declatared method
            return false;
        }

        $start = $tokens[$stackPtr]['scope_opener'] + 1;
        do {
            /**
             * Disable message about T_YIELD_FROM. PHPCS will define the constant itself if it is not defined
             * @see vendor/squizlabs/php_codesniffer/src/Util/Tokens.php:114
             */
            // phpcs:disable PHPCompatibility.Constants.NewConstants.t_yield_fromFound
            $return = $phpcsFile->findNext(
                [T_RETURN, T_CLOSURE, T_YIELD, T_YIELD_FROM],
                $start,
                $tokens[$stackPtr]['scope_closer'] - 1
            );

            if (in_array($tokens[$return]['code'], [T_YIELD, T_YIELD_FROM], true)) {
                // `yield` indicates a `\Generator` return type
                return true;
            }
            // phpcs:enable PHPCompatibility.Constants.NewConstants.t_yield_fromFound

            if (T_CLOSURE === $tokens[$return]['code']) {
                // Skip the body of any closures found
                $start = $tokens[$return]['scope_closer'] + 1;
                continue;
            }

            if (false !== $return) {
                $start = $return + 1;
                $returnWhat = $phpcsFile->findNext(
                    [T_WHITESPACE],
                    $start,
                    $tokens[$stackPtr]['scope_closer'] - 1,
                    true
                );
                if (T_SEMICOLON !== $tokens[$returnWhat]['code']) {
                    return true;
                }
            }
        } while (false !== $return);

        return false;
    }

    /**
     * Analyse if the function's return type is declared
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return bool
     * @throws \PHP_CodeSniffer\Exceptions\TokenizerException
     */
    protected function hasReturnTypeDeclared(File $phpcsFile, $stackPtr)
    {
        $funcProperties = $phpcsFile->getMethodProperties($stackPtr);

        if (!isset($funcProperties['return_type']) || empty($funcProperties['return_type'])
            && $this->doesReturnSomething($phpcsFile, $stackPtr)) {
            return false;
        }

        return true;
    }
}
