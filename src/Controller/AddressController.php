<?php

namespace App\Controller;

use App\Entity\Addresses;
use App\Entity\Users;
use App\Service\CheckPrivilegesService;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchAddressesService;
use App\Service\Searches\SearchCountriesService;
use App\Service\Searches\SearchUsersService;
use App\Service\Validators\ValidatorAddressesService;
use App\Traits\accessTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AddressController
{
    use accessTrait;

    private CheckRequestService $checkRequestService;
    private GenerateResponseService $generateResponseService;
    private SearchUsersService $searchUsersService;
    private SearchAddressesService $searchAddressesService;
    private SearchCountriesService $searchCountriesService;
    private EntityManagerInterface $entityManager;
    private ValidatorAddressesService $validatorAddressesService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchUsersService $searchUsersService,
        SearchAddressesService $searchAddressesService,
        SearchCountriesService $searchCountriesService,
        EntityManagerInterface $entityManager,
        ValidatorAddressesService $validatorAddressesService,
        CheckPrivilegesService $checkPrivilegesService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchUsersService = $searchUsersService;
        $this->searchAddressesService = $searchAddressesService;
        $this->searchCountriesService = $searchCountriesService;
        $this->entityManager = $entityManager;
        $this->validatorAddressesService = $validatorAddressesService;
        $this->checkPrivilegesService = $checkPrivilegesService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/address/add", methods={"POST"})
     */
    public function addAddress(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['country', 'city', 'street', 'home_number', 'premises_number', 'zip'])
            ->setFieldsOptional(['display'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        /** @var Users $user */
        $user = $data['access_user'];

        $validFields = $this->validatorAddressesService->checkFullyAddressUser($data);

        if ($validFields['code'] !== 200) {
            return $validFields['data'];
        }

        $isCountryExist = $this->searchCountriesService->findOneByCode($data['country']);

        if ($isCountryExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isCountryExist['code'], $isCountryExist['message'])['data'];
        }

        $country = $isCountryExist['data']['country'];

        $address = (new Addresses())
            ->setCountry($country)
            ->setCity($data['city'])
            ->setStreet($data['street'])
            ->setHomeNumber($data['home_number'])
            ->setPremisesNumber($data['premises_number'])
            ->setZip($data['zip'])
            ->setDisplay($data['display'] ?? false)
            ->setUsers($user);

        $this->entityManager->persist($address);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'address added')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/address/getForUser", methods={"GET"})
     */
    public function getAddressesUser(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        /** @var Users $user */
        $user = $data['access_user'];

        $userAddresses = $this->searchAddressesService->findByUser($user);

        if ($userAddresses['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($userAddresses['code'], $userAddresses['message'])['data'];
        }

        /** @var Addresses[] $userAddresses */
        $userAddresses = $userAddresses['data'];

        $addressesResponse = [];

        foreach ($userAddresses as $userAddress) {
            $addressesResponse[] = [
                'id' => $userAddress->getId(),
                'country' => [
                    'id' => $userAddress->getCountry()->getId(),
                    'code' => $userAddress->getCountry()->getCode(),
                    'name' => $userAddress->getCountry()->getName(),
                ],
                'city' => $userAddress->getCity(),
                'street' => $userAddress->getStreet(),
                'home_number' => $userAddress->getHomeNumber(),
                'premises_number' => $userAddress->getPremisesNumber(),
                'zip' => $userAddress->getZip(),
                'display' => $userAddress->getDisplay(),
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return all addresses user', $addressesResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/address/changeData", methods={"PATCH"})
     */
    public function changeAddress(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['address_id'])
            ->setFieldsOptional(['new_country', 'new_city', 'new_street', 'new_home_number', 'new_premises_number', 'new_zip', 'new_display'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        /** @var Users $user */
        $user = $data['access_user'];

        $userAddresses = $user->getAddresses()->getValues();

        if (empty($userAddresses)) {
            return $this->generateResponseService->generateJsonResponse(404, 'user does not have addresses')['data'];
        }

        /** @var Addresses $address */
        foreach ($userAddresses as $key => $address) {
            if ($address->getId() !== (int)$data['address_id']) {
                unset($userAddresses[$key]);
            }
        }

        $address = end($userAddresses);

        if (isset($data['new_country'])) {
            $isChanged = $this->changeCountry($address, $data['new_country']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $address = $isChanged['data']['address'];
        }

        if (isset($data['new_city'])) {
            $isChanged = $this->changeCity($address, $data['new_city']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $address = $isChanged['data']['address'];
        }

        if (isset($data['new_street'])) {
            $isChanged = $this->changeStreet($address, $data['new_street']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $address = $isChanged['data']['address'];
        }

        if (isset($data['new_home_number'])) {
            $isChanged = $this->changeHomeNumber($address, $data['new_home_number']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $address = $isChanged['data']['address'];
        }

        if (isset($data['new_premises_number'])) {
            $isChanged = $this->changePremisesNumber($address, $data['new_premises_number']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $address = $isChanged['data']['address'];
        }

        if (isset($data['new_zip'])) {
            $isChanged = $this->changeZip($address, $data['new_zip']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $address = $isChanged['data']['address'];
        }

        if (isset($data['new_display'])) {
            $isChanged = $this->changeDisplay($address, $data['new_display']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $address = $isChanged['data']['address'];
        }


        $this->entityManager->persist($address);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'return all addresses user', [
            'id' => $address->getId(),
            'country' => [
                'id' => $address->getCountry()->getId(),
                'code' => $address->getCountry()->getCode(),
                'name' => $address->getCountry()->getName(),
            ],
            'city' => $address->getCity(),
            'street' => $address->getStreet(),
            'home_number' => $address->getHomeNumber(),
            'premises_number' => $address->getPremisesNumber(),
            'zip' => $address->getZip(),
            'display' => $address->getDisplay(),
        ])['data'];
    }

    private function changeCountry(Addresses $address, string $newCountry): array
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

    private function changeCity(Addresses $address, string $newCity): array
    {
        $isChanged = $this->validatorAddressesService->checkField('city', $newCity);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setCity($newCity);

        return $this->generateResponseService->generateArrayResponse(200, 'changed city', ['address' => $address]);
    }

    private function changeStreet(Addresses $address, string $newStreet): array
    {
        $isChanged = $this->validatorAddressesService->checkField('city', $newStreet);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setStreet($newStreet);

        return $this->generateResponseService->generateArrayResponse(200, 'changed street', ['address' => $address]);
    }

    private function changeHomeNumber(Addresses $address, int $newHomeNumber): array
    {
        $isChanged = $this->validatorAddressesService->checkField('home_number', $newHomeNumber);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setHomeNumber($newHomeNumber);

        return $this->generateResponseService->generateArrayResponse(200, 'changed homeNumber', ['address' => $address]);
    }

    private function changePremisesNumber(Addresses $address, int $newPremisesNumber): array
    {
        $isChanged = $this->validatorAddressesService->checkField('premises_number', $newPremisesNumber);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setPremisesNumber($newPremisesNumber);

        return $this->generateResponseService->generateArrayResponse(200, 'changed premisesNumber', ['address' => $address]);
    }

    private function changeZip(Addresses $address, int $newZip): array
    {
        $isChanged = $this->validatorAddressesService->checkField('premises_number', $newZip);

        if ($isChanged['code'] !== 200) {
            return $isChanged;
        }

        $address->setZip($newZip);

        return $this->generateResponseService->generateArrayResponse(200, 'changed zip', ['address' => $address]);
    }

    private function changeDisplay(Addresses $address, bool $newDisplay): array
    {
        $address->setDisplay($newDisplay);

        return $this->generateResponseService->generateArrayResponse(200, 'changed zip', ['address' => $address]);
    }
}