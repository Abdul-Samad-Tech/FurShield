<?php

namespace App\Exceptions;

use RuntimeException;

class GoogleSignInException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Google sign-in failed');
    }
}
