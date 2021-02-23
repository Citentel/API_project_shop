<?php


namespace App\Controller;

use App\Entity\Products;
use App\Entity\SizeType;
use App\Model\AbstractType;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchProductsService;
use App\Service\Searches\SearchSizeTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SizeTypeController extends AbstractType
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected SearchSizeTypeService $searchSizeTypeService;
    protected EntityManagerInterface $entityManager;
    protected SearchProductsService $searchProductsService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchSizeTypeService $searchSizeTypeService,
        EntityManagerInterface $entityManager,
        SearchProductsService $searchProductsService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchSizeTypeService = $searchSizeTypeService;
        $this->entityManager = $entityManager;
        $this->searchProductsService = $searchProductsService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sizeType/add", methods={"POST"})
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
    public function getTypes(Request $request): JsonResponse
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

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/sexType/update", methods={"PATCH"})
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

        $isSizeTypeExist = $this->searchSizeTypeService->findOneByName($data['name']);

        if ($isSizeTypeExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'size type with name (' . $data['name'] . ') exist in database')['data'];
        }

        $isSizeTypeExist = $this->searchSizeTypeService->findOneById($data['id']);

        if ($isSizeTypeExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isSizeTypeExist['code'], $isSizeTypeExist['message'])['data'];
        }

        /** @var SizeType $sizeType */
        $sizeType = $isSizeTypeExist['data']['sizeType'];

        $sizeType->setName($data['name']);

        $this->entityManager->persist($sizeType);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'size type updated')['data'];
    }
}