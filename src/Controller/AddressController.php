<?php

namespace App\Controller;

use App\Entity\Addresses;
use App\Entity\Users;
use App\Model\AbstractAddress;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AddressController extends AbstractAddress
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/address/add", methods={"POST"})
     */
    public function addAddress(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['uid', 'country', 'city', 'street', 'home_number', 'premises_number', 'zip'])
            ->setFieldsOptional(['display'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isUserExist = $this->searchUsersService->findOneById($data['uid']);

        if ($isUserExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(500, 'something goes wrong')['data'];
        }

        /** @var Users $user */
        $user = $isUserExist['data']['user'];

        unset($data['uid']);

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
     * @Route("/address/getForUser", methods={"GET"})
     */
    public function getAddressesUser(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['uid'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isUserExist = $this->searchUsersService->findOneById($data['uid']);

        if ($isUserExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(500, 'something goes wrong')['data'];
        }

        /** @var Users $user */
        $user = $isUserExist['data']['user'];

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
     * @Route("/address/changeData", methods={"PATCH"})
     */
    public function changeAddress(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['address_id', 'uid'])
            ->setFieldsOptional(['new_country', 'new_city', 'new_street', 'new_home_number', 'new_premises_number', 'new_zip', 'new_display'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isUserExist = $this->searchUsersService->findOneById($data['uid']);

        if ($isUserExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(404, 'user is not exist')['data'];
        }

        /** @var Users $user */
        $user = $isUserExist['data']['user'];

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
}