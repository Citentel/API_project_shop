<?php

namespace App\Service\Searches;

use App\Entity\Addresses;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\GenerateResponseService;

class SearchAddressesService
{
    private GenerateResponseService $generateResponseService;
    private EntityManagerInterface $entityManager;
    private SearchUsersService $searchUsersService;

    public function __construct
    (
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchUsersService $searchUsersService
    )
    {
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchUsersService = $searchUsersService;
    }

    public function findOneById(int $id): array
    {
        $address = $this->entityManager->getRepository(Addresses::class)->findOneById($id);

        return $this->createMessage($address);
    }

    public function findByUser(Users $user): array
    {
        $addresses = $this->entityManager->getRepository(Addresses::class)->findBy(['users' => $user], ['id' => 'ASC']);

        if (empty($addresses)) {
            return $this->generateResponseService->generateArrayResponse(404, 'user does not have addresses');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'user addresses', $addresses);
    }

    private function createMessage($address): array
    {
        if ($address === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'address does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'address exist', ['address' => $address]);
    }
}