<?php

namespace App\Exception;

/**
 * Thrown when user is trying to create a new account with an email that is already in the db.
 * PHP Version 8.2.0.
 *
 * @category  Groups a series of packages together.
 * @package   Categorizes the associated element into a logical grouping or subdivision.
 *
 * @author    Mason Pike <masonapike@gmail.com>
 * @license   unlicense https://unlicense.org/
 *
 * @see       http://url.com
 */
class EmailAlreadyInUseException extends \Exception
{
    public function __construct()
    {
        $this->message =
        'Given email is aleady in use. Please use another email or change your password.';
    }
}
