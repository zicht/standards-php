<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Common;

/**
 * This sniff detects multiple assignments in control structures
 *
 * @property bool $allowedAssignments the maximum amount of assignments to allow
 *           in control structures {@see DisallowAssignmentsSniff::__set()}
 * @property bool $assignmentsFirst Whether or not to check if assignments come first
 *           before any other logic {@see DisallowAssignmentsSniff::__set()}
 *
 * @example 1 assigment allowed (with default settings):
 *          if ($foo = 'bar')
 * @example More than 1 assigment is disallowed (with default settings):
 *          if ($foo = 'bar' && $test = 'test')
 *
 * @example Assignments must come first (with default settings):
 *          if ($foo = 'bar' && is_string($foo))
 * @example Assignments must not come second (with default settings):
 *          if (!is_string($foo) && $foo = 'bar')
 */
class DisallowAssignmentsSniff implements Sniff
{
    /**
     * @var int The number of assignments to allow within control structures
     */
    protected $allowedAssignments = 1;

    /**
     * @var bool Assignment(s) are only allowed as the first element(s) of a multi clause statement
     */
    protected $assignmentsFirst = true;

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_IF,
            T_ELSEIF,
            T_WHILE,
            T_FOREACH,
            T_SWITCH,
        ];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'allowedAssignments':
                if ((int)$value < 0) {
                    throw new \UnexpectedValueException('The number of allowed assignments must be 0 or greater');
                }
                $this->{$name} = (int)$value;
                break;
            case 'assignmentsFirst':
                $this->{$name} = (bool)$value;
                break;
            default:
                if ((int)$value < 0) {
                    throw new \RuntimeException(sprintf('Unknown property "%s::%s"', get_class($this), $name));
                }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$stackPtr]['parenthesis_opener']) || !isset($tokens[$stackPtr]['parenthesis_closer'])
            || null === $tokens[$stackPtr]['parenthesis_opener'] || null === $tokens[$stackPtr]['parenthesis_closer']) {
            // This could happen while still editing a file. Don't process (yet)
            return;
        }
        if (isset($tokens[$stackPtr]['parenthesis_owner']) && $stackPtr !== $tokens[$stackPtr]['parenthesis_owner']) {
            // Not our parenthesis
            return;
        }

        $type = strtolower($tokens[$stackPtr]['content']);

        $processMethod = sprintf('process%sStructure', ucfirst($type));
        if (!method_exists($this, $processMethod)) {
            $processMethod = 'processControlStructure';
        }
        /**
         * @see DisallowAssignmentsSniff::processForeachStructure()
         * @see DisallowAssignmentsSniff::processControlStructure()
         */
        $this->{$processMethod}(
            $phpcsFile,
            $stackPtr,
            $type,
            $tokens[$stackPtr]['parenthesis_opener'],
            $tokens[$stackPtr]['parenthesis_closer']
        );
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param string $type
     * @param int $start
     * @param int $end
     */
    protected function processForeachStructure(File $phpcsFile, $stackPtr, $type, $start, $end)
    {
        $tokens = $phpcsFile->getTokens();
        for ($i = $start + 1; $i < $end; $i++) {
            if (T_AS === $tokens[$i]['code']) {
                $end = $i;
                break;
            }
        }
        $this->processControlStructure($phpcsFile, $stackPtr, $type, $start, $end);
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param string $type
     * @param int $start
     * @param int $end
     */
    protected function processControlStructure(File $phpcsFile, $stackPtr, $type, $start, $end)
    {
        $tokens = $phpcsFile->getTokens();
        $logicalOperators = [T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR];
        $assignments = [];
        $positions = [];
        $pos = 1;
        for ($ptr = $start + 1; $ptr < $end; $ptr++) {
            if (T_EQUAL === $tokens[$ptr]['code']) {
                $assignments[] = $ptr;
                $positions[] = [$ptr, $pos];
            } elseif (in_array($tokens[$ptr]['code'], $logicalOperators, true)) {
                $pos++;
            }
        }
        $this->processAssignmentCount($phpcsFile, $stackPtr, $type, $assignments);
        $this->processAssignmentPositions($phpcsFile, $stackPtr, $type, $positions);
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param string $type
     * @param int[] $assignments
     */
    protected function processAssignmentCount(File $phpcsFile, $stackPtr, $type, array $assignments)
    {
        if (count($assignments) <= $this->allowedAssignments) {
            return;
        }

        if (0 === $this->allowedAssignments) {
            $error = 'Found %d assignments in %s-statement while assignments in %2$s-statements are not allowed';
            $data = [count($assignments), $type];
        } else {
            $error = 'Found %d assignments in %s-statement while a maximum of %d assignment%s is allowed';
            $data = [count($assignments), $type, $this->allowedAssignments, 1 !== $this->allowedAssignments ? 's' : ''];
        }
        $code = 'TooManyAssignmentsIn' . ucfirst($type);
        $phpcsFile->addError($error, $assignments[$this->allowedAssignments], $code, $data);
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param string $type
     * @param int[] $positions
     */
    protected function processAssignmentPositions(File $phpcsFile, $stackPtr, $type, array $positions)
    {
        if (!$this->assignmentsFirst) {
            return;
        }

        if (0 === (int)$this->allowedAssignments) {
            $error = Common::getSniffCode(get_class($this)) . ' misconfiguration: assignmentsFirst was set TRUE while '
                . 'allowedAssignments is 0';
            $phpcsFile->addWarning($error, null, 'Internal.MisConfiguration');
        }

        $prevPos = null;
        foreach ($positions as $i => list($ptr, $pos)) {
            if (null === $prevPos && $pos > 1 || null !== $prevPos && $prevPos < $pos - 1) {
                $error = 'Found assignment in %s-statement on position %d '
                    . 'while %s in %1$s-statements must come first before any other logic';
                $data = [$type, $pos, 1 !== $this->allowedAssignments ? 'assignments' : 'an assignment'];
                $code = 'AssignmentsFirstIn' . ucfirst($type);
                $phpcsFile->addError($error, $ptr, $code, $data);
            }
        }
    }
}
