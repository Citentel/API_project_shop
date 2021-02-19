<?php

namespace App\Service\Searches;

use App\Entity\SizeType;
use App\Service\GenerateResponseService;
use Doctrine\ORM\EntityManagerInterface;

class SearchSizeTypeService
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
        $sizeType = $this->entityManager->getRepository(SizeType::class)->findOneById($id);

        return $this->createMessage($sizeType);
    }

    public function findOneByName(int $name): array
    {
        $sizeType = $this->entityManager->getRepository(SizeType::class)->findOneByName($name);

        return $this->createMessage($sizeType);
    }

    private function createMessage($sizeType): array
    {
        if (!$sizeType) {
            return $this->generateResponseService->generateArrayResponse(404, 'size type does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'size type exist', ['sizeType' => $sizeType]);
    }
}