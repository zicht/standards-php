# Zicht coding standards #

Roughly based on the PEAR and Zend coding standards, the Zicht coding standard applies some custom rules and
modifications to these standards:

- There are no required tags for file and class comments.
- All bracing style is "same line", including class, function and method declarations
- Since we do no longer live in an era with fixed width consoles, the line length limit is fixed at 120, warning at
  120, erroring at 130
- Doc comment tags are compulsory for define() calls
- Global function names are `underscored_and_lowercased()`, and method names are `lowerCamelCased`
- All constants, both global and class constants are `UPPERCASED_AND_UNDERSCORED`
- Excessive whitespace is discouraged, i.e. more than two lines of whitespace and whitespace before the end of a
  scope (before a closing '}') causes warnings

# Usage #

Install PHPCS through pear:

    pear install PHP_CodeSniffer

Install the Zicht directory in the PHPCS library:

    cd /usr/lib/php/PHP/CodeSniffer/Standards/
    cp ~/path/to/Zicht .

Run the codechecker on your code:

    phpcs --standard=Zicht ./my/library/

Read it and weep :)