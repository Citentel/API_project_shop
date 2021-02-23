<?php

namespace App\Service;

use App\Entity\Roles;
use App\Entity\Users;
use App\Service\Searches\SearchRolesService;
use App\Service\Searches\SearchUsersService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    public function checkPrivileges(int $access_uid, string $roleRequired): array
    {
        $isUserExist = $this->searchUsersService->findOneById($access_uid);

        if ($isUserExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isUserExist['code'], $isUserExist['message']);
        }

        /** @var Users $user */
        $user = $isUserExist['data']['user'];

        $isRoleExist = $this->searchRolesService->findOneByName($roleRequired);

        if ($isRoleExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isRoleExist['code'], $isRoleExist['message']);
        }

        /** @var Roles $role */
        $role = $isRoleExist['data']['role'];

        $userCost = $user->getRole()->getCost();
        $roleCost = $role->getCost();

        if ($userCost < $roleCost) {
            return $this->generateResponseService->generateJsonResponse(423, 'user does not have access');
        }

        return $this->generateResponseService->generateJsonResponse(200, 'user have access');
    }
}