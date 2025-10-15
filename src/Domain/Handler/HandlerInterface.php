<?php

namespace App\Domain\Handler;

use App\Domain\DTO\Request\RequestDTO;
use App\Domain\DTO\Response\ResponseDTO;

interface HandlerInterface
{
    public function handle(RequestDTO $requestDTO): ResponseDTO;
}
