<?php

namespace App\Model;

use App\Service\CheckPrivilegesService;
use App\Traits\accessTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractTypes
{
    use accessTrait;

    public function __construct
    (
        CheckPrivilegesService $checkPrivilegesService
    )
    {
        $this->checkPrivilegesService = $checkPrivilegesService;
    }

    abstract public function addType(Request $request): JsonResponse;

    abstract public function getType(Request $request): JsonResponse;

    abstract public function getTypes(Request $request): JsonResponse;

    abstract public function updateType(Request $request): JsonResponse;
}