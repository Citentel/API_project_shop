<?php


namespace App\Controller;


use App\Entity\Payment;
use App\Model\AbstractTypes;
use App\Service\CheckPrivilegesService;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchPaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractTypes
{
    private CheckRequestService $checkRequestService;
    private GenerateResponseService $generateResponseService;
    private EntityManagerInterface $entityManager;
    private SearchPaymentService $searchPaymentService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchPaymentService $searchPaymentService,
        CheckPrivilegesService $checkPrivilegesService
    )
    {
        parent::__construct($checkPrivilegesService);
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchPaymentService = $searchPaymentService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/payment/add", methods={"POST"})
     */
    public function addType(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isPaymentExist = $this->searchPaymentService->findOneByName($data['name']);

        if ($isPaymentExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, $isPaymentExist['message'])['data'];
        }

        $payment = (new Payment())
            ->setName($data['name']);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'payment added')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/payment/update", methods={"PATCH"})
     */
    public function updateType(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->setFieldsOptional(['name'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isPaymentExist = $this->searchPaymentService->findOneById($data['id']);

        if ($isPaymentExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isPaymentExist['code'], $isPaymentExist['message'])['data'];
        }

        /** @var Payment $payment */
        $payment = $isPaymentExist['data']['payment'];

        if (isset($data['name'])) {
            $payment->setName($data['name']);

            $this->entityManager->persist($payment);
            $this->entityManager->flush();
        }

        return $this->generateResponseService->generateJsonResponse(200, 'payment updated')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/payment/getOne", methods={"GET"})
     */
    public function getType(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isPaymentExist = $this->searchPaymentService->findOneById($data['id']);

        if ($isPaymentExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isPaymentExist['code'], $isPaymentExist['message'])['data'];
        }

        /** @var Payment $payment */
        $payment = $isPaymentExist['data']['payment'];

        $paymentResponse = [
            'id' => $payment->getId(),
            'name' => $payment->getName()
        ];

        return $this->generateResponseService->generateJsonResponse(200, 'return payment', $paymentResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/payment/getAll", methods={"GET"})
     */
    public function getTypes(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        /** @var Payment[] $payments */
        $payments = $this->entityManager->getRepository(Payment::class)->findAll();

        if (empty($payments)) {
            return $this->generateResponseService->generateJsonResponse(404, 'database do not contain any payment')['data'];
        }

        $paymentsResponse = [];

        foreach ($payments as $payment) {
            $paymentsResponse[] = [
                'id' => $payment->getId(),
                'name' => $payment->getName()
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return all payments', $paymentsResponse)['data'];
    }
}