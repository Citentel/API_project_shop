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

        return $this->returnResponse($testFields);
    }

    public function checkPassword(string $newPassword): array
    {
        $testFields['password'] = $this->validator->validate(
            $newPassword, [
                new Length(['min' => 5]),
                new NotBlank(),
            ]
        );

        return $this->returnResponse($testFields);
    }

    public function checkFirstname(string $newFirstname): array
    {
        $testFields['firstname'] = $this->validator->validate(
            $newFirstname, [
                new Length(['min' => 2]),
                new NotBlank(),
            ]
        );

        return $this->returnResponse($testFields);
    }

    public function checkLastname(string $newLastname): array
    {
        $testFields['lastname'] = $this->validator->validate(
            $newLastname, [
                new Length(['min' => 2]),
                new NotBlank(),
            ]
        );

        return $this->returnResponse($testFields);
    }

    public function checkEmail(string $newEmail): array
    {
        $testFields['email'] = $this->validator->validate(
            $newEmail, [
                new Length(['min' => 5]),
                new NotBlank(),
                new Email(),
            ]
        );

        return $this->returnResponse($testFields);
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

    private function returnResponse(array $testFields): array
    {
        $errorsFields = $this->getErrorMessage($testFields);

        if (!empty($errorsFields)) {
            return $this->generateResponseService->generateJsonResponse(423, 'invalid fields', $errorsFields);
        }

        return $this->generateResponseService->generateArrayResponse(200, 'fields filled in correctly');
    }

}