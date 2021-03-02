<?php

namespace App\Controller;

use App\Entity\Status;
use App\Model\AbstractTypes;
use App\Service\CheckPrivilegesService;
use App\Service\CheckRequestService;
use App\Service\GenerateResponseService;
use App\Service\Searches\SearchStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends AbstractTypes
{
    private CheckRequestService $checkRequestService;
    private GenerateResponseService $generateResponseService;
    private EntityManagerInterface $entityManager;
    private SearchStatusService $searchStatusService;

    public function __construct
    (
        CheckRequestService $checkRequestService,
        GenerateResponseService $generateResponseService,
        EntityManagerInterface $entityManager,
        CheckPrivilegesService $checkPrivilegesService,
        SearchStatusService $searchStatusService
    )
    {
        parent::__construct($checkPrivilegesService);
        $this->checkRequestService = $checkRequestService;
        $this->generateResponseService = $generateResponseService;
        $this->entityManager = $entityManager;
        $this->searchStatusService = $searchStatusService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/status/add", methods={"POST"})
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

        $isStatusExist = $this->searchStatusService->findOneByName($data['name']);

        if ($isStatusExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, $isStatusExist['message'])['data'];
        }

        $status = (new Status())
            ->setName($data['name']);

        $this->entityManager->persist($status);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'status added')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/status/update", methods={"PATCH"})
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

        $isStatusExist = $this->searchStatusService->findOneById($data['id']);

        if ($isStatusExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isStatusExist['code'], $isStatusExist['message'])['data'];
        }

        /** @var Status $status */
        $status = $isStatusExist['data']['status'];

        if (isset($data['name'])) {
            $status->setName($data['name']);

            $this->entityManager->persist($status);
            $this->entityManager->flush();
        }

        return $this->generateResponseService->generateJsonResponse(200, 'status updated')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/status/getOne", methods={"GET"})
     */
    public function getType(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isStatusExist = $this->searchStatusService->findOneById($data['id']);

        if ($isStatusExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isStatusExist['code'], $isStatusExist['message'])['data'];
        }

        /** @var Status $status */
        $status = $isStatusExist['data']['status'];

        $statusResponse = [
            'id' => $status->getId(),
            'name' => $status->getName()
        ];

        return $this->generateResponseService->generateJsonResponse(200, 'return status', $statusResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/status/getAll", methods={"GET"})
     */
    public function getTypes(Request $request): JsonResponse
    {
        /** @var Status[] $statuses */
        $statuses = $this->entityManager->getRepository(Status::class)->findAll();

        if (empty($statuses)) {
            return $this->generateResponseService->generateJsonResponse(404, 'database do not contain any status status')['data'];
        }

        $statusesResponse = [];

        foreach ($statuses as $status) {
            $statusesResponse[] = [
                'id' => $status->getId(),
                'name' => $status->getName()
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return all status', $statusesResponse)['data'];
    }
}