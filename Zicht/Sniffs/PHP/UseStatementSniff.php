<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sniffs for
 */
class UseStatementSniff implements Sniff
{
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register()
    {
        return [
            T_USE,
        ];
    }

    /**
     * Called when one of the token types that this sniff is listening for
     * is found.
     *
     * @param File $phpcsFile The PHP_CodeSniffer file where the
     *                                        token was found.
     * @param int $stackPtr The position in the PHP_CodeSniffer
     *                                        file's token stack where the token
     *                                        was found.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $ptr = $stackPtr;

        // only check if the use statement is part of the global scope
        while (--$ptr) {
            if (in_array($tokens[ $ptr ]['code'], [T_FUNCTION, T_CLASS, T_TRAIT, T_INTERFACE])) {
                return;
            }
        }

        $this->processUseStatementValue($phpcsFile, $stackPtr);
        $this->processUseStatementPosition($phpcsFile, $stackPtr);
        $this->processUseLists($phpcsFile, $stackPtr);
    }


    public function processUseLists(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $ptr = $stackPtr;
        $commas = 0;
        do {
            $ptr++;
            if ($tokens[ $ptr ]['code'] == T_COMMA) {
                $commas++;
            }
        } while ($tokens[ $ptr ]['code'] != T_SEMICOLON);

        if ($commas > 0) {
            $phpcsFile->addError(
                'Use statements must be split up, usage of comma\'s in use statement is disallowed',
                $stackPtr
            );
        }
    }


    public function processUseStatementPosition(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();


        $ptr = $stackPtr;
        // scan back to the start of file, and make sure only the specified tokens occur before
        // the use statement
        do {
            --$ptr;
        } while (
            $ptr > 0
            && in_array(
                $tokens[ $ptr ]['code'],
                [
                    T_OPEN_TAG,
                    T_DECLARE,
                    T_LNUMBER,
                    T_NAMESPACE,
                    T_COMMENT,
                    T_DOC_COMMENT,
                    T_WHITESPACE,
                    T_SEMICOLON,
                    T_STRING,
                    T_NS_SEPARATOR,
                    T_USE,
                    T_AS,
                    T_DOC_COMMENT_CLOSE_TAG,
                    T_DOC_COMMENT_STAR,
                    T_DOC_COMMENT_WHITESPACE,
                    T_DOC_COMMENT_TAG,
                    T_DOC_COMMENT_OPEN_TAG,
                    T_DOC_COMMENT_CLOSE_TAG,
                    T_DOC_COMMENT_STRING,
                ]
            )
        );
        if ($ptr !== 0) {
            $phpcsFile->addWarning(
                'Use statement on any other position than top of file is discouraged.',
                $stackPtr,
                'TopOfFile'
            );
        } else {
            if (!empty($tokens[ $stackPtr - 1 ])) {
                $previous = $tokens[ $stackPtr - 1 ];
                if ($previous['content']{strlen($previous['content']) - 1} != "\n") {
                    $phpcsFile->addWarning(
                        'Use statement should be on its own line.',
                        $stackPtr
                    );
                }
            }
        }
    }


    public function processUseStatementValue(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $ptr = $stackPtr;
        do {
            ++$ptr;
        } while ($tokens[ $ptr ]['code'] == T_WHITESPACE);

        if ($tokens[ $ptr ]['code'] == T_NS_SEPARATOR) {
            $code = '';
            do {
                $code .= $tokens[ $ptr ]['content'];
                ++$ptr;
            } while (isset($tokens[ $ptr ]) && !in_array($tokens[ $ptr ]['content'], [';', 'as']));
            $phpcsFile->addWarning(
                'Use statements should not refer global namespace, found ' . trim($code),
                $stackPtr,
                'GlobalReference'
            );
        }
    }
}
