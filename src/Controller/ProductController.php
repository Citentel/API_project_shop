<?php


namespace App\Controller;

use App\Entity\Products;
use App\Model\AbstractProduct;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractProduct
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/product/add", methods={"POST"})
     */
    public function createProduct(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name', 'description', 'price_crossed', 'price', 'ammount', 'display', 'size', 'sex_type'])
            ->setFieldsOptional(['main_type', 'sub_type'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isNameExist = $this->searchProductService->findOneByName($data['name']);

        if ($isNameExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse('409', 'product name exist')['data'];
        }

        $product = (new Products())
            ->setName($data['name'])
            ->setDescription($data['description'])
            ->setPriceCrossed($data['price_crossed'])
            ->setPrice($data['price'])
            ->setAmmount($data['ammount'])
            ->setDisplay($data['display']);

        foreach ($data['size'] as $sizeType) {
            $isSizeTypeExist = $this->searchSizeTypeService->findOneById($sizeType);

            if ($isSizeTypeExist['code'] === 200) {
                $product->addSizeType($isSizeTypeExist['data']['sizeType']);
            }
        }

        foreach ($data['sex_type'] as $sexType) {
            $isSexTypeExist = $this->searchSexTypeService->findOneById($sexType);

            if ($isSexTypeExist['code'] === 200) {
                $product->addSexType($isSexTypeExist['data']['sexType']);
            }
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'product added')['data'];
    }

    public function updateProduct(Request $request): JsonResponse
    {
        // TODO: Implement updateProduct() method.
    }

    public function getProduct(Request $request): JsonResponse
    {
        // TODO: Implement getProduct() method.
    }

    public function getProducts(Request $request): JsonResponse
    {
        // TODO: Implement getProducts() method.
    }
}