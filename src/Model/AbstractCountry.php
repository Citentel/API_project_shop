<?php

namespace App\Model;

use App\Entity\Countries;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchCountriesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractCountry extends AbstractController
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected EntityManagerInterface $entityManager;
    protected SearchCountriesService $searchCountriesService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchCountriesService $searchCountriesService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchCountriesService = $searchCountriesService;
    }

    protected function getCountryBy(array $isCountryExist): JsonResponse
    {
        if ($isCountryExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'country does not exist')['data'];
        }

        /** @var Countries $country */
        $country = $isCountryExist['data']['country'];

        return $this->generateResponseService->generateJsonResponse(200, 'country return', [
            'id' => $country->getId(),
            'code' => $country->getCode(),
            'name' => $country->getName(),
        ])['data'];
    }
}