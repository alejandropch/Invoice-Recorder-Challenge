<?php

namespace App\Exceptions;

use Exception;

class VoucherException extends Exception
{
     // You can define a custom message, code, and additional logic
     public function __construct($message, $code)
     {
         parent::__construct($message, $code);
     }

}
