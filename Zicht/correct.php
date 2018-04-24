<?php
/**
 * File comment
 */

namespace Zicht;

/**
 * Trait D
 */
trait D
{

}

/**
 * Comment of class A
 */
class A
{

    use D;

    /**
     * Constant description
     */
    const CONSTANT = 'Some value';


    /**
     * Constructor of A
     *
     * @param string $paramOne First parameter
     * @param string $paramTwo Second parameter
     */
    public function __construct($paramOne, $paramTwo = SOME_DEFAULT_VALUE)
    {
        $this->somethingElse();
    }


    /**
     * Documentation of this method.
     *
     * @param string $someParameter some parameter that does something cool
     * @return mixed
     */
    public function somethingElse($someParameter = null)
    {
        switch ($someParameter) {
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
interface C
{
}

/**
 * Comment of class B
 */
class B extends A implements C
{
    /**
     * method name with number in it
     */
    public function base64Encoding()
    {
    }
}


/**
 * Comment of function some_global_function. The function is lowercased and underscored
 *
 * @return void
 */
function some_global_function()
{
}


some_global_function(
    [1, 2, 3],
    [
         'indented',
         'array',
    ]
);

$publishingDate = null;
$now = new \DateTime();

if ($isFuturePublishingDate = null !== $publishingDate && $now < $publishingDate) {
}

/**
 * The description of this constant
 */
define('SOME_DEFAULT_VALUE', '1234');
