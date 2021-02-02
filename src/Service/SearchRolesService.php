<?php

namespace App\Service;

use App\Entity\Roles;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

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
        // TODO add body function findOneById
    }

    public function findOneByName(string $name): array
    {
        $role = $this->entityManager->getRepository(Roles::class)->findOneByName($name);

        if ($role === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'role does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'role exist', ['role' => $role]);
    }
}