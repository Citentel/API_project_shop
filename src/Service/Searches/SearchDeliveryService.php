<?php

namespace App\Service\Searches;

use App\Entity\Delivery;
use App\Model\AbstractSearchService;

class SearchDeliveryService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $delivery = $this->entityManager->getRepository(Delivery::class)->findOneById($id);

        return $this->createMessage($delivery);
    }

    public function findOneByName(string $name): array
    {
        $delivery = $this->entityManager->getRepository(Delivery::class)->findOneByName($name);

        return $this->createMessage($delivery);
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'delivery does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'delivery exist', ['delivery' => $data]);
    }
}