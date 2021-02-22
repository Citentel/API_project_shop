<?php

namespace App\Service\Searches;

use App\Entity\Products;
use App\Model\AbstractSearchService;

class SearchProductsService extends AbstractSearchService
{
    public function findOneById(int $id): array
    {
        $product = $this->entityManager->getRepository(Products::class)->findOneById($id);

        return $this->createMessage($product);
    }

    public function findOneByName(string $name): array
    {
        $products = $this->entityManager->getRepository(Products::class)->findBy(['name' => $name]);

        return $this->createMessage($products);
    }

    public function generateResponseProduct(Products $product): array
    {
        $productResponse = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price_crossed' => $product->getPriceCrossed() ?? 0,
            'price' => $product->getPrice() ?? 0,
            'ammount' => $product->getAmmount(),
            'display' => $product->getDisplay(),
            'sex_type' => [],
            'size_type' => [],
            'main_type' => [],
            'sub_type' => [],
        ];

        if (!empty($product->getSexTypes()->getValues())) {
            foreach ($product->getSexTypes()->getValues() as $sexType) {
                $productResponse['sex_type'][] = [
                    'id' => $sexType->getId(),
                    'name' => $sexType->getName(),
                ];
            }
        }

        if (!empty($product->getSizeTypes()->getValues())) {
            foreach ($product->getSizeTypes()->getValues() as $sizeType) {
                $productResponse['size_type'][] = [
                    'id' => $sizeType->getId(),
                    'name' => $sizeType->getName(),
                ];
            }
        }

        if (!empty($product->getMainTypes()->getValues())) {
            foreach ($product->getMainTypes()->getValues() as $mainType) {
                $productResponse['main_type'][] = [
                    'id' => $mainType->getId(),
                    'name' => $mainType->getName(),
                ];
            }
        }

        if (!empty($product->getSubTypes()->getValues())) {
            foreach ($product->getSubTypes()->getValues() as $subType) {
                $productResponse['sub_type'][] = [
                    'id' => $subType->getId(),
                    'name' => $subType->getName(),
                ];
            }
        }

        return $productResponse;
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'product does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'product exist', ['product' => $data]);
    }
}