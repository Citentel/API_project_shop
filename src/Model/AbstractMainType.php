<?php

namespace App\Model;

use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchMainTypeService;
use App\Service\Searches\SearchProductsService;
use App\Service\Searches\SearchSubTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractMainType
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

    abstract public function addMainType(Request $request): JsonResponse;

    abstract public function addSubTypeToMainType(Request $request): JsonResponse;

    abstract public function removeSubTypeFromMainType(Request $request): JsonResponse;

    abstract public function getMainType(Request $request): JsonResponse;

    abstract public function getMainTypes(Request $request): JsonResponse;

    abstract public function getSubTypesFromMainType(Request $request): JsonResponse;
}