<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Sniffs\ControlStructures;

use PHP_CodeSniffer\Sniffs\AbstractPatternSniff;

/**
 * Checks for the defined control structures signature.
 */
class ControlSignatureSniff extends AbstractPatternSniff
{
    /**
     * {@inheritdoc}
     */
    protected function getPatterns()
    {
        return [
            'do {EOL...} while (...);EOL',
            'while (...) {EOL',
            'for (...) {EOL',
            'if (...) {EOL',
            'foreach (...) {EOL',
            '} else if (...) {EOL',
            '} elseif (...) {EOL',
            '} else {EOL',
            'do {EOL',
        ];
    }
}
