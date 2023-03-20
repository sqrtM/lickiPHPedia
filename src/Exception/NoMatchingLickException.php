<?php

namespace App\Exception;

/**
 * Thrown when lick cannot be found in database.
 * PHP Version 8.2.0
 *
 * @category  Groups a series of packages together.
 * @package   Categorizes the associated element into a logical grouping or subdivision.
 *
 * @author    Mason Pike <masonapike@gmail.com>
 * @license   unlicense https://unlicense.org/
 *
 * @see       http://url.com
 */
class NoMatchingLickException extends \Exception
{
    public function __construct()
    {
        $this->message =
        'No lick with the given UUID found.';
    }
}
