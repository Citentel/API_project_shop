<?php

namespace App\Controller;

use App\Entity\Users;
use App\Model\AbstractUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractUser
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user/register", methods={"POST"})
     */
    public function registerUser(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['firstname', 'lastname', 'email', 'password'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $checkDataFromUser = $this->validatorUserDataService->checkRegisterData($data);

        if ($checkDataFromUser['code'] !== 200) {
            return $checkDataFromUser['data'];
        }

        $isUserExist = $this->searchUsersService->findOneByEmail($data['email']);

        if ($isUserExist['code'] === 200 && !$isUserExist['data']['user']->getWasDeleted()) {
            return $this->generateResponseService->generateJsonResponse(409, 'user exist')['data'];
        }

        $isRoleExist = $this->searchRolesService->findOneByName('ROLE_USER');

        if ($isRoleExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'role does not exist')['data'];
        }

        $role = $isRoleExist['data']['role'];

        $verifyCode = $this->generateCode($data['email']);

        $newUser = (new Users())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setPassword($data['password'])
            ->setVerifyCode($verifyCode)
            ->setRole($role);

        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        $isUserExist = $this->searchUsersService->findOneByEmail($data['email']);

        if ($isUserExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(500, 'something goes wrong')['data'];
        }

        $userId = $isUserExist['data']['user']->getId();

        $email = $this->emailPrepareModel
            ->to($data['email'])
            ->subject('User registration')
            ->html(
                '<p><b>Hello there!</b></p><p>You have been registered with the application. To use the app fully go to the link below and verify your account.</p><p>http://127.0.0.1::8000/user/verify/?uid='.$userId.'&vc='.$verifyCode.'</p><p><small>This message has been generated automatically. Do not reply to this message.</small></p>');

        $this->emailSendService->sendEmail($email);

        return $this->generateResponseService->generateJsonResponse(200, 'user added to database')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user/verify", methods={"PATCH"})
     */
    public function verificationUser(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['uid', 'vc'])
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

        if ($user->getVerifyCode() === null) {
            return $this->generateResponseService->generateJsonResponse(409, 'account has already been verified')['data'];
        }

        if (!password_verify($data['vc'], $user->getVerifyCode())) {
            return $this->generateResponseService->generateJsonResponse(409, 'verify code is wrong')['data'];
        }

        $user->setVerifyCode(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $email = $this->emailPrepareModel
            ->to($user->getEmail())
            ->subject('User verified')
            ->html(
                '<p><b>Hello there!</b></p><p>Your account has been verified.</p><p><small>This message has been generated automatically. Do not reply to this message.</small></p>');

        $this->emailSendService->sendEmail($email);

        return $this->generateResponseService->generateJsonResponse(200, 'user verified')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user/restartCode", methods={"POST"})
     */
    public function generateRestartCode(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['email'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isUserExist = $this->searchUsersService->findOneByEmail($data['email']);

        if ($isUserExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(404, 'user is not exist')['data'];
        }

        /** @var Users $user */
        $user = $isUserExist['data']['user'];

        $restartCode = $this->generateCode($data['email'], 20);

        $user->setRestartCode($restartCode);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $email = $this->emailPrepareModel
            ->to($user->getEmail())
            ->subject('User restart password')
            ->html(
                '<p><b>Hello there!</b></p><p>A request has been sent to reset your account password. To reset your password click the link below.</p><p>http://127.0.0.1::8000/user/restartPassword/?uid='.$user->getId().'&rc='.$restartCode.'</p><p><small>This message has been generated automatically. Do not reply to this message.</small></p>');

        $this->emailSendService->sendEmail($email);

        return $this->generateResponseService->generateJsonResponse(200, 'generate restart code')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user/restartPassword", methods={"PATCH"})
     */
    public function restartPassword(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['email', 'rc', 'new_password'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isUserExist = $this->searchUsersService->findOneByEmail($data['email']);

        if ($isUserExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(404, 'user is not exist')['data'];
        }

        /** @var Users $user */
        $user = $isUserExist['data']['user'];

        if ($user->getRestartCode() === null || !password_verify($data['rc'], $user->getRestartCode())) {
            return $this->generateResponseService->generateJsonResponse(409, 'invalid restart code')['data'];
        }

        $checkDataFromUser = $this->validatorUserDataService->checkPassword($data['new_password']);

        if ($checkDataFromUser['code'] !== 200) {
            return $checkDataFromUser['data'];
        }

        $user->setRestartCode(null)->setPassword($data['new_password']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $email = $this->emailPrepareModel
            ->to($user->getEmail())
            ->subject('User restart password')
            ->html(
                '<p><b>Hello there!</b></p><p>Your password has been reset<p><small>This message has been generated automatically. Do not reply to this message.</small></p>');

        $this->emailSendService->sendEmail($email);

        return $this->generateResponseService->generateJsonResponse(200, 'add new password')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user/delete", methods={"DELETE"})
     */
    public function deleteUser(Request $request): JsonResponse
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
            return $this->generateResponseService->generateJsonResponse(404, 'user is not exist')['data'];
        }

        /** @var Users $user */
        $user = $isUserExist['data']['user'];

        $user->setWasDeleted(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $email = $this->emailPrepareModel
            ->to($user->getEmail())
            ->subject('User deleted')
            ->html(
                '<p><b>Hello there!</b></p><p>Your account has been deleted.<p><small>This message has been generated automatically. Do not reply to this message.</small></p>');

        $this->emailSendService->sendEmail($email);

        return $this->generateResponseService->generateJsonResponse(200, 'user deleted')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user/changeData", methods={"PATCH"})
     */
    public function changeDataUser(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['uid'])
            ->setFieldsOptional(['new_firstname', 'new_lastname', 'new_email', 'new_password'])
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

        if (isset($data['new_firstname'])) {
            $isChanged = $this->changeFirstname($user, $data['new_firstname']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $user = $isChanged['data']['user'];
        }

        if (isset($data['new_lastname'])) {
            $isChanged = $this->changeLastname($user, $data['new_lastname']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $user = $isChanged['data']['user'];
        }

        if (isset($data['new_email'])) {
            $isChanged = $this->changeEmail($user, $data['new_email']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $user = $isChanged['data']['user'];
        }

        if (isset($data['new_password'])) {
            $isChanged = $this->changePassword($user, $data['new_password']);

            if ($isChanged['code'] !== 200) {
                return $isChanged['data'];
            }

            $user = $isChanged['data']['user'];
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'change data user', [
            'uid' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'role' => [
                'id' => $user->getRole()->getId(),
                'name' => $user->getRole()->getName(),
            ],
        ])['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user/get/single", methods={"GET"})
     */
    public function getSingleUser(Request $request): JsonResponse
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
            return $this->generateResponseService->generateJsonResponse(404, 'user is not exist')['data'];
        }

        /** @var Users $user */
        $user = $isUserExist['data']['user'];

        return $this->generateResponseService->generateJsonResponse(200, 'get single user', [
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
        ])['data'];
    }

    /**
     * @return JsonResponse
     * @Route("/user/get/all", methods={"GET"})
     */
    public function getAllUsers(): JsonResponse
    {
        $users = $this->entityManager->getRepository(Users::class)->findAll();

        if (!$users) {
            return $this->generateResponseService->generateJsonResponse(404, 'no users')['data'];
        }

        $response = [];

        /** @var Users $user */
        foreach ($users as $user) {
            $response[] = [
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

        return $this->generateResponseService->generateJsonResponse(200, 'get all users', $response)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user/checkArchivedEmail", methods={"POST"})
     */
    public function checkAccountByArchivedEmail(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['firstname', 'lastname', 'email', 'password'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $checkDataFromUser = $this->validatorUserDataService->checkRegisterData($data);

        if ($checkDataFromUser['code'] !== 200) {
            return $checkDataFromUser['data'];
        }

        $isUserExist = $this->searchUsersService->findOneByArchivedEmail($data['email']);

        if ($isUserExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(404, 'user is not exist')['data'];
        }

        /** @var Users $user */
        $user = $isUserExist['data']['user'];

        if (!password_verify($data['password'], $user->getPassword())) {
            return $this->generateResponseService->generateJsonResponse(403, 'invalid password')['data'];
        }

        $compareFirstname = $this->compareTwoStrings($user->getFirstname(), $data['firstname']);
        $compareLastname = $this->compareTwoStrings($user->getLastname(), $data['lastname']);

        if ($compareFirstname !== true && $compareLastname !== true) {
            return $this->generateResponseService->generateJsonResponse(403, 'invalid firstname or lastname')['data'];
        }

        $isChanged = $this->changeEmail($user, $data['email']);

        if ($isChanged['code'] !== 200) {
            return $isChanged['data'];
        }

        $user->setArchivedEmail(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'change email user')['data'];
    }
}