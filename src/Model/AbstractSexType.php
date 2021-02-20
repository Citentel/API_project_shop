<?php

namespace App\Model;

use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchSexTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSexType
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected SearchSexTypeService $searchSexTypeService;
    protected EntityManagerInterface $entityManager;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchSexTypeService $searchSexTypeService,
        EntityManagerInterface $entityManager
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchSexTypeService = $searchSexTypeService;
        $this->entityManager = $entityManager;
    }

    abstract public function addSexType(Request $request): JsonResponse;

    abstract public function getSexType(Request $request): JsonResponse;

    abstract public function getSexTypes(Request $request): JsonResponse;
}