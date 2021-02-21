<?php


namespace App\Controller;

use App\Entity\Products;
use App\Entity\SubType;
use App\Model\AbstractSubType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SubTypeController extends AbstractSubType
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/subType/add", methods={"POST"})
     */
    public function addSubType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSubTypeExist = $this->searchSubTypeService->findOneByName($data['name']);

        if ($isSubTypeExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'sub type exist in database')['data'];
        }

        $subType = (new SubType())
            ->setName($data['name']);

        $this->entityManager->persist($subType);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'sub type added')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/subType/getOne", methods={"GET"})
     */
    public function getSubType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSubTypeExist = $this->searchSubTypeService->findOneById($data['id']);

        if ($isSubTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(404, 'sub type does nor exist')['data'];
        }

        /** @var SubType $subType */
        $subType = $isSubTypeExist['data']['subType'];

        return $this->generateResponseService->generateJsonResponse(200, 'return sub type', [
            'id' => $subType->getId(),
            'name' => $subType->getName(),
        ])['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/subType/getAll", methods={"GET"})
     */
    public function getSubTypes(Request $request): JsonResponse
    {
        $subTypes = $this->entityManager->getRepository(SubType::class)->findAll();

        if (!$subTypes) {
            return $this->generateResponseService->generateJsonResponse(404, 'database dont have sub types')['data'];
        }

        $responseSubTypes = [];

        foreach ($subTypes as $subType) {
            $responseSubTypes[] = [
                'id' => $subType->getId(),
                'name' => $subType->getName(),
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return sub types', $responseSubTypes)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/subType/getProducts", methods={"GET"})
     */
    public function getProductBySubType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSubTypeExist = $this->searchSubTypeService->findOneById((int)$data['id']);

        if ($isSubTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isSubTypeExist['code'], $isSubTypeExist['message'])['data'];
        }

        /** @var SubType $subType */
        $subType = $isSubTypeExist['data']['subType'];

        $products  = $subType->getProducts()->getValues();

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