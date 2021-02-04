<?php


namespace App\Service\Validators;

use App\Service\GenerateResponseService;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorAddressesService
{
    private GenerateResponseService $generateResponseService;
    private ValidatorInterface $validator;
    private array $checkString;
    private array $checkInt;

    public function __construct
    (
        GenerateResponseService $generateResponseService
    )
    {
        $this->generateResponseService = $generateResponseService;
        $this->validator = Validation::createValidator();
        $this->checkString = [new Length(['min' => 2]), new NotBlank(), new Type('string')];
        $this->checkInt = [new Length(['min' => 1]), new NotBlank(), new Type('int')];
    }

    public function checkField(string $fieldName, $fieldValue): array {
        switch ($fieldName){
            case 'city':
            case 'street':
                $checkedField = $this->checkStringField($fieldValue, $this->checkString);
                return $this->createMessage($checkedField);
            case 'home_number':
            case 'premises_number':
            case 'zip':
                $checkedField = $this->checkIntField($fieldValue, $this->checkInt);
                return $this->createMessage($checkedField);
            case 'country':
                $checkArray = $this->checkString;
                $checkArray[] = new Country();

                $checkedField = $this->checkStringField($fieldValue, $checkArray);

                return $this->createMessage($checkedField);
        }
    }

    public function checkFullyAddressUser(array $fields): array
    {
        $response = [];

        foreach ($fields as $key => $value) {
            $checkedField = $this->checkField($key, $value);
            if ($checkedField['code'] !== 200) {
                $response[$key] = $checkedField['data'];
            }
        }

        if (!empty($response)) {
            return $this->generateResponseService->generateArrayResponse(412, 'something is wrong', $response);
        }

        return $this->generateResponseService->generateArrayResponse(200, 'looks good');
    }

    private function checkStringField(string $field, array $checkArray): ConstraintViolationListInterface
    {
        return $this->validator->validate($field, $checkArray);
    }

    private function checkIntField(int $field, array $checkArray): ConstraintViolationListInterface
    {
        return $this->validator->validate($field, $checkArray);
    }

    private function createMessage(ConstraintViolationListInterface $checkedField): array
    {
        if ($checkedField->count() !== 0) {

            $errorMessages = [];

            /** @var ConstraintViolation $error */
            foreach ($checkedField as $error) {
                $errorMessages[$error->getCode()] = $error->getMessage();
            }

            return $this->generateResponseService->generateArrayResponse(412, 'something is wrong', $errorMessages);
        }

        return $this->generateResponseService->generateArrayResponse(200, 'looks good');
    }
}