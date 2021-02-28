<?php

namespace App\Model;

use App\Service\CheckPrivilegesService;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchProductsService;
use App\Service\Searches\SearchUsersService;
use App\Service\Searches\SearchWishlistService;
use App\Traits\accessTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractWishlist
{
    use accessTrait;

    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected SearchUsersService $searchUsersService;
    protected SearchProductsService $searchProductsService;
    protected SearchWishlistService $searchWishlistService;
    protected EntityManagerInterface $entityManager;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchUsersService $searchUsersService,
        SearchProductsService $searchProductsService,
        SearchWishlistService $searchWishlistService,
        EntityManagerInterface $entityManager,
        CheckPrivilegesService $checkPrivilegesService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchUsersService = $searchUsersService;
        $this->searchProductsService = $searchProductsService;
        $this->searchWishlistService = $searchWishlistService;
        $this->entityManager = $entityManager;
        $this->checkPrivilegesService = $checkPrivilegesService;
    }

    abstract public function addList(Request $request): JsonResponse;

    abstract public function removeList(Request $request): JsonResponse;

    abstract public function clearList(Request $request): JsonResponse;

    abstract public function addProduct(Request $request): JsonResponse;

    abstract public function removeProduct(Request $request): JsonResponse;

    abstract public function getList(Request $request): JsonResponse;
}