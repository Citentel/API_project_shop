<?php

namespace App\Service\Searches;

use App\Entity\SexType;
use App\Model\AbstractSearchService;

class SearchSexTypeService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $sexType = $this->entityManager->getRepository(SexType::class)->findOneById($id);

        return $this->createMessage($sexType);
    }

    public function findOneByName(string $name): array
    {
        $sexType = $this->entityManager->getRepository(SexType::class)->findOneByName($name);

        return $this->createMessage($sexType);
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'sex type does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'sex type exist', ['sexType' => $data]);
    }
}