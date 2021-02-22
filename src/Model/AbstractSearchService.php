<?php


namespace App\Model;

use App\Service\GenerateResponseService;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractSearchService
{
    protected GenerateResponseService $generateResponseService;
    protected EntityManagerInterface $entityManager;

    public function __construct
    (
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager
    )
    {
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
    }

    abstract public function findOneById(int $id): array;

    abstract public function findOneByName(string $name): array;

    abstract protected function createMessage($data): array;
}