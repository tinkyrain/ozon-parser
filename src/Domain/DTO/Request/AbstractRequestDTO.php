<?php

namespace App\Domain\DTO\Request;

use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractRequestDTO implements RequestDTO
{
    public function __construct()
    {
        $this->validateSelf();
    }

    private function validateSelf(): void
    {
        $validator = $this->getValidator();
        $errors = $validator->validate($this);

        if (count($errors) > 0) {
            throw new ValidationFailedException($this, $errors);
        }
    }

    private function getValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
