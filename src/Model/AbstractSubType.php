<?php

namespace App\Model;

use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchSubTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSubType
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected SearchSubTypeService $searchSubTypeService;
    protected EntityManagerInterface $entityManager;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchSubTypeService $searchSubTypeService,
        EntityManagerInterface $entityManager
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchSubTypeService = $searchSubTypeService;
        $this->entityManager = $entityManager;
    }

    abstract public function addSubType(Request $request): JsonResponse;

    abstract public function getSubType(Request $request): JsonResponse;

    abstract public function getSubTypes(Request $request): JsonResponse;
}