<?php

namespace App\Model;

use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchMainTypeService;
use App\Service\Searches\SearchProductsService;
use App\Service\Searches\SearchSexTypeService;
use App\Service\Searches\SearchSizeTypeService;
use App\Service\Searches\SearchSubTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractProduct
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected EntityManagerInterface $entityManager;
    protected SearchProductsService $searchProductService;
    protected SearchSizeTypeService $searchSizeTypeService;
    protected SearchSexTypeService $searchSexTypeService;
    protected SearchMainTypeService $searchMainTypeService;
    protected SearchSubTypeService $searchSubTypeService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchProductsService $searchProductService,
        SearchSizeTypeService $searchSizeTypeService,
        SearchSexTypeService $searchSexTypeService,
        SearchMainTypeService $searchMainTypeService,
        SearchSubTypeService $searchSubTypeService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchProductService = $searchProductService;
        $this->searchSizeTypeService = $searchSizeTypeService;
        $this->searchSexTypeService = $searchSexTypeService;
        $this->searchMainTypeService = $searchMainTypeService;
        $this->searchSubTypeService = $searchSubTypeService;
    }

    abstract public function createProduct(Request $request): JsonResponse;

    abstract public function updateProduct(Request $request): JsonResponse;

    abstract public function getProduct(Request $request): JsonResponse;

    abstract public function getProducts(Request $request): JsonResponse;
}