<?php


namespace App\Controller;

use App\Entity\MainType;
use App\Entity\Products;
use App\Entity\SubType;
use App\Model\AbstractMainType;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchMainTypeService;
use App\Service\Searches\SearchProductsService;
use App\Service\Searches\SearchSubTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainTypeController extends AbstractMainType
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected SearchMainTypeService $searchMainTypeService;
    protected SearchSubTypeService $searchSubTypeService;
    protected EntityManagerInterface $entityManager;
    protected SearchProductsService $searchProductsService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchMainTypeService $searchMainTypeService,
        SearchSubTypeService $searchSubTypeService,
        EntityManagerInterface $entityManager,
        SearchProductsService $searchProductsService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchMainTypeService = $searchMainTypeService;
        $this->searchSubTypeService = $searchSubTypeService;
        $this->entityManager = $entityManager;
        $this->searchProductsService = $searchProductsService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/mainType/add", methods={"POST"})
     */
    public function addType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isMainTypeExist = $this->searchMainTypeService->findOneByName($data['name']);

        if ($isMainTypeExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'main type exist in database')['data'];
        }

        $mainType = (new MainType())
            ->setName($data['name']);

        $this->entityManager->persist($mainType);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'main type added')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/mainType/addSubType", methods={"POST"})
     */
    public function addSubTypeToMainType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['sub_type_id', 'main_type_id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSubTypeExist = $this->searchSubTypeService->findOneById($data['sub_type_id']);

        if ($isSubTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isSubTypeExist['code'], $isSubTypeExist['message'])['data'];
        }

        /** @var SubType $subType */
        $subType = $isSubTypeExist['data']['subType'];

        $isMainTypeExist = $this->searchMainTypeService->findOneById($data['main_type_id']);

        if ($isMainTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isMainTypeExist['code'], $isMainTypeExist['message'])['data'];
        }

        /** @var MainType $mainType */
        $mainType = $isMainTypeExist['data']['mainType'];

        $mainType->addSubType($subType);

        $this->entityManager->persist($mainType);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added sub type into main type')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/mainType/removeSubType", methods={"DELETE"})
     */
    public function removeSubTypeFromMainType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['sub_type_id', 'main_type_id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isSubTypeExist = $this->searchSubTypeService->findOneById($data['sub_type_id']);

        if ($isSubTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isSubTypeExist['code'], $isSubTypeExist['message'])['data'];
        }

        /** @var SubType $subType */
        $subType = $isSubTypeExist['data']['subType'];

        $isMainTypeExist = $this->searchMainTypeService->findOneById($data['main_type_id']);

        if ($isMainTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isMainTypeExist['code'], $isMainTypeExist['message'])['data'];
        }

        /** @var MainType $mainType */
        $mainType = $isMainTypeExist['data']['mainType'];

        $mainType->removeSubType($subType);

        $this->entityManager->persist($mainType);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'removed sub type from main type')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/mainType/getOne", methods={"GET"})
     */
    public function getType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isMainTypeExist = $this->searchMainTypeService->findOneById($data['id']);

        if ($isMainTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isMainTypeExist['code'], $isMainTypeExist['message'])['data'];
        }

        /** @var MainType $mainType */
        $mainType = $isMainTypeExist['data']['mainType'];

        $mainTypeResponse = [
            'id' => $mainType->getId(),
            'name' => $mainType->getName(),
        ];

        return $this->generateResponseService->generateJsonResponse(200, 'return main type', $mainTypeResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/mainType/getAll", methods={"GET"})
     */
    public function getTypes(Request $request): JsonResponse
    {
        $mainTypes = $this->entityManager->getRepository(MainType::class)->findAll();

        if (!$mainTypes) {
            return $this->generateResponseService->generateJsonResponse(404, 'database does not have main type')['data'];
        }

        $mainTypesResponse = [];

        /** @var MainType $mainType */
        foreach ($mainTypes as $mainType) {
            $mainTypesResponse[] = [
                'id' => $mainType->getId(),
                'name' => $mainType->getName(),
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return all main types', $mainTypesResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/mainType/getSubTypes", methods={"GET"})
     */
    public function getSubTypesFromMainType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isMainTypeExist = $this->searchMainTypeService->findOneById($data['id']);

        if ($isMainTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isMainTypeExist['code'], $isMainTypeExist['message'])['data'];
        }

        /** @var MainType $mainType */
        $mainType = $isMainTypeExist['data']['mainType'];

        $subTypes = $mainType->getSubTypes()->getValues();

        if (empty($subTypes)) {
            return $this->generateResponseService->generateJsonResponse(200, 'main type does not have sub types')['data'];
        }

        $subTypesResponse = [];

        /** @var SubType $subType */
        foreach ($subTypes as $subType) {
            $subTypesResponse[] = [
                'id' => $subType->getId(),
                'name' => $subType->getName()
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return all sub types', $subTypesResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/mainType/getProducts", methods={"GET"})
     */
    public function getProductByType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isMainTypeExist = $this->searchSubTypeService->findOneById((int)$data['id']);

        if ($isMainTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isMainTypeExist['code'], $isMainTypeExist['message'])['data'];
        }

        /** @var MainType $mainType */
        $mainType = $isMainTypeExist['data']['mainType'];

        $products  = $mainType->getProducts()->getValues();

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

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/mainType/update", methods={"PATCH"})
     */
    public function updateType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id', 'name'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isMainTypeExist = $this->searchMainTypeService->findOneByName($data['name']);

        if ($isMainTypeExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'main type with name (' . $data['name'] . ') exist in database')['data'];
        }

        $isMainTypeExist = $this->searchMainTypeService->findOneById($data['id']);

        if ($isMainTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isMainTypeExist['code'], $isMainTypeExist['message'])['data'];
        }

        /** @var MainType $mainType */
        $mainType = $isMainTypeExist['data']['mainType'];

        $mainType->setName($data['name']);

        $this->entityManager->persist($mainType);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'main type updated')['data'];
    }
}