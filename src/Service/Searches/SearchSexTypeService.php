<?php


namespace App\Service\Searches;


use App\Entity\SexType;
use App\Service\GenerateResponseService;
use Doctrine\ORM\EntityManagerInterface;

class SearchSexTypeService
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
        $sexType = $this->entityManager->getRepository(SexType::class)->findOneById($id);

        return $this->createMessage($sexType);
    }

    public function findOneByName(string $name): array
    {
        $sexType = $this->entityManager->getRepository(SexType::class)->findOneByName($name);

        return $this->createMessage($sexType);
    }

    private function createMessage($sexType): array
    {
        if (!$sexType) {
            return $this->generateResponseService->generateArrayResponse(404, 'sex type does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'sex type exist', ['sexType' => $sexType]);
    }
}