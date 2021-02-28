<?php

namespace App\Controller;

use App\Entity\Roles;
use App\Entity\Users;
use App\Model\AbstractRole;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RoleController extends AbstractRole
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/role/add", methods={"POST"})
     */
    public function addRole(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name', 'cost'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isRoleExist = $this->searchRolesService->findOneByName($data['name']);

        if ($isRoleExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'role exist in database')['data'];
        }

        $role = (new Roles())
            ->setName($data['name'])
            ->setCost($data['cost'] < 0 ? 0 : $data['cost']);

        $this->entityManager->persist($role);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'role added')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/role/update", methods={"PATCH"})
     */
    public function updateRole(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->setFieldsOptional(['name', 'cost'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isRoleExist = $this->searchRolesService->findOneById($data['id']);

        if ($isRoleExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse($isRoleExist['code'], $isRoleExist['message'])['data'];
        }

        /** @var Roles $role */
        $role = $isRoleExist['data']['role'];

        if (isset($data['name'])) {
            $role->setName($data['name']);
        }

        if (isset($data['cost'])) {
            $role->setCost($data['cost'] < 0 ? 0 : $data['cost']);
        }

        $this->entityManager->persist($role);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'role updated')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/role/getOne", methods={"GET"})
     */
    public function getRole(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isRoleExist = $this->searchRolesService->findOneById($data['id']);

        if ($isRoleExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isRoleExist['code'], $isRoleExist['message'])['data'];
        }

        /** @var Roles $role */
        $role = $isRoleExist['data']['role'];

        $roleResponse = [
            'id' => $role->getId(),
            'name' => $role->getName(),
            'cost' => $role->getCost(),
        ];

        return $this->generateResponseService->generateJsonResponse(200, 'return role', $roleResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/role/getAll", methods={"GET"})
     */
    public function getRoles(Request $request): JsonResponse
    {
        $roles = $this->entityManager->getRepository(Roles::class)->findAll();

        if (empty($roles)) {
            return $this->generateResponseService->generateJsonResponse(404, 'roles do not in database')['data'];
        }

        $rolesResponse = [];

        /** @var Roles $role */
        foreach ($roles as $role) {
            $rolesResponse[] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'cost' => $role->getCost(),
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return roles', $rolesResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/role/getUsers", methods={"GET"})
     */
    public function getUsersByRole(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isRoleExist = $this->searchRolesService->findOneById($data['id']);

        if ($isRoleExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isRoleExist['code'], $isRoleExist['message'])['data'];
        }

        /** @var Roles $role */
        $role = $isRoleExist['data']['role'];

        $users = $role->getUsers()->getValues();

        if (empty($users)) {
            return $this->generateResponseService->generateJsonResponse(404, 'role do not have any users')['data'];
        }

        $usersResponse = [];

        /** @var Users $user */
        foreach ($users as $user) {
            $usersResponse[] = [
                'uid' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
                'role' => [
                    'id' => $user->getRole()->getId(),
                    'name' => $user->getRole()->getName(),
                ],
                'verifyCode' => $user->getVerifyCode() !== null ? 'not verified' : 'verified',
                'restartCode' => $user->getRestartCode() !== null ? 'set' : 'not set',
                'wasDeleted' => (bool)$user->getWasDeleted(),
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return users', $usersResponse)['data'];
    }
}