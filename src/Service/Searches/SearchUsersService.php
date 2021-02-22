<?php

namespace App\Service\Searches;

use App\Entity\Users;
use App\Model\AbstractSearchService;

class SearchUsersService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $user = $this->entityManager->getRepository(Users::class)->findOneById($id);

        return $this->createMessage($user);
    }

    public function findOneByEmail(string $email): array
    {
        $user = $this->entityManager->getRepository(Users::class)->findOneByEmail($email);

        return $this->createMessage($user);
    }

    public function findOneByArchivedEmail(string $archivedEmail): array
    {
        $user = $this->entityManager->getRepository(Users::class)->findOneByArchivedEmail($archivedEmail);

        return $this->createMessage($user);
    }

    protected function createMessage($data): array
    {
        if ($data === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'user does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'user exist', ['user' => $data]);
    }

    public function findOneByName(string $name): array
    {
        // TODO: Implement findOneByName() method.
    }
}