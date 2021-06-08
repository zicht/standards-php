<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\Commenting;

use PHP_CodeSniffer\Exceptions\RuntimeException as PhpCsRuntimeException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Util\Tokens;
use Zicht\StandardsPhp\DocComment;
use Zicht\StandardsPhp\FileUtils;

/**
 * Checks for empty or superfluous class doc comments, sniffs the doc comment tags in class level comments
 */
class PropertyCommentSniff extends AbstractVariableSniff
{
    /**
     * @var array<string, array<string, bool>>
     */
    protected $tags = [
        '@var' => [
            'required' => true,
            'allow_multiple' => false,
            'allow_empty' => false,
        ],
        '@link' => [
            'required' => false,
            'allow_multiple' => true,
            'allow_empty' => false,
        ],
        '@see' => [
            'required' => false,
            'allow_multiple' => true,
            'allow_empty' => false,
        ],
        '@deprecated' => [
            'required' => false,
            'allow_multiple' => false,
            'allow_empty' => false,
        ],
        '@example' => [
            'required' => false,
            'allow_multiple' => true,
        ],
    ];

    /** @var string[] */
    protected $otherPhpDocTags = [
        '@api', '@author', '@category', '@const', '@constant', '@copyright', '@filesource', '@global', '@ignore',
        '@internal', '@license', '@method', '@package', '@param', '@property', '@property-read', '@property-write',
        '@return', '@since', '@source', '@subpackage', '@throws', '@todo', '@uses', '@used-by', '@version',
    ];

