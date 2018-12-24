<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht;

use PHP_CodeSniffer\Files\File;

/**
 * @deprecated 3.4.1 Will be moved and renamed in version 4.0.0
 */
class PhpCsDocComment
{
    /** @var File */
    private $phpcsFile;

    /** @var array */
    private $tokens;

    /** @var int */
    private $stackPtr;

    /** @var int */
    private $commentStart;

    /** @var string[] */
    private $descriptionStrings = [];

    /** @var array<string, array<string[]>> */
    private $tags = [];

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param int $commentStart
     */
    public function __construct(File $phpcsFile, $stackPtr, $commentStart)
    {
        $this->phpcsFile = $phpcsFile;
        $this->tokens = $phpcsFile->getTokens();
        $this->stackPtr = $stackPtr;
        $this->commentStart = $commentStart;
        $this->parseDocComment();
    }

    /**
     * Get doc comment info (description, tags, etc.)
     */
    private function parseDocComment()
    {
        $this->descriptionStrings = $this->getDocCommentStringsAfterPos($this->commentStart);

        foreach ($this->tokens[$this->commentStart]['comment_tags'] as $tagPos) {
            $tag = strtolower($this->tokens[$tagPos]['content']);
            if (!isset($this->tags[$tag])) {
                $this->tags[$tag] = [];
            }
            $this->tags[$tag][$tagPos] = $this->getDocCommentStringsAfterPos($tagPos);
        }
    }

    /**
     * @param int $pos
     * @return array
     */
    private function getDocCommentStringsAfterPos($pos)
    {
        $strings = [];
        $find = [T_DOC_COMMENT_STRING, T_DOC_COMMENT_WHITESPACE, T_DOC_COMMENT_STAR];
        for ($p = $pos + 1; $p < $this->getCommentEnd(); $p++) {
            if (!in_array($this->tokens[$p]['code'], $find, true)) {
                break;
            }
            if (T_DOC_COMMENT_STRING === $this->tokens[$p]['code']) {
                $strings[$p] = $this->tokens[$p]['content'];
            }
        }

        return $strings;
    }

    /**
     * @return int
     */
    public function getStackPtr()
    {
        return $this->stackPtr;
    }

    /**
     * @return int
     */
    public function getCommentStart()
    {
        return $this->commentStart;
    }

    /**
     * @return int
     */
    public function getCommentEnd()
    {
        return $this->tokens[$this->commentStart]['comment_closer'];
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return 0 === count($this->descriptionStrings) && 0 === count($this->tags);
    }

    /**
     * @return string[]
     */
    public function getDescriptionStrings()
    {
        return $this->descriptionStrings;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return implode("\n", $this->descriptionStrings);
    }

    /**
     * @param string $tag
     * @param string|string[]|null $description
     * @return bool
     */
    public function hasInlineTag($tag, $description = null)
    {
        if (null !== $description && is_array($description)) {
            $description = implode("\n", $description);
        }
        return false !== stripos(
            (null === $description ? $this->getDescription() : $description),
            sprintf('{%s}', $tag)
        );
    }

    /**
     * @return array<string, array<string[]>>
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $tag
     * @return bool
     */
    public function hasTag($tag)
    {
        return array_key_exists(strtolower($tag), $this->tags);
    }

    /**
     * @param string $tag
     * @return array<string[]>|null
     */
    public function getTag($tag)
    {
        return array_key_exists(strtolower($tag), $this->tags)
            ? $this->tags[strtolower($tag)] : null;
    }
public function dump(){var_dump(['description'=>$this->descriptionStrings,'tags'=>$this->tags,'empty'=>$this->isEmpty(),]);}
}
