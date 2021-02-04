<?php

namespace App\Service\Searches;

use App\Entity\Countries;
use App\Service\GenerateResponseService;
use Doctrine\ORM\EntityManagerInterface;

class SearchCountriesService
{
    private GenerateResponseService $generateResponseService;
    private EntityManagerInterface $entityManager;

    public function __construct
    (
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager
    )
    {
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
    }

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

    private function createMessage($country): array
    {
        if ($country === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'country does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'country exist', ['country' => $country]);
    }
}