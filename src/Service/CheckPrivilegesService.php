<?php

namespace App\Service;

use App\Entity\Roles;
use App\Service\Searches\SearchRolesService;
use App\Service\Searches\SearchUsersService;
use Symfony\Component\HttpFoundation\Request;

class CheckPrivilegesService
{
    private SearchUsersService $searchUsersService;
    private CheckRequestService $checkRequestService;
    private GenerateResponseService $generateResponseService;
    private SearchRolesService $searchRolesService;

    public function __construct
    (
        SearchUsersService $searchUsersService,
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        SearchRolesService $searchRolesService
    )
    {
        $this->searchUsersService = $searchUsersService;
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->searchRolesService = $searchRolesService;
    }

    public function checkPrivileges(Request $request, string $roleRequired): array
    {
        $accessUser = $request->request->get('access_user');

        $isRoleExist = $this->searchRolesService->findOneByName($roleRequired);

        if ($isRoleExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isRoleExist['code'], $isRoleExist['message']);
        }

        /** @var Roles $role */
        $role = $isRoleExist['data']['role'];

        $userCost = $accessUser->getRole()->getCost();
        $roleCost = $role->getCost();

        if ($userCost < $roleCost) {
            return $this->generateResponseService->generateJsonResponse(423, 'user does not have access');
        }

        return $this->generateResponseService->generateJsonResponse(200, 'user have access');
    }
}