<?php

namespace App\Controller;

use App\Entity\Addresses;
use App\Entity\Delivery;
use App\Entity\Orders;
use App\Entity\Payment;
use App\Entity\Products;
use App\Entity\Status;
use App\Service\CheckPrivilegesService;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchAddressesService;
use App\Service\Searches\SearchDeliveryService;
use App\Service\Searches\SearchOrdersService;
use App\Service\Searches\SearchPaymentService;
use App\Service\Searches\SearchProductsService;
use App\Service\Searches\SearchStatusService;
use App\Traits\accessTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderController
{
    use accessTrait;

    private CheckRequestService $checkRequestService;
    private GenerateResponseService $generateResponseService;
    private EntityManagerInterface $entityManager;
    private SearchOrdersService $searchOrdersService;
    private SearchStatusService $searchStatusService;
    private SearchDeliveryService $searchDeliveryService;
    private SearchAddressesService $searchAddressesService;
    private SearchProductsService $searchProductsService;
    private SearchPaymentService $searchPaymentService;

    public function __construct(
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchOrdersService $searchOrdersService,
        CheckPrivilegesService $checkPrivilegesService,
        SearchStatusService $searchStatusService,
        SearchDeliveryService $searchDeliveryService,
        SearchAddressesService $searchAddressesService,
        SearchProductsService $searchProductsService,
        SearchPaymentService $searchPaymentService
    )
    {
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchOrdersService = $searchOrdersService;
        $this->checkPrivilegesService = $checkPrivilegesService;
        $this->searchStatusService = $searchStatusService;
        $this->searchDeliveryService = $searchDeliveryService;
        $this->searchAddressesService = $searchAddressesService;
        $this->searchProductsService = $searchProductsService;
        $this->searchPaymentService = $searchPaymentService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/order/add", methods={"POST"})
     */
    public function addOrder(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['products', 'address', 'payment', 'delivery'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isStatusExist = $this->searchStatusService->findOneByName('created');

        if ($isStatusExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isStatusExist['code'], $isStatusExist['message'])['data'];
        }

        /** @var Status $status */
        $status = $isStatusExist['data']['status'];

        $isDeliveryExist = $this->searchDeliveryService->findOneById($data['delivery']);

        if ($isDeliveryExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isDeliveryExist['code'], $isDeliveryExist['message'])['data'];
        }

        /** @var Delivery $delivery */
        $delivery = $isDeliveryExist['data']['delivery'];

        $isAddressesExist = $this->searchAddressesService->findByUser($data['access_user']);

        if ($isAddressesExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isAddressesExist['code'], $isAddressesExist['message'])['data'];
        }

        /** @var Addresses[] $addresses */
        $addresses = $isAddressesExist['data'];
        $addressOrder = null;

        foreach ($addresses as $address) {
            if ($address->getId() === $data['address']) {
                $addressOrder = $address;
            }
        }

        if ($addressOrder === null) {
            return $this->generateResponseService->generateJsonResponse(409, 'undefined address')['data'];
        }

        $isPaymentExist = $this->searchPaymentService->findOneById($data['payment']);

        if ($isPaymentExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isPaymentExist['code'], $isPaymentExist['message'])['data'];
        }

        /** @var Payment $payment */
        $payment = $isPaymentExist['data']['payment'];

        $order = (new Orders())
            ->setUsers($data['access_user'])
            ->setHash('#' . $data['access_user']->getId() . '#' . time() . '#')
            ->setDataCreated(new \DateTime('now'))
            ->setDataUpdated(new \DateTime('now'))
            ->setStatus($status)
            ->setDelivery($delivery)
            ->setPricePay($delivery->getPrice())
            ->setAddress($addressOrder)
            ->setPayment($payment);

        foreach ($data['products'] as $product) {
            $isProductExist = $this->searchProductsService->findOneById($product);

            if ($isProductExist['code'] !== 200) {
                return $this->generateResponseService->generateJsonResponse($isProductExist['code'], $isProductExist['message'])['data'];
            }

            /** @var Products $productOrder */
            $productOrder = $isProductExist['data']['product'];

            $order->addProduct($productOrder);

            $order->setPricePay($order->getPricePay() + $productOrder->getPrice());
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added order')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/order/update", methods={"PATCH"})
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id', 'status'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isOrderExist = $this->searchOrdersService->findOneById($data['id']);

        if ($isOrderExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isOrderExist['code'], $isOrderExist['message'])['data'];
        }

        /** @var Orders $order */
        $order = $isOrderExist['data']['order'];

        $isStatusExist = $this->searchStatusService->findOneById($data['status']);

        if ($isStatusExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isStatusExist['code'], $isStatusExist['message'])['data'];
        }

        /** @var Status $status */
        $status = $isStatusExist['data']['status'];

        $order
            ->setStatus($status)
            ->setDataUpdated(new \DateTime('now'));

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'updated order status')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/order/get", methods={"GET"})
     */
    public function getOrder(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isOrdersExist = $this->searchOrdersService->findByUser($data['access_user']);

        if ($isOrdersExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isOrdersExist['code'], $isOrdersExist['message'])['data'];
        }

        /** @var Orders[] $orders */
        $orders = $isOrdersExist['data']['orders'];

        $ordersResponse = [];

        foreach ($orders as $order) {
            $ordersResponse[] = $this->generateResponseOrder($order);
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return orders', $ordersResponse)['data'];
    }

    private function generateResponseOrder(Orders $order): array
    {
        $orderResponse = [
            'id' => $order->getId(),
            'hash' => $order->getHash(),
            'status' => [
                'id' => $order->getStatus()->getId(),
                'name' => $order->getStatus()->getName()
            ],
            'address' => [
                'id' => $order->getAddress()->getId(),
                'country' => [
                    'id' => $order->getAddress()->getCountry()->getId(),
                    'code' => $order->getAddress()->getCountry()->getCode(),
                    'name' => $order->getAddress()->getCountry()->getName(),
                ],
                'city' => $order->getAddress()->getCity(),
                'street' => $order->getAddress()->getStreet(),
                'home_number' => $order->getAddress()->getHomeNumber(),
                'premises_number' => $order->getAddress()->getPremisesNumber(),
                'zip' => $order->getAddress()->getZip(),
                'display' => $order->getAddress()->getDisplay(),
            ],
            'pricePay' => $order->getPricePay(),
            'delivery' => [
                'id' => $order->getDelivery()->getId(),
                'name' => $order->getDelivery()->getName(),
                'price' => $order->getDelivery()->getPrice(),
                'price_crossed' => $order->getDelivery()->getPriceCrossed(),
            ],
            'payment' => [
                'id' => $order->getPayment()->getId(),
                'name' => $order->getPayment()->getName(),
            ],
            'dateCreated' => $order->getDataCreated()->format('Y-m-d H:i:s'),
            'dateUpdated' => $order->getDataUpdated()->format('Y-m-d H:i:s'),
        ];

        /** @var Products $product */
        foreach ($order->getProducts() as $product) {
            $orderResponse['products'][] = $this->searchProductsService->generateResponseProduct($product);
        }

        return $orderResponse;
    }
}