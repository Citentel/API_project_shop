<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class CheckRequestService
{
    private Request $request;
    private array $fieldsRequired = [];
    private array $fieldsOptional = [];
    private GenerateResponseService $generateResponse;

    public function __construct(GenerateResponseService $generateResponse)
    {
        $this->generateResponse = $generateResponse;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function setFieldsRequired(array $fieldsRequired): self
    {
        $this->fieldsRequired = $fieldsRequired;

        $this->setFieldsOptional($fieldsRequired);

        return $this;
    }

    public function setFieldsOptional(array $fieldsOptional): self
    {
        if (!empty($this->fieldsOptional)) {
            $this->fieldsOptional = array_merge($fieldsOptional, $this->fieldsOptional);

            return $this;
        }

        $this->fieldsOptional = $fieldsOptional;

        return $this;
    }

    public function checker(): array
    {
        $requestDecode = json_decode($this->request->getContent(), true);

        if ($requestDecode === null) {
            return $this->generateResponse
                ->setCodeResponse(400)
                ->setMessageResponse('something goes wrong with decode request')
                ->generateJsonResponse();
        }

        if (!$this->array_required_keys($this->fieldsRequired, $requestDecode)) {
            return $this->generateResponse
                ->setCodeResponse(404)
                ->setMessageResponse('request do not have required fields')
                ->generateJsonResponse();
        }

        $acceptedKeys = $this->array_optional_keys($this->fieldsOptional, $requestDecode);

        return $this->generateResponse
            ->setDataResponse($acceptedKeys)
            ->generateArrayResponse();
    }

    private function array_required_keys(array $fieldsRequired, array $requestDecode): bool
    {
        return !array_diff_key(array_flip($fieldsRequired), $requestDecode);
    }

    private function array_optional_keys(array $fieldsOptional, array $requestDecode): array
    {
        $notAcceptedKeys = array_keys(array_diff_key($requestDecode, array_flip($fieldsOptional)));

        foreach ($notAcceptedKeys as $key) {
            unset($requestDecode[$key]);
        }

        return $requestDecode;
    }
}