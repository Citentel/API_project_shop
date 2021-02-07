<?php

namespace App\Model;

use App\Entity\Addresses;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchAddressesService;
use App\Service\Searches\SearchCountriesService;
use App\Service\Searches\SearchUsersService;
use App\Service\Validators\ValidatorAddressesService;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractAddress
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected SearchUsersService $searchUsersService;
    protected SearchAddressesService $searchAddressesService;
    protected SearchCountriesService $searchCountriesService;
    protected EntityManagerInterface $entityManager;
    protected ValidatorAddressesService $validatorAddressesService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchUsersService $searchUsersService,
        SearchAddressesService $searchAddressesService,
        SearchCountriesService $searchCountriesService,
        EntityManagerInterface $entityManager,
        ValidatorAddressesService $validatorAddressesService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchUsersService = $searchUsersService;
        $this->searchAddressesService = $searchAddressesService;
        $this->searchCountriesService = $searchCountriesService;
        $this->entityManager = $entityManager;
        $this->validatorAddressesService = $validatorAddressesService;
    }

    protected function changeCountry(Addresses $address, string $newCountry): array
    {
        $isChanged = $this->validatorAddressesService->checkField('country', $newCountry);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $isCountryExist = $this->searchCountriesService->findOneByCode($newCountry);

        if ($isCountryExist['code'] !== 200) {
            return $isCountryExist;
        }

        $country = $isCountryExist['data']['country'];

        $address->setCountry($country);

        return $this->generateResponseService->generateArrayResponse(200, 'changed country', ['address' => $address]);
    }

    protected function changeCity(Addresses $address, string $newCity): array
    {
        $isChanged = $this->validatorAddressesService->checkField('city', $newCity);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setCity($newCity);

        return $this->generateResponseService->generateArrayResponse(200, 'changed city', ['address' => $address]);
    }

    protected function changeStreet(Addresses $address, string $newStreet): array
    {
        $isChanged = $this->validatorAddressesService->checkField('city', $newStreet);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setStreet($newStreet);

        return $this->generateResponseService->generateArrayResponse(200, 'changed street', ['address' => $address]);
    }

    protected function changeHomeNumber(Addresses $address, int $newHomeNumber): array
    {
        $isChanged = $this->validatorAddressesService->checkField('home_number', $newHomeNumber);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setHomeNumber($newHomeNumber);

        return $this->generateResponseService->generateArrayResponse(200, 'changed homeNumber', ['address' => $address]);
    }

    protected function changePremisesNumber(Addresses $address, int $newPremisesNumber): array
    {
        $isChanged = $this->validatorAddressesService->checkField('premises_number', $newPremisesNumber);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setPremisesNumber($newPremisesNumber);

        return $this->generateResponseService->generateArrayResponse(200, 'changed premisesNumber', ['address' => $address]);
    }

    protected function changeZip(Addresses $address, int $newZip): array
    {
        $isChanged = $this->validatorAddressesService->checkField('premises_number', $newZip);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setZip($newZip);

        return $this->generateResponseService->generateArrayResponse(200, 'changed zip', ['address' => $address]);
    }

    protected function changeDisplay(Addresses $address, bool $newDisplay): array
    {
        $address->setDisplay($newDisplay);

        return $this->generateResponseService->generateArrayResponse(200, 'changed zip', ['address' => $address]);
    }
}