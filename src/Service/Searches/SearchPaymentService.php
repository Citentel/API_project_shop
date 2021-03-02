<?php

namespace App\Service\Searches;

use App\Entity\Payment;
use App\Model\AbstractSearchService;

class SearchPaymentService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $payment = $this->entityManager->getRepository(Payment::class)->findOneById($id);

        return $this->createMessage($payment);
    }

    public function findOneByName(string $name): array
    {
        $payment = $this->entityManager->getRepository(Payment::class)->findOneByName($name);

        return $this->createMessage($name);
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'payment does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'payment exist', ['payment' => $data]);
    }
}