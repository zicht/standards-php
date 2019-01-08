<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class UseStatementSniff implements Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_USE,
        ];
    }

    /**
     * {@inheritdoc}
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

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     */
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
                $stackPtr,
                'SplitUp'
            );
        }
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     */
    public function processUseStatementPosition(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $ptr = $stackPtr;
        // Scan back to the start of file, and see if certain tokens occur before the use statement
        $tokensNotAllowedBeforeUseStatement = [
            T_VARIABLE, T_STATIC, T_UNSET, T_CONST, T_LIST, T_CALLABLE,
            T_NEW, T_CLOSURE, T_OBJECT_OPERATOR, T_SELF,
            T_WHILE, T_DO, T_FOR, T_FOREACH, T_SWITCH, T_BREAK, T_CONTINUE,
            T_IF, T_ELSE, T_ELSEIF, T_ENDIF,
            T_THROW, T_TRY, T_CATCH, T_FINALLY,
            T_RETURN, T_EXIT, T_ECHO,
        ];

        do {
            --$ptr;
        } while ($ptr > 0 && !in_array($tokens[ $ptr ]['code'], $tokensNotAllowedBeforeUseStatement));

        if ($ptr !== 0) {
            $error = 'Use statement on any other position than top of file is discouraged. Found %s on line %d';
            $phpcsFile->addWarning($error, $stackPtr, 'TopOfFile', [$tokens[ $ptr ]['type'], $tokens[ $ptr ]['line']]);
        } else {
            if (!empty($tokens[ $stackPtr - 1 ])) {
                $previous = $tokens[ $stackPtr - 1 ];
                if ($previous['content']{strlen($previous['content']) - 1} != "\n") {
                    $phpcsFile->addWarning(
                        'Use statement should be on its own line.',
                        $stackPtr,
                        'OwnLine'
                    );
                }
            }
        }
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     */
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