    /**
     * Process T_VARIABLE tokens. Properties are marked as T_VARIABLE just like all other variables, so we need to try
     * to filter out only the variables that are in a class scope and not in any other (sub) scope.
     *
     * {@inheritDoc}
     *
     * @see \PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\VariableCommentSniff::processMemberVar()
     */
    public function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $ignore = array_merge(Tokens::$scopeModifiers, [T_VAR, T_STATIC, T_NULLABLE, T_STRING, T_WHITESPACE]);
        $commentEnd = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        $hasTypeDef = $tokens[$phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), $commentEnd ? $commentEnd : null, true)]['code'] === T_STRING;

        // If the var does not have a type definition, then it should have a comment (with @var type)
        if ($commentEnd === false || ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT)) {
            if (!$hasTypeDef) {
                $phpcsFile->addError('Missing property doc comment', $stackPtr, 'Missing');
            }
            return;
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a property comment', $stackPtr, 'WrongStyle');
            return;
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];

        // We found a property, now find its comment (if any)
        $nextNonWsPtrAfterComment = $phpcsFile->findNext(T_WHITESPACE, $commentEnd + 1, $stackPtr - 1, true);
        if ($tokens[$commentEnd]['line'] < $tokens[$nextNonWsPtrAfterComment]['line'] - 1) {
            $error = 'Doc comment for member var must be directly above the property. Found %d lines in between.';
            $numLines = $tokens[$nextNonWsPtrAfterComment]['line'] - $tokens[$commentEnd]['line'] - 1;
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'WhiteSpaceInBetween', [$numLines]);
            if ($fix) {
                $phpcsFile->fixer->beginChangeset();
                $nextLinePos = $commentEnd;
                while (null !== ($nextLinePos = FileUtils::getNextLine($phpcsFile, $nextLinePos))
                    && $tokens[$nextLinePos]['line'] < $tokens[$stackPtr]['line']) {
                    FileUtils::fixRemoveWholeLine($phpcsFile, $nextLinePos);
                }
                $phpcsFile->fixer->endChangeset();
            }
        }

        $docComment = new DocComment($phpcsFile, $stackPtr, $commentStart);

        // Check for separate description
        if (0 < count($docComment->getDescriptionStrings()) && (!$hasTypeDef || $docComment->hasTag('@var'))) {
            $error = 'Description in property doc comment should be placed in the @var tag, '
                . 'not separately on top of the doc comment';
            $phpcsFile->addError($error, key($docComment->getDescriptionStrings()), 'DescriptionNotAllowed');
        }

        // Check for empty lines
        if ($tokens[$commentStart]['line'] < $tokens[$commentEnd]['line'] - 1) {
            $line = null;
            for ($pos = $commentStart + 1; $pos < $commentEnd; $pos++) {
                if ($line < $tokens[$pos]['line']
                    && $tokens[$pos]['line'] !== $tokens[$commentStart]['line']
                    && $tokens[$pos]['line'] !== $tokens[$commentEnd]['line']) {
                    if ('' === trim(FileUtils::getLineContents($phpcsFile, $pos), " \t*\r\n")) {
                        $error = 'Empty comment lines are not allowed in property doc comments';
                        $fix = $phpcsFile->addFixableError($error, $pos, 'EmptyLinesNotAllowed');
                        if ($fix) {
                            $phpcsFile->fixer->beginChangeset();
                            FileUtils::fixRemoveWholeLine($phpcsFile, $pos);
                            $phpcsFile->fixer->endChangeset();
                        }
                    }
                    $line = $tokens[$pos]['line'];
                }
            }
        }

        // Check for missing (required) tags and check for tags that are not allowed to be placed multiple times
        foreach ($this->tags as $tag => $tagConfig) {
            $tagCode = ucfirst(strtolower(substr($tag, 1)));
            if (isset($tagConfig['required']) && true === $tagConfig['required'] && !$docComment->hasTag($tag)
                && ($tag !== '@var' || !$hasTypeDef)) {
                $error = 'Missing %s tag in property comment';
                $phpcsFile->addError($error, $commentEnd, 'Missing' . $tagCode, [$tag]);
            }
            if (isset($tagConfig['allow_multiple']) && false === $tagConfig['allow_multiple']
                && $docComment->hasTag($tag) && count($docComment->getTag($tag)) > 1) {
                $error = 'Only one %s tag is allowed in a property comment';
                $errorPos = array_keys($docComment->getTag($tag))[1];
                $phpcsFile->addError($error, $errorPos, 'Duplicate' . $tagCode . 'Tag', [$tag]);
            }
        }

        // Check each tag's content (is it allowed to be empty?) and call a specific tag's processor, if any exists
        $tagPrevLine = null;
        foreach ($docComment->getTags() as $tag => $positions) {
            $tagCode = ucfirst(strtolower(substr($tag, 1)));
            foreach ($positions as $tagPos => $descriptionStrings) {
                $allowEmpty = !isset($this->tags[$tag]['allow_empty']) || true === $this->tags[$tag]['allow_empty'];
                $isEmpty = [] === $descriptionStrings;

                if (!$allowEmpty && $isEmpty) {
                    $error = 'Content missing for %s tag in property comment';
                    $phpcsFile->addError($error, $tagPos, 'Empty' . $tagCode . 'Tag', [$tag]);
                } elseif (!$isEmpty) {
                    $tagMethodName = sprintf('process%sTag', ucfirst(substr($tag, 1)));
                    /**
                     * @see PropertyCommentSniff::processVarTag()
                     */
                    if (method_exists($this, $tagMethodName)) {
                        $this->{$tagMethodName}($phpcsFile, $docComment, $tagPos, $descriptionStrings);
                    }
                }
            }
        }

        $tags = $docComment->getTags();

        // Check for tags that are not allowed
        foreach (array_intersect_key($tags, array_flip($this->otherPhpDocTags)) as $otherTag => $positions) {
            $tagCode = str_replace(' ', '', ucwords(str_replace('-', ' ', substr($otherTag, 1))));
            $error = '%s tag is not allowed in property doc comment';
            $pos = key($positions);
            $phpcsFile->addError($error, $pos, $tagCode . 'TagNotAllowed', [$tokens[$pos]['content']]);
        }

        // Check order of tags: @var always first (check if there is a @var tag: Missing @var tag handled above)
        if ($docComment->hasTag('@var') && '@var' !== key($tags)) {
            $error = '@var tag must come first in property doc comment, found %s as first tag';
            $pos = key($tags[key($tags)]);
            $tagTextual = preg_replace('/^(@[A-z0-9_\-\\\\]+).*$/', '$1', $tokens[$pos]['content']);
            $phpcsFile->addError($error, $pos, 'VarTagFirst', [$tagTextual]);
        }

        // Check order of tags: Official PHPDoc tags always first before other CamelCased/\Name\Spaced tags
        $alienTagPosFound = false;
        $officialTags = array_merge($this->otherPhpDocTags, array_keys($this->tags));
        foreach ($tokens[$commentStart]['comment_tags'] as $tagPos) {
            $tag = strtolower($tokens[$tagPos]['content']);
            $currentTagIsAlien = !in_array($tag, $officialTags, true);
            if ($currentTagIsAlien) {
                $alienTagPosFound = $tagPos;
            } elseif (false !== $alienTagPosFound && !$currentTagIsAlien && '@var' !== $tag) {
                $tagTextual = preg_replace('/^(@[A-z0-9_\-\\\\]+).*$/', '$1', $tokens[$alienTagPosFound]['content']);
                $phpcsFile->addError(
                    'Official PHPDoc tags must be placed before any custom tags in property doc comment: '
                        . 'found %s before %s',
                    $alienTagPosFound,
                    'OfficialTagsFirst',
                    [$tagTextual, $tokens[$tagPos]['content']]
                );
            }
        }
    }

    /**
     * Must implement this because AbstractVariableSniff::processVariable() is abstract,
     * but there is no need for processing regular variables in this sniff
     * {@inheritDoc}
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {
    }

    /**
     * Must implement this because AbstractVariableSniff::processVariableInString() is abstract,
     * but there is no need for processing variables in strings in this sniff
     * {@inheritDoc}
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {
    }

    /**
     * @param File $phpcsFile
     * @param DocComment $docComment
     * @param int $tagPos
     * @param string[] $descriptionStrings
     * @return bool
     */
    protected function processVarTag(File $phpcsFile, $docComment, $tagPos, $descriptionStrings)
    {
        if (empty($descriptionStrings)) {
            return false;
        }

        $descLine1 = current($descriptionStrings);

        /**
         * Create a regex pattern that will match all possible types. This tests the overall syntax. Weird things or
         * exotic definitions should still be captured by human review.
         *
         * @see https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md#appendix-a-types
         * @see http://php.net/manual/en/language.oop5.basic.php
         * @example
         *         int|string|null            Just your regular definition with multiple possible types
         *         (int|string|null)[]        Array definition of multiple types possible as elements
         *         array<int, string>         Java/Google array<T> type definition with integer keys and string elements
         *         \SomeNs\SomeClass          Typical class type definition (or just use `SomeClass` when imported)
         * @example Or go wild and combine all the things!
         *         ArrayCollection<Object, (string|int)[]>|(bool[]|\Exception[])[]
         */
        $classPattern = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9\-_\x7f-\xff]*';
        $varPattern = sprintf('(\\\\?%s(?:\\\\%1$s)*)', $classPattern);
        $arrayPostfix = '(?:\\[\\])+|<(?:(?R), ?)?(?R)>';
        $typePattern = sprintf('(%1$s(%2$s)?|\\(((?R)(?:\\|(?R))+)\\)(%2$s))', $varPattern, $arrayPostfix);
        $fullPattern = $typePattern . '(?:[\\|&]' . $typePattern . ')*';

        $varTagContentPattern = str_replace('(?R)', '(?2)', '/^(' . $fullPattern . ')(?:[ \t]*$|[ \t]+(?P<d>.+)$)/');
        if (0 === (int)preg_match($varTagContentPattern, $descLine1, $match)) {
            $error = 'Could not find a valid @var type for property';
            $phpcsFile->addError($error, $tagPos, 'InvalidVarType');
            return false;
        }

        // Get the type definition part only for further processing
        $valid = true;
        $typesFound = [];
        $typeStrings = [substr($descLine1, 0, strlen($match[1]))];
        do {
            foreach ($typeStrings as $i => $typeString) {
                preg_match_all('/' . $typePattern . '/', $typeString, $typeMatches, PREG_SET_ORDER);
                foreach ($typeMatches as $typeMatch) {
                    // What do we have?
                    $arraySpec = null;
                    if (isset($typeMatch[2]) && !empty($typeMatch[2])) {
                        // Single type
                        $arraySpec = isset($typeMatch[3]) && !empty($typeMatch[3]) ? $typeMatch[3] : null;
                        $valid = $valid && $this->processVarTagType($phpcsFile, $tagPos, $typeMatch[2]);
                        $typesFound[] = $typeMatch[2];
                    } elseif (isset($typeMatch[4]) && !empty($typeMatch[4])) {
                        // Multiple types, add to array for further processing
                        $typeStrings[] = $typeMatch[4];
                        $arraySpec = isset($typeMatch[5]) && !empty($typeMatch[5]) ? $typeMatch[5] : null;
                    }

                    if (null !== $arraySpec && '<' === substr($arraySpec, 0, 1)) {
                        // Process types of an `array<type, type>` notation
                        preg_match(
                            '/^<(?:(?P<k>' . str_replace('(?R)', '(?2)', $fullPattern) . '), ?)?'
                            . '(?P<v>' . str_replace('(?R)', '(?13)', $fullPattern) . ')>$/',
                            $arraySpec,
                            $arraySpecMatch
                        );
                        $typeStrings[] = $arraySpecMatch['v'];
                        if (!empty($arraySpecMatch['k'])) {
                            // Add the key to be checked. If it is mixed, then add 'allow_' prefix to trick processVarTagType() to allow it
                            $typeStrings[] = ($arraySpecMatch['k'] === 'mixed' ? 'allow_' : '') . $arraySpecMatch['k'];
                        }
                    }
                }
                unset($typeStrings[$i]);
            }
        } while (0 < count($typeStrings));

        // Check rest of string, being the description
        $descriptionStrings[key($descriptionStrings)] = isset($match['d']) ? $match['d'] : null;
        if (0 < count(array_filter($descriptionStrings))) {
            $this->processVarTagDescription(
                $phpcsFile,
                $docComment,
                $tagPos,
                array_filter($descriptionStrings),
                $typesFound
            );
        }

        return $valid;
    }

    /**
     * @param File $phpcsFile
     * @param int $tagPos
     * @param string $type
     * @return bool
     */
    protected function processVarTagType(File $phpcsFile, $tagPos, $type)
    {
        if ('mixed' === strtolower($type)) {
            $warning = 'Avoid usage of "mixed" type. Try to define the specific types '
                . 'for type definition in @var tag for property';
            $phpcsFile->addWarning($warning, $tagPos, 'VarTypeAvoidMixed');
            return false;
        }

        if ('void' === strtolower($type)) {
            $warning = 'Type "void" should not be used. Try to define a specific type '
                . 'for type definition in @var tag for property';
            $phpcsFile->addWarning($warning, $tagPos, 'VarTypeAvoidMixed');
            return false;
        }

        if ('object' === $type) {
            $warning = 'Avoid usage of "object" type. Try to define the specific types of objects and/or interfaces '
                . 'for type definition in @var tag for property';
            $phpcsFile->addWarning($warning, $tagPos, 'VarTypeAvoidObject');
            return false;
        }

        $shortLongTypes = ['integer' => 'int', 'boolean' => 'bool'];
        if (array_key_exists(strtolower($type), $shortLongTypes)) {
            $warning = 'Found long type name "%s". Use short type "%s" for type definition in @var tag for property';
            $phpcsFile->addWarning($warning, $tagPos, 'VarTypeLong', [$type, $shortLongTypes[strtolower($type)]]);
            return false;
        }

        $types = [
            'null', 'bool', 'true', 'false', 'int', 'float', 'string', 'array', 'iterable', 'resource', 'callable',
        ];
        if (in_array(strtolower($type), $types) && $type !== strtolower($type)) {
            $warning = 'Type "%s" should be written lower case for type definition in @var tag for property';
            $phpcsFile->addWarning($warning, $tagPos, 'VarTypeWrongCase', [strtolower($type)]);
            return false;
        }

        return true;
    }

    /**
     * @param File $phpcsFile
     * @param DocComment $docComment
     * @param int $tagPos
     * @param string[] $description
     * @param string[] $superfluousWords
     */
    protected function processVarTagDescription(File $phpcsFile, $docComment, $tagPos, $description, $superfluousWords)
    {
        $tokens = $phpcsFile->getTokens();

        $filteredDescription = preg_replace('/[^a-z]/', '', strtolower(implode('', $description)));

        // Add property name and words
        $superfluousReplacements = [
            $tokens[$docComment->getStackPtr()]['content'],
            'property', 'member', 'variable', 'var', 'integer', 'boolean', 'number',
        ];
        try {
            foreach ($tokens[$docComment->getStackPtr()]['conditions'] as $scope => $code) {
                $superfluousReplacements[] = $tokens[$scope]['content'];
                $superfluousReplacements[] = $phpcsFile->getDeclarationName($scope);
            }
        } catch (PhpCsRuntimeException $e) {
            // Don't do anything when the declaration name of a condition cannot be determined
        }

        $superfluousReplacements = array_unique(
            array_map(
                function ($replacement) {
                    return preg_replace('/[^a-z]/', '', strtolower($replacement));
                },
                array_merge($superfluousReplacements, $superfluousWords)
            )
        );

        if (3 >= strlen($filteredDescription)
            || 3 >= strlen(str_replace($superfluousReplacements, '', $filteredDescription))) {
            $errorMssg = 'Description "%s" in @var tag for property is superfluous and must be improved or removed';
            $fix = $phpcsFile->addFixableError($errorMssg, $tagPos, 'Superfluous', [trim(implode(' ', $description))]);
            if ($fix) {
                $descStackPtrs = array_keys($description);
                $phpcsFile->fixer->beginChangeset();
                for ($pos = $descStackPtrs[0]; $pos <= $descStackPtrs[count($descStackPtrs) - 1]; $pos++) {
                    $replacement = '';
                    if ($pos === $descStackPtrs[0]) {
                        $replacement = rtrim(substr($tokens[$pos]['content'], 0, -1 * strlen($description[$pos])));
                    }
                    if ($pos + 1 === $docComment->getCommentEnd()) {
                        $replacement .= ' ';
                    }
                    $phpcsFile->fixer->replaceToken($pos, $replacement);
                }
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
