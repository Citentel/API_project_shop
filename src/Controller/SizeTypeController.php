<?php


namespace App\Controller;

use App\Entity\Products;
use App\Entity\SexType;
use App\Entity\SizeType;
use App\Model\AbstractSizeType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SizeTypeController extends AbstractSizeType
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sizeType/add", methods={"POST"})
     */
    public function addSizeType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data['name'] = strtoupper($checkRequest['data']['name']);

        $isSizeTypeExist = $this->searchSizeTypeService->findOneByName($data['name']);

        if ($isSizeTypeExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409 , $isSizeTypeExist['message'])['data'];
        }

        $sizeType = (new SizeType())
            ->setName($data['name']);

        $this->entityManager->persist($sizeType);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added size type')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sizeType/getOne", methods={"GET"})
     */
    public function getSizeType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSizeTypeExist = $this->searchSizeTypeService->findOneById((int)$data['id']);

        if ($isSizeTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isSizeTypeExist['code'], $isSizeTypeExist['message'])['data'];
        }

        /** @var SizeType $sizeType */
        $sizeType = $isSizeTypeExist['data']['sizeType'];

        return $this->generateResponseService->generateJsonResponse(200, 'return size type', [
            'id' => $sizeType->getId(),
            'name' => $sizeType->getName(),
        ])['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sizeType/getAll", methods={"GET"})
     */
    public function getSizeTypes(Request $request): JsonResponse
    {
        $sizeTypes = $this->entityManager->getRepository(SizeType::class)->findAll();

        if (empty($sizeTypes)) {
            return $this->generateResponseService->generateJsonResponse(404, 'database does not have size type')['data'];
        }

        $sizeTypesResponse = [];

        /** @var SizeType $sizeType */
        foreach ($sizeTypes as $sizeType) {
            $sizeTypesResponse[] = [
                'id' => $sizeType->getId(),
                'name' => $sizeType->getName()
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return all size types', $sizeTypesResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sizeType/getProducts", methods={"GET"})
     */
    public function getProductBySizeType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSizeTypeExist = $this->searchSizeTypeService->findOneById((int)$data['id']);

        if ($isSizeTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isSizeTypeExist['code'], $isSizeTypeExist['message'])['data'];
        }

        /** @var SizeType $sizeType */
        $sizeType = $isSizeTypeExist['data']['sizeType'];

        $products  = $sizeType->getProducts()->getValues();

        if (empty($products)) {
            return $this->generateResponseService->generateJsonResponse(404, 'database does not have product')['data'];
        }

        $productsResponse = [];

        /** @var Products $product */
        foreach ($products as $product) {
            $productsResponse[] = $this->searchProductsService->generateResponseProduct($product);
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return products', $productsResponse)['data'];
    }
}