<?php

namespace App\Domain\Parser;

interface ParserInterface
{
    public function getData(string $url);
}
