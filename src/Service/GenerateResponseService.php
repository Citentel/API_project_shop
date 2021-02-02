<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class GenerateResponseService
{
    private int $codeResponse = 200;
    private string $messageResponse = 'looks good';
    private array $dataResponse = [];

    public function setCodeResponse(int $codeResponse): self
    {
        $this->codeResponse = $codeResponse;

        return $this;
    }

    public function setMessageResponse(string $messageResponse): self
    {
        $this->messageResponse = $messageResponse;

        return $this;
    }

    public function setDataResponse(array $dataResponse): self
    {
        $this->dataResponse = $dataResponse;

        return $this;
    }

    public function generateJsonResponse(): array
    {
        $data = [
            'code' => $this->codeResponse,
            'message' => $this->messageResponse,
        ];

        if (!empty($this->dataResponse)) {
            $data['data'] = $this->dataResponse;
        }

        return [
            'code' => $this->codeResponse,
            'data' => new JsonResponse($data, $this->codeResponse),
        ];
    }

    public function generateArrayResponse(): array
    {
        $data = [
            'code' => $this->codeResponse,
            'message' => $this->messageResponse,
        ];

        if (!empty($this->dataResponse)) {
            $data['data'] = $this->dataResponse;
        }

        return $data;
    }
}