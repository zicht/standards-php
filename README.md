# Zicht PHP standards

Roughly based on the PEAR and Zend coding standards, with inclusion of the PSR-1 and PSR-2 standards, the Zicht coding
standard applies some custom rules and modifications to these standards:

- There are no required tags for file and class comments.
- Since we do no longer live in an era with fixed width consoles, the line length limit is fixed at 120, warning at
  120, erroring at 130.
- Doc comment tags are compulsory for define() calls.
- Global function names are `underscored_and_lowercased()`, and method names are `studlyCased`.
- All constants, both global and class constants are `UPPERCASED_AND_UNDERSCORED`.
- Excessive whitespace is discouraged, i.e. more than two lines of whitespace and whitespace before the end of a
  scope (before a closing '}') causes warnings.
- Referring global namespaces in use statements is not allowed (must not begin with a backslash).
- Referring global namespaces for non-global classes (classes that do not reside in the global namespace, i.e. PHP core classes) is discouraged.

## Usage 

```bash
composer require --dev zicht/standards-php
```

Run `vendor/bin/phpcs -s -p --standard=vendor/zicht/standards-php/Zicht --extensions=php <directories-and-files>`

Also you could incorporate the check in the `scripts` section of composer like this and also add a fix command:
```json
{
    "scripts": {
        "lint": [
            "phpcs -s -p --standard=vendor/zicht/standards-php/Zicht --extensions=php src/ tests/"
        ],
        "lint-fix": [
            "phpcbf -s -p --standard=vendor/zicht/standards-php/Zicht --extensions=php src/ tests/"
        ]
    }
}

```

## Current ruleset

### Zicht Sniffs
In this section each of the rules from the Zicht set are explained.

#### Commenting in general
All doc block comment sniffs (ClassComment, FileComment, FunctionComment and PropertyComment) will scan for empty doc
blocks and doc blocks containing superfluous descriptions. Empty doc blocks and doc block containing only a superfluous
comment (no tags) must be improved or removed. The description of doc blocks having a superfluous description and having
tags must be improved or removed.

Superfluous descriptions are detected by looking at the declaration the doc block belongs to and see if it is a repetition
of its name, which obviously is not adding anything of value. Superfluous doc blocks/descriptions are auto-fixable.
Example, all three doc block comments produce an error by the related sniff because the descriptions are superfluous:
```php
<?php
/**
 * Class SomeExampleClass                   <-- Doc block must be improved or removed
 */
class SomeExampleClass
{
    /**
     * SomeClass constructor.               <-- Doc block must be improved or removed
     */
    public function __construct()
    {
        $this->setSomeValues([1, 2, 3]);
    }

    /**
     * Set some values                      <-- Description must be improved or removed
     *
     * @param int[] $someValues  Integer values to set
     */
    public function setSomeValues(array $someValues)
    {
        $this->someValues = $someValues;
    }
}
```

