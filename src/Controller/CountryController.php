<?php

namespace App\Controller;

use App\Entity\Countries;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchCountriesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CountryController
{
    private CheckRequestService $checkRequestService;
    private GenerateResponseService $generateResponseService;
    private EntityManagerInterface $entityManager;
    private SearchCountriesService $searchCountriesService;

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

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/country/get/one")
     */
    public function getCountry(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsOptional(['id', 'code', 'name'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        if (!isset($checkRequest['data'])) {
            return $this->generateResponseService->generateJsonResponse(409, 'request must have at least one field')['data'];
        }

        $data = $checkRequest['data'];

        if (isset($data['id'])) {
            $isCountryExist = $this->searchCountriesService->findOneById($data['id']);

            return $this->getCountryBy($isCountryExist);
        }

        if (isset($data['code'])) {
            $isCountryExist = $this->searchCountriesService->findOneByCode($data['code']);

            return $this->getCountryBy($isCountryExist);
        }

        if (isset($data['name'])) {
            $isCountryExist = $this->searchCountriesService->findOneByName($data['name']);

            return $this->getCountryBy($isCountryExist);
        }

        return $this->generateResponseService->generateJsonResponse(409, 'request must have at least one field')['data'];
    }

    /**
     * @return JsonResponse
     * @Route("/country/get/all")
     */
    public function getCountries(): JsonResponse
    {
        $countries = $this->entityManager->getRepository(Countries::class)->findAll();

        if (empty($countries)) {
            return $this->generateResponseService->generateJsonResponse(409, 'country does not exist')['data'];
        }

        $countriesResponse = [];

        /** @var Countries $country */
        foreach ($countries as $country) {
            $countriesResponse[] = [
                'id' => $country->getId(),
                'code' => $country->getCode(),
                'name' => $country->getName(),
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'get all countries', $countriesResponse)['data'];
    }

    private function getCountryBy(array $isCountryExist): JsonResponse
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