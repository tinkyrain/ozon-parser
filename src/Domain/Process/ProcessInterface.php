<?php

namespace App\Domain\Process;

use App\Domain\DTO\Request\RequestDTO;

interface ProcessInterface
{
    public function run(RequestDTO $requestDTO);
}
