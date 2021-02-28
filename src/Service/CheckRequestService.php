<?php

namespace App\Service;

use App\Entity\Users;
use Symfony\Component\HttpFoundation\Request;

class CheckRequestService
{
    private Request $request;
    private array $fieldsRequired = [];
    private array $fieldsOptional = [];
    private GenerateResponseService $generateResponseService;

    public function __construct(GenerateResponseService $generateResponseService)
    {
        $this->generateResponseService = $generateResponseService;
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
        $requestDecode = $this->getDataByMethod();

        if ($requestDecode === null) {
            return $this->generateResponseService->generateJsonResponse(400, 'something goes wrong with decode request');
        }

        if (!$this->array_required_keys($this->fieldsRequired, $requestDecode)) {
            return $this->generateResponseService->generateJsonResponse(404, 'request do not have required fields');
        }

        $acceptedKeys = $this->array_optional_keys($this->fieldsOptional, $requestDecode);

        $acceptedKeys = $this->addAccessUidIntoResponse($acceptedKeys);

        return $this->generateResponseService->generateArrayResponse(200, 'looks good', $acceptedKeys);
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

        foreach ($requestDecode as $item => $value) {
            switch (gettype($value)) {
                case 'integer':
                    $requestDecode[$item] = (int)htmlspecialchars($value);
                    break;
                case 'boolean':
                    $requestDecode[$item] = (bool)htmlspecialchars($value);
                    break;
                case 'array':
                    $requestDecode[$item] = $value;
                    break;
                default:
                    $requestDecode[$item] = htmlspecialchars($value);
                    break;
            }
        }

        return $requestDecode;
    }

    private function getDataByMethod(): array
    {
        if ($this->request->getMethod() === 'GET') {
            return $this->request->query->all();
        }

        return json_decode($this->request->getContent(), true);
    }

    private function addAccessUidIntoResponse(array $acceptedKeys): array
    {
        /** @var null|Users $accessUser */
        $accessUser = $this->request->get('access_user');

        if (!$this->request->get('access_user')) {
            return $acceptedKeys;
        }

        $acceptedKeys['access_user'] = $accessUser;

        return $acceptedKeys;
    }
}