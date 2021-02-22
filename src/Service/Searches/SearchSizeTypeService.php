<?php

namespace App\Service\Searches;

use App\Entity\SizeType;
use App\Model\AbstractSearchService;

class SearchSizeTypeService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $sizeType = $this->entityManager->getRepository(SizeType::class)->findOneById($id);

        return $this->createMessage($sizeType);
    }

    public function findOneByName(string $name): array
    {
        $sizeType = $this->entityManager->getRepository(SizeType::class)->findOneByName($name);

        return $this->createMessage($sizeType);
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'size type does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'size type exist', ['sizeType' => $data]);
    }
}