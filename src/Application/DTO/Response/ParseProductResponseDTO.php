<?php

namespace App\Application\DTO\Response;

use App\Domain\DTO\Response\ResponseDTO;

class ParseProductResponseDTO implements ResponseDTO
{
    public function __construct(
        public ?string $title,
        public string $category,
        public ?string $article,
        public ?string $type,
        public ?string $country,
        public ?string $description,
        public array $images,
        public array $characteristics,
    ) {}

    public function toArray(): array
    {
        return [
            'Название' => $this->title,
            'Категории' => $this->category,
            'Артикул' => $this->article,
            'Тип' => $this->type,
            'Страна' => $this->country,
            'Описание' => $this->description,
            'Изображения' => $this->images,
            'Характеристики' => $this->characteristics,
        ];
    }
}
