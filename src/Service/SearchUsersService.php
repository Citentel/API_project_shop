<?php

namespace App\Service;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

class SearchUsersService
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

    public function findOneById(int $uid): array
    {
        // TODO add body function findOneById
    }

    public function findOneByEmail(string $email): array
    {
        $user = $this->entityManager->getRepository(Users::class)->findOneByEmail($email);

        if ($user === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'user does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'user exist', ['user' => $user]);
    }
}