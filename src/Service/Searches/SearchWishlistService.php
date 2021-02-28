<?php

namespace App\Service\Searches;

use App\Entity\Users;
use App\Entity\Wishlist;
use App\Model\AbstractSearchService;

class SearchWishlistService extends AbstractSearchService
{

    public function findOneById(int $id): array
    {
        $wishlist = $this->entityManager->getRepository(Wishlist::class)->findOneById($id);

        return $this->createMessage($wishlist);
    }

    public function findOneByName(string $name): array
    {
        $wishlist = $this->entityManager->getRepository(Wishlist::class)->findOneByName($name);

        return $this->createMessage($wishlist);
    }

    public function findOneByUser(Users $user): array
    {
        $wishlist = $this->entityManager->getRepository(Wishlist::class)->findOneByUser($user);

        return $this->createMessage($wishlist);
    }

    protected function createMessage($data): array
    {
        if ($data === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'wishlist does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'wishlist exist', ['wishlist' => $data]);
    }
}