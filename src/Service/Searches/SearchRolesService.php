<?php

namespace App\Service\Searches;

use App\Entity\Roles;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\GenerateResponseService;

class SearchRolesService
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
        $role = $this->entityManager->getRepository(Roles::class)->findOneById($id);

        return $this->createMessage($role);
    }

    public function findOneByName(string $name): array
    {
        $role = $this->entityManager->getRepository(Roles::class)->findOneByName($name);

        return $this->createMessage($role);
    }

    private function createMessage($role): array
    {
        if ($role === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'role does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'role exist', ['role' => $role]);
    }
}