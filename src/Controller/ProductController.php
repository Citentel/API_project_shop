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
            ->setFieldsRequired(['name', 'description', 'price', 'display'])
            ->setFieldsOptional(['main_type', 'sub_type', 'size_type', 'sex_type', 'price_crossed', 'ammount'])
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
            ->setPriceCrossed(isset($data['price_crossed']) ? $data['price_crossed'] : null)
            ->setPrice($data['price'])
            ->setDisplay($data['display']);

        $ammount = isset($data['ammount']) ? $data['ammount'] : 0;
        $ammount = $ammount < 0 ? 0 : $ammount;

        $product->setAmmount($ammount);

        if (isset($data['size_type'])) {
            foreach ($data['size'] as $sizeType) {
                $isSizeTypeExist = $this->searchSizeTypeService->findOneById($sizeType);

                if ($isSizeTypeExist['code'] === 200) {
                    $product->addSizeType($isSizeTypeExist['data']['sizeType']);
                }
            }
        }

        if (isset($data['sex_type'])) {
            foreach ($data['sex_type'] as $sexType) {
                $isSexTypeExist = $this->searchSexTypeService->findOneById($sexType);

                if ($isSexTypeExist['code'] === 200) {
                    $product->addSexType($isSexTypeExist['data']['sexType']);
                }
            }
        }

        if (isset($data['main_type'])) {
            foreach ($data['main_type'] as $mainType) {
                $isMainTypeExist = $this->searchMainTypeService->findOneById($mainType);

                if ($isMainTypeExist['code'] === 200) {
                    $product->addMainType($isMainTypeExist['data']['mainType']);
                }
            }

        }

        if (isset($data['sub_type'])) {
            foreach ($data['sub_type'] as $subType) {
                $isSubTypeExist = $this->searchSubTypeService->findOneById($subType);

                if ($isSubTypeExist['code'] === 200) {
                    $product->addSubType($isSubTypeExist['data']['subType']);
                }
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