#### Zicht.Commenting.ClassComment
Extends `PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff`. Additionally detects empty or superfluous
comments (see _[Commenting in general](#commenting-in-general)_) and adds rules about what the doc block is allowed to
contain in a class doc comment.

- `@category`, `@package`, `@subpackage`, `@author` and `@copyright` tags are not allowed in class doc comments.
- `@version` tag in doc is not required but only one is allowed. Follows `@license`.
- `@link` tag in doc is not required but one or more is allowed.
- `@see` tag in doc is not required but one or more is allowed. Follows `@link`.
- `@deprecated` tag in doc is not required but only one is allowed. Follows `@see` (if used) or `@version` (if used).

#### Zicht.Commenting.DefineComment
Looks for comments before the `define` function of PHP.

#### Zicht.Commenting.FileComment
Extends `PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff`. Additionally detects empty or superfluous
comments (see _[Commenting in general](#commenting-in-general)_) and adds rules about what the doc block is allowed to
contain in a file doc comment.

- `@category`, `@package`, `@subpackage` and `@author` tags are not allowed in file doc comments.
- `@copyright` tag in doc is not required but one or more is allowed.
- `@version` tag in doc is not required but only one is allowed. Follows `@license`.
- `@link` tag in doc is not required but one or more is allowed.
- `@see` tag in doc is not required but one or more is allowed. Follows `@link`.
- `@deprecated` tag in doc is not required but only one is allowed. Follows `@see` (if used) or `@version` (if used) or 
`@copyright` (if used).

#### Zicht.Commenting.FunctionComment
Extends `\PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff`. Additionally detects empty or
superfluous comments (see _[Commenting in general](#commenting-in-general)_) and detects `{@inheritdoc}` in the function
comment which makes it skip params and return tags validation (if no `@param` or `@return` is added additionally).
Also this sniff allows skipping a function comment when there are no parameters and returned values or when all parameters
and the returned value have their type declared (type _hinted_):
```php
public function getMeSomeArray(int $id, SomeObject $someObject = null): array
{
    return [];
}
```
No function doc comment is needed in above example.

#### Zicht.Commenting.PropertyComment
This sniffs checks for required property comment tags (`@var`), their (required) content and the order of tags (`@var`
tag must always come first and official PHPDoc tags must be placed above other custom tags. Empty lines are not allowed
within the property comment and a separate description is not allowed and must be placed with the `@var` tag.
The sniff also detects for superfluous descriptions (see _[Commenting in general](#commenting-in-general)_).
Single line property comments (`/** @var <type> */`) are allowed.

#### Zicht.ControlStructures.ControlSignature
Checks if certain structures are formed according to the definition of the signature.

`do {EOL...} while (...);EOL`,
`while (...) {EOL`,
`for (...) {EOL`,
`if (...) {EOL`,
`foreach (...) {EOL`,
`} else if (...) {EOL`,
`} elseif (...) {EOL`,
`} else {EOL`,
`do {EOL`,

for example `do {EOL...} while (...);EOL` means:
do {// (EOL) End of line from here
} while ();// (EOL) End of line from here.

#### Zicht.ControlStructures.DisallowAssignments
Detects if there are any assignments happening in if, elseif, while, foreach and switch control structures. By
default configuration a maximum of 1 assignment is allowed and it must come first before any other logic in the
statement:
```php
    if ($result = someDataRetrieval() && isset($result['success']) && true === $result['success'])
```

#### Zicht.Functions.FunctionCallSignature
This sniff overrides the PEAR Sniff to allow function call opening parenthesis and array square brackets on the same
line. It is only allowing this if there's only one argument, which should be an array or could be a closure.

#### Zicht.Methods.FunctionOpeningBrace
Detects if there are no empty lines between a function's opening brace and the first line of code.

#### Zicht.NamingConventions.Classname
This sniff requires class names to be `CamelCased`.

#### Zicht.NamingConventions.Constants
This sniff requires a constant name to be `UPPERCASE` (no lower case characters allowed) and no characters other than
A-Z, 0-9 and underscore.
 
#### Zicht.NamingConventions.Functions
This sniff defines the naming conventions.

Class methods are required to be `studlyCased` or alternatively named `lowerCamelCased`.
The following methods are allowed: `construct`, `get`, `set`, `call`, `callStatic`, `invoke`, `destruct`, `toString`, 
`clone`, `invoke`, `invokeStatic`.
Underscore and numbers are discouraged to be used in method names in classes.
A number creates a warning whereas an underscore creates an error.

Global functions are required to be `snake_cased` so all lower an divided by a underscore.

#### Zicht.PHP.Namespace
Except for global classes all other classes in namespaces are not allowed to be used in code referring to the fully
qualified class name. Like `$sniff = new \Zicht\Sniffs\PHP\NamespaceSniff())` use an use statement and format your
code like `$sniff = new NamespaceSniff()`;

#### Zicht.PHP.UseStatement
This sniff defines that the use statements should be at the top of in a PHP file and can only be preceded by 
a declare statement, doc blocks or the namespace declaration (and surely whitespaces etc).

#### Zicht.Whitespace.ExcessiveWhitespace
This sniff looks for more then one whitespace after the last `}` in a file. 

### Other rules
To view the rules in this ruleset you can use the following command from the package root:
```bash
vendor/bin/phpcs --standard=Zicht -e
```
That will produce the following set:

```text
   The Zicht standard contains 65 sniffs

   Generic (14 sniffs)
   -------------------
     Generic.Arrays.DisallowLongArraySyntax
     Generic.ControlStructures.InlineControlStructure
     Generic.Files.ByteOrderMark
     Generic.Files.LineEndings
     Generic.Formatting.DisallowMultipleStatements
     Generic.Formatting.NoSpaceAfterCast
     Generic.Formatting.SpaceAfterCast
     Generic.Functions.FunctionCallArgumentSpacing
     Generic.Functions.OpeningFunctionBraceBsdAllman
     Generic.NamingConventions.UpperCaseConstantName
     Generic.PHP.DisallowShortOpenTag
     Generic.PHP.LowerCaseConstant
     Generic.PHP.LowerCaseKeyword
     Generic.WhiteSpace.DisallowTabIndent

   PEAR (3 sniffs)
   ---------------
     PEAR.ControlStructures.ControlSignature
     PEAR.Functions.ValidDefaultValue
     PEAR.WhiteSpace.ScopeClosingBrace

   PSR1 (3 sniffs)
   ---------------
     PSR1.Classes.ClassDeclaration
     PSR1.Files.SideEffects
     PSR1.Methods.CamelCapsMethodName

   PSR12 (1 sniff)
   ----------------
     PSR12.Operators.OperatorSpacing

   PSR2 (12 sniffs)
   ----------------
     PSR2.Classes.ClassDeclaration
     PSR2.Classes.PropertyDeclaration
     PSR2.ControlStructures.ControlStructureSpacing
     PSR2.ControlStructures.ElseIfDeclaration
     PSR2.ControlStructures.SwitchDeclaration
     PSR2.Files.ClosingTag
     PSR2.Files.EndFileNewline
     PSR2.Methods.FunctionCallSignature
     PSR2.Methods.FunctionClosingBrace
     PSR2.Methods.MethodDeclaration
     PSR2.Namespaces.NamespaceDeclaration
     PSR2.Namespaces.UseDeclaration

   Squiz (16 sniffs)
   -----------------
     Squiz.Arrays.ArrayDeclaration
     Squiz.Classes.ValidClassName
     Squiz.ControlStructures.ControlSignature
     Squiz.ControlStructures.ForEachLoopDeclaration
     Squiz.ControlStructures.ForLoopDeclaration
     Squiz.ControlStructures.LowercaseDeclaration
     Squiz.Functions.FunctionDeclaration
     Squiz.Functions.FunctionDeclarationArgumentSpacing
     Squiz.Functions.LowercaseFunctionKeywords
     Squiz.Functions.MultiLineFunctionDeclaration
     Squiz.Scope.MethodScope
     Squiz.Strings.DoubleQuoteUsage
     Squiz.WhiteSpace.ControlStructureSpacing
     Squiz.WhiteSpace.ScopeClosingBrace
     Squiz.WhiteSpace.ScopeKeywordSpacing
     Squiz.WhiteSpace.SuperfluousWhitespace

   Zend (2 sniffs)
   ---------------
     Zend.Debug.CodeAnalyzer
     Zend.Files.ClosingTag

   Zicht (15 sniffs)
   -----------------
     Zicht.Commenting.ClassComment
     Zicht.Commenting.DefineComment
     Zicht.Commenting.FileComment
     Zicht.Commenting.FunctionComment
     Zicht.Commenting.PropertyComment
     Zicht.ControlStructures.ControlSignature
     Zicht.Functions.FunctionCallSignature
     Zicht.Methods.FunctionOpeningBrace
     Zicht.NamingConventions.Classname
     Zicht.NamingConventions.Constants
     Zicht.NamingConventions.Functions
     Zicht.PHP.DisallowMultipleAssignmentsInIfStatements
     Zicht.PHP.Namespace
     Zicht.PHP.UseStatement
     Zicht.Whitespace.ExcessiveWhitespace
```

These namespaces of rules are applied as you could see above. Check the documentation of these namespaces for explanation.
Generic
PEAR
PSR1
PSR2
Squiz
Zend
Zicht

# Maintainers
* Boudewijn Schoon <boudewijn@zicht.nl>
* Erik Trapman <erik@zicht.nl>
* Jochem Klaver <jochem@zicht.nl>
