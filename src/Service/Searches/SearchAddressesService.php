<?php

namespace App\Service\Searches;

use App\Entity\Addresses;
use App\Entity\Users;
use App\Model\AbstractSearchService;
use App\Service\GenerateResponseService;
use Doctrine\ORM\EntityManagerInterface;

class SearchAddressesService extends AbstractSearchService
{
    protected SearchUsersService $searchUsersService;

    public function __construct
    (
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchUsersService $searchUsersService
    )
    {
        parent::__construct($generateResponseService, $entityManager);
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

    protected function createMessage($data): array
    {
        if ($data === null) {
            return $this->generateResponseService->generateArrayResponse(404, 'address does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'address exist', ['address' => $data]);
    }

    public function findOneByName(string $name): array
    {
        // TODO: Implement findOneByName() method.
    }
}