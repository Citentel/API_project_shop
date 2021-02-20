<?php

namespace App\Service\Searches;

use App\Entity\MainType;
use App\Service\GenerateResponseService;
use Doctrine\ORM\EntityManagerInterface;

class SearchMainTypeService
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
        $mainType = $this->entityManager->getRepository(MainType::class)->findOneById($id);

        return $this->createMessage($mainType);
    }

    public function findOneByName(string $name): array
    {
        $mainType = $this->entityManager->getRepository(MainType::class)->findOneByName($name);

        return $this->createMessage($mainType);
    }

    private function createMessage($mainType): array
    {
        if (!$mainType) {
            return $this->generateResponseService->generateArrayResponse(404, 'main type does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'main type exist', ['mainType' => $mainType]);
    }
}