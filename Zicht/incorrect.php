<?php
# Example file of incorrect code. This, for example, is incorrect because # comments are prohibited.

// Doc comment is missing for the file


class A { // wrong brace placement
     const wrongFormat = '2314';
}


define('someConstant');






// excessive whitespace

function invalidFunctionName() {
    // excess whitespace at end of function
    return false;

}



class invalidName {
}


class Invalid_name {
}


"invalid use of double quotes";

$foo = false;

if ($test = $foo || $test = $foo && $test = $foo) {
} elseif ($test = $foo && $test = $foo) {
}