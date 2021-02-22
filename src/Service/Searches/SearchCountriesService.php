<?php

namespace App\Service\Searches;

use App\Entity\Countries;
use App\Model\AbstractSearchService;

class SearchCountriesService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $country = $this->entityManager->getRepository(Countries::class)->findOneById($id);

        return $this->createMessage($country);
    }

    public function findOneByCode(string $code): array
    {
        $country = $this->entityManager->getRepository(Countries::class)->findOneByCode($code);

        return $this->createMessage($country);
    }

    public function findOneByName(string $name): array
    {
        $country = $this->entityManager->getRepository(Countries::class)->findOneByName($name);

        return $this->createMessage($country);
    }

    protected function createMessage($data): array
    {
        if ($data === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'country does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'country exist', ['country' => $data]);
    }
}