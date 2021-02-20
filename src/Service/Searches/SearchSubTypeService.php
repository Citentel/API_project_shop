<?php

namespace App\Service\Searches;

use App\Entity\SubType;
use App\Service\GenerateResponseService;
use Doctrine\ORM\EntityManagerInterface;

class SearchSubTypeService
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
        $subType = $this->entityManager->getRepository(SubType::class)->findOneById($id);

        return $this->createMessage($subType);
    }

    public function findOneByName(string $name): array
    {
        $subType = $this->entityManager->getRepository(SubType::class)->findOneByName($name);

        return $this->createMessage($subType);
    }

    private function createMessage($subType): array
    {
        if (!$subType) {
            return $this->generateResponseService->generateArrayResponse(404, 'sub type does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'sub type exist', ['subType' => $subType]);
    }
}