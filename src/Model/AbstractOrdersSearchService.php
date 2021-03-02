<?php


namespace App\Model;

use App\Entity\Payment;
use App\Entity\Status;
use App\Entity\Users;

abstract class AbstractOrdersSearchService extends AbstractSearchService
{
    abstract public function findByUser(Users $users): array;
}