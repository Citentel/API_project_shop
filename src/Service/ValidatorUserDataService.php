<?php


namespace App\Service;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorUserDataService
{
    private GenerateResponseService $generateResponseService;
    private ValidatorInterface $validator;

    public function __construct
    (
        GenerateResponseService $generateResponseService
    )
    {
        $this->generateResponseService = $generateResponseService;
        $this->validator = Validation::createValidator();
    }

    public function checkRegisterData(array $dataRegister): array
    {
        /** @var ConstraintViolationListInterface[] $testFields */
        $testFields = [];

        $testFields['firstname'] = $this->validator->validate(
            $dataRegister['firstname'], [
                new Length(['min' => 2]),
                new NotBlank(),
            ]
        );

        $testFields['lastname'] = $this->validator->validate(
            $dataRegister['lastname'], [
                new Length(['min' => 2]),
                new NotBlank(),
            ]
        );

        $testFields['email'] = $this->validator->validate(
            $dataRegister['email'], [
                new Length(['min' => 5]),
                new NotBlank(),
                new Email(),
            ]
        );

        $testFields['password'] = $this->validator->validate(
            $dataRegister['password'], [
                new Length(['min' => 5]),
                new NotBlank(),
            ]
        );

        $errorsFields = $this->getErrorMessage($testFields);

        if (!empty($errorsFields)) {
            return $this->generateResponseService->generateJsonResponse(423, 'invalid fields', $errorsFields);
        }

        return $this->generateResponseService->generateArrayResponse(200, 'fields filled in correctly');
    }

    private function getErrorMessage(array $testFields): array
    {
        $responseError = [];

        /** @var ConstraintViolationListInterface $field */
        foreach ($testFields as $name => $testField) {
            if (!empty($testField)) {
                foreach ($testField as $error) {
                    $responseError[$name][] = $error->getMessage();
                }
            } else {
                $responseError[$name] = [];
            }
        }

        return $responseError;
    }

}