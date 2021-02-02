<?php

namespace App\Controller;

use App\Entity\Users;
use App\Model\EmailPrepareModel;
use App\Service\CheckRequestService;
use App\Service\EmailSendService;
use App\Service\GenerateResponseService;
use App\Service\SearchRolesService;
use App\Service\SearchUsersService;
use App\Service\ValidatorUserDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private CheckRequestService $checkRequestService;
    private GenerateResponseService $generateResponseService;
    private SearchUsersService $searchUsersService;
    private EntityManagerInterface $entityManager;
    private SearchRolesService $searchRolesService;
    private ValidatorUserDataService $validatorUserDataService;
    private EmailPrepareModel $emailPrepareModel;
    private EmailSendService $emailSendService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchUsersService $searchUsersService,
        SearchRolesService $searchRolesService,
        ValidatorUserDataService $validatorUserDataService,
        EmailPrepareModel $emailPrepareModel,
        EmailSendService $emailSendService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchUsersService = $searchUsersService;
        $this->searchRolesService = $searchRolesService;
        $this->validatorUserDataService = $validatorUserDataService;
        $this->emailPrepareModel = $emailPrepareModel;
        $this->emailSendService = $emailSendService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user/register")
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

        if ($isUserExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'user exist')['data'];
        }

        $isRoleExist = $this->searchRolesService->findOneByName('ROLE_USER');

        if ($isRoleExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'role does not exist')['data'];
        }

        $role = $isRoleExist['data']['role'];
        $verifyCode = substr(password_hash(md5($data['email']), PASSWORD_BCRYPT), 0, 10);

        $newUser = (new Users())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setPassword(password_hash($data['password'], PASSWORD_BCRYPT))
            ->setVerifyCode(password_hash($verifyCode, PASSWORD_BCRYPT))
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
                '<p><b>Hello there!</b></p><p>You have been registered with the application. To use the app fully go to the link below and verify your account.</p><p>http://127.0.0.1::8000/user/verify/?id='.$userId.'&vc='.$verifyCode.'</p><p><small>This message has been generated automatically. Do not reply to this message.</small></p>');

        $this->emailSendService->sendEmail($email);

        return $this->generateResponseService->generateJsonResponse(200, 'user added to database')['data'];
    }
}