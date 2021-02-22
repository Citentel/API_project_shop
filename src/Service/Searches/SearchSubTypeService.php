<?php

namespace App\Service\Searches;

use App\Entity\SubType;
use App\Model\AbstractSearchService;

class SearchSubTypeService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $subType = $this->entityManager->getRepository(SubType::class)->findOneById($id);

        return $this->createMessage($subType);
    }

    public function findOneByName(string $name): array
    {
        $subType = $this->entityManager->getRepository(SubType::class)->findOneByName($name);

        return $this->createMessage($subType);
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'sub type does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'sub type exist', ['subType' => $data]);
    }
}