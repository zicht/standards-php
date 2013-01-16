<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

/**
 * Sniffs for
 */
class Zicht_Sniffs_PHP_UseStatementSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return array(int)
     * @see    Tokens.php
     */
    public function register() {
        return array(
            T_USE
        );
    }

    /**
     * Called when one of the token types that this sniff is listening for
     * is found.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The PHP_CodeSniffer file where the
     *                                        token was found.
     * @param int                  $stackPtr  The position in the PHP_CodeSniffer
     *                                        file's token stack where the token
     *                                        was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();
        $ptr = $stackPtr;

        do {
            $ptr++;
        } while ($tokens[$ptr]['code'] == T_WHITESPACE);

        if ($tokens[$ptr]['content'] == '(') {
            // skip, this is the case where use is part of a closure:
            // function() use($var) {}
            return;
        }

        $this->processUseStatementValue($phpcsFile, $stackPtr);
        $this->processUseStatementPosition($phpcsFile, $stackPtr);
        $this->processUseLists($phpcsFile, $stackPtr);
    }


    public function processUseLists(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();

        $ptr = $stackPtr;
        $commas = 0;
        do {
            $ptr++;
            if ($tokens[$ptr]['code'] == T_COMMA) {
                $commas++;
            }
        } while ($tokens[$ptr]['code'] != T_SEMICOLON);

        if ($commas > 0) {
            $phpcsFile->addError(
                'Use statements must be split up, usage of comma\'s in use statement is disallowed',
                $stackPtr
            );
        }
    }


    public function processUseStatementPosition(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();


        $ptr = $stackPtr;
        // scan back to the start of file, and make sure only the specified tokens occur before
        // the use statement
        do {
            --$ptr;
        } while (
            $ptr > 0
            && in_array(
                $tokens[$ptr]['code'],
                array(
                     T_OPEN_TAG,
                     T_NAMESPACE,
                     T_COMMENT,
                     T_DOC_COMMENT,
                     T_WHITESPACE,
                     T_SEMICOLON,
                     T_STRING,
                     T_NS_SEPARATOR,
                     T_USE,
                     T_AS
                )
            )
        );
        if ($ptr !== 0) {
            $phpcsFile->addWarning(
                'Use statement on any other position than top of file is discouraged.',
                $stackPtr,
                'TopOfFile'
            );
        } else {
            if (!empty($tokens[$stackPtr - 1])) {
                $previous = $tokens[$stackPtr - 1];
                if ($previous['content']{strlen($previous['content']) - 1} != "\n") {
                    $phpcsFile->addWarning(
                        'Use statement should be on its own line.',
                        $stackPtr
                    );
                }
            }
        }
    }


    public function processUseStatementValue(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();
        $ptr = $stackPtr;
        do {
            ++$ptr;
        } while ($tokens[$ptr]['code'] == T_WHITESPACE);

        if ($tokens[$ptr]['code'] != T_NS_SEPARATOR) {
            $code = '';
            do {
                $code .= $tokens[$ptr]['content'];
                ++$ptr;
            } while (isset($tokens[$ptr]) && !in_array($tokens[$ptr]['content'], array(';', 'as')));
            $phpcsFile->addWarning(
                'Use statements should always refer global namespace, found ' . trim($code),
                $stackPtr,
                'GlobalReference'
            );
        }
    }
}