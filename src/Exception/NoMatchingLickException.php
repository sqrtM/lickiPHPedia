<?php

namespace App\Exception;

class NoMatchingLickException extends \Exception
{
    public function __construct()
    {
        $this->message =
        'No lick with the given UUID found.';
    }
}
