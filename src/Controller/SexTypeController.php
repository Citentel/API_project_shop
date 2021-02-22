<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\SexType;
use App\Model\AbstractType;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchProductsService;
use App\Service\Searches\SearchSexTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SexTypeController extends AbstractType
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected SearchSexTypeService $searchSexTypeService;
    protected EntityManagerInterface $entityManager;
    protected SearchProductsService $searchProductsService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchSexTypeService $searchSexTypeService,
        EntityManagerInterface $entityManager,
        SearchProductsService $searchProductsService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchSexTypeService = $searchSexTypeService;
        $this->entityManager = $entityManager;
        $this->searchProductsService = $searchProductsService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sexType/add", methods={"POST"})
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
    public function getTypes(Request $request): JsonResponse
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