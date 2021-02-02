<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class GenerateResponseService
{
    private int $codeResponse = 200;
    private string $messageResponse = 'looks good';
    private array $dataResponse = [];

    private function setProperties(int $code, string $message, array $dataResponse): void
    {
        $this->codeResponse = $code;
        $this->messageResponse = $message;
        $this->dataResponse = $dataResponse;
    }

    public function generateJsonResponse(int $code, string $message, array $dataResponse = []): array
    {
        $this->setProperties($code, $message, $dataResponse);

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

    public function generateArrayResponse(int $code, string $message, array $dataResponse = []): array
    {
        $this->setProperties($code, $message, $dataResponse);

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