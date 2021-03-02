<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Model\AbstractTypes;
use App\Service\CheckPrivilegesService;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchDeliveryService;
use App\Traits\accessTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DeliveryController extends AbstractTypes
{
    use accessTrait;

    private CheckRequestService $checkRequestService;
    private GenerateResponseService $generateResponseService;
    private EntityManagerInterface $entityManager;
    private SearchDeliveryService $searchDeliveryService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        SearchDeliveryService $searchDeliveryService,
        CheckPrivilegesService $checkPrivilegesService
    )
    {
        parent::__construct($checkPrivilegesService);
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchDeliveryService = $searchDeliveryService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/delivery/add", methods={"POST"})
     */
    public function addType(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name', 'price'])
            ->setFieldsOptional(['price_crossed'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isDeliveryExist = $this->searchDeliveryService->findOneByName($data['name']);

        if ($isDeliveryExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse($isDeliveryExist['code'], $isDeliveryExist['message'])['data'];
        }

        $delivery = (new Delivery())
            ->setName($data['name'])
            ->setPrice($data['price'] < 0 ? 0 : $data['price'])
            ->setPriceCrossed(($data['price_crossed'] ?? 0) < 0 ? 0 : $data['price_crossed']);

        $this->entityManager->persist($delivery);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added new delivery type')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/delivery/update", methods={"PATCH"})
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
            ->setFieldsOptional(['name', 'price', 'price_crossed'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isDeliveryExist = $this->searchDeliveryService->findOneById($data['id']);

        if ($isDeliveryExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isDeliveryExist['code'], $isDeliveryExist['message'])['data'];
        }

        /** @var Delivery $delivery */
        $delivery = $isDeliveryExist['data']['delivery'];

        if (isset($data['name'])) {
            $delivery->setName($data['name']);
        }

        if (isset($data['price'])) {
            $delivery->setName($data['price'] < 0 ? 0 : $data['price']);
        }

        if (isset($data['price_crossed'])) {
            $delivery->setName($data['price_crossed'] < 0 ? 0 : $data['price_crossed']);
        }

        $this->entityManager->persist($delivery);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'updated delivery type')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/delivery/getOne", methods={"GET"})
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

        $isDeliveryExist = $this->searchDeliveryService->findOneById((int)$data['id']);

        if ($isDeliveryExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isDeliveryExist['code'], $isDeliveryExist['message'])['data'];
        }

        /** @var Delivery $delivery */
        $delivery = $isDeliveryExist['data']['delivery'];

        $deliveryResponse = [
            'id' => $delivery->getId(),
            'name' => $delivery->getName(),
            'price' => $delivery->getPrice(),
            'price_crossed' => $delivery->getPriceCrossed()
        ];

        return $this->generateResponseService->generateJsonResponse(200, 'return delivery', $deliveryResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/delivery/getAll", methods={"GET"})
     */
    public function getTypes(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        /** @var Delivery[] $deliveryTypes */
        $deliveryTypes = $this->entityManager->getRepository(Delivery::class)->findAll();

        if (empty($deliveryTypes)) {
            return $this->generateResponseService->generateJsonResponse(404, 'delivery does not contain in database')['code'];
        }

        $deliveryTypesResponse = [];

        foreach ($deliveryTypes as $delivery) {
            $deliveryTypesResponse[] = [
                'id' => $delivery->getId(),
                'name' => $delivery->getName(),
                'price' => $delivery->getPrice(),
                'price_crossed' => $delivery->getPriceCrossed()
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return all delivery', $deliveryTypesResponse)['data'];
    }
}