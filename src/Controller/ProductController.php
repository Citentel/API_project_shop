<?php


namespace App\Controller;

use App\Entity\Images;
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
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name', 'description', 'price', 'display'])
            ->setFieldsOptional(['price_crossed', 'ammount'])
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

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'product added')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/product/update", methods={"PATCH"})
     */
    public function updateProduct(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->setFieldsOptional(['name', 'description', 'price', 'display', 'price_crossed', 'ammount'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isProductExist = $this->searchProductService->findOneById($data['id']);

        if ($isProductExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isProductExist['code'], $isProductExist['message'])['data'];
        }

        /** @var Products $product */
        $product = $isProductExist['data']['product'];

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }

        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }

        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }

        if (isset($data['display'])) {
            $product->setDisplay($data['display']);
        }

        if (isset($data['price_crossed'])) {
            $product->setPriceCrossed($data['price_crossed']);
        }

        if (isset($data['ammount'])) {
            $product->setAmmount($data['ammount'] < 0 ? 0 : $data['ammount']);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'product updated')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/product/addType", methods={"POST"})
     */
    public function addType(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->setFieldsOptional(['sex_type_id', 'size_type_id', 'main_type_id', 'sub_type_id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isProductExist = $this->searchProductService->findOneById($data['id']);

        if ($isProductExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isProductExist['code'], $isProductExist['message'])['data'];
        }

        /** @var Products $product */
        $product = $isProductExist['data']['product'];

        if (isset($data['sex_type_id'])) {
            $isSexTypeExist = $this->searchSexTypeService->findOneById($data['sex_type_id']);

            if ($isSexTypeExist['code'] !== 200) {
                return $this->generateResponseService->generateJsonResponse($isSexTypeExist['code'], $isSexTypeExist['message'])['data'];
            }

            $sexType = $isSexTypeExist['data']['sexType'];

            $product->addSexType($sexType);
        }

        if (isset($data['size_type_id'])) {
            $isSizeTypeExist = $this->searchSizeTypeService->findOneById($data['size_type_id']);

            if ($isSizeTypeExist['code'] !== 200) {
                return $this->generateResponseService->generateJsonResponse($isSizeTypeExist['code'], $isSizeTypeExist['message'])['data'];
            }

            $sizeType = $isSizeTypeExist['data']['sizeType'];

            $product->addSizeType($sizeType);
        }

        if (isset($data['main_type_id'])) {
            $isMainTypeExist = $this->searchMainTypeService->findOneById($data['main_type_id']);

            if ($isMainTypeExist['code'] !== 200) {
                return $this->generateResponseService->generateJsonResponse($isMainTypeExist['code'], $isMainTypeExist['message'])['data'];
            }

            $mainType = $isMainTypeExist['data']['mainType'];

            $product->addMainType($mainType);
        }

        if (isset($data['sub_type_id'])) {
            $isSubTypeExist = $this->searchSubTypeService->findOneById($data['sub_type_id']);

            if ($isSubTypeExist['code'] !== 200) {
                return $this->generateResponseService->generateJsonResponse($isSubTypeExist['code'], $isSubTypeExist['message'])['data'];
            }

            $subType = $isSubTypeExist['data']['subType'];

            $product->addSubType($subType);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added types to product')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/product/removeType", methods={"DELETE"})
     */
    public function removeType(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->setFieldsOptional(['sex_type_id', 'size_type_id', 'main_type_id', 'sub_type_id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isProductExist = $this->searchProductService->findOneById($data['id']);

        if ($isProductExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isProductExist['code'], $isProductExist['message'])['data'];
        }

        /** @var Products $product */
        $product = $isProductExist['data']['product'];

        if (isset($data['sex_type_id'])) {
            $isSexTypeExist = $this->searchSexTypeService->findOneById($data['sex_type_id']);

            if ($isSexTypeExist['code'] !== 200) {
                return $this->generateResponseService->generateJsonResponse($isSexTypeExist['code'], $isSexTypeExist['message'])['data'];
            }

            $sexType = $isSexTypeExist['data']['sexType'];

            $product->removeSexType($sexType);
        }

        if (isset($data['size_type_id'])) {
            $isSizeTypeExist = $this->searchSizeTypeService->findOneById($data['size_type_id']);

            if ($isSizeTypeExist['code'] !== 200) {
                return $this->generateResponseService->generateJsonResponse($isSizeTypeExist['code'], $isSizeTypeExist['message'])['data'];
            }

            $sizeType = $isSizeTypeExist['data']['sizeType'];

            $product->removeSizeType($sizeType);
        }

        if (isset($data['main_type_id'])) {
            $isMainTypeExist = $this->searchMainTypeService->findOneById($data['main_type_id']);

            if ($isMainTypeExist['code'] !== 200) {
                return $this->generateResponseService->generateJsonResponse($isMainTypeExist['code'], $isMainTypeExist['message'])['data'];
            }

            $mainType = $isMainTypeExist['data']['mainType'];

            $product->removeMainType($mainType);
        }

        if (isset($data['sub_type_id'])) {
            $isSubTypeExist = $this->searchSubTypeService->findOneById($data['sub_type_id']);

            if ($isSubTypeExist['code'] !== 200) {
                return $this->generateResponseService->generateJsonResponse($isSubTypeExist['code'], $isSubTypeExist['message'])['data'];
            }

            $subType = $isSubTypeExist['data']['subType'];

            $product->removeSubType($subType);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'removed types from product')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/product/getOne", methods={"GET"})
     */
    public function getProduct(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isProductExist = $this->searchProductService->findOneById((int)$data['id']);

        if ($isProductExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isProductExist['code'], $isProductExist['message'])['data'];
        }

        /** @var Products $product */
        $product = $isProductExist['data']['product'];

        $productResponse = $this->searchProductService->generateResponseProduct($product);

        return $this->generateResponseService->generateJsonResponse(200, 'return product', $productResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/product/getAll", methods={"GET"})
     */
    public function getProducts(Request $request): JsonResponse
    {
        $products = $this->entityManager->getRepository(Products::class)->findAll();

        if (empty($products)) {
            return $this->generateResponseService->generateJsonResponse(404, 'database does not have product')['data'];
        }

        $productsResponse = [];

        /** @var Products $product */
        foreach ($products as $product) {
            $productsResponse[] = $this->searchProductService->generateResponseProduct($product);
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return products', $productsResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/product/addImage", methods={"POST"})
     */
    public function addImage(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id', 'image'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isProductExist = $this->searchProductService->findOneById($data['id']);

        if ($isProductExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isProductExist['code'], $isProductExist['message'])['data'];
        }

        /** @var Products $product */
        $product = $isProductExist['data']['product'];

        $isImageExist = $this->searchImageService->findOneById($data['image']);

        if ($isImageExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isImageExist['code'], $isImageExist['message'])['data'];
        }

        /** @var Images $image */
        $image = $isImageExist['data']['image'];

        $product->addImage($image);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added image to product')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/product/removeImage", methods={"DELETE"})
     */
    public function removeImage(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id', 'image'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isProductExist = $this->searchProductService->findOneById($data['id']);

        if ($isProductExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isProductExist['code'], $isProductExist['message'])['data'];
        }

        /** @var Products $product */
        $product = $isProductExist['data']['product'];

        $isImageExist = $this->searchImageService->findOneById($data['image']);

        if ($isImageExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isImageExist['code'], $isImageExist['message'])['data'];
        }

        /** @var Images $image */
        $image = $isImageExist['data']['image'];

        $product->removeImage($image);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'removed image from product')['data'];
    }
}