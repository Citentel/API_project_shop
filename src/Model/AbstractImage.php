<?php

namespace App\Model;

use App\Service\CheckPrivilegesService;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchImageService;
use App\Service\Searches\SearchProductsService;
use App\Traits\accessTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractImage
{
    use accessTrait;

    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected EntityManagerInterface $entityManager;
    protected SearchImageService $searchImageService;
    protected SearchProductsService $searchProductsService;
    protected KernelInterface $appKernel;
    protected string $imagesPath;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchImageService $searchImageService,
        SearchProductsService $searchProductsService,
        KernelInterface $appKernel,
        CheckPrivilegesService $checkPrivilegesService
    )
    {
        $this->checkPrivilegesService = $checkPrivilegesService;
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchImageService = $searchImageService;
        $this->searchProductsService = $searchProductsService;
        $this->appKernel = $appKernel;
        $this->imagesPath = $this->appKernel->getProjectDir() . '/public/images/';
    }

    abstract public function addImage(Request $request): JsonResponse;

    abstract public function updateImage(Request $request): JsonResponse;

    abstract public function getImage(Request $request): JsonResponse;

    abstract public function getImages(Request $request): JsonResponse;

    abstract public function getImageByProduct(Request $request): JsonResponse;
}