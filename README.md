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
- Referring local namespaces in use statements is discouraged, and they should begin with a backslash.
- Referring global namespaces for non-global classes (i.e., classes that do not reside in the global namespace
  is discouraged.

## Usage 

composer require --dev `zicht/standards-php`

Run `./vendor/bin/phpcs -s src/Zicht/ --standard=vendor/zicht-standards/php/Zicht --extensions=php`

Also you could incorporate the check in the `scripts` section of composer like this.
```
"code-style": [
    "./vendor/bin/phpcs -s src/Zicht/ --standard=vendor/zicht-standards/php/Zicht --extensions=php"
]
```

## Current ruleset

### Zicht Sniffs
In this section each of the rules from the Zicht set are explained.

#### Zicht.Commenting.ClassComment
Extends `PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff` and adds rules about what the doc block could 
contain in a class doc comment.

`@author` tag in doc is not required but one or more is allowed. Precedes `@copyright`.
`@see` tag in doc is not required but one or more is allowed. Follows `@link`.
`@copyright` tag in doc is not required but one or more is allowed. Follows `@author`.
`@version` tag in doc is not required but only one is allowed. Follows `@license`.
`@deprecated` tag in doc is not required but only one is allowed. Follows `@see` (if used) or `@version` (if used) or 
`@copyright` (if used).

#### Zicht.Commenting.ClassConstantComment
Requires constants in classes to have a doc block. 

#### Zicht.Commenting.DefineComment
Looks for comments before the `define` function of PHP.

#### Zicht.Commenting.FileComment
Extends `PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff` and adds rules about what the doc block could 
contain in a file doc comment.

`@author` tag in doc is not required but one or more is allowed. Precedes `@copyright`.
`@see` tag in doc is not required but one or more is allowed. Follows `@link`.
`@copyright` tag in doc is not required but one or more is allowed. Follows `@author`.
`@version` tag in doc is not required but only one is allowed. Follows `@license`.
`@deprecated` tag in doc is not required but only one is allowed. Follows `@see` (if used) or `@version` (if used) or 
`@copyright` (if used).

#### Zicht.Commenting.FunctionComment
This is a fork of `PEAR_Sniffs_Commenting_FunctionCommentSniff`. The only difference is allowing `@{inheritDoc}` in the 
function comments, but only if it is the sole content of the comment.

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

#### Zicht.NamingConventions.Classname
This sniff requires class names to be `CamelCased`.

#### Zicht.NamingConventions.Constants
This sniff requires a constant name to start with letters `A-Z` followed by `A-Z`, `_` or `0-9`.
 
#### Zicht.NamingConventions.Functions
This sniff defines the naming conventions.

Class methods are required to be `studlyCased` or alternatively named `lowerCamelCased`.
The following methods are allowed: `construct`, `get`, `set`, `call`, `callStatic`, `invoke`, `destruct`, `toString`, 
`clone`, `invoke`, `invokeStatic`.
Underscore and numbers are discouraged to be used in method names in classes.
A number creates a warning whereas an underscore creates an error.

Global functions are required to be `snake_cased` so all lower an divided by a underscore.

#### Zicht.PHP.DisallowMultipleAssignmentsInIfStatementsSniff.TooManyAssignments
Disallows multiple assignments in a condition of if statements.
```
if ($foo = $bar && $foo = $bar)
```
The example above is disallowed.

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
To view the rules in this ruleset you can use the following command.
```
./vendor/bin/phpcs --standard=Zicht -e
```
That gives us the following set.

```  
   The Zicht standard contains 63 sniffs
   
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
   
   PEAR (4 sniffs)
   ---------------
     PEAR.ControlStructures.ControlSignature
     PEAR.Functions.FunctionCallSignature
     PEAR.Functions.ValidDefaultValue
     PEAR.WhiteSpace.ScopeClosingBrace
   
   PSR1 (3 sniffs)
   ---------------
     PSR1.Classes.ClassDeclaration
     PSR1.Files.SideEffects
     PSR1.Methods.CamelCapsMethodName
   
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
   
   Squiz (15 sniffs)
   -----------------
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
   
   Zicht (13 sniffs)
   -----------------
     Zicht.Commenting.ClassComment
     Zicht.Commenting.ClassConstantComment
     Zicht.Commenting.DefineComment
     Zicht.Commenting.FileComment
     Zicht.Commenting.FunctionComment
     Zicht.ControlStructures.ControlSignature
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
* Robert van Kempen <robert@zicht.nl>
