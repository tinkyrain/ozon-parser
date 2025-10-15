<?php

namespace App\Application\Handler;

use App\Domain\DTO\Request\RequestDTO;
use App\Domain\DTO\Response\ResponseDTO;
use App\Domain\Handler\HandlerInterface;
use App\Domain\Process\ProcessInterface;

readonly class ProductParserHandler implements HandlerInterface
{
    public function __construct(
        private ProcessInterface $process
    )
    {}

    /**
     * @param RequestDTO $requestDTO
     * @return ResponseDTO
     */
    public function handle(RequestDTO $requestDTO): ResponseDTO
    {
        return $this->process->run($requestDTO);
    }
}
