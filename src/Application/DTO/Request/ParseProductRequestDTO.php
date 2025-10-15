<?php

namespace App\Application\DTO\Request;

use App\Domain\DTO\Request\AbstractRequestDTO;
use Symfony\Component\Validator\Constraints as Assert;

class ParseProductRequestDTO extends AbstractRequestDTO
{
    #[Assert\NotBlank(message: 'SKU не может быть пустым')]
    private string $sku;

    public function __construct(string $sku)
    {
        $this->sku = $sku;
        parent::__construct();
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getUrl(): string
    {
        return 'https://www.ozon.ru/product/' . $this->getSku() . '/';
    }
}
