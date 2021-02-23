<?php

namespace App\Model;

use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchRolesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractRole
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected EntityManagerInterface $entityManager;
    protected SearchRolesService $searchRolesService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchRolesService $searchRolesService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchRolesService = $searchRolesService;
    }

    abstract public function addRole(Request $request): JsonResponse;

    abstract public function updateRole(Request $request): JsonResponse;

    abstract public function getRole(Request $request): JsonResponse;

    abstract public function getRoles(Request $request): JsonResponse;

    abstract public function getUsersByRole(Request $request): JsonResponse;
}