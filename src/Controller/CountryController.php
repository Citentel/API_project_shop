<?php

namespace App\Controller;

use App\Entity\Countries;
use App\Model\AbstractCountry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CountryController extends AbstractCountry
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/country/get/one")
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
     * @Route("api/country/get/all")
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
}