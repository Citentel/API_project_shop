<?php

namespace App\Service\Searches;

use App\Entity\Roles;
use App\Model\AbstractSearchService;

class SearchRolesService extends AbstractSearchService
{
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

    protected function createMessage($data): array
    {
        if ($data === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'role does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'role exist', ['role' => $data]);
    }
}