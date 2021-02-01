<?php


namespace App\EventListener;

use App\Entity\Users;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        /** @var Users $user */
        $user = $event->getUser();


        if (!$user instanceof UserInterface) {
            return;
        }

        $data['user'] = [
            'uid' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
        ];

        $event->setData($data);
    }

    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $data = [
            'status'  => '401 Unauthorized',
            'message' => 'Bad credentials, please verify that your username/password are correctly set',
        ];

        $response = new JWTAuthenticationFailureResponse($data);

        $event->setResponse($response);
    }

    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $response = new JWTAuthenticationFailureResponse('Your token is invalid, please login again to get a new one', 403);

        $event->setResponse($response);
    }

    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $data = [
            'status'  => '403 Forbidden',
            'message' => 'Missing token',
        ];

        $response = new JsonResponse($data, 403);

        $event->setResponse($response);
    }

    public function onJWTExpired(JWTExpiredEvent $event)
    {
        /** @var JWTAuthenticationFailureResponse */
        $response = $event->getResponse();

        $response->setMessage('Your token is expired, please renew it.');
    }
}