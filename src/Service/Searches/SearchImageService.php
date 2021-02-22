<?php

namespace App\Service\Searches;

use App\Entity\Images;
use App\Model\AbstractSearchService;

class SearchImageService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $image = $this->entityManager->getRepository(Images::class)->findOneById($id);

        return $this->createMessage($image);
    }

    public function findOneByName(string $name): array
    {
        $image = $this->entityManager->getRepository(Images::class)->findOneByName($name);

        return $this->createMessage($image);
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'image does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'image exist', ['image' => $data]);
    }
}