<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

/**
 * A sniff that checks for excessive whitespace lines.
 */
class Zicht_Sniffs_Whitespace_ExcessiveWhitespaceSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Registers for whitespace tokens
     *
     * @return array
     */
    public function register() {
        return array(
            T_WHITESPACE
        );
    }


    /**
     * Sniffs for multiple newlines
     * 
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();
        if($tokens[$stackPtr -1]['code'] == T_WHITESPACE) {
            // we already processed consecutive whitespace, no need to do it again.
            return;
        }

        $concat = $tokens[$stackPtr]['content'];
        $pos = $stackPtr;
        while(isset($tokens[++$pos]) && $tokens[$pos]['code'] == T_WHITESPACE) {
            $concat .= $tokens[$pos]['content'];
        }

        if(preg_match('/^[^\n]\s+?\n/', $concat)) {
            $phpcsFile->addWarning(
                "There should be no whitespace before the end of line",
                $stackPtr,
                "WhitespaceBeforeEol"
            );
        }

        $newlines = substr_count($concat, "\n");
        
        if($newlines > 3) {
            $phpcsFile->addWarning(
                'Excessive whitespace, no more than two lines of whitespace is allowed',
                $stackPtr,
                'WhiteLines'
            );
        }
        
        if(!isset($tokens[$pos]) && $newlines > 1) {
            $phpcsFile->addWarning('Excess whitespace at end of file', $stackPtr, 'WhiteLinesBeforeClosingBracket');
        } elseif(
            isset($tokens[$pos])
            && in_array(
                $tokens[$pos]['code'],
                array(T_CLOSE_CURLY_BRACKET, T_CLOSE_PARENTHESIS, T_CLOSE_PARENTHESIS)
            )
            && $newlines > 1
        ) {
            $phpcsFile->addWarning(
                'Excess whitespace before closing bracket',
                $stackPtr,
                'WhiteLinesBeforeClosingBracket'
            );
        }
    }
}