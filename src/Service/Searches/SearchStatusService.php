<?php

namespace App\Service\Searches;

use App\Entity\Status;
use App\Model\AbstractSearchService;

class SearchStatusService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $status = $this->entityManager->getRepository(Status::class)->findOneById($id);

        return $this->createMessage($status);
    }

    public function findOneByName(string $name): array
    {
        $status = $this->entityManager->getRepository(Status::class)->findOneByName($name);

        return $this->createMessage($status);
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'status does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'status exist', ['status' => $data]);
    }
}