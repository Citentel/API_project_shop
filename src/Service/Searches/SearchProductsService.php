<?php

namespace App\Service\Searches;

use App\Entity\Products;
use App\Service\GenerateResponseService;
use Doctrine\ORM\EntityManagerInterface;

class SearchProductsService
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
        $product = $this->entityManager->getRepository(Products::class)->findOneById($id);

        return $this->createMessage($product);
    }

    public function findByName(string $name): array
    {
        $products = $this->entityManager->getRepository(Products::class)->findBy(['name' => $name]);

        return $this->createMessage($products);
    }

    public function findBySize(string $size): array
    {
        $products = $this->entityManager->getRepository(Products::class)->findBy(['size' => $size]);

        return $this->createMessage($products);
    }

    public function findBySexType(string $sexType): array
    {
        $products = $this->entityManager->getRepository(Products::class)->findBy(['sexType' => $sexType]);

        return $this->createMessage($products);
    }

    private function createMessage($product): array
    {
        if (!$product) {
            return $this->generateResponseService->generateArrayResponse(404, 'product does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'product exist', ['product' => $product]);
    }
}