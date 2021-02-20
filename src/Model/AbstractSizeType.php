<?php

namespace App\Model;

use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchSizeTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSizeType
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected SearchSizeTypeService $searchSizeTypeService;
    protected EntityManagerInterface $entityManager;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchSizeTypeService $searchSizeTypeService,
        EntityManagerInterface $entityManager
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchSizeTypeService = $searchSizeTypeService;
        $this->entityManager = $entityManager;
    }

    abstract public function addSizeType(Request $request): JsonResponse;

    abstract public function getSizeType(Request $request): JsonResponse;

    abstract public function getSizeTypes(Request $request): JsonResponse;
}