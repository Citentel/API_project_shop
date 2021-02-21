<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\SexType;
use App\Model\AbstractSexType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SexTypeController extends AbstractSexType
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sexType/add", methods={"POST"})
     */
    public function addSexType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSexTypeExist = $this->searchSexTypeService->findOneByName($data['name']);

        if ($isSexTypeExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, $isSexTypeExist['message'])['data'];
        }

        $sexType = (new SexType())
            ->setName($data['name']);

        $this->entityManager->persist($sexType);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added sex type')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sexType/getOne", methods={"GET"})
     */
    public function getSexType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSexTypeExist = $this->searchSexTypeService->findOneById((int)$data['id']);

        if ($isSexTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isSexTypeExist['code'], $isSexTypeExist['message'])['data'];
        }

        /** @var SexType $sexType */
        $sexType = $isSexTypeExist['data']['sexType'];

        return $this->generateResponseService->generateJsonResponse(200, 'return sex type', [
            'id' => $sexType->getId(),
            'name' => $sexType->getName(),
        ])['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sexType/getAll", methods={"GET"})
     */
    public function getSexTypes(Request $request): JsonResponse
    {
        $sexTypes = $this->entityManager->getRepository(SexType::class)->findAll();

        if (empty($sexTypes)) {
            return $this->generateResponseService->generateJsonResponse(404, 'database does not have any sex type')['data'];
        }

        $sexTypesResponse = [];

        /** @var SexType $sexType */
        foreach ($sexTypes as $sexType) {
            $sexTypesResponse[] = [
                'id' => $sexType->getId(),
                'name' => $sexType->getName()
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return all sex types', $sexTypesResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sexType/getProducts", methods={"GET"})
     */
    public function getProductBySexType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSexTypeExist = $this->searchSexTypeService->findOneById((int)$data['id']);

        if ($isSexTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isSexTypeExist['code'], $isSexTypeExist['message'])['data'];
        }

        /** @var SexType $sexType */
        $sexType = $isSexTypeExist['data']['sexType'];

        $products  = $sexType->getProducts()->getValues();

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