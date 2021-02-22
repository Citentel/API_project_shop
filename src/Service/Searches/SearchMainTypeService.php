<?php

namespace App\Service\Searches;

use App\Entity\MainType;
use App\Model\AbstractSearchService;

class SearchMainTypeService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $mainType = $this->entityManager->getRepository(MainType::class)->findOneById($id);

        return $this->createMessage($mainType);
    }

    public function findOneByName(string $name): array
    {
        $mainType = $this->entityManager->getRepository(MainType::class)->findOneByName($name);

        return $this->createMessage($mainType);
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'main type does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'main type exist', ['mainType' => $data]);
    }
}