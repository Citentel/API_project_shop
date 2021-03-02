<?php

namespace App\Service\Searches;

use App\Entity\Orders;
use App\Entity\Payment;
use App\Entity\Status;
use App\Entity\Users;
use App\Model\AbstractOrdersSearchService;

class SearchOrdersService extends AbstractOrdersSearchService
{

    public function findOneById(int $id): array
    {
        $order = $this->entityManager->getRepository(Orders::class)->findOneById($id);

        return $this->createMessage($order);
    }

    public function findOneByName(string $name): array
    {
        $order = $this->entityManager->getRepository(Orders::class)->findOneByHash($name);

        return $this->createMessage($order);
    }

    public function findByUser(Users $users): array
    {
        $orders = $this->entityManager->getRepository(Orders::class)->findByUsers($users);

        if (empty($orders)) {
            return $this->createMessage(false);
        }

        return $this->generateResponseService->generateArrayResponse(200, 'orders exist', ['orders' => $orders]);
    }

    protected function createMessage($data): array
    {
        if (!$data) {
            return $this->generateResponseService->generateArrayResponse(404, 'order does not exist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'order exist', ['order' => $data]);
    }
}