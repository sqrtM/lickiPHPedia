<?php

namespace App\Exception;

class InvalidPasswordException extends \Exception
{
    public function __construct()
    {
        $this->message =
        'Password cannot be longer than 72 characters.';
    }
}
