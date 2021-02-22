<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractType
{
    abstract public function addType(Request $request): JsonResponse;

    abstract public function getType(Request $request): JsonResponse;

    abstract public function getTypes(Request $request): JsonResponse;

    abstract public function getProductByType(Request $request): JsonResponse;
}