<?php
/**
 * File comment
 */

/**
 * Comment of class A
 */
class A {
    /**
     * Constant description
     */
    const CONSTANT = "Some value";


    /**
     * Constructor of A
     *
     * @param string $paramOne First parameter
     * @param string $paramTwo Second parameter
     */
    function __construct($paramOne, $paramTwo = SOME_DEFAULT_VALUE) {
        $this->somethingElse();
    }


    /**
     * Documentation of this method.
     *
     * @param string $someParameter some parameter that does something cool
     * @return mixed
     */
    function somethingElse($someParameter = null) {
        switch($someParameter) {
            case true:
                return 'foo';
                break;
            case false:
                return 'bar';
                break;
        }
        return false;
    }
}


/**
 * Sample interface
 */
interface C {
}

/**
 * Comment of class B
 */
class B extends A implements C {
}


/**
 * Comment of function some_global_function. The function is lowercased and underscored
 *
 * @return void
 */
function some_global_function() {
}


some_global_function(
    array(1, 2, 3),
    array(
         'indented',
         'array',
    )
);


/**
 * Drupal update functions may contain numbers, as long as they pertain the format *_update_NNNN
 * with NNNN being the version number
 *
 * @return void
 */
function mod_some_drupal_update_hook_update_6001() {
}


/**
 * The description of this constant
 */
define('SOME_DEFAULT_VALUE', '1234');