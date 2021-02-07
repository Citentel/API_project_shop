<?php

namespace App\Model;

use App\Entity\Users;
use App\Service\CheckRequestService;
use App\Service\EmailSendService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchRolesService;
use App\Service\Searches\SearchUsersService;
use App\Service\Validators\ValidatorUserDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractUser extends AbstractController
{
    protected CheckRequestService $checkRequestService;
    protected GenerateResponseService $generateResponseService;
    protected SearchUsersService $searchUsersService;
    protected EntityManagerInterface $entityManager;
    protected SearchRolesService $searchRolesService;
    protected ValidatorUserDataService $validatorUserDataService;
    protected EmailPrepareModel $emailPrepareModel;
    protected EmailSendService $emailSendService;
    protected static array $REGEX_CODE = ['/', '.', ',', '\\'];

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


    protected function changeFirstname(Users $user, string $newFirstname): array
    {
        $checkDataFromUser = $this->validatorUserDataService->checkFirstname($newFirstname);

        if ($checkDataFromUser['code'] !== 200) {
            return $checkDataFromUser;
        }

        $user->setFirstname($newFirstname);

        return $this->generateResponseService->generateArrayResponse(200, 'changed firstname', ['user' => $user]);
    }

    protected function changeLastname(Users $user, string $newLastname): array
    {
        $checkDataFromUser = $this->validatorUserDataService->checkLastname($newLastname);

        if ($checkDataFromUser['code'] !== 200) {
            return $checkDataFromUser;
        }

        $user->setLastname($newLastname);

        return $this->generateResponseService->generateArrayResponse(200, 'changed lastname', ['user' => $user]);
    }

    protected function changeEmail(Users $user, string $newEmail): array
    {
        $isUserExist = $this->searchUsersService->findOneByEmail($newEmail);

        if ($isUserExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, 'email exist');
        }

        $checkDataFromUser = $this->validatorUserDataService->checkEmail($newEmail);

        if ($checkDataFromUser['code'] !== 200) {
            return $checkDataFromUser;
        }

        $verifyCode = $this->generateCode($newEmail);

        $user->setArchivedEmail($user->getEmail());
        $user->setEmail($newEmail)->setVerifyCode($verifyCode);

        $email = $this->emailPrepareModel
            ->to($newEmail)
            ->subject('User email authorization')
            ->html(
                '<p><b>Hello there!</b></p><p>Your email address has been changed. To use the app fully go to the link below and verify your email.</p><p>http://127.0.0.1::8000/user/verify/?uid='.$user->getId().'&vc='.$verifyCode.'</p><p><small>This message has been generated automatically. Do not reply to this message.</small></p>');

        $this->emailSendService->sendEmail($email);

        return $this->generateResponseService->generateArrayResponse(200, 'changed email', ['user' => $user]);
    }

    protected function changePassword(Users $user, string $newPassword): array
    {
        $checkDataFromUser = $this->validatorUserDataService->checkPassword($newPassword);

        if ($checkDataFromUser['code'] !== 200) {
            return $checkDataFromUser;
        }

        $user->setPassword($newPassword);

        $email = $this->emailPrepareModel
            ->to($user->getEmail())
            ->subject('User change password')
            ->html(
                '<p><b>Hello there!</b></p><p>Your password has been changed<p><small>This message has been generated automatically. Do not reply to this message.</small></p>');

        $this->emailSendService->sendEmail($email);

        return $this->generateResponseService->generateArrayResponse(200, 'changed password', ['user' => $user]);
    }

    protected function generateCode(string $email, int $maxLength = 10): string
    {
        $code = substr(password_hash(md5($email), PASSWORD_BCRYPT), 0, $maxLength);
        return str_replace(self::$REGEX_CODE, '0', $code);
    }

    protected function compareTwoStrings(string $str1, string $str2): bool
    {
        $str1 = $this->prepareStringToCompare($str1);
        $str2 = $this->prepareStringToCompare($str2);

        return strcmp($str1, $str2) === 0;
    }

    protected function prepareStringToCompare(string $toCompare): string
    {
        return preg_replace('/\s+/', '', strtolower($toCompare));
    }
}