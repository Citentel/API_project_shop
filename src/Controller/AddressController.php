<?php

namespace App\Controller;

use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchAddressesService;
use App\Service\Searches\SearchUsersService;
use App\Service\Validators\ValidatorAddressesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AddressController extends AbstractController
{
    private CheckRequestService $checkRequestService;
    private GenerateResponseService $generateResponseService;
    private SearchUsersService $searchUsersService;
    private SearchAddressesService $searchAddressesService;
    private EntityManagerInterface $entityManager;
    private ValidatorAddressesService $validatorAddressesService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchUsersService $searchUsersService,
        SearchAddressesService $searchAddressesService,
        EntityManagerInterface $entityManager,
        ValidatorAddressesService $validatorAddressesService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchUsersService = $searchUsersService;
        $this->searchAddressesService = $searchAddressesService;
        $this->entityManager = $entityManager;
        $this->validatorAddressesService = $validatorAddressesService;
    }

    /**
     * @param Request $request
     * @Route("/test1")
     */
    public function test(Request $request)
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['country', 'city', 'street', 'home_number', 'premises_number', 'zip'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $this->validatorAddressesService->checkFullyAddressUser($data);

        dd('end');
    }
}