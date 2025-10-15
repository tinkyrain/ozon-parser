<?php

namespace App\Domain\Exceptions\Selenium;

use Exception;
use Throwable;

class SeleniumConnectionException extends Exception
{
    public function __construct(
        $message = "Не удалось подключиться к Selenium",
        $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
